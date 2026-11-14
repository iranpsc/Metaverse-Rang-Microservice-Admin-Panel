const ACTIVE_TAB_STORAGE_KEY = 'admin_active_tab_id'
const CHANNEL_NAME = 'admin-tab-session'

let tabId = null
let initialized = false
let ownsTab = false
let hasClaimedTab = false
let initializationPromise = null
let claimHandler = null
let channel = null
let storageHandler = null
let visibilityHandler = null
let initialResolve = null
let initialReject = null
let initialClaimStarted = false
let activeTabId = null

const getOrCreateTabId = () => {
  if (tabId) return tabId

  try {
    if (typeof crypto?.randomUUID === 'function') {
      tabId = crypto.randomUUID()
    } else if (typeof crypto?.getRandomValues === 'function') {
      const bytes = new Uint8Array(16)
      crypto.getRandomValues(bytes)
      bytes[6] = (bytes[6] & 0x0f) | 0x40
      bytes[8] = (bytes[8] & 0x3f) | 0x80
      tabId = [...bytes]
        .map((byte, index) => `${byte.toString(16).padStart(2, '0')}${[3, 5, 7, 9].includes(index) ? '-' : ''}`)
        .join('')
    } else {
      throw new Error('Secure random number generation is unavailable')
    }
  } catch {
    tabId = null
  }

  if (!tabId) {
    throw new Error('Unable to create a secure admin tab identifier')
  }

  return tabId
}

const getActiveTabId = () => {
  try {
    return localStorage.getItem(ACTIVE_TAB_STORAGE_KEY) || activeTabId
  } catch {
    return activeTabId
  }
}

const setActiveTabId = (activeId) => {
  activeTabId = activeId || null

  try {
    if (activeId) {
      localStorage.setItem(ACTIVE_TAB_STORAGE_KEY, activeId)
    } else {
      localStorage.removeItem(ACTIVE_TAB_STORAGE_KEY)
    }
  } catch {
    // Storage can be unavailable in restricted browser contexts.
  }
}

const announceActiveTab = () => {
  const activeId = getOrCreateTabId()
  setActiveTabId(activeId)
  channel?.postMessage({ type: 'claimed', tabId: activeId })
  window.dispatchEvent(new CustomEvent('admin-tab-claimed', { detail: { tabId: activeId } }))
}

const completeClaim = () => {
  hasClaimedTab = true
  ownsTab = true
  announceActiveTab()
  initialResolve?.()
  initialResolve = null
  initialReject = null
}

const markInactive = (activeId = getActiveTabId()) => {
  ownsTab = activeId === getOrCreateTabId()

  if (!ownsTab && document.visibilityState === 'visible') {
    window.dispatchEvent(new CustomEvent('admin-tab-conflict', { detail: { tabId: activeId } }))
  }
}

const claimInitialTab = () => {
  if (initialClaimStarted) return initializationPromise || Promise.resolve()

  initialClaimStarted = true
  const claimPromise = Promise.resolve(claimHandler())
    .then(completeClaim)
    .catch((error) => {
      const isTabConflict =
        error?.response?.status === 409 &&
        error?.response?.data?.code === 'admin_tab_conflict'
      const isWaitingForVisibility = Boolean(initialResolve)

      initialClaimStarted = false
      initialReject?.(error)
      initialResolve = null
      initialReject = null

      // A tab that lost the initial race must still be able to submit the
      // explicit activation request. Its normal API calls remain protected by
      // the server-side tab check and will receive 409 until activation.
      if (isTabConflict && !isWaitingForVisibility) {
        initializationPromise = Promise.resolve()
      }

      throw error
    })

  if (!initialResolve) initializationPromise = claimPromise

  return initializationPromise
}

export const getAdminTabId = () => getOrCreateTabId()

export const isAdminTabActive = () => ownsTab && getActiveTabId() === getOrCreateTabId()

export const initializeAdminTabSession = (claim) => {
  if (initialized) return initializationPromise || Promise.resolve()

  initialized = true
  claimHandler = claim

  const currentTabId = getOrCreateTabId()

  try {
    channel = new BroadcastChannel(CHANNEL_NAME)
    channel.addEventListener('message', (event) => {
      if (event.data?.type === 'claimed' && event.data.tabId !== currentTabId) {
        setActiveTabId(event.data.tabId)
        markInactive(event.data.tabId)
      }
    })
  } catch {
    channel = null
  }

  storageHandler = (event) => {
    if (event.key === ACTIVE_TAB_STORAGE_KEY && event.newValue !== currentTabId) {
      markInactive(event.newValue)
    }
  }
  window.addEventListener('storage', storageHandler)

  visibilityHandler = () => {
    if (document.visibilityState !== 'visible') return

    if (!hasClaimedTab) {
      claimInitialTab().catch(() => {})
      return
    }

    if (!isAdminTabActive()) {
      window.dispatchEvent(new CustomEvent('admin-tab-conflict', {
        detail: { tabId: getActiveTabId() }
      }))
    }
  }
  document.addEventListener('visibilitychange', visibilityHandler)

  // A tab that has just been opened and is visible takes ownership. Existing
  // tabs learn about the change through BroadcastChannel/storage events.
  if (document.visibilityState === 'visible') {
    initializationPromise = claimInitialTab()
  } else {
    initializationPromise = new Promise((resolve, reject) => {
      initialResolve = resolve
      initialReject = reject
    })
  }

  return initializationPromise
}

export const activateCurrentAdminTab = async () => {
  if (!claimHandler) return

  await claimHandler()
  completeClaim()
  initialClaimStarted = false
  initializationPromise = Promise.resolve()
}

export const markAdminTabInactive = (activeId) => {
  const currentActiveId = activeId || getActiveTabId()
  if (currentActiveId) setActiveTabId(currentActiveId)
  ownsTab = false
  window.dispatchEvent(new CustomEvent('admin-tab-conflict', {
    detail: { tabId: currentActiveId }
  }))
}

export const waitForAdminTabSession = () => initializationPromise || Promise.resolve()

export const resetAdminTabSession = () => {
  initialized = false
  ownsTab = false
  hasClaimedTab = false
  initializationPromise = null
  initialResolve = null
  initialReject = null
  initialClaimStarted = false
  activeTabId = null
  claimHandler = null
  if (storageHandler) window.removeEventListener('storage', storageHandler)
  if (visibilityHandler) document.removeEventListener('visibilitychange', visibilityHandler)
  storageHandler = null
  visibilityHandler = null
  channel?.close()
  channel = null
}
