<template>
  <div class="p-6 space-y-6" dir="rtl">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <h1 class="text-3xl font-bold text-[var(--theme-text-primary)] mb-2">مدیریت سطوح</h1>
        <p class="text-[var(--theme-text-secondary)]">ایجاد، ویرایش و مدیریت سطوح متاورس</p>
      </div>
      <Button
        variant="primary"
        rounded="full"
        @click="openCreateModal"
      >
        تعریف سطح جدید
      </Button>
    </div>

    <!-- Content States -->
    <LoadingState v-if="loading" />

    <ErrorState
      v-else-if="error"
      :message="error"
      variant="error"
    />

    <template v-else>
      <Table
        v-if="levels.length > 0"
        :columns="tableColumns"
        :data="levels"
        :pagination="pagination"
        :show-row-number="true"
        empty-state-message="سطحی یافت نشد"
      >
        <template #cell-image="{ value }">
          <a
            v-if="value"
            :href="value"
            target="_blank"
            rel="noopener"
            class="inline-flex items-center justify-center text-primary-300 hover:text-primary-200"
            title="مشاهده تصویر"
            aria-label="مشاهده تصویر"
          >
            <TableActionIcon name="image" icon-class="w-4 h-4 shrink-0" />
          </a>
          <span v-else class="text-[var(--theme-text-muted)]">-</span>
        </template>

        <template #cell-background_image="{ value }">
          <a
            v-if="value"
            :href="value"
            target="_blank"
            rel="noopener"
            class="inline-flex items-center justify-center text-secondary-300 hover:text-secondary-200"
            title="مشاهده تصویر"
            aria-label="مشاهده تصویر"
          >
            <TableActionIcon name="image" icon-class="w-4 h-4 shrink-0" />
          </a>
          <span v-else class="text-[var(--theme-text-muted)]">-</span>
        </template>

        <template #cell-actions="{ row }">
          <div class="flex flex-wrap gap-2 justify-end">
            <Button
              size="sm"
              variant="glass"
              rounded="full"
              class="!p-2 !gap-0 min-w-[2.25rem]"
              title="اطلاعات سطح"
              aria-label="اطلاعات سطح"
              @click="openInfoModal(row)"
            >
              <template #icon-left>
                <TableActionIcon name="details" />
              </template>
            </Button>
            <Button
              size="sm"
              variant="primary"
              rounded="full"
              class="!p-2 !gap-0 min-w-[2.25rem]"
              title="ویرایش"
              aria-label="ویرایش"
              @click="openUpdateModal(row)"
            >
              <template #icon-left>
                <TableActionIcon name="edit" />
              </template>
            </Button>
            <Button
              size="sm"
              variant="danger"
              rounded="full"
              class="!p-2 !gap-0 min-w-[2.25rem]"
              title="حذف"
              aria-label="حذف"
              @click="handleDelete(row)"
            >
              <template #icon-left>
                <TableActionIcon name="delete" />
              </template>
            </Button>
          </div>
        </template>
      </Table>

      <Alert
        v-else
        variant="warning"
        message="سطحی ثبت نشده است"
        :dismissible="false"
      />

      <Pagination
        v-if="pagination && pagination.total > 0"
        :pagination="pagination"
        :disabled="loading"
        @page-change="goToPage"
      />
    </template>

    <!-- Create Level Modal -->
    <Modal
      :model-value="isCreateModalOpen"
      @update:model-value="handleCreateModalToggle"
      title="تعریف سطح جدید"
      size="xl"
    >
      <div class="space-y-6" dir="rtl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <Input
            v-model="createForm.name"
            label="نام سطح"
            required
            :error="createErrors.name"
          />
          <Input
            v-model="createForm.slug"
            label="نامک"
            required
            :error="createErrors.slug"
          />
          <Input
            v-model="createForm.score"
            label="امتیاز مورد نیاز"
            type="number"
            min="0"
            required
            :error="createErrors.score"
          />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <FileInput
            v-model="createForm.image"
            label="تصویر"
            accept="image/*"
            :error="createErrors.image"
            helper-text="فرمت‌های مجاز: jpg، jpeg، png، bmp"
          />

          <FileInput
            v-model="createForm.backgroundImage"
            label="تصویر پس زمینه"
            accept="image/*"
            required
            :error="createErrors.background_image"
            helper-text="حداکثر حجم 5 مگابایت"
          />
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end gap-3" dir="rtl">
          <Button
            variant="primary"
            rounded="full"
            :loading="createSubmitting"
            @click="handleCreateSubmit"
          >
            ثبت
          </Button>
          <Button
            variant="danger"
            rounded="full"
            :disabled="createSubmitting"
            @click="closeCreateModal"
          >
            بستن
          </Button>
        </div>
      </template>
    </Modal>

    <!-- Update Level Modal -->
    <Modal
      :model-value="isUpdateModalOpen"
      @update:model-value="handleUpdateModalToggle"
      title="ویرایش سطح"
      size="xl"
    >
      <div v-if="selectedLevel" class="space-y-6" dir="rtl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <Input
            v-model="updateForm.name"
            label="نام سطح"
            required
            :error="updateErrors.name"
          />
          <Input
            v-model="updateForm.slug"
            label="نامک"
            required
            :error="updateErrors.slug"
          />
          <Input
            v-model="updateForm.score"
            label="امتیاز مورد نیاز"
            type="number"
            min="0"
            required
            :error="updateErrors.score"
          />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="space-y-3">
            <FileInput
              v-model="updateForm.image"
              label="تصویر"
              accept="image/*"
              :error="updateErrors.image"
              helper-text="عدم انتخاب فایل به معنی حفظ تصویر فعلی است"
            />
            <p v-if="updateForm.existingImageUrl" class="text-xs text-[var(--theme-text-secondary)]">
              تصویر فعلی:
              <a
                :href="updateForm.existingImageUrl"
                target="_blank"
                rel="noopener"
                class="inline-flex items-center gap-1 text-primary-300 hover:text-primary-200 underline"
                title="مشاهده تصویر"
                aria-label="مشاهده تصویر"
              >
                <TableActionIcon name="image" icon-class="w-4 h-4 shrink-0" />
              </a>
            </p>
          </div>

          <div class="space-y-3">
            <FileInput
              v-model="updateForm.backgroundImage"
              label="تصویر پس زمینه"
              accept="image/*"
              :error="updateErrors.background_image"
              helper-text="عدم انتخاب فایل به معنی حفظ تصویر فعلی است"
            />
            <p v-if="updateForm.existingBackgroundUrl" class="text-xs text-[var(--theme-text-secondary)]">
              تصویر فعلی:
              <a
                :href="updateForm.existingBackgroundUrl"
                target="_blank"
                rel="noopener"
                class="inline-flex items-center gap-1 text-secondary-300 hover:text-secondary-200 underline"
                title="مشاهده تصویر"
                aria-label="مشاهده تصویر"
              >
                <TableActionIcon name="image" icon-class="w-4 h-4 shrink-0" />
              </a>
            </p>
          </div>
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end gap-3" dir="rtl">
          <Button
            variant="primary"
            rounded="full"
            :loading="updateSubmitting"
            @click="handleUpdateSubmit"
          >
            ثبت تغییرات
          </Button>
          <Button
            variant="danger"
            rounded="full"
            :disabled="updateSubmitting"
            @click="closeUpdateModal"
          >
            بستن
          </Button>
        </div>
      </template>
    </Modal>

    <!-- Create Verification Dialog -->
    <Modal
      :model-value="showCreateVerificationDialog"
      @update:model-value="handleCreateVerificationDialogToggle"
      @close="handleCloseCreateVerificationDialog"
      title="تایید نهایی"
      size="md"
    >
      <div dir="rtl" class="space-y-6">
        <VerificationForm
          ref="createVerificationFormRef"
          :auto-start="true"
          @verified="handleCreateAutoVerifyAndSubmit"
        />

        <Alert
          variant="info"
      message="کد تایید پیامکی را وارد کنید تا عملیات ایجاد سطح نهایی شود."
          :dismissible="false"
        />
      </div>

      <template #footer>
        <div class="flex justify-end gap-3" dir="rtl">
          <Button
            variant="primary"
            rounded="full"
            :loading="createSubmitting"
            @click="handleCreateVerificationSubmit"
          >
            تایید و ثبت
          </Button>
          <Button
            variant="danger"
            rounded="full"
            :disabled="createSubmitting"
            @click="handleCloseCreateVerificationDialog"
          >
            بستن
          </Button>
        </div>
      </template>
    </Modal>

    <!-- Update Verification Dialog -->
    <Modal
      :model-value="showUpdateVerificationDialog"
      @update:model-value="handleUpdateVerificationDialogToggle"
      @close="handleCloseUpdateVerificationDialog"
      title="تایید نهایی"
      size="md"
    >
      <div dir="rtl" class="space-y-6">
        <VerificationForm
          ref="updateVerificationFormRef"
          :auto-start="true"
          @verified="handleUpdateAutoVerifyAndSubmit"
        />

        <Alert
          variant="info"
      message="کد تایید پیامکی را وارد کنید تا تغییرات سطح ثبت شود."
          :dismissible="false"
        />
      </div>

      <template #footer>
        <div class="flex justify-end gap-3" dir="rtl">
          <Button
            variant="primary"
            rounded="full"
            :loading="updateSubmitting"
            @click="handleUpdateVerificationSubmit"
          >
            تایید و ثبت
          </Button>
          <Button
            variant="danger"
            rounded="full"
            :disabled="updateSubmitting"
            @click="handleCloseUpdateVerificationDialog"
          >
            بستن
          </Button>
        </div>
      </template>
    </Modal>

    <!-- Level Info Modal -->
    <Modal
      :model-value="isInfoModalOpen"
      @update:model-value="handleInfoModalToggle"
      title="اطلاعات سطح"
      size="xl"
    >
      <div v-if="selectedLevel" class="space-y-6" dir="rtl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="bg-[var(--theme-bg-elevated)]/60 border border-[var(--theme-border)] rounded-lg p-4 space-y-1">
            <p class="text-sm text-[var(--theme-text-secondary)]">نام سطح</p>
            <p class="text-lg font-semibold text-[var(--theme-text-primary)]">{{ selectedLevel.name }}</p>
          </div>
          <div class="bg-[var(--theme-bg-elevated)]/60 border border-[var(--theme-border)] rounded-lg p-4 space-y-1">
            <p class="text-sm text-[var(--theme-text-secondary)]">نامک</p>
            <p class="text-lg font-semibold text-[var(--theme-text-primary)]">{{ selectedLevel.slug }}</p>
          </div>
          <div class="bg-[var(--theme-bg-elevated)]/60 border border-[var(--theme-border)] rounded-lg p-4 space-y-1">
            <p class="text-sm text-[var(--theme-text-secondary)]">امتیاز مورد نیاز</p>
            <p class="text-lg font-semibold text-[var(--theme-text-primary)]">{{ selectedLevel.score }}</p>
          </div>
          <div class="bg-[var(--theme-bg-elevated)]/60 border border-[var(--theme-border)] rounded-lg p-4 space-y-3">
            <p class="text-sm text-[var(--theme-text-secondary)]">تصاویر</p>
            <div class="flex flex-col gap-2 text-sm">
              <div>
                <span class="text-[var(--theme-text-muted)]">تصویر اصلی:</span>
                <a
                  v-if="selectedLevel.image"
                  :href="selectedLevel.image"
                  target="_blank"
                  rel="noopener"
                  class="ml-2 inline-flex items-center gap-1 text-primary-300 hover:text-primary-200 underline"
                  title="مشاهده تصویر"
                  aria-label="مشاهده تصویر"
                >
                  <TableActionIcon name="image" icon-class="w-4 h-4 shrink-0" />
                </a>
                <span v-else class="ml-2 text-[var(--theme-text-muted)]">-</span>
              </div>
              <div>
                <span class="text-[var(--theme-text-muted)]">پس زمینه:</span>
                <a
                  v-if="selectedLevel.background_image"
                  :href="selectedLevel.background_image"
                  target="_blank"
                  rel="noopener"
                  class="ml-2 inline-flex items-center gap-1 text-secondary-300 hover:text-secondary-200 underline"
                  title="مشاهده تصویر"
                  aria-label="مشاهده تصویر"
                >
                  <TableActionIcon name="image" icon-class="w-4 h-4 shrink-0" />
                </a>
                <span v-else class="ml-2 text-[var(--theme-text-muted)]">-</span>
              </div>
            </div>
          </div>
        </div>

        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <Button
            variant="primary"
            rounded="full"
            class="self-start lg:self-auto"
            @click="goToPrizePage"
          >
            مدیریت پاداش سطح
          </Button>
          <Button
            variant="secondary"
            rounded="full"
            class="self-start lg:self-auto"
            @click="goToLicensesPage"
          >
            مدیریت مجوزهای سطح
          </Button>
          <Button
            variant="glass"
            rounded="full"
            class="self-start lg:self-auto"
            @click="goToGiftPage"
          >
            مدیریت هدیه سطح
          </Button>
          <Button
            variant="outline"
            rounded="full"
            class="self-start lg:self-auto"
            @click="goToGeneralInfoPage"
          >
            مدیریت اطلاعات کلی سطح
          </Button>
          <Button
            variant="ghost"
            rounded="full"
            class="self-start lg:self-auto"
            @click="goToGemPage"
          >
            مدیریت نگین سطح
          </Button>
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end" dir="rtl">
          <Button
            variant="glass"
            rounded="full"
            @click="closeInfoModal"
          >
            بستن
          </Button>
        </div>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { Table, Pagination, Button, Modal, Input, Alert, LoadingState, ErrorState, FileInput } from '../../components/ui'
