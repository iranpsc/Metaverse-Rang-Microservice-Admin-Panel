<template>
  <div class="p-6 space-y-6">
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-[var(--theme-text-primary)] mb-2">لیست نقشه ها</h1>
      <p class="text-[var(--theme-text-secondary)]">مدیریت و مشاهده نقشه‌های بارگذاری شده</p>
    </div>

    <!-- Upload Button -->
    <div class="mb-6">
      <Button variant="primary" @click="showUploadModal = true">
        بارگذاری نقشه
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
      :data="maps"
      :pagination="pagination"
      empty-state-message="هیچ نقشه ای برای نمایش وجود ندارد."
    >
      <template #cell-status="{ row }">
        <Badge :variant="row.status === 1 ? 'success' : 'danger'">
          {{ row.status === 1 ? 'منتشر شده' : 'منتشر نشده' }}
        </Badge>
      </template>
      <template #cell-actions="{ row }">
        <div class="flex gap-2" dir="rtl">
          <Button
            v-if="row.status !== 1"
            variant="primary"
            size="sm"
            rounded="full"
            @click="openInsertIntoDatabaseModal(row)"
          >
            انتشار
          </Button>
          <Button
            variant="secondary"
            size="sm"
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
        </div>
      </template>
    </Table>

    <!-- Pagination -->
    <Pagination
      v-if="pagination && pagination.total > 0"
      :pagination="pagination"
      :disabled="loading"
      @page-change="goToPage"
    />

    <!-- Upload Map Modal -->
    <Modal
      :model-value="showUploadModal"
      @update:model-value="showUploadModal = false"
      title="بارگذاری فایل نقشه"
      size="xl"
    >
      <div class="space-y-4" dir="rtl">
        <Input
          v-model="uploadFormData.name"
          label="نام آبادی"
          :error="uploadErrors.name"
          required
        />

        <FileInput
          v-model="mapFile"
          label="فایل نقشه"
          accept=".json"
          required
          placeholder="فایل JSON نقشه را انتخاب کنید"
          helper-text="فرمت مجاز: JSON"
          :error="uploadErrorMessage(uploadErrors.map_file)"
          @change="clearUploadFieldError('map_file')"
        />

        <FileInput
          v-model="pointFile"
          label="فایل نقطه مرکزی"
          accept=".json"
          required
          placeholder="فایل نقطه مرکزی را انتخاب کنید"
          helper-text="فرمت مجاز: JSON"
          :error="uploadErrorMessage(uploadErrors.point_file)"
          @change="clearUploadFieldError('point_file')"
        />

        <FileInput
          v-model="borderFile"
          label="فایل مرز"
          accept=".json"
          required
          placeholder="فایل مرز را انتخاب کنید"
          helper-text="فرمت مجاز: JSON"
          :error="uploadErrorMessage(uploadErrors.border_file)"
          @change="clearUploadFieldError('border_file')"
        />

        <Input
          v-model="uploadFormData.color"
          type="color"
          label="رنگ محدوده"
          :error="uploadErrors.color"
          required
        />
      </div>

      <template #footer>
        <div class="flex gap-3 justify-end" dir="rtl">
          <Button
            variant="primary"
            :loading="uploadSaving"
            @click="handleUploadSubmit"
            class="w-1/2"
          >
            بارگذاری
          </Button>
          <Button
            variant="danger"
            @click="showUploadModal = false"
            class="w-1/2"
          >
            بستن
          </Button>
        </div>
      </template>
    </Modal>

    <!-- Update Map Modal -->
    <Modal
      v-if="selectedMap"
      :model-value="showUpdateModal"
      @update:model-value="closeUpdateModal"
      title="بروزرسانی نقشه"
      size="xl"
    >
      <div class="space-y-4" dir="rtl">
        <Input
          v-model="updateFormData.name"
          label="نام آبادی"
          :error="updateErrors.name"
          required
        />

        <FileInput
          v-model="updatePointFile"
          label="بارگذاری فایل نقطه مرکزی"
          accept=".json"
          required
          placeholder="فایل نقطه مرکزی را انتخاب کنید"
          helper-text="فرمت مجاز: JSON"
          :error="uploadErrorMessage(updateErrors.point_file)"
          @change="clearUpdateFieldError('point_file')"
        />

        <FileInput
          v-model="updateBorderFile"
          label="بارگذاری فایل مرز"
          accept=".json"
          required
          placeholder="فایل مرز را انتخاب کنید"
          helper-text="فرمت مجاز: JSON"
          :error="uploadErrorMessage(updateErrors.border_file)"
          @change="clearUpdateFieldError('border_file')"
        />

        <Input
          v-model="updateFormData.color"
          type="color"
          label="رنگ محدوده"
          :error="updateErrors.color"
          required
        />
      </div>

      <template #footer>
        <div class="flex gap-3 justify-end" dir="rtl">
          <Button
            variant="primary"
            :loading="updateSaving"
            @click="handleUpdateSubmit"
            class="w-1/2"
          >
            بارگذاری
          </Button>
          <Button
            variant="danger"
            @click="closeUpdateModal"
            class="w-1/2"
          >
            بستن
          </Button>
        </div>
      </template>
    </Modal>

    <!-- Insert Into Database Modal -->
    <Modal
      v-if="selectedMap"
      :model-value="showInsertIntoDatabaseModal"
      @update:model-value="closeInsertIntoDatabaseModal"
      title="وارد کردن اطلاعات نقشه به دیتابیس"
      size="md"
    >
      <div class="space-y-4" dir="rtl">
        <Table
          :columns="insertModalTableColumns"
          :data="selectedMap ? [selectedMap] : []"
          :show-row-number="false"
          empty-state-message=""
        />
      </div>

      <template #footer>
        <div class="flex gap-3 justify-end" dir="rtl">
          <Button
            variant="primary"
            :loading="insertSaving"
            @click="handleInsertSubmit"
            class="w-1/2"
          >
            ثبت نهایی
          </Button>
          <Button
            variant="danger"
            @click="closeInsertIntoDatabaseModal"
            class="w-1/2"
          >
            بستن
          </Button>
        </div>
      </template>
    </Modal>

    <!-- Verification Dialog (Shared) -->
    <Modal
      :model-value="showVerificationDialog"
      @update:model-value="handleCloseVerificationDialog"
      @close="handleCloseVerificationDialog"
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
    </Modal>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted } from 'vue'
