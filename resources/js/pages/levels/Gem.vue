<template>
  <div class="p-6 space-y-6" dir="rtl">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
      <div>
        <h1 class="text-3xl font-bold text-[var(--theme-text-primary)] mb-2">
          نگین سطح
        </h1>
        <p class="text-[var(--theme-text-secondary)]">
          مدیریت ویژگی‌ها، فایل‌ها و رمزگذاری نگین اختصاص‌یافته به سطح انتخاب‌شده.
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
        <Card variant="glass" padding="xl" rounded="lg">
          <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <h2 class="text-2xl font-semibold text-[var(--theme-text-primary)]">
                  مشخصات اصلی نگین
                </h2>
                <p class="text-sm text-[var(--theme-text-secondary)]">
                  نام، توصیف و ویژگی‌های بنیادی نگین را تنظیم کنید.
                </p>
              </div>
            </div>
          </template>

          <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <Input
              v-model="form.name"
              label="نام نگین"
              :error="errors.name"
              required
            />

            <Input
              v-model="form.thread"
              label="ترد"
              :error="errors.thread"
              required
            />

            <Input
              v-model="form.color"
              label="رنگ غالب"
              placeholder="مثال: #7C3AED"
              :error="errors.color"
              required
            />

            <Input
              v-model.number="form.points"
              label="امتیازات"
              type="number"
              min="0"
              step="1"
              :error="errors.points"
              required
            />

            <Input
              v-model="form.volume"
              label="حجم (MB)"
              type="number"
              min="0"
              step="0.001"
              :error="errors.volume"
              required
            />

            <Input
              v-model.number="form.lines"
              label="تعداد خطوط"
              type="number"
              min="0"
              step="1"
              :error="errors.lines"
              required
            />
          </div>

          <div class="mt-6">
            <label class="block text-sm font-medium text-[var(--theme-text-primary)] mb-2">
              توضیحات نگین
              <span class="text-error">*</span>
            </label>
            <div :class="['rich-editor-container', errors.description && 'has-error']">
              <Editor
                v-model="form.description"
                editorStyle="height: 220px"
                :editorOptions="editorOptions"
                placeholder="توضیحات کامل نگین را وارد کنید"
              />
            </div>
            <p v-if="errors.description" class="mt-1.5 text-xs text-error">{{ errors.description }}</p>
          </div>
        </Card>

        <Card variant="glass" padding="xl" rounded="lg">
          <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <h2 class="text-2xl font-semibold text-[var(--theme-text-primary)]">
                  تنظیمات امنیتی و متادیتا
                </h2>
                <p class="text-sm text-[var(--theme-text-secondary)]">
                  طراح نگین و وضعیت رمزگذاری/انیمیشن را مشخص کنید.
                </p>
              </div>
            </div>
          </template>

          <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <Input
              v-model="form.designer"
              label="طراح نگین"
              :error="errors.designer"
              required
            />

            <div class="flex items-center justify-between rounded-lg border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)]/60 p-4">
              <div>
                <h3 class="text-lg font-medium text-[var(--theme-text-primary)]">رمزگذاری شده</h3>
                <p class="text-sm text-[var(--theme-text-secondary)]">فعال‌سازی رمزگذاری برای حفاظت از داده‌های نگین.</p>
              </div>
              <Checkbox v-model="form.encryption" variant="secondary" />
            </div>

            <div class="flex items-center justify-between rounded-lg border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)]/60 p-4">
              <div>
                <h3 class="text-lg font-medium text-[var(--theme-text-primary)]">دارای انیمیشن</h3>
                <p class="text-sm text-[var(--theme-text-secondary)]">اگر فایل نگین دارای انیمیشن است این گزینه را فعال کنید.</p>
              </div>
              <Checkbox v-model="form.has_animation" variant="primary" />
            </div>
          </div>
        </Card>

        <Card variant="glass" padding="xl" rounded="lg">
          <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <h2 class="text-2xl font-semibold text-[var(--theme-text-primary)]">
                  مدیریت فایل‌ها
                </h2>
                <p class="text-sm text-[var(--theme-text-secondary)]">
                  فایل‌های نمایشی نگین را بارگذاری یا جایگزین کنید. عدم انتخاب فایل جدید به معنی حفظ فایل فعلی است.
                </p>
              </div>
            </div>
          </template>

          <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="space-y-2">
              <FileInput
                v-model="form.png_file"
                label="فایل PNG"
                accept="image/png"
                placeholder="انتخاب تصویر PNG"
                helper-text="حداکثر 5 مگابایت"
                :error="errors.png_file"
              />
              <ExistingFileHint :url="existingFiles.png_file" label="تصویر فعلی" />
            </div>

            <div class="space-y-2">
              <FileInput
                v-model="form.fbx_file"
                label="فایل FBX"
                accept=".fbx"
                placeholder="انتخاب مدل FBX"
                helper-text="حداکثر 300 مگابایت"
                :error="errors.fbx_file"
              />
              <ExistingFileHint :url="existingFiles.fbx_file" label="مدل فعلی" />
            </div>
          </div>
        </Card>
      </div>
    </template>
    <PhoneVerificationModal :phone-verification="phoneVerification" title="تایید نهایی" />
  </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import apiClient from '../../utils/api'
