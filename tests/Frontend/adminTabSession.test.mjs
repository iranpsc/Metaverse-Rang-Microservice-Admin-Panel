import assert from 'node:assert/strict'
import { after, beforeEach, test } from 'node:test'
import { AxiosError } from 'axios'
import { createServer } from 'vite'

const activeTabKey = 'admin_active_tab_id'
const claimPath = '/session/tab/claim'
const stored = new Map()
const browserWindow = new EventTarget()
const browserDocument = Object.assign(new EventTarget(), {
  cookie: 'XSRF-TOKEN=test',
  visibilityState: 'visible'
})
const conflicts = []
browserWindow.addEventListener('admin-tab-conflict', (event) => conflicts.push(event.detail))

const vite = await createServer({
  configFile: false,
  appType: 'custom',
  optimizeDeps: { noDiscovery: true, include: [] },
  server: { middlewareMode: true, ws: false, watch: null }
})
const { default: api, ensureAdminTabSession } = await vite.ssrLoadModule('/resources/js/utils/api.ts')
const { activateCurrentAdminTab, getAdminTabId, isAdminTabActive, resetAdminTabSession } =
  await vite.ssrLoadModule('/resources/js/utils/adminTabSession.js')

Object.defineProperty(globalThis, 'localStorage', {
  configurable: true,
  value: {
    getItem: (key) => stored.get(key) ?? null,
    setItem: (key, value) => stored.set(key, value),
    removeItem: (key) => stored.delete(key)
  }
})
globalThis.window = browserWindow
globalThis.document = browserDocument

beforeEach(() => {
  resetAdminTabSession()
  stored.clear()
  stored.set('admin_token', 'test-token')
  conflicts.length = 0
  browserDocument.visibilityState = 'visible'
})

after(async () => {
  resetAdminTabSession()
  await vite.close()
})

const respond = (config) => ({ config, data: { success: true }, status: 200 })

test('startup /me waits for the shared claim even before the guard mounts', async () => {
  stored.set(activeTabKey, 'previous-document')
  const requests = []
  api.defaults.adapter = async (config) => {
    requests.push(config.url)
    assert.equal(config.headers.get('X-Admin-Tab-Id'), getAdminTabId())
    if (config.url === '/me') assert.equal(isAdminTabActive(), true)
    return respond(config)
  }

  await Promise.all([api.get('/me'), api.get('/dashboard'), ensureAdminTabSession()])

  assert.equal(requests[0], claimPath)
  assert.equal(requests.filter((url) => url === claimPath).length, 1)
  assert.deepEqual(conflicts, [])
})

test('a failed startup claim raises no tab conflict and a later request can retry', async () => {
  let claimAttempts = 0
  api.defaults.adapter = async (config) => {
    if (config.url === claimPath && ++claimAttempts === 1) {
      throw new AxiosError('Too many requests', undefined, config, undefined, {
        ...respond(config), status: 429
      })
    }
    return respond(config)
  }

  await assert.rejects(api.get('/me'))
  browserDocument.dispatchEvent(new Event('visibilitychange'))
  assert.deepEqual(conflicts, [])
  assert.equal(isAdminTabActive(), false)

  await api.get('/me')
  assert.equal(claimAttempts, 2)
  assert.equal(isAdminTabActive(), true)
})

test('a hidden tab waits for visibility and reports a peer takeover when revisited', async () => {
  let claimAttempts = 0
  api.defaults.adapter = async (config) => {
    if (config.url === claimPath) claimAttempts++
    return respond(config)
  }
  const peerClaim = (peerId) => browserWindow.dispatchEvent(Object.assign(new Event('storage'), {
    key: activeTabKey,
    newValue: peerId
  }))
  const showTab = () => {
    browserDocument.visibilityState = 'visible'
    browserDocument.dispatchEvent(new Event('visibilitychange'))
  }

  browserDocument.visibilityState = 'hidden'
  const startup = ensureAdminTabSession()
  assert.equal(claimAttempts, 0)
  showTab()
  await startup

  peerClaim('stale-announcement')
  assert.equal(isAdminTabActive(), true)
  assert.deepEqual(conflicts, [])

  browserDocument.visibilityState = 'hidden'
  stored.set(activeTabKey, 'other-tab')
  peerClaim('other-tab')
  assert.deepEqual(conflicts, [])
  showTab()
  assert.deepEqual(conflicts, [{ tabId: 'other-tab' }])

  await activateCurrentAdminTab()
  assert.equal(claimAttempts, 2)
  assert.equal(isAdminTabActive(), true)
  assert.equal(stored.get(activeTabKey), getAdminTabId())
})
