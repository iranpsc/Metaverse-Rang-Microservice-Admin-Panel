<template>
  <div class="activity-logs-page" dir="rtl">
    <div class="activity-logs-page__header">
      <h1 class="activity-logs-page__title">گزارش فعالیت‌ها</h1>
      <p class="activity-logs-page__subtitle">
        مشاهده و جستجوی تمام فعالیت‌های ثبت‌شده در سیستم شامل تغییرات داده و احراز هویت
      </p>
    </div>

    <section
      class="filters-panel"
      aria-label="جستجو و فیلتر فعالیت‌ها"
    >
      <div class="filters-panel__search">
        <label for="activity-log-search" class="filters-panel__label">جستجو</label>
        <SearchBox
          id="activity-log-search"
          v-model="searchTerm"
          placeholder="توضیحات، رویداد، مدل یا کاربر..."
          :debounce-ms="400"
          container-class="w-full"
          @search="handleSearch"
          @clear="handleClear"
        />
      </div>

      <div class="filters-panel__grid">
        <div class="filters-panel__field">
          <label for="activity-log-category" class="filters-panel__label">دسته‌بندی</label>
          <select
            id="activity-log-category"
            v-model="activeCategory"
            class="filters-panel__select"
            @change="handleCategoryChange"
          >
            <option value="all">همه دسته‌ها</option>
            <option
              v-for="cat in categories"
              :key="cat.id"
              :value="cat.id"
            >
              {{ cat.label }}
            </option>
          </select>
        </div>

        <div class="filters-panel__field">
          <label for="activity-log-event" class="filters-panel__label">رویداد</label>
          <select
            id="activity-log-event"
            v-model="activeEvent"
            class="filters-panel__select"
            @change="fetchActivities"
          >
            <option value="all">همه رویدادها</option>
            <option value="created">ایجاد</option>
            <option value="updated">ویرایش</option>
            <option value="deleted">حذف</option>
            <option value="login">ورود</option>
            <option value="logout">خروج</option>
            <option value="login_failed">ورود ناموفق</option>
            <option value="password_reset">تغییر رمز</option>
            <option value="password_reset_requested">درخواست بازیابی رمز</option>
          </select>
        </div>
      </div>

      <div
        v-if="hasActiveFilters"
        class="filters-panel__actions"
      >
        <Button
          type="button"
          variant="glass"
          size="sm"
          @click="resetFilters"
        >
          پاک کردن فیلترها
        </Button>
      </div>
    </section>

    <LoadingState v-if="loading" />

    <ErrorState
      v-else-if="error"
      :message="error"
      variant="error"
    />

    <template v-else>
      <Table
        :columns="tableColumns"
        :data="activities"
        :pagination="pagination"
        :show-row-number="true"
        empty-state-message="فعالیتی ثبت نشده است"
      >
        <template #cell-category_label="{ value }">
          <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-primary-500/10 text-primary-300 border border-primary-500/20">
            {{ value }}
          </span>
        </template>
        <template #cell-event="{ value }">
          <span :class="eventBadgeClass(value)">{{ eventLabel(value) }}</span>
        </template>
        <template #cell-causer="{ row }">
          <span class="text-[var(--theme-text-secondary)]">
            {{ row.causer?.name ?? 'سیستم' }}
          </span>
        </template>
        <template #cell-actions="{ row }">
          <Button
            size="xs"
            variant="glass"
            rounded="full"
            class="!p-2 !gap-0 min-w-[2.25rem]"
            title="جزئیات"
            aria-label="جزئیات"
            @click="openDetailModal(row)"
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

    <Modal
      :model-value="isDetailModalOpen"
      title="جزئیات فعالیت"
      size="lg"
      @update:model-value="handleModalToggle"
    >
      <div v-if="selectedActivity" class="space-y-6" dir="rtl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="bg-[var(--theme-bg-glass)] border border-[var(--theme-border)] rounded-lg p-4">
            <p class="text-xs text-[var(--theme-text-muted)] mb-1">شناسه</p>
            <p class="text-[var(--theme-text-primary)] font-semibold">{{ selectedActivity.id }}</p>
          </div>
          <div class="bg-[var(--theme-bg-glass)] border border-[var(--theme-border)] rounded-lg p-4">
            <p class="text-xs text-[var(--theme-text-muted)] mb-1">دسته‌بندی</p>
            <p class="text-[var(--theme-text-primary)] font-semibold">{{ selectedActivity.category_label }}</p>
          </div>
          <div class="bg-[var(--theme-bg-glass)] border border-[var(--theme-border)] rounded-lg p-4">
            <p class="text-xs text-[var(--theme-text-muted)] mb-1">رویداد</p>
            <p class="text-[var(--theme-text-primary)] font-semibold">{{ eventLabel(selectedActivity.event) }}</p>
          </div>
          <div class="bg-[var(--theme-bg-glass)] border border-[var(--theme-border)] rounded-lg p-4">
            <p class="text-xs text-[var(--theme-text-muted)] mb-1">کاربر</p>
            <p class="text-[var(--theme-text-primary)] font-semibold">{{ selectedActivity.causer?.name ?? 'سیستم' }}</p>
          </div>
          <div class="bg-[var(--theme-bg-glass)] border border-[var(--theme-border)] rounded-lg p-4">
            <p class="text-xs text-[var(--theme-text-muted)] mb-1">تاریخ رویداد</p>
            <p class="text-[var(--theme-text-primary)] font-semibold">{{ modalEventDate }}</p>
          </div>
          <div class="bg-[var(--theme-bg-glass)] border border-[var(--theme-border)] rounded-lg p-4">
            <p class="text-xs text-[var(--theme-text-muted)] mb-1">زمان رویداد</p>
            <p class="text-[var(--theme-text-primary)] font-semibold">{{ modalEventTime }}</p>
          </div>
          <div
            v-if="selectedActivity.subject_type"
            class="bg-[var(--theme-bg-glass)] border border-[var(--theme-border)] rounded-lg p-4 md:col-span-2"
          >
            <p class="text-xs text-[var(--theme-text-muted)] mb-1">موضوع</p>
            <p class="text-[var(--theme-text-primary)] font-semibold">
              {{ selectedActivity.subject_type }} #{{ selectedActivity.subject_id }}
            </p>
          </div>
        </div>

        <div class="space-y-2">
          <h3 class="text-lg font-semibold text-[var(--theme-text-primary)]">توضیحات</h3>
          <p class="text-[var(--theme-text-secondary)]">{{ selectedActivity.description }}</p>
        </div>

        <div v-if="hasChangeDetails" class="space-y-2">
          <h3 class="text-lg font-semibold text-[var(--theme-text-primary)]">تغییرات</h3>
          <pre class="text-xs text-[var(--theme-text-secondary)] bg-[var(--theme-bg-glass)] border border-[var(--theme-border)] rounded-lg p-4 overflow-x-auto max-h-64">{{ formattedProperties }}</pre>
        </div>
      </div>
    </Modal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import apiClient from '../../utils/api'
