<template>
  <span
    :class="[
      'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium',
      variantClasses,
      sizeClasses
    ]"
  >
    <component v-if="icon" :is="icon" class="w-3 h-3" />
    <slot />
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'primary', 'secondary', 'success', 'warning', 'danger', 'info', 'glass'].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  icon: {
    type: [String, Object],
    default: null
  }
})

const variantClasses = computed(() => {
  const variants = {
    default: 'bg-bg-elevated text-text-primary border border-border',
    primary: 'bg-primary-500/20 text-primary-400 border border-primary-500/30',
    secondary: 'bg-secondary-500/20 text-secondary-400 border border-secondary-500/30',
    success: 'bg-success/20 text-success border border-success/30',
    warning: 'bg-warning/20 text-warning border border-warning/30',
    danger: 'bg-error/20 text-error border border-error/30',
    info: 'bg-info/20 text-info border border-info/30',
    glass: 'bg-bg-glass text-text-primary border border-border backdrop-blur-md'
  }
  return variants[props.variant]
})

const sizeClasses = computed(() => {
  const sizes = {
    sm: 'text-xs px-2 py-0.5',
    md: 'text-xs px-2.5 py-1',
    lg: 'text-sm px-3 py-1.5'
  }
  return sizes[props.size]
})
</script>

<style scoped>
.bg-bg-elevated {
  background-color: var(--theme-bg-elevated, #1E293B);
}

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

