import { ref } from 'vue'
import { getApiErrorMessage, handleAuthListError } from '../utils/apiErrors'

/**
 * Pagination + search state for standard admin list pages.
 * Pass fetch callbacks into search/clear/goToPage after defining the fetch function.
 *
 * @param {object} [options]
 * @param {number} [options.perPage=10]
 */
export function usePaginatedList(options = {}) {
  const perPage = options.perPage ?? 10

  const loading = ref(true)
  const error = ref(null)
  const pagination = ref(null)
  const searchTerm = ref('')
  const currentPage = ref(1)

  const buildParams = (extra = {}) => {
    const params = {
      page: currentPage.value,
      per_page: perPage,
      ...extra
    }

    const term = searchTerm.value?.trim()
    if (term) {
      params.search = term
    }

    return params
  }

  const resetToFirstPage = () => {
    currentPage.value = 1
  }

  /**
   * @param {() => Promise<void>} fetcher
   * @param {object} [runOptions]
   * @param {string} [runOptions.fallbackMessage]
   * @param {() => void} [runOptions.onClear]
   * @param {string} [runOptions.logLabel]
   */
  const execute = async (fetcher, runOptions = {}) => {
    const {
      fallbackMessage = 'خطا در بارگذاری اطلاعات',
      onClear,
      logLabel
    } = runOptions

    try {
      loading.value = true
      error.value = null
      await fetcher()
    } catch (err) {
      if (logLabel) {
        console.error(logLabel, err)
      }

      if (handleAuthListError(err, { onClear, loading })) {
        return
      }

      error.value = getApiErrorMessage(err, fallbackMessage)
      onClear?.()
    } finally {
      loading.value = false
    }
  }

  const search = (fetch) => {
    resetToFirstPage()
    return fetch()
  }

  const clear = (fetch) => {
    resetToFirstPage()
    return fetch()
  }

  const goToPage = (page, fetch) => {
    if (page >= 1 && page <= (pagination.value?.last_page ?? page)) {
      currentPage.value = page
      return fetch()
    }
  }

  return {
    perPage,
    loading,
    error,
    pagination,
    searchTerm,
    currentPage,
    buildParams,
    execute,
    search,
    clear,
    goToPage,
    resetToFirstPage
  }
}
