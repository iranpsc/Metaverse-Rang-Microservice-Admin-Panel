import menuConfig from '../router/menuConfig'
import { mergeAccess } from '../utils/authorization'

function flattenMenuAccess(menus, parentAccess = { roles: [], permissions: [] }, map = {}) {
  for (const item of menus) {
    const access = mergeAccess(parentAccess, item)

    if (item.route && item.route !== '#') {
      map[item.route] = access
    }

    if (item.children?.length) {
      flattenMenuAccess(item.children, access, map)
    }
  }

  return map
}

const levelAccess = {
  roles: ['level-management'],
  permissions: ['manage-level']
}

const translationAccess = {
  roles: ['access-management'],
  permissions: ['manage-access']
}

/** Route path -> { roles, permissions } derived from menu config plus nested pages. */
export const routeAccessMap = {
  ...flattenMenuAccess(menuConfig),
  '/profile': { roles: [], permissions: [] },
  '/levels': levelAccess,
  '/user-levels': levelAccess,
  '/calendar': { roles: ['calendar-management'], permissions: ['manage-calendar'] },
  '/challenge': { roles: ['level-management'], permissions: ['manage-level'] },
  '/isic-codes': translationAccess,
  '/translations': translationAccess
}

const levelChildPattern = /^\/levels\/[^/]+\/(prize|licenses|gift|general-info|gem)$/

export function resolveRouteAccess(path) {
  if (routeAccessMap[path]) {
    return routeAccessMap[path]
  }

  if (levelChildPattern.test(path)) {
    return levelAccess
  }

  if (path.startsWith('/translations/')) {
    return translationAccess
  }

  if (path.startsWith('/support/')) {
    const department = path.replace('/support/', '')
    const departmentPermissions = {
      citizens_safety: 'respond-to-citziens-safety-tickets',
      technical_support: 'respond-to-technical-support-tickets',
      investment: 'respond-to-investment-tickets',
      inspection: 'respond-to-inspection-tickets',
      protection: 'respond-to-protection-tickets',
      ztb: 'respond-to-ztb-management-tickets'
    }

    const permission = departmentPermissions[department]

    return {
      roles: ['support-management'],
      permissions: permission ? [permission] : []
    }
  }

  return null
}
