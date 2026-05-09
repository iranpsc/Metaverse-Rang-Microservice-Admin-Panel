import apiClient from '../utils/api'

export function useLevelGift() {
  const fetchLevelGift = (levelId) => apiClient.get(`/levels/${levelId}/gift`)
  const sendVerificationSms = () => apiClient.post('/send-verification-sms')
  const saveLevelGift = (url, formData) => apiClient.post(url, formData, {
    headers: { 'Content-Type': 'multipart/form-data' }
  })

  return {
    fetchLevelGift,
    sendVerificationSms,
    saveLevelGift
  }
}
