<template>
  <div class="p-6 space-y-6" dir="rtl">
    <PageHeader
      title="مدیریت سطوح"
      subtitle="ایجاد، ویرایش و مدیریت سطوح متاورس"
    />
    <div>
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
        @page-change="onPageChange"
      />
    </template>

    <!-- Create / Update Level Modal -->
    <Modal
      :model-value="isFormModalOpen"
      @update:model-value="handleFormModalToggle"
      :title="formModalTitle"
      size="xl"
    >
      <div class="space-y-6" dir="rtl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <Input
            v-model="form.name"
            label="نام سطح"
            required
            :error="formErrors.name"
          />
          <Input
            v-model="form.slug"
            label="نامک"
            required
            :error="formErrors.slug"
          />
          <Input
            v-model="form.score"
            label="امتیاز مورد نیاز"
            type="number"
            min="0"
            required
            :error="formErrors.score"
          />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="space-y-3">
            <FileInput
              v-model="form.image"
              label="تصویر"
              accept="image/*"
              :error="formErrors.image"
              :helper-text="imageHelperText"
            />
            <p v-if="isEditMode && form.existingImageUrl" class="text-xs text-[var(--theme-text-secondary)]">
              تصویر فعلی:
              <a
                :href="form.existingImageUrl"
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
              v-model="form.backgroundImage"
              label="تصویر پس زمینه"
              accept="image/*"
              :required="!isEditMode"
              :error="formErrors.background_image"
              :helper-text="backgroundHelperText"
            />
            <p v-if="isEditMode && form.existingBackgroundUrl" class="text-xs text-[var(--theme-text-secondary)]">
              تصویر فعلی:
              <a
                :href="form.existingBackgroundUrl"
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
            :loading="formSubmitting"
            @click="handleFormSubmit"
          >
            {{ formSubmitLabel }}
          </Button>
          <Button
            variant="danger"
            rounded="full"
            :disabled="formSubmitting"
            @click="closeFormModal"
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
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { Table, Pagination, Button, Modal, Input, LoadingState, ErrorState, FileInput, PageHeader } from '../../components/ui'
import { usePaginatedList } from '../../composables/usePaginatedList'
import { useToast } from '../../composables/useToast'
import { confirm } from '../../utils/notifications'
import { useLevels } from '../../composables/useLevels'
import TableActionIcon from '../../components/icons/TableActionIcon.vue'

const { showToast } = useToast()
const {
  fetchLevels: fetchLevelsApi,
  createLevel,
  updateLevel,
  deleteLevel
} = useLevels()

const router = useRouter()

const {
  loading,
  error,
  pagination,
  execute,
  goToPage,
  buildParams
} = usePaginatedList({ perPage: 10 })

const levels = ref([])

const isFormModalOpen = ref(false)
const isInfoModalOpen = ref(false)

const selectedLevel = ref(null)
const editingLevel = ref(null)

const isEditMode = computed(() => Boolean(editingLevel.value))
const formModalTitle = computed(() => isEditMode.value ? 'ویرایش سطح' : 'تعریف سطح جدید')
const formSubmitLabel = computed(() => isEditMode.value ? 'ثبت تغییرات' : 'ثبت')
const imageHelperText = computed(() => (
  isEditMode.value
    ? 'عدم انتخاب فایل به معنی حفظ تصویر فعلی است'
    : 'فرمت‌های مجاز: jpg، jpeg، png، bmp'
))
const backgroundHelperText = computed(() => (
  isEditMode.value
    ? 'عدم انتخاب فایل به معنی حفظ تصویر فعلی است'
    : 'حداکثر حجم 5 مگابایت'
))

const form = reactive({
  name: '',
  slug: '',
  score: '',
  image: null,
  backgroundImage: null,
  existingImageUrl: null,
  existingBackgroundUrl: null
})

const formErrors = reactive({})
const formSubmitting = ref(false)

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

const clearLevels = () => {
  levels.value = []
  pagination.value = null
}

const fetchLevels = () => execute(async () => {
  const response = await fetchLevelsApi(buildParams())

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
    clearLevels()
  }
}, {
  onClear: clearLevels,
  logLabel: 'Levels fetch error:',
  fallbackMessage: 'خطا در بارگذاری اطلاعات'
})

const onPageChange = (page) => goToPage(page, fetchLevels)

