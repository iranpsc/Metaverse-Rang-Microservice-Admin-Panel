<template>
  <div class="p-6 space-y-6">
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-[var(--theme-text-primary)] mb-2">پیام های سلسله</h1>
      <p class="text-[var(--theme-text-secondary)]">مدیریت پیام‌های سلسله خانوادگی</p>
    </div>

    <!-- Create Button -->
    <div class="mb-6">
      <Button variant="primary" @click="openCreateModal">
        ایجاد پیام
      </Button>
    </div>

    <!-- Loading State -->
    <LoadingState v-if="loading" />

    <!-- Error State -->
    <ErrorState
      v-else-if="error"
      :message="error"
      variant="error"
    />

    <!-- Table -->
    <Table
      v-else
      :columns="tableColumns"
      :data="messages"
      empty-state-message="پیامی برای سلسله تعریف نشده است"
    >
      <template #cell-actions="{ row }">
        <div class="flex items-center gap-2">
          <Button
            size="sm"
            variant="secondary"
            rounded="full"
            class="!p-2 !gap-0 min-w-[2.25rem]"
            title="مشاهده"
            aria-label="مشاهده"
            @click="openViewModal(row)"
          >
            <template #icon-left>
              <TableActionIcon name="view" />
            </template>
          </Button>
          <Button
            size="sm"
            variant="primary"
            rounded="full"
            class="!p-2 !gap-0 min-w-[2.25rem]"
            title="ویرایش"
            aria-label="ویرایش"
            @click="openEditModal(row)"
          >
            <template #icon-left>
              <TableActionIcon name="edit" />
            </template>
          </Button>
          <Button
            size="sm"
            variant="danger"
            rounded="full"
            class="!p-2 !gap-0 min-w-[2.25rem]"
            title="حذف"
            aria-label="حذف"
            @click="handleDelete(row)"
          >
            <template #icon-left>
              <TableActionIcon name="delete" />
            </template>
          </Button>
        </div>
      </template>
    </Table>

    <!-- Create Modal -->
    <Modal
      v-model="showCreateModal"
      title="تعریف پیام سلسله"
      size="xl"
      @close="resetCreateForm"
    >
      <div class="space-y-6">
        <!-- Shortcodes Info -->
        <Alert
          variant="info"
          title="لیست شورت کدهای پیامهای سلسله"
          :dismissible="false"
        >
          <ul class="list-disc list-inside space-y-1 mt-2">
            <li>نسبت خانوادگی: [relationship]</li>
            <li>کد شهروندی دریافت کننده: [reciever-code]</li>
            <li>کد شهروندی ارسال کننده: [sender-code]</li>
            <li>تاریخ: [created_at]</li>
            <li>نام فرستنده: [sender-name]</li>
            <li>نام دریافت کننده: [reciever-name]</li>
          </ul>
        </Alert>

        <!-- Message Type -->
        <Select
          v-model="form.type"
          label="نوع پیام"
          placeholder="انتخاب کنید"
          :options="messageTypeOptions"
          :error="errors.type"
          required
        />

        <!-- Message Content -->
        <div>
          <label class="block text-sm font-medium mb-2 text-text-primary">
            متن پیام
            <span class="text-error">*</span>
          </label>
          <Editor
            v-model="form.content"
            editorStyle="min-height: 200px"
            :class="{ 'border-error': errors.content }"
            :editorOptions="editorOptions"
          />
          <p v-if="errors.content" class="text-xs text-error flex items-center gap-1 mt-1.5">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
              <path
                fill-rule="evenodd"
                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                clip-rule="evenodd"
              />
            </svg>
            {{ errors.content }}
          </p>
        </div>
      </div>

      <template #footer>
        <Button variant="primary" :loading="saving" @click="handleCreate">
          ذخیره
        </Button>
        <Button variant="danger" @click="closeCreateModal">
          بستن
        </Button>
      </template>
    </Modal>

    <!-- View Modal -->
    <Modal
      v-model="showViewModal"
      title="مشاهده پیام"
      size="lg"
    >
      <div class="p-4 rounded-lg bg-bg-elevated border border-border">
        <div v-html="sanitizedViewingMessage" class="text-text-primary"></div>
      </div>

      <template #footer>
        <Button variant="danger" @click="showViewModal = false">
          بستن
        </Button>
      </template>
    </Modal>

    <!-- Edit Modal -->
    <Modal
      v-model="showEditModal"
      title="ویرایش پیام"
      size="xl"
      @close="resetEditForm"
    >
      <div class="space-y-6">
        <!-- Message Content -->
        <div>
          <label class="block text-sm font-medium mb-2 text-text-primary">
            متن پیام
            <span class="text-error">*</span>
          </label>
          <Editor
            v-model="editForm.content"
            editorStyle="min-height: 200px"
            :class="{ 'border-error': errors.content }"
            :editorOptions="editorOptions"
          />
          <p v-if="errors.content" class="text-xs text-error flex items-center gap-1 mt-1.5">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
              <path
                fill-rule="evenodd"
                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                clip-rule="evenodd"
              />
            </svg>
            {{ errors.content }}
          </p>
        </div>
      </div>

      <template #footer>
        <Button variant="primary" :loading="updating" @click="handleUpdate">
          ذخیره
        </Button>
        <Button variant="danger" @click="closeEditModal">
          بستن
        </Button>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import Editor from 'primevue/editor'
