<template>
  <div class="p-6 space-y-6">
    <PageHeader
      title="محدودیت املاک"
      subtitle="تعریف و مدیریت محدودیت‌های املاک"
    />

    <!-- Create Limit Button -->
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between mb-6">
      <div class="flex-1 max-w-md">
        <SearchBox
          v-model="searchTerm"
          placeholder="جستجو بر اساس عنوان..."
          :debounce-ms="500"
          @search="handleSearch"
          @clear="handleClear"
        />
      </div>
      <Button variant="primary" @click="showCreateModal = true">
        ایجاد محدودیت
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
      v-else-if="featureLimits && featureLimits.length > 0"
      :columns="tableColumns"
      :data="featureLimits"
      :pagination="pagination"
      show-row-number
    >
      <template #cell-limits="{ row }">
        <ul class="list-disc list-inside text-right space-y-1">
          <li v-if="row.verified_kyc_limit">محدودیت احراز هویت تایید شده</li>
          <li v-if="row.verified_bank_account_limit">محدودیت حساب بانکی تایید شده</li>
          <li v-if="row.not_sellable">غیرقابل فروش</li>
          <li v-if="row.under_18_limit">محدودیت زیر ۱۸ سال</li>
          <li v-if="row.more_than_18_limit">محدودیت بالای ۱۸ سال</li>
          <li v-if="row.dynasty_owner_limit">محدودیت دارنده سلسله</li>
        </ul>
      </template>

      <template #cell-status="{ row }">
        <Badge :variant="row.expired ? 'danger' : 'success'">
          {{ row.expired ? 'منقضی شده' : 'فعال' }}
        </Badge>
      </template>

      <template #cell-actions="{ row }">
        <Button
          variant="danger"
          size="sm"
          rounded="full"
          class="!p-2 !gap-0 min-w-[2.25rem]"
          :title="row.expired ? 'محدودیت منقضی‌شده قابل حذف نیست' : 'حذف'"
          :aria-label="row.expired ? 'محدودیت منقضی‌شده قابل حذف نیست' : 'حذف'"
          :loading="deleting && deletingLimitId === row.id"
          :disabled="deleting || row.expired"
          @click="handleDelete(row)"
        >
          <template #icon-left>
            <TableActionIcon name="delete" />
          </template>
        </Button>
      </template>
    </Table>

    <!-- Empty State -->
    <Alert
      v-else
      variant="warning"
      message="محدودیتی یافت نشد!"
      :dismissible="false"
    />

    <!-- Pagination -->
    <Pagination
      v-if="pagination && pagination.total > 0"
      :pagination="pagination"
      :disabled="loading"
      @page-change="onPageChange"
    />

    <!-- Create Limit Modal -->
    <Modal
      v-model="showCreateModal"
      title="تعریف محدودیت"
      size="full"
    >
      <div class="space-y-6">
        <!-- Alert -->
        <Alert variant="danger" :dismissible="false">
          <div class="space-y-2">
            <p><strong>توجه:</strong> تاریخ شروع و پایان نباید با دیگر محدودیت ها تداخل داشته باشد.</p>
            <p><strong>توجه:</strong> پیشوند شناسه های شروع و پایان باید با یکدیگر یکسان باشند.</p>
          </div>
        </Alert>

        <!-- Title -->
        <Input
          v-model="formData.title"
          label="عنوان"
          required
          :error="errors.title"
        />

        <!-- Start and End ID -->
        <div class="grid grid-cols-2 gap-4">
          <Input
            v-model="formData.start_id"
            label="شناسه شروع"
            required
            :error="errors.start_id"
          />
          <Input
            v-model="formData.end_id"
            label="شناسه پایانی"
            required
            :error="errors.end_id"
          />
        </div>

        <!-- Checkboxes Row 1 -->
        <div class="grid grid-cols-3 gap-4">
          <div class="flex items-center space-x-2 space-x-reverse">
            <input
              id="verified_kyc_limit"
              v-model="formData.verified_kyc_limit"
              type="checkbox"
              class="w-4 h-4 rounded border-border focus:ring-primary-500"
            />
            <label for="verified_kyc_limit" class="text-sm text-[var(--theme-text-primary)]">
              محدودیت احراز هویت تایید شده
            </label>
          </div>
          <div class="flex items-center space-x-2 space-x-reverse">
            <input
              id="verified_bank_account_limit"
              v-model="formData.verified_bank_account_limit"
              type="checkbox"
              class="w-4 h-4 rounded border-border focus:ring-primary-500"
            />
            <label for="verified_bank_account_limit" class="text-sm text-[var(--theme-text-primary)]">
              محدودیت حساب بانکی تایید شده
            </label>
          </div>
          <div class="flex items-center space-x-2 space-x-reverse">
            <input
              id="not_sellable"
              v-model="formData.not_sellable"
              type="checkbox"
              class="w-4 h-4 rounded border-border focus:ring-primary-500"
            />
            <label for="not_sellable" class="text-sm text-[var(--theme-text-primary)]">
              غیرقابل فروش
            </label>
          </div>
        </div>

        <!-- Checkboxes Row 2 -->
        <div class="grid grid-cols-3 gap-4">
          <div class="flex items-center space-x-2 space-x-reverse">
            <input
              id="under_18_limit"
              v-model="formData.under_18_limit"
              type="checkbox"
              class="w-4 h-4 rounded border-border focus:ring-primary-500"
            />
            <label for="under_18_limit" class="text-sm text-[var(--theme-text-primary)]">
              محدودیت زیر ۱۸ سال
            </label>
          </div>
          <div class="flex items-center space-x-2 space-x-reverse">
            <input
              id="more_than_18_limit"
              v-model="formData.more_than_18_limit"
              type="checkbox"
              class="w-4 h-4 rounded border-border focus:ring-primary-500"
            />
            <label for="more_than_18_limit" class="text-sm text-[var(--theme-text-primary)]">
              محدودیت بالای ۱۸ سال
            </label>
          </div>
          <div class="flex items-center space-x-2 space-x-reverse">
            <input
              id="dynasty_owner_limit"
              v-model="formData.dynasty_owner_limit"
              type="checkbox"
              class="w-4 h-4 rounded border-border focus:ring-primary-500"
            />
            <label for="dynasty_owner_limit" class="text-sm text-[var(--theme-text-primary)]">
              محدودیت دارنده سلسله
            </label>
          </div>
        </div>

        <!-- Individual Buy Limit and Price Limit -->
        <div class="grid grid-cols-2 gap-4">
          <div class="border border-[var(--theme-border)] rounded-lg p-4 space-y-4">
            <div class="flex items-center space-x-2 space-x-reverse">
              <input
                id="individual_buy_limit"
                v-model="formData.individual_buy_limit"
                type="checkbox"
                class="w-4 h-4 rounded border-border focus:ring-primary-500"
              />
              <label for="individual_buy_limit" class="text-sm text-[var(--theme-text-primary)]">
                محدودیت تعداد خرید
              </label>
            </div>
            <Input
              v-model="formData.individual_buy_count"
              label="تعداد خرید"
              type="number"
              :disabled="!formData.individual_buy_limit"
              :error="errors.individual_buy_count"
            />
          </div>
          <div class="border border-[var(--theme-border)] rounded-lg p-4 space-y-4">
            <div class="flex items-center space-x-2 space-x-reverse">
              <input
                id="price_limit"
                v-model="formData.price_limit"
                type="checkbox"
                class="w-4 h-4 rounded border-border focus:ring-primary-500"
              />
              <label for="price_limit" class="text-sm text-[var(--theme-text-primary)]">
                محدودیت قیمت ثابت
              </label>
            </div>
            <Input
              v-model="formData.price"
              label="قیمت ثابت"
              type="number"
              :disabled="!formData.price_limit"
              :error="errors.price"
            />
          </div>
        </div>

        <!-- Dates -->
        <div class="grid grid-cols-2 gap-4">
          <PersianDatePicker
            :key="`start-date-${modalOpenKey}`"
            v-model="formData.start_date"
            label="تاریخ شروع"
            required
            :error="errors.start_date"
          />
          <PersianDatePicker
            :key="`end-date-${modalOpenKey}`"
            v-model="formData.end_date"
            label="تاریخ پایان"
            required
            :error="errors.end_date"
          />
        </div>
      </div>

      <template #footer>
        <Button
          variant="primary"
          :loading="saving"
          @click="handleSave"
        >
          {{ submitButtonLabel }}
        </Button>
        <Button variant="danger" class="w-full" @click="showCreateModal = false">
          انصراف
        </Button>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { Table, Pagination, Button, LoadingState, ErrorState, Alert, Modal, Input, Badge, SearchBox, PageHeader } from '../../components/ui'