import VerificationForm from '../../components/VerificationForm.vue'
import { useToast } from '../../composables/useToast'
import { useLevels } from '../../composables/useLevels'
import { confirm } from '../../utils/notifications'
import TableActionIcon from '../../components/icons/TableActionIcon.vue'

const { showToast } = useToast()
const {
  fetchLevels: fetchLevelsApi,
  sendVerificationSms,
  createLevel,
  updateLevel,
  deleteLevel
} = useLevels()

const router = useRouter()

const loading = ref(true)
const error = ref(null)
const levels = ref([])
const pagination = ref(null)
const currentPage = ref(1)
const perPage = 10

const isCreateModalOpen = ref(false)
const isUpdateModalOpen = ref(false)
const isInfoModalOpen = ref(false)

const selectedLevel = ref(null)

const createForm = reactive({
  name: '',
  slug: '',
  score: '',
  image: null,
  backgroundImage: null
})

const updateForm = reactive({
  name: '',
  slug: '',
  score: '',
  image: null,
  backgroundImage: null,
  existingImageUrl: null,
  existingBackgroundUrl: null
})

const createErrors = reactive({})
const updateErrors = reactive({})

const createSubmitting = ref(false)
const updateSubmitting = ref(false)

const createVerificationFormRef = ref(null)
const updateVerificationFormRef = ref(null)