import { Button, Card, Checkbox, Input, LoadingState, ErrorState, FileInput } from '../../components/ui'
import Editor from 'primevue/editor'
import PhoneVerificationModal from '../../components/PhoneVerificationModal.vue'
import ExistingFileHint from '../../components/levels/ExistingFileHint.vue'
import { useToast } from '../../composables/useToast'
import { usePhoneVerification, applyVerificationPayload } from '../../composables/usePhoneVerification'

const { showToast } = useToast()
const phoneVerification = usePhoneVerification()

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const saving = ref(false)
const error = ref(null)

const hasExistingGem = ref(false)

const defaultValues = Object.freeze({
  name: '',
  description: '',
  thread: '',
  points: 0,
  volume: 0,
  color: '',
  encryption: false,
  designer: '',
  has_animation: false,
  lines: 0
})

const form = reactive({
  ...defaultValues,
  png_file: null,
  fbx_file: null
})

const existingFiles = reactive({
  png_file: null,
  fbx_file: null
})

const originalValues = ref({ ...defaultValues })

const errors = reactive({
  name: '',
  description: '',
  thread: '',
  points: '',
  volume: '',
  color: '',
  encryption: '',
  designer: '',
  has_animation: '',
  lines: '',
  png_file: '',
  fbx_file: ''
})

const fieldKeys = Object.keys(defaultValues)

const levelId = computed(() => route.params?.levelId || null)
const levelLabel = computed(() => route.query?.name || route.query?.title || '')

const submitButtonLabel = computed(() => {
  if (phoneVerification.isProduction.value && !phoneVerification.isVerified.value) {
    return 'ارسال کد تایید'
  }
  return 'ثبت اطلاعات'
})

const editorOptions = Object.freeze({
  modules: {
    toolbar: [
      [{ header: [1, 2, false] }],
      [{ font: [] }],
      [{ size: [] }],
      ['bold', 'italic', 'underline', 'strike'],
      [{ color: [] }, { background: [] }],
      [{ list: 'ordered' }, { list: 'bullet' }],
      [{ indent: '-1' }, { indent: '+1' }],
      [{ direction: 'rtl' }],
      [{ align: [] }],
      ['clean']
    ]
  },
  theme: 'snow',
  formats: ['direction']
})

const resetErrors = () => {
  Object.keys(errors).forEach((key) => {
    errors[key] = ''
  })
}

const normalizeBoolean = (value) => {
  if (typeof value === 'boolean') return value
  if (typeof value === 'number') return value === 1
  if (typeof value === 'string') {
    const lowered = value.toLowerCase()
    if (lowered === '1' || lowered === 'true') return true
    if (lowered === '0' || lowered === 'false') return false
  }
  return false
}

const setFormValues = (data) => {
  const payload = data || {}

  form.name = payload.name || ''
  form.description = payload.description || ''
  form.thread = payload.thread || ''
  form.points = payload.points != null ? Number(payload.points) : 0
  form.volume = payload.volume != null ? Number(payload.volume) : 0
  form.color = payload.color || ''
  form.encryption = normalizeBoolean(payload.encryption)
  form.designer = payload.designer || ''
  form.has_animation = normalizeBoolean(payload.has_animation)
  form.lines = payload.lines != null ? Number(payload.lines) : 0

  form.png_file = null
  form.fbx_file = null

  existingFiles.png_file = payload.png_file || null
  existingFiles.fbx_file = payload.fbx_file || null

  originalValues.value = fieldKeys.reduce((acc, key) => {
    acc[key] = form[key]
    return acc
  }, {})

  resetErrors()
}

