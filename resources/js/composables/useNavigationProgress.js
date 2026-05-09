import { ref, computed } from 'vue'

// Global state
const progress = ref(0)
const isVisible = ref(false)
let progressTimer = null
let finishTimer = null

// Progress control functions
const start = () => {
  isVisible.value = true
  progress.value = 0

  // Clear any existing timers
  if (progressTimer) clearInterval(progressTimer)
  if (finishTimer) clearTimeout(finishTimer)

  // Simulate progress
  progressTimer = setInterval(() => {
    if (progress.value < 90) {
      // Increment progress slowly
      const increment = Math.random() * 15
      progress.value = Math.min(progress.value + increment, 90)
    }
  }, 100)
}

const finish = () => {
  // Clear progress timer
  if (progressTimer) {
    clearInterval(progressTimer)
    progressTimer = null
  }

  // Complete the progress
  progress.value = 100

  // Hide after a short delay
  finishTimer = setTimeout(() => {
    isVisible.value = false
    // Reset after animation
    setTimeout(() => {
      progress.value = 0
    }, 300)
  }, 200)
}

const reset = () => {
  if (progressTimer) clearInterval(progressTimer)
  if (finishTimer) clearTimeout(finishTimer)
  isVisible.value = false
  progress.value = 0
}

// Export global progress controls (for router hooks)
export const navigationProgress = {
  start,
  finish,
  reset
}

// Export composable (for components)
export const useNavigationProgress = () => {
  return {
    progress: computed(() => progress.value),
    isVisible: computed(() => isVisible.value),
    start,
    finish,
    reset
  }
}
