<template>
  <div class="p-6 space-y-6" dir="rtl">
    <!-- Page Header -->
    <div class="flex flex-col gap-2">
      <h1 class="text-3xl font-bold text-[var(--theme-text-primary)]">گزارشات کاربران</h1>
      <p class="text-[var(--theme-text-secondary)]">
        بررسی و مدیریت گزارش‌های ثبت‌شده توسط کاربران در بخش‌های مختلف متاورس
      </p>
    </div>

    <!-- Search -->
    <div class="w-full md:max-w-xl">
      <SearchBox
        v-model="searchTerm"
        placeholder="جستجو بر اساس عنوان، آدرس یا نام کاربر..."
        :debounce-ms="400"
        container-class="w-full"
        @search="handleSearch"
        @clear="handleClear"
      />
    </div>

    <!-- Tabs -->
    <div
      class="bg-[var(--theme-bg-elevated)] border border-[var(--theme-border)] rounded-xl overflow-hidden"
      role="tablist"
      aria-label="Report subjects"
    >
      <div class="flex flex-wrap">
        <button
          v-for="tab in subjectTabs"
          :key="tab.id"
          type="button"
          class="tab-button"
          :class="{ 'tab-button--active': activeSubject === tab.id }"
          role="tab"
          :aria-selected="activeSubject === tab.id"
          :tabindex="activeSubject === tab.id ? 0 : -1"
          @click="handleTabChange(tab.id)"
        >
          <span class="tab-button__label">{{ tab.label }}</span>
          <span class="tab-button__description">{{ tab.description }}</span>
        </button>
      </div>
    </div>

    <!-- Content States -->
    <LoadingState v-if="loading" />

    <ErrorState
      v-else-if="error"
      :message="error"
      variant="error"
    />

    <template v-else>
      <Table
        :columns="tableColumns"
        :data="reports"
        :pagination="pagination"
        :show-row-number="true"
        empty-state-message="گزارشی ثبت نشده است!"
      >
        <template #cell-title="{ value }">
          <span
            class="title-cell"
            :title="value || '-'"
          >
            {{ shortenTitle(value) }}
          </span>
        </template>
        <template #cell-view="{ row }">
          <Button
            size="xs"
            variant="glass"
            rounded="full"
            class="!p-2 !gap-0 min-w-[2.25rem]"
            title="مشاهده"
            aria-label="مشاهده"
            @click="openReportModal(row)"
          >
            <template #icon-left>
              <TableActionIcon name="view" />
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
    </template>

    <!-- Report Details Modal -->
    <Modal
      :model-value="isReportModalOpen"
      title="جزئیات گزارش"
      size="lg"
      @update:model-value="handleModalToggle"
    >
      <div v-if="selectedReport" class="space-y-6" dir="rtl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="bg-[var(--theme-bg-glass)] border border-[var(--theme-border)] rounded-lg p-4">
            <p class="text-xs text-[var(--theme-text-muted)] mb-1">شناسه گزارش</p>
            <p class="text-[var(--theme-text-primary)] font-semibold">{{ selectedReport.id }}</p>
          </div>
          <div class="bg-[var(--theme-bg-glass)] border border-[var(--theme-border)] rounded-lg p-4">
            <p class="text-xs text-[var(--theme-text-muted)] mb-1">نوع گزارش</p>
            <p class="text-[var(--theme-text-primary)] font-semibold">{{ subjectLabelMap[selectedReport.subject] }}</p>
          </div>
          <div class="bg-[var(--theme-bg-glass)] border border-[var(--theme-border)] rounded-lg p-4">
            <p class="text-xs text-[var(--theme-text-muted)] mb-1">نام کاربر</p>
            <p class="text-[var(--theme-text-primary)] font-semibold">{{ selectedReport.user?.name ?? 'نامشخص' }}</p>
          </div>
          <div class="bg-[var(--theme-bg-glass)] border border-[var(--theme-border)] rounded-lg p-4">
            <p class="text-xs text-[var(--theme-text-muted)] mb-1">شناسه شهروندی</p>
            <p class="text-[var(--theme-text-primary)] font-semibold">{{ selectedReport.user?.code ?? '-' }}</p>
          </div>
          <div class="bg-[var(--theme-bg-glass)] border border-[var(--theme-border)] rounded-lg p-4">
            <p class="text-xs text-[var(--theme-text-muted)] mb-1">تاریخ گزارش</p>
            <p class="text-[var(--theme-text-primary)] font-semibold">{{ selectedReport.created_at_jalali ?? '-' }}</p>
          </div>
          <div class="bg-[var(--theme-bg-glass)] border border-[var(--theme-border)] rounded-lg p-4">
            <p class="text-xs text-[var(--theme-text-muted)] mb-1">زمان گزارش</p>
            <p class="text-[var(--theme-text-primary)] font-semibold">{{ selectedReport.created_at_time ?? '-' }}</p>
          </div>
        </div>

        <div class="space-y-3">
          <h3 class="text-lg font-semibold text-[var(--theme-text-primary)]">عنوان گزارش</h3>
          <p class="text-[var(--theme-text-secondary)]">{{ selectedReport.title }}</p>
        </div>

        <div class="space-y-3">
          <h3 class="text-lg font-semibold text-[var(--theme-text-primary)]">متن گزارش</h3>
          <p class="text-[var(--theme-text-secondary)] leading-7 whitespace-pre-line">
            {{ selectedReport.content || 'متنی ثبت نشده است.' }}
          </p>
        </div>

        <div v-if="selectedReport.url" class="space-y-2">
          <h3 class="text-lg font-semibold text-[var(--theme-text-primary)]">آدرس مرتبط</h3>
          <a
            :href="selectedReport.url"
            target="_blank"
            rel="noopener"
            class="inline-flex items-center gap-2 text-secondary-300 hover:text-secondary-200"
          >
            {{ selectedReport.url }}
          </a>
        </div>

        <div v-if="selectedReport.images && selectedReport.images.length" class="space-y-3">
          <h3 class="text-lg font-semibold text-[var(--theme-text-primary)]">پیوست‌ها</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <a
              v-for="image in selectedReport.images"
              :key="image.id"
              :href="image.url"
              target="_blank"
              rel="noopener"
              class="group block bg-[var(--theme-bg-glass)] border border-[var(--theme-border)] rounded-lg overflow-hidden"
            >
              <img
                v-if="image.url"
                :src="image.url"
                alt="گزارش"
                class="w-full h-40 object-cover transition-transform duration-200 group-hover:scale-[1.02]"
              >
              <div v-else class="p-4 text-[var(--theme-text-secondary)] text-sm">
                تصویری برای نمایش موجود نیست.
              </div>
            </a>
          </div>
        </div>
      </div>

      <template #footer>
        <Button
          variant="danger"
          rounded="full"
          @click="closeReportModal"
        >
          بستن
        </Button>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import apiClient from '../../utils/api'
