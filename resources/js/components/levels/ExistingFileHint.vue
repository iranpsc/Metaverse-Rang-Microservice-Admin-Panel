<template>
  <div v-if="hasFiles" class="space-y-2">
    <button
      v-if="isCollapsible"
      type="button"
      class="flex w-full items-center justify-between gap-2 rounded-lg border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)]/50 px-3 py-2 text-xs transition hover:border-primary-400/40"
      :aria-expanded="isExpanded"
      @click="isExpanded = !isExpanded"
    >
      <span class="truncate text-[var(--theme-text-secondary)]">
        {{ label || 'فایل‌های موجود' }}
        <span class="text-[var(--theme-text-muted)]">({{ fileCount }})</span>
      </span>
      <svg
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
        stroke-width="1.5"
        stroke="currentColor"
        class="h-4 w-4 shrink-0 text-[var(--theme-text-muted)] transition-transform duration-200"
        :class="{ 'rotate-180': isExpanded }"
        aria-hidden="true"
      >
        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
      </svg>
    </button>

    <p v-else-if="label" class="text-xs text-[var(--theme-text-secondary)]">
      {{ label }}
    </p>

    <ul v-show="!isCollapsible || isExpanded" class="space-y-1.5">
      <li
        v-for="(url, fileType) in fileEntries"
        :key="`${fileType}-${url}`"
        class="flex items-center justify-between gap-2 rounded-lg border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)]/50 px-3 py-2 text-xs"
      >
        <span class="min-w-0 truncate text-[var(--theme-text-secondary)]" :title="String(fileType)">
          {{ fileType }}
        </span>
        <div class="flex shrink-0 items-center gap-2">
          <a
            :href="url"
            target="_blank"
            rel="noopener"
            class="inline-flex items-center text-primary-300 hover:text-primary-200"
            :title="`مشاهده ${fileType}`"
            :aria-label="`مشاهده ${fileType}`"
          >
            <TableActionIcon name="file" icon-class="w-4 h-4 shrink-0" />
          </a>
          <button
            v-if="deletable"
            type="button"
            class="inline-flex items-center text-error hover:opacity-80 disabled:cursor-not-allowed disabled:opacity-50"
            :disabled="deletingKey === fileType"
            :title="`حذف ${fileType}`"
            :aria-label="`حذف ${fileType}`"
            @click="handleDelete(String(fileType))"
          >
            <TableActionIcon name="delete" icon-class="w-4 h-4 shrink-0" />
          </button>
        </div>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import TableActionIcon from '../icons/TableActionIcon.vue'
import apiClient from '../../utils/api'
import { confirm, notifyError, notifySuccess } from '../../utils/notifications'

const props = defineProps({
  url: {
    type: [String, Object, Array],
    default: null
  },
  label: {
    type: String,
    default: ''
  },
  deletable: {
    type: Boolean,
    default: false
  },
  /**
   * API path relative to /api, e.g. `/levels/1/gem/files`
   * When omitted, delete is local-only (emits `deleted` without calling the API).
   */
  deleteUrl: {
    type: String,
    default: ''
  },
  /**
   * Model field name, e.g. `fbx_file`, `png_file`, `gif_file`
   */
  field: {
    type: String,
    default: 'fbx_file'
  }
})

const emit = defineEmits(['deleted'])

const isExpanded = ref(true)
const deletingKey = ref(null)
const localEntries = ref({})

const parseEntries = (value) => {
  if (!value) {
    return {}
  }

  if (typeof value === 'string') {
    const trimmed = value.trim()
    if (!trimmed) return {}

    try {
      const parsed = JSON.parse(trimmed)
      if (parsed && typeof parsed === 'object' && !Array.isArray(parsed)) {
        return Object.fromEntries(
          Object.entries(parsed).filter(([, link]) => typeof link === 'string' && link)
        )
      }
    } catch {
      // plain URL string
    }

    return { file: trimmed }
  }

  if (Array.isArray(value)) {
    return value.reduce((acc, item, index) => {
      if (typeof item === 'string' && item) {
        acc[`file_${index + 1}`] = item
      }
      return acc
    }, {})
  }

  if (typeof value === 'object') {
    return Object.fromEntries(
      Object.entries(value).filter(([, link]) => typeof link === 'string' && link)
    )
  }

  return {}
}

watch(
  () => props.url,
  (value) => {
    localEntries.value = parseEntries(value)
  },
  { immediate: true, deep: true }
)

const fileEntries = computed(() => localEntries.value)
const fileCount = computed(() => Object.keys(fileEntries.value).length)
const hasFiles = computed(() => fileCount.value > 0)
const isCollapsible = computed(() => fileCount.value > 1)
const isMapField = computed(() => props.field === 'fbx_file')

const handleDelete = async (fileKey) => {
  if (!props.deletable || deletingKey.value) return

  if (!(fileKey in localEntries.value)) return

  const result = await confirm(
    `آیا از حذف فایل «${fileKey}» اطمینان دارید؟ این عمل قابل بازگشت نیست.`,
    'حذف فایل',
    {
      icon: 'warning',
      confirmText: 'بله، حذف شود',
      cancelText: 'انصراف'
    }
  )

  if (!result.isConfirmed) return

  deletingKey.value = fileKey

  try {
    let remaining = { ...localEntries.value }
    delete remaining[fileKey]

    if (!isMapField.value) {
      remaining = {}
    }

    if (props.deleteUrl) {
      const payload = { field: props.field }
      if (isMapField.value) {
        payload.file_key = fileKey
      }

      const response = await apiClient.delete(props.deleteUrl, { data: payload })

      if (!response.data?.success) {
        notifyError(response.data?.message || 'خطا در حذف فایل')
        return
      }

      const updatedField = response.data?.data?.[props.field]
      if (updatedField && typeof updatedField === 'object' && !Array.isArray(updatedField)) {
        remaining = parseEntries(updatedField)
      } else {
        remaining = {}
      }

      notifySuccess(response.data?.message || 'فایل با موفقیت حذف شد.')
    }

    localEntries.value = remaining

    emit('deleted', {
      field: props.field,
      fileKey,
      remaining: Object.keys(remaining).length ? remaining : null
    })
  } catch (error) {
    console.error('Delete file error:', error)
    notifyError(error.response?.data?.message || 'خطا در حذف فایل')
  } finally {
    deletingKey.value = null
  }
}
</script>
