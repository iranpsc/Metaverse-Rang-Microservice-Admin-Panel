<template>
  <div class="p-6 space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
      <div>
        <h1 class="text-3xl font-bold text-[var(--theme-text-primary)]">مدیریت دسته بندی ویدیوها</h1>
        <p class="text-[var(--theme-text-secondary)]">ایجاد، ویرایش و مشاهده دسته بندی های آموزشی</p>
      </div>
      <Button
        variant="primary"
        size="lg"
        rounded="full"
        class="self-start md:self-auto"
        @click="openCreateModal"
      >
        ایجاد دسته بندی جدید
      </Button>
    </div>

    <div class="max-w-xl">
      <SearchBox
        v-model="searchTerm"
        placeholder="جستجو بر اساس نام یا نامک"
        :debounce-ms="500"
        @search="handleSearch"
        @clear="handleClear"
      />
    </div>

    <LoadingState v-if="loading" />

    <ErrorState
      v-else-if="error"
      :message="error"
      variant="error"
    />

    <div v-else class="space-y-6">
      <Table
        :columns="tableColumns"
        :data="categories"
        :pagination="pagination"
        :show-row-number="true"
        empty-state-message="دسته بندی ویدیو ثبت نشده است."
      >
        <template #cell-image_url="{ row }">
          <MediaCellButton
            :url="row.image_url"
            title="مشاهده تصویر"
            @open="openMedia"
          />
        </template>

        <template #cell-icon_url="{ row }">
          <MediaCellButton
            :url="row.icon_url"
            title="مشاهده آیکون"
            @open="openMedia"
          />
        </template>

        <template #cell-actions="{ row }">
          <div class="flex flex-wrap gap-2">
            <Button
              variant="glass"
              size="sm"
              rounded="full"
              class="!p-2 !gap-0 min-w-[2.25rem]"
              title="ویرایش"
              aria-label="ویرایش"
              @click="openEditModal(row)"
            >
              <template #icon-left>
                <TableActionIcon name="edit" />
              </template>
            </Button>
            <Button
              variant="danger"
              size="sm"
              rounded="full"
              class="!p-2 !gap-0 min-w-[2.25rem]"
              title="حذف"
              aria-label="حذف"
              :loading="deletingId === row.id"
              @click="confirmDelete(row)"
            >
              <template #icon-left>
                <TableActionIcon name="delete" />
              </template>
            </Button>
          </div>
        </template>
      </Table>

      <Pagination
        v-if="pagination && pagination.total > 0"
        :pagination="pagination"
        :disabled="loading"
        @page-change="goToPage"
      />
    </div>

    <Modal
      v-model="createModalOpen"
      title="ایجاد دسته بندی جدید"
      size="xl"
      close-on-backdrop
      @close="resetCreateForm"
    >
      <form class="space-y-5" @submit.prevent="submitCreate">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <Input
            v-model="createForm.name"
            label="نام"
            placeholder="نام دسته بندی"
            required
            :error="createErrors.name"
          />
          <Input
            v-model="createForm.slug"
            label="نامک"
            placeholder="slug-example"
            required
            :error="createErrors.slug"
          />
        </div>

        <RichTextEditor
          v-model="createForm.description"
          label="توضیحات"
          required
          :error="createErrors.description"
        />

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <FileInput
            v-model="createForm.image"
            label="تصویر"
            required
            accept="image/*"
            :error="createErrors.image"
          />
          <FileInput
            v-model="createForm.icon"
            label="آیکون (فایل SVG)"
            required
            accept="image/svg+xml"
            :error="createErrors.icon"
          />
        </div>

        <div class="flex items-center justify-end gap-3 pt-4">
          <Button variant="ghost" rounded="full" @click.prevent="closeCreateModal">
            انصراف
          </Button>
          <Button
            type="submit"
            variant="primary"
            rounded="full"
            :loading="isProduction && !isVerified ? sendingVerification : creating"
          >
            {{ createSubmitLabel }}
          </Button>
        </div>
      </form>
    </Modal>

    <Modal
      v-model="editModalOpen"
      title="ویرایش دسته بندی"
      size="xl"
      close-on-backdrop
      @close="resetEditForm"
    >
      <form class="space-y-5" @submit.prevent="submitEdit">
        <Input
          v-model="editForm.name"
          label="نام"
          required
          :error="editErrors.name"
        />

        <RichTextEditor
          v-model="editForm.description"
          label="توضیحات"
          required
          :error="editErrors.description"
        />

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div class="space-y-3">
            <FileInput
              v-model="editForm.image"
              label="تصویر جدید (اختیاری)"
              accept="image/*"
              :error="editErrors.image"
            />
            <div
              v-if="selectedCategory?.image_url"
              class="rounded-lg border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)]/60 p-3 text-sm text-[var(--theme-text-secondary)]"
            >
              تصویر فعلی:
              <a
                :href="selectedCategory.image_url"
                target="_blank"
                rel="noopener"
                class="inline-flex items-center gap-1 text-primary-300 hover:underline"
                title="مشاهده تصویر"
                aria-label="مشاهده تصویر"
              >
                <TableActionIcon name="image" icon-class="w-4 h-4 shrink-0" />
              </a>
            </div>
          </div>

          <div class="space-y-3">
            <FileInput
              v-model="editForm.icon"
              label="آیکون جدید (اختیاری)"
              accept="image/svg+xml"
              :error="editErrors.icon"
            />
            <div
              v-if="selectedCategory?.icon_url"
              class="rounded-lg border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)]/60 p-3 text-sm text-[var(--theme-text-secondary)]"
            >
              آیکون فعلی:
              <a
                :href="selectedCategory.icon_url"
                target="_blank"
                rel="noopener"
                class="inline-flex items-center gap-1 text-primary-300 hover:underline"
                title="مشاهده آیکون"
                aria-label="مشاهده آیکون"
              >
                <TableActionIcon name="image" icon-class="w-4 h-4 shrink-0" />
              </a>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4">
          <Button variant="ghost" rounded="full" @click.prevent="closeEditModal()">
            انصراف
          </Button>
          <Button
            type="submit"
            variant="primary"
            rounded="full"
            :loading="isProduction && !isVerified ? sendingVerification : updating"
          >
            {{ editSubmitLabel }}
          </Button>
        </div>
      </form>
    </Modal>

    <PhoneVerificationModal :phone-verification="phoneVerification" />
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import apiClient from '../../utils/api'
import { useToast } from '../../composables/useToast'
import {
  Table,
  Pagination,
  SearchBox,
  LoadingState,
  ErrorState,
  Button,
  Modal,
  Input,
  FileInput
} from '../../components/ui'
import RichTextEditor from '../../components/ui/RichTextEditor.vue'
import TableActionIcon from '../../components/icons/TableActionIcon.vue'
import MediaCellButton from '../../components/ui/MediaCellButton.vue'
import PhoneVerificationModal from '../../components/PhoneVerificationModal.vue'
import { usePhoneVerification, applyVerificationPayload } from '../../composables/usePhoneVerification'

