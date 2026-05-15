<template>
  <Modal
    :model-value="show"
    @update:model-value="$emit('close')"
    title="جزئیات اطلاعات کاربر"
    size="xl"
  >
    <div v-if="loading" class="flex justify-center py-8">
      <Spinner size="lg" />
    </div>

    <div v-else-if="error" class="py-4">
      <Alert variant="danger" :message="error" />
    </div>

    <div v-else-if="kyc" class="space-y-4" dir="rtl">
      <!-- KYC Details Table -->
      <div class="overflow-x-auto">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-[var(--theme-bg-glass)] border-b-2 border-[var(--theme-border)]">
              <th class="px-4 py-3 text-sm font-semibold text-[var(--theme-text-primary)] border border-[var(--theme-border)] text-right">عنوان</th>
              <th class="px-4 py-3 text-sm font-semibold text-[var(--theme-text-primary)] border border-[var(--theme-border)] text-right">مقدار</th>
              <th v-if="kyc.status === 0" class="px-4 py-3 text-sm font-semibold text-[var(--theme-text-primary)] border border-[var(--theme-border)] text-right">بررسی</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[var(--theme-border)]">
            <!-- Name -->
            <tr class="hover:bg-[var(--theme-bg-glass)]">
              <td class="px-4 py-3 text-sm font-medium text-[var(--theme-text-primary)] border border-[var(--theme-border)] text-right">نام</td>
              <td class="px-4 py-3 text-sm text-[var(--theme-text-secondary)] border border-[var(--theme-border)] text-right">{{ kyc.fname }}</td>
              <td v-if="kyc.status === 0" class="px-4 py-3 border border-[var(--theme-border)]">
                <ErrorInputField
                  field-name="fname_err"
                  :existing-error="getExistingError('fname_err')"
                  @save-error="handleSaveError"
                />
              </td>
            </tr>

            <!-- Last Name -->
            <tr class="hover:bg-[var(--theme-bg-glass)]">
              <td class="px-4 py-3 text-sm font-medium text-[var(--theme-text-primary)] border border-[var(--theme-border)] text-right">نام خانوادگی</td>
              <td class="px-4 py-3 text-sm text-[var(--theme-text-secondary)] border border-[var(--theme-border)] text-right">{{ kyc.lname }}</td>
              <td v-if="kyc.status === 0" class="px-4 py-3 border border-[var(--theme-border)]">
                <ErrorInputField
                  field-name="lname_err"
                  :existing-error="getExistingError('lname_err')"
                  @save-error="handleSaveError"
                />
              </td>
            </tr>

            <!-- National Code -->
            <tr class="hover:bg-[var(--theme-bg-glass)]">
              <td class="px-4 py-3 text-sm font-medium text-[var(--theme-text-primary)] border border-[var(--theme-border)] text-right">کد ملی</td>
              <td class="px-4 py-3 text-sm text-[var(--theme-text-secondary)] border border-[var(--theme-border)] text-right">{{ kyc.melli_code }}</td>
              <td v-if="kyc.status === 0" class="px-4 py-3 border border-[var(--theme-border)]">
                <ErrorInputField
                  field-name="melli_code_err"
                  :existing-error="getExistingError('melli_code_err')"
                  @save-error="handleSaveError"
                />
              </td>
            </tr>

            <!-- Birthdate -->
            <tr class="hover:bg-[var(--theme-bg-glass)]">
              <td class="px-4 py-3 text-sm font-medium text-[var(--theme-text-primary)] border border-[var(--theme-border)] text-right">تاریخ تولد</td>
              <td class="px-4 py-3 text-sm text-[var(--theme-text-secondary)] border border-[var(--theme-border)] text-right">{{ kyc.birthdate }}</td>
              <td v-if="kyc.status === 0" class="px-4 py-3 border border-[var(--theme-border)]">
                <ErrorInputField
                  field-name="birthdate_err"
                  :existing-error="getExistingError('birthdate_err')"
                  @save-error="handleSaveError"
                />
              </td>
            </tr>

            <!-- Province -->
            <tr class="hover:bg-[var(--theme-bg-glass)]">
              <td class="px-4 py-3 text-sm font-medium text-[var(--theme-text-primary)] border border-[var(--theme-border)] text-right">استان</td>
              <td class="px-4 py-3 text-sm text-[var(--theme-text-secondary)] border border-[var(--theme-border)] text-right">{{ kyc.province }}</td>
              <td v-if="kyc.status === 0" class="px-4 py-3 border border-[var(--theme-border)]">
                <ErrorInputField
                  field-name="province_err"
                  :existing-error="getExistingError('province_err')"
                  @save-error="handleSaveError"
                />
              </td>
            </tr>

            <!-- National Card Image -->
            <tr class="hover:bg-[var(--theme-bg-glass)]">
              <td class="px-4 py-3 text-sm font-medium text-[var(--theme-text-primary)] border border-[var(--theme-border)] text-right">تصویر کارت ملی</td>
              <td class="px-4 py-3 text-sm text-[var(--theme-text-secondary)] border border-[var(--theme-border)] text-right">
                <Button
                  v-if="kyc.melli_card"
                  variant="ghost"
                  size="sm"
                  rounded="full"
                  class="!p-2 !gap-0 min-w-[2.25rem]"
                  title="مشاهده"
                  aria-label="مشاهده تصویر کارت ملی"
                  @click="showMelliCardModalRef = true"
                >
                  <template #icon-left>
                    <TableActionIcon name="view" />
                  </template>
                </Button>
                <span v-else class="text-[var(--theme-text-muted)]">-</span>
              </td>
              <td v-if="kyc.status === 0" class="px-4 py-3 border border-[var(--theme-border)]">
                <ErrorInputField
                  field-name="melli_card_err"
                  :existing-error="getExistingError('melli_card_err')"
                  @save-error="handleSaveError"
                />
              </td>
            </tr>

            <!-- Video -->
            <tr class="hover:bg-[var(--theme-bg-glass)]">
              <td class="px-4 py-3 text-sm font-medium text-[var(--theme-text-primary)] border border-[var(--theme-border)] text-right">فیلم احراز مستند</td>
              <td class="px-4 py-3 text-sm text-[var(--theme-text-secondary)] border border-[var(--theme-border)] text-right">
                <Button
                  variant="ghost"
                  size="sm"
                  rounded="full"
                  class="!p-2 !gap-0 min-w-[2.25rem]"
                  title="مشاهده"
                  aria-label="مشاهده فیلم احراز مستند"
                  @click="showVideoModalRef = true"
                >
                  <template #icon-left>
                    <TableActionIcon name="view" />
                  </template>
                </Button>
              </td>
              <td v-if="kyc.status === 0" class="px-4 py-3 border border-[var(--theme-border)]">
                <ErrorInputField
                  field-name="video_err"
                  :existing-error="getExistingError('video_err')"
                  @save-error="handleSaveError"
                />
              </td>
            </tr>

            <!-- Gender -->
            <tr class="hover:bg-[var(--theme-bg-glass)]">
              <td class="px-4 py-3 text-sm font-medium text-[var(--theme-text-primary)] border border-[var(--theme-border)] text-right">جنسیت</td>
              <td class="px-4 py-3 text-sm text-[var(--theme-text-secondary)] border border-[var(--theme-border)] text-right">{{ getGenderLabel(kyc.gender) }}</td>
              <td v-if="kyc.status === 0" class="px-4 py-3 border border-[var(--theme-border)]">
                <ErrorInputField
                  field-name="gender_err"
                  :existing-error="getExistingError('gender_err')"
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
          v-if="kyc && kyc.status === 0 && isProduction && !isVerified"
          variant="primary"
          :loading="sendingVerification"
          @click="handleSendVerificationCode"
          class="w-1/2"
        >
          ارسال کد تایید
        </Button>
        <Button
          v-if="kyc && kyc.status === 0 && (!isProduction || isVerified)"
          variant="primary"
          :loading="saving"
          @click="handleSubmit"
          class="w-1/2"
        >
          ثبت
        </Button>
        <Button
          variant="danger"
          @click="$emit('close')"
          :class="kyc && kyc.status === 0 ? 'w-1/2' : 'w-full'"
        >
          بستن
        </Button>
      </div>
    </template>
  </Modal>

  <!-- Melli Card Image Modal -->
  <Modal
    v-if="showMelliCardModalRef"
    :model-value="showMelliCardModalRef"
    @update:model-value="showMelliCardModalRef = false"
    title="تصویر کارت ملی"
    size="lg"
  >
    <div v-if="kyc && kyc.melli_card" class="space-y-4" dir="rtl">
      <div class="flex justify-center max-h-[70vh] overflow-y-auto overflow-x-auto p-2">
        <img
          :src="kyc.melli_card"
          alt="کارت ملی"
          class="max-w-full h-auto rounded-lg border border-[var(--theme-border)]"
        />
      </div>
      <div v-if="kyc && kyc.user" class="text-center">
        <h4 class="text-lg font-semibold text-[var(--theme-text-primary)]">{{ kyc.user.code }}</h4>
        <p class="text-sm text-[var(--theme-text-secondary)] mt-2">
          {{ kyc.fname }} {{ kyc.lname }}
        </p>
      </div>
    </div>
  </Modal>

  <!-- Video Modal -->
  <Modal
    v-if="showVideoModalRef"
    :model-value="showVideoModalRef"
    @update:model-value="showVideoModalRef = false"
    title="فیلم احراز مستند"
    size="lg"
  >
    <div v-if="kyc" class="space-y-4" dir="rtl">
      <video
        v-if="kyc && kyc.video"
        :src="kyc.video"
        controls
        class="w-full rounded-lg"
      >
        مرورگر شما از پخش ویدیو پشتیبانی نمی‌کند.
      </video>
      <div v-if="kyc && kyc.user" class="text-center">
        <h4 class="text-lg font-semibold text-[var(--theme-text-primary)]">{{ kyc.user.code }}</h4>
        <p v-if="kyc.verify_text" class="text-sm text-[var(--theme-text-secondary)] mt-2">{{ kyc.verify_text.text }}</p>
      </div>
    </div>
  </Modal>

  <!-- Verification Dialog -->
  <Modal
    v-model="showVerificationDialog"
    title="تایید نهایی"
    size="md"
    :close-on-backdrop="false"
    :close-on-escape="false"
    @close="handleUserCloseVerificationDialog"
  >
    <div dir="rtl">
      <VerificationForm
        ref="verificationFormRef"
        :auto-start="false"
        @verified="handleVerificationVerified"
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
import TableActionIcon from '../icons/TableActionIcon.vue'
import { notifySuccess, notifyError } from '../../utils/notifications'

