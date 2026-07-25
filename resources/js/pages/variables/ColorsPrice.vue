<template>
  <div class="p-6 space-y-6">
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-[var(--theme-text-primary)] mb-2">قیمت رنگ ها</h1>
      <p class="text-[var(--theme-text-secondary)]">مدیریت قیمت ارزها و رنگ‌ها</p>
    </div>

    <!-- Action Bar -->
    <div class="flex justify-between items-center mb-6">
      <Button
        variant="primary"
        @click="openCreateModal"
      >
        ایجاد ارز
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
    <Table
      v-else
      :columns="tableColumns"
      :data="variables"
      empty-state-message="هیچ ارزی ثبت نشده است"
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
        <span v-else class="text-red-500">بدون تصویر</span>
      </template>

      <template #cell-actions="{ row }">
        <div class="flex gap-2 flex-wrap">
          <Button
            variant="primary"
            size="sm"
            rounded="full"
            class="!p-2 !gap-0 min-w-[2.25rem]"
            title="بروزرسانی"
            aria-label="بروزرسانی"
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
    </Table>

    <!-- Variable Form Modal (Create/Edit) -->
    <Modal
      :model-value="showFormModal"
      @update:model-value="closeFormModal"
      :title="isEditMode ? 'ویرایش ارز' : 'تعریف ارز'"
      size="lg"
    >
      <div class="space-y-4" dir="rtl">
        <!-- Asset Selection (Only for Create) -->
        <div v-if="!isEditMode">
          <Select
            v-model="formData.asset"
            label="نام ارز"
            :options="assetOptions"
            placeholder="نام ارز را به انگلیسی انتخاب کنید"
            :error="errors.asset"
          />
        </div>

        <!-- Price Input -->
        <Input
          v-model="formData.price"
          type="number"
          label="قیمت واحد"
          placeholder="قیمت واحد را به تومان وارد کنید"
          :error="errors.price"
        />

        <FileInput
          v-model="formData.image"
          label="تصویر ارز"
          accept="image/*"
          :required="!isEditMode"
          placeholder="تصویر ارز را انتخاب کنید"
          helper-text="در حالت ویرایش، انتخاب تصویر جدید اختیاری است"
          :error="fieldError(errors.image)"
          @change="handleCurrencyImageChange"
        />

        <div v-if="imagePreview || (isEditMode && existingImageUrl)" class="mt-1">
          <img
            :src="imagePreview || existingImageUrl"
            alt="پیش‌نمایش تصویر ارز"
            class="h-24 w-24 object-cover rounded-md border border-[var(--theme-border)]"
          />
        </div>

        <!-- Note (Only for Edit) -->
        <div v-if="isEditMode">
          <Input
            v-model="formData.note"
            label="علت بروزرسانی"
            placeholder="علت بروزرسانی را وارد کنید"
            :error="errors.note"
          />
        </div>
      </div>

      <template #footer>
        <div class="flex gap-3 justify-end" dir="rtl">
          <Button
            variant="primary"
            rounded="full"
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
      :title="`تاریخچه تغییرات - ${selectedVariable?.asset_title || ''}`"
      size="xl"
    >
      <div v-if="selectedVariable?.price_change_logs && selectedVariable.price_change_logs.length > 0" class="overflow-x-auto" dir="rtl">
        <Table
          :columns="historyColumns"
          :data="selectedVariable.price_change_logs"
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
import { Button, LoadingState, ErrorState, Table, Modal, Input, Select, FileInput } from '../../components/ui'
import TableActionIcon from '../../components/icons/TableActionIcon.vue'
import { useToast } from '../../composables/useToast'
import { confirm } from '../../utils/notifications'

const { showToast } = useToast()


const loading = ref(true)
const error = ref(null)
const variables = ref([])
const showFormModal = ref(false)
const showHistoryModal = ref(false)
const isEditMode = ref(false)
const selectedVariable = ref(null)
const saving = ref(false)
const imagePreview = ref(null)

const formData = ref({
  asset: '',
  price: '',
  image: null,
  note: ''
})

const errors = ref({})

const submitButtonLabel = computed(() => isEditMode.value ? 'بروزرسانی' : 'ثبت')

const existingImageUrl = computed(() => {
  return isEditMode.value && selectedVariable.value?.image_url ? selectedVariable.value.image_url : null
})

const fieldError = (val) => {
  if (val == null || val === '') return ''
  return Array.isArray(val) ? val[0] : String(val)
}

