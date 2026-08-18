<template>
  <div
    :class="[
      'inline-flex items-center justify-center',
      'overflow-hidden',
      sizeClasses,
      roundedClasses,
      { 'ring-2 ring-primary-500': ring },
      { 'ring-2 ring-offset-2 ring-offset-bg-base': ring }
    ]"
    :style="gradientStyle"
  >
    <img
      v-if="src"
      :src="src"
      :alt="alt"
      class="w-full h-full object-cover"
      @error="handleImageError"
    />
    <span
      v-else-if="fallback"
      :class="['font-semibold', textSizeClasses]"
    >
      {{ fallback }}
    </span>
    <span
      v-else-if="initials"
      :class="['font-semibold', textSizeClasses]"
    >
      {{ initials }}
    </span>
    <div
      v-else
      class="w-full h-full bg-gradient-to-br from-primary-400 to-secondary-400 flex items-center justify-center"
    >
      <svg
        class="w-1/2 h-1/2 text-white"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
        />
      </svg>
    </div>

    <!-- Status Indicator -->
    <div
      v-if="status"
      :class="[
        'absolute bottom-0 right-0 rounded-full border-2 border-bg-elevated',
        statusClasses,
        statusSizeClasses
      ]"
    />
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  src: {
    type: String,
    default: ''
  },
  alt: {
    type: String,
    default: 'Avatar'
  },
  fallback: {
    type: String,
    default: ''
  },
  initials: {
    type: String,
    default: ''
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl', '2xl'].includes(value)
  },
  rounded: {
    type: String,
    default: 'full',
    validator: (value) => ['none', 'sm', 'md', 'lg', 'full'].includes(value)
  },
  status: {
    type: String,
    default: '',
    validator: (value) => ['', 'online', 'offline', 'away', 'busy'].includes(value)
  },
  ring: {
    type: Boolean,
    default: false
  },
  gradient: {
    type: Boolean,
    default: false
  }
})

const imageError = ref(false)

const sizeClasses = computed(() => {
  const sizes = {
    xs: 'w-6 h-6',
    sm: 'w-8 h-8',
    md: 'w-10 h-10',
    lg: 'w-12 h-12',
    xl: 'w-16 h-16',
    '2xl': 'w-24 h-24'
  }
  return `${sizes[props.size]} relative`
})

const roundedClasses = computed(() => {
  const rounded = {
    none: 'rounded-none',
    sm: 'rounded-sm',
    md: 'rounded-md',
    lg: 'rounded-lg',
    full: 'rounded-full'
  }
  return rounded[props.rounded]
})

const textSizeClasses = computed(() => {
  const sizes = {
    xs: 'text-xs',
    sm: 'text-sm',
    md: 'text-base',
    lg: 'text-lg',
    xl: 'text-xl',
    '2xl': 'text-2xl'
  }
  return sizes[props.size]
})

const statusClasses = computed(() => {
  const statuses = {
    online: 'bg-success',
    offline: 'bg-text-muted',
    away: 'bg-warning',
    busy: 'bg-error'
  }
  return statuses[props.status] || ''
})

const statusSizeClasses = computed(() => {
  const sizes = {
    xs: 'w-1.5 h-1.5',
    sm: 'w-2 h-2',
    md: 'w-2.5 h-2.5',
    lg: 'w-3 h-3',
    xl: 'w-4 h-4',
    '2xl': 'w-5 h-5'
  }
  return sizes[props.size]
})

const gradientStyle = computed(() => {
  if (props.gradient && !props.src && !imageError.value) {
    return {
      background: 'linear-gradient(135deg, var(--color-primary-400, #A78BFA) 0%, var(--color-secondary-400, #22D3EE) 100%)'
    }
  }
  return {}
})

const handleImageError = () => {
  imageError.value = true
}

watch(
  () => props.src,
  () => {
    imageError.value = false
  }
)
</script>

<style scoped>
.ring-offset-bg-base {
  --tw-ring-offset-color: var(--theme-bg-base, #0F172A);
}
</style>

