<template>
  <Modal
    :model-value="show"
    @update:model-value="$emit('close')"
    title="ثبت متن احراز ویدیویی"
    size="lg"
  >
    <div class="flex flex-col h-full max-h-[70vh]">
      <div class="flex-1 overflow-y-auto space-y-6 pr-2">
        <!-- Text Input -->
        <div class="space-y-2">
          <label class="block text-sm font-medium text-[var(--theme-text-primary)]">
            متن احراز ویدیویی
          </label>
          <textarea
            v-model="text"
            class="w-full px-3 py-2 bg-[var(--theme-bg-base)] border border-[var(--theme-border)] rounded-lg text-sm text-[var(--theme-text-primary)] focus:outline-none focus:ring-2 focus:ring-primary-500/50"
            rows="5"
            placeholder="متن را وارد کنید"
          />
          <p v-if="errors.text" class="text-xs text-error">{{ errors.text }}</p>

          <!-- Submit Button -->
          <div class="flex justify-end pt-2">
            <Button
              variant="primary"
              :loading="saving"
              @click="handleSave"
            >
              ثبت
            </Button>
          </div>
        </div>

        <hr class="border-[var(--theme-border)]" />

        <!-- Existing Texts -->
        <div class="space-y-3">
          <h4 class="text-sm font-semibold text-[var(--theme-text-primary)]">متون موجود:</h4>
          <div v-if="loadingTexts && texts.length === 0" class="flex justify-center py-4">
            <Spinner />
          </div>
          <div v-else-if="texts.length > 0" class="space-y-2">
            <div
              v-for="textItem in texts"
              :key="textItem.id"
              class="p-3 bg-[var(--theme-bg-glass)] rounded-lg border border-[var(--theme-border)] cursor-pointer hover:bg-[var(--theme-bg-elevated)] transition-colors"
              @click="openTextDialog(textItem)"
            >
              <p class="text-sm text-[var(--theme-text-secondary)] mb-2">
                {{ truncateText(textItem.text, 100) }}
                <span v-if="textItem.text.length > 100" class="text-primary-500 text-xs">(کلیک برای مشاهده کامل)</span>
              </p>
            </div>

            <!-- View More Button -->
            <div v-if="hasMorePages && !loadingMore" class="flex justify-center pt-2">
              <Button
                variant="secondary"
                size="sm"
                rounded="full"
                class="!p-2 !gap-0 min-w-[2.25rem]"
                title="مشاهده بیشتر"
                aria-label="مشاهده بیشتر"
                @click="loadMore"
              >
                <template #icon-left>
                  <TableActionIcon name="view" />
                </template>
              </Button>
            </div>

            <!-- Loading More Indicator -->
            <div v-if="loadingMore" class="flex justify-center py-2">
              <Spinner size="sm" />
            </div>
          </div>
          <Alert
            v-else-if="!loadingTexts"
            variant="warning"
            message="اطلاعاتی تعریف نشده است"
          />
        </div>
      </div>
    </div>

    <template #footer>
      <div class="flex gap-3 justify-end">
        <Button
          variant="danger"
          @click="$emit('close')"
        >
          بستن
        </Button>
      </div>
    </template>
  </Modal>

  <!-- Text Detail Dialog -->
  <Modal
    :model-value="showTextDialog"
    @update:model-value="showTextDialog = false"
    title="متن کامل احراز ویدیویی"
    size="lg"
    closeOnBackdrop
  >
    <div v-if="selectedText" class="space-y-6">
      <!-- View Mode -->
      <div v-if="!isEditing" class="space-y-4">
        <div class="p-4 bg-[var(--theme-bg-base)] rounded-lg border border-[var(--theme-border)]">
          <p class="text-sm text-[var(--theme-text-secondary)] whitespace-pre-wrap">{{ selectedText.text }}</p>
        </div>
      </div>

      <!-- Edit Mode -->
      <div v-else class="space-y-4">
        <div class="space-y-2">
          <label class="block text-sm font-medium text-[var(--theme-text-primary)]">
            متن احراز ویدیویی
          </label>
          <textarea
            v-model="editText"
            class="w-full px-3 py-2 bg-[var(--theme-bg-base)] border border-[var(--theme-border)] rounded-lg text-sm text-[var(--theme-text-primary)] focus:outline-none focus:ring-2 focus:ring-primary-500/50"
            rows="8"
            placeholder="متن را وارد کنید"
          />
          <p v-if="editErrors.text" class="text-xs text-error">{{ editErrors.text }}</p>
        </div>

      </div>
    </div>

    <template #footer>
      <div class="flex gap-3 justify-end">
        <Button
          variant="ghost"
          @click="showTextDialog = false"
        >
          بستن
        </Button>
        <template v-if="!isEditing">
          <Button
            variant="danger"
            rounded="full"
            class="!p-2 !gap-0 min-w-[2.5rem] sm:min-w-0 sm:px-4 sm:!gap-2"
            title="حذف"
            aria-label="حذف متن"
            :loading="deletingId === selectedText?.id"
            @click="handleDeleteFromDialog"
          >
            <template #icon-left>
              <TableActionIcon name="delete" />
            </template>
          </Button>
          <Button
            variant="primary"
            rounded="full"
            class="!p-2 !gap-0 min-w-[2.5rem] sm:min-w-0 sm:px-4 sm:!gap-2"
            title="ویرایش"
            aria-label="ویرایش متن"
            @click="startEditing"
          >
            <template #icon-left>
              <TableActionIcon name="edit" />
            </template>
          </Button>
        </template>
        <template v-else>
          <Button
            variant="ghost"
            @click="cancelEditing"
          >
            لغو
          </Button>
          <Button
            variant="primary"
            :loading="updating"
            @click="handleUpdate"
          >
            ذخیره تغییرات
          </Button>
        </template>
      </div>
    </template>
  </Modal>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import apiClient from '../../utils/api'
