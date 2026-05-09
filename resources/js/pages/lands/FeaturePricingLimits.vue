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

    <!-- Verification Dialog -->
    <Modal
      :model-value="showVerificationDialog"
      @update:model-value="handleCloseVerificationDialog"
      @close="handleCloseVerificationDialog"
      title="تایید نهایی"
      size="md"
    >
      <div dir="rtl">
        <VerificationForm
          ref="verificationFormRef"
          :auto-start="true"
          @verified="handleAutoVerifyAndSubmit"
        />
      </div>
    </Modal>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue'
import apiClient from '../../utils/api'
import { Modal, Input, Button, Alert, LoadingState, ErrorState, Table } from '../../components/ui'
import VerificationForm from '../../components/VerificationForm.vue'
import { useToast } from '../../composables/useToast'

const { showToast } = useToast()

const loading = ref(true)
const error = ref(null)
const priceLimits = ref(null)
const saving = ref(false)
const errors = ref({})
const verificationFormRef = ref(null)
const showVerificationDialog = ref(false)

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

const isProduction = computed(() => {
  // Check Laravel's APP_ENV from meta tag, fallback to Vite mode
  const metaEnv = document.querySelector('meta[name="app-env"]')?.getAttribute('content')
  return metaEnv === 'production' || import.meta.env.MODE === 'production'
})

const sendVerificationCode = async () => {
  try {
    // Send verification code when submit button is clicked
    const response = await apiClient.post('/send-verification-sms')

    if (response.data.success) {
      // Show dialog after code is sent
      showVerificationDialog.value = true
      return true
    } else {
      showToast('خطا در ارسال کد تایید', 'error')
      return false
    }
  } catch (err) {
    console.error('Verification SMS send error:', err)
    showToast(err.response?.data?.message || 'خطا در ارسال کد تایید', 'error')
    return false
  } finally {
    // Reset saving state after verification code is sent (success or failure)
    // The actual submission will set it to true again when submitting
    saving.value = false
  }
}

const submitPricingLimitsUpdate = async (verificationData = {}) => {
  try {
    saving.value = true
    error.value = null
    errors.value = {}

    const response = await apiClient.post('/lands/feature-pricing-limits', {
      ...formData.value,
      ...verificationData
    })

    if (response.data.success) {
      showToast('محدودیت‌های قیمت با موفقیت به‌روزرسانی شدند', 'success')
      showVerificationDialog.value = false
      // Reset verification form errors on success
      if (verificationFormRef.value && verificationFormRef.value.setErrors) {
        verificationFormRef.value.setErrors({})
      }
      await fetchPriceLimits()
    } else {
      error.value = 'خطا در ثبت اطلاعات'
    }
  } catch (err) {
    console.error('Pricing limits update error:', err)

    // Handle validation errors
    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors
    }

    error.value = err.response?.data?.message || 'خطا در ثبت اطلاعات'

    // Extract validation errors and display them on the form fields
    if (err.response?.data?.errors && verificationFormRef.value && verificationFormRef.value.setErrors) {
      const validationErrors = {}

      // Map Laravel validation errors to form fields
      if (err.response.data.errors.phone_verification) {
        validationErrors.phone_verification = Array.isArray(err.response.data.errors.phone_verification)
          ? err.response.data.errors.phone_verification[0]
          : err.response.data.errors.phone_verification
      }

      // Set errors on the verification form
      verificationFormRef.value.setErrors(validationErrors)
    }
  } finally {
    saving.value = false
  }
}

const handleSave = async () => {
  // Reset errors
  errors.value = {}
  error.value = null

  // Basic validation
  if (!formData.value.public_price_limit || formData.value.public_price_limit === '') {
    errors.value.public_price_limit = 'محدودیت قیمت گذاری عموم الزامی است'
    return
  }

  if (!formData.value.under_eighteen_price_limit || formData.value.under_eighteen_price_limit === '') {
    errors.value.under_eighteen_price_limit = 'محدودیت قیمت گذاری زیر ۱۸ سال الزامی است'
    return
  }

  // Set loading state immediately when button is clicked
  saving.value = true

  // In production: send verification code first, then show dialog
  if (isProduction.value) {
    // Send verification code and show dialog
    const codeSent = await sendVerificationCode()
    if (!codeSent) {
      // Error already handled in sendVerificationCode, state reset in finally block
      return
    }
    // Dialog will be shown, user enters code and password, then clicks submit
    // Note: saving.value is reset to false in sendVerificationCode finally block
    // It will be set to true again when submitPricingLimitsUpdate is called
  } else {
    // In non-production: submit directly without verification
    await submitPricingLimitsUpdate()
  }
}

const handleAutoVerifyAndSubmit = async (verificationData) => {
  // Auto-submit when verification form emits verified event (all 6 digits entered)
  // Prevent multiple submissions
  if (saving.value) {
    return
  }

  if (!verificationFormRef.value) {
    showToast('خطا در تایید', 'error')
    return
  }

  // Get verification data and submit
  const data = verificationData || verificationFormRef.value.getData()
  await submitPricingLimitsUpdate(data)
}

const handleCloseVerificationDialog = () => {
  // Clear errors when closing the dialog
  if (verificationFormRef.value && verificationFormRef.value.setErrors) {
    verificationFormRef.value.setErrors({})
  }
  showVerificationDialog.value = false
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

        // Format for history table (matching Blade view structure)
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

watch(() => showVerificationDialog, async (newVal) => {
  if (newVal) {
    // Wait for component to mount and ensure timer starts
    await nextTick()

    // Give it a delay to ensure component is fully ready
    setTimeout(() => {
      if (verificationFormRef.value && verificationFormRef.value.startTimer) {
        verificationFormRef.value.startTimer()
      }
    }, 100)

    // Focus the first input after modal is fully opened
    // Modal transition is 300ms, so wait a bit longer to ensure it's complete
    setTimeout(() => {
      if (verificationFormRef.value && verificationFormRef.value.focusFirstInput) {
        verificationFormRef.value.focusFirstInput()
      }
    }, 400)

    // Retry focus after modal animation completes (additional safety)
    setTimeout(() => {
      if (verificationFormRef.value && verificationFormRef.value.focusFirstInput) {
        verificationFormRef.value.focusFirstInput()
      }
    }, 600)
  } else {
    // Reset form when dialog closes
    if (verificationFormRef.value && verificationFormRef.value.reset) {
      verificationFormRef.value.reset()
    }
  }
})

onMounted(() => {
  fetchPriceLimits()
})
</script>

<style scoped>
/* Additional styles if needed */
</style>