import { Table, Pagination, LoadingState, ErrorState, Button, Modal, SearchBox } from '../../components/ui'
import TableActionIcon from '../../components/icons/TableActionIcon.vue'
import { useToast } from '../../composables/useToast'

const { showToast } = useToast()

const loading = ref(true)
const error = ref(null)
const activities = ref([])
const pagination = ref(null)
const categories = ref([])
const searchTerm = ref('')
const activeCategory = ref('all')
const activeEvent = ref('all')
const currentPage = ref(1)
const isDetailModalOpen = ref(false)
const selectedActivity = ref(null)

const tableColumns = [
  { key: 'id', label: 'شناسه' },
  { key: 'category_label', label: 'دسته‌بندی' },
  { key: 'event', label: 'رویداد' },
  { key: 'description', label: 'توضیحات' },
  { key: 'causer', label: 'کاربر' },
  { key: 'created_at_jalali', label: 'تاریخ' },
  { key: 'created_at_time', label: 'زمان' },
  { key: 'actions', label: 'عملیات' }
]

const eventLabels = {
  created: 'ایجاد',
  updated: 'ویرایش',
  deleted: 'حذف',
  login: 'ورود',
  logout: 'خروج',
  login_failed: 'ورود ناموفق',
  password_reset: 'تغییر رمز',
  password_reset_requested: 'درخواست بازیابی رمز'
}