import { Table, Pagination, Button, Badge, LoadingState, ErrorState, Modal, Input, FileInput } from '../../components/ui'
import VerificationForm from '../../components/VerificationForm.vue'
import { useToast } from '../../composables/useToast'
import { useMaps } from '../../composables/useMaps'
import { confirm } from '../../utils/notifications'
import TableActionIcon from '../../components/icons/TableActionIcon.vue'

const { showToast } = useToast()
const {
  fetchMaps: fetchMapsApi,
  createMap,
  updateMap,
  insertMapIntoDatabase,
  sendVerificationSms,
  deleteMap
} = useMaps()

const loading = ref(true)
const error = ref(null)
const maps = ref([])
const pagination = ref(null)
const currentPage = ref(1)

const showUploadModal = ref(false)
const showUpdateModal = ref(false)
const showInsertIntoDatabaseModal = ref(false)
const showVerificationDialog = ref(false)
const selectedMap = ref(null)

// Upload form state
const uploadSaving = ref(false)
const uploadErrors = ref({})
const uploadFormData = ref({
  name: '',
  color: '#000000'
})
const mapFile = ref(null)
const pointFile = ref(null)
const borderFile = ref(null)

// Update form state
const updateSaving = ref(false)
const updateErrors = ref({})
const updateFormData = ref({
  name: '',
  color: '#000000'
})
const updatePointFile = ref(null)
const updateBorderFile = ref(null)

// Insert form state
const insertSaving = ref(false)

// Verification
const verificationFormRef = ref(null)
const pendingAction = ref(null) // 'upload' | 'update' | 'insert'

const isProduction = computed(() => {
  const metaEnv = document.querySelector('meta[name="app-env"]')?.getAttribute('content')
  return metaEnv === 'production' || import.meta.env.MODE === 'production'
})

// Table columns configuration
const tableColumns = [
  {
    key: 'name',
    label: 'نام آبادی'
  },
  {
    key: 'publish_date',
    label: 'تاریخ انتشار'
  },
  {
    key: 'publisher_name',
    label: 'نام منتشر کننده'
  },
  {
    key: 'polygon_count',
    label: 'تعداد پالیگان'
  },
  {
    key: 'total_area',
    label: 'مساحت کل'
  },
  {
    key: 'first_id',
    label: 'آیدی اولین زمین'
  },
  {
    key: 'last_id',
    label: 'آیدی آخرین زمین'
  },
  {
    key: 'status',
    label: 'وضعیت'
  },
  {
    key: 'actions',
    label: 'مدیریت'
  }
]

