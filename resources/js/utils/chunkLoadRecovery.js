const CHUNK_RELOAD_PREFIX = 'vite-chunk-reload:'

export function isChunkLoadError(error) {
  if (!error) {
    return false
  }

  const message = String(error.message || error)
  return (
    error.name === 'ChunkLoadError'
    || message.includes('Failed to fetch dynamically imported module')
    || message.includes('Importing a module script failed')
    || message.includes('error loading dynamically imported module')
  )
}

export function reloadForStaleChunk(fullPath) {
  const key = `${CHUNK_RELOAD_PREFIX}${fullPath}`
  if (sessionStorage.getItem(key)) {
    sessionStorage.removeItem(key)
    return false
  }

  sessionStorage.setItem(key, '1')
  window.location.assign(fullPath)
  return true
}
