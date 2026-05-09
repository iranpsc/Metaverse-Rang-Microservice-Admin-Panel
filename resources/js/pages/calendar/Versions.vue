<template>
  <div class="p-6 space-y-6">
    <!-- Page Header -->
    <div class="mb-8 space-y-2">
      <h1 class="text-3xl font-bold text-[var(--theme-text-primary)]">ورژن‌ها</h1>
      <p class="text-[var(--theme-text-secondary)]">
        مدیریت نسخه‌های منتشر شده و ثبت نسخه‌های جدید سیستم
      </p>
    </div>

    <!-- Actions -->
    <div class="flex justify-end">
      <Button variant="primary" rounded="full" @click="openCreateModal">
        تعریف ورژن جدید
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

    <!-- Versions Table -->
    <Table
      v-else
      :columns="tableColumns"
      :data="versions"
      :pagination="pagination"
      empty-state-message="ورژنی ثبت نشده است"
    >
      <template #cell-content_excerpt="{ value }">
        <span class="text-[var(--theme-text-secondary)]">{{ value || '-' }}</span>
      </template>

      <template #cell-start_date_display="{ value }">
        <span class="text-[var(--theme-text-primary)]">{{ value || '-' }}</span>
      </template>

      <template #cell-actions="{ row }">
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
      </template>
    </Table>

    <!-- Pagination -->
    <Pagination
      v-if="pagination && pagination.total > 0"
      :pagination="pagination"
      :disabled="loading"
      @page-change="goToPage"
    />

    <!-- Create Version Modal -->
    <Modal
      v-model="showCreateModal"
      title="تعریف ورژن"
      size="lg"
      @close="handleCreateModalClose"
    >
      <div class="space-y-5" dir="rtl">
        <Input
          v-model="createForm.version_title"
          label="شناسه نسخه"
          :error="createErrors.version_title"
          required
        />

        <Input
          v-model="createForm.title"
          label="عنوان"
          :error="createErrors.title"
          required
        />

        <RichTextEditor
          v-model="createForm.content"
          label="متن"
          placeholder="محتوای نسخه را وارد کنید"
          :error="createErrors.content"
          required
        />

        <PersianDatePicker
          v-model="createForm.starts_at"
          label="تاریخ شروع"
          :error="createErrors.starts_at"
          required
        />
      </div>

      <template #footer>
        <div class="flex flex-col md:flex-row gap-3" dir="rtl">
          <Button
            variant="primary"
            class="w-full"
            :loading="saving"
            @click="handleCreateSubmit"
          >
            ثبت
          </Button>
          <Button variant="danger" class="w-full" @click="handleCreateModalClose">
            انصراف
          </Button>
        </div>
      </template>
    </Modal>

  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import apiClient from '../../utils/api'
import {
  Button,
  Table,
  Pagination,
  Modal,
  Input,
  LoadingState,
  ErrorState
} from '../../components/ui'
import RichTextEditor from '../../components/ui/RichTextEditor.vue'
import PersianDatePicker from '../../components/ui/PersianDatePicker.vue'
import { useToast } from '../../composables/useToast'
import { confirm } from '../../utils/notifications'
import TableActionIcon from '../../components/icons/TableActionIcon.vue'
import { formatPersianDate } from '../../utils/dateFormatter'

const { showToast } = useToast()

const loading = ref(true)
const error = ref(null)
const versions = ref([])
const pagination = ref(null)
const currentPage = ref(1)
const perPage = 10

const showCreateModal = ref(false)
const saving = ref(false)

const createForm = reactive({
  version_title: '',
  title: '',
  content: '',
  starts_at: ''
})

const createErrors = reactive({
  version_title: '',
  title: '',
  content: '',
  starts_at: ''
})

const tableColumns = [
  {
    key: 'title',
    label: 'عنوان'
  },
  {
    key: 'version_title',
    label: 'شناسه نسخه'
  },
  {
    key: 'content_excerpt',
    label: 'خلاصه متن',
    textSecondary: true,
    defaultValue: '-'
  },
  {
    key: 'start_date_display',
    label: 'تاریخ شروع',
    textSecondary: true,
    defaultValue: '-'
  },
  {
    key: 'views_count',
    label: 'تعداد بازدید',
    textSecondary: true,
    defaultValue: 0
  },
  {
    key: 'actions',
    label: 'اقدامات'
  }
]

const resetCreateForm = () => {
  createForm.version_title = ''
  createForm.title = ''
  createForm.content = ''
  createForm.starts_at = ''
}

const resetCreateErrors = () => {
  createErrors.version_title = ''
  createErrors.title = ''
  createErrors.content = ''
  createErrors.starts_at = ''
}

