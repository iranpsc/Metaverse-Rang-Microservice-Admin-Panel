<template>
  <Modal
    :model-value="show"
    @update:model-value="$emit('close')"
    :title="modalTitle"
    size="xl"
  >
    <div v-if="loading" class="flex justify-center py-8">
      <Spinner size="lg" />
    </div>

    <div v-else-if="error" class="py-4">
      <Alert variant="danger" :message="error" />
    </div>

    <div v-else-if="bankAccount" class="space-y-4" dir="rtl">
      <!-- Bank Account Details Table -->
      <div class="overflow-x-auto">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-[var(--theme-bg-glass)] border-b-2 border-[var(--theme-border)]">
              <th class="px-4 py-3 text-sm font-semibold text-[var(--theme-text-primary)] border border-[var(--theme-border)] text-right">عنوان</th>
              <th class="px-4 py-3 text-sm font-semibold text-[var(--theme-text-primary)] border border-[var(--theme-border)] text-right">مقدار</th>
              <th v-if="bankAccount.status === 0" class="px-4 py-3 text-sm font-semibold text-[var(--theme-text-primary)] border border-[var(--theme-border)] text-right">بررسی</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[var(--theme-border)]">
            <!-- Bank Name -->
            <tr class="hover:bg-[var(--theme-bg-glass)]">
              <td class="px-4 py-3 text-sm font-medium text-[var(--theme-text-primary)] border border-[var(--theme-border)] text-right">نام بانک</td>
              <td class="px-4 py-3 text-sm text-[var(--theme-text-secondary)] border border-[var(--theme-border)] text-right">{{ bankAccount.bank_name }}</td>
              <td v-if="bankAccount.status === 0" class="px-4 py-3 border border-[var(--theme-border)]">
                <ErrorInputField
                  field-name="bank_name_err"
                  :existing-error="getExistingError('bank_name_err')"
                  @save-error="handleSaveError"
                />
              </td>
            </tr>

            <!-- Shaba Number -->
            <tr class="hover:bg-[var(--theme-bg-glass)]">
              <td class="px-4 py-3 text-sm font-medium text-[var(--theme-text-primary)] border border-[var(--theme-border)] text-right">شماره شبا</td>
              <td class="px-4 py-3 text-sm text-[var(--theme-text-secondary)] border border-[var(--theme-border)] text-right">{{ bankAccount.shaba_num }}</td>
              <td v-if="bankAccount.status === 0" class="px-4 py-3 border border-[var(--theme-border)]">
                <ErrorInputField
                  field-name="shaba_num_err"
                  :existing-error="getExistingError('shaba_num_err')"
                  @save-error="handleSaveError"
                />
              </td>
            </tr>

            <!-- Card Number -->
            <tr class="hover:bg-[var(--theme-bg-glass)]">
              <td class="px-4 py-3 text-sm font-medium text-[var(--theme-text-primary)] border border-[var(--theme-border)] text-right">شماره کارت</td>
              <td class="px-4 py-3 text-sm text-[var(--theme-text-secondary)] border border-[var(--theme-border)] text-right">{{ bankAccount.card_num }}</td>
              <td v-if="bankAccount.status === 0" class="px-4 py-3 border border-[var(--theme-border)]">
                <ErrorInputField
                  field-name="card_num_err"
                  :existing-error="getExistingError('card_num_err')"
                  @save-error="handleSaveError"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>

    <template #footer>
      <div class="flex gap-3 justify-end" dir="rtl">
        <Button
          v-if="bankAccount && bankAccount.status === 0"
          variant="primary"
          :loading="saving"
          @click="handleSave"
          class="w-1/2"
        >
          ثبت
        </Button>
        <Button
          variant="danger"
          @click="$emit('close')"
          :class="bankAccount && bankAccount.status === 0 ? 'w-1/2' : 'w-full'"
        >
          بستن
        </Button>
      </div>
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
</template>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue'
import apiClient from '../../utils/api'
import { Modal, Button, Spinner, Alert } from '../ui'
import ErrorInputField from './ErrorInputField.vue'
import VerificationForm from '../VerificationForm.vue'
import { notifySuccess, notifyError } from '../../utils/notifications'

const props = defineProps({
  bankAccountId: {
    type: Number,
    required: true
  },
  show: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'updated'])

const loading = ref(false)
const saving = ref(false)
const error = ref(null)
const bankAccount = ref(null)
const bankAccountErrors = ref([])
const verificationFormRef = ref(null)
const showVerificationDialog = ref(false)

const isProduction = computed(() => {
  // Check Laravel's APP_ENV from meta tag, fallback to Vite mode
  const metaEnv = document.querySelector('meta[name="app-env"]')?.getAttribute('content')
  return metaEnv === 'production' || import.meta.env.MODE === 'production'
})