import { usePaginatedList } from '../../composables/usePaginatedList'
import PersianDatePicker from '../../components/ui/PersianDatePicker.vue'
import { useToast } from '../../composables/useToast'
import { confirm } from '../../utils/notifications'
import { useFeatureLimits } from '../../composables/useFeatureLimits'
import { gregorianToShamsiSync, todayInShamsi, addDaysInShamsi } from '../../utils/dateConverter'
import TableActionIcon from '../../components/icons/TableActionIcon.vue'

const { showToast } = useToast()
const {
  createFeatureLimit,
  deleteFeatureLimit,
  fetchFeatureLimits: fetchFeatureLimitsApi
} = useFeatureLimits()

const {
  loading,
  error,
  pagination,
  searchTerm,
  execute,
  search,
  clear,
  goToPage,
  buildParams
} = usePaginatedList({ perPage: 10 })

const featureLimits = ref([])
const showCreateModal = ref(false)
const saving = ref(false)
const deletingLimitId = ref(null)
const deleting = ref(false)
const modalOpenKey = ref(0)

const submitButtonLabel = computed(() => {
  return 'ثبت'
})

const errors = ref({})

const getTodayShamsi = todayInShamsi
const getFutureDateShamsi = addDaysInShamsi

const formData = ref({
  verified_kyc_limit: false,
  verified_bank_account_limit: false,
  not_sellable: false,
  under_18_limit: false,
  more_than_18_limit: false,
  dynasty_owner_limit: false,
  title: '',
  start_id: '',
  end_id: '',
  start_date: getTodayShamsi(),
  end_date: getFutureDateShamsi(30),
  price_limit: false,
  price: 0,
  individual_buy_limit: false,
  individual_buy_count: 0
})

