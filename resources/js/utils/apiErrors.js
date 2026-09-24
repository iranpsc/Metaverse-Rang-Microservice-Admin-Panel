/**
 * Shared API error helpers for list pages and forms.
 */

/**
 * @param {unknown} err
 * @returns {boolean}
 */
export function isAuthError(err) {
  const status = err?.response?.status
  return status === 401 || status === 403
}

/**
 * @param {unknown} err
 * @param {string} [fallback='خطا در بارگذاری اطلاعات']
 * @returns {string}
 */
export function getApiErrorMessage(err, fallback = 'خطا در بارگذاری اطلاعات') {
  return err?.response?.data?.message || fallback
}

/**
 * Handle 401/403 on list fetches: clear data and stop without setting error.
 * @param {unknown} err
 * @param {object} [options]
 * @param {() => void} [options.onClear]
 * @param {import('vue').Ref<boolean>} [options.loading]
 * @returns {boolean} true if auth error was handled
 */
export function handleAuthListError(err, options = {}) {
  if (!isAuthError(err)) {
    return false
  }

  options.onClear?.()

  if (options.loading) {
    options.loading.value = false
  }

  return true
}