const { showToast } = useToast()
const phoneVerification = usePhoneVerification()
const {
  isProduction,
  isVerified,
  sendingVerification,
  beginVerifyForSubmit,
  getSubmitPayload,
  confirmThenVerify,
  handleApiVerificationError,
  resetVerificationState
} = phoneVerification

const createSubmitLabel = computed(() =>
  isProduction.value && !isVerified.value ? 'ارسال کد تایید' : 'ثبت دسته بندی'
)
const editSubmitLabel = computed(() =>
  isProduction.value && !isVerified.value ? 'ارسال کد تایید' : 'ذخیره تغییرات'
)

const loading = ref(true)
const creating = ref(false)
const updating = ref(false)
const deletingId = ref(null)
const error = ref(null)

const categories = ref([])
const pagination = ref(null)

const searchTerm = ref('')
const currentPage = ref(1)
const perPage = 10

const createModalOpen = ref(false)
const editModalOpen = ref(false)
const selectedCategory = ref(null)

const createForm = reactive({
  name: '',
  slug: '',
  description: '',
  image: null,
  icon: null
})

const editForm = reactive({
  name: '',
  description: '',
  image: null,
  icon: null
})

const createErrors = reactive({
  name: null,
  slug: null,
  description: null,
  image: null,
  icon: null
})

