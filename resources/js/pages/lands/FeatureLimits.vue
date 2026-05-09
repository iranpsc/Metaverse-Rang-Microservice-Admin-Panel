<template>
  <div class="p-6 space-y-6">
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-[var(--theme-text-primary)] mb-2">محدودیت املاک</h1>
      <p class="text-[var(--theme-text-secondary)]">تعریف و مدیریت محدودیت‌های املاک</p>
    </div>

    <!-- Create Limit Button -->
    <div class="mb-6">
      <Button variant="primary" @click="showCreateModal = true">
        ایجاد محدودیت
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
      v-else-if="featureLimits && featureLimits.length > 0"
      :columns="tableColumns"
      :data="featureLimits"
      :pagination="pagination"
      show-row-number
    >
      <template #cell-limits="{ row }">
        <ul class="list-disc list-inside text-right space-y-1">
          <li v-if="row.verified_kyc_limit">محدودیت احراز هویت تایید شده</li>
          <li v-if="row.verified_bank_account_limit">محدودیت حساب بانکی تایید شده</li>
          <li v-if="row.not_sellable">غیرقابل فروش</li>
          <li v-if="row.under_18_limit">محدودیت زیر ۱۸ سال</li>
          <li v-if="row.more_than_18_limit">محدودیت بالای ۱۸ سال</li>
          <li v-if="row.dynasty_owner_limit">محدودیت دارنده سلسله</li>
        </ul>
      </template>

      <template #cell-status="{ row }">
        <Badge :variant="row.expired ? 'danger' : 'success'">
          {{ row.expired ? 'منقضی شده' : 'فعال' }}
        </Badge>
      </template>

      <template #cell-actions="{ row }">
        <Button
          variant="danger"
          size="sm"
          rounded="full"
          class="!p-2 !gap-0 min-w-[2.25rem]"
          title="حذف"
          aria-label="حذف"
          :loading="deleting && deletingLimitId === row.id"
          :disabled="deleting"
          @click="handleDelete(row)"
        >
          <template #icon-left>
            <TableActionIcon name="delete" />
          </template>
        </Button>
      </template>
    </Table>

    <!-- Empty State -->
    <Alert
      v-else
      variant="warning"
      message="محدودیتی یافت نشد!"
      :dismissible="false"
    />

    <!-- Pagination -->
    <Pagination
      v-if="pagination && pagination.total > 0"
      :pagination="pagination"
      :disabled="loading"
      @page-change="goToPage"
    />

    <!-- Create Limit Modal -->
    <Modal
      v-model="showCreateModal"
      title="تعریف محدودیت"
      size="full"
    >
      <div class="space-y-6">
        <!-- Alert -->
        <Alert variant="danger" :dismissible="false">
          <div class="space-y-2">
            <p><strong>توجه:</strong> تاریخ شروع و پایان نباید با دیگر محدودیت ها تداخل داشته باشد.</p>
            <p><strong>توجه:</strong> پیشوند شناسه های شروع و پایان باید با یکدیگر یکسان باشند.</p>
          </div>
        </Alert>

        <!-- Title -->
        <Input
          v-model="formData.title"
          label="عنوان"
          required
          :error="errors.title"
        />

        <!-- Start and End ID -->
        <div class="grid grid-cols-2 gap-4">
          <Input
            v-model="formData.start_id"
            label="شناسه شروع"
            required
            :error="errors.start_id"
          />
          <Input
            v-model="formData.end_id"
            label="شناسه پایانی"
            required
            :error="errors.end_id"
          />
        </div>

        <!-- Checkboxes Row 1 -->
        <div class="grid grid-cols-3 gap-4">
          <div class="flex items-center space-x-2 space-x-reverse">
            <input
              id="verified_kyc_limit"
              v-model="formData.verified_kyc_limit"
              type="checkbox"
              class="w-4 h-4 rounded border-border focus:ring-primary-500"
            />
            <label for="verified_kyc_limit" class="text-sm text-[var(--theme-text-primary)]">
              محدودیت احراز هویت تایید شده
            </label>
          </div>
          <div class="flex items-center space-x-2 space-x-reverse">
            <input
              id="verified_bank_account_limit"
              v-model="formData.verified_bank_account_limit"
              type="checkbox"
              class="w-4 h-4 rounded border-border focus:ring-primary-500"
            />
            <label for="verified_bank_account_limit" class="text-sm text-[var(--theme-text-primary)]">
              محدودیت حساب بانکی تایید شده
            </label>
          </div>
          <div class="flex items-center space-x-2 space-x-reverse">
            <input
              id="not_sellable"
              v-model="formData.not_sellable"
              type="checkbox"
              class="w-4 h-4 rounded border-border focus:ring-primary-500"
            />
            <label for="not_sellable" class="text-sm text-[var(--theme-text-primary)]">
              غیرقابل فروش
            </label>
          </div>
        </div>

        <!-- Checkboxes Row 2 -->
        <div class="grid grid-cols-3 gap-4">
          <div class="flex items-center space-x-2 space-x-reverse">
            <input
              id="under_18_limit"
              v-model="formData.under_18_limit"
              type="checkbox"
              class="w-4 h-4 rounded border-border focus:ring-primary-500"
            />
            <label for="under_18_limit" class="text-sm text-[var(--theme-text-primary)]">
              محدودیت زیر ۱۸ سال
            </label>
          </div>
          <div class="flex items-center space-x-2 space-x-reverse">
            <input
              id="more_than_18_limit"
              v-model="formData.more_than_18_limit"
              type="checkbox"
              class="w-4 h-4 rounded border-border focus:ring-primary-500"
            />
            <label for="more_than_18_limit" class="text-sm text-[var(--theme-text-primary)]">
              محدودیت بالای ۱۸ سال
            </label>
          </div>
          <div class="flex items-center space-x-2 space-x-reverse">
            <input
              id="dynasty_owner_limit"
              v-model="formData.dynasty_owner_limit"
              type="checkbox"
              class="w-4 h-4 rounded border-border focus:ring-primary-500"
            />
            <label for="dynasty_owner_limit" class="text-sm text-[var(--theme-text-primary)]">
              محدودیت دارنده سلسله
            </label>
          </div>
        </div>

        <!-- Individual Buy Limit and Price Limit -->
        <div class="grid grid-cols-2 gap-4">
          <div class="border border-[var(--theme-border)] rounded-lg p-4 space-y-4">
            <div class="flex items-center space-x-2 space-x-reverse">
              <input
                id="individual_buy_limit"
                v-model="formData.individual_buy_limit"
                type="checkbox"
                class="w-4 h-4 rounded border-border focus:ring-primary-500"
              />
              <label for="individual_buy_limit" class="text-sm text-[var(--theme-text-primary)]">
                محدودیت تعداد خرید
              </label>
            </div>
            <Input
              v-model="formData.individual_buy_count"
              label="تعداد خرید"
              type="number"
              :disabled="!formData.individual_buy_limit"
              :error="errors.individual_buy_count"
            />
          </div>
          <div class="border border-[var(--theme-border)] rounded-lg p-4 space-y-4">
            <div class="flex items-center space-x-2 space-x-reverse">
              <input
                id="price_limit"
                v-model="formData.price_limit"
                type="checkbox"
                class="w-4 h-4 rounded border-border focus:ring-primary-500"
              />
              <label for="price_limit" class="text-sm text-[var(--theme-text-primary)]">
                محدودیت قیمت ثابت
              </label>
            </div>
            <Input
              v-model="formData.price"
              label="قیمت ثابت"
              type="number"
              :disabled="!formData.price_limit"
              :error="errors.price"
            />
          </div>
        </div>

        <!-- Dates -->
        <div class="grid grid-cols-2 gap-4">
          <PersianDatePicker
            :key="`start-date-${modalOpenKey}`"
            v-model="formData.start_date"
            label="تاریخ شروع"
            required
            :error="errors.start_date"
          />
          <PersianDatePicker
            :key="`end-date-${modalOpenKey}`"
            v-model="formData.end_date"
            label="تاریخ پایان"
            required
            :error="errors.end_date"
          />
        </div>
      </div>

      <template #footer>
        <Button variant="primary" :loading="saving" @click="handleSave">
          ثبت
        </Button>
        <Button variant="danger" class="w-full" @click="showCreateModal = false">
          انصراف
        </Button>
      </template>
    </Modal>

    <!-- Verification Dialog -->
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

    <!-- Delete Confirmation Modal -->
    <Modal
      :model-value="showDeleteConfirmModal"
      @update:model-value="handleCloseDeleteConfirmModal"
      @close="handleCloseDeleteConfirmModal"
      title="تایید حذف محدودیت"
      size="md"
    >
      <div dir="rtl" class="space-y-4">
        <Alert variant="warning" :dismissible="false">
          <div class="space-y-2">
            <p><strong>توجه:</strong> آیا مطمئن هستید که می‌خواهید این محدودیت را حذف کنید؟</p>
            <p>این عمل غیرقابل بازگشت است و تمام محدودیت‌های اعمال شده بر روی املاک حذف خواهد شد.</p>
          </div>
        </Alert>
        <div v-if="deletingLimitId" class="text-sm text-[var(--theme-text-secondary)]">
          <p><strong>عنوان محدودیت:</strong> {{ getLimitTitle(deletingLimitId) }}</p>
        </div>
      </div>

      <template #footer>
        <Button
          variant="danger"
          :loading="deleting"
          @click="handleConfirmDelete"
        >
          بله، حذف شود
        </Button>
        <Button
          variant="ghost"
          :disabled="deleting"
          @click="handleCloseDeleteConfirmModal"
        >
          لغو
        </Button>
      </template>
    </Modal>

    <!-- Delete Verification Dialog -->
    <Modal
      :model-value="showDeleteVerificationDialog"
      @update:model-value="handleCloseDeleteVerificationDialog"
      @close="handleCloseDeleteVerificationDialog"
      title="تایید نهایی حذف"
      size="md"
    >
      <div dir="rtl">
        <VerificationForm
          ref="deleteVerificationFormRef"
          :auto-start="true"
          @verified="handleAutoVerifyAndDelete"
        />
      </div>
    </Modal>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch, nextTick } from 'vue'
