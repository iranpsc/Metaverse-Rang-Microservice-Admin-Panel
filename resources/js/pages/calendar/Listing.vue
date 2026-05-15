<template>
  <div class="p-6 space-y-6">
    <!-- Page Header -->
    <div class="mb-8" dir="rtl">
      <h1 class="text-3xl font-bold text-[var(--theme-text-primary)] mb-2">مدیریت وقایع</h1>
      <p class="text-[var(--theme-text-secondary)]">ایجاد، مدیریت و مشاهده وقایع تقویم متاورس</p>
    </div>

    <!-- Actions Row -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4" dir="rtl">
      <SearchBox
        v-model="searchTerm"
        placeholder="جستجو بر اساس عنوان یا متن..."
        :debounce-ms="500"
        container-class="w-full md:max-w-md"
        @search="handleSearch"
        @clear="handleClear"
      />

      <Button variant="primary" rounded="full" @click="openCreateModal">
        ایجاد وقعه
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

    <!-- Events Table -->
    <Table
      v-else
      :columns="tableColumns"
      :data="events"
      :pagination="pagination"
      empty-state-message="هیچ وقعهای برای نمایش وجود ندارد"
    >
      <template #cell-title="{ value }">
        <span class="font-medium text-[var(--theme-text-primary)]">{{ value || '-' }}</span>
      </template>

      <template #cell-content="{ value }">
        <span class="text-[var(--theme-text-secondary)]">{{ truncateText(value) }}</span>
      </template>

      <template #cell-color="{ row }">
        <div class="flex items-center gap-2">
          <span
            class="inline-flex h-4 w-4 rounded-full border border-white/20 shadow-sm"
            :style="{ backgroundColor: row.color || '#000000' }"
          />
          <span>{{ row.color || '-' }}</span>
        </div>
      </template>

      <template #cell-start_date="{ row }">
        <div class="flex flex-col">
          <span>{{ row.start_date || '-' }}</span>
          <span v-if="row.start_time" class="text-xs text-[var(--theme-text-secondary)]">{{ row.start_time }}</span>
        </div>
      </template>

      <template #cell-end_date="{ row }">
        <div class="flex flex-col">
          <span>{{ row.end_date || '-' }}</span>
          <span v-if="row.end_time" class="text-xs text-[var(--theme-text-secondary)]">{{ row.end_time }}</span>
        </div>
      </template>

      <template #cell-created_at_jalali="{ value }">
        <span>{{ value || '-' }}</span>
      </template>

      <template #cell-image_url="{ value }">
        <a
          v-if="value"
          :href="value"
          target="_blank"
          rel="noopener noreferrer"
          class="inline-flex items-center justify-center text-secondary-300 hover:text-secondary-200"
          title="مشاهده تصویر"
          aria-label="مشاهده تصویر"
        >
          <TableActionIcon name="image" icon-class="w-4 h-4 shrink-0" />
        </a>
        <span v-else>-</span>
      </template>

      <template #cell-status="{ row }">
        <Badge :variant="row.status === 'در حال برگزاری' ? 'success' : 'warning'">
          {{ row.status || '-' }}
        </Badge>
      </template>

      <template #cell-actions="{ row }">
        <div class="flex items-center gap-2" dir="rtl">
          <Button
            variant="secondary"
            size="sm"
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
            variant="danger"
            size="sm"
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

    <!-- Pagination -->
    <Pagination
      v-if="pagination && pagination.total > 0"
      :pagination="pagination"
      :disabled="loading"
      @page-change="goToPage"
    />

    <!-- Create Event Modal -->
    <Modal
      v-model="showCreateModal"
      title="ایجاد وقعه"
      size="xl"
      @close="handleCreateModalClose"
    >
      <div class="space-y-5" dir="rtl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <Input
            v-model="createForm.title"
            label="عنوان"
            :error="createErrors.title"
            required
          />

          <Input
            v-model="createForm.btn_name"
            label="نام دکمه ورود به واقعه"
            :error="createErrors.btn_name"
          />

          <Input
            v-model="createForm.btn_link"
            label="لینک دکمه ورود به واقعه"
            :error="createErrors.btn_link"
          />

          <Input
            v-model="createForm.color"
            type="color"
            label="رنگ"
            :error="createErrors.color"
          />
        </div>

        <RichTextEditor
          v-model="createForm.content"
          label="متن"
          placeholder="متن وقعه را وارد کنید"
          :error="createErrors.content"
          required
        />

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <PersianDatePicker
            v-model="createForm.start_date"
            label="تاریخ شروع"
            :error="createErrors.start_date"
            required
          />

          <PersianDatePicker
            v-model="createForm.end_date"
            label="تاریخ پایان"
            :error="createErrors.end_date"
            required
          />

          <TimePicker
            v-model="createForm.start_time"
            label="ساعت شروع"
            :error="createErrors.start_time"
            required
          />

          <TimePicker
            v-model="createForm.end_time"
            label="ساعت پایان"
            :error="createErrors.end_time"
            required
          />
        </div>

        <FileInput
          v-model="createForm.image"
          label="تصویر"
          accept="image/png,image/jpg,image/jpeg"
          :error="createErrors.image"
          required
        />
      </div>

      <template #footer>
        <div class="flex gap-3" dir="rtl">
          <Button
            variant="primary"
            class="w-full"
            :loading="saving"
            @click="handleCreateSubmit"
          >
            ثبت
          </Button>
          <Button
            variant="danger"
            class="w-full"
            @click="handleCreateModalClose"
          >
            انصراف
          </Button>
        </div>
      </template>
    </Modal>

    <!-- Edit Event Modal -->
    <Modal
      v-if="selectedEvent"
      v-model="showEditModal"
      title="ویرایش وقعه"
      size="xl"
      @close="handleEditModalClose"
    >
      <div class="space-y-5" dir="rtl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <Input
            v-model="editForm.title"
            label="عنوان"
            :error="editErrors.title"
            required
          />

          <Input
            v-model="editForm.btn_name"
            label="نام دکمه ورود به واقعه"
            :error="editErrors.btn_name"
          />

          <Input
            v-model="editForm.btn_link"
            label="لینک دکمه ورود به واقعه"
            :error="editErrors.btn_link"
          />

          <Input
            v-model="editForm.color"
            type="color"
            label="رنگ"
            :error="editErrors.color"
          />
        </div>

        <RichTextEditor
          v-model="editForm.content"
          label="متن"
          placeholder="متن وقعه را وارد کنید"
          :error="editErrors.content"
          required
        />

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <PersianDatePicker
            v-model="editForm.start_date"
            label="تاریخ شروع"
            :error="editErrors.start_date"
            required
          />

          <PersianDatePicker
            v-model="editForm.end_date"
            label="تاریخ پایان"
            :error="editErrors.end_date"
          />

          <TimePicker
            v-model="editForm.start_time"
            label="ساعت شروع"
            :error="editErrors.start_time"
            required
          />

          <TimePicker
            v-model="editForm.end_time"
            label="ساعت پایان"
            :error="editErrors.end_time"
          />
        </div>

        <div class="space-y-2">
          <FileInput
            v-model="editForm.image"
            label="تصویر"
            accept="image/png,image/jpg,image/jpeg"
            :error="editErrors.image"
          />

          <div v-if="selectedEvent?.image_url" class="text-xs text-[var(--theme-text-secondary)]">
            تصویر فعلی:
            <a
              :href="selectedEvent.image_url"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex items-center gap-1 underline decoration-dotted text-secondary-300 hover:text-secondary-200"
              title="مشاهده تصویر"
              aria-label="مشاهده تصویر"
            >
              <TableActionIcon name="image" icon-class="w-4 h-4 shrink-0" />
            </a>
          </div>
        </div>
      </div>

      <template #footer>
        <div class="flex gap-3" dir="rtl">
          <Button
            variant="primary"
            class="w-full"
            :loading="updating"
            @click="handleEditSubmit"
          >
            ثبت
          </Button>
          <Button
            variant="danger"
            class="w-full"
            @click="handleEditModalClose"
          >
            انصراف
          </Button>
        </div>
      </template>
    </Modal>

    <PhoneVerificationModal :phone-verification="phoneVerification" />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import apiClient from '../../utils/api'