import { Modal, Button, Spinner, Alert } from '../ui'
import { useToast } from '../../composables/useToast'
import { notifyError, confirm } from '../../utils/notifications'
import TableActionIcon from '../icons/TableActionIcon.vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close'])

const text = ref('')
const saving = ref(false)
const loadingTexts = ref(false)
const loadingMore = ref(false)
const deletingId = ref(null)
const updating = ref(false)
const texts = ref([])
const errors = ref({})
const editErrors = ref({})
const currentPage = ref(1)
const hasMorePages = ref(false)
const perPage = ref(10)
const showTextDialog = ref(false)
const selectedText = ref(null)
const isEditing = ref(false)
const editText = ref('')

const { showToast } = useToast()

const truncateText = (text, maxLength = 100) => {
  if (!text || text.length <= maxLength) return text
  return text.substring(0, maxLength) + '...'
}

const openTextDialog = (textItem) => {
  selectedText.value = textItem
  editText.value = textItem.text
  isEditing.value = false
  showTextDialog.value = true
  editErrors.value = {}
}

const startEditing = () => {
  isEditing.value = true
  editText.value = selectedText.value?.text || ''
  editErrors.value = {}
}

const cancelEditing = () => {
  isEditing.value = false
  editText.value = selectedText.value?.text || ''
  editErrors.value = {}
}

const handleUpdate = async () => {
  try {
    editErrors.value = {}

    if (!editText.value || !editText.value.trim()) {
      editErrors.value.text = 'متن را وارد کنید'
      return
    }

    updating.value = true

    const response = await apiClient.put(`/kyc-video-texts/${selectedText.value.id}`, {
      text: editText.value
    })

    if (response.data.success) {
      showToast('متن احراز ویدیویی با موفقیت به‌روزرسانی شد.', 'success')

      // Update the item in the local array
      const index = texts.value.findIndex(item => item.id === selectedText.value.id)
      if (index !== -1) {
        texts.value[index] = response.data.data
        selectedText.value = response.data.data
      }

      isEditing.value = false
    } else {
      editErrors.value = response.data.errors || {}
    }
  } catch (err) {
    console.error('KYC video text update error:', err)
    if (err.response?.data?.errors) {
      editErrors.value = err.response.data.errors
    } else {
      await notifyError(err.response?.data?.message || 'خطا در به‌روزرسانی اطلاعات')
    }
  } finally {
    updating.value = false
  }
}

