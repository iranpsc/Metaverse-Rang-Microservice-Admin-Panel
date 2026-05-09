import { storeToRefs } from 'pinia'
import { useThemeStore } from '../store/themeStore'

export const useTheme = () => {
  const store = useThemeStore()
  store.initializeTheme()
  const { currentTheme } = storeToRefs(store)

  return {
    currentTheme,
    toggleTheme: store.toggleTheme,
    setTheme: store.setTheme,
    initializeTheme: store.initializeTheme
  }
}

