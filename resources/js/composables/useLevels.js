import apiClient from '../utils/api'

export function useLevels() {
  const fetchLevels = (params) => apiClient.get('/levels', { params })
  const sendVerificationSms = () => apiClient.post('/send-verification-sms')
  const createLevel = (formData) => apiClient.post('/levels', formData, {
    headers: { 'Content-Type': 'multipart/form-data' }
  })
  const updateLevel = (levelId, formData) => apiClient.post(`/levels/${levelId}`, formData, {
    headers: { 'Content-Type': 'multipart/form-data' }
  })
  const deleteLevel = (levelId, payload = {}) => apiClient.delete(`/levels/${levelId}`, { data: payload })

  return {
    fetchLevels,
    sendVerificationSms,
    createLevel,
    updateLevel,
    deleteLevel
  }
}
