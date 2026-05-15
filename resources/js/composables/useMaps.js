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
  const deleteMap = (mapId, payload = {}) => apiClient.delete(`/maps/${mapId}`, { data: payload })

  return {
    fetchMaps,
    createMap,
    updateMap,
    insertMapIntoDatabase,
    deleteMap
  }
}
