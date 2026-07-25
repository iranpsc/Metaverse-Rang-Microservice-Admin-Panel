<template>
  <div class="p-6 space-y-6">
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-[var(--theme-text-primary)] mb-2">پکیج های رنگی</h1>
      <p class="text-[var(--theme-text-secondary)]">مدیریت پکیج‌های رنگی</p>
    </div>

    <!-- Action Bar -->
    <div class="flex flex-col sm:flex-row gap-3 justify-between sm:items-center mb-6">
      <Button
        variant="primary"
        @click="openCreateModal"
      >
        ایجاد پکیج رنگ
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

    <!-- Table -->
    <div v-else>
      <Table
        :columns="tableColumns"
        :data="options"
        :pagination="pagination"
        empty-state-message="هیچ بسته ای تعریف نشده است"
      >
      <template #cell-image_url="{ value }">
        <a
          v-if="value"
          :href="value"
          target="_blank"
          class="inline-flex items-center justify-center text-[var(--theme-primary)] hover:opacity-90"
          title="مشاهده تصویر"
          aria-label="مشاهده تصویر"
        >
          <TableActionIcon name="image" icon-class="w-4 h-4 shrink-0" />
        </a>
        <span v-else class="text-gray-500">-</span>
      </template>

      <template #cell-actions="{ row }">
        <div class="flex gap-2 flex-wrap">
          <Button
            variant="primary"
            size="sm"
            rounded="full"
            class="!p-2 !gap-0 min-w-[2.25rem]"
            title="بروز رسانی"
            aria-label="بروز رسانی"
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
            @click="handleDelete(row)"
          >
            <template #icon-left>
              <TableActionIcon name="delete" />
            </template>
          </Button>
          <Button
            v-if="row.price_change_logs && row.price_change_logs.length > 0"
            variant="info"
            size="sm"
            @click="openHistoryModal(row)"
          >
            تاریخچه تغییرات
          </Button>
        </div>
      </template>

      <template #cell-created_time="{ row }">
        <span class="text-[var(--theme-text-secondary)]">
          {{ formatHistoryTime(row.created_at) }}
        </span>
      </template>
      </Table>

      <!-- Pagination -->
      <Pagination
        v-if="pagination && pagination.total > 0"
        :pagination="pagination"
        :disabled="loading"
        @page-change="goToPage"
      />
    </div>

    <!-- Option Form Modal (Create/Edit) -->
    <Modal
      :model-value="showFormModal"
      @update:model-value="closeFormModal"
      :title="isEditMode ? 'بروزرسانی بسته' : 'تعریف بسته'"
      size="lg"
    >
      <div class="space-y-4" dir="rtl">
        <Select
          v-model="formData.asset"
          label="ارز"
          :options="variableOptions"
          placeholder="ارز را انتخاب کنید"
          :error="errors.asset"
          :loading="loadingVariables"
        />

        <Input
          v-model="formData.amount"
          type="number"
          label="تعداد"
          placeholder="تعداد را وارد کنید"
          :error="errors.amount"
        />

        <Input
          v-model="formData.code"
          label="کد بسته"
          placeholder="کد بسته را وارد کنید"
          :error="errors.code"
        />

        <FileInput
          v-model="formData.image"
          label="تصویر"
          accept="image/*"
          placeholder="تصویر بسته را انتخاب کنید"
          helper-text="تصویر جدید برای بروزرسانی اختیاری است"
          :error="fieldError(errors.image)"
          @change="handleOptionImageChange"
        />

        <div v-if="imagePreview || (isEditMode && existingImageUrl)" class="mt-1">
          <img
            :src="imagePreview || existingImageUrl"
            alt="پیش‌نمایش تصویر"
            class="h-24 w-24 object-cover rounded-md border border-[var(--theme-border)]"
          />
        </div>

        <div v-if="isEditMode" class="form-group">
          <label class="block text-sm font-medium text-[var(--theme-text-primary)] mb-2">یادداشت *</label>
          <textarea
            v-model="formData.note"
            rows="4"
            placeholder="توضیحات تغییر را وارد کنید"
            class="w-full px-3 py-2 text-[var(--theme-text-primary)] bg-[var(--theme-bg-elevated)] border border-[var(--theme-border)] rounded-md focus:outline-none focus:ring-2 focus:ring-[var(--theme-primary)] focus:border-transparent"
            :class="{ 'border-red-500': errors.note }"
          ></textarea>
          <p v-if="errors.note" class="mt-1 text-sm text-red-500">{{ errors.note }}</p>
        </div>
      </div>

      <template #footer>
        <div class="flex gap-3 justify-end" dir="rtl">
          <Button
            variant="primary"
            :loading="saving"
            @click="handleFormFooterAction"
          >
            {{ submitButtonLabel }}
          </Button>
          <Button
            variant="danger"
            @click="closeFormModal"
            :disabled="saving"
          >
            بستن
          </Button>
        </div>
      </template>
    </Modal>

    <!-- Change History Modal -->
    <Modal
      :model-value="showHistoryModal"
      @update:model-value="closeHistoryModal"
      title="تاریخچه تغییرات"
      size="xl"
    >
      <div v-if="selectedOption?.price_change_logs && selectedOption.price_change_logs.length > 0" class="overflow-x-auto" dir="rtl">
        <Table
          :columns="historyColumns"
          :data="selectedOption.price_change_logs"
          :show-row-number="true"
          empty-state-message="تاریخچه تغییرات یافت نشد"
        />
      </div>
      <div v-else class="py-8 text-center">
        <p class="text-[var(--theme-text-secondary)]">تاریخچه تغییرات یافت نشد</p>
      </div>

      <template #footer>
        <div class="flex justify-end" dir="rtl">
          <Button
            variant="danger"
            @click="closeHistoryModal"
          >
            بستن
          </Button>
        </div>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import apiClient from '../../utils/api'