import {
  Button,
  Table,
  Pagination,
  SearchBox,
  LoadingState,
  ErrorState,
  Modal
} from '../../components/ui'
import TableActionIcon from '../../components/icons/TableActionIcon.vue'

const subjectTabs = [
  {
    id: 'spellingError',
    label: 'غلط املایی',
    description: 'خطاهای نوشتاری و نگارشی'
  },
  {
    id: 'fpsError',
    label: 'خطای FPS',
    description: 'مشکلات عملکرد و سقوط فریم'
  },
  {
    id: 'disrespect',
    label: 'بی‌احترامی',
    description: 'گزارش‌های رفتار نامناسب کاربران'
  },
  {
    id: 'displayError',
    label: 'خطا در نمایش',
    description: 'مشکلات نمایشی و بصری'
  },
  {
    id: 'codingError',
    label: 'خطا در کدنویسی',
    description: 'گزارش‌های مربوط به باگ‌های فنی'
  }
]

const tableColumns = [
  {
    key: 'id',
    label: 'شناسه'
  },
  {
    key: 'title',
    label: 'عنوان'
  },
  {
    key: 'view',
    label: 'متن گزارش',
    headerClass: 'text-center',
    cellClass: 'text-center'
  },
  {
    key: 'url',
    label: 'URL',
    textSecondary: true,
    defaultValue: '-'
  },
  {
    key: 'user.name',
    label: 'نام کاربر',
    defaultValue: 'نامشخص'
  },
  {
    key: 'user.code',
    label: 'شناسه شهروندی',
    textSecondary: true,
    defaultValue: '-'
  },
  {
    key: 'created_at_jalali',
    label: 'تاریخ گزارش',
    textSecondary: true,
    defaultValue: '-'
  },
  {
    key: 'created_at_time',
    label: 'ساعت گزارش',
    textSecondary: true,
    defaultValue: '-'
  }
]

