<template>
  <div class="p-6 space-y-6">
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-[var(--theme-text-primary)] mb-2">متغیرهای سیستم</h1>
      <p class="text-[var(--theme-text-secondary)]">مدیریت، ایجاد و ویرایش متغیرهای سیستمی</p>
    </div>

    <!-- Actions -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <SearchBox
        v-model="searchTerm"
        placeholder="جستجو بر اساس نام یا اسلاگ..."
        :debounce-ms="400"
        container-class="w-full md:max-w-md"
        @search="handleSearch"
        @clear="handleClear"
      />

      <Button
        variant="primary"
        rounded="full"
        class="w-full md:w-auto"
        @click="openCreateModal"
      >
        ایجاد متغیر جدید
      </Button>
    </div>

    <!-- Loading State -->
    <LoadingState v-if="loading" />

    <!-- Error State -->
    <ErrorState
      v-else-if="error"
      :message="error"
      variant="error"
    />

    <!-- Variables Table -->
    <div v-else class="space-y-4">
      <Table
        :columns="tableColumns"
        :data="variables"
        :pagination="pagination"
        empty-state-message="متغیری ثبت نشده است"
      >
        <template #cell-value="{ value }">
          <span class="font-mono">{{ formatNumber(value) }}</span>
        </template>

        <template #cell-updated_at="{ value }">
          <div class="flex flex-col" dir="rtl">
            <span>{{ formatDate(value) }}</span>
            <span class="text-[var(--theme-text-secondary)] text-xs">{{ formatTime(value) }}</span>
          </div>
        </template>

        <template #cell-actions="{ row }">
          <div class="flex flex-wrap gap-2" dir="rtl">
            <Button
              size="sm"
              variant="glass"
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
              size="sm"
              variant="danger"
              rounded="full"
              class="!p-2 !gap-0 min-w-[2.25rem]"
              title="حذف"
              aria-label="حذف"
              :loading="deletingId === row.id"
              @click="handleDelete(row)"
            >
              <template #icon-left>
                <TableActionIcon name="delete" />
              </template>
            </Button>
            <Button
              v-if="row.change_logs && row.change_logs.length"
              size="sm"
              variant="secondary"
              @click="openHistoryModal(row)"
            >
              تاریخچه تغییرات
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

    <!-- Create Modal -->
    <Modal
      :model-value="showCreateModal"
      @update:model-value="closeCreateModal"
      title="ایجاد متغیر سیستم"
      size="lg"
    >
      <div class="space-y-5" dir="rtl">
        <Input
          v-model="createForm.name"
          label="نام متغیر"
          placeholder="نام متغیر را وارد کنید"
          :error="createErrors.name"
          required
        />

        <Input
          v-model="createForm.slug"
          label="اسلاگ"
          placeholder="اسلاگ یکتا وارد کنید"
          :error="createErrors.slug"
          required
        />

        <Input
          v-model="createForm.value"
          type="number"
          step="any"
          label="مقدار"
          placeholder="مقدار متغیر را وارد کنید"
          :error="createErrors.value"
          required
        />
      </div>

      <template #footer>
        <div class="flex justify-end gap-3" dir="rtl">
          <Button
            variant="primary"
            :loading="submittingCreate"
            @click="submitCreate"
          >
            ثبت
          </Button>
          <Button
            variant="danger"
            :disabled="submittingCreate"
            @click="closeCreateModal"
          >
            بستن
          </Button>
        </div>
      </template>
    </Modal>

    <!-- Edit Modal -->
    <Modal
      :model-value="showEditModal"
      @update:model-value="closeEditModal"
      title="ویرایش متغیر سیستم"
      size="lg"
    >
      <div class="space-y-5" dir="rtl">
        <Input
          v-model="editForm.name"
          label="نام متغیر"
          placeholder="نام متغیر را وارد کنید"
          :error="editErrors.name"
          required
        />

        <Input
          v-model="editForm.slug"
          label="اسلاگ"
          placeholder="اسلاگ یکتا وارد کنید"
          :error="editErrors.slug"
          required
        />

        <Input
          v-model="editForm.value"
          type="number"
          step="any"
          label="مقدار"
          placeholder="مقدار متغیر را وارد کنید"
          :error="editErrors.value"
          required
        />

        <div>
          <label class="block text-sm font-medium text-[var(--theme-text-primary)] mb-2" for="edit-note">یادداشت (اختیاری)</label>
          <textarea
            id="edit-note"
            v-model="editForm.note"
            rows="3"
            class="w-full rounded-lg border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)] text-[var(--theme-text-primary)] focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500 px-4 py-3"
            placeholder="توضیح مختصری درباره این تغییر وارد کنید"
          ></textarea>
          <p v-if="editErrors.note" class="mt-1 text-sm text-red-500">{{ editErrors.note }}</p>
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end gap-3" dir="rtl">
          <Button
            variant="primary"
            :loading="submittingEdit"
            @click="submitEdit"
          >
            ثبت تغییرات
          </Button>
          <Button
            variant="danger"
            :disabled="submittingEdit"
            @click="closeEditModal"
          >
            بستن
          </Button>
        </div>
      </template>
    </Modal>

    <!-- History Modal -->
    <Modal
      :model-value="showHistoryModal"
      @update:model-value="closeHistoryModal"
      :title="historyModalTitle"
      size="xl"
    >
      <div v-if="selectedVariable?.change_logs?.length" class="space-y-4" dir="rtl">
        <Table
          :columns="historyColumns"
          :data="selectedVariable.change_logs"
          :show-row-number="true"
          empty-state-message="تاریخچه ای یافت نشد"
        >
          <template #cell-previous_value="{ value }">
            <span class="font-mono">{{ formatNumber(value) }}</span>
          </template>
          <template #cell-current_value="{ value }">
            <span class="font-mono">{{ formatNumber(value) }}</span>
          </template>
          <template #cell-created_at="{ value }">
            <div class="flex flex-col">
              <span>{{ formatDate(value) }}</span>
              <span class="text-[var(--theme-text-secondary)] text-xs">{{ formatTime(value) }}</span>
            </div>
          </template>
        </Table>
      </div>
      <div v-else class="py-8 text-center text-[var(--theme-text-secondary)]">
        تاریخچه ای برای این متغیر ثبت نشده است
      </div>

      <template #footer>
        <div class="flex justify-end" dir="rtl">
          <Button variant="danger" @click="closeHistoryModal">
            بستن
          </Button>
        </div>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import apiClient from '../../utils/api'