const showCreateVerificationDialog = ref(false)
const showUpdateVerificationDialog = ref(false)

const createPendingData = ref(null)
const updatePendingData = ref(null)

const createModalClosingForVerification = ref(false)
const updateModalClosingForVerification = ref(false)

const isProduction = computed(() => {
  const metaEnv = document.querySelector('meta[name="app-env"]')?.getAttribute('content')
  return metaEnv === 'production' || import.meta.env.MODE === 'production'
})

const tableColumns = [
  {
    key: 'name',
    label: 'نام سطح'
  },
  {
    key: 'score',
    label: 'امتیاز مورد نیاز'
  },
  {
    key: 'slug',
    label: 'نامک'
  },
  {
    key: 'image',
    label: 'تصویر',
    textSecondary: true
  },
  {
    key: 'background_image',
    label: 'تصویر پس زمینه',
    textSecondary: true
  },
  {
    key: 'actions',
    label: 'اقدامات'
  }
]

const mapLevel = (level) => {
  const imageUrl = level.image_url || level.image?.url ? resolveImageUrl(level.image_url || level.image?.url) : null
  return {
    id: level.id,
    name: level.name,
    slug: level.slug,
    score: level.score,
    image: imageUrl,
    background_image: level.background_image_url || level.background_image || null,
    raw: level
  }
}

