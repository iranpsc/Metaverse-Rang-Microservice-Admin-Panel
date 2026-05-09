<template>
  <div
    :class="[
      'relative overflow-hidden transition-all duration-300 ease-in-out',
      roundedClasses,
      paddingClasses,
      variantClasses,
      { 'hover:shadow-glass': hoverGlow }
    ]"
  >
    <!-- Gradient Border (Optional) -->
    <div
      v-if="gradientBorder"
      class="absolute inset-0 rounded-inherit bg-gradient-to-r from-primary-500 to-secondary-500 opacity-0 hover:opacity-20 transition-opacity duration-300 pointer-events-none"
    />

    <!-- Content -->
    <div class="relative z-10">
      <!-- Header -->
      <div v-if="$slots.header || title" class="mb-4">
        <slot name="header">
          <h3 v-if="title" class="text-lg font-semibold text-text-primary">
            {{ title }}
          </h3>
          <p v-if="subtitle" class="text-sm text-text-secondary mt-1">
            {{ subtitle }}
          </p>
        </slot>
      </div>

      <!-- Default Content -->
      <slot />

      <!-- Footer -->
      <div v-if="$slots.footer" class="mt-4 pt-4 border-t border-border">
        <slot name="footer" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  variant: {
    type: String,
    default: 'elevated',
    validator: (value) => ['elevated', 'glass', 'flat'].includes(value)
  },
  rounded: {
    type: String,
    default: 'lg',
    validator: (value) => ['none', 'sm', 'md', 'lg', 'xl'].includes(value)
  },
  padding: {
    type: String,
    default: 'md',
    validator: (value) => ['none', 'sm', 'md', 'lg', 'xl'].includes(value)
  },
  title: {
    type: String,
    default: ''
  },
  subtitle: {
    type: String,
    default: ''
  },
  gradientBorder: {
    type: Boolean,
    default: false
  },
  hoverGlow: {
    type: Boolean,
    default: true
  }
})

const roundedClasses = computed(() => {
  const rounded = {
    none: 'rounded-none',
    sm: 'rounded-sm',
    md: 'rounded-md',
    lg: 'rounded-lg',
    xl: 'rounded-xl'
  }
  return rounded[props.rounded]
})

const paddingClasses = computed(() => {
  const padding = {
    none: '',
    sm: 'p-3',
    md: 'p-4',
    lg: 'p-6',
    xl: 'p-8'
  }
  return padding[props.padding]
})

const variantClasses = computed(() => {
  const variants = {
    elevated: 'bg-bg-elevated border border-border shadow-lg',
    glass: 'bg-bg-glass backdrop-blur-md border border-border',
    flat: 'bg-bg-elevated border border-border'
  }
  return variants[props.variant]
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

.text-text-secondary {
  color: var(--theme-text-secondary, #CBD5E1);
}

.hover\:shadow-glass:hover {
  box-shadow: 0 0 30px rgba(124, 58, 237, 0.2);
}
</style>

