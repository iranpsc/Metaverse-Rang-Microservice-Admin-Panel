import apiClient from '../utils/api'

export function useVideos() {
  const fetchVideoMeta = () => apiClient.get('/videos/meta')
  const fetchVideos = (params) => apiClient.get('/videos', { params })
  const createVideo = (formData) => apiClient.post('/videos', formData, {
    headers: { 'Content-Type': 'multipart/form-data' }
  })
  const updateVideo = (videoId, formData) => apiClient.post(`/videos/${videoId}`, formData, {
    headers: { 'Content-Type': 'multipart/form-data' }
  })
  const deleteVideo = (videoId) => apiClient.delete(`/videos/${videoId}`)

  return {
    fetchVideoMeta,
    fetchVideos,
    createVideo,
    updateVideo,
    deleteVideo
  }
}