const resolveImageUrl = (value) => {
  if (!value) return null
  if (value.startsWith('http://') || value.startsWith('https://')) {
    return value
  }
  return `/uploads/${value.replace(/^\/+/g, '')}`
}

const fetchLevels = async () => {
  try {
    loading.value = true
    error.value = null

    const params = {
      page: currentPage.value,
      per_page: perPage
    }

    const response = await fetchLevelsApi(params)

    if (response.data?.success) {
      const payload = response.data.data || {}
      const rawLevels = Array.isArray(payload.levels?.data)
        ? payload.levels.data
        : Array.isArray(payload.levels)
          ? payload.levels
          : []

      levels.value = rawLevels.map(mapLevel)

      if (payload.pagination) {
        pagination.value = payload.pagination
      } else if (payload.levels?.meta) {
        const meta = payload.levels.meta
        pagination.value = {
          total: meta.total,
          per_page: meta.per_page,
          current_page: meta.current_page,
          last_page: meta.last_page
        }
      } else {
        pagination.value = null
      }
    } else {
      error.value = response.data?.message || 'خطا در دریافت اطلاعات سطوح'
      levels.value = []
      pagination.value = null
    }
  } catch (err) {
    console.error('Levels fetch error:', err)
    error.value = err.response?.data?.message || 'خطا در بارگذاری اطلاعات'
    levels.value = []
    pagination.value = null
  } finally {
    loading.value = false
  }
}

