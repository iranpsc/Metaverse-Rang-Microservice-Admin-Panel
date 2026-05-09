import apiClient from '../utils/api'

const baseUrl = '/v1/translations'

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

  async deleteTranslation (translationId) {
    const response = await apiClient.delete(`${baseUrl}/${translationId}`)
    return response.data
  },

  async toggleTranslationStatus (translationId) {
    const response = await apiClient.patch(`${baseUrl}/${translationId}/status`)
    return response.data
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
      const contentType = error.response?.headers?.['content-type']

      if (contentType && contentType.includes('application/json')) {
        const text = await error.response.data.text()
        return {
          type: 'message',
          data: JSON.parse(text)
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

  async deleteModal (translationId, modalId) {
    const response = await apiClient.delete(`${baseUrl}/${translationId}/modals/${modalId}`)
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

  async deleteTab (translationId, modalId, tabId) {
    const response = await apiClient.delete(`${baseUrl}/${translationId}/modals/${modalId}/tabs/${tabId}`)
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

  async deleteField (translationId, modalId, tabId, fieldId) {
    const response = await apiClient.delete(`${baseUrl}/${translationId}/modals/${modalId}/tabs/${tabId}/fields/${fieldId}`)
    return response.data
  }
}