import { Table, Pagination, Button, LoadingState, ErrorState, Alert, Modal, Input, Badge } from '../../components/ui'
import PersianDatePicker from '../../components/ui/PersianDatePicker.vue'
import VerificationForm from '../../components/VerificationForm.vue'
import { useToast } from '../../composables/useToast'
import { useFeatureLimits } from '../../composables/useFeatureLimits'
import { gregorianToShamsiSync } from '../../utils/dateConverter'
import TableActionIcon from '../../components/icons/TableActionIcon.vue'

const { showToast } = useToast()
const {
  sendVerificationSms,
  createFeatureLimit,
  deleteFeatureLimit,
  fetchFeatureLimits: fetchFeatureLimitsApi
} = useFeatureLimits()

const loading = ref(true)
const error = ref(null)
const featureLimits = ref([])
const pagination = ref(null)
const currentPage = ref(1)
const showCreateModal = ref(false)
const saving = ref(false)
const verificationFormRef = ref(null)
const showVerificationDialog = ref(false)
const deletingLimitId = ref(null)
const deleteVerificationFormRef = ref(null)
const showDeleteVerificationDialog = ref(false)
const showDeleteConfirmModal = ref(false)
const deleting = ref(false)
const modalOpenKey = ref(0)

// Check if production environment
const isProduction = computed(() => {
  // Check Laravel's APP_ENV from meta tag, fallback to Vite mode
  const metaEnv = document.querySelector('meta[name="app-env"]')?.getAttribute('content')
  return metaEnv === 'production' || import.meta.env.MODE === 'production'
})

