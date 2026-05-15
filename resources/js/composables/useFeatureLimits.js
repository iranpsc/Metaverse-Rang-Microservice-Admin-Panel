import apiClient from '../utils/api'

export function useFeatureLimits() {
  const createFeatureLimit = (payload) => apiClient.post('/lands/feature-limits', payload)
  const deleteFeatureLimit = (id, payload = {}) => apiClient.delete(`/lands/feature-limits/${id}`, { data: payload })
  const fetchFeatureLimits = (params) => apiClient.get('/lands/feature-limits', { params })

  return {
    createFeatureLimit,
    deleteFeatureLimit,
    fetchFeatureLimits
  }
}
