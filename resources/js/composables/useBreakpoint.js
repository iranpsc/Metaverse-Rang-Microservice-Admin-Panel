import { computed, ref } from 'vue'

const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1024)
let listenerCount = 0
let cleanup = null

const updateWindowWidth = () => {
  if (typeof window === 'undefined') return
  windowWidth.value = window.innerWidth
}

const ensureListener = () => {
  if (typeof window === 'undefined') return
  if (!cleanup) {
    window.addEventListener('resize', updateWindowWidth)
    cleanup = () => window.removeEventListener('resize', updateWindowWidth)
  }
  listenerCount += 1
}

const releaseListener = () => {
  listenerCount = Math.max(0, listenerCount - 1)
  if (listenerCount === 0 && cleanup) {
    cleanup()
    cleanup = null
  }
}

export function useBreakpoint() {
  ensureListener()

  return {
    windowWidth,
    isMobile: computed(() => windowWidth.value < 768),
    isTablet: computed(() => windowWidth.value >= 768 && windowWidth.value < 1024),
    isDesktop: computed(() => windowWidth.value >= 1024),
    stopBreakpoint: releaseListener
  }
}