const goToPage = (page) => {
  if (!pagination.value) return
  if (page < 1 || page > pagination.value.last_page) return
  currentPage.value = page
  fetchLevels()
}

const resetCreateForm = () => {
  createForm.name = ''
  createForm.slug = ''
  createForm.score = ''
  createForm.image = null
  createForm.backgroundImage = null
  Object.keys(createErrors).forEach((key) => delete createErrors[key])
  createPendingData.value = null
  showCreateVerificationDialog.value = false
  if (createVerificationFormRef.value) createVerificationFormRef.value.reset()
}

const resetUpdateForm = () => {
  if (!selectedLevel.value) return
  updateForm.name = selectedLevel.value.name
  updateForm.slug = selectedLevel.value.slug
  updateForm.score = selectedLevel.value.score
  updateForm.image = null
  updateForm.backgroundImage = null
  updateForm.existingImageUrl = selectedLevel.value.image || null
  updateForm.existingBackgroundUrl = selectedLevel.value.background_image || null
  Object.keys(updateErrors).forEach((key) => delete updateErrors[key])
  updatePendingData.value = null
  showUpdateVerificationDialog.value = false
  if (updateVerificationFormRef.value) updateVerificationFormRef.value.reset()
}

const validateCreateForm = () => {
  Object.keys(createErrors).forEach((key) => delete createErrors[key])

  if (!createForm.name) {
    createErrors.name = 'نام سطح را وارد کنید'
  }

  if (!createForm.slug) {
    createErrors.slug = 'نامک را وارد کنید'
  }

  if (createForm.score === '' || Number(createForm.score) < 0) {
    createErrors.score = 'امتیاز معتبر وارد کنید'
  }

  if (!createForm.backgroundImage) {
    createErrors.background_image = 'انتخاب تصویر پس زمینه الزامی است'
  }

  return Object.keys(createErrors).length === 0
}

const validateUpdateForm = () => {
  Object.keys(updateErrors).forEach((key) => delete updateErrors[key])

  if (!updateForm.name) {
    updateErrors.name = 'نام سطح را وارد کنید'
  }

  if (!updateForm.slug) {
    updateErrors.slug = 'نامک را وارد کنید'
  }

  if (updateForm.score === '' || Number(updateForm.score) < 0) {
    updateErrors.score = 'امتیاز معتبر وارد کنید'
  }

  return Object.keys(updateErrors).length === 0
}

watch(
  () => createForm.image,
  (file) => {
    if (file && createErrors.image) {
      delete createErrors.image
    }
  }
)

watch(
  () => createForm.backgroundImage,
  (file) => {
    if (file && createErrors.background_image) {
      delete createErrors.background_image
    }
  }
)

watch(
  () => updateForm.image,
  (file) => {
    if (file && updateErrors.image) {
      delete updateErrors.image
    }
  }
)

watch(
  () => updateForm.backgroundImage,
  (file) => {
    if (file && updateErrors.background_image) {
      delete updateErrors.background_image
    }
  }
)

const buildCreatePayload = () => ({
  name: createForm.name,
  slug: createForm.slug,
  score: createForm.score,
  image: createForm.image,
  backgroundImage: createForm.backgroundImage
})

const buildUpdatePayload = () => ({
  name: updateForm.name,
  slug: updateForm.slug,
  score: updateForm.score,
  image: updateForm.image,
  backgroundImage: updateForm.backgroundImage
})

const sendVerificationCode = async () => {
  try {
    const response = await sendVerificationSms()

    if (response.data.success) {
      return true
    }

    showToast('خطا در ارسال کد تایید', 'error')
    return false
  } catch (err) {
    console.error('Verification SMS send error:', err)
    showToast(err.response?.data?.message || 'خطا در ارسال کد تایید', 'error')
    return false
  }
}

