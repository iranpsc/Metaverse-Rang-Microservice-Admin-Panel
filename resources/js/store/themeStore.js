import { defineStore } from 'pinia'

const isBrowser = typeof window !== 'undefined'
const isDocument = typeof document !== 'undefined'

export const useThemeStore = defineStore('theme', {
  state: () => ({
    currentTheme: isBrowser ? window.localStorage.getItem('theme') || 'dark' : 'dark',
    initialized: false
  }),
  actions: {
    applyTheme(theme) {
      if (!isDocument) return
      document.documentElement.setAttribute('data-theme', theme)
      if (isBrowser) window.localStorage.setItem('theme', theme)
    },
    setTheme(theme) {
      this.currentTheme = theme
      this.applyTheme(theme)
    },
    toggleTheme() {
      this.setTheme(this.currentTheme === 'dark' ? 'light' : 'dark')
    },
    initializeTheme() {
      if (this.initialized) return
      this.applyTheme(this.currentTheme || 'dark')
      this.initialized = true
    }
  }
})