const eventLabel = (event) => eventLabels[event] || event || '-'

const eventBadgeClass = (event) => {
  const base = 'inline-flex px-2.5 py-1 rounded-full text-xs font-medium border '
  const map = {
    created: 'bg-emerald-500/10 text-emerald-300 border-emerald-500/20',
    updated: 'bg-blue-500/10 text-blue-300 border-blue-500/20',
    deleted: 'bg-rose-500/10 text-rose-300 border-rose-500/20',
    login: 'bg-primary-500/10 text-primary-300 border-primary-500/20',
    logout: 'bg-[var(--theme-bg-glass)] text-[var(--theme-text-secondary)] border-[var(--theme-border)]',
    login_failed: 'bg-rose-500/10 text-rose-300 border-rose-500/20'
  }
  return base + (map[event] || 'bg-[var(--theme-bg-glass)] text-[var(--theme-text-secondary)] border-[var(--theme-border)]')
}

const hasActiveFilters = computed(() => {
  return (
    searchTerm.value.trim() !== '' ||
    activeCategory.value !== 'all' ||
    activeEvent.value !== 'all'
  )
})

const splitJalaliDateTime = (jalaliValue, timeValue) => {
  if (timeValue) {
    return {
      date: jalaliValue || '-',
      time: timeValue
    }
  }

  if (!jalaliValue) {
    return { date: '-', time: '-' }
  }

  const parts = String(jalaliValue).trim().split(/\s+/)
  if (parts.length >= 2) {
    return {
      date: parts[0],
      time: parts.slice(1).join(' ')
    }
  }

  return { date: jalaliValue, time: '-' }
}

const modalEventDate = computed(() => {
  if (!selectedActivity.value) return '-'
  return splitJalaliDateTime(
    selectedActivity.value.created_at_jalali,
    selectedActivity.value.created_at_time
  ).date
})

const modalEventTime = computed(() => {
  if (!selectedActivity.value) return '-'
  return splitJalaliDateTime(
    selectedActivity.value.created_at_jalali,
    selectedActivity.value.created_at_time
  ).time
})

const hasChangeDetails = computed(() => {
  if (!selectedActivity.value?.properties) return false
  const props = selectedActivity.value.properties
  return props.attributes || props.old
})

const formattedProperties = computed(() => {
  if (!selectedActivity.value?.properties) return ''
  const { attributes, old, ...rest } = selectedActivity.value.properties
  return JSON.stringify({ attributes, old, ...rest }, null, 2)
})

const fetchCategories = async () => {
  try {
    const response = await apiClient.get('/activity-logs/categories')
    if (response.data.success) {
      categories.value = response.data.data.categories
    }
  } catch (err) {
    if (err.response?.status === 403) {
      error.value = 'شما دسترسی مشاهده گزارش فعالیت‌ها را ندارید.'
    }
  }
}

const fetchActivities = async () => {
  loading.value = true
  error.value = null
  try {
    const params = {
      page: currentPage.value,
      per_page: 15,
      search: searchTerm.value || undefined,
      category: activeCategory.value !== 'all' ? activeCategory.value : undefined,
      event: activeEvent.value !== 'all' ? activeEvent.value : undefined
    }
    const response = await apiClient.get('/activity-logs', { params })
    if (response.data.success) {
      activities.value = response.data.data.activities
      pagination.value = response.data.data.pagination
    } else {
      activities.value = []
      error.value = response.data.message || 'خطا در دریافت گزارش فعالیت‌ها'
    }
  } catch (err) {
    if (err.response?.status === 403) {
      error.value = 'شما دسترسی مشاهده گزارش فعالیت‌ها را ندارید.'
    } else {
      error.value = err.response?.data?.message || 'خطا در دریافت گزارش فعالیت‌ها'
    }
    activities.value = []
    showToast(error.value, 'error')
  } finally {
    loading.value = false
  }
}

const handleSearch = () => {
  currentPage.value = 1
  fetchActivities()
}

const handleClear = () => {
  searchTerm.value = ''
  currentPage.value = 1
  fetchActivities()
}

