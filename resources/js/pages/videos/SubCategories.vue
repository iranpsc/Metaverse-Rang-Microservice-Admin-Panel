<template>
  <div class="p-6 space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
      <div>
        <h1 class="text-3xl font-bold text-[var(--theme-text-primary)]">مدیریت زیر دسته های ویدیو</h1>
        <p class="text-[var(--theme-text-secondary)]">ساخت و ویرایش زیر دسته های مرتبط با دسته بندی های آموزشی</p>
      </div>
      <Button
        variant="primary"
        size="lg"
        rounded="full"
        class="self-start md:self-auto"
        @click="openCreateModal"
      >
        ایجاد زیر دسته جدید
      </Button>
    </div>

    <div class="grid gap-4 md:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]">
      <SearchBox
        v-model="searchTerm"
        placeholder="جستجو بر اساس نام یا نامک"
        :debounce-ms="500"
        @search="handleSearch"
        @clear="handleClear"
      />
      <div class="flex items-center gap-3">
        <Select
          v-model="selectedCategoryFilter"
          :options="categoryOptions"
          option-value="value"
          option-label="label"
          placeholder="دسته بندی (همه)"
          label=""
          size="md"
        />
      </div>
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
        :data="subCategories"
        :pagination="pagination"
        empty-state-message="زیر دسته ای ثبت نشده است."
      >
        <template #cell-category="{ row }">
          <span class="text-sm text-[var(--theme-text-secondary)]">{{ row.category?.name || '-' }}</span>
        </template>

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
      title="ایجاد زیر دسته جدید"
      size="xl"
      close-on-backdrop
      @close="resetCreateForm"
    >
      <form class="space-y-5" @submit.prevent="submitCreate">
        <Select
          v-model="createForm.video_category_id"
          :options="categorySelectOptions"
          option-value="value"
          option-label="label"
          label="دسته بندی والد"
          placeholder="انتخاب دسته بندی"
          required
          :error="createErrors.video_category_id"
        />

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <Input
            v-model="createForm.name"
            label="نام"
            placeholder="نام زیر دسته"
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
            :loading="creating"
          >
            ثبت زیر دسته
          </Button>
        </div>
      </form>
    </Modal>

    <Modal
      v-model="editModalOpen"
      title="ویرایش زیر دسته"
      size="xl"
      close-on-backdrop
      @close="resetEditForm"
    >
      <form class="space-y-5" @submit.prevent="submitEdit">
        <Select
          v-model="editForm.video_category_id"
          :options="categorySelectOptions"
          option-value="value"
          option-label="label"
          label="دسته بندی والد"
          placeholder="انتخاب دسته بندی"
          required
          :error="editErrors.video_category_id"
        />

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <Input
            v-model="editForm.name"
            label="نام"
            required
            :error="editErrors.name"
          />
          <Input
            v-model="editForm.slug"
            label="نامک"
            :error="editErrors.slug"
          />
        </div>

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
              v-if="selectedSubCategory?.image_url"
              class="rounded-lg border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)]/60 p-3 text-sm text-[var(--theme-text-secondary)]"
            >
              تصویر فعلی:
              <a
                :href="selectedSubCategory.image_url"
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
              v-if="selectedSubCategory?.icon_url"
              class="rounded-lg border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)]/60 p-3 text-sm text-[var(--theme-text-secondary)]"
            >
              آیکون فعلی:
              <a
                :href="selectedSubCategory.icon_url"
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
          <Button variant="ghost" rounded="full" @click.prevent="closeEditModal">
            انصراف
          </Button>
          <Button
            type="submit"
            variant="primary"
            rounded="full"
            :loading="updating"
          >
            ذخیره تغییرات
          </Button>
        </div>
      </form>
    </Modal>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
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
  FileInput,
  Select
} from '../../components/ui'
import RichTextEditor from '../../components/ui/RichTextEditor.vue'
import TableActionIcon from '../../components/icons/TableActionIcon.vue'
import MediaCellButton from '../../components/ui/MediaCellButton.vue'
import { confirm } from '../../utils/notifications'