const assetOptions = [
  { value: 'red', label: 'قرمز' },
  { value: 'blue', label: 'آبی' },
  { value: 'yellow', label: 'زرد' },
  { value: 'irr', label: 'ریال' },
  { value: 'psc', label: 'psc' },
  { value: 'satisfaction', label: 'رضایت' },
  { value: 'effect', label: 'حد تاثیر' }
]

const tableColumns = [
  {
    key: 'asset_title',
    label: 'نام ارز'
  },
  {
    key: 'price',
    label: 'قیمت واحد'
  },
  {
    key: 'image_url',
    label: 'تصویر'
  },
  {
    key: 'updated_at',
    label: 'آخرین بروز رسانی',
    textSecondary: true,
    formatter: (value) => {
      if (!value) return '-'
      const date = new Date(value)
      return date.toLocaleDateString('fa-IR')
    }
  },
  {
    key: 'note',
    label: 'دلیل بروز رسانی',
    textSecondary: true,
    defaultValue: '-'
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
    key: 'created_at',
    label: 'ساعت تغییر',
    formatter: (value) => {
      if (!value) return '-'
      const date = new Date(value)
      return date.toLocaleTimeString('fa-IR', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
    }
  }
]

const fetchVariables = async () => {
  try {
    loading.value = true
    error.value = null

    const response = await apiClient.get('/variables')

    if (response.data.success) {
      variables.value = response.data.data
    } else {
      error.value = 'خطا در دریافت اطلاعات'
    }
  } catch (err) {
    console.error('Variables fetch error:', err)

    if (err.response && (err.response.status === 401 || err.response.status === 403)) {
      variables.value = []
      loading.value = false
      return
    }

    error.value = err.response?.data?.message || 'خطا در بارگذاری اطلاعات'
    variables.value = []
  } finally {
    loading.value = false
  }
}

const handleCurrencyImageChange = (file) => {
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

  if (!isEditMode.value && !formData.value.asset) {
    errors.value.asset = 'نام ارز الزامی است'
  }

  if (!formData.value.price || formData.value.price < 1) {
    errors.value.price = 'قیمت واحد باید بیشتر از صفر باشد'
  }

  if (!isEditMode.value && !formData.value.image) {
    errors.value.image = 'تصویر ارز الزامی است'
  }

  return Object.keys(errors.value).length === 0
}

const submitForm = async () => {
  try {
    saving.value = true
    errors.value = {}

    const formDataToSend = new FormData()

    if (!isEditMode.value) {
      formDataToSend.append('asset', formData.value.asset)
    }

    formDataToSend.append('price', formData.value.price)

    if (formData.value.image) {
      formDataToSend.append('image', formData.value.image)
    }

    if (isEditMode.value && formData.value.note) {
      formDataToSend.append('note', formData.value.note)
    }

    formDataToSend

    const url = isEditMode.value ? `/variables/${selectedVariable.value.id}` : '/variables'

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
      await fetchVariables()
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
  selectedVariable.value = null
  formData.value = {
    asset: '',
    price: '',
    image: null,
    note: ''
  }
  errors.value = {}
  imagePreview.value = null
  showFormModal.value = true
}

const openEditModal = (variable) => {
  isEditMode.value = true
  selectedVariable.value = variable
  formData.value = {
    asset: variable.asset,
    price: variable.price,
    image: null,
    note: ''
  }
  errors.value = {}
  imagePreview.value = null
  showFormModal.value = true
}

const closeFormModal = () => {
  showFormModal.value = false
  isEditMode.value = false
  selectedVariable.value = null
  formData.value = {
    asset: '',
    price: '',
    image: null,
    note: ''
  }
  errors.value = {}
  imagePreview.value = null
}

const openHistoryModal = (variable) => {
  selectedVariable.value = variable
  showHistoryModal.value = true
}

const closeHistoryModal = () => {
  showHistoryModal.value = false
  selectedVariable.value = null
}

const handleDelete = async (row) => {
  const result = await confirm(
    `آیا این ارز (${row.asset_title}) را حذف می کنید؟`,
    'حذف ارز',
    { confirmText: 'بله، حذف شود', cancelText: 'انصراف' }
  )
  if (!result.isConfirmed) return

  try {
        const response = await apiClient.delete(`/variables/${row.id}`)

        if (response.data.success) {
          showToast(response.data.message, 'success')
          await fetchVariables()
        }
      } catch (err) {
        console.error('Delete error:', err)

        showToast(err.response?.data?.message || 'خطا در حذف ارز', 'error')
      }
}

onMounted(() => {
  fetchVariables()
})
</script>

<style scoped>
/* Additional styles if needed */
</style>
