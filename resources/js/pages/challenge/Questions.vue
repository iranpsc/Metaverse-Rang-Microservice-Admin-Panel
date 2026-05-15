<template>
  <div class="p-6 space-y-6">
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-[var(--theme-text-primary)] mb-2">چالش پرسش و پاسخ</h1>
      <p class="text-[var(--theme-text-secondary)]">مدیریت سوالات چالش و مدیریت پاسخ‌ها</p>
    </div>

    <!-- Actions -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <SearchBox
        v-model="searchTerm"
        placeholder="جستجو بر اساس عنوان یا کد..."
        :debounce-ms="400"
        container-class="w-full md:max-w-md"
        @search="handleSearch"
        @clear="handleClear"
      />

      <Button
        variant="primary"
        rounded="full"
        class="w-full md:w-auto"
        @click="openImportModal"
      >
        درون‌ریزی سوالات
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

    <!-- Questions Table -->
    <div v-else class="space-y-4" dir="rtl">
      <Table
        :columns="tableColumns"
        :data="questions"
        :pagination="pagination"
        empty-state-message="سوالی ثبت نشده است"
      >
        <template #cell-created_at="{ value }">
          <div class="flex flex-col">
            <span>{{ formatDate(value) }}</span>
            <span class="text-[var(--theme-text-secondary)] text-xs">{{ formatTime(value) }}</span>
          </div>
        </template>

        <template #cell-answers="{ row }">
          <Button
            size="sm"
            variant="glass"
            rounded="full"
            class="!p-2 !gap-0 min-w-[2.25rem]"
            title="نمایش پاسخ‌ها"
            aria-label="نمایش پاسخ‌ها"
            @click="openAnswersModal(row)"
          >
            <template #icon-left>
              <TableActionIcon name="view" />
            </template>
          </Button>
        </template>

        <template #cell-actions="{ row }">
          <Button
            size="sm"
            variant="danger"
            rounded="full"
            class="!p-2 !gap-0 min-w-[2.25rem]"
            title="حذف"
            aria-label="حذف"
            :loading="deletingId === row.id"
            @click="handleDelete(row)"
          >
            <template #icon-left>
              <TableActionIcon name="delete" />
            </template>
          </Button>
        </template>
      </Table>

      <Pagination
        v-if="pagination && pagination.total > 0"
        :pagination="pagination"
        :disabled="loading"
        @page-change="goToPage"
      />
    </div>

    <!-- Answers Modal -->
    <Modal
      :model-value="answersModalOpen"
      @update:model-value="closeAnswersModal"
      :title="answersModalTitle"
      size="lg"
    >
      <div v-if="selectedQuestion" class="space-y-4" dir="rtl">
        <Table
          :columns="answersColumns"
          :data="selectedQuestion.answers || []"
          :show-row-number="true"
          empty-state-message="پاسخی ثبت نشده است"
        >
          <template #cell-is_correct="{ value }">
            <span :class="value ? 'text-success-400' : 'text-[var(--theme-text-secondary)]'">
              {{ value ? 'بله' : 'خیر' }}
            </span>
          </template>

          <template #cell-created_at="{ value }">
            <div class="flex flex-col">
              <span>{{ formatDate(value) }}</span>
              <span class="text-[var(--theme-text-secondary)] text-xs">{{ formatTime(value) }}</span>
            </div>
          </template>
        </Table>
      </div>
      <div v-else class="py-8 text-center text-[var(--theme-text-secondary)]">
        سوالی برای نمایش انتخاب نشده است
      </div>

      <template #footer>
        <div class="flex justify-end" dir="rtl">
          <Button variant="danger" @click="closeAnswersModal">
            بستن
          </Button>
        </div>
      </template>
    </Modal>

    <!-- Import Modal -->
    <Modal
      :model-value="importModalOpen"
      @update:model-value="closeImportModal"
      title="درون‌ریزی سوالات"
      size="md"
    >
      <div class="space-y-5" dir="rtl">
        <FileInput
          v-model="importFile"
          label="فایل سوالات"
          placeholder="فایل اکسل یا CSV را انتخاب کنید"
          accept=".xlsx,.csv"
          :error="importError"
          helper-text="فرمت‌های مجاز: xlsx، csv"
        />

        <p class="text-xs text-[var(--theme-text-secondary)] leading-6">
          پس از انتخاب فایل، داده‌ها برای پردازش در صف قرار می‌گیرند. این عملیات ممکن است چند دقیقه طول بکشد.
        </p>
      </div>

      <template #footer>
        <div class="flex justify-end gap-3" dir="rtl">
          <Button
            variant="primary"
            :loading="importing"
            @click="submitImport"
          >
            ثبت درون‌ریزی
          </Button>
          <Button
            variant="danger"
            :disabled="importing"
            @click="closeImportModal"
          >
            بستن
          </Button>
        </div>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import apiClient from '../../utils/api'
import { Button, ErrorState, FileInput, LoadingState, Modal, Pagination, SearchBox, Table } from '../../components/ui'
import { useToast } from '../../composables/useToast'
import { confirm } from '../../utils/notifications'
import TableActionIcon from '../../components/icons/TableActionIcon.vue'

const { showToast } = useToast()

const loading = ref(true)
const error = ref(null)
const questions = ref([])
const pagination = ref(null)
const currentPage = ref(1)
const searchTerm = ref('')