import {
  Table,
  Pagination,
  SearchBox,
  LoadingState,
  ErrorState,
  Button,
  Modal,
  Input,
  FileInput,
  Badge,
  TimePicker
} from '../../components/ui'
import RichTextEditor from '../../components/ui/RichTextEditor.vue'
import PersianDatePicker from '../../components/ui/PersianDatePicker.vue'
import TableActionIcon from '../../components/icons/TableActionIcon.vue'
import PhoneVerificationModal from '../../components/PhoneVerificationModal.vue'
import { useToast } from '../../composables/useToast'
import { usePhoneVerification } from '../../composables/usePhoneVerification'

const { showToast } = useToast()
const phoneVerification = usePhoneVerification()

const loading = ref(true)
const saving = ref(false)
const updating = ref(false)
const error = ref(null)
const events = ref([])
const pagination = ref(null)
const searchTerm = ref('')
const currentPage = ref(1)

const showCreateModal = ref(false)
const showEditModal = ref(false)
const selectedEvent = ref(null)

const createForm = reactive(defaultEventForm())
const editForm = reactive(defaultEventForm())
const createErrors = reactive({})
const editErrors = reactive({})

const tableColumns = [
  { key: 'title', label: 'عنوان' },
  { key: 'content', label: 'متن', textSecondary: true },
  { key: 'color', label: 'رنگ' },
  { key: 'start_date', label: 'تاریخ شروع' },
  { key: 'end_date', label: 'تاریخ پایان' },
  { key: 'created_at_jalali', label: 'تاریخ ثبت', textSecondary: true },
  { key: 'image_url', label: 'تصویر' },
  { key: 'views_count', label: 'بازدید' },
  { key: 'likes_count', label: 'لایک' },
  { key: 'dislikes_count', label: 'دیسلایک' },
  { key: 'status', label: 'وضعیت' },
  { key: 'actions', label: 'اقدامات' }
]