const sanitizeRichText = (value) => {
  if (!value) return ''
  return value
    .replace(/<[^>]*>/g, ' ')
    .replace(/&nbsp;/gi, ' ')
    .replace(/\s+/g, ' ')
    .trim()
}

const validateString = (field, label, { max = null, required = true, richText = false } = {}) => {
  const rawValue = (form[field] ?? '').toString()
  const value = richText ? sanitizeRichText(rawValue) : rawValue.trim()

  if (required && !value) {
    errors[field] = `${label} الزامی است`
    return false
  }

  if (max && value.length > max) {
    errors[field] = `${label} می‌تواند حداکثر ${max} کاراکتر داشته باشد`
    return false
  }

  errors[field] = ''
  return true
}

const validateInteger = (field, label, { min = 0 } = {}) => {
  const value = form[field]
  if (value === null || value === undefined || value === '') {
    errors[field] = `${label} الزامی است`
    return false
  }
  const parsed = Number(value)
  if (!Number.isInteger(parsed) || parsed < min) {
    errors[field] = `${label} باید عدد صحیح بزرگ‌تر یا مساوی ${min} باشد`
    return false
  }
  errors[field] = ''
  return true
}

const validateDecimal = (field, label, { min = 0, precision = 3 } = {}) => {
  const value = form[field]
  if (value === null || value === undefined || value === '') {
    errors[field] = `${label} الزامی است`
    return false
  }
  const numericValue = Number(value)
  if (Number.isNaN(numericValue) || numericValue < min) {
    errors[field] = `${label} باید عددی بزرگ‌تر یا مساوی ${min} باشد`
    return false
  }
  const decimalPattern = new RegExp(`^\\d+(\\.\\d{1,${precision}})?$`)
  if (!decimalPattern.test(String(value))) {
    errors[field] = `${label} می‌تواند حداکثر ${precision} رقم اعشار داشته باشد`
    return false
  }
  errors[field] = ''
  return true
}

const validateFile = (field, file, { maxSizeMB, allowedTypes = [], allowedExtensions = [] }) => {
  if (!file) {
    errors[field] = ''
    return true
  }

  if (maxSizeMB && file.size > maxSizeMB * 1024 * 1024) {
    errors[field] = `حجم فایل باید حداکثر ${maxSizeMB} مگابایت باشد`
    return false
  }

  if (allowedTypes.length > 0 && file.type && !allowedTypes.includes(file.type)) {
    errors[field] = 'فرمت فایل مجاز نیست'
    return false
  }

  if (allowedExtensions.length > 0) {
    const lowerName = file.name?.toLowerCase() || ''
    const isAllowed = allowedExtensions.some((ext) => lowerName.endsWith(ext))
    if (!isAllowed) {
      errors[field] = 'پسوند فایل مجاز نیست'
      return false
    }
  }

  errors[field] = ''
  return true
}

const hasFormChanges = () => {
  const nonFileChanged = fieldKeys.some((key) => form[key] !== originalValues.value[key])
  const fileChanged = Boolean(form.png_file || form.fbx_file)
  return nonFileChanged || fileChanged
}

const validateForm = () => {
  resetErrors()

  const validators = [
    validateString('name', 'نام نگین', { max: 255 }),
    validateString('description', 'توضیحات', { max: 6000, richText: true }),
    validateString('thread', 'ترد', { max: 255 }),
    validateInteger('points', 'امتیازات'),
    validateDecimal('volume', 'حجم', { precision: 3 }),
    validateString('color', 'رنگ', { max: 255 }),
    validateString('designer', 'طراح', { max: 255 }),
    validateInteger('lines', 'تعداد خطوط')
  ]

  const filesValid = [
    validateFile('png_file', form.png_file, { maxSizeMB: 5, allowedTypes: ['image/png'], allowedExtensions: ['.png'] }),
    validateFile('fbx_file', form.fbx_file, { maxSizeMB: 300, allowedExtensions: ['.fbx'] })
  ]

  const allValid = [...validators, ...filesValid].every(Boolean)

  if (!allValid) {
    return false
  }

  if (hasExistingGem.value && !hasFormChanges()) {
    return 'noChanges'
  }

  return true
}

const buildPayload = () => ({
  fields: {
    name: form.name.trim(),
    description: form.description.toString().trim(),
    thread: form.thread.trim(),
    points: Number(form.points),
    volume: Number(form.volume),
    color: form.color.trim(),
    encryption: Boolean(form.encryption),
    designer: form.designer.trim(),
    has_animation: Boolean(form.has_animation),
    lines: Number(form.lines)
  },
  files: {
    png_file: form.png_file,
    fbx_file: form.fbx_file
  }
})