import apiClient from '../../utils/api'
import { Table, Modal, Button, Select, Alert, LoadingState, ErrorState } from '../../components/ui'
import { useToast } from '../../composables/useToast'
import { confirm } from '../../utils/notifications'
import TableActionIcon from '../../components/icons/TableActionIcon.vue'
import { sanitizeHtml } from '../../utils/sanitize'

const { showToast } = useToast()

const loading = ref(true)
const error = ref(null)
const messages = ref([])
const saving = ref(false)
const updating = ref(false)

// Modal states
const showCreateModal = ref(false)
const showViewModal = ref(false)
const showEditModal = ref(false)
const viewingMessage = ref(null)
const editingMessage = ref(null)

// Form data
const form = ref({
  type: '',
  content: ''
})

// Edit form data
const editForm = ref({
  content: ''
})

const errors = ref({})
const sanitizedViewingMessage = computed(() => sanitizeHtml(viewingMessage.value?.message || ''))

// Quill Editor Options for RTL
const editorOptions = {
  modules: {
    toolbar: [
      [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
      [{ 'font': [] }],
      [{ 'size': [] }],
      ['bold', 'italic', 'underline', 'strike'],
      [{ 'color': [] }, { 'background': [] }],
      [{ 'script': 'sub' }, { 'script': 'super' }],
      [{ 'list': 'ordered' }, { 'list': 'bullet' }],
      [{ 'indent': '-1' }, { 'indent': '+1' }],
      [{ 'direction': 'rtl' }],
      [{ 'align': [] }],
      ['link', 'image', 'video'],
      ['clean']
    ]
  },
  theme: 'snow',
  formats: ['direction']
}

// Message type options
const messageTypeOptions = [
  { value: 'requester_confirmation_message', label: 'پیام تایید درخواست کننده' },
  { value: 'reciever_message', label: 'پیام دریافت کننده درخواست' },
  { value: 'reciever_accept_message', label: 'پیام تایید پذیرش پیوستن به سلسله' },
  { value: 'requester_accept_message', label: 'پیام ارسالی به درخواست کننده مبنی بر پذیرش درخواست و پاداش دریافتی' }
]

// Table columns
const tableColumns = [
  {
    key: 'rowId',
    label: '#'
  },
  {
    key: 'type',
    label: 'نوع پیام',
    formatter: (value) => {
      const option = messageTypeOptions.find(opt => opt.value === value)
      return option ? option.label : value
    }
  },
  {
    key: 'actions',
    label: 'مدیریت'
  }
]

const openCreateModal = () => {
  showCreateModal.value = true
}

const closeCreateModal = () => {
  showCreateModal.value = false
  resetCreateForm()
}

const resetCreateForm = () => {
  form.value = {
    type: '',
    content: ''
  }
  errors.value = {}
}

const openViewModal = (message) => {
  viewingMessage.value = message
  showViewModal.value = true
}

const openEditModal = (message) => {
  editingMessage.value = message
  editForm.value.content = message.message || ''
  showEditModal.value = true
}

const closeEditModal = () => {
  showEditModal.value = false
  resetEditForm()
}

const resetEditForm = () => {
  editingMessage.value = null
  editForm.value.content = ''
  errors.value = {}
}

const validateForm = () => {
  errors.value = {}
  let isValid = true

  if (!form.value.type) {
    errors.value.type = 'نوع پیام الزامی است'
    isValid = false
  }

  if (!form.value.content || form.value.content.trim() === '') {
    errors.value.content = 'متن پیام الزامی است'
    isValid = false
  }

  return isValid
}

const handleCreate = async () => {
  if (!validateForm()) {
    return
  }

  try {
    saving.value = true
    errors.value = {}

    const response = await apiClient.post('/dynasty/messages', {
      type: form.value.type,
      content: form.value.content
    })

    if (response.data.success) {
      await fetchMessages()
      closeCreateModal()
      showToast('اطلاعات با موفقیت ثبت شد', 'success')
    } else {
      const errorMsg = response.data.message || 'خطا در ثبت اطلاعات'
      showToast(errorMsg, 'error')
      error.value = errorMsg
    }
  } catch (err) {
    console.error('Create message error:', err)
    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors
      // Show first error message
      const firstError = Object.values(err.response.data.errors)[0]
      if (Array.isArray(firstError)) {
        showToast(firstError[0], 'error')
      } else {
        showToast(firstError, 'error')
      }
    } else {
      const errorMsg = err.response?.data?.message || 'خطا در ثبت اطلاعات'
      showToast(errorMsg, 'error')
      error.value = errorMsg
    }
  } finally {
    saving.value = false
  }
}

