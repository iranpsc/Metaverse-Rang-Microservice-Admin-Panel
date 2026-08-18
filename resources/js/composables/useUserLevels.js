import apiClient from '../utils/api'

export function useUserLevels() {
  const fetchUsers = (params) => apiClient.get('/user-levels', { params })

  const searchUsersForSelect = ({ search, page, per_page = 10 }) =>
    apiClient.get('/users/search', {
      params: {
        search,
        page,
        per_page
      }
    })

  const promoteUser = (userId, score) =>
    apiClient.post('/user-levels/promote', {
      user_id: userId,
      score
    })

  return {
    fetchUsers,
    searchUsersForSelect,
    promoteUser
  }
}