// Insert modal table columns
const insertModalTableColumns = [
  {
    key: 'name',
    label: 'نام'
  },
  {
    key: 'polygon_count',
    label: 'تعداد پالیگان'
  },
  {
    key: 'karbari',
    label: 'کاربری'
  }
]

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value?.last_page) {
    currentPage.value = page
    fetchMaps()
  }
}

const fetchMaps = async () => {
  try {
    loading.value = true
    error.value = null

    const params = {
      page: currentPage.value,
      per_page: 10,
    }

    const response = await fetchMapsApi(params)

    if (response.data.success) {
      maps.value = response.data.data.maps
      pagination.value = response.data.data.pagination
    } else {
      error.value = 'خطا در دریافت اطلاعات نقشه‌ها'
    }
  } catch (err) {
    console.error('Maps fetch error:', err)

    if (err.response && (err.response.status === 401 || err.response.status === 403)) {
      maps.value = []
      pagination.value = null
      loading.value = false
      return
    }

    error.value = err.response?.data?.message || 'خطا در بارگذاری اطلاعات'
    maps.value = []
    pagination.value = null
  } finally {
    loading.value = false
  }
}

const uploadErrorMessage = (val) => {
  if (val == null || val === '') return ''
  return Array.isArray(val) ? val[0] : String(val)
}

const clearUploadFieldError = (key) => {
  if (uploadErrors.value[key]) {
    delete uploadErrors.value[key]
  }
}

const clearUpdateFieldError = (key) => {
  if (updateErrors.value[key]) {
    delete updateErrors.value[key]
  }
}

const validateUploadForm = () => {
  uploadErrors.value = {}

  if (!uploadFormData.value.name || uploadFormData.value.name.trim().length < 2) {
    uploadErrors.value.name = 'نام آبادی باید حداقل 2 کاراکتر باشد'
  }

  if (!mapFile.value) {
    uploadErrors.value.map_file = 'فایل نقشه الزامی است'
  }

  if (!pointFile.value) {
    uploadErrors.value.point_file = 'فایل نقطه مرکزی الزامی است'
  }

  if (!borderFile.value) {
    uploadErrors.value.border_file = 'فایل مرز الزامی است'
  }

  if (!uploadFormData.value.color) {
    uploadErrors.value.color = 'رنگ محدوده الزامی است'
  }

  return Object.keys(uploadErrors.value).length === 0
}

const submitUploadForm = async (verificationData = {}) => {
  try {
    uploadSaving.value = true
    uploadErrors.value = {}

    const formDataToSend = new FormData()
    formDataToSend.append('name', uploadFormData.value.name)
    formDataToSend.append('map_file', mapFile.value)
    formDataToSend.append('point_file', pointFile.value)
    formDataToSend.append('border_file', borderFile.value)
    formDataToSend.append('color', uploadFormData.value.color)

    if (verificationData.phone_verification) {
      formDataToSend.append('phone_verification', verificationData.phone_verification)
    }

    const response = await createMap(formDataToSend)

    if (response.data.success) {
      showToast(response.data.message, 'success')
      showVerificationDialog.value = false
      if (verificationFormRef.value && verificationFormRef.value.setErrors) {
        verificationFormRef.value.setErrors({})
      }

      // Reset form
      uploadFormData.value = { name: '', color: '#000000' }
      mapFile.value = null
      pointFile.value = null
      borderFile.value = null

      showUploadModal.value = false
      fetchMaps()
    } else {
      showToast('خطا در بارگذاری فایل', 'error')
    }
  } catch (err) {
    console.error('Upload error:', err)

    if (err.response?.status === 422 && err.response?.data?.errors) {
      uploadErrors.value = err.response.data.errors

      if (verificationFormRef.value && verificationFormRef.value.setErrors) {
        const validationErrors = {}
        if (err.response.data.errors.phone_verification) {
          validationErrors.phone_verification = Array.isArray(err.response.data.errors.phone_verification)
            ? err.response.data.errors.phone_verification[0]
            : err.response.data.errors.phone_verification
        }
        verificationFormRef.value.setErrors(validationErrors)
      }
    } else {
      showToast(err.response?.data?.message || 'خطا در بارگذاری فایل', 'error')
    }
  } finally {
    uploadSaving.value = false
  }
}

const handleUploadSubmit = async () => {
  if (!validateUploadForm()) {
    return
  }

  uploadSaving.value = true

  if (isProduction.value) {
    pendingAction.value = 'upload'
    const codeSent = await sendVerificationCode()
    if (!codeSent) {
      uploadSaving.value = false
      return
    }
  } else {
    await submitUploadForm()
  }
}