function defaultEventForm() {
  return {
    title: '',
    content: '',
    color: '#7C3AED',
    start_date: '',
    end_date: '',
    start_time: '',
    end_time: '',
    btn_name: '',
    btn_link: '',
    image: null
  }
}

const stripHtml = (value) => {
  if (!value) return ''
  return value
    .replace(/<[^>]*>/g, ' ')
    .replace(/&nbsp;/g, ' ')
    .replace(/&amp;/g, '&')
    .replace(/\s+/g, ' ')
    .trim()
}

const hasRichTextContent = (value) => stripHtml(value).length > 0

const persianDigitMap = {
  '۰': '0',
  '۱': '1',
  '۲': '2',
  '۳': '3',
  '۴': '4',
  '۵': '5',
  '۶': '6',
  '۷': '7',
  '۸': '8',
  '۹': '9'
}

const toEnglishDigits = (input) => {
  if (!input) return ''
  return input.replace(/[۰۱۲۳۴۵۶۷۸۹]/g, (d) => persianDigitMap[d] || d)
}

const isoToJalaliDate = (isoString) => {
  if (!isoString) return ''
  const date = new Date(isoString)
  if (Number.isNaN(date.getTime())) {
    return ''
  }

  const formatter = new Intl.DateTimeFormat('fa-IR-u-ca-persian', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit'
  })

  const parts = formatter.formatToParts(date)
  const year = toEnglishDigits(parts.find((p) => p.type === 'year')?.value || '')
  const month = toEnglishDigits(parts.find((p) => p.type === 'month')?.value || '')
  const day = toEnglishDigits(parts.find((p) => p.type === 'day')?.value || '')

  if (!year || !month || !day) {
    return ''
  }

  return `${year.padStart(4, '0')}/${month.padStart(2, '0')}/${day.padStart(2, '0')}`
}

const truncateText = (text, length = 60) => {
  const plain = stripHtml(text)
  if (!plain) return '-'
  return plain.length > length ? `${plain.slice(0, length)}…` : plain
}

const resetErrors = (target) => {
  Object.keys(target).forEach((key) => delete target[key])
}

const assignValidationErrors = (target, source) => {
  resetErrors(target)
  Object.entries(source || {}).forEach(([key, value]) => {
    if (Array.isArray(value)) {
      target[key] = value[0]
    } else if (typeof value === 'string') {
      target[key] = value
    }
  })
}