import { Button, LoadingState, ErrorState, Table, Pagination, Modal, Input, Select, FileInput } from '../../components/ui'
import TableActionIcon from '../../components/icons/TableActionIcon.vue'
import { useToast } from '../../composables/useToast'
import { confirm } from '../../utils/notifications'

const { showToast } = useToast()


const loading = ref(true)
const error = ref(null)
const options = ref([])
const pagination = ref(null)
const currentPage = ref(1)
const showFormModal = ref(false)
const showHistoryModal = ref(false)
const isEditMode = ref(false)
const selectedOption = ref(null)
const saving = ref(false)
const loadingVariables = ref(false)
const variableOptions = ref([])
const imagePreview = ref(null)

const formData = ref({
  asset: '',
  amount: '',
  code: '',
  image: null,
  note: ''
})

const errors = ref({})

const submitButtonLabel = computed(() => isEditMode.value ? 'بروزرسانی' : 'ثبت')

const existingImageUrl = computed(() => {
  return isEditMode.value && selectedOption.value?.image_url ? selectedOption.value.image_url : null
})

const fieldError = (val) => {
  if (val == null || val === '') return ''
  return Array.isArray(val) ? val[0] : String(val)
}

const tableColumns = [
  {
    key: 'code',
    label: 'کد بسته'
  },
  {
    key: 'asset_title',
    label: 'ارز'
  },
  {
    key: 'package_price',
    label: 'قیمت بسته'
  },
  {
    key: 'amount',
    label: 'تعداد'
  },
  {
    key: 'updated_at',
    label: 'تاریخ و ساعت بروزرسانی',
    textSecondary: true,
    formatter: (value) => {
      if (!value) return '-'
      const date = new Date(value)
      return date.toLocaleDateString('fa-IR')
    }
  },
  {
    key: 'image_url',
    label: 'تصویر'
  },
  {
    key: 'note',
    label: 'علت تغییر',
    textSecondary: true,
    defaultValue: '-'
  },
  {
    key: 'actions',
    label: 'ملاحضات'
  }
]

const historyColumns = [
  {
    key: 'changer_name',
    label: 'تغییر دهنده'
  },
  {
    key: 'previous_value',
    label: 'وضعیت گذشته'
  },
  {
    key: 'current_value',
    label: 'وضعیت حال'
  },
  {
    key: 'note',
    label: 'توضیحات',
    defaultValue: '-'
  },
  {
    key: 'created_at',
    label: 'تاریخ تغییر',
    formatter: (value) => {
      if (!value) return '-'
      const date = new Date(value)
      return date.toLocaleDateString('fa-IR')
    }
  },
  {
    key: 'created_time',
    label: 'ساعت تغییر',
  }
]

const formatHistoryTime = (value) => {
  if (!value) return '-'
  const date = new Date(value)
  return date.toLocaleTimeString('fa-IR', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
}

const fetchOptions = async () => {
  try {
    loading.value = true
    error.value = null

    const params = {
      page: currentPage.value,
      per_page: 10,
    }

    const response = await apiClient.get('/options', { params })

    if (response.data.success) {
      options.value = response.data.data.options
      pagination.value = response.data.data.pagination
    } else {
      error.value = 'خطا در دریافت اطلاعات'
    }
  } catch (err) {
    console.error('Options fetch error:', err)

    if (err.response && (err.response.status === 401 || err.response.status === 403)) {
      options.value = []
      pagination.value = null
      loading.value = false
      return
    }

    error.value = err.response?.data?.message || 'خطا در بارگذاری اطلاعات'
    options.value = []
    pagination.value = null
  } finally {
    loading.value = false
  }
}

const fetchVariables = async () => {
  try {
    loadingVariables.value = true
    const response = await apiClient.get('/options/variables')

    if (response.data.success) {
      variableOptions.value = response.data.data.map(v => ({
        value: v.asset,
        label: v.asset_title
      }))
    }
  } catch (err) {
    console.error('Error fetching variables:', err)
    showToast('خطا در بارگذاری لیست ارزها', 'error')
  } finally {
    loadingVariables.value = false
  }
}

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value?.last_page) {
    currentPage.value = page
    fetchOptions()
  }
}

