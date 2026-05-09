import apiClient from '../utils/api'

export const profileApi = {
  async getProfile () {
    const response = await apiClient.get('/profile')
    return response.data
  },

  async updateProfileInfo (payload) {
    let formData

    if (payload instanceof FormData) {
      formData = payload
    } else {
      formData = new FormData()
      Object.entries(payload || {}).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
          formData.append(key, value)
        }
      })
    }

    if (!formData.has('_method')) {
      formData.append('_method', 'PUT')
    }

    const response = await apiClient.post('/profile/info', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    return response.data
  },

  async requestPasswordChange (payload) {
    const response = await apiClient.post('/profile/password/request', payload)
    return response.data
  },

  async verifyPasswordChange (payload) {
    const response = await apiClient.post('/profile/password/verify', payload)
    return response.data
  }
}


