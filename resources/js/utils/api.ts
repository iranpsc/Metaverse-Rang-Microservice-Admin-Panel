import axios, { type AxiosError, type AxiosRequestConfig } from 'axios'

export type ApiResponse<T> = {
  success: boolean
  data: T
  message?: string
}

const apiClient = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  },
  withCredentials: true
})

const stateChangingMethods = ['post', 'put', 'patch', 'delete']
let csrfCookiePromise: Promise<unknown> | null = null

const getCookie = (name: string) => {
  const value = document.cookie
    .split('; ')
    .find((row) => row.startsWith(`${name}=`))
  return value ? decodeURIComponent(value.split('=')[1]) : null
}

export const getSanctumStatefulHeaders = () => {
  const headers: Record<string, string> = {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
  const xsrf = getCookie('XSRF-TOKEN')
  if (xsrf) headers['X-XSRF-TOKEN'] = xsrf
  const authToken = localStorage.getItem('admin_token')
  if (authToken) headers.Authorization = `Bearer ${authToken}`
  return headers
}

const ensureCsrfCookie = async (force = false) => {
  if (!csrfCookiePromise || force) {
    csrfCookiePromise = axios
      .get('/sanctum/csrf-cookie', {
        withCredentials: true,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
      .catch((error) => {
        csrfCookiePromise = null
        throw error
      })
  }
  await csrfCookiePromise
}

export { ensureCsrfCookie }

apiClient.interceptors.request.use(
  async (config) => {
    const method = config.method?.toLowerCase()
    if (method && stateChangingMethods.includes(method)) {
      const hasXsrfToken = !!getCookie('XSRF-TOKEN')
      if (!hasXsrfToken) await ensureCsrfCookie()
      const token = getCookie('XSRF-TOKEN')
      if (token) {
        config.headers = config.headers || {}
        config.headers['X-XSRF-TOKEN'] = token
      }
    }

    const authToken = localStorage.getItem('admin_token')
    if (authToken) {
      config.headers = config.headers || {}
      config.headers.Authorization = `Bearer ${authToken}`
    }
    return config
  },
  (error) => Promise.reject(error)
)

apiClient.interceptors.response.use(
  (response) => response,
  (error: AxiosError) => {
    if (error.response) {
      if (error.response.status === 401 && window.location.pathname !== '/login') {
        localStorage.removeItem('admin_authenticated')
        localStorage.removeItem('admin_token')
        localStorage.removeItem('admin_token_expires_at')
        localStorage.removeItem('admin_user_data')
        window.location.href = '/login'
        return Promise.reject(error)
      }

      const retryConfig = error.config as AxiosRequestConfig & { _retry?: boolean }
      if (error.response.status === 419 && retryConfig && !retryConfig._retry) {
        retryConfig._retry = true
        return ensureCsrfCookie(true).then(() => {
          const token = getCookie('XSRF-TOKEN')
          if (token && retryConfig.headers) {
            retryConfig.headers['X-XSRF-TOKEN'] = token
          }
          return apiClient.request(retryConfig)
        })
      }
    }
    return Promise.reject(error)
  }
)

const throwApiError = (error: unknown): never => {
  const axiosError = error as AxiosError<{ message?: string }>
  throw (axiosError.response?.data || axiosError.message || 'Request failed')
}

export const authApi = {
  login: async (credentials: Record<string, unknown>) => {
    try {
      const response = await apiClient.post('/login', credentials)
      return response.data
    } catch (error) {
      return throwApiError(error)
    }
  },
  logout: async () => {
    try {
      await apiClient.post('/logout')
      localStorage.removeItem('admin_authenticated')
      localStorage.removeItem('admin_token')
      localStorage.removeItem('admin_token_expires_at')
    } catch (error) {
      return throwApiError(error)
    }
  },
  forgotPassword: async (email: string) => {
    try {
      const response = await apiClient.post('/password/email', { email })
      return response.data
    } catch (error) {
      return throwApiError(error)
    }
  },
  resetPassword: async (data: Record<string, unknown>) => {
    try {
      const response = await apiClient.post('/password/reset', data)
      return response.data
    } catch (error) {
      return throwApiError(error)
    }
  },
  getAuthUser: async () => {
    try {
      const response = await apiClient.get('/me')
      return response.data
    } catch (error) {
      return throwApiError(error)
    }
  }
}

export default apiClient