const { showToast } = useToast()

const loading = ref(true)
const creating = ref(false)
const updating = ref(false)
const deletingId = ref(null)
const error = ref(null)

const subCategories = ref([])
const pagination = ref(null)

const searchTerm = ref('')
const currentPage = ref(1)
const perPage = 10
const selectedCategoryFilter = ref('')

const createModalOpen = ref(false)
const editModalOpen = ref(false)
const selectedSubCategory = ref(null)

const categoryOptions = ref([{ value: '', label: 'همه دسته بندی ها' }])
const categorySelectOptions = ref([])

const createForm = reactive({
  video_category_id: '',
  name: '',
  slug: '',
  description: '',
  image: null,
  icon: null
})

const editForm = reactive({
  video_category_id: '',
  name: '',
  slug: '',
  description: '',
  image: null,
  icon: null
})

const createErrors = reactive({
  video_category_id: null,
  name: null,
  slug: null,
  description: null,
  image: null,
  icon: null
})

const editErrors = reactive({
  video_category_id: null,
  name: null,
  slug: null,
  description: null,
  image: null,
  icon: null
})

const tableColumns = computed(() => [
  {
    key: 'category',
    label: 'دسته بندی والد'
  },
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
  createForm.video_category_id = ''
  createForm.name = ''
  createForm.slug = ''
  createForm.description = ''
  createForm.image = null
  createForm.icon = null
  resetErrors(createErrors)
}

const resetEditForm = () => {
  editForm.video_category_id = ''
  editForm.name = ''
  editForm.slug = ''
  editForm.description = ''
  editForm.image = null
  editForm.icon = null
  selectedSubCategory.value = null
  resetErrors(editErrors)
}

const openCreateModal = () => {
  resetCreateForm()
  createModalOpen.value = true
}

const closeCreateModal = () => {
  createModalOpen.value = false
}

const openEditModal = (subCategory) => {
  selectedSubCategory.value = subCategory
  editForm.video_category_id = String(subCategory.video_category_id)
  editForm.name = subCategory.name
  editForm.slug = subCategory.slug || ''
  editForm.description = subCategory.description || ''
  editForm.image = null
  editForm.icon = null
  resetErrors(editErrors)
  editModalOpen.value = true
}

const closeEditModal = () => {
  editModalOpen.value = false
}

const normalizeErrors = (errors, target) => {
  Object.keys(target).forEach((key) => {
    target[key] = errors?.[key]?.[0] || null
  })
}

const fetchCategoryOptions = async () => {
  try {
    const response = await apiClient.get('/video-categories', {
      params: {
        per_page: 100,
        page: 1
      }
    })

    if (response.data.success) {
      const items = response.data.data.categories || []
      const options = items.map((category) => ({
        value: String(category.id),
        label: category.name
      }))
      categorySelectOptions.value = options
      categoryOptions.value = [{ value: '', label: 'همه دسته بندی ها' }, ...options]
    }
  } catch (err) {
    console.error('Video categories options fetch error:', err)
  }
}

const fetchSubCategories = async () => {
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

    if (selectedCategoryFilter.value) {
      params.video_category_id = selectedCategoryFilter.value
    }

    const response = await apiClient.get('/video-sub-categories', { params })

    if (response.data.success) {
      subCategories.value = response.data.data.sub_categories || []
      pagination.value = response.data.data.pagination || null
    } else {
      subCategories.value = []
      pagination.value = null
      error.value = response.data.message || 'خطا در دریافت زیر دسته ها'
    }
  } catch (err) {
    console.error('Video sub categories fetch error:', err)

    if (err.response && (err.response.status === 401 || err.response.status === 403)) {
      loading.value = false
      return
    }

    error.value = err.response?.data?.message || 'خطا در بارگذاری زیر دسته ها'
    subCategories.value = []
    pagination.value = null
  } finally {
    loading.value = false
  }
}

