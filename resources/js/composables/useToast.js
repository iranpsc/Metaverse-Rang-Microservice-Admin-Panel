import { ref } from 'vue'
import { notifyError, notifyInfo, notifySuccess, notifyWarning } from '../utils/notifications'

const emptyToasts = ref([])

const notifyByType = (message, type = 'info') => {
  switch (type) {
    case 'success':
      return notifySuccess(message)
    case 'error':
      return notifyError(message)
    case 'warning':
      return notifyWarning(message)
    default:
      return notifyInfo(message)
  }
}

export function useToast() {
  return {
    // Kept for compatibility with existing ToastContainer usage.
    toasts: emptyToasts,
    showToast: notifyByType,
    removeToast: () => {},
    clearToasts: () => {}
  }
}

