import apiClient from '../utils/api'

const baseUrl = '/translations'

/**
 * @typedef {object} AutoTranslationRun
 * @property {number} id Run identifier.
 * @property {number} translation_id Destination locale identifier.
 * @property {'openai'|'cloudflare'} provider Provider used for this run.
 * @property {'queued'|'translating'|'publishing'|'completed'|'failed'|'publication_failed'|'cancelled'} status Current run state.
 * @property {number} total Number of candidate keys.
 * @property {number} processed Number of validated translations.
 * @property {number} translated Number of translations applied to the destination.
 * @property {string|null} error Most recent displayable provider diagnostic.
 */

export const translationApi = {
  async getLanguages () {
    const response = await apiClient.get(`${baseUrl}/languages`)
    return response.data.data.languages
  },

  async getTranslations (params = {}) {
    const response = await apiClient.get(baseUrl, { params })
    return response.data.data
  },

  async getTranslation (translationId) {
    const response = await apiClient.get(`${baseUrl}/${translationId}`)
    return response.data.data.translation
  },

  async createTranslation (payload) {
    const response = await apiClient.post(baseUrl, payload)
    return response.data
  },

  async deleteTranslation (translationId, payload = {}) {
    const response = await apiClient.delete(`${baseUrl}/${translationId}`, { data: payload })
    return response.data
  },

  async toggleTranslationStatus (translationId) {
    const response = await apiClient.patch(`${baseUrl}/${translationId}/status`)
    return response.data
  },

  /**
   * Start or resume an auto-translation run for the destination language.
   *
   * @param {number} translationId Destination locale ID in the request path.
   * @param {'openai'|'cloudflare'} [provider='openai'] Provider selected by the admin.
   * @returns {Promise<AutoTranslationRun>} Run from the HTTP 202 response.
   */
  async startAutoTranslation (translationId, provider = 'openai') {
    const response = await apiClient.post(`${baseUrl}/${translationId}/auto-translate`, { provider })
    return response.data.data
  },

  /**
   * Read automatic translation runs that are still active or await publication retry.
   *
   * @returns {Promise<AutoTranslationRun[]>} Active run records for the queue-status panel.
   */
  async getActiveAutoTranslationRuns () {
    const response = await apiClient.get(`${baseUrl}/auto-translate/runs`)
    return response.data.data
  },

  /**
   * Stop a locale-scoped automatic translation run.
   *
   * @param {number} translationId Destination locale ID in the request path.
   * @param {number} runId Active automatic translation run ID.
   * @returns {Promise<AutoTranslationRun>} Cancelled run returned by the API.
   */
  async cancelAutoTranslationRun (translationId, runId) {
    const response = await apiClient.delete(`${baseUrl}/${translationId}/auto-translate/${runId}`)
    return response.data.data
  },

  /**
   * Read the latest run, including its terminal status.
   *
   * @param {number} translationId Destination locale ID in the request path.
   * @returns {Promise<AutoTranslationRun|null>} Latest run, or null before the first run.
   */
  async getLatestAutoTranslationRun (translationId) {
    const response = await apiClient.get(`${baseUrl}/${translationId}/auto-translate`)
    return response.data.data
  },

  /**
   * Read progress and results for a run in the destination language.
   *
   * @param {number} translationId Destination locale ID in the request path.
   * @param {number} runId Run ID in the request path.
   * @returns {Promise<AutoTranslationRun>} Locale-scoped run from the HTTP 200 response.
   */
  async getAutoTranslationRun (translationId, runId) {
    const response = await apiClient.get(`${baseUrl}/${translationId}/auto-translate/${runId}`)
    return response.data.data
  },

  async exportTranslation (translationId) {
    try {
      const response = await apiClient.post(`${baseUrl}/${translationId}/export`, {}, { responseType: 'blob' })
      const disposition = response.headers['content-disposition']
      const contentType = response.headers['content-type']

      if (contentType && contentType.includes('application/json')) {
        const text = await response.data.text()
        return {
          type: 'message',
          data: JSON.parse(text)
        }
      }

      let fileName = `translation-${translationId}.json`
      if (disposition) {
        const matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(disposition)
        if (matches && matches[1]) {
          fileName = matches[1].replace(/['"]/g, '')
        }
      }

      return {
        type: 'file',
        fileName,
        blob: response.data
      }
    } catch (error) {
      const contentType = error.response?.headers?.['content-type'] || ''
      let data = error.response?.data

      if (data instanceof Blob && contentType.includes('application/json')) {
        const text = await data.text()
        data = JSON.parse(text)
      }

      if (data?.requires_phone_verification) {
        throw error
      }

      if (contentType.includes('application/json') && data && typeof data === 'object') {
        return {
          type: 'message',
          data
        }
      }

      if (error.response?.data) {
        return {
          type: 'message',
          data: error.response.data
        }
      }

      throw error
    }
  },

  async importTranslation (file, { code, translationId } = {}) {
    const formData = new FormData()
    formData.append('file', file)

    if (code) {
      formData.append('code', code)
    }

    const url = translationId
      ? `${baseUrl}/${translationId}/import`
      : `${baseUrl}/import`

    const response = await apiClient.post(
      url,
      formData,
      { headers: { 'Content-Type': 'multipart/form-data' } }
    )

    return response.data
  },

  async getModals (translationId, params = {}) {
    const response = await apiClient.get(`${baseUrl}/${translationId}/modals`, { params })
    return response.data.data
  },

  async getModal (translationId, modalId) {
    const response = await apiClient.get(`${baseUrl}/${translationId}/modals/${modalId}`)
    return response.data.data.modal
  },

  async createModal (translationId, payload) {
    const response = await apiClient.post(`${baseUrl}/${translationId}/modals`, payload)
    return response.data
  },

  async updateModal (translationId, modalId, payload) {
    const response = await apiClient.patch(`${baseUrl}/${translationId}/modals/${modalId}`, payload)
    return response.data
  },

  async deleteModal (translationId, modalId, payload = {}) {
    const response = await apiClient.delete(`${baseUrl}/${translationId}/modals/${modalId}`, { data: payload })
    return response.data
  },

  async getTabs (translationId, modalId, params = {}) {
    const response = await apiClient.get(`${baseUrl}/${translationId}/modals/${modalId}/tabs`, { params })
    return response.data.data
  },

  async getTab (translationId, modalId, tabId) {
    const response = await apiClient.get(`${baseUrl}/${translationId}/modals/${modalId}/tabs/${tabId}`)
    return response.data.data.tab
  },

  async createTab (translationId, modalId, payload) {
    const response = await apiClient.post(`${baseUrl}/${translationId}/modals/${modalId}/tabs`, payload)
    return response.data
  },

  async updateTab (translationId, modalId, tabId, payload) {
    const response = await apiClient.patch(`${baseUrl}/${translationId}/modals/${modalId}/tabs/${tabId}`, payload)
    return response.data
  },

  async deleteTab (translationId, modalId, tabId, payload = {}) {
    const response = await apiClient.delete(`${baseUrl}/${translationId}/modals/${modalId}/tabs/${tabId}`, { data: payload })
    return response.data
  },

  async getFields (translationId, modalId, tabId, params = {}) {
    const response = await apiClient.get(`${baseUrl}/${translationId}/modals/${modalId}/tabs/${tabId}/fields`, { params })
    return response.data.data
  },

  async createField (translationId, modalId, tabId, payload) {
    const response = await apiClient.post(`${baseUrl}/${translationId}/modals/${modalId}/tabs/${tabId}/fields`, payload)
    return response.data
  },

  async updateField (translationId, modalId, tabId, fieldId, payload) {
    const response = await apiClient.patch(`${baseUrl}/${translationId}/modals/${modalId}/tabs/${tabId}/fields/${fieldId}`, payload)
    return response.data
  },

  async deleteField (translationId, modalId, tabId, fieldId, payload = {}) {
    const response = await apiClient.delete(`${baseUrl}/${translationId}/modals/${modalId}/tabs/${tabId}/fields/${fieldId}`, { data: payload })
    return response.data
  }
}


