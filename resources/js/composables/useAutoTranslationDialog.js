import { isCancel } from 'axios'
import { onScopeDispose } from 'vue'
import { translationApi } from '../api/translations'
import { getApiErrorMessage } from '../utils/apiErrors'
import { confirm, Swal } from '../utils/notifications'
import { formatPersianNumber } from '../utils/numberFormatter'

/** @typedef {import('../api/translations').AutoTranslationRun} AutoTranslationRun */

/**
 * Format the current stage and any provider error as plain text.
 *
 * @param {AutoTranslationRun} run Current run returned by the API.
 * @returns {string}
 */
function describeRun (run) {
  switch (run.status) {
    case 'queued':
      return 'در صف ترجمه. پردازش هنوز شروع نشده است.'
    case 'translating': {
      const progress = `در حال ترجمه ${formatPersianNumber(run.processed)} از ${formatPersianNumber(run.total)} عبارت`
      return run.error ? `${progress}\nتلاش دوباره به‌صورت خودکار انجام می‌شود.\n${run.error}` : progress
    }
    case 'publishing':
      return 'در حال انتشار JSON'
    case 'publication_failed':
      return 'ترجمه‌ها ذخیره شدند. انتشار JSON ناموفق بود.'
    case 'failed':
      return run.error || 'ترجمه خودکار انجام نشد.'
    default:
      if (run.translated > 0) return `${formatPersianNumber(run.translated)} عبارت ترجمه شد.`
      return run.total === 0 ? 'عبارت قابل ترجمه‌ای پیدا نشد.' : 'هیچ عبارتی در این اجرا ذخیره نشد.'
  }
}

/**
 * Poll an auto-translation run while its SweetAlert dialog is open.
 *
 * @param {() => Promise<unknown>} refreshTranslations Refresh language metadata after publication.
 * @returns {(row: {id: number, name: string}) => Promise<void>} Open or reopen the selected locale's dialog.
 */
export function useAutoTranslationDialog (refreshTranslations) {
  let loadingRun = false
  let currentPopup
  let disposed = false

  /**
   * Load the latest run or submit a start request through the shared verification flow.
   *
   * @param {number} translationId Destination locale ID.
   * @param {boolean} restart Whether the admin explicitly requested another run or publication retry.
   * @returns {Promise<AutoTranslationRun>}
   */
  async function loadRun (translationId, restart) {
    if (!restart) {
      const run = await translationApi.getLatestAutoTranslationRun(translationId)
      if (run) return run
    }

    return translationApi.startAutoTranslation(translationId)
  }

  /**
   * Show and track a locale's run until the admin dismisses the dialog.
   *
   * @param {{id: number, name: string}} row Selected language.
   * @param {boolean} [restart=false] Whether to submit a new request explicitly.
   * @returns {Promise<void>}
   */
  async function open (row, restart = false) {
    if (loadingRun || disposed) return

    loadingRun = true
    let run
    let requestError
    try {
      // A protected start request stays pending while the phone modal is open.
      run = await loadRun(row.id, restart)
    } catch (error) {
      if (isCancel(error)) return
      requestError = error
    } finally {
      loadingRun = false
    }

    if (disposed) return

    let timer
    let closed = false
    let retryRun = false
    const close = () => {
      closed = true
      clearTimeout(timer)
    }
    const result = confirm('در حال دریافت وضعیت ترجمه…', 'ترجمه خودکار', {
      icon: undefined,
      title: undefined,
      titleText: `ترجمه خودکار ${row.name}`,
      showConfirmButton: false,
      showCloseButton: true,
      closeButtonAriaLabel: 'بستن',
      cancelText: 'بستن',
      allowEscapeKey: true,
      allowOutsideClick: true,
      willClose: close,
      didDestroy: close
    })
    const popup = Swal.getPopup()
    currentPopup = popup
    Swal.getHtmlContainer().classList.add('whitespace-pre-line', 'break-words')
    Swal.showLoading()

    const isCurrent = () => !closed && !disposed && popup === currentPopup && popup === Swal.getPopup()

    /**
     * Show a failed status request and let the admin fetch it again.
     *
     * @param {unknown} error Request error returned by the admin API.
     * @returns {void}
     */
    function showRequestError (error) {
      if (!isCurrent()) return

      Swal.hideLoading()
      Swal.update({
        icon: 'error',
        text: getApiErrorMessage(error, 'دریافت وضعیت ترجمه خودکار امکان‌پذیر نبود.'),
        showConfirmButton: true,
        confirmButtonText: 'بررسی دوباره وضعیت'
      })
    }

    /**
     * Render a run and schedule another status check while it is active.
     *
     * @param {AutoTranslationRun} run Fresh status returned by the API.
     * @returns {void}
     */
    function showRun (run) {
      if (!isCurrent()) return

      const text = describeRun(run)
      if (['queued', 'translating', 'publishing'].includes(run.status)) {
        Swal.update({ text })
        timer = setTimeout(() => {
          if (!isCurrent()) return
          void translationApi.getAutoTranslationRun(row.id, run.id).then(showRun, showRequestError)
        }, 2000)
        return
      }

      retryRun = true
      const completed = run.status === 'completed'
      Swal.hideLoading()
      Swal.update({
        text,
        icon: completed ? 'success' : 'error',
        showConfirmButton: true,
        confirmButtonText: run.status === 'publication_failed' ? 'تلاش دوباره برای انتشار' : 'اجرای دوباره'
      })
      if (completed && run.translated > 0) void refreshTranslations()
    }

    if (run) {
      showRun(run)
    } else {
      showRequestError(requestError)
    }
    const { isConfirmed } = await result
    if (currentPopup === popup) currentPopup = undefined
    if (isConfirmed && !disposed) await open(row, retryRun)
  }

  onScopeDispose(() => {
    disposed = true
    if (currentPopup && currentPopup === Swal.getPopup()) Swal.close()
  })

  return open
}
