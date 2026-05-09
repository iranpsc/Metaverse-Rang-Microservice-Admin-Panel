<template>
  <div class="p-6 space-y-6" dir="rtl">
    <!-- Page Header -->
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
      <div>
        <h1 class="text-3xl font-bold text-[var(--theme-text-primary)] mb-2">
          پاداش سطح
        </h1>
        <p class="text-[var(--theme-text-secondary)]">
          مدیریت امتیازات و جوایز اختصاص‌یافته به سطح انتخاب‌شده در متاورس
        </p>
        <p v-if="levelLabel" class="mt-1 text-sm text-[var(--theme-text-muted)]">
          نام سطح: <span class="text-[var(--theme-text-primary)] font-medium">{{ levelLabel }}</span>
        </p>
      </div>

      <div class="flex items-center gap-3">
        <Button
          variant="glass"
          rounded="full"
          @click="goBackToListing"
        >
          بازگشت به مدیریت سطوح
        </Button>

        <Button
          variant="primary"
          rounded="full"
          :loading="saving"
          @click="handleSubmit"
        >
          ثبت اطلاعات
        </Button>
      </div>
    </div>

    <LoadingState v-if="loading" />

    <ErrorState
      v-else-if="error"
      :message="error"
      variant="error"
    />

    <template v-else>
      <!-- Level Prize Form -->
      <Card
        variant="glass"
        padding="xl"
        rounded="lg"
      >
        <template #header>
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
            <div>
              <h2 class="text-2xl font-semibold text-[var(--theme-text-primary)]">
                مقادیر پاداش
              </h2>
              <p class="text-sm text-[var(--theme-text-secondary)]">
                مقادیر را بر اساس سیاست‌های متاورس و دستاوردهای سطح تنظیم کنید.
              </p>
            </div>
          </div>
        </template>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
          <Input
            v-model.number="form.psc"
            label="دریافت PSC"
            type="number"
            min="0"
            step="1"
            required
            :error="errors.psc"
            helper-text="واحد: PSC (عدد صحیح)"
          />

          <Input
            v-model.number="form.yellow"
            label="دریافت رنگ زرد"
            type="number"
            min="0"
            step="1"
            required
            :error="errors.yellow"
            helper-text="واحد: تعداد واحدهای رنگ زرد"
          />

          <Input
            v-model.number="form.blue"
            label="دریافت رنگ آبی"
            type="number"
            min="0"
            step="1"
            required
            :error="errors.blue"
            helper-text="واحد: تعداد واحدهای رنگ آبی"
          />

          <Input
            v-model.number="form.red"
            label="دریافت رنگ قرمز"
            type="number"
            min="0"
            step="1"
            required
            :error="errors.red"
            helper-text="واحد: تعداد واحدهای رنگ قرمز"
          />

          <Input
            v-model="form.satisfaction"
            label="واحد رضایت"
            type="number"
            min="0"
            step="0.0001"
            required
            :error="errors.satisfaction"
            helper-text="عدد اعشاری تا چهار رقم اعشار"
          />

          <Input
            v-model.number="form.effect"
            label="دریافت حدتاثیر"
            type="number"
            min="0"
            step="1"
            required
            :error="errors.effect"
            helper-text="واحد: امتیاز اثرگذاری"
          />
        </div>
      </Card>

    </template>

    <Modal
      :model-value="showVerificationDialog"
      @update:model-value="handleModalToggle"
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

      <template #footer>
        <div class="flex justify-end gap-3" dir="rtl">
          <Button
            variant="primary"
            rounded="full"
            :loading="saving"
            @click="handleVerificationSubmit"
          >
            ثبت نهایی
          </Button>
          <Button
            variant="danger"
            rounded="full"
            :disabled="saving"
            @click="handleCloseVerificationDialog"
          >
            انصراف
          </Button>
        </div>
      </template>
    </Modal>

  </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import apiClient from '../../utils/api'
import { Button, Card, Input, Alert, Modal, LoadingState, ErrorState } from '../../components/ui'
import VerificationForm from '../../components/VerificationForm.vue'
import { useToast } from '../../composables/useToast'

const { showToast } = useToast()

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const saving = ref(false)
const error = ref(null)

const showVerificationDialog = ref(false)
const verificationFormRef = ref(null)

const hasExistingPrize = ref(false)
const pendingPayload = ref(null)

