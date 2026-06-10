import { defineStore } from 'pinia'
import apiClient from '../utils/api'

const isProductionEnv = () => {
  const metaEnv = document.querySelector('meta[name="app-env"]')?.getAttribute('content')
  return metaEnv === 'production' || import.meta.env.MODE === 'production'
}

export const usePhoneVerificationStore = defineStore('phoneVerification', {
  state: () => ({
    verified: false,
    remainingSeconds: 0,
    expiresAt: null,
    durationMinutes: null,
    showModal: false,
    modalStep: 'duration',
    durationInput: 15,
    sendingCode: false,
    confirming: false,
    codeError: '',
    pendingRetries: [],
    countdownTimer: null,
    statusPollTimer: null
  }),

  getters: {
    isProduction: () => isProductionEnv(),

    formattedRemaining(state) {
      if (!state.verified || state.remainingSeconds <= 0) {
        return null
      }

      const mins = Math.floor(state.remainingSeconds / 60)
      const secs = state.remainingSeconds % 60
      return `${mins}:${secs.toString().padStart(2, '0')}`
    }
  },

  actions: {
    applyStatus(data = {}) {
      this.verified = Boolean(data.verified)
      this.remainingSeconds = data.remaining_seconds ?? 0
      this.expiresAt = data.expires_at ?? null
      this.durationMinutes = data.duration_minutes ?? null
    },

    async fetchStatus() {
      if (!this.isProduction) {
        this.applyStatus({ verified: true, remaining_seconds: null })
        return
      }

      try {
        const response = await apiClient.get('/phone-verification/status')
        if (response.data?.success) {
          this.applyStatus(response.data.data)
        }
      } catch (error) {
        console.error('Phone verification status error:', error)
      }
    },

    startLocalCountdown() {
      this.stopLocalCountdown()

      if (!this.isProduction || !this.verified) {
        return
      }

      this.countdownTimer = setInterval(() => {
        if (this.remainingSeconds > 0) {
          this.remainingSeconds -= 1
        }

        if (this.remainingSeconds <= 0) {
          this.verified = false
          this.stopLocalCountdown()
        }
      }, 1000)
    },

    stopLocalCountdown() {
      if (this.countdownTimer) {
        clearInterval(this.countdownTimer)
        this.countdownTimer = null
      }
    },

    startStatusPolling() {
      this.stopStatusPolling()

      if (!this.isProduction) {
        return
      }

      this.statusPollTimer = setInterval(() => {
        this.fetchStatus()
      }, 60000)
    },

    stopStatusPolling() {
      if (this.statusPollTimer) {
        clearInterval(this.statusPollTimer)
        this.statusPollTimer = null
      }
    },

    init() {
      if (!this.isProduction) {
        return
      }

      this.fetchStatus().then(() => {
        this.startLocalCountdown()
        this.startStatusPolling()
      })
    },

    resetModal() {
      this.modalStep = 'duration'
      this.durationInput = 15
      this.codeError = ''
      this.sendingCode = false
      this.confirming = false
    },

    openModal() {
      this.resetModal()
      this.showModal = true
    },

    closeModal() {
      this.showModal = false
      this.resetModal()
      this.rejectPendingRetries(new Error('Phone verification cancelled'))
    },

    enqueueRetry(retry) {
      this.pendingRetries.push(retry)

      if (!this.showModal) {
        this.openModal()
      }
    },

    rejectPendingRetries(error) {
      const queue = [...this.pendingRetries]
      this.pendingRetries = []
      queue.forEach(({ reject }) => reject(error))
    },

    async resolvePendingRetries() {
      const queue = [...this.pendingRetries]
      this.pendingRetries = []

      for (const { config, resolve, reject } of queue) {
        try {
          const response = await apiClient.request(config)
          resolve(response)
        } catch (error) {
          reject(error)
        }
      }
    },

    async sendCode() {
      this.codeError = ''

      try {
        this.sendingCode = true
        const response = await apiClient.post('/send-verification-sms')

        if (response.data?.success) {
          this.modalStep = 'code'
          return true
        }

        this.codeError = response.data?.message || 'خطا در ارسال کد تایید'
        return false
      } catch (error) {
        this.codeError = error.response?.data?.message || 'خطا در ارسال کد تایید'
        return false
      } finally {
        this.sendingCode = false
      }
    },

    async confirmCode(code) {
      if (!code || code.length !== 6) {
        this.codeError = 'کد تایید را وارد کنید'
        return false
      }

      this.codeError = ''

      try {
        this.confirming = true
        const response = await apiClient.post('/phone-verification/confirm', {
          phone_verification: code,
          duration_minutes: Number(this.durationInput)
        })

        if (response.data?.success) {
          this.applyStatus(response.data.data)
          this.showModal = false
          this.resetModal()
          this.startLocalCountdown()
          await this.resolvePendingRetries()
          return true
        }

        this.codeError = response.data?.message || 'کد تایید صحیح نیست'
        return false
      } catch (error) {
        const phoneError = error.response?.data?.errors?.phone_verification
        this.codeError = phoneError
          ? (Array.isArray(phoneError) ? phoneError[0] : phoneError)
          : (error.response?.data?.message || 'کد تایید صحیح نیست')
        return false
      } finally {
        this.confirming = false
      }
    },

    destroy() {
      this.stopLocalCountdown()
      this.stopStatusPolling()
      this.rejectPendingRetries(new Error('Phone verification store destroyed'))
    }
  }
})
