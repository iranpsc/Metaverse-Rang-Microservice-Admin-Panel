import Swal from 'sweetalert2'

const themedPopupClasses =
  'rounded-2xl border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)] text-[var(--theme-text-primary)] shadow-[var(--theme-shadow-lg)]'

const themedTitleClass = 'text-[var(--theme-text-primary)] font-semibold'
const themedContentClass = 'text-[var(--theme-text-secondary)] text-sm'
const confirmButtonClass =
  'inline-flex items-center justify-center rounded-full mx-2 px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-[#7C3AED] to-[#06B6D4] hover:opacity-90 transition'
const cancelButtonClass =
  'inline-flex items-center justify-center rounded-full mx-2 px-4 py-2 text-sm font-medium border border-[var(--theme-border)] text-[var(--theme-text-primary)] bg-[var(--theme-bg-glass)] hover:bg-[var(--theme-bg-elevated)] transition'

const getBaseOptions = () => ({
  background: 'var(--theme-bg-elevated)',
  color: 'var(--theme-text-primary)',
  customClass: {
    popup: themedPopupClasses,
    title: themedTitleClass,
    htmlContainer: themedContentClass
  },
  didOpen: (popup) => {
    popup.setAttribute('dir', 'rtl')
  }
})

const getToastOptions = () => ({
  ...getBaseOptions(),
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timerProgressBar: true
})

/**
 * Show a success notification
 * @param {string} message - The message to display
 */
export const notifySuccess = (message) => {
  return Swal.fire({
    ...getToastOptions(),
    icon: 'success',
    text: message,
    timer: 3000
  })
}

/**
 * Show an error notification
 * @param {string} message - The message to display
 */
export const notifyError = (message) => {
  return Swal.fire({
    ...getToastOptions(),
    icon: 'error',
    text: message,
    timer: 3500
  })
}

/**
 * Show a warning notification
 * @param {string} message - The message to display
 */
export const notifyWarning = (message) => {
  return Swal.fire({
    ...getToastOptions(),
    icon: 'warning',
    text: message,
    timer: 3500
  })
}

/**
 * Show an info notification
 * @param {string} message - The message to display
 * @param {string} title - Optional title
 */
export const notifyInfo = (message, title = 'اطلاعات') => {
  return Swal.fire({
    ...getToastOptions(),
    icon: 'info',
    title,
    text: message,
    timer: 3000
  })
}

/**
 * Show a confirmation dialog
 * @param {string} message - The message to display
 * @param {string} title - Optional title
 * @param {Object} options - Additional Swal options
 * @returns {Promise} - Resolves to result if confirmed, rejects if cancelled
 */
export const confirm = (message, title = 'آیا مطمئن هستید؟', options = {}) => {
  const { confirmText, cancelText, ...restOptions } = options

  return Swal.fire({
    ...getBaseOptions(),
    title,
    text: message,
    icon: 'question',
    showCancelButton: true,
    buttonsStyling: false,
    customClass: {
      ...getBaseOptions().customClass,
      confirmButton: confirmButtonClass,
      cancelButton: cancelButtonClass
    },
    confirmButtonText: confirmText || 'بله، انجام شود',
    cancelButtonText: cancelText || 'لغو',
    ...restOptions
  })
}

/**
 * Export Swal for advanced usage
 */
export { Swal }