const resetForm = () => {
  const level = editingLevel.value

  form.name = level?.name || ''
  form.slug = level?.slug || ''
  form.score = level?.score ?? ''
  form.image = null
  form.backgroundImage = null
  form.existingImageUrl = level?.image || null
  form.existingBackgroundUrl = level?.background_image || null
  Object.keys(formErrors).forEach((key) => delete formErrors[key])
}

const validateForm = () => {
  Object.keys(formErrors).forEach((key) => delete formErrors[key])

  if (!form.name) {
    formErrors.name = 'نام سطح را وارد کنید'
  }

  if (!form.slug) {
    formErrors.slug = 'نامک را وارد کنید'
  }

  if (form.score === '' || Number(form.score) < 0) {
    formErrors.score = 'امتیاز معتبر وارد کنید'
  }

  if (!isEditMode.value && !form.backgroundImage) {
    formErrors.background_image = 'انتخاب تصویر پس زمینه الزامی است'
  }

  return Object.keys(formErrors).length === 0
}

watch(
  () => form.image,
  (file) => {
    if (file && formErrors.image) {
      delete formErrors.image
    }
  }
)

watch(
  () => form.backgroundImage,
  (file) => {
    if (file && formErrors.background_image) {
      delete formErrors.background_image
    }
  }
)

const buildFormPayload = () => ({
  name: form.name,
  slug: form.slug,
  score: form.score,
  image: form.image,
  backgroundImage: form.backgroundImage
})

const buildLevelFormData = (payload) => {
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

  return formData
}

const submitLevelForm = async () => {
  try {
    formSubmitting.value = true
    Object.keys(formErrors).forEach((key) => delete formErrors[key])

    const payload = buildFormPayload()
    const formData = buildLevelFormData(payload)

    let response

    if (isEditMode.value) {
      formData.append('_method', 'PUT')
      response = await updateLevel(editingLevel.value.id, formData)
    } else {
      response = await createLevel(formData)
    }

    if (response.data?.success) {
      showToast(
        response.data?.message || (isEditMode.value ? 'سطح با موفقیت بروزرسانی شد' : 'سطح با موفقیت ایجاد شد'),
        'success'
      )
      closeFormModal()
      fetchLevels()
    } else {
      showToast(
        response.data?.message || (isEditMode.value ? 'خطا در بروزرسانی سطح' : 'خطا در ثبت سطح'),
        'error'
      )
    }
  } catch (err) {
    console.error(isEditMode.value ? 'Update level error:' : 'Create level error:', err)

    if (err.response?.status === 422 && err.response?.data?.errors) {
      const errorsBag = err.response.data.errors

      Object.keys(errorsBag).forEach((field) => {
        const message = Array.isArray(errorsBag[field]) ? errorsBag[field][0] : errorsBag[field]
        formErrors[field] = message
      })
    } else {
      showToast(
        err.response?.data?.message || (isEditMode.value ? 'خطا در بروزرسانی سطح' : 'خطا در ثبت سطح'),
        'error'
      )
    }
  } finally {
    formSubmitting.value = false
  }
}

const handleFormSubmit = async () => {
  if (formSubmitting.value) return
  if (isEditMode.value && !editingLevel.value) return

  if (!validateForm()) {
    return
  }

  await submitLevelForm()
}

const handleDelete = async (level) => {
  const result = await confirm(
    'آیا از حذف این سطح اطمینان دارید؟',
    'حذف سطح',
    { confirmText: 'بله، حذف شود', cancelText: 'انصراف' }
  )
  if (!result.isConfirmed) return

  try {
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
  editingLevel.value = null
  resetForm()
  isFormModalOpen.value = true
}

const openUpdateModal = (level) => {
  editingLevel.value = level
  resetForm()
  isFormModalOpen.value = true
}

const closeFormModal = () => {
  isFormModalOpen.value = false
  editingLevel.value = null
  resetForm()
}

const handleFormModalToggle = (value) => {
  if (!value) {
    closeFormModal()
  } else {
    isFormModalOpen.value = true
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

watch(isFormModalOpen, (isOpen) => {
  if (!isOpen) {
    resetForm()
  }
})

onMounted(() => {
  fetchLevels()
})
</script>

<style scoped>
.text-error {
  color: var(--color-error, #EF4444);
}
</style>

