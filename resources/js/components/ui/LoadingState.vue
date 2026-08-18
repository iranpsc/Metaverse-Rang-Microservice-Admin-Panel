<template>
  <div
    :class="[
      'flex items-center justify-center',
      minHeightClass,
      containerClass
    ]"
  >
    <div :class="['text-center', contentClass]">
      <slot name="spinner">
        <div
          :class="[
            'inline-block animate-spin rounded-full border-t-2 border-b-2',
            spinnerClasses
          ]"
        ></div>
      </slot>
      <p
        v-if="message"
        :class="[
          'mt-4 text-[var(--theme-text-secondary)]',
          messageClass
        ]"
      >
        {{ message }}
      </p>
      <slot />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  message: {
    type: String,
    default: 'در حال بارگذاری...'
  },
  size: {
    type: String,
    default: 'lg',
    validator: (value) => ['sm', 'md', 'lg', 'xl'].includes(value)
  },
  minHeight: {
    type: String,
    default: '400px',
    validator: (value) => {
      // Accept standard Tailwind classes or custom values
      return true
    }
  },
  containerClass: {
    type: String,
    default: ''
  },
  contentClass: {
    type: String,
    default: ''
  },
  messageClass: {
    type: String,
    default: ''
  },
  spinnerColor: {
    type: String,
    default: 'primary'
  }
})

const minHeightClass = computed(() => {
  // If it's a Tailwind class, use it directly, otherwise use custom style
  if (props.minHeight.includes('[') || props.minHeight.includes('-')) {
    return `min-h-[${props.minHeight}]`
  }
  return `min-h-[${props.minHeight}]`
})

const spinnerClasses = computed(() => {
  const sizes = {
    sm: 'h-6 w-6',
    md: 'h-8 w-8',
    lg: 'h-12 w-12',
    xl: 'h-16 w-16'
  }
  
  const colors = {
    primary: 'border-[var(--theme-primary)]',
    secondary: 'border-[var(--theme-secondary)]',
    white: 'border-white',
    gray: 'border-gray-400'
  }
  
  return `${sizes[props.size]} ${colors[props.spinnerColor] || colors.primary}`
})
</script>

<style scoped>
/* Additional styles if needed */
</style>