const fetchEvents = async () => {
  try {
    loading.value = true
    error.value = null

    const params = {
      page: currentPage.value,
      per_page: 10
    }

    if (searchTerm.value) {
      params.search = searchTerm.value
    }

    const response = await apiClient.get('/calendars', { params })

    if (response.data.success) {
      events.value = response.data.data.events || []
      pagination.value = response.data.data.pagination || null
    } else {
      events.value = []
      pagination.value = null
      error.value = response.data.message || 'خطا در دریافت اطلاعات وقایع'
    }
  } catch (err) {
    console.error('Calendar fetch error:', err)
    events.value = []
    pagination.value = null
    error.value = err.response?.data?.message || 'خطا در بارگذاری لیست وقایع'
  } finally {
    loading.value = false
  }
}

const goToPage = (page) => {
  if (!pagination.value) return
  if (page >= 1 && page <= pagination.value.last_page) {
    currentPage.value = page
    fetchEvents()
  }
}

const handleSearch = () => {
  currentPage.value = 1
  fetchEvents()
}

const handleClear = () => {
  searchTerm.value = ''
  currentPage.value = 1
  fetchEvents()
}

const openCreateModal = () => {
  resetCreateForm()
  showCreateModal.value = true
}

const handleCreateModalClose = () => {
  showCreateModal.value = false
  resetCreateForm()
}

const resetCreateForm = () => {
  Object.assign(createForm, defaultEventForm())
  resetErrors(createErrors)
}

const validateCreateForm = () => {
  resetErrors(createErrors)

  if (!createForm.title || !createForm.title.trim()) {
    createErrors.title = 'عنوان الزامی است'
  }

  if (!hasRichTextContent(createForm.content)) {
    createErrors.content = 'متن وقعه الزامی است'
  }

  if (!createForm.start_date) {
    createErrors.start_date = 'تاریخ شروع الزامی است'
  }

  if (!createForm.end_date) {
    createErrors.end_date = 'تاریخ پایان الزامی است'
  }

  if (!createForm.start_time) {
    createErrors.start_time = 'ساعت شروع الزامی است'
  }

  if (!createForm.end_time) {
    createErrors.end_time = 'ساعت پایان الزامی است'
  }

  if (!createForm.image) {
    createErrors.image = 'انتخاب تصویر الزامی است'
  }

  return Object.keys(createErrors).length === 0
}

