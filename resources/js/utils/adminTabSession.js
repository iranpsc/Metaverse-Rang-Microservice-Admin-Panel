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
let resolveVisibility = null
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
}

const reportConflict = (activeId = getActiveTabId()) => {
  if (hasClaimedTab && document.visibilityState === 'visible') {
    window.dispatchEvent(new CustomEvent('admin-tab-conflict', { detail: { tabId: activeId } }))
  }
}

const claimInitialTab = () => {
  if (initializationPromise) return initializationPromise

  const ready = document.visibilityState === 'visible'
    ? Promise.resolve()
    : new Promise((resolve) => { resolveVisibility = resolve })

  const pendingClaim = ready.then(async () => {
    if (initializationPromise !== pendingClaim) return
    await claimHandler()
    if (initializationPromise === pendingClaim) completeClaim()
  }).catch((error) => {
    if (initializationPromise === pendingClaim) initializationPromise = null
    throw error
  })

  initializationPromise = pendingClaim
  return pendingClaim
}

export const getAdminTabId = () => getOrCreateTabId()

export const isAdminTabActive = () => ownsTab && getActiveTabId() === getOrCreateTabId()

export const initializeAdminTabSession = (claim) => {
  if (initialized) return claimInitialTab()

  initialized = true
  claimHandler = claim

  const currentTabId = getOrCreateTabId()
  const handlePeerClaim = (announcedId) => {
    // Messages can arrive out of order. Prefer the latest shared owner and
    // keep announcements from overwriting it.
    let latestId = announcedId
    try {
      latestId = localStorage.getItem(ACTIVE_TAB_STORAGE_KEY) || announcedId
    } catch {
      // BroadcastChannel also works when storage is unavailable.
    }

    activeTabId = latestId
    if (!ownsTab || !latestId || latestId === currentTabId) return

    ownsTab = false
    reportConflict(latestId)
  }

  try {
    channel = new BroadcastChannel(CHANNEL_NAME)
    channel.addEventListener('message', (event) => {
      if (event.data?.type === 'claimed' && event.data.tabId !== currentTabId) {
        handlePeerClaim(event.data.tabId)
      }
    })
  } catch {
    channel = null
  }

  storageHandler = (event) => {
    if (event.key === ACTIVE_TAB_STORAGE_KEY && event.newValue) {
      handlePeerClaim(event.newValue)
    }
  }
  window.addEventListener('storage', storageHandler)

  visibilityHandler = () => {
    if (document.visibilityState !== 'visible') return

    if (resolveVisibility) {
      resolveVisibility()
      resolveVisibility = null
      return
    }

    if (!isAdminTabActive()) reportConflict()
  }
  document.addEventListener('visibilitychange', visibilityHandler)

  // A tab that has just been opened and is visible takes ownership. Existing
  // tabs learn about the change through BroadcastChannel/storage events.
  return claimInitialTab()
}

export const activateCurrentAdminTab = async () => {
  if (!claimHandler) return

  await claimHandler()
  completeClaim()
}

export const markAdminTabInactive = () => {
  ownsTab = false
  reportConflict()
}

export const resetAdminTabSession = () => {
  initialized = false
  ownsTab = false
  hasClaimedTab = false
  initializationPromise = null
  resolveVisibility?.()
  resolveVisibility = null
  activeTabId = null
  claimHandler = null
  if (storageHandler) window.removeEventListener('storage', storageHandler)
  if (visibilityHandler) document.removeEventListener('visibilitychange', visibilityHandler)
  storageHandler = null
  visibilityHandler = null
  channel?.close()
  channel = null
}