// Table columns configuration
const tableColumns = [
  {
    key: 'title',
    label: 'عنوان'
  },
  {
    key: 'start_date_shamsi',
    label: 'تاریخ شروع'
  },
  {
    key: 'end_date_shamsi',
    label: 'تاریخ پایان'
  },
  {
    key: 'start_id',
    label: 'شناسه شروع'
  },
  {
    key: 'end_id',
    label: 'شناسه پایانی'
  },
  {
    key: 'limits',
    label: 'محدودیت ها'
  },
  {
    key: 'status',
    label: 'وضعیت'
  },
  {
    key: 'actions',
    label: 'اقدامات'
  }
]

const onPageChange = (page) => goToPage(page, fetchFeatureLimits)
const handleSearch = () => search(fetchFeatureLimits)
const handleClear = () => {
  searchTerm.value = ''
  clear(fetchFeatureLimits)
}

const submitFeatureLimitCreate = async () => {
  try {
    saving.value = true
    error.value = null
    errors.value = {}

    const payload = { ...formData.value }

    const response = await createFeatureLimit(payload)

    if (response.data.success) {
      showToast('محدودیت املاک با موفقیت ایجاد شد', 'success')
      showCreateModal.value = false
      resetForm()
      await fetchFeatureLimits()
    } else {
      error.value = 'خطا در ثبت اطلاعات'
    }
  } catch (err) {
    console.error('Feature limit create error:', err)

    error.value = err.response?.data?.message || 'خطا در ثبت اطلاعات'

    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors
    }
  } finally {
    saving.value = false
  }
}

const handleSave = async () => {

  await submitFeatureLimitCreate()
}

const handleDelete = async (row) => {
  if (row.expired) return

  const result = await confirm(
      `آیا از حذف محدودیت «${row.title}» مطمئن هستید؟ این عمل غیرقابل بازگشت است و تمام محدودیت‌های اعمال شده بر روی املاک حذف خواهد شد.`,
    'تایید حذف محدودیت',
    { confirmText: 'بله، حذف شود', cancelText: 'انصراف' }
  )
  if (!result.isConfirmed) return

  try {
        deleting.value = true
        deletingLimitId.value = row.id

        const response = await deleteFeatureLimit(row.id)

        if (response.data.success) {
          showToast('محدودیت املاک با موفقیت حذف شد', 'success')
          deletingLimitId.value = null
          await fetchFeatureLimits()
        }
      } catch (err) {
        console.error('Delete feature limit error:', err)

        showToast(err.response?.data?.message || 'خطا در حذف محدودیت', 'error')
      } finally {
        deleting.value = false
      }
}

const clearFeatureLimits = () => {
  featureLimits.value = []
  pagination.value = null
}

const fetchFeatureLimits = () => execute(async () => {
  const response = await fetchFeatureLimitsApi(buildParams())

  if (response.data.success) {
    featureLimits.value = response.data.data.feature_limits.map(limit => ({
      ...limit,
      start_date_shamsi: limit.start_date_shamsi
        || (limit.start_date ? gregorianToShamsiSync(limit.start_date) : null)
        || '-',
      end_date_shamsi: limit.end_date_shamsi
        || (limit.end_date ? gregorianToShamsiSync(limit.end_date) : null)
        || '-'
    }))
    pagination.value = response.data.data.pagination
  } else {
    error.value = 'خطا در دریافت اطلاعات محدودیت‌ها'
    clearFeatureLimits()
  }
}, {
  onClear: clearFeatureLimits,
  logLabel: 'Feature limits fetch error:',
  fallbackMessage: 'خطا در بارگذاری اطلاعات'
})

const resetForm = () => {
  formData.value = {
    verified_kyc_limit: false,
    verified_bank_account_limit: false,
    not_sellable: false,
    under_18_limit: false,
    more_than_18_limit: false,
    dynasty_owner_limit: false,
    title: '',
    start_id: '',
    end_id: '',
    start_date: getTodayShamsi(),
    end_date: getFutureDateShamsi(30),
    price_limit: false,
    price: 0,
    individual_buy_limit: false,
    individual_buy_count: 0
  }
  errors.value = {}
}

watch(showCreateModal, (isOpen) => {
  if (isOpen) {
    // Remount date pickers so they re-init after the modal opens
    modalOpenKey.value++
  }
})

onMounted(() => {
  fetchFeatureLimits()
})
</script>

<style scoped>
/* Additional styles if needed */
</style>

<style>
/* Global overrides (unscoped): :deep() is only valid in scoped SFC styles. */
/* Make the create limit modal wider - target modal content container */
.relative.z-10.w-full.max-w-full {
  max-width: 80rem !important; /* 7xl = 80rem = 1280px */
}
</style>