const modalTitle = computed(() => {
  if (!bankAccount.value || !bankAccount.value.bankable) {
    return 'جزئیات حساب بانکی'
  }

  const bankable = bankAccount.value.bankable
  const userName = bankable.fname && bankable.lname
    ? `${bankable.fname} ${bankable.lname}`
    : bankable.name || 'کاربر'

  return `جزئیات حساب بانکی - ${userName}`
})

const getExistingError = (fieldName) => {
  if (!bankAccount.value?.errors) return null
  const error = bankAccountErrors.value.find(e => e.name === fieldName)
  return error ? error.message : null
}

const handleSaveError = (fieldName, errorMessage) => {
  // Remove existing error for this field if any
  bankAccountErrors.value = bankAccountErrors.value.filter(e => e.name !== fieldName)

  // Add new error only if message provided (error fields are optional)
  if (errorMessage && errorMessage.trim()) {
    bankAccountErrors.value.push({
      name: fieldName,
      message: errorMessage
    })
  }
  // If errorMessage is empty, the field is cleared and no error is added
}

const sendVerificationCode = async () => {
  try {
    // Send verification code when submit button is clicked
    const response = await apiClient.post('/send-verification-sms')

    if (response.data.success) {
      // Show dialog after code is sent
      showVerificationDialog.value = true
      return true
    } else {
      await notifyError('خطا در ارسال کد تایید')
      return false
    }
  } catch (err) {
    console.error('Verification SMS send error:', err)
    await notifyError(err.response?.data?.message || 'خطا در ارسال کد تایید')
    return false
  } finally {
    // Reset saving state after verification code is sent (success or failure)
    // The actual submission will set it to true again when submitting
    saving.value = false
  }
}

const submitBankAccountUpdate = async (verificationData = {}) => {
  try {
    saving.value = true
    error.value = null

    // Always send bank_account_errors as array (empty if no errors)
    const response = await apiClient.put(`/bank-accounts/${props.bankAccountId}`, {
      bank_account_errors: bankAccountErrors.value,
      ...verificationData
    })

    if (response.data.success) {
      await notifySuccess('اطلاعات با موفقیت ثبت شد')
      showVerificationDialog.value = false
      // Reset verification form errors on success
      if (verificationFormRef.value && verificationFormRef.value.setErrors) {
        verificationFormRef.value.setErrors({})
      }
      emit('updated')
    } else {
      error.value = 'خطا در ثبت اطلاعات'
    }
  } catch (err) {
    console.error('Bank Account update error:', err)
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
    // It will be set to true again when submitBankAccountUpdate is called
  } else {
    // In non-production: submit directly without verification
    await submitBankAccountUpdate()
  }
}

const handleAutoVerifyAndSubmit = async (verificationData) => {
  // Auto-submit when verification form emits verified event (all 6 digits entered)
  // Prevent multiple submissions
  if (saving.value) {
    return
  }

  if (!verificationFormRef.value) {
    await notifyError('خطا در تایید')
    return
  }

  // Get verification data and submit
  const data = verificationData || verificationFormRef.value.getData()
  await submitBankAccountUpdate(data)
}

const handleCloseVerificationDialog = () => {
  // Clear errors when closing the dialog
  if (verificationFormRef.value && verificationFormRef.value.setErrors) {
    verificationFormRef.value.setErrors({})
  }
  showVerificationDialog.value = false
}

const fetchBankAccountDetails = async () => {
  try {
    loading.value = true
    error.value = null

    const response = await apiClient.get(`/bank-accounts/${props.bankAccountId}`)

    if (response.data.success) {
      bankAccount.value = response.data.data
      // Initialize errors array from existing errors
      if (bankAccount.value.errors && Array.isArray(bankAccount.value.errors)) {
        bankAccountErrors.value = [...bankAccount.value.errors]
      }
    } else {
      error.value = 'خطا در دریافت اطلاعات'
    }
  } catch (err) {
    console.error('Bank Account details fetch error:', err)
    error.value = err.response?.data?.message || 'خطا در بارگذاری اطلاعات'
  } finally {
    loading.value = false
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

watch(() => props.show, (newVal) => {
  if (newVal && props.bankAccountId) {
    bankAccountErrors.value = []
    fetchBankAccountDetails()
  } else {
    // Reset verification dialog when modal closes
    showVerificationDialog.value = false
  }
})

watch(() => props.bankAccountId, (newVal) => {
  if (newVal && props.show) {
    bankAccountErrors.value = []
    fetchBankAccountDetails()
  }
})

onMounted(() => {
  if (props.show && props.bankAccountId) {
    fetchBankAccountDetails()
  }
})
</script>

<style scoped>
/* Additional styles if needed */
</style>