const handleDeleteFromDialog = async () => {
  if (!selectedText.value) return

  await handleDelete(selectedText.value.id)

  // Close dialog after deletion
  showTextDialog.value = false
  selectedText.value = null
}

const handleSave = async () => {
  try {
    errors.value = {}

    if (!text.value || !text.value.trim()) {
      errors.value.text = 'متن را وارد کنید'
      return
    }

    saving.value = true

    const response = await apiClient.post('/kyc-video-texts', {
      text: text.value
    })

    if (response.data.success) {
      showToast('متن احراز ویدیویی با موفقیت ثبت شد.', 'success')

      text.value = ''
      currentPage.value = 1
      hasMorePages.value = false
      fetchTexts(1, false)
    } else {
      errors.value = response.data.errors || {}
    }
  } catch (err) {
    console.error('KYC video text save error:', err)
    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors
    } else {
      await notifyError(err.response?.data?.message || 'خطا در ثبت اطلاعات')
    }
  } finally {
    saving.value = false
  }
}

const handleDelete = async (id) => {
  try {
    const result = await confirm(
      'آیا از حذف این متن اطمینان دارید؟',
      'آیا مطمئن هستید؟',
      {
        confirmText: 'بله، حذف کن',
        cancelText: 'لغو'
      }
    )

    if (!result.isConfirmed) return

    deletingId.value = id

    const response = await apiClient.delete(`/kyc-video-texts/${id}`)

    if (response.data.success) {
      showToast('متن احراز ویدیویی با موفقیت حذف شد.', 'success')
      // Remove deleted item from local array instead of refetching all
      texts.value = texts.value.filter(item => item.id !== id)
      // Check if we need to load more items to fill the gap
      if (texts.value.length < perPage.value && hasMorePages.value) {
        await fetchTexts(currentPage.value + 1, true)
      }
    }
  } catch (err) {
    console.error('KYC video text delete error:', err)
    await notifyError(err.response?.data?.message || 'خطا در حذف متن')
  } finally {
    deletingId.value = null
  }
}

const fetchTexts = async (page = 1, append = false) => {
  try {
    if (append) {
      loadingMore.value = true
    } else {
      loadingTexts.value = true
    }

    const response = await apiClient.get('/kyc-video-texts', {
      params: {
        page,
        per_page: perPage.value
      }
    })

    if (response.data.success) {
      if (append) {
        texts.value = [...texts.value, ...response.data.data]
      } else {
        texts.value = response.data.data
      }

      currentPage.value = response.data.pagination.current_page
      hasMorePages.value = response.data.pagination.has_more
    }
  } catch (err) {
    console.error('KYC video texts fetch error:', err)
  } finally {
    loadingTexts.value = false
    loadingMore.value = false
  }
}

const loadMore = async () => {
  if (!hasMorePages.value || loadingMore.value) return
  await fetchTexts(currentPage.value + 1, true)
}

watch(() => props.show, (newVal) => {
  if (newVal) {
    text.value = ''
    errors.value = {}
    currentPage.value = 1
    hasMorePages.value = false
    fetchTexts(1, false)
  }
})

watch(() => showTextDialog.value, (newVal) => {
  if (!newVal) {
    // Reset editing state when dialog closes
    isEditing.value = false
    selectedText.value = null
    editText.value = ''
    editErrors.value = {}
  }
})

onMounted(() => {
  if (props.show) {
    fetchTexts(1, false)
  }
})
</script>

<style scoped>
/* Additional styles if needed */
</style>

