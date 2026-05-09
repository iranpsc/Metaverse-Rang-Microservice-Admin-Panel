<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :class="[
      'relative inline-flex items-center justify-center gap-2',
      'font-medium transition-all duration-200 ease-in-out',
      'focus:outline-none focus:ring-2 focus:ring-offset-2',
      'disabled:opacity-50 disabled:cursor-not-allowed',
      sizeClasses,
      variantClasses,
      roundedClasses,
      { 'pointer-events-none': loading }
    ]"
    @click="handleClick"
  >
    <!-- Loading Spinner -->
    <svg
      v-if="loading"
      class="animate-spin h-4 w-4"
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle
        class="opacity-25"
        cx="12"
        cy="12"
        r="10"
        stroke="currentColor"
        stroke-width="4"
      />
      <path
        class="opacity-75"
        fill="currentColor"
        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
      />
    </svg>

    <!-- Icon (Left) -->
    <slot name="icon-left" />

    <!-- Content -->
    <span :class="{ 'opacity-0': loading }">
      <slot />
    </span>

    <!-- Icon (Right) -->
    <slot name="icon-right" />
  </button>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'danger', 'success', 'warning', 'outline', 'ghost', 'glass'].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value)
  },
  rounded: {
    type: String,
    default: 'md',
    validator: (value) => ['none', 'sm', 'md', 'lg', 'full'].includes(value)
  },
  type: {
    type: String,
    default: 'button'
  },
  disabled: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  fullWidth: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['click'])

const sizeClasses = computed(() => {
  const sizes = {
    xs: 'px-2.5 py-1.5 text-xs',
    sm: 'px-3 py-2 text-sm',
    md: 'px-4 py-2.5 text-sm',
    lg: 'px-5 py-3 text-base',
    xl: 'px-6 py-3.5 text-lg'
  }
  return sizes[props.size]
})

const roundedClasses = computed(() => {
  const rounded = {
    none: 'rounded-none',
    sm: 'rounded-sm',
    md: 'rounded-md',
    lg: 'rounded-lg',
    full: 'rounded-full'
  }
  return `${rounded[props.rounded]} ${props.fullWidth ? 'w-full' : ''}`
})

const variantClasses = computed(() => {
  const variants = {
    primary: 'bg-gradient-to-r from-primary-500 to-secondary-500 text-white hover:shadow-lg hover:shadow-primary-500/50 focus:ring-primary-500',
    secondary: 'bg-secondary-500 text-white hover:bg-secondary-600 focus:ring-secondary-500',
    danger: 'bg-error text-white hover:bg-red-600 focus:ring-red-500',
    success: 'bg-success text-white hover:bg-green-600 focus:ring-green-500',
    warning: 'bg-warning text-slate-900 hover:bg-yellow-500 focus:ring-yellow-500',
    outline: 'border-2 border-primary-500 text-primary-500 bg-transparent hover:bg-primary-500/10 dark:hover:bg-primary-500/20 focus:ring-primary-500',
    ghost: 'text-primary-500 bg-transparent hover:bg-primary-500/10 dark:hover:bg-primary-500/20 focus:ring-primary-500',
    glass: 'bg-bg-glass backdrop-blur-md border border-border text-text-primary hover:bg-white/10 focus:ring-primary-500'
  }
  return variants[props.variant]
})

const handleClick = (event) => {
  if (!props.disabled && !props.loading) {
    emit('click', event)
  }
}
</script>

<style scoped>
/* Additional glass effect for glass variant */
.bg-bg-glass {
  background-color: var(--theme-bg-glass, rgba(255, 255, 255, 0.05));
}

.border-border {
  border-color: var(--theme-border, rgba(255, 255, 255, 0.1));
}

.text-text-primary {
  color: var(--theme-text-primary, #F8FAFC);
}
</style>