const loading = ref(true)
const error = ref(null)
const reports = ref([])
const pagination = ref(null)
const searchTerm = ref('')
const currentPage = ref(1)
const activeSubject = ref(subjectTabs[0].id)
const isReportModalOpen = ref(false)
const selectedReport = ref(null)
const perPage = 10

const subjectLabelMap = computed(() => {
  return subjectTabs.reduce((acc, tab) => {
    acc[tab.id] = tab.label
    return acc
  }, {})
})

const fetchReports = async () => {
  try {
    loading.value = true
    error.value = null

    const params = {
      subject: activeSubject.value,
      page: currentPage.value,
      per_page: perPage
    }

    if (searchTerm.value) {
      params.search = searchTerm.value
    }

    const response = await apiClient.get('/reports', { params })

    if (response.data.success) {
      reports.value = response.data.data.reports
      pagination.value = response.data.data.pagination
    } else {
      error.value = response.data.message || 'خطا در دریافت گزارش‌ها'
      reports.value = []
      pagination.value = null
    }
  } catch (err) {
    console.error('Reports fetch error:', err)

    if (err.response && (err.response.status === 401 || err.response.status === 403)) {
      reports.value = []
      pagination.value = null
      loading.value = false
      return
    }

    error.value = err.response?.data?.message || 'خطا در بارگذاری گزارش‌ها'
    reports.value = []
    pagination.value = null
  } finally {
    loading.value = false
  }
}

const handleSearch = () => {
  currentPage.value = 1
  fetchReports()
}

const handleClear = () => {
  searchTerm.value = ''
  currentPage.value = 1
  fetchReports()
}

const goToPage = (page) => {
  if (page >= 1 && (!pagination.value || page <= pagination.value.last_page)) {
    currentPage.value = page
    fetchReports()
  }
}

const handleTabChange = (subjectId) => {
  if (activeSubject.value !== subjectId) {
    activeSubject.value = subjectId
    currentPage.value = 1
    fetchReports()
  }
}

const openReportModal = (report) => {
  selectedReport.value = report
  isReportModalOpen.value = true
}

const closeReportModal = () => {
  isReportModalOpen.value = false
  selectedReport.value = null
}

const handleModalToggle = (value) => {
  isReportModalOpen.value = value
  if (!value) {
    selectedReport.value = null
  }
}

const shortenTitle = (text) => {
  if (!text) return '-'
  const normalized = text.trim()
  return normalized.length > 45 ? `${normalized.slice(0, 45)}…` : normalized
}

onMounted(() => {
  fetchReports()
})
</script>

<style scoped>
.text-secondary-300 {
  color: var(--theme-secondary, #06B6D4);
}

.hover\:text-secondary-200:hover {
  color: #22D3EE;
}

.bg-\[var\(--theme-bg-glass\)\] {
  background-color: var(--theme-bg-glass);
}

.border-\[var\(--theme-border\)\] {
  border-color: var(--theme-border);
}

.text-\[var\(--theme-text-muted\)\] {
  color: var(--theme-text-muted);
}

.tab-button {
  position: relative;
  flex: 1 1 180px;
  padding: 1rem 1.5rem;
  background: transparent;
  border: none;
  color: var(--theme-text-secondary);
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 0.25rem;
  cursor: pointer;
  transition: color 0.2s ease, background 0.2s ease;
}

.tab-button::after {
  content: '';
  position: absolute;
  left: 50%;
  bottom: 0;
  transform: translateX(-50%);
  width: 0;
  height: 2px;
  background: transparent;
  transition: width 0.2s ease, background 0.2s ease;
}

.tab-button:hover {
  color: var(--theme-text-primary);
  background: rgba(255, 255, 255, 0.02);
}

.tab-button--active {
  color: var(--theme-text-primary);
  background: rgba(255, 255, 255, 0.04);
}

.tab-button--active::after {
  width: 70%;
  height: 3px;
  background: var(--theme-text-primary);
}

.tab-button__label {
  font-size: 1rem;
  font-weight: 600;
}

.tab-button__description {
  font-size: 0.75rem;
  opacity: 0.7;
}

.title-cell {
  display: inline-block;
  max-width: 18rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>