const props = defineProps({
  kycId: {
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
const sendingVerification = ref(false)
const error = ref(null)
const kyc = ref(null)
const kycErrors = ref([])
const showMelliCardModalRef = ref(false)
const showVideoModalRef = ref(false)
const verificationFormRef = ref(null)
const showVerificationDialog = ref(false)
const isVerified = ref(false)
const pendingVerificationData = ref({})

const isProduction = computed(() => {
  // Check Laravel's APP_ENV from meta tag, fallback to Vite mode
  const metaEnv = document.querySelector('meta[name="app-env"]')?.getAttribute('content')
  return metaEnv === 'production' || import.meta.env.MODE === 'production'
})

const getGenderLabel = (gender) => {
  if (!gender) return gender
  const genderMap = {
    'male': 'مرد',
    'female': 'زن',
    'Male': 'مرد',
    'Female': 'زن',
    'MALE': 'مرد',
    'FEMALE': 'زن'
  }
  return genderMap[gender] || gender
}

const getExistingError = (fieldName) => {
  if (!kyc.value?.errors) return null
  const error = kycErrors.value.find(e => e.name === fieldName)
  return error ? error.message : null
}

const handleSaveError = (fieldName, errorMessage) => {
  // Remove existing error for this field if any
  kycErrors.value = kycErrors.value.filter(e => e.name !== fieldName)

  // Add new error only if message provided (error fields are optional)
  if (errorMessage && errorMessage.trim()) {
    kycErrors.value.push({
      name: fieldName,
      message: errorMessage
    })
  }
  // If errorMessage is empty, the field is cleared and no error is added
}

const resetVerificationCredentials = async ({ ensureModalOpen = false } = {}) => {
  isVerified.value = false
  pendingVerificationData.value = {}
  verificationFormRef.value?.setErrors?.({})
  verificationFormRef.value?.reset?.()

  if (ensureModalOpen && !showVerificationDialog.value) {
    showVerificationDialog.value = true
    await nextTick()
  }
}

const resetVerificationState = () => {
  isVerified.value = false
  pendingVerificationData.value = {}
  showVerificationDialog.value = false
  verificationFormRef.value?.setErrors?.({})
  verificationFormRef.value?.reset?.()
}

const sendVerificationCode = async () => {
  try {
    sendingVerification.value = true
    const response = await apiClient.post('/send-verification-sms')

    if (response.data.success) {
      showVerificationDialog.value = true
      return true
    }

    await notifyError('خطا در ارسال کد تایید')
    return false
  } catch (err) {
    console.error('Verification SMS send error:', err)
    await notifyError(err.response?.data?.message || 'خطا در ارسال کد تایید')
    return false
  } finally {
    sendingVerification.value = false
  }
}

const submitKycUpdate = async (verificationData = {}) => {
  try {
    saving.value = true
    error.value = null

    const response = await apiClient.put(`/kycs/${props.kycId}`, {
      kyc_errors: kycErrors.value,
      ...verificationData
    })

    if (response.data.success) {
      await notifySuccess('اطلاعات با موفقیت ثبت شد')
      resetVerificationState()
      emit('updated')
    } else {
      error.value = 'خطا در ثبت اطلاعات'
    }
  } catch (err) {
    console.error('KYC update error:', err)
    error.value = err.response?.data?.message || 'خطا در ثبت اطلاعات'

    if (err.response?.data?.errors?.phone_verification) {
      await resetVerificationCredentials({ ensureModalOpen: true })

      if (verificationFormRef.value?.setErrors) {
        const phoneError = err.response.data.errors.phone_verification
        verificationFormRef.value.setErrors({
          phone_verification: Array.isArray(phoneError) ? phoneError[0] : phoneError
        })
      }
    }
  } finally {
    saving.value = false
  }
}

const handleSendVerificationCode = async () => {
  isVerified.value = false
  pendingVerificationData.value = {}
  await sendVerificationCode()
}

const handleSubmit = async () => {
  if (isProduction.value) {
    await submitKycUpdate(pendingVerificationData.value)
    return
  }

  await submitKycUpdate()
}

const handleVerificationVerified = async (verificationData) => {
  const data = verificationData || verificationFormRef.value?.getData()

  if (!data?.phone_verification) {
    await notifyError('کد تایید را وارد کنید')
    return
  }

  pendingVerificationData.value = data
  isVerified.value = true
  verificationFormRef.value?.setErrors?.({})
}

const handleUserCloseVerificationDialog = () => {
  verificationFormRef.value?.setErrors?.({})
}


const fetchKycDetails = async () => {
  try {
    loading.value = true
    error.value = null

    const response = await apiClient.get(`/kycs/${props.kycId}`)

    if (response.data.success) {
      kyc.value = response.data.data
      // Initialize errors array from existing errors
      if (kyc.value.errors && Array.isArray(kyc.value.errors)) {
        kycErrors.value = [...kyc.value.errors]
      }
    } else {
      error.value = 'خطا در دریافت اطلاعات'
    }
  } catch (err) {
    console.error('KYC details fetch error:', err)
    error.value = err.response?.data?.message || 'خطا در بارگذاری اطلاعات'
  } finally {
    loading.value = false
  }
}

watch(showVerificationDialog, async (newVal, oldVal) => {
  if (newVal) {
    await nextTick()

    setTimeout(() => {
      verificationFormRef.value?.startTimer?.()
    }, 100)

    setTimeout(() => {
      verificationFormRef.value?.focusFirstInput?.()
    }, 400)
  } else if (oldVal) {
    verificationFormRef.value?.setErrors?.({})
    verificationFormRef.value?.reset?.()
  }
})

watch(() => props.show, (newVal) => {
  if (newVal && props.kycId) {
    kycErrors.value = []
    resetVerificationState()
    fetchKycDetails()
  } else {
    resetVerificationState()
    saving.value = false
    sendingVerification.value = false
  }
})

watch(() => props.kycId, (newVal) => {
  if (newVal && props.show) {
    kycErrors.value = []
    resetVerificationState()
    fetchKycDetails()
  }
})

onMounted(() => {
  if (props.show && props.kycId) {
    fetchKycDetails()
  }
})
</script>

<style scoped>
/* Additional styles if needed */
</style>