const validateUpdateForm = () => {
  updateErrors.value = {}

  if (!updateFormData.value.name || updateFormData.value.name.trim().length < 2) {
    updateErrors.value.name = 'نام آبادی باید حداقل 2 کاراکتر باشد'
  }

  if (!updatePointFile.value) {
    updateErrors.value.point_file = 'فایل نقطه مرکزی الزامی است'
  }

  if (!updateBorderFile.value) {
    updateErrors.value.border_file = 'فایل مرز الزامی است'
  }

  if (!updateFormData.value.color) {
    updateErrors.value.color = 'رنگ محدوده الزامی است'
  }

  return Object.keys(updateErrors.value).length === 0
}

const submitUpdateForm = async (verificationData = {}) => {
  try {
    updateSaving.value = true
    updateErrors.value = {}

    const formDataToSend = new FormData()
    formDataToSend.append('name', updateFormData.value.name)
    formDataToSend.append('point_file', updatePointFile.value)
    formDataToSend.append('border_file', updateBorderFile.value)
    formDataToSend.append('color', updateFormData.value.color)
    formDataToSend.append('_method', 'PUT')

    if (verificationData.phone_verification) {
      formDataToSend.append('phone_verification', verificationData.phone_verification)
    }

    const response = await updateMap(selectedMap.value.id, formDataToSend)

    if (response.data.success) {
      showToast(response.data.message, 'success')
      showVerificationDialog.value = false
      if (verificationFormRef.value && verificationFormRef.value.setErrors) {
        verificationFormRef.value.setErrors({})
      }

      closeUpdateModal()
      fetchMaps()
    } else {
      showToast('خطا در ویرایش اطلاعات', 'error')
    }
  } catch (err) {
    console.error('Update error:', err)

    if (err.response?.status === 422 && err.response?.data?.errors) {
      updateErrors.value = err.response.data.errors

      if (verificationFormRef.value && verificationFormRef.value.setErrors) {
        const validationErrors = {}
        if (err.response.data.errors.phone_verification) {
          validationErrors.phone_verification = Array.isArray(err.response.data.errors.phone_verification)
            ? err.response.data.errors.phone_verification[0]
            : err.response.data.errors.phone_verification
        }
        verificationFormRef.value.setErrors(validationErrors)
      }
    } else {
      showToast(err.response?.data?.message || 'خطا در ویرایش اطلاعات', 'error')
    }
  } finally {
    updateSaving.value = false
  }
}

const handleUpdateSubmit = async () => {
  if (!validateUpdateForm()) {
    return
  }

  updateSaving.value = true

  if (isProduction.value) {
    pendingAction.value = 'update'
    const codeSent = await sendVerificationCode()
    if (!codeSent) {
      updateSaving.value = false
      return
    }
  } else {
    await submitUpdateForm()
  }
}

// Insert handlers
const submitInsertForm = async (verificationData = {}) => {
  try {
    insertSaving.value = true

    const formDataToSend = new FormData()

    if (verificationData.phone_verification) {
      formDataToSend.append('phone_verification', verificationData.phone_verification)
    }

    const response = await insertMapIntoDatabase(selectedMap.value.id, formDataToSend)

    if (response.data.success) {
      showToast(response.data.message, 'success')
      showVerificationDialog.value = false
      if (verificationFormRef.value && verificationFormRef.value.setErrors) {
        verificationFormRef.value.setErrors({})
      }

      closeInsertIntoDatabaseModal()
      fetchMaps()
    } else {
      showToast('خطا در وارد کردن اطلاعات', 'error')
    }
  } catch (err) {
    console.error('Insert error:', err)

    if (err.response?.status === 422 && err.response?.data?.errors) {
      if (verificationFormRef.value && verificationFormRef.value.setErrors) {
        const validationErrors = {}
        if (err.response.data.errors.phone_verification) {
          validationErrors.phone_verification = Array.isArray(err.response.data.errors.phone_verification)
            ? err.response.data.errors.phone_verification[0]
            : err.response.data.errors.phone_verification
        }
        verificationFormRef.value.setErrors(validationErrors)
      }
    } else {
      showToast(err.response?.data?.message || 'خطا در وارد کردن اطلاعات', 'error')
    }
  } finally {
    insertSaving.value = false
  }
}