const defaultValues = Object.freeze({
  psc: 0,
  yellow: 0,
  blue: 0,
  red: 0,
  satisfaction: 0,
  effect: 0
})

const form = reactive({
  psc: defaultValues.psc,
  yellow: defaultValues.yellow,
  blue: defaultValues.blue,
  red: defaultValues.red,
  satisfaction: defaultValues.satisfaction,
  effect: defaultValues.effect
})

const originalValues = ref({ ...defaultValues })

const errors = reactive({
  psc: '',
  yellow: '',
  blue: '',
  red: '',
  satisfaction: '',
  effect: ''
})

const levelId = computed(() => route.params?.levelId || null)
const levelLabel = computed(() => route.query?.name || route.query?.title || '')

const isProduction = computed(() => {
  const metaEnv = document.querySelector('meta[name="app-env"]')?.getAttribute('content')
  return metaEnv === 'production' || import.meta.env.MODE === 'production'
})

const resetErrors = () => {
  Object.keys(errors).forEach((key) => {
    errors[key] = ''
  })
}

const normalizeValue = (key, value) => {
  if (value === null || value === undefined || value === '') {
    return defaultValues[key]
  }
  const numericValue = Number(value)
  return Number.isNaN(numericValue) ? defaultValues[key] : numericValue
}

const setFormValues = (data) => {
  Object.keys(defaultValues).forEach((key) => {
    form[key] = normalizeValue(key, data?.[key])
  })

  originalValues.value = Object.keys(defaultValues).reduce((acc, key) => {
    acc[key] = form[key]
    return acc
  }, {})

  resetErrors()
}

const validateNumericField = (field, label, { allowDecimal = false } = {}) => {
  const value = form[field]

  if (value === null || value === undefined || value === '') {
    errors[field] = `${label} الزامی است`
    return false
  }

  const numericValue = Number(value)
  const isValidNumber = !Number.isNaN(numericValue) && numericValue >= 0

  if (!isValidNumber) {
    errors[field] = `${label} باید عددی بزرگ‌تر یا مساوی صفر باشد`
    return false
  }

  if (!allowDecimal && !Number.isInteger(numericValue)) {
    errors[field] = `${label} باید عدد صحیح باشد`
    return false
  }

  if (allowDecimal) {
    const decimalPattern = /^\d+(\.\d{1,4})?$/
    if (!decimalPattern.test(String(value))) {
      errors[field] = `${label} می‌تواند حداکثر چهار رقم اعشار داشته باشد`
      return false
    }
  }

  errors[field] = ''
  return true
}

const validateForm = () => {
  resetErrors()

  const validators = [
    validateNumericField('psc', 'دریافت PSC'),
    validateNumericField('yellow', 'دریافت رنگ زرد'),
    validateNumericField('blue', 'دریافت رنگ آبی'),
    validateNumericField('red', 'دریافت رنگ قرمز'),
    validateNumericField('effect', 'دریافت حدتاثیر'),
    validateNumericField('satisfaction', 'واحد رضایت', { allowDecimal: true })
  ]

  return validators.every(Boolean)
}

const buildPayload = () => ({
  psc: Number(form.psc),
  yellow: Number(form.yellow),
  blue: Number(form.blue),
  red: Number(form.red),
  satisfaction: Number(form.satisfaction),
  effect: Number(form.effect)
})

const fetchPrize = async () => {
  if (!levelId.value) {
    error.value = 'شناسه سطح نامعتبر است'
    loading.value = false
    return
  }

  try {
    loading.value = true
    error.value = null

    const response = await apiClient.get(`/levels/${levelId.value}/prize`)

    if (response.data.success) {
      const prize = response.data.data?.prize || null
      hasExistingPrize.value = Boolean(prize)
      setFormValues(prize)
    } else {
      error.value = response.data.message || 'خطا در دریافت اطلاعات پاداش'
    }
  } catch (err) {
    console.error('Prize fetch error:', err)

    if (err.response?.status === 404) {
      hasExistingPrize.value = false
      setFormValues(null)
    } else if (err.response && (err.response.status === 401 || err.response.status === 403)) {
      // Authorization handled globally
      return
    } else {
      error.value = err.response?.data?.message || 'خطا در دریافت اطلاعات پاداش'
    }
  } finally {
    loading.value = false
  }
}

