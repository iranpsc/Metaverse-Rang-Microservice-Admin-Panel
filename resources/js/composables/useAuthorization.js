import { storeToRefs } from 'pinia'
import { useAuthStore } from '../store/authStore'
import { canAccess, canAccessMenuItem, hasValidAuthToken, clearAuthStorage } from '../utils/authorization'

export function useAuthorization() {
  const store = useAuthStore()
  const { user, isAuthenticated, isLoading } = storeToRefs(store)

  const can = (access) => canAccess(access, store.user)
  const canViewMenuItem = (item, parentAccess) => canAccessMenuItem(item, store.user, parentAccess)

  return {
    user,
    isAuthenticated,
    isLoading,
    can,
    canViewMenuItem,
    hasValidAuthToken,
    clearAuthStorage,
    refreshUser: store.refreshUser,
    checkAuth: store.checkAuth
  }
}
