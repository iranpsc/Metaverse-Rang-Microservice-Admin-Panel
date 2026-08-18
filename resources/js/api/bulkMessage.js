import apiClient from '../utils/api'

export function sendBulkMessage(data) {
  return apiClient.post('/bulk-messages/send', data)
}

export async function searchBulkMessageUsers({ search, page, per_page = 20 }) {
  const response = await apiClient.get('/bulk-messages/users/search', {
    params: { search, page, per_page }
  })

  if (!response.data.success) {
    throw new Error('خطا در دریافت کاربران')
  }

  return {
    results: response.data.data.options,
    more: response.data.data.pagination.more
  }
}
