<template>
  <div class="p-6 space-y-6" dir="rtl">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
      <div>
        <h1 class="text-3xl font-bold text-[var(--theme-text-primary)] mb-2">
          اطلاعات کلی سطح
        </h1>
        <p class="text-[var(--theme-text-secondary)]">
          مدیریت مشخصات عمومی، طراحان و فایل‌های پایه سطح انتخاب‌شده در متاورس
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
      <div class="space-y-6">
        <Card variant="glass" padding="xl" rounded="lg">
          <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <h2 class="text-2xl font-semibold text-[var(--theme-text-primary)]">
                  شاخص‌های اصلی
                </h2>
                <p class="text-sm text-[var(--theme-text-secondary)]">
                  امتیازها، رتبه و تقسیم‌بندی‌های سطح را بروزرسانی کنید.
                </p>
              </div>
            </div>
          </template>

          <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <Input
              v-model.number="form.score"
              label="امتیاز پایه"
              type="number"
              min="0"
              step="1"
              :error="errors.score"
              required
            />

            <Input
              v-model.number="form.rank"
              label="رتبه"
              type="number"
              min="0"
              step="1"
              :error="errors.rank"
              required
            />

            <Input
              v-model.number="form.points"
              label="تعداد امتیازات"
              type="number"
              min="0"
              step="1"
              :error="errors.points"
              required
            />

            <Input
              v-model.number="form.subcategories"
              label="تعداد زیردسته‌ها"
              type="number"
              min="0"
              step="1"
              :error="errors.subcategories"
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
              توضیحات سطح
              <span class="text-error">*</span>
            </label>
            <div :class="['rich-editor-container', errors.description && 'has-error']">
              <Editor
                v-model="form.description"
                editorStyle="height: 220px"
                :editorOptions="editorOptions"
                placeholder="توضیحات جامع سطح را وارد کنید"
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
                  تایپوگرافی و رنگ‌ها
                </h2>
                <p class="text-sm text-[var(--theme-text-secondary)]">
                  فونت‌ها، حجم فایل و رنگ‌های استفاده‌شده در سطح را مشخص کنید.
                </p>
              </div>
            </div>
          </template>

          <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <Input
              v-model="form.persian_font"
              label="فونت فارسی"
              :error="errors.persian_font"
              required
            />

            <Input
              v-model="form.english_font"
              label="فونت انگلیسی"
              :error="errors.english_font"
              required
            />

            <Input
              v-model="form.file_volume"
              label="حجم فایل (MB)"
              type="number"
              min="0"
              step="0.001"
              :error="errors.file_volume"
              required
            />

            <Input
              v-model="form.used_colors"
              label="رنگ‌های استفاده شده"
              placeholder="مثال: #7C3AED، #06B6D4"
              :error="errors.used_colors"
              required
            />

            <div class="space-y-2">
              <Input
                v-model="form.creation_date"
                label="تاریخ ایجاد (شمسی)"
                placeholder="مثال: 1402/05/17"
                :error="errors.creation_date"
                required
              />
              <p class="text-xs text-[var(--theme-text-muted)]">فرمت پیشنهادی: YYYY/MM/DD</p>
            </div>
          </div>
        </Card>

        <Card variant="glass" padding="xl" rounded="lg">
          <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <h2 class="text-2xl font-semibold text-[var(--theme-text-primary)]">
                  طراحان و ویژگی‌ها
                </h2>
                <p class="text-sm text-[var(--theme-text-secondary)]">
                  اطلاعات طراحان سطح و وضعیت انیمیشن را تنظیم کنید.
                </p>
              </div>
            </div>
          </template>

          <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <Input
              v-model="form.designer"
              label="طراح اصلی"
              :error="errors.designer"
              required
            />

            <Input
              v-model="form.model_designer"
              label="طراح مدل"
              :error="errors.model_designer"
              required
            />

            <div class="flex items-center justify-between rounded-lg border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)]/60 p-4">
              <div>
                <h3 class="text-lg font-medium text-[var(--theme-text-primary)]">دارای انیمیشن</h3>
                <p class="text-sm text-[var(--theme-text-secondary)]">فعال بودن انیمیشن در مدل‌های سطح را مشخص کنید.</p>
              </div>
              <Checkbox v-model="form.has_animation" variant="secondary" />
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
                  فایل‌های نمایشی سطح را بارگذاری یا جایگزین کنید. در صورت عدم انتخاب فایل جدید، نسخه فعلی حفظ می‌شود.
                </p>
              </div>
            </div>
          </template>

          <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
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

            <div class="space-y-2">
              <FileInput
                v-model="form.gif_file"
                label="فایل GIF"
                accept="image/gif"
                placeholder="انتخاب تصویر GIF"
                helper-text="حداکثر 5 مگابایت"
                :error="errors.gif_file"
              />
              <ExistingFileHint :url="existingFiles.gif_file" label="گیف فعلی" />
            </div>
          </div>
        </Card>
      </div>
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
import { reactive, ref, computed, onMounted, nextTick, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import apiClient from '../../utils/api'
import { Button, Card, Checkbox, Input, Modal, LoadingState, ErrorState, FileInput } from '../../components/ui'
import Editor from 'primevue/editor'
import VerificationForm from '../../components/VerificationForm.vue'
import ExistingFileHint from '../../components/levels/ExistingFileHint.vue'
import { useToast } from '../../composables/useToast'

const { showToast } = useToast()
const route = useRoute()
const router = useRouter()

const loading = ref(true)
const saving = ref(false)
const error = ref(null)

const showVerificationDialog = ref(false)
const verificationFormRef = ref(null)

const hasExistingGeneralInfo = ref(false)
const pendingPayload = ref(null)

const defaultValues = Object.freeze({
  score: 0,
  description: '',
  rank: 0,
  subcategories: 0,
  persian_font: '',
  english_font: '',
  file_volume: 0,
  used_colors: '',
  points: 0,
  designer: '',
  model_designer: '',
  creation_date: '',
  has_animation: false,
  lines: 0
})

const form = reactive({
  ...defaultValues,
  png_file: null,
  fbx_file: null,
  gif_file: null
})

const existingFiles = reactive({
  png_file: null,
  fbx_file: null,
  gif_file: null
})

const originalValues = ref({ ...defaultValues })

const errors = reactive({
  score: '',
  description: '',
  rank: '',
  subcategories: '',
  persian_font: '',
  english_font: '',
  file_volume: '',
  used_colors: '',
  points: '',
  designer: '',
  model_designer: '',
  creation_date: '',
  has_animation: '',
  lines: '',
  png_file: '',
  fbx_file: '',
  gif_file: ''
})

const fieldKeys = Object.keys(defaultValues)

const levelId = computed(() => route.params?.levelId || null)
const levelLabel = computed(() => route.query?.name || route.query?.title || '')

const isProduction = computed(() => {
  const metaEnv = document.querySelector('meta[name="app-env"]')?.getAttribute('content')
  return metaEnv === 'production' || import.meta.env.MODE === 'production'
})

const editorOptions = Object.freeze({
  modules: {
    toolbar: [
      [{ header: [1, 2, 3, false] }],
      [{ font: [] }],
      [{ size: [] }],
      ['bold', 'italic', 'underline', 'strike'],
      [{ color: [] }, { background: [] }],
      [{ script: 'sub' }, { script: 'super' }],
      [{ list: 'ordered' }, { list: 'bullet' }],
      [{ indent: '-1' }, { indent: '+1' }],
      [{ direction: 'rtl' }],
      [{ align: [] }],
      ['link'],
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

  form.score = payload.score != null ? Number(payload.score) : 0
  form.description = payload.description || ''
  form.rank = payload.rank != null ? Number(payload.rank) : 0
  form.subcategories = payload.subcategories != null ? Number(payload.subcategories) : 0
  form.persian_font = payload.persian_font || ''
  form.english_font = payload.english_font || ''
  form.file_volume = payload.file_volume != null ? Number(payload.file_volume) : 0
  form.used_colors = payload.used_colors || ''
  form.points = payload.points != null ? Number(payload.points) : 0
  form.designer = payload.designer || ''
  form.model_designer = payload.model_designer || ''
  form.creation_date = payload.creation_date || ''
  form.has_animation = normalizeBoolean(payload.has_animation)
  form.lines = payload.lines != null ? Number(payload.lines) : 0

  form.png_file = null
  form.fbx_file = null
  form.gif_file = null

  existingFiles.png_file = payload.png_file || null
  existingFiles.fbx_file = payload.fbx_file || null
  existingFiles.gif_file = payload.gif_file || null

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
  const fileChanged = Boolean(form.png_file || form.fbx_file || form.gif_file)
  return nonFileChanged || fileChanged
}

const validateForm = () => {
  resetErrors()

  const validators = [
    validateInteger('score', 'امتیاز پایه'),
    validateString('description', 'توضیحات', { max: 6000, richText: true }),
    validateInteger('rank', 'رتبه'),
    validateInteger('subcategories', 'تعداد زیردسته‌ها'),
    validateString('persian_font', 'فونت فارسی', { max: 255 }),
    validateString('english_font', 'فونت انگلیسی', { max: 255 }),
    validateDecimal('file_volume', 'حجم فایل', { precision: 3 }),
    validateString('used_colors', 'رنگ‌های استفاده شده', { max: 500 }),
    validateInteger('points', 'تعداد امتیازات'),
    validateString('designer', 'طراح اصلی', { max: 255 }),
    validateString('model_designer', 'طراح مدل', { max: 255 }),
    validateString('creation_date', 'تاریخ ایجاد', { max: 255 }),
    validateInteger('lines', 'تعداد خطوط')
  ]

  const filesValid = [
    validateFile('png_file', form.png_file, { maxSizeMB: 5, allowedTypes: ['image/png'], allowedExtensions: ['.png'] }),
    validateFile('gif_file', form.gif_file, { maxSizeMB: 5, allowedTypes: ['image/gif'], allowedExtensions: ['.gif'] }),
    validateFile('fbx_file', form.fbx_file, { maxSizeMB: 300, allowedExtensions: ['.fbx'] })
  ]

  const allValid = [...validators, ...filesValid].every(Boolean)

  if (!allValid) {
    return false
  }

  if (hasExistingGeneralInfo.value && !hasFormChanges()) {
    return 'noChanges'
  }

  return true
}

const buildPayload = () => ({
  fields: {
    score: Number(form.score),
    description: form.description.toString().trim(),
    rank: Number(form.rank),
    subcategories: Number(form.subcategories),
    persian_font: form.persian_font.trim(),
    english_font: form.english_font.trim(),
    file_volume: Number(form.file_volume),
    used_colors: form.used_colors.trim(),
    points: Number(form.points),
    designer: form.designer.trim(),
    model_designer: form.model_designer.trim(),
    creation_date: form.creation_date.trim(),
    has_animation: Boolean(form.has_animation),
    lines: Number(form.lines)
  },
  files: {
    png_file: form.png_file,
    fbx_file: form.fbx_file,
    gif_file: form.gif_file
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

  if (verificationData?.phone_verification) {
    formData.append('phone_verification', verificationData.phone_verification)
  }

  if (hasExistingGeneralInfo.value) {
    formData.append('_method', 'PUT')
  }

  return formData
}

const fetchGeneralInfo = async () => {
  if (!levelId.value) {
    error.value = 'شناسه سطح نامعتبر است'
    loading.value = false
    return
  }

  try {
    loading.value = true
    error.value = null

    const response = await apiClient.get(`/levels/${levelId.value}/general-info`)

    if (response.data.success) {
      const info = response.data.data?.general_info || null
      hasExistingGeneralInfo.value = Boolean(info)
      setFormValues(info)
    } else {
      error.value = response.data.message || 'خطا در دریافت اطلاعات کلی'
    }
  } catch (err) {
    console.error('General info fetch error:', err)

    if (err.response?.status === 404) {
      hasExistingGeneralInfo.value = false
      setFormValues(null)
    } else if (err.response && (err.response.status === 401 || err.response.status === 403)) {
      return
    } else {
      error.value = err.response?.data?.message || 'خطا در دریافت اطلاعات کلی'
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

const persistGeneralInfo = async (payload, verificationData = {}) => {
  if (!levelId.value) {
    showToast('شناسه سطح نامعتبر است', 'error')
    return
  }

  const formData = appendFormData(payload, verificationData)
  const url = `/levels/${levelId.value}/general-info`

  try {
    saving.value = true

    const response = await apiClient.post(url, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    if (response.data.success) {
      showToast(response.data.message || 'اطلاعات با موفقیت ثبت شد', 'success')
      showVerificationDialog.value = false
      pendingPayload.value = null
      hasExistingGeneralInfo.value = true

      const info = response.data.data?.general_info || null
      setFormValues(info)
    } else {
      showToast(response.data.message || 'خطا در ثبت اطلاعات', 'error')
    }
  } catch (err) {
    console.error('General info submit error:', err)

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

  const validationResult = validateForm()
  if (validationResult !== true) {
    if (validationResult === 'noChanges') {
      showToast('تغییری برای ثبت وجود ندارد.', 'warning')
    } else {
      showToast('لطفاً خطاهای فرم را برطرف کرده و دوباره تلاش کنید.', 'warning')
    }
    return
  }

  const payload = buildPayload()

  if (!isProduction.value) {
    await persistGeneralInfo(payload)
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

  await persistGeneralInfo(pendingPayload.value, verificationData)
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

  await persistGeneralInfo(pendingPayload.value, verificationData)
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

watch(
  () => form.description,
  (value) => {
    if (sanitizeRichText(value)) {
      errors.description = ''
    }
  }
)

watch(
  () => form.used_colors,
  (value) => {
    if ((value ?? '').toString().trim()) {
      errors.used_colors = ''
    }
  }
)

watch(() => form.gif_file, (file) => {
  if (file) {
    errors.gif_file = ''
  }
})

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

watch(
  () => showVerificationDialog.value,
  async (isOpen) => {
    if (isOpen) {
      await nextTick()
      if (isProduction.value) {
        setTimeout(() => {
          verificationFormRef.value?.startTimer?.()
        }, 100)
      }
      setTimeout(() => {
        verificationFormRef.value?.focusFirstInput?.()
      }, 400)
    } else {
      verificationFormRef.value?.reset?.()
    }
  }
)

onMounted(() => {
  fetchGeneralInfo()
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