const handleCategoryChange = () => {
  currentPage.value = 1
  fetchActivities()
}

const resetFilters = () => {
  searchTerm.value = ''
  activeCategory.value = 'all'
  activeEvent.value = 'all'
  currentPage.value = 1
  fetchActivities()
}

const goToPage = (page) => {
  currentPage.value = page
  fetchActivities()
}

const openDetailModal = async (row) => {
  try {
    const response = await apiClient.get(`/activity-logs/${row.id}`)
    if (response.data.success) {
      selectedActivity.value = response.data.data.activity
      isDetailModalOpen.value = true
    }
  } catch {
    selectedActivity.value = row
    isDetailModalOpen.value = true
  }
}

const handleModalToggle = (value) => {
  isDetailModalOpen.value = value
  if (!value) selectedActivity.value = null
}

onMounted(async () => {
  await fetchCategories()
  await fetchActivities()
})
</script>

<style scoped>
.activity-logs-page {
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

@media (min-width: 640px) {
  .activity-logs-page {
    padding: 1.5rem;
    gap: 1.5rem;
  }
}

.activity-logs-page__header {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

@media (min-width: 640px) {
  .activity-logs-page__header {
    gap: 0.5rem;
  }
}

.activity-logs-page__title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--theme-text-primary);
  line-height: 1.3;
}

@media (min-width: 640px) {
  .activity-logs-page__title {
    font-size: 1.875rem;
  }
}

.activity-logs-page__subtitle {
  font-size: 0.875rem;
  color: var(--theme-text-secondary);
  line-height: 1.6;
}

@media (min-width: 640px) {
  .activity-logs-page__subtitle {
    font-size: 1rem;
  }
}

.filters-panel {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  padding: 1rem;
  border-radius: 0.75rem;
  border: 1px solid var(--theme-border);
  background: var(--theme-bg-elevated);
}

@media (min-width: 640px) {
  .filters-panel {
    padding: 1.25rem;
    gap: 1.25rem;
  }
}

.filters-panel__search {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  width: 100%;
}

.filters-panel__grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
  width: 100%;
}

@media (min-width: 480px) {
  .filters-panel__grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (min-width: 1024px) {
  .filters-panel {
    display: grid;
    grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr) minmax(0, 1fr);
    grid-template-areas:
      'search search search'
      'category event actions';
    align-items: end;
    column-gap: 1rem;
    row-gap: 1rem;
  }

  .filters-panel__search {
    grid-area: search;
  }

  .filters-panel__grid {
    display: contents;
  }

  .filters-panel__field:first-child {
    grid-area: category;
  }

  .filters-panel__field:last-child {
    grid-area: event;
  }

  .filters-panel__actions {
    grid-area: actions;
    justify-self: start;
    align-self: end;
  }
}

.filters-panel__field {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  min-width: 0;
}

.filters-panel__label {
  font-size: 0.8125rem;
  font-weight: 500;
  color: var(--theme-text-secondary);
}

@media (min-width: 640px) {
  .filters-panel__label {
    font-size: 0.875rem;
  }
}

.filters-panel__select {
  width: 100%;
  min-height: 2.75rem;
  padding: 0.5rem 2.25rem 0.5rem 0.75rem;
  border-radius: 0.5rem;
  border: 1px solid var(--theme-border);
  background: var(--theme-bg-glass);
  color: var(--theme-text-primary);
  font-size: 0.875rem;
  line-height: 1.5;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: left 0.65rem center;
  background-size: 1rem;
  cursor: pointer;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.filters-panel__select:focus {
  outline: none;
  border-color: rgba(124, 58, 237, 0.5);
  box-shadow: 0 0 0 2px rgba(124, 58, 237, 0.25);
}

.filters-panel__select:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.filters-panel__actions {
  display: flex;
  justify-content: flex-start;
  width: 100%;
  padding-top: 0.25rem;
}

@media (min-width: 480px) and (max-width: 1023px) {
  .filters-panel__actions {
    grid-column: 1 / -1;
  }
}

@media (max-width: 479px) {
  .filters-panel__actions :deep(button) {
    width: 100%;
    justify-content: center;
  }
}
</style>
