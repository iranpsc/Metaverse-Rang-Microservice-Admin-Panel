export const SUPER_ADMIN_ROLE = 'super-admin'

/**
 * Merge parent menu/route access constraints with the current item.
 */
export function mergeAccess(parentAccess = {}, item = {}) {
  return {
    roles: [...new Set([...(parentAccess.roles || []), ...(item.roles || [])])],
    permissions: [...new Set([...(parentAccess.permissions || []), ...(item.permissions || [])])]
  }
}

/**
 * Check whether a user can access a resource described by roles/permissions.
 */
export function canAccess(access = {}, user) {
  if (!user) return false

  const userRoles = user.roles || []
  const userPermissions = user.permissions || []

  if (userRoles.includes(SUPER_ADMIN_ROLE)) return true

  const roles = access.roles || []
  const permissions = access.permissions || []

  if (!roles.length && !permissions.length) return true

  const hasRole = !roles.length || roles.some((role) => userRoles.includes(role))
  const hasPermission = !permissions.length || permissions.some((permission) => userPermissions.includes(permission))

  if (roles.length && permissions.length) return hasRole || hasPermission
  if (roles.length) return hasRole
  return hasPermission
}

export function canAccessMenuItem(item, user, parentAccess = { roles: [], permissions: [] }) {
  return canAccess(mergeAccess(parentAccess, item), user)
}

export function hasValidAuthToken() {
  const token = localStorage.getItem('admin_token')
  const expiresAt = localStorage.getItem('admin_token_expires_at')
  const authenticated = localStorage.getItem('admin_authenticated') === 'true'

  if (!token || !authenticated) return false
  if (!expiresAt) return false

  return new Date(expiresAt) >= new Date()
}

export function clearAuthStorage() {
  localStorage.removeItem('admin_authenticated')
  localStorage.removeItem('admin_token')
  localStorage.removeItem('admin_token_expires_at')
  localStorage.removeItem('admin_user_data')
}