const handleSearch = () => {
  currentPage.value = 1
  fetchSubCategories()
}

const handleClear = () => {
  searchTerm.value = ''
  currentPage.value = 1
  fetchSubCategories()
}

const goToPage = (page) => {
  if (page >= 1 && (!pagination.value || page <= pagination.value.last_page)) {
    currentPage.value = page
    fetchSubCategories()
  }
}

const buildFormData = (form, includeSlug = true) => {
  const formData = new FormData()
  formData.append('video_category_id', form.video_category_id || '')
  formData.append('name', form.name || '')
  if (includeSlug) {
    formData.append('slug', form.slug || '')
  } else if (form.slug) {
    formData.append('slug', form.slug)
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

const submitCreate = async () => {
  resetErrors(createErrors)

  try {
    creating.value = true
    const formData = buildFormData(createForm, true)

    const response = await apiClient.post('/video-sub-categories', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    if (response.data.success) {
      showToast('زیر دسته با موفقیت ایجاد شد.', 'success')
      closeCreateModal()
      await fetchSubCategories()
    } else {
      showToast(response.data.message || 'خطا در ایجاد زیر دسته', 'error')
    }
  } catch (err) {
    if (err.response?.status === 422) {
      normalizeErrors(err.response.data.errors, createErrors)
      showToast('لطفا خطاهای فرم را بررسی کنید.', 'warning')
    } else {
      console.error('Video sub category create error:', err)
      showToast(err.response?.data?.message || 'خطا در ایجاد زیر دسته', 'error')
    }
  } finally {
    creating.value = false
  }
}

const submitEdit = async () => {
  if (!selectedSubCategory.value) {
    return
  }

  resetErrors(editErrors)

  try {
    updating.value = true
    const formData = buildFormData(editForm, false)
    formData.append('_method', 'PUT')

    const response = await apiClient.post(`/video-sub-categories/${selectedSubCategory.value.id}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    if (response.data.success) {
      showToast('زیر دسته با موفقیت به روزرسانی شد.', 'success')
      closeEditModal()
      await fetchSubCategories()
    } else {
      showToast(response.data.message || 'خطا در به روزرسانی زیر دسته', 'error')
    }
  } catch (err) {
    if (err.response?.status === 422) {
      normalizeErrors(err.response.data.errors, editErrors)
      showToast('لطفا خطاهای فرم را بررسی کنید.', 'warning')
    } else {
      console.error('Video sub category update error:', err)
      showToast(err.response?.data?.message || 'خطا در به روزرسانی زیر دسته', 'error')
    }
  } finally {
    updating.value = false
  }
}

const confirmDelete = async (subCategory) => {
  if (!subCategory || deletingId.value) {
    return
  }

  const result = await confirm('آیا از حذف این زیر دسته اطمینان دارید؟', 'حذف زیر دسته', {
    confirmText: 'بله، حذف شود',
    cancelText: 'انصراف'
  })

  if (!result.isConfirmed) {
    return
  }

  try {
    deletingId.value = subCategory.id
    const response = await apiClient.delete(`/video-sub-categories/${subCategory.id}`)
    if (response.data.success) {
      showToast('زیر دسته با موفقیت حذف شد.', 'success')
      await fetchSubCategories()
    } else {
      showToast(response.data.message || 'خطا در حذف زیر دسته', 'error')
    }
  } catch (err) {
    console.error('Video sub category delete error:', err)
    showToast(err.response?.data?.message || 'خطا در حذف زیر دسته', 'error')
  } finally {
    deletingId.value = null
  }
}

const openMedia = (url) => {
  if (!url) {
    return
  }
  window.open(url, '_blank', 'noopener')
}

watch(selectedCategoryFilter, () => {
  currentPage.value = 1
  fetchSubCategories()
})

onMounted(async () => {
  await Promise.all([fetchCategoryOptions(), fetchSubCategories()])
})
</script>

<style scoped>
.space-y-5 > :deep(* + *) {
  margin-top: 1.25rem;
}
</style>


