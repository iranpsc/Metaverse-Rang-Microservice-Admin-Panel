import { storeToRefs } from 'pinia'
import { useAuthStore } from '../store/authStore'

export function useAuth() {
  const store = useAuthStore()
  const { user, isAuthenticated, isLoading } = storeToRefs(store)

  return {
    user,
    isAuthenticated,
    isLoading,
    login: store.login,
    logout: store.logout,
    checkAuth: store.checkAuth,
    init: store.init,
    refreshUser: store.refreshUser,
    setUser: store.setUserData
  }
}

