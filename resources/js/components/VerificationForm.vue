<template>
  <div class="space-y-6" dir="rtl">
    <!-- Inputs in Column Layout -->
    <div class="space-y-6">
      <!-- Phone Verification -->
      <div class="space-y-2">
        <!-- Alert: Code Sent - above verification input -->
        <Alert
          v-if="codeSent"
          variant="success"
          message="کد تایید به شماره شما ارسال شد. لطفا کد تایید را وارد کنید."
          :dismissible="false"
        />

        <div>
          <MaskedInput
            ref="maskedInputRef"
            v-model="phoneVerification"
            placeholder="کد تایید را وارد کنید"
            :max-length="6"
            :error="verificationErrors.phone_verification"
            :show-toggle="true"
            :auto-reveal-on-focus="true"
            @complete="handleAutoSubmit"
          />

          <!-- Timer Display (shown when countdown > 0) - directly beneath input -->
          <div v-if="countdown > 0" class="mt-2 text-right">
            <div class="text-sm text-[var(--theme-text-secondary)]">زمان باقیمانده: {{ formatTime(countdown) }}</div>
          </div>

          <!-- Resend Text Button (shown when countdown is 0) - directly beneath input -->
          <div v-else-if="countdown === 0" class="mt-2 text-right">
            <button
              @click="sendSMS"
              :disabled="sendingSMS"
              class="text-sm text-primary-400 hover:text-primary-300 underline transition-colors flex items-center gap-2"
              :class="{
                'cursor-pointer': !sendingSMS,
                'cursor-not-allowed opacity-50': sendingSMS
              }"
            >
              <Spinner v-if="sendingSMS" size="sm" />
              <span>ارسال مجدد کد تایید</span>
            </button>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onUnmounted, onMounted, nextTick } from 'vue'
import apiClient from '../utils/api'
import { MaskedInput, Alert, Spinner } from './ui'
import { notifyError } from '../utils/notifications'

const props = defineProps({
  autoStart: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['verified'])

const phoneVerification = ref('')
const sendingSMS = ref(false)
const countdown = ref(0)
const verificationErrors = ref({})
const codeSent = ref(false)
const maskedInputRef = ref(null)

let countdownInterval = null

// Clear error when user starts typing
watch(phoneVerification, () => {
  if (verificationErrors.value.phone_verification) {
    delete verificationErrors.value.phone_verification
  }
})

const formatTime = (seconds) => {
  const mins = Math.floor(seconds / 60)
  const secs = seconds % 60
  return `${mins}:${secs.toString().padStart(2, '0')}`
}

const sendSMS = async () => {
  try {
    sendingSMS.value = true

    const response = await apiClient.post('/send-verification-sms')

    if (response.data.success) {
      // Show alert that code was sent
      codeSent.value = true

      // Start countdown (1 minute = 60 seconds to match token expiry)
      countdown.value = 60
      if (countdownInterval) clearInterval(countdownInterval)
      countdownInterval = setInterval(() => {
        countdown.value--
        if (countdown.value <= 0) {
          clearInterval(countdownInterval)
          countdownInterval = null
        }
      }, 1000)
    }
  } catch (err) {
    console.error('SMS send error:', err)
    await notifyError(err.response?.data?.message || 'خطا در ارسال کد تایید')
  } finally {
    sendingSMS.value = false
  }
}

const validate = async () => {
  verificationErrors.value = {}

  const metaEnv = document.querySelector('meta[name="app-env"]')?.getAttribute('content')
  const isProduction = metaEnv === 'production' || import.meta.env.MODE === 'production'

  if (isProduction) {
    if (!phoneVerification.value || phoneVerification.value.length !== 6) {
      verificationErrors.value.phone_verification = 'کد تایید را وارد کنید'
      return false
    }
  }

  emit('verified', getData(isProduction))
  return true
}

const getData = (isProduction = null) => {
  if (isProduction === null) {
    const metaEnv = document.querySelector('meta[name="app-env"]')?.getAttribute('content')
    isProduction = metaEnv === 'production' || import.meta.env.MODE === 'production'
  }

  return {
    phone_verification: isProduction ? phoneVerification.value : null
  }
}

const startTimer = () => {
  // Start countdown (1 minute = 60 seconds to match token expiry)
  countdown.value = 60
  codeSent.value = true
  if (countdownInterval) clearInterval(countdownInterval)
  countdownInterval = setInterval(() => {
    countdown.value--
    if (countdown.value <= 0) {
      clearInterval(countdownInterval)
      countdownInterval = null
    }
  }, 1000)
}

const setErrors = (errors) => {
  verificationErrors.value = errors || {}
}

const handleAutoSubmit = async (value) => {
  // Auto-submit when all 6 digits are entered
  const isValid = await validate()
  if (isValid) {
    // The validate function already emits 'verified', so we don't need to do anything else
  }
}

const reset = () => {
  phoneVerification.value = ''
  verificationErrors.value = {}
  codeSent.value = false
  if (countdownInterval) {
    clearInterval(countdownInterval)
    countdownInterval = null
  }
  countdown.value = 0
}

// Focus the first input
const focusFirstInput = () => {
  if (maskedInputRef.value && maskedInputRef.value.focusFirstInput) {
    maskedInputRef.value.focusFirstInput()
  }
}

// Expose methods to parent
defineExpose({
  validate,
  getData,
  reset,
  sendSMS,
  startTimer,
  setErrors,
  codeSent,
  focusFirstInput
})

// Auto-start timer if prop is set (when code was already sent)
onMounted(() => {
  // Use nextTick to ensure component is fully rendered
  nextTick(() => {
    if (props.autoStart) {
      startTimer()
    }
  })
})

// Cleanup on unmount
onUnmounted(() => {
  if (countdownInterval) {
    clearInterval(countdownInterval)
  }
})
</script>

<style scoped>
/* Additional styles if needed */
</style>

