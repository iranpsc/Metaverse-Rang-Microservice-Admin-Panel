import apiClient from '../utils/api'

export function useMaps() {
  const fetchMaps = (params) => apiClient.get('/maps', { params })
  const createMap = (formData) => apiClient.post('/maps', formData, {
    headers: { 'Content-Type': 'multipart/form-data' }
  })
  const updateMap = (mapId, formData) => apiClient.post(`/maps/${mapId}`, formData, {
    headers: { 'Content-Type': 'multipart/form-data' }
  })
  const insertMapIntoDatabase = (mapId, formData) => apiClient.post(`/maps/${mapId}/insert-into-database`, formData, {
    headers: { 'Content-Type': 'multipart/form-data' }
  })
  const sendVerificationSms = () => apiClient.post('/send-verification-sms')
  const deleteMap = (mapId) => apiClient.delete(`/maps/${mapId}`)

  return {
    fetchMaps,
    createMap,
    updateMap,
    insertMapIntoDatabase,
    sendVerificationSms,
    deleteMap
  }
}
