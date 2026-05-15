<template>
  <div class="p-6 space-y-6" dir="rtl">
    <!-- Page Header -->
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
      <div>
        <h1 class="text-3xl font-bold text-[var(--theme-text-primary)] mb-2">
          مجوزهای سطح
        </h1>
        <p class="text-[var(--theme-text-secondary)]">
          مدیریت مجوزها و دسترسی‌های اختصاص‌یافته به سطح انتخاب‌شده در متاورس
        </p>
        <p v-if="levelLabel" class="mt-1 text-sm text-[var(--theme-text-muted)]">
          نام سطح:
          <span class="text-[var(--theme-text-primary)] font-medium">{{ levelLabel }}</span>
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
          :loading="saving || phoneVerification.sendingVerification.value"
          @click="handleSubmit"
        >
          {{ submitButtonLabel }}
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
      <div class="space-y-6">
        <Card
          v-for="section in licenseSections"
          :key="section.title"
          variant="glass"
          padding="xl"
          rounded="lg"
        >
          <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <h2 class="text-2xl font-semibold text-[var(--theme-text-primary)]">
                  {{ section.title }}
                </h2>
                <p class="text-sm text-[var(--theme-text-secondary)]">
                  {{ section.description }}
                </p>
              </div>
            </div>
          </template>

          <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div
              v-for="item in section.items"
              :key="item.key"
              class="relative overflow-hidden rounded-lg border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)]/60 p-4 transition-all duration-300 hover:shadow-[0_0_30px_rgba(124,58,237,0.2)]"
            >
              <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r" :class="item.gradient"></div>

              <div class="flex items-start justify-between gap-3">
                <div class="space-y-2">
                  <h3 class="text-lg font-medium text-[var(--theme-text-primary)]">
                    {{ item.label }}
                  </h3>
                  <p class="text-sm text-[var(--theme-text-secondary)]">
                    {{ item.helper }}
                  </p>
                </div>

                <Checkbox
                  v-model="form[item.key]"
                  :id="`license-${item.key}`"
                  :variant="item.checkboxVariant || 'primary'"
                />
              </div>

              <p
                v-if="errors[item.key]"
                class="mt-2 text-xs text-error"
              >
                {{ errors[item.key] }}
              </p>
            </div>
          </div>
        </Card>
      </div>
    </template>
    <PhoneVerificationModal :phone-verification="phoneVerification" title="تایید نهایی" />
  </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import apiClient from '../../utils/api'
import { Button, Card, Checkbox, LoadingState, ErrorState } from '../../components/ui'
import PhoneVerificationModal from '../../components/PhoneVerificationModal.vue'
import { useToast } from '../../composables/useToast'
import { usePhoneVerification, applyVerificationPayload } from '../../composables/usePhoneVerification'

const { showToast } = useToast()
const phoneVerification = usePhoneVerification()

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const saving = ref(false)
const error = ref(null)

const hasExistingLicenses = ref(false)

const defaultValues = Object.freeze({
  create_union: false,
  add_memeber_to_union: false,
  observation_license: false,
  gate_license: false,
  lawyer_license: false,
  city_counsile_entry: false,
  establish_special_residential_property: false,
  establish_property_on_surface: false,
  judge_entry: false,
  upload_image: false,
  delete_image: false,
  inter_level_general_points: false,
  inter_level_special_points: false,
  rent_out_satisfaction: false,
  access_to_answer_questions_unit: false,
  create_challenge_questions: false,
  upload_music: false
})

const form = reactive({
  ...defaultValues
})

const originalValues = ref({ ...defaultValues })

const errors = reactive({})