const extractExcerpt = (html, limit = 80) => {
  if (!html) return ''
  const text = html
    .replace(/<[^>]*>/g, ' ')
    .replace(/&nbsp;/gi, ' ')
    .replace(/\s+/g, ' ')
    .trim()

  return text.length > limit ? `${text.slice(0, limit)}…` : text
}

const hasRichTextContent = (value) => {
  if (!value) return false
  const stripped = value
    .replace(/<[^>]*>/g, '')
    .replace(/&nbsp;/gi, ' ')
    .replace(/\s+/g, '')
    .trim()
  return stripped.length > 0
}

const assignValidationErrors = (target, errors) => {
  Object.keys(target).forEach((key) => {
    target[key] = errors?.[key]?.[0] || ''
  })
}

const openCreateModal = () => {
  resetCreateErrors()
  resetCreateForm()
  showCreateModal.value = true
}

const handleCreateModalClose = () => {
  showCreateModal.value = false
  resetCreateErrors()
  resetCreateForm()
}

const validateCreateForm = () => {
  resetCreateErrors()

  if (!createForm.version_title || !createForm.version_title.trim()) {
    createErrors.version_title = 'شناسه نسخه الزامی است'
  }

  if (!createForm.title || !createForm.title.trim()) {
    createErrors.title = 'عنوان الزامی است'
  }

  if (!hasRichTextContent(createForm.content)) {
    createErrors.content = 'متن ورژن الزامی است'
  }

  if (!createForm.starts_at) {
    createErrors.starts_at = 'تاریخ شروع الزامی است'
  }

  return !createErrors.version_title && !createErrors.title && !createErrors.content && !createErrors.starts_at
}

const handleCreateSubmit = async () => {
  if (!validateCreateForm()) {
    return
  }

  try {
    saving.value = true

    const payload = {
      version_title: createForm.version_title.trim(),
      title: createForm.title.trim(),
      content: createForm.content,
      starts_at: createForm.starts_at
    }

    const response = await apiClient.post('/versions', payload)

    if (response.data.success) {
      showToast(response.data.message || 'ورژن جدید با موفقیت ایجاد شد', 'success')
      handleCreateModalClose()
      currentPage.value = 1
      await fetchVersions()
    } else {
      showToast(response.data.message || 'خطا در ثبت ورژن', 'error')
    }
  } catch (err) {
    console.error('Version create error:', err)

    if (err.response?.status === 422 && err.response?.data?.errors) {
      assignValidationErrors(createErrors, err.response.data.errors)
    } else {
      showToast(err.response?.data?.message || 'خطا در ثبت ورژن', 'error')
    }
  } finally {
    saving.value = false
  }
}

const handleDelete = async (version) => {
  try {
    const result = await confirm('آیا می‌خواهید این ورژن را حذف کنید؟', 'حذف ورژن', {
      confirmText: 'بله، حذف شود',
      cancelText: 'لغو'
    })

    if (!result.isConfirmed) {
      return
    }

    const response = await apiClient.delete(`/versions/${version.id}`)

    if (response.data.success) {
      showToast(response.data.message || 'ورژن با موفقیت حذف شد', 'success')
      if (versions.value.length === 1 && currentPage.value > 1) {
        currentPage.value -= 1
      }
      await fetchVersions()
    } else {
      showToast(response.data.message || 'خطا در حذف ورژن', 'error')
    }
  } catch (err) {
    console.error('Version delete error:', err)

    if (err.response && (err.response.status === 401 || err.response.status === 403)) {
      return
    }

    showToast(err.response?.data?.message || 'خطا در حذف ورژن', 'error')
  }
}

const fetchVersions = async () => {
  try {
    loading.value = true
    error.value = null

    const response = await apiClient.get('/versions', {
      params: {
        page: currentPage.value,
        per_page: perPage
      }
    })

    if (response.data.success) {
      const rawVersions = response.data.data?.versions || []

      versions.value = rawVersions.map((item) => {
        const startDate = item.start_date || formatPersianDate(item.starts_at)

        return {
          ...item,
          content_excerpt: extractExcerpt(item.content),
          start_date_display: startDate
        }
      })

      pagination.value = response.data.data?.pagination || null
    } else {
      error.value = response.data.message || 'خطا در دریافت لیست ورژن‌ها'
      versions.value = []
      pagination.value = null
    }
  } catch (err) {
    console.error('Versions fetch error:', err)

    if (err.response && (err.response.status === 401 || err.response.status === 403)) {
      versions.value = []
      pagination.value = null
      loading.value = false
      return
    }

    error.value = err.response?.data?.message || 'خطا در بارگذاری اطلاعات'
    versions.value = []
    pagination.value = null
  } finally {
    loading.value = false
  }
}

const goToPage = (page) => {
  if (page < 1) return
  if (pagination.value && page > pagination.value.last_page) return

  currentPage.value = page
  fetchVersions()
}

onMounted(() => {
  fetchVersions()
})
</script>