const editErrors = reactive({
  name: null,
  description: null,
  image: null,
  icon: null
})

const tableColumns = computed(() => [
  {
    key: 'name',
    label: 'نام'
  },
  {
    key: 'slug',
    label: 'نامک'
  },
  {
    key: 'image_url',
    label: 'تصویر',
    textSecondary: true
  },
  {
    key: 'icon_url',
    label: 'آیکون',
    textSecondary: true
  },
  {
    key: 'sub_categories_count',
    label: 'تعداد زیر دسته',
    defaultValue: 0
  },
  {
    key: 'created_at_formatted.date',
    label: 'تاریخ ایجاد',
    textSecondary: true
  },
  {
    key: 'created_at_formatted.time',
    label: 'ساعت ایجاد',
    textSecondary: true
  },
  {
    key: 'actions',
    label: 'مدیریت'
  }
])

const resetErrors = (target) => {
  Object.keys(target).forEach((key) => {
    target[key] = null
  })
}

const resetCreateForm = () => {
  createForm.name = ''
  createForm.slug = ''
  createForm.description = ''
  createForm.image = null
  createForm.icon = null
  resetErrors(createErrors)
}

const resetEditForm = () => {
  editForm.name = ''
  editForm.description = ''
  editForm.image = null
  editForm.icon = null
  selectedCategory.value = null
  resetErrors(editErrors)
}

const openCreateModal = () => {
  resetVerificationState()
  resetCreateForm()
  createModalOpen.value = true
}

const closeCreateModal = () => {
  createModalOpen.value = false
  resetVerificationState()
}

const openEditModal = (category) => {
  resetVerificationState()
  selectedCategory.value = category
  editForm.name = category.name
  editForm.description = category.description || ''
  editForm.image = null
  editForm.icon = null
  resetErrors(editErrors)
  editModalOpen.value = true
}

const closeEditModal = () => {
  editModalOpen.value = false
  resetVerificationState()
}

const normalizeErrors = (errors, target) => {
  Object.keys(target).forEach((key) => {
    target[key] = errors?.[key]?.[0] || null
  })
}

const fetchCategories = async () => {
  try {
    loading.value = true
    error.value = null

    const params = {
      page: currentPage.value,
      per_page: perPage
    }

    if (searchTerm.value.trim()) {
      params.search = searchTerm.value.trim()
    }

    const response = await apiClient.get('/video-categories', { params })

    if (response.data.success) {
      categories.value = response.data.data.categories || []
      pagination.value = response.data.data.pagination || null
    } else {
      categories.value = []
      pagination.value = null
      error.value = response.data.message || 'خطا در دریافت دسته بندی ها'
    }
  } catch (err) {
    console.error('Video categories fetch error:', err)

    if (err.response && (err.response.status === 401 || err.response.status === 403)) {
      loading.value = false
      return
    }

    error.value = err.response?.data?.message || 'خطا در بارگذاری دسته بندی ها'
    categories.value = []
    pagination.value = null
  } finally {
    loading.value = false
  }
}

const handleSearch = () => {
  currentPage.value = 1
  fetchCategories()
}

const handleClear = () => {
  searchTerm.value = ''
  currentPage.value = 1
  fetchCategories()
}

const goToPage = (page) => {
  if (page >= 1 && (!pagination.value || page <= pagination.value.last_page)) {
    currentPage.value = page
    fetchCategories()
  }
}

const buildFormData = (form, includeSlug) => {
  const formData = new FormData()
  formData.append('name', form.name || '')
  if (includeSlug) {
    formData.append('slug', form.slug || '')
  }
  formData.append('description', form.description || '')

  if (form.image instanceof File) {
    formData.append('image', form.image)
  }

  if (form.icon instanceof File) {
    formData.append('icon', form.icon)
  }

  return formData
}

