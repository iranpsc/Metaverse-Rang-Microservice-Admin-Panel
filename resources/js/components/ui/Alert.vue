<template>
  <Transition
    enter-active-class="transition-all duration-300 ease-out"
    enter-from-class="opacity-0 translate-y-2"
    enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition-all duration-200 ease-in"
    leave-from-class="opacity-100 translate-y-0"
    leave-to-class="opacity-0 translate-y-2"
  >
    <div
      v-if="modelValue"
      :class="[
        'flex items-start gap-3 p-4 rounded-lg border',
        variantClasses,
        { 'cursor-pointer': dismissible }
      ]"
      @click="handleDismiss"
    >
      <!-- Icon -->
      <div :class="iconClasses">
        <component v-if="icon" :is="icon" class="w-5 h-5" />
        <component v-else :is="defaultIcon" class="w-5 h-5" />
      </div>

      <!-- Content -->
      <div class="flex-1 min-w-0">
        <h4 v-if="title" :class="['font-semibold mb-1', titleClasses]">
          {{ title }}
        </h4>
        <p :class="messageClasses">
          <slot>{{ message }}</slot>
        </p>
      </div>

      <!-- Dismiss Button -->
      <button
        v-if="dismissible"
        @click.stop="handleDismiss"
        class="flex-shrink-0 p-1 rounded-lg hover:bg-black/5 dark:hover:bg-white/5 transition-colors focus:outline-none"
        aria-label="Dismiss"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M6 18L18 6M6 6l12 12"
          />
        </svg>
      </button>
    </div>
  </Transition>
</template>

<script setup>
import { computed, h } from 'vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: true
  },
  variant: {
    type: String,
    default: 'info',
    validator: (value) => ['success', 'warning', 'danger', 'info'].includes(value)
  },
  title: {
    type: String,
    default: ''
  },
  message: {
    type: String,
    default: ''
  },
  dismissible: {
    type: Boolean,
    default: false
  },
  icon: {
    type: [String, Object],
    default: null
  }
})

const emit = defineEmits(['update:modelValue', 'dismiss'])

const variantClasses = computed(() => {
  const variants = {
    success: 'bg-success/10 border-success/30 text-success',
    warning: 'bg-warning/10 border-warning/30 text-warning',
    danger: 'bg-error/10 border-error/30 text-error',
    info: 'bg-info/10 border-info/30 text-info'
  }
  return variants[props.variant]
})

const iconClasses = computed(() => {
  return 'flex-shrink-0'
})

const titleClasses = computed(() => {
  const variants = {
    success: 'text-success',
    warning: 'text-warning',
    danger: 'text-error',
    info: 'text-info'
  }
  return variants[props.variant]
})

const messageClasses = computed(() => {
  const variants = {
    success: 'text-success/90',
    warning: 'text-warning/90',
    danger: 'text-error/90',
    info: 'text-info/90'
  }
  return variants[props.variant]
})

const defaultIcon = computed(() => {
  const icons = {
    success: () => h('svg', {
      class: 'w-5 h-5',
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
      })
    ]),
    warning: () => h('svg', {
      class: 'w-5 h-5',
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'
      })
    ]),
    danger: () => h('svg', {
      class: 'w-5 h-5',
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'
      })
    ]),
    info: () => h('svg', {
      class: 'w-5 h-5',
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
      })
    ])
  }
  return icons[props.variant] || icons.info
})

const handleDismiss = () => {
  if (props.dismissible) {
    emit('update:modelValue', false)
    emit('dismiss')
  }
}
</script>

<style scoped>
/* Additional styles can be added if needed */
</style>

