import apiClient from '../utils/api'

export function useFeatureLimits() {
  const sendVerificationSms = () => apiClient.post('/send-verification-sms')
  const createFeatureLimit = (payload) => apiClient.post('/lands/feature-limits', payload)
  const deleteFeatureLimit = (id, payload) => apiClient.delete(`/lands/feature-limits/${id}`, { data: payload })
  const fetchFeatureLimits = (params) => apiClient.get('/lands/feature-limits', { params })

  return {
    sendVerificationSms,
    createFeatureLimit,
    deleteFeatureLimit,
    fetchFeatureLimits
  }
}