const handleInsertSubmit = async () => {
  insertSaving.value = true

  if (isProduction.value) {
    pendingAction.value = 'insert'
    const codeSent = await sendVerificationCode()
    if (!codeSent) {
      insertSaving.value = false
      return
    }
  } else {
    await submitInsertForm()
  }
}

// Verification handlers
const sendVerificationCode = async () => {
  try {
    const response = await sendVerificationSms()

    if (response.data.success) {
      showVerificationDialog.value = true
      return true
    } else {
      showToast('خطا در ارسال کد تایید', 'error')
      return false
    }
  } catch (err) {
    console.error('Verification SMS send error:', err)
    showToast(err.response?.data?.message || 'خطا در ارسال کد تایید', 'error')
    return false
  } finally {
    // Reset saving state based on action
    if (pendingAction.value === 'upload') uploadSaving.value = false
    else if (pendingAction.value === 'update') updateSaving.value = false
    else if (pendingAction.value === 'insert') insertSaving.value = false
  }
}

const handleAutoVerifyAndSubmit = async (verificationData) => {
  if (!verificationFormRef.value) {
    showToast('خطا در تایید', 'error')
    return
  }

  const data = verificationData || verificationFormRef.value.getData()

  if (pendingAction.value === 'upload') {
    await submitUploadForm(data)
  } else if (pendingAction.value === 'update') {
    await submitUpdateForm(data)
  } else if (pendingAction.value === 'insert') {
    await submitInsertForm(data)
  }

  pendingAction.value = null
}

const handleCloseVerificationDialog = () => {
  if (verificationFormRef.value && verificationFormRef.value.setErrors) {
    verificationFormRef.value.setErrors({})
  }
  showVerificationDialog.value = false
  pendingAction.value = null
}

// Modal handlers
const openUpdateModal = (map) => {
  selectedMap.value = map
  updateFormData.value = {
    name: map.name || '',
    color: map.polygon_color || '#000000'
  }
  updatePointFile.value = null
  updateBorderFile.value = null
  updateErrors.value = {}
  showUpdateModal.value = true
}

const closeUpdateModal = () => {
  showUpdateModal.value = false
  selectedMap.value = null
  updateFormData.value = { name: '', color: '#000000' }
  updatePointFile.value = null
  updateBorderFile.value = null
  updateErrors.value = {}
}

const openInsertIntoDatabaseModal = (map) => {
  selectedMap.value = map
  showInsertIntoDatabaseModal.value = true
}

const closeInsertIntoDatabaseModal = () => {
  showInsertIntoDatabaseModal.value = false
  selectedMap.value = null
}

const handleDelete = async (map) => {
  try {
    const result = await confirm(
      `آیا از حذف نقشه «${map.name}» مطمئن هستید؟`,
      'تایید حذف نقشه',
      {
        confirmText: 'بله، حذف شود',
        cancelText: 'انصراف'
      }
    )

    if (!result.isConfirmed) {
      return
    }

    const response = await deleteMap(map.id)

    if (response.data.success) {
      showToast('نقشه با موفقیت حذف شد', 'success')
      fetchMaps()
    } else {
      showToast('خطا در حذف نقشه', 'error')
    }
  } catch (err) {
    // If error is not from API (user cancelled), just return
    if (err === 'cancel' || err === 'dismiss') {
      return
    }

    console.error('Delete map error:', err)
    showToast(err.response?.data?.message || 'خطا در حذف نقشه', 'error')
  }
}

watch(showUploadModal, (newVal) => {
  if (!newVal) {
    uploadFormData.value = { name: '', color: '#000000' }
    mapFile.value = null
    pointFile.value = null
    borderFile.value = null
    uploadErrors.value = {}
    showVerificationDialog.value = false
  }
})

watch(showVerificationDialog, async (newVal) => {
  if (newVal) {
    await nextTick()

    setTimeout(() => {
      if (verificationFormRef.value && verificationFormRef.value.startTimer) {
        verificationFormRef.value.startTimer()
      }
    }, 100)

    setTimeout(() => {
      if (verificationFormRef.value && verificationFormRef.value.focusFirstInput) {
        verificationFormRef.value.focusFirstInput()
      }
    }, 400)

    setTimeout(() => {
      if (verificationFormRef.value && verificationFormRef.value.focusFirstInput) {
        verificationFormRef.value.focusFirstInput()
      }
    }, 600)
  } else {
    if (verificationFormRef.value && verificationFormRef.value.reset) {
      verificationFormRef.value.reset()
    }
  }
})

onMounted(() => {
  fetchMaps()
})
</script>

<style scoped>
/* Additional styles if needed */
</style>