const submitCreateLevel = async (verificationData = {}) => {
  try {
    createSubmitting.value = true

    const payload = createPendingData.value || buildCreatePayload()
    Object.keys(createErrors).forEach((key) => delete createErrors[key])
    const formData = new FormData()
    formData.append('name', payload.name)
    formData.append('slug', payload.slug)
    formData.append('score', payload.score)

    if (payload.image) {
      formData.append('image', payload.image)
    }

    if (payload.backgroundImage) {
      formData.append('background_image', payload.backgroundImage)
    }

    if (verificationData?.phone_verification) {
      formData.append('phone_verification', verificationData.phone_verification)
    }

    const response = await createLevel(formData)

    if (response.data?.success) {
      showToast(response.data?.message || 'سطح با موفقیت ایجاد شد', 'success')
      showCreateVerificationDialog.value = false
      createPendingData.value = null
      closeCreateModal()
      fetchLevels()
    } else {
      showToast(response.data?.message || 'خطا در ثبت سطح', 'error')
    }
  } catch (err) {
    console.error('Create level error:', err)

    let shouldReopenForm = false

    if (err.response?.status === 422 && err.response?.data?.errors) {
      const errorsBag = err.response.data.errors
      const verificationErrorsBag = {}

      Object.keys(errorsBag).forEach((field) => {
        const message = Array.isArray(errorsBag[field]) ? errorsBag[field][0] : errorsBag[field]

        if (field === 'phone_verification') {
          verificationErrorsBag[field] = message
        } else {
          createErrors[field] = message
          shouldReopenForm = true
        }
      })

      if (Object.keys(verificationErrorsBag).length > 0) {
        createVerificationFormRef.value?.setErrors?.(verificationErrorsBag)
      }
    } else {
      showToast(err.response?.data?.message || 'خطا در ثبت سطح', 'error')
      shouldReopenForm = true
    }

    if (shouldReopenForm) {
      createPendingData.value = null
      showCreateVerificationDialog.value = false
      createVerificationFormRef.value?.reset?.()
      isCreateModalOpen.value = true
    }
  } finally {
    createSubmitting.value = false
  }
}

const submitUpdateLevel = async (verificationData = {}) => {
  try {
    updateSubmitting.value = true

    const payload = updatePendingData.value || buildUpdatePayload()
    Object.keys(updateErrors).forEach((key) => delete updateErrors[key])
    const formData = new FormData()
    formData.append('name', payload.name)
    formData.append('slug', payload.slug)
    formData.append('score', payload.score)

    if (payload.image) {
      formData.append('image', payload.image)
    }

    if (payload.backgroundImage) {
      formData.append('background_image', payload.backgroundImage)
    }

    if (verificationData?.phone_verification) {
      formData.append('phone_verification', verificationData.phone_verification)
    }

    formData.append('_method', 'PUT')

    const response = await updateLevel(selectedLevel.value.id, formData)

    if (response.data?.success) {
      showToast(response.data?.message || 'سطح با موفقیت بروزرسانی شد', 'success')
      showUpdateVerificationDialog.value = false
      updatePendingData.value = null
      closeUpdateModal()
      fetchLevels()
    } else {
      showToast(response.data?.message || 'خطا در بروزرسانی سطح', 'error')
    }
  } catch (err) {
    console.error('Update level error:', err)

    let shouldReopenForm = false

    if (err.response?.status === 422 && err.response?.data?.errors) {
      const errorsBag = err.response.data.errors
      const verificationErrorsBag = {}

      Object.keys(errorsBag).forEach((field) => {
        const message = Array.isArray(errorsBag[field]) ? errorsBag[field][0] : errorsBag[field]

        if (field === 'phone_verification') {
          verificationErrorsBag[field] = message
        } else {
          updateErrors[field] = message
          shouldReopenForm = true
        }
      })

      if (Object.keys(verificationErrorsBag).length > 0) {
        updateVerificationFormRef.value?.setErrors?.(verificationErrorsBag)
      }
    } else {
      showToast(err.response?.data?.message || 'خطا در بروزرسانی سطح', 'error')
      shouldReopenForm = true
    }

    if (shouldReopenForm) {
      updatePendingData.value = null
      showUpdateVerificationDialog.value = false
      updateVerificationFormRef.value?.reset?.()
      if (selectedLevel.value) {
        isUpdateModalOpen.value = true
      }
    }
  } finally {
    updateSubmitting.value = false
  }
}