import { Button, ErrorState, Input, LoadingState, Modal, Pagination, SearchBox, Table } from '../../components/ui'
import { useToast } from '../../composables/useToast'
import { confirm } from '../../utils/notifications'
import TableActionIcon from '../../components/icons/TableActionIcon.vue'

const { showToast } = useToast()

const loading = ref(true)
const error = ref(null)
const variables = ref([])
const pagination = ref(null)
const currentPage = ref(1)
const searchTerm = ref('')

const showCreateModal = ref(false)
const showEditModal = ref(false)
const showHistoryModal = ref(false)

const submittingCreate = ref(false)
const submittingEdit = ref(false)
const deletingId = ref(null)

const selectedVariable = ref(null)

const createForm = reactive({
  name: '',
  slug: '',
  value: ''
})

const editForm = reactive({
  id: null,
  name: '',
  slug: '',
  value: '',
  note: ''
})

const createErrors = reactive({})
const editErrors = reactive({})

const tableColumns = [
  {
    key: 'name',
    label: 'نام متغیر'
  },
  {
    key: 'slug',
    label: 'اسلاگ'
  },
  {
    key: 'value',
    label: 'مقدار'
  },
  {
    key: 'updated_at',
    label: 'آخرین بروزرسانی',
    textSecondary: true
  },
  {
    key: 'actions',
    label: 'مدیریت'
  }
]

const historyColumns = [
  {
    key: 'changer_name',
    label: 'تغییر دهنده'
  },
  {
    key: 'previous_value',
    label: 'مقدار قبلی'
  },
  {
    key: 'current_value',
    label: 'مقدار فعلی'
  },
  {
    key: 'note',
    label: 'یادداشت',
    defaultValue: '-'
  },
  {
    key: 'created_at',
    label: 'زمان تغییر',
    textSecondary: true
  }
]

const historyModalTitle = computed(() => {
  if (!selectedVariable.value) {
    return 'تاریخچه تغییرات'
  }

  return `تاریخچه تغییرات - ${selectedVariable.value.name}`
})

const formatNumber = (value) => {
  if (value === null || value === undefined || value === '') {
    return '-'
  }

  const parsed = Number(value)
  if (Number.isNaN(parsed)) {
    return value
  }

  return parsed.toLocaleString('fa-IR')
}

const formatDate = (value) => {
  if (!value) {
    return '-'
  }

  const date = new Date(value)
  if (Number.isNaN(date.getTime())) {
    return '-'
  }

  return date.toLocaleDateString('fa-IR')
}

const formatTime = (value) => {
  if (!value) {
    return '-'
  }

  const date = new Date(value)
  if (Number.isNaN(date.getTime())) {
    return '-'
  }

  return date.toLocaleTimeString('fa-IR', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  })
}

const resetCreateForm = () => {
  createForm.name = ''
  createForm.slug = ''
  createForm.value = ''
  Object.keys(createErrors).forEach((key) => {
    delete createErrors[key]
  })
}

const resetEditForm = () => {
  editForm.id = null
  editForm.name = ''
  editForm.slug = ''
  editForm.value = ''
  editForm.note = ''
  Object.keys(editErrors).forEach((key) => {
    delete editErrors[key]
  })
}

const buildPagination = (payload) => {
  if (!payload) {
    return null
  }

  return {
    current_page: payload.current_page,
    last_page: payload.last_page,
    per_page: payload.per_page,
    total: payload.total,
    from: payload.from,
    to: payload.to
  }
}