const handleUpdate = async () => {
  if (!editingMessage.value) return

  if (!editForm.value.content || editForm.value.content.trim() === '') {
    errors.value.content = 'متن پیام الزامی است'
    return
  }

  try {
    updating.value = true
    errors.value = {}

    const response = await apiClient.put(`/dynasty/messages/${editingMessage.value.id}`, {
      content: editForm.value.content
    })

    if (response.data.success) {
      await fetchMessages()
      closeEditModal()
      showToast('اطلاعات با موفقیت به‌روزرسانی شد', 'success')
    } else {
      const errorMsg = response.data.message || 'خطا در به‌روزرسانی اطلاعات'
      showToast(errorMsg, 'error')
      error.value = errorMsg
    }
  } catch (err) {
    console.error('Update message error:', err)
    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors
      // Show first error message
      const firstError = Object.values(err.response.data.errors)[0]
      if (Array.isArray(firstError)) {
        showToast(firstError[0], 'error')
      } else {
        showToast(firstError, 'error')
      }
    } else {
      const errorMsg = err.response?.data?.message || 'خطا در به‌روزرسانی اطلاعات'
      showToast(errorMsg, 'error')
      error.value = errorMsg
    }
  } finally {
    updating.value = false
  }
}

const handleDelete = async (message) => {
  try {
    const result = await confirm(
      'آیا می‌خواهید این پیام را حذف کنید؟',
      'حذف پیام',
      {
        confirmText: 'بله، حذف شود',
        cancelText: 'لغو'
      }
    )

    if (!result.isConfirmed) {
      return
    }

    const id = message.id

    const response = await apiClient.delete(`/dynasty/messages/${id}`)

    if (response.data.success) {
      await fetchMessages()
      showToast('پیام با موفقیت حذف شد', 'success')
    } else {
      const errorMsg = response.data.message || 'خطا در حذف پیام'
      showToast(errorMsg, 'error')
      error.value = errorMsg
    }
  } catch (err) {
    console.error('Delete message error:', err)
    const errorMsg = err.response?.data?.message || 'خطا در حذف پیام'
    showToast(errorMsg, 'error')
    error.value = errorMsg
  }
}

const fetchMessages = async () => {
  try {
    loading.value = true
    error.value = null

    const response = await apiClient.get('/dynasty/messages')

    if (response.data.success) {
      messages.value = response.data.data.map((msg, index) => ({
        ...msg,
        rowId: index + 1
      }))
    } else {
      error.value = 'خطا در دریافت اطلاعات پیام‌ها'
      messages.value = []
    }
  } catch (err) {
    console.error('Fetch messages error:', err)

    if (err.response && (err.response.status === 401 || err.response.status === 403)) {
      messages.value = []
      loading.value = false
      return
    }

    error.value = err.response?.data?.message || 'خطا در بارگذاری اطلاعات'
    messages.value = []
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchMessages()
})
</script>

<style scoped>
.text-text-primary {
  color: var(--theme-text-primary, #F8FAFC);
}

.text-text-secondary {
  color: var(--theme-text-secondary, #CBD5E1);
}

.bg-bg-elevated {
  background-color: var(--theme-bg-elevated, #1E293B);
}

.border-border {
  border-color: var(--theme-border, rgba(255, 255, 255, 0.1));
}

.text-error {
  color: var(--color-error, #EF4444);
}
</style>

