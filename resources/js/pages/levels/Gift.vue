<template>
  <div class="p-6 space-y-6" dir="rtl">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
      <div>
        <h1 class="text-3xl font-bold text-[var(--theme-text-primary)] mb-2">
          هدیه سطح
        </h1>
        <p class="text-[var(--theme-text-secondary)]">
          مدیریت اطلاعات هدیه، ظرفیت‌ها و فایل‌های اختصاص‌یافته به سطح انتخاب‌شده در متاورس
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
                  مشخصات اصلی
                </h2>
                <p class="text-sm text-[var(--theme-text-secondary)]">
                  اطلاعات عمومی هدیه و معرفی آن برای شهروندان سطح انتخاب‌شده.
                </p>
              </div>
            </div>
          </template>

          <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <Input
              v-model="form.name"
              label="نام هدیه"
              :error="errors.name"
              required
            />

            <Input
              v-model="form.seller_link"
              label="لینک فروش"
              :error="errors.seller_link"
              required
            />

            <Input
              v-model="form.designer"
              label="طراح"
              :error="errors.designer"
              required
            />

            <Input
              v-model.number="form.monthly_capacity_count"
              label="ظرفیت ماهانه"
              type="number"
              min="0"
              step="1"
              :error="errors.monthly_capacity_count"
              required
            />
          </div>

          <div class="grid grid-cols-1 gap-6">
            <div>
              <label class="block text-sm font-medium text-[var(--theme-text-primary)] mb-2">
                توضیحات
                <span class="text-error">*</span>
              </label>
              <div :class="['rich-editor-container', errors.description && 'has-error']">
                <Editor
                  v-model="form.description"
                  editorStyle="height: 220px"
                  :editorOptions="editorOptions"
                  placeholder="توضیحات کامل هدیه را وارد کنید"
                />
              </div>
              <p v-if="errors.description" class="mt-1.5 text-xs text-error">{{ errors.description }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-[var(--theme-text-primary)] mb-2">
                ویژگی‌ها
                <span class="text-error">*</span>
              </label>
              <div :class="['rich-editor-container', errors.features && 'has-error']">
                <Editor
                  v-model="form.features"
                  editorStyle="height: 220px"
                  :editorOptions="editorOptions"
                  placeholder="ویژگی‌ها و مزایای هدیه را وارد کنید"
                />
              </div>
              <p v-if="errors.features" class="mt-1.5 text-xs text-error">{{ errors.features }}</p>
            </div>
          </div>
        </Card>

        <Card variant="glass" padding="xl" rounded="lg">
          <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <h2 class="text-2xl font-semibold text-[var(--theme-text-primary)]">
                  دسترسی‌ها و ظرفیت فروش
                </h2>
                <p class="text-sm text-[var(--theme-text-secondary)]">
                  تنظیم مجوزهای فروش، نمایش و اجاره هدیه در فروشگاه‌های متاورس.
                </p>
              </div>
            </div>
          </template>

          <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div class="flex items-center justify-between rounded-lg border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)]/60 p-4">
              <div>
                <h3 class="text-lg font-medium text-[var(--theme-text-primary)]">ظرفیت فروشگاه</h3>
                <p class="text-sm text-[var(--theme-text-secondary)]">اجازه قرارگیری هدیه در فروشگاه‌های رسمی.</p>
              </div>
              <Checkbox v-model="form.store_capacity" variant="secondary" />
            </div>

            <div class="flex items-center justify-between rounded-lg border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)]/60 p-4">
              <div>
                <h3 class="text-lg font-medium text-[var(--theme-text-primary)]">ظرفیت فروش مستقیم</h3>
                <p class="text-sm text-[var(--theme-text-secondary)]">امکان فروش مستقیم هدیه توسط شهروند.</p>
              </div>
              <Checkbox v-model="form.sell_capacity" variant="primary" />
            </div>

            <div class="flex items-center justify-between rounded-lg border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)]/60 p-4">
              <div>
                <h3 class="text-lg font-medium text-[var(--theme-text-primary)]">قابلیت فروش فعال</h3>
                <p class="text-sm text-[var(--theme-text-secondary)]">امکان فعال یا غیرفعال‌کردن فروش هدیه.</p>
              </div>
              <Checkbox v-model="form.sell" variant="success" />
            </div>

            <div class="flex items-center justify-between rounded-lg border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)]/60 p-4">
              <div>
                <h3 class="text-lg font-medium text-[var(--theme-text-primary)]">ثبت مستندات VOD</h3>
                <p class="text-sm text-[var(--theme-text-secondary)]">اجازه ثبت و مستندسازی ویدئوهای هدیه.</p>
              </div>
              <Checkbox v-model="form.vod_document_registration" variant="primary" />
            </div>

            <div class="flex items-center justify-between rounded-lg border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)]/60 p-4">
              <div>
                <h3 class="text-lg font-medium text-[var(--theme-text-primary)]">اجاره با واحد رضایت</h3>
                <p class="text-sm text-[var(--theme-text-secondary)]">امکان اجاره هدیه با استفاده از واحد رضایت.</p>
              </div>
              <Checkbox v-model="form.rent" variant="warning" />
            </div>
          </div>
        </Card>

        <Card variant="glass" padding="xl" rounded="lg">
          <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <h2 class="text-2xl font-semibold text-[var(--theme-text-primary)]">
                  مدل سه‌بعدی و انیمیشن
                </h2>
                <p class="text-sm text-[var(--theme-text-secondary)]">
                  مشخصات مدل سه‌بعدی هدیه برای حفظ استانداردهای متاورس.
                </p>
              </div>
            </div>
          </template>

          <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <Input
              v-model="form.three_d_model_volume"
              label="حجم مدل سه‌بعدی (MB)"
              type="number"
              min="0"
              step="0.0001"
              :error="errors.three_d_model_volume"
              required
            />

            <Input
              v-model.number="form.three_d_model_points"
              label="تعداد نقاط"
              type="number"
              min="0"
              step="1"
              :error="errors.three_d_model_points"
              required
            />

            <Input
              v-model.number="form.three_d_model_lines"
              label="تعداد خطوط"
              type="number"
              min="0"
              step="1"
              :error="errors.three_d_model_lines"
              required
            />
          </div>

          <div class="mt-4 flex items-center justify-between rounded-lg border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)]/60 p-4">
            <div>
              <h3 class="text-lg font-medium text-[var(--theme-text-primary)]">دارای انیمیشن</h3>
              <p class="text-sm text-[var(--theme-text-secondary)]">در صورت فعال بودن، فایل هدیه باید انیمیشن معتبر داشته باشد.</p>
            </div>
            <Checkbox v-model="form.has_animation" variant="secondary" />
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
                  فایل‌های مرتبط با هدیه را بارگذاری یا بروزرسانی کنید. در صورت عدم انتخاب فایل جدید، نسخه فعلی حفظ می‌شود.
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
                helper-text="حداکثر 20 مگابایت"
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
                helper-text="حداکثر 500 مگابایت"
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
                helper-text="حداکثر 20 مگابایت"
                :error="errors.gif_file"
              />
              <ExistingFileHint :url="existingFiles.gif_file" label="گیف فعلی" />
            </div>
          </div>
        </Card>

        <Card variant="glass" padding="xl" rounded="lg">
          <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <h2 class="text-2xl font-semibold text-[var(--theme-text-primary)]">
                  شاخص‌های VOD
                </h2>
                <p class="text-sm text-[var(--theme-text-secondary)]">
                  مدیریت شناسه‌های شروع و پایان VOD برای هدیه.
                </p>
              </div>
            </div>
          </template>

          <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
            <Input
              v-model.number="form.vod_count"
              label="تعداد VOD"
              type="number"
              min="0"
              step="1"
              :error="errors.vod_count"
              required
            />

            <Input
              v-model="form.start_vod_id"
              label="شناسه شروع VOD"
              :error="errors.start_vod_id"
            />

            <Input
              v-model="form.end_vod_id"
              label="شناسه پایان VOD"
              :error="errors.end_vod_id"
            />
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
import { Button, Card, Checkbox, Input, Modal, LoadingState, ErrorState, FileInput } from '../../components/ui'
import Editor from 'primevue/editor'
import VerificationForm from '../../components/VerificationForm.vue'
import ExistingFileHint from '../../components/levels/ExistingFileHint.vue'
import { useToast } from '../../composables/useToast'
import { useLevelGift } from '../../composables/useLevelGift'

const { showToast } = useToast()
const { fetchLevelGift, sendVerificationSms, saveLevelGift } = useLevelGift()

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const saving = ref(false)
const error = ref(null)

const showVerificationDialog = ref(false)
const verificationFormRef = ref(null)

const hasExistingGift = ref(false)
const pendingPayload = ref(null)

const defaultValues = Object.freeze({
  name: '',
  description: '',
  monthly_capacity_count: 0,
  store_capacity: false,
  sell_capacity: false,
  features: '',
  sell: false,
  vod_document_registration: false,
  seller_link: '',
  designer: '',
  three_d_model_volume: 0,
  three_d_model_points: 0,
  three_d_model_lines: 0,
  has_animation: false,
  rent: false,
  vod_count: 0,
  start_vod_id: '',
  end_vod_id: ''
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
  name: '',
  description: '',
  monthly_capacity_count: '',
  store_capacity: '',
  sell_capacity: '',
  features: '',
  sell: '',
  vod_document_registration: '',
  seller_link: '',
  designer: '',
  three_d_model_volume: '',
  three_d_model_points: '',
  three_d_model_lines: '',
  has_animation: '',
  rent: '',
  vod_count: '',
  start_vod_id: '',
  end_vod_id: '',
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

  form.name = payload.name || ''
  form.description = payload.description || ''
  form.monthly_capacity_count = payload.monthly_capacity_count != null ? Number(payload.monthly_capacity_count) : 0
  form.store_capacity = normalizeBoolean(payload.store_capacity)
  form.sell_capacity = normalizeBoolean(payload.sell_capacity)
  form.features = payload.features || ''
  form.sell = normalizeBoolean(payload.sell)
  form.vod_document_registration = normalizeBoolean(payload.vod_document_registration)
  form.seller_link = payload.seller_link || ''
  form.designer = payload.designer || ''
  form.three_d_model_volume = payload.three_d_model_volume != null ? Number(payload.three_d_model_volume) : 0
  form.three_d_model_points = payload.three_d_model_points != null ? Number(payload.three_d_model_points) : 0
  form.three_d_model_lines = payload.three_d_model_lines != null ? Number(payload.three_d_model_lines) : 0
  form.has_animation = normalizeBoolean(payload.has_animation)
  form.rent = normalizeBoolean(payload.rent)
  form.vod_count = payload.vod_count != null ? Number(payload.vod_count) : 0
  form.start_vod_id = payload.start_vod_id || ''
  form.end_vod_id = payload.end_vod_id || ''

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

const validateDecimal = (field, label, { min = 0, precision = 4 } = {}) => {
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
    validateString('name', 'نام هدیه', { max: 255 }),
    validateString('description', 'توضیحات', { max: 6000, richText: true }),
    validateInteger('monthly_capacity_count', 'ظرفیت ماهانه'),
    validateString('features', 'ویژگی‌ها', { max: 5000, richText: true }),
    validateString('seller_link', 'لینک فروش', { max: 255 }),
    validateString('designer', 'طراح', { max: 255 }),
    validateDecimal('three_d_model_volume', 'حجم مدل سه‌بعدی', { min: 0, precision: 4 }),
    validateInteger('three_d_model_points', 'تعداد نقاط'),
    validateInteger('three_d_model_lines', 'تعداد خطوط'),
    validateInteger('vod_count', 'تعداد VOD'),
    validateString('start_vod_id', 'شناسه شروع VOD', { max: 255, required: false }),
    validateString('end_vod_id', 'شناسه پایان VOD', { max: 255, required: false })
  ]

  const filesValid = [
    validateFile('png_file', form.png_file, { maxSizeMB: 20, allowedTypes: ['image/png'], allowedExtensions: ['.png'] }),
    validateFile('gif_file', form.gif_file, { maxSizeMB: 20, allowedTypes: ['image/gif'], allowedExtensions: ['.gif'] }),
    validateFile('fbx_file', form.fbx_file, { maxSizeMB: 500, allowedExtensions: ['.fbx'] })
  ]

  const allValid = [...validators, ...filesValid].every(Boolean)

  if (!allValid) {
    return false
  }

  if (hasExistingGift.value && !hasFormChanges()) {
    return 'noChanges'
  }

  return true
}

const buildPayload = () => ({
  fields: {
    name: form.name.trim(),
    description: form.description.toString().trim(),
    monthly_capacity_count: Number(form.monthly_capacity_count),
    store_capacity: Boolean(form.store_capacity),
    sell_capacity: Boolean(form.sell_capacity),
    features: form.features.toString().trim(),
    sell: Boolean(form.sell),
    vod_document_registration: Boolean(form.vod_document_registration),
    seller_link: form.seller_link.trim(),
    designer: form.designer.trim(),
    three_d_model_volume: Number(form.three_d_model_volume),
    three_d_model_points: Number(form.three_d_model_points),
    three_d_model_lines: Number(form.three_d_model_lines),
    has_animation: Boolean(form.has_animation),
    rent: Boolean(form.rent),
    vod_count: Number(form.vod_count),
    start_vod_id: form.start_vod_id?.trim() || '',
    end_vod_id: form.end_vod_id?.trim() || ''
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

  if (hasExistingGift.value) {
    formData.append('_method', 'PUT')
  }

  return formData
}

const fetchGift = async () => {
  if (!levelId.value) {
    error.value = 'شناسه سطح نامعتبر است'
    loading.value = false
    return
  }

  try {
    loading.value = true
    error.value = null

    const response = await fetchLevelGift(levelId.value)

    if (response.data.success) {
      const gift = response.data.data?.gift || null
      hasExistingGift.value = Boolean(gift)
      setFormValues(gift)
    } else {
      error.value = response.data.message || 'خطا در دریافت اطلاعات هدیه'
    }
  } catch (err) {
    console.error('Gift fetch error:', err)

    if (err.response?.status === 404) {
      hasExistingGift.value = false
      setFormValues(null)
    } else if (err.response && (err.response.status === 401 || err.response.status === 403)) {
      return
    } else {
      error.value = err.response?.data?.message || 'خطا در دریافت اطلاعات هدیه'
    }
  } finally {
    loading.value = false
  }
}

const sendVerificationCode = async () => {
  try {
    const response = await sendVerificationSms()

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

const persistGift = async (payload, verificationData = {}) => {
  if (!levelId.value) {
    showToast('شناسه سطح نامعتبر است', 'error')
    return
  }

  const formData = appendFormData(payload, verificationData)
  const url = `/levels/${levelId.value}/gift`

  try {
    saving.value = true

    const response = await saveLevelGift(url, formData)

    if (response.data.success) {
      showToast(response.data.message || 'اطلاعات با موفقیت ثبت شد', 'success')
      showVerificationDialog.value = false
      pendingPayload.value = null
      hasExistingGift.value = true

      const gift = response.data.data?.gift || null
      setFormValues(gift)
    } else {
      showToast(response.data.message || 'خطا در ثبت اطلاعات', 'error')
    }
  } catch (err) {
    console.error('Gift submit error:', err)

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
    await persistGift(payload)
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

  await persistGift(pendingPayload.value, verificationData)
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

  await persistGift(pendingPayload.value, verificationData)
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

watch(() => form.png_file, (file) => {
  if (file) {
    errors.png_file = ''
  }
})

watch(
  () => form.description,
  (value) => {
    if (sanitizeRichText(value)) {
      errors.description = ''
    }
  }
)

watch(
  () => form.features,
  (value) => {
    if (sanitizeRichText(value)) {
      errors.features = ''
    }
  }
)

watch(() => form.gif_file, (file) => {
  if (file) {
    errors.gif_file = ''
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
  fetchGift()
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