const handleCreateSubmit = async () => {
  if (createSubmitting.value) return

  if (!validateCreateForm()) {
    return
  }

  createPendingData.value = buildCreatePayload()

  if (!isProduction.value) {
    await submitCreateLevel()
    return
  }

  createModalClosingForVerification.value = true
  isCreateModalOpen.value = false

  createSubmitting.value = true
  const codeSent = await sendVerificationCode()
  createSubmitting.value = false

  if (!codeSent) {
    createPendingData.value = null
    isCreateModalOpen.value = true
    return
  }

  showCreateVerificationDialog.value = true
}

const handleUpdateSubmit = async () => {
  if (updateSubmitting.value || !selectedLevel.value) return

  if (!validateUpdateForm()) {
    return
  }

  updatePendingData.value = buildUpdatePayload()

  if (!isProduction.value) {
    await submitUpdateLevel()
    return
  }

  updateModalClosingForVerification.value = true
  isUpdateModalOpen.value = false

  updateSubmitting.value = true
  const codeSent = await sendVerificationCode()
  updateSubmitting.value = false

  if (!codeSent) {
    updatePendingData.value = null
    isUpdateModalOpen.value = true
    return
  }

  showUpdateVerificationDialog.value = true
}

const handleCreateAutoVerifyAndSubmit = async (verificationData) => {
  if (createSubmitting.value) {
    return
  }

  if (!createVerificationFormRef.value) {
    showToast('خطا در تایید', 'error')
    return
  }

  await submitCreateLevel(verificationData)
}

const handleCreateVerificationSubmit = async () => {
  if (createSubmitting.value) {
    return
  }

  if (!createVerificationFormRef.value) {
    showToast('خطا در تایید', 'error')
    return
  }

  const isValid = await createVerificationFormRef.value.validate()
  if (!isValid) {
    return
  }

  const verificationData = createVerificationFormRef.value.getData()
  await submitCreateLevel(verificationData)
}

const handleUpdateAutoVerifyAndSubmit = async (verificationData) => {
  if (updateSubmitting.value) {
    return
  }

  if (!updateVerificationFormRef.value) {
    showToast('خطا در تایید', 'error')
    return
  }

  await submitUpdateLevel(verificationData)
}

const handleUpdateVerificationSubmit = async () => {
  if (updateSubmitting.value) {
    return
  }

  if (!updateVerificationFormRef.value) {
    showToast('خطا در تایید', 'error')
    return
  }

  const isValid = await updateVerificationFormRef.value.validate()
  if (!isValid) {
    return
  }

  const verificationData = updateVerificationFormRef.value.getData()
  await submitUpdateLevel(verificationData)
}

const handleCloseCreateVerificationDialog = () => {
  if (createVerificationFormRef.value?.setErrors) {
    createVerificationFormRef.value.setErrors({})
  }
  if (createVerificationFormRef.value?.reset) {
    createVerificationFormRef.value.reset()
  }
  showCreateVerificationDialog.value = false
  if (createPendingData.value) {
    isCreateModalOpen.value = true
  }
  createPendingData.value = null
}

const handleCloseUpdateVerificationDialog = () => {
  if (updateVerificationFormRef.value?.setErrors) {
    updateVerificationFormRef.value.setErrors({})
  }
  if (updateVerificationFormRef.value?.reset) {
    updateVerificationFormRef.value.reset()
  }
  showUpdateVerificationDialog.value = false
  if (updatePendingData.value && selectedLevel.value) {
    isUpdateModalOpen.value = true
  }
  updatePendingData.value = null
}

const handleDelete = async (level) => {
  try {
    const result = await confirm(
      'آیا از حذف این سطح اطمینان دارید؟',
      'حذف سطح',
      {
        confirmText: 'بله، حذف شود',
        cancelText: 'انصراف'
      }
    )

    if (!result.isConfirmed) {
      return
    }

    const response = await deleteLevel(level.id)
    if (response.data?.success) {
      showToast(response.data?.message || 'سطح با موفقیت حذف شد', 'success')
      fetchLevels()
    } else {
      showToast(response.data?.message || 'خطا در حذف سطح', 'error')
    }
  } catch (err) {
    console.error('Delete level error:', err)
    showToast(err.response?.data?.message || 'خطا در حذف سطح', 'error')
  }
}

const openCreateModal = () => {
  resetCreateForm()
  isCreateModalOpen.value = true
}