const answersModalOpen = ref(false)
const selectedQuestion = ref(null)

const importModalOpen = ref(false)
const importFile = ref(null)
const importing = ref(false)
const importError = ref('')

const deletingId = ref(null)

const tableColumns = [
  {
    key: 'title',
    label: 'عنوان سوال'
  },
  {
    key: 'code',
    label: 'کد'
  },
  {
    key: 'created_at',
    label: 'تاریخ ایجاد',
    textSecondary: true
  },
  {
    key: 'answers',
    label: 'پاسخ‌ها'
  },
  {
    key: 'actions',
    label: 'عملیات'
  }
]

const answersColumns = [
  {
    key: 'title',
    label: 'عنوان پاسخ'
  },
  {
    key: 'is_correct',
    label: 'پاسخ صحیح',
    textSecondary: true
  },
  {
    key: 'created_at',
    label: 'تاریخ ایجاد',
    textSecondary: true
  }
]

const answersModalTitle = computed(() => {
  if (!selectedQuestion.value) {
    return 'پاسخ‌ها'
  }
  return `پاسخ‌ها - ${selectedQuestion.value.title || ''}`
})

const fetchQuestions = async () => {
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

    const response = await apiClient.get('/challenge/questions', { params })

    if (response.data?.success) {
      questions.value = response.data.data.questions
      pagination.value = response.data.data.pagination
    } else {
      throw new Error(response.data?.message || 'خطا در دریافت سوالات')
    }
  } catch (err) {
    console.error('Challenge questions fetch error:', err)

    if (err.response && (err.response.status === 401 || err.response.status === 403)) {
      questions.value = []
      pagination.value = null
      loading.value = false
      return
    }

    error.value = err.response?.data?.message || err.message || 'خطا در بارگذاری سوالات'
    questions.value = []
    pagination.value = null
  } finally {
    loading.value = false
  }
}

const handleSearch = () => {
  currentPage.value = 1
  fetchQuestions()
}

const handleClear = () => {
  searchTerm.value = ''
  currentPage.value = 1
  fetchQuestions()
}

const goToPage = (page) => {
  if (!pagination.value) {
    return
  }

  if (page >= 1 && page <= pagination.value.last_page) {
    currentPage.value = page
    fetchQuestions()
  }
}

const openAnswersModal = (question) => {
  selectedQuestion.value = question
  answersModalOpen.value = true
}

const closeAnswersModal = () => {
  answersModalOpen.value = false
  selectedQuestion.value = null
}

const openImportModal = () => {
  importModalOpen.value = true
}

const closeImportModal = () => {
  importModalOpen.value = false
  importFile.value = null
  importError.value = ''
}

const submitImport = async () => {
  if (!(importFile.value instanceof File)) {
    importError.value = 'لطفاً فایل معتبری انتخاب کنید.'
    return
  }

  const formData = new FormData()
  formData.append('file', importFile.value)

  try {
    importing.value = true
    importError.value = ''

    const response = await apiClient.post('/challenge/questions/import', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    if (response.data?.success) {
      showToast(response.data.message || 'درون‌ریزی آغاز شد.', 'success')
      closeImportModal()
      fetchQuestions()
    } else {
      throw new Error(response.data?.message || 'خطا در درون‌ریزی سوالات')
    }
  } catch (err) {
    console.error('Challenge questions import error:', err)

    if (err.response?.status === 422) {
      importError.value = err.response?.data?.message || 'فایل ارسالی معتبر نیست.'
    } else if (err.response?.data?.message) {
      importError.value = err.response.data.message
    } else {
      importError.value = err.message || 'خطا در پردازش درخواست'
    }

    showToast(importError.value, 'error')
  } finally {
    importing.value = false
  }
}

const handleDelete = async (question) => {
  try {
    const result = await confirm('آیا از حذف این سوال اطمینان دارید؟', 'حذف سوال', {
      confirmText: 'بله، حذف شود',
      cancelText: 'انصراف'
    })

    if (!result.isConfirmed) {
      return
    }

    deletingId.value = question.id

    const response = await apiClient.delete(`/challenge/questions/${question.id}`)

    if (response.data?.success) {
      showToast(response.data.message || 'سوال با موفقیت حذف شد.', 'success')

      const isLastItem = questions.value.length === 1
      const isNotFirstPage = currentPage.value > 1

      if (isLastItem && isNotFirstPage) {
        currentPage.value -= 1
      }

      await fetchQuestions()
    } else {
      throw new Error(response.data?.message || 'خطا در حذف سوال')
    }
  } catch (err) {
    if (err?.isDismissed) {
      return
    }

    console.error('Challenge question delete error:', err)
    const message = err.response?.data?.message || err.message || 'خطا در حذف سوال'
    showToast(message, 'error')
  } finally {
    deletingId.value = null
  }
}

const formatDate = (value) => {
  if (!value) {
    return '-'
  }

  const date = new Date(value)
  if (Number.isNaN(date.getTime())) {
    return '-'
  }

  return date.toLocaleDateString('fa-IR')
}

const formatTime = (value) => {
  if (!value) {
    return '-'
  }

  const date = new Date(value)
  if (Number.isNaN(date.getTime())) {
    return '-'
  }

  return date.toLocaleTimeString('fa-IR', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

onMounted(() => {
  fetchQuestions()
})
</script>

<style scoped>
.text-success-400 {
  color: var(--color-success, #22C55E);
}
</style>


