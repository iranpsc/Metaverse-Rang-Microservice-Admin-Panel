<template>
  <div
    :class="[
      'rounded-xl border p-4',
      variantClasses,
      containerClass
    ]"
  >
    <div class="flex items-start gap-3">
      <!-- Error Icon -->
      <div :class="['flex-shrink-0', iconClass]">
        <slot name="icon">
          <svg
            v-if="variant === 'error'"
            class="w-5 h-5 text-red-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
          <svg
            v-else-if="variant === 'warning'"
            class="w-5 h-5 text-yellow-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
            />
          </svg>
          <svg
            v-else-if="variant === 'info'"
            class="w-5 h-5 text-blue-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
        </slot>
      </div>

      <!-- Error Content -->
      <div class="flex-1 min-w-0">
        <slot>
          <p
            v-if="title"
            :class="['font-semibold mb-1', titleClass || defaultTitleClass]"
          >
            {{ title }}
          </p>
          <p
            v-if="message"
            :class="['text-sm', messageClass || defaultMessageClass]"
          >
            {{ message }}
          </p>
          <div v-if="$slots.default" :class="['mt-2', contentClass]">
            <slot name="content" />
          </div>
        </slot>
      </div>

      <!-- Close Button (Optional) -->
      <button
        v-if="closable"
        @click="$emit('close')"
        :class="[
          'flex-shrink-0 p-1 rounded-md hover:bg-white/5 transition-colors',
          'focus:outline-none focus:ring-2 focus:ring-offset-2',
          closeButtonClass
        ]"
        aria-label="Close"
      >
        <svg
          class="w-4 h-4"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M6 18L18 6M6 6l12 12"
          />
        </svg>
      </button>
    </div>

    <!-- Action Button (Optional) -->
    <div
      v-if="showAction && $slots.action"
      :class="['mt-4', actionClass]"
    >
      <slot name="action" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  message: {
    type: String,
    default: ''
  },
  title: {
    type: String,
    default: ''
  },
  variant: {
    type: String,
    default: 'error',
    validator: (value) => ['error', 'warning', 'info'].includes(value)
  },
  closable: {
    type: Boolean,
    default: false
  },
  showAction: {
    type: Boolean,
    default: false
  },
  containerClass: {
    type: String,
    default: ''
  },
  iconClass: {
    type: String,
    default: ''
  },
  titleClass: {
    type: String,
    default: ''
  },
  messageClass: {
    type: String,
    default: ''
  },
  contentClass: {
    type: String,
    default: ''
  },
  actionClass: {
    type: String,
    default: ''
  },
  closeButtonClass: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['close'])

const variantClasses = computed(() => {
  const variants = {
    error: 'bg-red-500/10 border-red-500/20',
    warning: 'bg-yellow-500/10 border-yellow-500/20',
    info: 'bg-blue-500/10 border-blue-500/20'
  }
  return variants[props.variant] || variants.error
})

const defaultTitleClass = computed(() => {
  const variants = {
    error: 'text-red-400',
    warning: 'text-yellow-400',
    info: 'text-blue-400'
  }
  return variants[props.variant] || variants.error
})

const defaultMessageClass = computed(() => {
  const variants = {
    error: 'text-red-400',
    warning: 'text-yellow-400',
    info: 'text-blue-400'
  }
  return variants[props.variant] || variants.error
})
</script>

<style scoped>
/* Additional styles if needed */
</style>