const closeCreateModal = () => {
  isCreateModalOpen.value = false
  resetCreateForm()
}

const handleCreateModalToggle = (value) => {
  if (!value) {
    closeCreateModal()
  } else {
    isCreateModalOpen.value = true
  }
}

const openUpdateModal = (level) => {
  selectedLevel.value = level
  resetUpdateForm()
  isUpdateModalOpen.value = true
}

const closeUpdateModal = () => {
  isUpdateModalOpen.value = false
  if (updateVerificationFormRef.value) {
    updateVerificationFormRef.value.reset()
  }
}

const handleUpdateModalToggle = (value) => {
  if (!value) {
    closeUpdateModal()
  } else {
    isUpdateModalOpen.value = true
    nextTick(() => resetUpdateForm())
  }
}

const openInfoModal = (level) => {
  selectedLevel.value = level
  isInfoModalOpen.value = true
}

const closeInfoModal = () => {
  isInfoModalOpen.value = false
}

const handleInfoModalToggle = (value) => {
  if (!value) {
    closeInfoModal()
  } else {
    isInfoModalOpen.value = true
  }
}

const goToPrizePage = () => {
  if (!selectedLevel.value?.id) return
  router.push({
    name: 'levels-prize',
    params: { levelId: selectedLevel.value.id },
    query: { name: selectedLevel.value.name }
  })
}

const goToLicensesPage = () => {
  if (!selectedLevel.value?.id) return
  router.push({
    name: 'levels-licenses',
    params: { levelId: selectedLevel.value.id },
    query: { name: selectedLevel.value.name }
  })
}

const goToGiftPage = () => {
  if (!selectedLevel.value?.id) return
  router.push({
    name: 'levels-gift',
    params: { levelId: selectedLevel.value.id },
    query: { name: selectedLevel.value.name }
  })
}

const goToGeneralInfoPage = () => {
  if (!selectedLevel.value?.id) return
  router.push({
    name: 'levels-general-info',
    params: { levelId: selectedLevel.value.id },
    query: { name: selectedLevel.value.name }
  })
}

const goToGemPage = () => {
  if (!selectedLevel.value?.id) return
  router.push({
    name: 'levels-gem',
    params: { levelId: selectedLevel.value.id },
    query: { name: selectedLevel.value.name }
  })
}

const handleCreateVerificationDialogToggle = (value) => {
  if (!value) {
    handleCloseCreateVerificationDialog()
  } else {
    showCreateVerificationDialog.value = true
  }
}

const handleUpdateVerificationDialogToggle = (value) => {
  if (!value) {
    handleCloseUpdateVerificationDialog()
  } else {
    showUpdateVerificationDialog.value = true
  }
}

watch(isCreateModalOpen, (isOpen) => {
  if (!isOpen) {
    if (createModalClosingForVerification.value) {
      createModalClosingForVerification.value = false
      return
    }
    resetCreateForm()
  }
})

watch(isUpdateModalOpen, (isOpen) => {
  if (!isOpen) {
    if (updateModalClosingForVerification.value) {
      updateModalClosingForVerification.value = false
      return
    }
    updatePendingData.value = null
    showUpdateVerificationDialog.value = false
    if (updateVerificationFormRef.value) {
      updateVerificationFormRef.value.reset()
    }
    resetUpdateForm()
  }
})

watch(
  () => showCreateVerificationDialog.value,
  async (isOpen) => {
    if (isOpen) {
      await nextTick()
      if (isProduction.value) {
        setTimeout(() => {
          createVerificationFormRef.value?.startTimer?.()
        }, 100)
      }
      setTimeout(() => {
        createVerificationFormRef.value?.focusFirstInput?.()
      }, 400)
    } else {
      createVerificationFormRef.value?.reset?.()
    }
  }
)

watch(
  () => showUpdateVerificationDialog.value,
  async (isOpen) => {
    if (isOpen) {
      await nextTick()
      if (isProduction.value) {
        setTimeout(() => {
          updateVerificationFormRef.value?.startTimer?.()
        }, 100)
      }
      setTimeout(() => {
        updateVerificationFormRef.value?.focusFirstInput?.()
      }, 400)
    } else {
      updateVerificationFormRef.value?.reset?.()
    }
  }
)

onMounted(() => {
  fetchLevels()
})
</script>

<style scoped>
.text-error {
  color: var(--color-error, #EF4444);
}
</style>