const errors = ref({})

// Get today's date in Shamsi format for default values
const getTodayShamsi = () => {
  const today = new Date()
  return gregorianToShamsiSync(today.toISOString().split('T')[0]) || ''
}

const getFutureDateShamsi = (days) => {
  const futureDate = new Date(Date.now() + days * 24 * 60 * 60 * 1000)
  return gregorianToShamsiSync(futureDate.toISOString().split('T')[0]) || ''
}

const formData = ref({
  verified_kyc_limit: false,
  verified_bank_account_limit: false,
  not_sellable: false,
  under_18_limit: false,
  more_than_18_limit: false,
  dynasty_owner_limit: false,
  title: '',
  start_id: '',
  end_id: '',
  start_date: getTodayShamsi(),
  end_date: getFutureDateShamsi(30),
  price_limit: false,
  price: 0,
  individual_buy_limit: false,
  individual_buy_count: 0
})

// Table columns configuration
const tableColumns = [
  {
    key: 'title',
    label: 'عنوان'
  },
  {
    key: 'start_date',
    label: 'تاریخ شروع'
  },
  {
    key: 'end_date',
    label: 'تاریخ پایان'
  },
  {
    key: 'start_id',
    label: 'شناسه شروع'
  },
  {
    key: 'end_id',
    label: 'شناسه پایانی'
  },
  {
    key: 'limits',
    label: 'محدودیت ها'
  },
  {
    key: 'status',
    label: 'وضعیت'
  },
  {
    key: 'actions',
    label: 'اقدامات'
  }
]

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value?.last_page) {
    currentPage.value = page
    fetchFeatureLimits()
  }
}

