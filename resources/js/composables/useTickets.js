import { ref } from 'vue'
import apiClient from '../utils/api'
import { formatPersianDate } from '../utils/dateFormatter'

export function useTickets() {
  const tickets = ref([])
  const loading = ref(false)
  const error = ref(null)
  const pagination = ref({
    total: 0,
    per_page: 10,
    current_page: 1,
    last_page: 1,
    from: 0,
    to: 0
  })

  /**
   * Fetch tickets by department
   */
  const fetchTickets = async (department, page = 1, perPage = 10) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiClient.get('/tickets', {
        params: {
          department,
          page,
          per_page: perPage
        }
      })

      if (response.data.success) {
        tickets.value = response.data.data.tickets
        pagination.value = response.data.data.pagination
      }

      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'خطا در دریافت تیکت‌ها'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Send response to a ticket
   */
  const sendResponse = async (ticketId, responseData) => {
    loading.value = true
    error.value = null

    try {
      const formData = new FormData()
      formData.append('response', responseData.response)
      
      if (responseData.attachment) {
        formData.append('attachment', responseData.attachment)
      }

      const response = await apiClient.post(`/tickets/${ticketId}/response`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })

      if (response.data.success) {
        // Update the ticket in the list
        const index = tickets.value.findIndex(t => t.id === ticketId)
        if (index !== -1) {
          tickets.value[index] = response.data.data.ticket
        }
      }

      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'خطا در ارسال پاسخ'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Transfer ticket to another department
   */
  const transferTicket = async (ticketId, transferData) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiClient.post(`/tickets/${ticketId}/transfer`, transferData)

      if (response.data.success) {
        // Remove ticket from current list
        const index = tickets.value.findIndex(t => t.id === ticketId)
        if (index !== -1) {
          tickets.value.splice(index, 1)
        }
      }

      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'خطا در ارجاع تیکت'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Get available departments
   */
  const getDepartments = async () => {
    try {
      const response = await apiClient.get('/tickets/departments')
      return response.data.data.departments
    } catch (err) {
      error.value = err.response?.data?.message || 'خطا در دریافت لیست واحدها'
      throw err
    }
  }

  /**
   * Get status badge class
   */
  const getStatusBadgeClass = (status) => {
    const classes = {
      0: 'badge-primary',
      1: 'badge-success',
      3: 'badge-info',
      4: 'badge-success'
    }
    return classes[status] || 'badge-secondary'
  }

  /**
   * Get status label
   */
  const getStatusLabel = (status) => {
    const labels = {
      0: 'جدید',
      1: 'پاسخ داده شده',
      3: 'درحال بررسی',
      4: 'بسته شده'
    }
    return labels[status] || 'نامشخص'
  }

  /**
   * Get importance options
   */
  const getImportanceOptions = () => {
    return [
      { value: -1, label: 'کم' },
      { value: 0, label: 'متوسط' },
      { value: 1, label: 'زیاد' }
    ]
  }

  /**
   * Format date
   */
  const formatDate = (dateString) => {
    return formatPersianDate(dateString)
  }

  return {
    tickets,
    loading,
    error,
    pagination,
    fetchTickets,
    sendResponse,
    transferTicket,
    getDepartments,
    getStatusBadgeClass,
    getStatusLabel,
    getImportanceOptions,
    formatDate
  }
}