const appendFormData = (payload, verificationData = {}) => {
  const formData = new FormData()

  Object.entries(payload.fields).forEach(([key, value]) => {
    if (typeof value === 'boolean') {
      formData.append(key, value ? '1' : '0')
    } else if (value !== null && value !== undefined) {
      formData.append(key, value)
    }
  })

  Object.entries(payload.files).forEach(([key, file]) => {
    if (file instanceof File) {
      formData.append(key, file)
    }
  })

  if (hasExistingGem.value) {
    formData.append('_method', 'PUT')
  }

  return formData
}

const fetchGem = async () => {
  if (!levelId.value) {
    error.value = 'شناسه سطح نامعتبر است'
    loading.value = false
    return
  }

  try {
    loading.value = true
    error.value = null

    const response = await apiClient.get(`/levels/${levelId.value}/gem`)

    if (response.data.success) {
      const gem = response.data.data?.gem || null
      hasExistingGem.value = Boolean(gem)
      setFormValues(gem)
    } else {
      error.value = response.data.message || 'خطا در دریافت اطلاعات نگین'
    }
  } catch (err) {
    console.error('Gem fetch error:', err)

    if (err.response?.status === 404) {
      hasExistingGem.value = false
      setFormValues(null)
    } else if (err.response && (err.response.status === 401 || err.response.status === 403)) {
      return
    } else {
      error.value = err.response?.data?.message || 'خطا در دریافت اطلاعات نگین'
    }
  } finally {
    loading.value = false
  }
}

const persistGem = async () => {
  if (!levelId.value) {
    showToast('شناسه سطح نامعتبر است', 'error')
    return
  }

  const formData = applyVerificationPayload(
    appendFormData(buildPayload(), {}),
    phoneVerification.getSubmitPayload()
  )
  const url = `/levels/${levelId.value}/gem`

  try {
    saving.value = true

    const response = await apiClient.post(url, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    if (response.data.success) {
      showToast(response.data.message || 'اطلاعات با موفقیت ثبت شد', 'success')
      phoneVerification.resetVerificationState()
      hasExistingGem.value = true

      const gem = response.data.data?.gem || null
      setFormValues(gem)
    } else {
      showToast(response.data.message || 'خطا در ثبت اطلاعات', 'error')
    }
  } catch (err) {
    console.error('Gem submit error:', err)

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

  await persistGem()
}

const goBackToListing = () => {
  router.push({ name: 'levels-listing' })
}

watch(
  () => form.description,
  (value) => {
    if (sanitizeRichText(value)) {
      errors.description = ''
    }
  }
)

watch(() => form.png_file, (file) => {
  if (file) {
    errors.png_file = ''
  }
})

watch(() => form.fbx_file, (file) => {
  if (file) {
    errors.fbx_file = ''
  }
})

onMounted(() => {
  fetchGem()
})
</script>

<style scoped>
.text-error {
  color: var(--color-error, #EF4444);
}

.rich-editor-container {
  border-radius: 12px;
  border: 1px solid var(--theme-border);
  background: rgba(30, 41, 59, 0.75);
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.rich-editor-container.has-error {
  border-color: var(--color-error, #EF4444);
  box-shadow: 0 0 0 1px rgba(239, 68, 68, 0.35);
}

.rich-editor-container :deep(.p-editor-container) {
  border: none;
  background: transparent;
  color: var(--theme-text-primary);
  direction: rtl;
  font-family: 'Inter', sans-serif;
}

.rich-editor-container :deep(.p-editor-toolbar) {
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
  background: rgba(15, 23, 42, 0.65);
  backdrop-filter: blur(18px);
}

.rich-editor-container :deep(.p-editor-toolbar button) {
  color: var(--theme-text-secondary);
}

.rich-editor-container :deep(.p-editor-toolbar button.p-highlight) {
  color: var(--theme-primary-500, #7C3AED);
}

.rich-editor-container :deep(.ql-editor) {
  min-height: 180px;
  color: var(--theme-text-primary);
  font-size: 0.95rem;
  direction: rtl;
  text-align: right;
}

.rich-editor-container :deep(.ql-editor::placeholder) {
  color: var(--theme-text-muted);
}
</style>


