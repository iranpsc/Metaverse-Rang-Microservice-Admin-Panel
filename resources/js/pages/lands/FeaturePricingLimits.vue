<template>
  <div class="p-6 space-y-6" dir="rtl">
    <PageHeader
      title="محدودیت‌های قیمت"
      subtitle="تنظیم محدودیت‌های قیمت گذاری"
    />

    <!-- Loading State -->
    <LoadingState v-if="loading" />

    <!-- Error State -->
    <ErrorState
      v-else-if="error"
      :message="error"
      variant="error"
    />

    <!-- Main Content -->
    <div v-else class="space-y-6">
      <!-- Form -->
      <div class="bg-[var(--theme-bg-elevated)] rounded-lg border border-[var(--theme-border)] p-6">
        <div class="grid grid-cols-2 gap-6 mb-6">
          <div>
            <label class="block text-sm font-medium mb-2 text-[var(--theme-text-primary)]">
              محدودیت قیمت گذاری عموم
            </label>
            <Input
              v-model="formData.public_price_limit"
              type="number"
              :error="errors.public_price_limit"
            />
          </div>
          <div>
            <label class="block text-sm font-medium mb-2 text-[var(--theme-text-primary)]">
              محدودیت قیمت گذاری زیر ۱۸ سال
            </label>
            <Input
              v-model="formData.under_eighteen_price_limit"
              type="number"
              :error="errors.under_eighteen_price_limit"
            />
          </div>
        </div>

        <div class="flex justify-end">
          <Button
            variant="primary"
            :loading="saving"
            @click="handleSave"
            class="w-1/4"
          >
            ثبت
          </Button>
        </div>
      </div>

      <!-- Activity Logs -->
      <div>
        <h2 class="text-lg font-semibold text-[var(--theme-text-primary)] mb-3">
          گزارش فعالیت‌ها
        </h2>

        <Table
          v-if="activityLogs.length"
          :columns="activityColumns"
          :data="activityLogs"
          container-class="mb-6"
          row-number-header-class="text-center"
          row-number-cell-class="text-center"
          empty-state-message="فعالیتی ثبت نشده است"
        >
          <template #cell-event="{ value }">
            <span :class="eventBadgeClass(value)">{{ eventLabel(value) }}</span>
          </template>
        </Table>

        <Alert
          v-else
          variant="info"
          message="فعالیتی برای محدودیت‌های قیمت ثبت نشده است."
          :dismissible="false"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import apiClient from '../../utils/api'
import { Input, Button, Alert, LoadingState, ErrorState, Table, PageHeader } from '../../components/ui'
import { getApiErrorMessage, handleAuthListError } from '../../utils/apiErrors'
import { useToast } from '../../composables/useToast'

const { showToast } = useToast()

const loading = ref(true)
const error = ref(null)
const priceLimits = ref(null)
const activityLogs = ref([])
const saving = ref(false)
const errors = ref({})

const activityColumns = [
  {
    key: 'created_at_jalali',
    label: 'تاریخ تغییر',
    textSecondary: true,
    cellClass: 'text-right',
    headerClass: 'text-right'
  },
  {
    key: 'created_at_time',
    label: 'ساعت تغییر',
    textSecondary: true,
    cellClass: 'text-right',
    headerClass: 'text-right'
  },
  {
    key: 'causer_name',
    label: 'نام تغییر دهنده',
    textSecondary: true,
    cellClass: 'text-right',
    headerClass: 'text-right'
  },
  {
    key: 'event',
    label: 'رویداد',
    cellClass: 'text-right',
    headerClass: 'text-right'
  }
]

const eventLabels = {
  created: 'ایجاد',
  updated: 'ویرایش',
  deleted: 'حذف'
}

const eventLabel = (event) => eventLabels[event] || event || '-'

const eventBadgeClass = (event) => {
  const base = 'inline-flex px-2.5 py-1 rounded-full text-xs font-medium border '
  const map = {
    created: 'bg-emerald-500/10 text-emerald-300 border-emerald-500/20',
    updated: 'bg-blue-500/10 text-blue-300 border-blue-500/20',
    deleted: 'bg-rose-500/10 text-rose-300 border-rose-500/20'
  }
  return base + (map[event] || 'bg-[var(--theme-bg-glass)] text-[var(--theme-text-secondary)] border-[var(--theme-border)]')
}

const formData = ref({
  public_price_limit: 0,
  under_eighteen_price_limit: 0
})

const validateForm = () => {
  errors.value = {}
  error.value = null

  if (formData.value.public_price_limit === '' || formData.value.public_price_limit === null || formData.value.public_price_limit === undefined) {
    errors.value.public_price_limit = 'محدودیت قیمت گذاری عموم الزامی است'
    return false
  }

  if (formData.value.under_eighteen_price_limit === '' || formData.value.under_eighteen_price_limit === null || formData.value.under_eighteen_price_limit === undefined) {
    errors.value.under_eighteen_price_limit = 'محدودیت قیمت گذاری زیر ۱۸ سال الزامی است'
    return false
  }

  return true
}

const applyResponseData = (payload) => {
  priceLimits.value = payload?.price_limits ?? null
  activityLogs.value = payload?.activity_logs ?? []

  if (priceLimits.value) {
    formData.value = {
      public_price_limit: priceLimits.value.public_price_limit || 0,
      under_eighteen_price_limit: priceLimits.value.under_eighteen_price_limit || 0
    }
  }
}

const submitPricingLimitsUpdate = async () => {
  try {
    saving.value = true
    error.value = null
    errors.value = {}

    const response = await apiClient.post('/lands/feature-pricing-limits', {
      ...formData.value
    })

    if (response.data.success) {
      showToast('محدودیت‌های قیمت با موفقیت به‌روزرسانی شدند', 'success')
      if (response.data.data) {
        applyResponseData(response.data.data)
      } else {
        await fetchPriceLimits()
      }
    } else {
      error.value = 'خطا در ثبت اطلاعات'
    }
  } catch (err) {
    console.error('Pricing limits update error:', err)

    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors
    }

    error.value = err.response?.data?.message || 'خطا در ثبت اطلاعات'
  } finally {
    saving.value = false
  }
}

const handleSave = async () => {
  if (!validateForm()) {
    return
  }

  await submitPricingLimitsUpdate()
}

const fetchPriceLimits = async () => {
  try {
    loading.value = true
    error.value = null

    const response = await apiClient.get('/lands/feature-pricing-limits')

    if (response.data.success) {
      applyResponseData(response.data.data)
    } else {
      error.value = 'خطا در دریافت اطلاعات محدودیت قیمت'
    }
  } catch (err) {
    console.error('Pricing limits fetch error:', err)

    const clearPricingData = () => {
      priceLimits.value = null
      activityLogs.value = []
    }

    if (handleAuthListError(err, { onClear: clearPricingData, loading })) {
      return
    }

    error.value = getApiErrorMessage(err, 'خطا در بارگذاری اطلاعات')
    clearPricingData()
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchPriceLimits()
})
</script>