const performCreate = async (verificationPayload = {}) => {
  try {
    creating.value = true
    const formData = applyVerificationPayload(buildFormData(createForm, true), verificationPayload)

    const response = await apiClient.post('/video-categories', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    if (response.data.success) {
      showToast('دسته بندی با موفقیت ایجاد شد.', 'success')
      resetVerificationState()
      closeCreateModal()
      await fetchCategories()
    } else {
      showToast(response.data.message || 'خطا در ایجاد دسته بندی', 'error')
    }
  } catch (err) {
    if (await handleApiVerificationError(err)) {
      return
    }

    if (err.response?.status === 422) {
      normalizeErrors(err.response.data.errors, createErrors)
      showToast('لطفا خطاهای فرم را بررسی کنید.', 'warning')
    } else {
      console.error('Video category create error:', err)
      showToast(err.response?.data?.message || 'خطا در ایجاد دسته بندی', 'error')
    }
  } finally {
    creating.value = false
  }
}

const submitCreate = async () => {
  resetErrors(createErrors)

  if (isProduction.value && !isVerified.value) {
    await beginVerifyForSubmit()
    return
  }

  await performCreate(getSubmitPayload())
}

const performEdit = async (verificationPayload = {}) => {
  if (!selectedCategory.value) {
    return
  }

  try {
    updating.value = true
    const formData = applyVerificationPayload(buildFormData(editForm, false), verificationPayload)
    formData.append('_method', 'PUT')

    const response = await apiClient.post(`/video-categories/${selectedCategory.value.id}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    if (response.data.success) {
      showToast('دسته بندی با موفقیت به روزرسانی شد.', 'success')
      resetVerificationState()
      closeEditModal()
      await fetchCategories()
    } else {
      showToast(response.data.message || 'خطا در به روزرسانی دسته بندی', 'error')
    }
  } catch (err) {
    if (await handleApiVerificationError(err)) {
      return
    }

    if (err.response?.status === 422) {
      normalizeErrors(err.response.data.errors, editErrors)
      showToast('لطفا خطاهای فرم را بررسی کنید.', 'warning')
    } else {
      console.error('Video category update error:', err)
      showToast(err.response?.data?.message || 'خطا در به روزرسانی دسته بندی', 'error')
    }
  } finally {
    updating.value = false
  }
}

const submitEdit = async () => {
  if (!selectedCategory.value) {
    return
  }

  resetErrors(editErrors)

  if (isProduction.value && !isVerified.value) {
    await beginVerifyForSubmit()
    return
  }

  await performEdit(getSubmitPayload())
}

const confirmDelete = async (category) => {
  if (!category || deletingId.value) {
    return
  }

  await phoneVerification.confirmThenVerify(
    {
      message: 'آیا از حذف این دسته بندی اطمینان دارید؟',
      title: 'حذف دسته بندی',
      confirmText: 'بله، حذف شود',
      cancelText: 'انصراف'
    },
    async (payload) => {
      try {
        deletingId.value = category.id
        await apiClient.delete(`/video-categories/${category.id}`, { data: payload })
        showToast('دسته بندی با موفقیت حذف شد.', 'success')
        phoneVerification.resetVerificationState()
        await fetchCategories()
      } catch (err) {
        console.error('Video category delete error:', err)

        if (await phoneVerification.handleApiVerificationError(err)) {
          return
        }

        showToast(err.response?.data?.message || 'خطا در حذف دسته بندی', 'error')
      } finally {
        deletingId.value = null
      }
    }
  )
}

const openMedia = (url) => {
  if (!url) {
    return
  }
  window.open(url, '_blank', 'noopener')
}

onMounted(() => {
  fetchCategories()
})
</script>

<style scoped>
.space-y-5 > :deep(* + *) {
  margin-top: 1.25rem;
}
</style>