const sendVerificationCode = async () => {
  try {
    const response = await apiClient.post('/send-verification-sms')

    if (response.data.success) {
      showVerificationDialog.value = true
      await nextTick()
      verificationFormRef.value?.reset?.()
      verificationFormRef.value?.setErrors?.({})
      verificationFormRef.value?.startTimer?.()
      verificationFormRef.value?.focusFirstInput?.()
      return true
    }

    showToast(response.data.message || 'خطا در ارسال کد تایید', 'error')
    return false
  } catch (err) {
    console.error('Verification SMS send error:', err)
    showToast(err.response?.data?.message || 'خطا در ارسال کد تایید', 'error')
    return false
  }
}

const persistPrize = async (payload) => {
  if (!levelId.value) {
    showToast('شناسه سطح نامعتبر است', 'error')
    return
  }

  const url = `/levels/${levelId.value}/prize`
  const method = hasExistingPrize.value ? 'put' : 'post'

  try {
    saving.value = true

    const response = await apiClient[method](url, payload)

    if (response.data.success) {
      showToast(response.data.message || 'اطلاعات با موفقیت ثبت شد', 'success')
      showVerificationDialog.value = false
      pendingPayload.value = null
      hasExistingPrize.value = true

      const prize = response.data.data?.prize || payload
      setFormValues(prize)
      verificationFormRef.value?.setErrors?.({})
      verificationFormRef.value?.reset?.()
    } else {
      showToast(response.data.message || 'خطا در ثبت اطلاعات', 'error')
    }
  } catch (err) {
    console.error('Prize submit error:', err)

    if (err.response?.status === 422 && err.response?.data?.errors) {
      const validationErrors = err.response.data.errors

      const verificationErrors = {}

      Object.keys(validationErrors).forEach((field) => {
        const message = Array.isArray(validationErrors[field]) ? validationErrors[field][0] : validationErrors[field]

        if (field === 'phone_verification') {
          verificationErrors.phone_verification = message
        } else if (errors[field] !== undefined) {
          errors[field] = message
        }
      })

      if (Object.keys(verificationErrors).length > 0) {
        verificationFormRef.value?.setErrors?.(verificationErrors)
      }
    } else {
      showToast(err.response?.data?.message || 'خطا در ثبت اطلاعات', 'error')
    }
  } finally {
    saving.value = false
  }
}

const handleSubmit = async () => {
  if (saving.value) return

  const isValid = validateForm()
  if (!isValid) {
    showToast('لطفاً خطاهای فرم را برطرف کنید و دوباره تلاش نمایید.', 'warning')
    return
  }

  const payload = buildPayload()

  if (!isProduction.value) {
    await persistPrize(payload)
    return
  }

  pendingPayload.value = payload

  saving.value = true
  const codeSent = await sendVerificationCode()
  saving.value = false

  if (!codeSent) {
    pendingPayload.value = null
  }
}

const handleAutoVerifyAndSubmit = async (verificationData) => {
  if (saving.value) {
    return
  }

  if (!pendingPayload.value) {
    showToast('اطلاعاتی برای ثبت موجود نیست. لطفاً مجدداً تلاش کنید.', 'error')
    handleCloseVerificationDialog()
    return
  }

  const payload = {
    ...pendingPayload.value,
    ...verificationData
  }

  await persistPrize(payload)
}

const handleVerificationSubmit = async () => {
  if (saving.value || !verificationFormRef.value) {
    return
  }

  const isValid = await verificationFormRef.value.validate()
  if (!isValid) {
    return
  }

  const verificationData = verificationFormRef.value.getData() || {}

  if (!pendingPayload.value) {
    showToast('اطلاعاتی برای ثبت موجود نیست. لطفاً مجدداً تلاش کنید.', 'error')
    handleCloseVerificationDialog()
    return
  }

  const payload = {
    ...pendingPayload.value,
    ...verificationData
  }

  await persistPrize(payload)
}

const handleCloseVerificationDialog = () => {
  if (saving.value) {
    return
  }

  verificationFormRef.value?.setErrors?.({})
  verificationFormRef.value?.reset?.()
  showVerificationDialog.value = false
  pendingPayload.value = null
}

const handleModalToggle = (value) => {
  if (!value) {
    handleCloseVerificationDialog()
  } else {
    showVerificationDialog.value = true
  }
}

const goBackToListing = () => {
  router.push({ name: 'levels-listing' })
}

onMounted(() => {
  fetchPrize()
})
</script>

