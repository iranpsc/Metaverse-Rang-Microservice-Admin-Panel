import { ref, computed, watch, nextTick } from 'vue'
import apiClient from '../utils/api'
import { notifyError } from '../utils/notifications'
import { confirm } from '../utils/notifications'

/**
 * Phone verification for production CUD operations.
 *
 * - beginVerifyForSubmit(): send code → verify in modal → close → user submits (defer)
 * - runWithVerification(action): send code → verify → run action immediately (delete)
 * - confirmThenVerify(options, action): confirm dialog → then runWithVerification
 */
export function usePhoneVerification() {
  const showVerificationDialog = ref(false)
  const verifyingCode = ref(false)
  const sendingVerification = ref(false)
  const isVerified = ref(false)
  const pendingVerificationData = ref({})
  const verificationFormRef = ref(null)
  const pendingImmediateAction = ref(null)
  const executionMode = ref('defer')

  const isProduction = computed(() => {
    const metaEnv = document.querySelector('meta[name="app-env"]')?.getAttribute('content')
    return metaEnv === 'production' || import.meta.env.MODE === 'production'
  })

  const setPhoneVerificationError = (message) => {
    verificationFormRef.value?.setErrors?.({
      phone_verification: message
    })
  }

  const ensureVerificationModalOpen = async () => {
    if (!showVerificationDialog.value) {
      showVerificationDialog.value = true
      await nextTick()
    }
  }

  const resetVerificationState = () => {
    isVerified.value = false
    pendingVerificationData.value = {}
    pendingImmediateAction.value = null
    executionMode.value = 'defer'
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

  const validateVerificationCode = async (code) => {
    if (!isProduction.value) {
      return { success: true, data: {} }
    }

    if (!code) {
      return { success: false, message: 'کد تایید را وارد کنید' }
    }

    try {
      const response = await apiClient.post('/verify-verification-sms', {
        phone_verification: code
      })

      if (response.data.success) {
        return {
          success: true,
          data: { phone_verification: code }
        }
      }

      return {
        success: false,
        message: response.data.message || 'کد تایید صحیح نیست'
      }
    } catch (err) {
      const phoneError = err.response?.data?.errors?.phone_verification
      return {
        success: false,
        message: phoneError
          ? (Array.isArray(phoneError) ? phoneError[0] : phoneError)
          : (err.response?.data?.message || 'کد تایید صحیح نیست')
      }
    }
  }

  const getSubmitPayload = () => {
    if (!isProduction.value) {
      return {}
    }
    return { ...pendingVerificationData.value }
  }

  const handleVerificationVerified = async (verificationData) => {
    const data = verificationData || verificationFormRef.value?.getData()
    const code = data?.phone_verification

    verifyingCode.value = true

    try {
      const result = await validateVerificationCode(code)

      if (!result.success) {
        setPhoneVerificationError(result.message)
        isVerified.value = false
        pendingVerificationData.value = {}
        return
      }

      pendingVerificationData.value = result.data
      isVerified.value = true
      verificationFormRef.value?.setErrors?.({})
      showVerificationDialog.value = false

      if (executionMode.value === 'immediate' && pendingImmediateAction.value) {
        const action = pendingImmediateAction.value
        pendingImmediateAction.value = null
        await action(getSubmitPayload())
      }
    } finally {
      verifyingCode.value = false
    }
  }

  const beginVerifyForSubmit = async () => {
    executionMode.value = 'defer'
    pendingImmediateAction.value = null
    isVerified.value = false
    pendingVerificationData.value = {}

    if (!isProduction.value) {
      isVerified.value = true
      return true
    }

    return sendVerificationCode()
  }

  const runWithVerification = async (action) => {
    if (!isProduction.value) {
      await action({})
      return true
    }

    executionMode.value = 'immediate'
    pendingImmediateAction.value = action
    isVerified.value = false
    pendingVerificationData.value = {}

    return sendVerificationCode()
  }

  const confirmThenVerify = async (confirmOptions, action) => {
    const result = await confirm(
      confirmOptions.message,
      confirmOptions.title,
      {
        confirmText: confirmOptions.confirmText || 'بله، حذف شود',
        cancelText: confirmOptions.cancelText || 'انصراف'
      }
    )

    if (!result.isConfirmed) {
      return false
    }

    return runWithVerification(action)
  }

  const handleApiVerificationError = async (err) => {
    if (!err?.response?.data?.errors?.phone_verification) {
      return false
    }

    isVerified.value = false
    pendingVerificationData.value = {}
    pendingImmediateAction.value = null
    executionMode.value = 'defer'

    await ensureVerificationModalOpen()

    const phoneError = err.response.data.errors.phone_verification
    setPhoneVerificationError(
      Array.isArray(phoneError) ? phoneError[0] : phoneError
    )

    return true
  }

  const handleUserCloseVerificationDialog = () => {
    verificationFormRef.value?.setErrors?.({})
    if (executionMode.value === 'immediate' && !verifyingCode.value) {
      pendingImmediateAction.value = null
    }
  }

  watch(showVerificationDialog, async (newVal, oldVal) => {
    if (newVal) {
      await nextTick()
      setTimeout(() => verificationFormRef.value?.startTimer?.(), 100)
      setTimeout(() => verificationFormRef.value?.focusFirstInput?.(), 400)
    } else if (oldVal) {
      verificationFormRef.value?.setErrors?.({})
      verificationFormRef.value?.reset?.()
    }
  })

  return {
    showVerificationDialog,
    verifyingCode,
    sendingVerification,
    isVerified,
    verificationFormRef,
    isProduction,
    sendVerificationCode,
    beginVerifyForSubmit,
    getSubmitPayload,
    runWithVerification,
    confirmThenVerify,
    handleVerificationVerified,
    handleUserCloseVerificationDialog,
    handleApiVerificationError,
    resetVerificationState,
    setPhoneVerificationError,
    ensureVerificationModalOpen
  }
}

/**
 * Merge verification payload into a plain object or FormData.
 */
export function applyVerificationPayload(target, verificationPayload = {}) {
  const code = verificationPayload?.phone_verification
  if (!code) {
    return target
  }

  if (target instanceof FormData) {
    target.append('phone_verification', code)
    return target
  }

  return {
    ...target,
    phone_verification: code
  }
}