const sendVerificationCode = async () => {
  try {
    // Send verification code when submit button is clicked
    const response = await sendVerificationSms()

    if (response.data.success) {
      // Show dialog after code is sent
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
    // Reset saving state after verification code is sent (success or failure)
    // The actual submission will set it to true again when submitting
    saving.value = false
  }
}

const submitFeatureLimitCreate = async (verificationData = {}) => {
  try {
    saving.value = true
    error.value = null
    errors.value = {}

    const response = await createFeatureLimit({
      ...formData.value,
      ...verificationData
    })

    if (response.data.success) {
      showToast('محدودیت املاک با موفقیت ایجاد شد', 'success')
      showVerificationDialog.value = false
      // Reset verification form errors on success
      if (verificationFormRef.value && verificationFormRef.value.setErrors) {
        verificationFormRef.value.setErrors({})
      }
      showCreateModal.value = false
      resetForm()
      await fetchFeatureLimits()
    } else {
      error.value = 'خطا در ثبت اطلاعات'
    }
  } catch (err) {
    console.error('Feature limit create error:', err)
    error.value = err.response?.data?.message || 'خطا در ثبت اطلاعات'

    // Extract validation errors and display them on the form fields
    if (err.response?.data?.errors && verificationFormRef.value && verificationFormRef.value.setErrors) {
      const validationErrors = {}

      // Map Laravel validation errors to form fields
      if (err.response.data.errors.phone_verification) {
        validationErrors.phone_verification = Array.isArray(err.response.data.errors.phone_verification)
          ? err.response.data.errors.phone_verification[0]
          : err.response.data.errors.phone_verification
      }

      // Set errors on the verification form
      verificationFormRef.value.setErrors(validationErrors)
    }

    // Handle validation errors for form fields
    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors
    }
  } finally {
    saving.value = false
  }
}

const handleSave = async () => {
  // Set loading state immediately when button is clicked
  saving.value = true

  // In production: send verification code first, then show dialog
  if (isProduction.value) {
    // Send verification code and show dialog
    const codeSent = await sendVerificationCode()
    if (!codeSent) {
      // Error already handled in sendVerificationCode, state reset in finally block
      return
    }
    // Dialog will be shown, user enters code and password, then clicks submit
    // Note: saving.value is reset to false in sendVerificationCode finally block
    // It will be set to true again when submitFeatureLimitCreate is called
  } else {
    // In non-production: submit directly without verification
    await submitFeatureLimitCreate()
  }
}

const handleAutoVerifyAndSubmit = async (verificationData) => {
  // Auto-submit when verification form emits verified event (all 6 digits entered)
  // Prevent multiple submissions
  if (saving.value) {
    return
  }

  if (!verificationFormRef.value) {
    showToast('خطا در تایید', 'error')
    return
  }

  // Get verification data and submit
  const data = verificationData || verificationFormRef.value.getData()
  await submitFeatureLimitCreate(data)
}

const handleCloseVerificationDialog = () => {
  // Clear errors when closing the dialog
  if (verificationFormRef.value && verificationFormRef.value.setErrors) {
    verificationFormRef.value.setErrors({})
  }
  showVerificationDialog.value = false
}

const handleDelete = async (limit) => {
  // Store the limit ID and show confirmation modal
  deletingLimitId.value = limit.id
  showDeleteConfirmModal.value = true
}

const getLimitTitle = (limitId) => {
  const limit = featureLimits.value.find(l => l.id === limitId)
  return limit ? limit.title : ''
}

const handleConfirmDelete = async () => {
  if (!deletingLimitId.value) {
    return
  }

  // Close confirmation modal
  showDeleteConfirmModal.value = false

  // In production: send verification code first, then show dialog
  if (isProduction.value) {
    deleting.value = true
    const codeSent = await sendDeleteVerificationCode()
    if (!codeSent) {
      deleting.value = false
      deletingLimitId.value = null
      return
    }
  } else {
    // In non-production: delete directly without verification
    await performDelete()
  }
}

const handleCloseDeleteConfirmModal = () => {
  if (!deleting.value) {
    deletingLimitId.value = null
    showDeleteConfirmModal.value = false
  }
}