const handleOptionImageChange = (file) => {
  if (file) {
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreview.value = e.target.result
    }
    reader.readAsDataURL(file)
    errors.value.image = null
  } else {
    imagePreview.value = null
  }
}

const validateForm = () => {
  errors.value = {}

  if (!formData.value.asset) {
    errors.value.asset = 'ارز الزامی است'
  }

  if (!formData.value.amount || formData.value.amount < 1) {
    errors.value.amount = 'تعداد باید بیشتر از صفر باشد'
  }

  if (!formData.value.code) {
    errors.value.code = 'کد بسته الزامی است'
  }

  if (isEditMode.value && !formData.value.note) {
    errors.value.note = 'یادداشت الزامی است'
  }

  return Object.keys(errors.value).length === 0
}

const submitForm = async () => {
  try {
    saving.value = true
    errors.value = {}

    const formDataToSend = new FormData()

    formDataToSend.append('asset', formData.value.asset)
    formDataToSend.append('amount', formData.value.amount)
    formDataToSend.append('code', formData.value.code)

    if (formData.value.image) {
      formDataToSend.append('image', formData.value.image)
    }

    if (isEditMode.value && formData.value.note) {
      formDataToSend.append('note', formData.value.note)
    }

    formDataToSend

    const url = isEditMode.value ? `/options/${selectedOption.value.id}` : '/options'

    let response
    if (isEditMode.value) {
      formDataToSend.append('_method', 'PUT')
      response = await apiClient.post(url, formDataToSend, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    } else {
      response = await apiClient.post(url, formDataToSend, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    }

    if (response.data.success) {
      showToast(response.data.message, 'success')
      closeFormModal()
      await fetchOptions()
    } else {
      showToast(response.data.message || 'خطا در ثبت اطلاعات', 'error')
    }
  } catch (err) {
    console.error('Form submit error:', err)

    if (err.response?.status === 422 && err.response?.data?.errors) {
      errors.value = err.response.data.errors
    } else {
      showToast(err.response?.data?.message || 'خطا در ثبت اطلاعات', 'error')
    }
  } finally {
    saving.value = false
  }
}

const handleFormFooterAction = async () => {
  if (!validateForm()) {
    return
  }

  await submitForm()
}

const openCreateModal = () => {
  isEditMode.value = false
  selectedOption.value = null
  formData.value = {
    asset: '',
    amount: '',
    code: '',
    image: null,
    note: ''
  }
  errors.value = {}
  imagePreview.value = null
  fetchVariables()
  showFormModal.value = true
}

const openEditModal = (option) => {
  isEditMode.value = true
  selectedOption.value = option
  formData.value = {
    asset: option.asset,
    amount: option.amount,
    code: option.code,
    image: null,
    note: ''
  }
  errors.value = {}
  imagePreview.value = null
  fetchVariables()
  showFormModal.value = true
}

const closeFormModal = () => {
  showFormModal.value = false
  isEditMode.value = false
  selectedOption.value = null
  formData.value = {
    asset: '',
    amount: '',
    code: '',
    image: null,
    note: ''
  }
  errors.value = {}
  imagePreview.value = null
}

const openHistoryModal = (option) => {
  selectedOption.value = option
  showHistoryModal.value = true
}

const closeHistoryModal = () => {
  showHistoryModal.value = false
  selectedOption.value = null
}

const handleDelete = async (row) => {
  const result = await confirm(
    `آیا این پکیج (${option.code}) را حذف می کنید؟`,
    'حذف پکیج',
    { confirmText: 'بله، حذف شود', cancelText: 'انصراف' }
  )
  if (!result.isConfirmed) return

  try {
        const response = await apiClient.delete(`/options/${option.id}`)

        if (response.data.success) {
          showToast(response.data.message, 'success')
          await fetchOptions()
        } else {
          showToast(response.data.message || 'خطا در حذف پکیج', 'error')
        }
      } catch (err) {
        console.error('Delete error:', err)

        showToast(err.response?.data?.message || 'خطا در حذف پکیج', 'error')
      }
}

onMounted(() => {
  fetchOptions()
})
</script>

<style scoped>
/* Additional styles if needed */
</style>