const fetchVariables = async () => {
  try {
    loading.value = true
    error.value = null

    const params = {
      page: currentPage.value,
      per_page: 10
    }

    if (searchTerm.value) {
      params.search = searchTerm.value
    }

    const response = await apiClient.get('/system-variables', { params })

    if (!response.data.success) {
      error.value = response.data.message || 'خطا در دریافت اطلاعات'
      variables.value = []
      pagination.value = null
      return
    }

    variables.value = response.data.data.variables || []
    pagination.value = buildPagination(response.data.data.pagination)
  } catch (err) {
    console.error('System variables fetch error:', err)

    if (err.response && (err.response.status === 401 || err.response.status === 403)) {
      variables.value = []
      pagination.value = null
      loading.value = false
      return
    }

    error.value = err.response?.data?.message || 'خطا در بارگذاری اطلاعات'
    variables.value = []
    pagination.value = null
  } finally {
    loading.value = false
  }
}

const handleSearch = () => {
  currentPage.value = 1
  fetchVariables()
}

const handleClear = () => {
  if (!searchTerm.value) {
    return
  }
  searchTerm.value = ''
  currentPage.value = 1
  fetchVariables()
}

const goToPage = (page) => {
  if (!pagination.value) {
    return
  }

  if (page < 1 || page > pagination.value.last_page) {
    return
  }

  currentPage.value = page
  fetchVariables()
}

const openCreateModal = () => {
  resetCreateForm()
  showCreateModal.value = true
}

const closeCreateModal = () => {
  showCreateModal.value = false
  resetCreateForm()
}

const openEditModal = (variable) => {
  resetEditForm()
  editForm.id = variable.id
  editForm.name = variable.name
  editForm.slug = variable.slug
  editForm.value = variable.value
  editForm.note = ''
  selectedVariable.value = variable
  showEditModal.value = true
}

const closeEditModal = () => {
  showEditModal.value = false
  resetEditForm()
  selectedVariable.value = null
}

const openHistoryModal = (variable) => {
  selectedVariable.value = variable
  showHistoryModal.value = true
}

const closeHistoryModal = () => {
  showHistoryModal.value = false
  selectedVariable.value = null
}

const handleValidationErrors = (errors, target) => {
  Object.keys(target).forEach((key) => {
    delete target[key]
  })

  if (!errors) {
    return
  }

  Object.entries(errors).forEach(([field, messages]) => {
    target[field] = Array.isArray(messages) ? messages[0] : messages
  })
}

const submitCreate = async () => {
  submittingCreate.value = true
  handleValidationErrors(null, createErrors)

  try {
    const payload = {
      name: createForm.name,
      slug: createForm.slug,
      value: createForm.value
    }

    const response = await apiClient.post('/system-variables', payload)

    if (response.data.success) {
      showToast(response.data.message || 'متغیر با موفقیت ثبت شد', 'success')
      closeCreateModal()
      fetchVariables()
    }
  } catch (err) {
    console.error('Create system variable error:', err)

    if (err.response?.status === 422) {
      handleValidationErrors(err.response.data.errors, createErrors)
      return
    }

    showToast(err.response?.data?.message || 'خطا در ثبت متغیر', 'error')
  } finally {
    submittingCreate.value = false
  }
}

const submitEdit = async () => {
  if (!editForm.id) {
    return
  }

  submittingEdit.value = true
  handleValidationErrors(null, editErrors)

  try {
    const payload = {
      name: editForm.name,
      slug: editForm.slug,
      value: editForm.value,
      note: editForm.note || null
    }

    const response = await apiClient.put(`/system-variables/${editForm.id}`, payload)

    if (response.data.success) {
      showToast(response.data.message || 'متغیر با موفقیت بروزرسانی شد', 'success')
      closeEditModal()
      fetchVariables()
    }
  } catch (err) {
    console.error('Update system variable error:', err)

    if (err.response?.status === 422) {
      handleValidationErrors(err.response.data.errors, editErrors)
      return
    }

    showToast(err.response?.data?.message || 'خطا در بروزرسانی متغیر', 'error')
  } finally {
    submittingEdit.value = false
  }
}

const handleDelete = async (variable) => {
  const result = await confirm(`آیا از حذف متغیر «${variable.name}» مطمئن هستید؟`, 'حذف متغیر', {
    confirmText: 'بله، حذف شود',
    cancelText: 'لغو'
  })

  if (!result.isConfirmed) {
    return
  }

  deletingId.value = variable.id

  try {
    const response = await apiClient.delete(`/system-variables/${variable.id}`)

    if (response.data.success) {
      showToast(response.data.message || 'متغیر با موفقیت حذف شد', 'success')
      fetchVariables()
    }
  } catch (err) {
    console.error('Delete system variable error:', err)
    showToast(err.response?.data?.message || 'خطا در حذف متغیر', 'error')
  } finally {
    deletingId.value = null
  }
}

onMounted(() => {
  fetchVariables()
})
</script>

<style scoped>
/* Additional scoped styles if needed */
</style>


