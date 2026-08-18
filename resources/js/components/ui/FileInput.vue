<template>
  <div class="space-y-2" dir="rtl">
    <label
      v-if="label"
      :for="inputId"
      :class="[
        'block text-sm font-medium text-[var(--theme-text-primary)]',
        error ? 'text-error' : ''
      ]"
    >
      {{ label }}
      <span v-if="required" class="text-error">*</span>
    </label>

    <div
      :class="[
        'relative flex items-center justify-between gap-3 rounded-xl border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)]/80 px-4 py-3 transition-all duration-200',
        'hover:border-primary-500/60 hover:shadow-[0_0_18px_rgba(124,58,237,0.25)] focus-within:border-primary-500 focus-within:ring-2 focus-within:ring-primary-500/40',
        error ? 'border-error focus-within:border-error focus-within:ring-error/40 hover:border-error/70' : '',
        (disabled || loading) ? 'opacity-60 cursor-not-allowed hover:border-[var(--theme-border)] hover:shadow-none' : ''
      ]"
      :aria-busy="loading ? 'true' : 'false'"
    >
      <div class="flex items-center gap-3 min-w-0">
        <div
          :class="[
            'flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg border border-[var(--theme-border)] bg-[var(--theme-bg-glass)] text-primary-300 shadow-inner',
            error ? 'text-error border-error/60' : ''
          ]"
        >
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M3 7v4a1 1 0 001 1h3l3 3v-3h2l3 3v-3h2a1 1 0 001-1V7a1 1 0 00-1-1H4a1 1 0 00-1 1z"
            />
          </svg>
        </div>

        <div class="flex flex-col min-w-0">
          <span class="text-sm font-medium text-[var(--theme-text-primary)]">
            {{ placeholderText }}
          </span>
          <span class="text-xs text-[var(--theme-text-secondary)] truncate" v-if="fileSummary">
            {{ fileSummary }}
          </span>
          <span class="text-xs text-[var(--theme-text-muted)]" v-else-if="helperText">
            {{ helperText }}
          </span>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <button
          v-if="clearable && hasValue"
          type="button"
          class="hidden text-xs font-medium text-error transition-colors hover:text-error/80 sm:inline-flex"
          :disabled="disabled || loading"
          @click="handleClear"
        >
          پاک کردن
        </button>

        <label
          :for="inputId"
          :class="[
            'inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-primary-500/90 to-secondary-500/80 px-3 py-2 text-xs font-medium text-white shadow-md transition-all duration-200',
            'hover:shadow-[0_0_18px_rgba(124,58,237,0.45)] hover:from-primary-500 hover:to-secondary-500 cursor-pointer',
            (disabled || loading) ? 'opacity-60 cursor-not-allowed hover:shadow-none hover:from-primary-500/90 hover:to-secondary-500/80' : ''
          ]"
        >
          <Spinner v-if="loading" size="sm" color="#ffffff" />
          <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M7 16a4 4 0 01-.88-7.903 5 5 0 019.76-1.14A4.5 4.5 0 1118.5 16H16"
            />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12v9m0 0l-3-3m3 3l3-3" />
          </svg>
          <span>{{ loading ? 'در حال بارگذاری...' : 'انتخاب فایل' }}</span>
        </label>
      </div>

      <input
        :id="inputId"
        ref="inputRef"
        :accept="accept"
        :disabled="disabled || loading"
        :multiple="multiple"
        type="file"
        class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
        @change="handleChange"
      />
    </div>

    <button
      v-if="clearable && hasValue"
      type="button"
      class="inline-flex items-center gap-1 text-xs font-medium text-error transition-colors hover:text-error/80 sm:hidden"
      :disabled="disabled || loading"
      @click="handleClear"
    >
      <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
      <span>پاک کردن فایل</span>
    </button>

    <p v-if="error" class="text-xs text-error">
      {{ error }}
    </p>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import Spinner from './Spinner.vue'

const props = defineProps({
  modelValue: {
    type: [File, Array, Object, String],
    default: null
  },
  label: {
    type: String,
    default: ''
  },
  helperText: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'انتخاب فایل یا کشیدن و رها کردن'
  },
  accept: {
    type: String,
    default: ''
  },
  required: {
    type: Boolean,
    default: false
  },
  multiple: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  error: {
    type: String,
    default: ''
  },
  id: {
    type: String,
    default: ''
  },
  clearable: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['update:modelValue', 'change', 'clear'])

const inputRef = ref(null)
const uniqueId = `file-input-${Math.random().toString(36).slice(2, 9)}`
const inputId = computed(() => props.id || uniqueId)

const hasValue = computed(() => {
  if (props.multiple) {
    return Array.isArray(props.modelValue) && props.modelValue.length > 0
  }
  return props.modelValue instanceof File
})

const placeholderText = computed(() => {
  if (props.loading) {
    return 'در حال بارگذاری فایل...'
  }

  if (hasValue.value) {
    return 'فایل انتخاب شد'
  }
  return props.placeholder
})

const fileSummary = computed(() => {
  if (!hasValue.value) {
    return ''
  }

  if (props.multiple) {
    const files = Array.isArray(props.modelValue) ? props.modelValue : []
    if (files.length === 0) return ''
    if (files.length === 1) {
      return files[0]?.name || ''
    }
    return `${files.length} فایل انتخاب شده`
  }

  return props.modelValue?.name || ''
})

const resetInput = () => {
  if (inputRef.value) {
    inputRef.value.value = ''
  }
}

const handleChange = (event) => {
  const files = event.target.files ? Array.from(event.target.files) : []

  const value = props.multiple ? files : files[0] || null

  emit('update:modelValue', value)
  emit('change', value)

  if (!value || (Array.isArray(value) && value.length === 0)) {
    resetInput()
  }
}

const handleClear = () => {
  const value = props.multiple ? [] : null
  emit('update:modelValue', value)
  emit('clear', value)
  resetInput()
}

watch(
  () => props.modelValue,
  (newValue) => {
    const isEmpty = props.multiple
      ? !Array.isArray(newValue) || newValue.length === 0
      : !(newValue instanceof File)

    if (isEmpty) {
      resetInput()
    }
  }
)
</script>

<style scoped>
.text-error {
  color: var(--color-error, #EF4444);
}
</style>