const licenseSections = [
  {
    title: 'اتحاد و حکمرانی',
    description: 'مجوزهای مرتبط با ساختارهای حکمرانی و فعالیت‌های اتحادی در متاورس.',
    items: [
      {
        key: 'create_union',
        label: 'مجوز تاسیس اتحاد',
        helper: 'امکان ایجاد اتحاد جدید برای شهروندان این سطح.',
        gradient: 'from-primary-500 to-secondary-500',
        checkboxVariant: 'secondary'
      },
      {
        key: 'add_memeber_to_union',
        label: 'مجوز عضوگیری اتحاد',
        helper: 'امکان دعوت و مدیریت اعضای جدید برای اتحاد.',
        gradient: 'from-secondary-500 to-primary-500',
        checkboxVariant: 'primary'
      },
      {
        key: 'observation_license',
        label: 'مجوز بازرسی',
        helper: 'دسترسی برای انجام بازرسی‌های رسمی در متاورس.',
        gradient: 'from-cyan-500 to-blue-500',
        checkboxVariant: 'primary'
      },
      {
        key: 'gate_license',
        label: 'مجوز تاسیس دروازه',
        helper: 'توانایی ایجاد دروازه‌های ارتباطی میان دنیاها.',
        gradient: 'from-indigo-500 to-purple-500',
        checkboxVariant: 'secondary'
      },
      {
        key: 'lawyer_license',
        label: 'مجوز وکالت',
        helper: 'اجازه ارائه خدمات حقوقی و نمایندگی رسمی.',
        gradient: 'from-purple-500 to-pink-500',
        checkboxVariant: 'secondary'
      },
      {
        key: 'city_counsile_entry',
        label: 'ورود به شورای شهر',
        helper: 'مجوز حضور و رای‌دهی در جلسات شورای شهر متاورس.',
        gradient: 'from-pink-500 to-rose-500',
        checkboxVariant: 'success'
      }
    ]
  },
  {
    title: 'املاک و مدیریت دارایی',
    description: 'کنترل بر دارایی‌ها، ملک‌ها و مدیریت نقاط سطح.',
    items: [
      {
        key: 'establish_special_residential_property',
        label: 'ملک مسکونی ویژه',
        helper: 'ساخت و مدیریت املاک مسکونی با ویژگی‌های خاص.',
        gradient: 'from-emerald-500 to-teal-500',
        checkboxVariant: 'success'
      },
      {
        key: 'establish_property_on_surface',
        label: 'ملک بر روی سطح',
        helper: 'امکان ایجاد سازه‌های ویژه بر روی سطوح خاص.',
        gradient: 'from-teal-500 to-cyan-500',
        checkboxVariant: 'secondary'
      },
      {
        key: 'judge_entry',
        label: 'ورود به جایگاه قضاوت',
        helper: 'پذیرش مسئولیت داوری در چالش‌ها و مناقشات.',
        gradient: 'from-amber-500 to-orange-500',
        checkboxVariant: 'warning'
      },
      {
        key: 'inter_level_general_points',
        label: 'ثبت موقعیت‌های عمومی سطح',
        helper: 'امکان تعیین و ثبت نقاط عمومی قابل‌دسترسی.',
        gradient: 'from-sky-500 to-blue-500',
        checkboxVariant: 'primary'
      },
      {
        key: 'inter_level_special_points',
        label: 'ثبت موقعیت‌های ویژه سطح',
        helper: 'تعیین نقاط ویژه با دسترسی محدود.',
        gradient: 'from-violet-500 to-fuchsia-500',
        checkboxVariant: 'secondary'
      },
      {
        key: 'rent_out_satisfaction',
        label: 'کرایه با واحد رضایت',
        helper: 'امکان اجاره دارایی‌ها با استفاده از واحد رضایت.',
        gradient: 'from-lime-500 to-green-500',
        checkboxVariant: 'success'
      }
    ]
  },
  {
    title: 'محتوا و تعاملات اجتماعی',
    description: 'مجوزهای مربوط به تولید محتوا، پاسخگویی و تعامل با جوامع.',
    items: [
      {
        key: 'upload_image',
        label: 'بارگذاری تصاویر آزاد',
        helper: 'اجازه بارگذاری و مدیریت تصاویر عمومی.',
        gradient: 'from-blue-500 to-cyan-500',
        checkboxVariant: 'primary'
      },
      {
        key: 'delete_image',
        label: 'حذف تصاویر آزاد',
        helper: 'توانایی حذف تصاویر بارگذاری‌شده.',
        gradient: 'from-rose-500 to-red-500',
        checkboxVariant: 'danger'
      },
      {
        key: 'access_to_answer_questions_unit',
        label: 'بخش پاسخ‌دهی به سوالات',
        helper: 'دسترسی به ماژول رسمی پاسخ‌دهی به سوالات.',
        gradient: 'from-indigo-500 to-blue-500',
        checkboxVariant: 'primary'
      },
      {
        key: 'create_challenge_questions',
        label: 'طرح سوال در چالش‌ها',
        helper: 'امکان طراحی و ارسال سوال برای چالش‌ها.',
        gradient: 'from-fuchsia-500 to-pink-500',
        checkboxVariant: 'secondary'
      },
      {
        key: 'upload_music',
        label: 'بارگذاری موزیک',
        helper: 'اضافه کردن موسیقی به صف انتظار یا رویدادها.',
        gradient: 'from-amber-500 to-yellow-500',
        checkboxVariant: 'warning'
      }
    ]
  }
]

const fieldKeys = Object.keys(defaultValues)

const levelId = computed(() => route.params?.levelId || null)
const levelLabel = computed(() => route.query?.name || route.query?.title || '')

const submitButtonLabel = computed(() => {
  if (phoneVerification.isProduction.value && !phoneVerification.isVerified.value) {
    return 'ارسال کد تایید'
  }
  return 'ثبت اطلاعات'
})

