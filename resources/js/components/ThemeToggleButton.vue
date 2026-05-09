<template>
  <button
    type="button"
    @click="toggleTheme"
    :class="[
      'relative flex items-center justify-center rounded-full transition-all duration-200 ease-out focus:outline-none focus-visible:ring-2',
      baseClasses
    ]"
    aria-label="Toggle theme"
  >
    <svg
      v-if="currentTheme === 'dark'"
      class="w-5 h-5"
      fill="none"
      stroke="currentColor"
      viewBox="0 0 24 24"
    >
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
        d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
      />
    </svg>
    <svg
      v-else
      class="w-5 h-5"
      fill="none"
      stroke="currentColor"
      viewBox="0 0 24 24"
    >
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
        d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
      />
    </svg>
  </button>
</template>

<script setup>
import { computed } from 'vue'
import { useTheme } from '../composables/useTheme'

const props = defineProps({
  size: {
    type: String,
    default: 'md'
  },
  variant: {
    type: String,
    default: 'glass'
  }
})

const sizeClasses = {
  sm: 'w-9 h-9 p-2 text-sm',
  md: 'w-11 h-11 p-3 text-base',
  lg: 'w-12 h-12 p-3 text-lg'
}

const variantClasses = {
  glass: 'text-[var(--theme-text-secondary)] hover:text-primary-400 hover:bg-[var(--theme-bg-glass)] focus-visible:ring-primary-400/70',
  solid: 'bg-gradient-to-r from-primary-500 to-secondary-500 text-[var(--theme-text-inverse)] hover:from-primary-400 hover:to-secondary-400 focus-visible:ring-secondary-400/70',
  subtle: 'bg-[var(--theme-bg-elevated)]/70 text-[var(--theme-text-secondary)] hover:bg-[var(--theme-bg-elevated)] focus-visible:ring-primary-400/60'
}

const { currentTheme, toggleTheme } = useTheme()

const baseClasses = computed(() => {
  const size = sizeClasses[props.size] || sizeClasses.md
  const variant = variantClasses[props.variant] || variantClasses.glass
  return `${size} ${variant}`
})
</script>