const sendDeleteVerificationCode = async () => {
  try {
    const response = await sendVerificationSms()

    if (response.data.success) {
      // Show dialog after code is sent
      showDeleteVerificationDialog.value = true
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
    // Reset deleting state after verification code is sent (success or failure)
    // The actual deletion will set it to true again when deleting
    deleting.value = false
  }
}

const performDelete = async (verificationData = {}) => {
  if (!deletingLimitId.value) {
    return
  }

  try {
    deleting.value = true

    const response = await deleteFeatureLimit(deletingLimitId.value, verificationData)

    if (response.data.success) {
      showToast('محدودیت املاک با موفقیت حذف شد', 'success')
      showDeleteVerificationDialog.value = false
      // Reset verification form errors on success
      if (deleteVerificationFormRef.value && deleteVerificationFormRef.value.setErrors) {
        deleteVerificationFormRef.value.setErrors({})
      }
      deletingLimitId.value = null
      await fetchFeatureLimits()
    }
  } catch (err) {
    console.error('Delete feature limit error:', err)
    showToast(err.response?.data?.message || 'خطا در حذف محدودیت', 'error')

    // Extract validation errors and display them on the form fields
    if (err.response?.data?.errors && deleteVerificationFormRef.value && deleteVerificationFormRef.value.setErrors) {
      const validationErrors = {}

      // Map Laravel validation errors to form fields
      if (err.response.data.errors.phone_verification) {
        validationErrors.phone_verification = Array.isArray(err.response.data.errors.phone_verification)
          ? err.response.data.errors.phone_verification[0]
          : err.response.data.errors.phone_verification
      }

      // Set errors on the verification form
      deleteVerificationFormRef.value.setErrors(validationErrors)
    }
  } finally {
    deleting.value = false
  }
}

const handleAutoVerifyAndDelete = async (verificationData) => {
  // Auto-submit when verification form emits verified event (all 6 digits entered)
  // Prevent multiple submissions
  if (deleting.value) {
    return
  }

  if (!deleteVerificationFormRef.value) {
    showToast('خطا در تایید', 'error')
    return
  }

  // Get verification data and delete
  const data = verificationData || deleteVerificationFormRef.value.getData()
  await performDelete(data)
}

const handleCloseDeleteVerificationDialog = () => {
  // Clear errors when closing the dialog
  if (deleteVerificationFormRef.value && deleteVerificationFormRef.value.setErrors) {
    deleteVerificationFormRef.value.setErrors({})
  }
  showDeleteVerificationDialog.value = false
  // Only clear deletingLimitId if not currently deleting
  if (!deleting.value) {
    deletingLimitId.value = null
    if (deleteVerificationFormRef.value) {
      deleteVerificationFormRef.value.reset()
    }
  }
}

const fetchFeatureLimits = async () => {
  try {
    loading.value = true
    error.value = null

    const params = {
      page: currentPage.value,
      per_page: 10
    }

    const response = await fetchFeatureLimitsApi(params)

    if (response.data.success) {
      featureLimits.value = response.data.data.feature_limits.map(limit => ({
        ...limit,
        // Use Shamsi dates from API response (start_date_shamsi, end_date_shamsi)
        // Backend already converts to Shamsi, so we use those values directly
        start_date: limit.start_date_shamsi || (limit.start_date ? gregorianToShamsiSync(limit.start_date) : '-'),
        end_date: limit.end_date_shamsi || (limit.end_date ? gregorianToShamsiSync(limit.end_date) : '-')
      }))
      pagination.value = response.data.data.pagination
    } else {
      error.value = 'خطا در دریافت اطلاعات محدودیت‌ها'
    }
  } catch (err) {
    console.error('Feature limits fetch error:', err)

    if (err.response && (err.response.status === 401 || err.response.status === 403)) {
      featureLimits.value = []
      pagination.value = null
      loading.value = false
      return
    }

    error.value = err.response?.data?.message || 'خطا در بارگذاری اطلاعات'
    featureLimits.value = []
    pagination.value = null
  } finally {
    loading.value = false
  }
}

const resetForm = () => {
  formData.value = {
    verified_kyc_limit: false,
    verified_bank_account_limit: false,
    not_sellable: false,
    under_18_limit: false,
    more_than_18_limit: false,
    dynasty_owner_limit: false,
    title: '',
    start_id: '',
    end_id: '',
    start_date: getTodayShamsi(),
    end_date: getFutureDateShamsi(30),
    price_limit: false,
    price: 0,
    individual_buy_limit: false,
    individual_buy_count: 0
  }
  errors.value = {}
  showVerificationDialog.value = false
  if (verificationFormRef.value) {
    verificationFormRef.value.reset()
  }
}

watch(() => showVerificationDialog, async (newVal) => {
  if (newVal) {
    // Wait for component to mount and ensure timer starts
    await nextTick()

    // Give it a delay to ensure component is fully ready
    setTimeout(() => {
      if (verificationFormRef.value && verificationFormRef.value.startTimer) {
        verificationFormRef.value.startTimer()
      }
    }, 100)

    // Focus the first input after modal is fully opened
    // Modal transition is 300ms, so wait a bit longer to ensure it's complete
    setTimeout(() => {
      if (verificationFormRef.value && verificationFormRef.value.focusFirstInput) {
        verificationFormRef.value.focusFirstInput()
      }
    }, 400)

    // Retry focus after modal animation completes (additional safety)
    setTimeout(() => {
      if (verificationFormRef.value && verificationFormRef.value.focusFirstInput) {
        verificationFormRef.value.focusFirstInput()
      }
    }, 600)
  } else {
    // Reset form when dialog closes
    if (verificationFormRef.value && verificationFormRef.value.reset) {
      verificationFormRef.value.reset()
    }
  }
})

watch(() => showDeleteVerificationDialog, async (newVal) => {
  if (newVal) {
    // Wait for component to mount and ensure timer starts
    await nextTick()

    // Give it a delay to ensure component is fully ready
    setTimeout(() => {
      if (deleteVerificationFormRef.value && deleteVerificationFormRef.value.startTimer) {
        deleteVerificationFormRef.value.startTimer()
      }
    }, 100)

    // Focus the first input after modal is fully opened
    // Modal transition is 300ms, so wait a bit longer to ensure it's complete
    setTimeout(() => {
      if (deleteVerificationFormRef.value && deleteVerificationFormRef.value.focusFirstInput) {
        deleteVerificationFormRef.value.focusFirstInput()
      }
    }, 400)

    // Retry focus after modal animation completes (additional safety)
    setTimeout(() => {
      if (deleteVerificationFormRef.value && deleteVerificationFormRef.value.focusFirstInput) {
        deleteVerificationFormRef.value.focusFirstInput()
      }
    }, 600)
  } else {
    // Reset form when dialog closes
    if (deleteVerificationFormRef.value && deleteVerificationFormRef.value.reset) {
      deleteVerificationFormRef.value.reset()
    }
  }
})

watch(() => showCreateModal, async (newVal) => {
  // Close verification dialog when create modal closes
  if (!newVal) {
    showVerificationDialog.value = false
    if (verificationFormRef.value && verificationFormRef.value.reset) {
      verificationFormRef.value.reset()
    }
  } else {
    // Increment key to force re-mount of date pickers
    modalOpenKey.value++
    // Modal opened - reset form and ensure date pickers are initialized
    await nextTick()
    // The key prop on PersianDatePicker will force re-initialization
    // Wait for modal transition to complete before ensuring date pickers are ready
    setTimeout(() => {
      // Double-check that date pickers are initialized after modal animation
      const datePickers = document.querySelectorAll('[id^="persian-date-"]')
      datePickers.forEach((picker) => {
        if (typeof window.kamaDatepicker !== 'undefined' && picker.id && !picker.dataset.initialized) {
          try {
            window.kamaDatepicker(picker.id, {
              placeholder: 'روز / ماه / سال',
              twodigit: true,
              closeAfterSelect: true,
              markToday: true,
              markHolidays: true,
              highlightSelectedDay: true,
              sync: true,
              buttonsColor: 'gray',
              forceFarsiDigits: false,
              gotoToday: true
            })
            picker.dataset.initialized = 'true'
          } catch (e) {
            // Ignore errors
          }
        }
      })
    }, 400)
  }
})

onMounted(() => {
  fetchFeatureLimits()
})
</script>

<style scoped>
/* Additional styles if needed */
</style>

<style>
/* Make the create limit modal wider - target modal content container */
:deep(.relative.z-10.w-full.max-w-full) {
  max-width: 80rem !important; /* 7xl = 80rem = 1280px */
}

/* Mobile-friendly SweetAlert styles */
:deep(.mobile-friendly-popup) {
  font-size: 14px;
}

:deep(.mobile-friendly-button) {
  font-size: 14px;
  padding: 12px 24px;
  border-radius: 8px;
  min-width: 100px;
}

@media (max-width: 640px) {
  :deep(.swal2-popup) {
    width: 90% !important;
    margin: 0 auto;
  }

  :deep(.swal2-title) {
    font-size: 18px !important;
  }

  :deep(.swal2-content) {
    font-size: 14px !important;
  }
}
</style>

