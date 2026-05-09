import { defineStore } from 'pinia'

export const useToastStore = defineStore('toast', {
  state: () => ({
    toasts: [],
    toastId: 0
  }),
  actions: {
    showToast(message, type = 'info', duration = 3000) {
      const id = this.toastId++
      this.toasts.push({ id, message, type, visible: true })
      if (duration > 0) {
        setTimeout(() => this.removeToast(id), duration)
      }
      return id
    },
    removeToast(id) {
      const index = this.toasts.findIndex((t) => t.id === id)
      if (index !== -1) {
        this.toasts[index].visible = false
        setTimeout(() => {
          const activeIndex = this.toasts.findIndex((t) => t.id === id)
          if (activeIndex !== -1) this.toasts.splice(activeIndex, 1)
        }, 300)
      }
    },
    clearToasts() {
      this.toasts = []
    }
  }
})