const handleCreateSubmit = async () => {
  if (!validateCreateForm()) {
    return
  }

  const formData = new FormData()
  formData.append('title', createForm.title)
  formData.append('content', createForm.content)
  formData.append('color', createForm.color || '')
  formData.append('start_date', createForm.start_date)
  formData.append('end_date', createForm.end_date)
  formData.append('start_time', createForm.start_time)
  formData.append('end_time', createForm.end_time)
  formData.append('btn_name', createForm.btn_name || '')
  formData.append('btn_link', createForm.btn_link || '')
  formData.append('image', createForm.image)

  try {
    saving.value = true
    const response = await apiClient.post('/calendars', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    if (response.data.success) {
      showToast(response.data.message || 'وقعه با موفقیت ثبت شد', 'success')
      handleCreateModalClose()
      currentPage.value = 1
      fetchEvents()
    } else {
      showToast(response.data.message || 'خطا در ثبت وقعه', 'error')
    }
  } catch (err) {
    console.error('Calendar create error:', err)

    if (err.response?.status === 422 && err.response?.data?.errors) {
      assignValidationErrors(createErrors, err.response.data.errors)
    } else {
      showToast(err.response?.data?.message || 'خطا در ثبت وقعه', 'error')
    }
  } finally {
    saving.value = false
  }
}

const openEditModal = (event) => {
  selectedEvent.value = event
  const startDate = event.start_date && /^\d{4}\/\d{2}\/\d{2}$/.test(event.start_date)
    ? event.start_date
    : isoToJalaliDate(event.starts_at)

  const endDate = event.end_date && /^\d{4}\/\d{2}\/\d{2}$/.test(event.end_date)
    ? event.end_date
    : isoToJalaliDate(event.ends_at)

  Object.assign(editForm, {
    title: event.title || '',
    content: event.content || '',
    color: event.color || '#7C3AED',
    start_date: startDate || '',
    end_date: endDate || '',
    start_time: event.start_time || '',
    end_time: event.end_time || '',
    btn_name: event.btn_name || '',
    btn_link: event.btn_link || '',
    image: null
  })
  resetErrors(editErrors)
  showEditModal.value = true
}

const handleEditModalClose = () => {
  showEditModal.value = false
  resetEditForm()
}

const resetEditForm = () => {
  Object.assign(editForm, defaultEventForm())
  resetErrors(editErrors)
  selectedEvent.value = null
}

const validateEditForm = () => {
  resetErrors(editErrors)

  if (!editForm.title || !editForm.title.trim()) {
    editErrors.title = 'عنوان الزامی است'
  }

  if (!hasRichTextContent(editForm.content)) {
    editErrors.content = 'متن وقعه الزامی است'
  }

  if (!editForm.start_date) {
    editErrors.start_date = 'تاریخ شروع الزامی است'
  }

  if (!editForm.start_time) {
    editErrors.start_time = 'ساعت شروع الزامی است'
  }

  return Object.keys(editErrors).length === 0
}

const handleEditSubmit = async () => {
  if (!selectedEvent.value) return
  if (!validateEditForm()) {
    return
  }

  const formData = new FormData()
  formData.append('title', editForm.title)
  formData.append('content', editForm.content)
  formData.append('color', editForm.color || '')
  formData.append('start_date', editForm.start_date)
  formData.append('start_time', editForm.start_time)
  formData.append('end_date', editForm.end_date || '')
  formData.append('end_time', editForm.end_time || '')
  formData.append('btn_name', editForm.btn_name || '')
  formData.append('btn_link', editForm.btn_link || '')
  formData.append('_method', 'PUT')

  if (editForm.image instanceof File) {
    formData.append('image', editForm.image)
  }

  try {
    updating.value = true
    const response = await apiClient.post(`/calendars/${selectedEvent.value.id}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    if (response.data.success) {
      showToast(response.data.message || 'وقعه با موفقیت بروزرسانی شد', 'success')
      handleEditModalClose()
      fetchEvents()
    } else {
      showToast(response.data.message || 'خطا در بروزرسانی وقعه', 'error')
    }
  } catch (err) {
    console.error('Calendar update error:', err)

    if (err.response?.status === 422 && err.response?.data?.errors) {
      assignValidationErrors(editErrors, err.response.data.errors)
    } else {
      showToast(err.response?.data?.message || 'خطا در بروزرسانی وقعه', 'error')
    }
  } finally {
    updating.value = false
  }
}

const handleDelete = async (event) => {
  await phoneVerification.confirmThenVerify(
    {
      message: `آیا از حذف وقعه «${event.title}» مطمئن هستید؟`,
      title: 'حذف وقعه',
      confirmText: 'بله، حذف شود',
      cancelText: 'انصراف'
    },
    async (payload) => {
      try {
        const response = await apiClient.delete(`/calendars/${event.id}`, { data: payload })

        if (response.data.success) {
          showToast(response.data.message || 'وقعه با موفقیت حذف شد', 'success')
          phoneVerification.resetVerificationState()

          if (events.value.length === 1 && currentPage.value > 1) {
            currentPage.value -= 1
          }

          fetchEvents()
        } else {
          showToast(response.data.message || 'خطا در حذف وقعه', 'error')
        }
      } catch (err) {
        console.error('Calendar delete error:', err)

        if (await phoneVerification.handleApiVerificationError(err)) {
          return
        }

        showToast(err.response?.data?.message || 'خطا در حذف وقعه', 'error')
      }
    }
  )
}

watch(
  () => createForm.image,
  (file) => {
    if (file) {
      delete createErrors.image
    }
  }
)

watch(
  () => createForm.content,
  (value) => {
    if (hasRichTextContent(value)) {
      delete createErrors.content
    }
  }
)

watch(
  () => editForm.image,
  (file) => {
    if (file) {
      delete editErrors.image
    }
  }
)

watch(
  () => editForm.content,
  (value) => {
    if (hasRichTextContent(value)) {
      delete editErrors.content
    }
  }
)

onMounted(() => {
  fetchEvents()
})
</script>

<style scoped>
.text-error {
  color: var(--color-error, #EF4444);
}
</style>


