<template>
  <div class="p-6 space-y-6" dir="rtl">
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-[var(--theme-text-primary)] mb-2">محدودیت‌های قیمت</h1>
      <p class="text-[var(--theme-text-secondary)]">تنظیم محدودیت‌های قیمت گذاری</p>
    </div>

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
      <!-- Price Limits History Table -->
      <Table
        v-if="priceLimits"
        :columns="priceLimitsColumns"
        :data="priceLimitsTableData"
        container-class="mb-6"
        row-number-header-class="text-center"
        row-number-cell-class="text-center"
      />

      <!-- No Price Limits Alert -->
      <Alert
        v-else-if="!loading && !priceLimits"
        variant="error"
        message="محدودیت قیمت گذاری برای این زمین ثبت نشده است."
        :dismissible="false"
      />

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
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import apiClient from '../../utils/api'
import { Input, Button, Alert, LoadingState, ErrorState, Table } from '../../components/ui'
import { useToast } from '../../composables/useToast'

const { showToast } = useToast()

const loading = ref(true)
const error = ref(null)
const priceLimits = ref(null)
const saving = ref(false)
const errors = ref({})

const priceLimitsColumns = [
  {
    key: 'updated_at_date',
    label: 'تاریخ تغییر',
    textSecondary: true,
    cellClass: 'text-right',
    headerClass: 'text-right'
  },
  {
    key: 'updated_at_time',
    label: 'ساعت تغییر',
    textSecondary: true,
    cellClass: 'text-right',
    headerClass: 'text-right'
  },
  {
    key: 'changer_name',
    label: 'نام تغییر دهنده',
    textSecondary: true,
    cellClass: 'text-right',
    headerClass: 'text-right'
  }
]

const priceLimitsTableData = computed(() => (priceLimits.value ? [priceLimits.value] : []))

const formData = ref({
  public_price_limit: 0,
  under_eighteen_price_limit: 0
})

const validateForm = () => {
  errors.value = {}
  error.value = null

  if (!formData.value.public_price_limit || formData.value.public_price_limit === '') {
    errors.value.public_price_limit = 'محدودیت قیمت گذاری عموم الزامی است'
    return false
  }

  if (!formData.value.under_eighteen_price_limit || formData.value.under_eighteen_price_limit === '') {
    errors.value.under_eighteen_price_limit = 'محدودیت قیمت گذاری زیر ۱۸ سال الزامی است'
    return false
  }

  return true
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
      await fetchPriceLimits()
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
      priceLimits.value = response.data.data.price_limits

      if (priceLimits.value) {
        formData.value = {
          public_price_limit: priceLimits.value.public_price_limit || 0,
          under_eighteen_price_limit: priceLimits.value.under_eighteen_price_limit || 0
        }

        priceLimits.value.updated_at_date = formatDate(priceLimits.value.updated_at)
        priceLimits.value.updated_at_time = formatTime(priceLimits.value.updated_at)
        priceLimits.value.changer_name = priceLimits.value.changer_name || '-'
      }
    } else {
      error.value = 'خطا در دریافت اطلاعات محدودیت قیمت'
    }
  } catch (err) {
    console.error('Pricing limits fetch error:', err)

    if (err.response && (err.response.status === 401 || err.response.status === 403)) {
      priceLimits.value = null
      loading.value = false
      return
    }

    error.value = err.response?.data?.message || 'خطا در بارگذاری اطلاعات'
    priceLimits.value = null
  } finally {
    loading.value = false
  }
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}/${month}/${day}`
}

const formatTime = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  const hours = String(date.getHours()).padStart(2, '0')
  const minutes = String(date.getMinutes()).padStart(2, '0')
  const seconds = String(date.getSeconds()).padStart(2, '0')
  return `${hours}:${minutes}:${seconds}`
}

onMounted(() => {
  fetchPriceLimits()
})
</script>
