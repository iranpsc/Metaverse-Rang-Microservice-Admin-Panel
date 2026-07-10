import { defineStore } from 'pinia'
import { authApi } from '../utils/api'
import { clearAuthStorage, hasValidAuthToken } from '../utils/authorization'

const restoreUserFromStorage = () => {
  try {
    const userDataStr = localStorage.getItem('admin_user_data')
    return userDataStr ? JSON.parse(userDataStr) : null
  } catch {
    return null
  }
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: restoreUserFromStorage(),
    isAuthenticated: localStorage.getItem('admin_authenticated') === 'true',
    isLoading: false,
    checkAuthPromise: null
  }),
  actions: {
    setUserData(userData) {
      if (userData) {
        this.user = userData
        this.isAuthenticated = true
        localStorage.setItem('admin_authenticated', 'true')
        localStorage.setItem('admin_user_data', JSON.stringify(userData))
      } else {
        this.user = null
        this.isAuthenticated = false
        localStorage.removeItem('admin_authenticated')
        localStorage.removeItem('admin_user_data')
      }
    },
    isTokenExpired() {
      return !hasValidAuthToken()
    },
    async login(credentials) {
      this.isLoading = true
      try {
        const loginResponse = await authApi.login(credentials)
        if (loginResponse.success && loginResponse.data) {
          const { token, token_expires_at } = loginResponse.data
          localStorage.setItem('admin_authenticated', 'true')
          localStorage.setItem('admin_token', token)
          localStorage.setItem('admin_token_expires_at', token_expires_at)
          try {
            const userResponse = await authApi.getAuthUser()
            if (userResponse.success && userResponse.data) {
              this.setUserData(userResponse.data)
            }
          } catch {
            // ignore - token is already stored
          }
          this.isAuthenticated = true
          return { success: true }
        }
        throw new Error(loginResponse.message || 'خطا در ورود به سیستم')
      } catch (error) {
        this.setUserData(null)
        localStorage.removeItem('admin_token')
        localStorage.removeItem('admin_token_expires_at')
        return { success: false, error: error.message || 'ایمیل یا رمز عبور نامعتبر است' }
      } finally {
        this.isLoading = false
      }
    },
    async logout() {
      this.isLoading = true
      try {
        await authApi.logout()
      } finally {
        this.setUserData(null)
        localStorage.removeItem('admin_token')
        localStorage.removeItem('admin_token_expires_at')
        this.isLoading = false
      }
      return { success: true }
    },
    async checkAuth() {
      if (this.checkAuthPromise) return this.checkAuthPromise
      const token = localStorage.getItem('admin_token')
      if (!token || this.isTokenExpired()) {
        this.setUserData(null)
        clearAuthStorage()
        return { success: false, authenticated: false }
      }
      this.checkAuthPromise = (async () => {
        this.isLoading = true
        try {
          const response = await authApi.getAuthUser()
          if (response?.success && response?.data) {
            this.setUserData(response.data)
            return { success: true, authenticated: true }
          }
          this.setUserData(null)
          return { success: false, authenticated: false }
        } catch (error) {
          return { success: false, authenticated: false, error: error.message }
        } finally {
          this.isLoading = false
          setTimeout(() => {
            this.checkAuthPromise = null
          }, 100)
        }
      })()
      return this.checkAuthPromise
    },
    init() {
      const stored = localStorage.getItem('admin_authenticated')
      const token = localStorage.getItem('admin_token')
      if (stored === 'true' && token && !this.isTokenExpired()) {
        this.checkAuth().catch(() => {})
      } else {
        this.setUserData(null)
        clearAuthStorage()
      }
    },
    async refreshUser() {
      try {
        const response = await authApi.getAuthUser()
        if (response?.success && response?.data) {
          this.setUserData(response.data)
          return { success: true, data: response.data }
        }
      } catch (error) {
        return { success: false, error: error.message || 'خطا در بروزرسانی کاربر' }
      }
      return { success: false, error: 'امکان بروزرسانی اطلاعات کاربر وجود ندارد' }
    }
  }
})