const resetErrors = () => {
  fieldKeys.forEach((key) => {
    errors[key] = ''
  })
}

const normalizeBoolean = (value) => {
  if (typeof value === 'boolean') return value
  if (typeof value === 'number') return value === 1
  if (typeof value === 'string') {
    if (value === '1' || value.toLowerCase() === 'true') return true
    if (value === '0' || value.toLowerCase() === 'false') return false
  }
  return false
}

const setFormValues = (data) => {
  fieldKeys.forEach((key) => {
    form[key] = normalizeBoolean(data?.[key])
  })

  originalValues.value = fieldKeys.reduce((acc, key) => {
    acc[key] = form[key]
    return acc
  }, {})

  resetErrors()
}

const validateForm = () => {
  resetErrors()
  let isValid = true

  fieldKeys.forEach((key) => {
    const value = form[key]
    if (typeof value !== 'boolean') {
      errors[key] = 'لطفاً مقدار را به صورت صحیح/غلط تنظیم کنید.'
      isValid = false
    }
  })

  if (!isValid) {
    return false
  }

  if (hasExistingLicenses.value) {
    const hasChanges = fieldKeys.some((key) => form[key] !== originalValues.value[key])
    if (!hasChanges) {
      return 'noChanges'
    }
  }

  return true
}

const buildPayload = () => fieldKeys.reduce((acc, key) => {
  acc[key] = Boolean(form[key])
  return acc
}, {})

const fetchLicenses = async () => {
  if (!levelId.value) {
    error.value = 'شناسه سطح نامعتبر است'
    loading.value = false
    return
  }

  try {
    loading.value = true
    error.value = null

    const response = await apiClient.get(`/levels/${levelId.value}/licenses`)

    if (response.data.success) {
      const licenses = response.data.data?.licenses || null
      hasExistingLicenses.value = Boolean(licenses)
      setFormValues(licenses || defaultValues)
    } else {
      error.value = response.data.message || 'خطا در دریافت اطلاعات مجوزها'
    }
  } catch (err) {
    console.error('Licenses fetch error:', err)

    if (err.response?.status === 404) {
      hasExistingLicenses.value = false
      setFormValues(defaultValues)
    } else if (err.response && (err.response.status === 401 || err.response.status === 403)) {
      return
    } else {
      error.value = err.response?.data?.message || 'خطا در دریافت اطلاعات مجوزها'
    }
  } finally {
    loading.value = false
  }
}

const persistLicenses = async () => {
  if (!levelId.value) {
    showToast('شناسه سطح نامعتبر است', 'error')
    return
  }

  const url = `/levels/${levelId.value}/licenses`
  const method = hasExistingLicenses.value ? 'put' : 'post'
  const payload = applyVerificationPayload(buildPayload(), phoneVerification.getSubmitPayload())

  try {
    saving.value = true

    const response = await apiClient[method](url, payload)

    if (response.data.success) {
      showToast(response.data.message || 'اطلاعات با موفقیت ثبت شد', 'success')
      phoneVerification.resetVerificationState()
      hasExistingLicenses.value = true

      const licenses = response.data.data?.licenses || payload
      setFormValues(licenses)
    } else {
      showToast(response.data.message || 'خطا در ثبت اطلاعات', 'error')
    }
  } catch (err) {
    console.error('Licenses submit error:', err)

    if (await phoneVerification.handleApiVerificationError(err)) {
      return
    }

    if (err.response?.status === 422 && err.response?.data?.errors) {
      const validationErrors = err.response.data.errors

      Object.keys(validationErrors).forEach((field) => {
        const message = Array.isArray(validationErrors[field]) ? validationErrors[field][0] : validationErrors[field]

        if (field !== 'phone_verification' && errors[field] !== undefined) {
          errors[field] = message
        }
      })
    } else {
      showToast(err.response?.data?.message || 'خطا در ثبت اطلاعات', 'error')
    }
  } finally {
    saving.value = false
  }
}

const handleSubmit = async () => {
  if (saving.value) return

  const validationResult = validateForm()
  if (validationResult !== true) {
    if (validationResult === 'noChanges') {
      showToast('تغییری برای ثبت وجود ندارد.', 'warning')
    } else {
      showToast('لطفاً خطاهای فرم را برطرف کرده و دوباره تلاش کنید.', 'warning')
    }
    return
  }

  if (phoneVerification.isProduction.value && !phoneVerification.isVerified.value) {
    await phoneVerification.beginVerifyForSubmit()
    return
  }

  await persistLicenses()
}

const goBackToListing = () => {
  router.push({ name: 'levels-listing' })
}

onMounted(() => {
  fetchLicenses()
})
</script>

<style scoped>
.text-error {
  color: var(--color-error, #EF4444);
}
</style>


