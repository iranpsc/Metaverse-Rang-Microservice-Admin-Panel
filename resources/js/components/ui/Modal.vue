<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition-opacity duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="modelValue"
        class="fixed inset-0 flex items-center justify-center p-4"
        :style="{ zIndex }"
        @click.self="handleBackdropClick"
      >
        <!-- Backdrop -->
        <div
          class="absolute inset-0 bg-black/60 backdrop-blur-sm"
          @click="handleBackdropClick"
        />

        <!-- Modal Container -->
        <Transition
          enter-active-class="transition-all duration-300 ease-out"
          enter-from-class="opacity-0 scale-95 -translate-y-4"
          enter-to-class="opacity-100 scale-100 translate-y-0"
          leave-active-class="transition-all duration-200 ease-in"
          leave-from-class="opacity-100 scale-100 translate-y-0"
          leave-to-class="opacity-0 scale-95 -translate-y-4"
          @after-enter="handleOpened"
          @after-leave="handleClosed"
        >
          <div
            v-if="modelValue"
            :class="[
              'relative z-10 w-full max-h-[90vh] rounded-xl',
              'bg-bg-elevated border border-border shadow-modal',
              'flex flex-col',
              sizeClasses
            ]"
            @click.stop
          >
            <!-- Header -->
            <div
              v-if="$slots.header || title"
              class="flex items-center justify-between p-6 border-b border-border flex-shrink-0"
              dir="rtl"
            >
              <div>
                <slot name="header">
                  <h3 v-if="title" class="text-xl font-semibold text-text-primary">
                    {{ title }}
                  </h3>
                  <p v-if="subtitle" class="text-sm text-text-secondary mt-1">
                    {{ subtitle }}
                  </p>
                </slot>
              </div>

              <!-- Close Button -->
              <button
                v-if="closable"
                @click="handleClose"
                class="p-1.5 rounded-lg text-text-secondary hover:text-text-primary hover:bg-white/5 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
                aria-label="Close modal"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
              </button>
            </div>

            <!-- Content -->
            <div class="p-6 overflow-y-auto flex-1 min-h-0">
              <slot />
            </div>

            <!-- Footer -->
            <div
              v-if="$slots.footer"
              class="flex items-center justify-end gap-3 p-6 border-t border-border flex-shrink-0"
            >
              <slot name="footer" />
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed, watch, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: ''
  },
  subtitle: {
    type: String,
    default: ''
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg', 'xl', 'full'].includes(value)
  },
  closable: {
    type: Boolean,
    default: true
  },
  closeOnBackdrop: {
    type: Boolean,
    default: false
  },
  closeOnEscape: {
    type: Boolean,
    default: true
  },
  zIndex: {
    type: Number,
    default: 50
  }
})

const emit = defineEmits(['update:modelValue', 'close', 'opened', 'closed'])

const sizeClasses = computed(() => {
  const sizes = {
    sm: 'max-w-sm',
    md: 'max-w-lg',
    lg: 'max-w-2xl',
    xl: 'max-w-4xl',
    full: 'max-w-full mx-4'
  }
  return sizes[props.size]
})

const handleClose = () => {
  emit('update:modelValue', false)
  emit('close')
}

const handleBackdropClick = () => {
  // Close when clicking the backdrop or outer container
  // The modal content has @click.stop which prevents clicks inside from reaching here
  if (props.closeOnBackdrop) {
    handleClose()
  }
}

const handleEscape = (event) => {
  if (event.key === 'Escape' && props.closeOnEscape && props.modelValue) {
    handleClose()
  }
}

const handleOpened = () => {
  emit('opened')
}

const handleClosed = () => {
  emit('closed')
}

watch(() => props.modelValue, (isOpen) => {
  if (isOpen) {
    document.body.style.overflow = 'hidden'
  } else {
    document.body.style.overflow = ''
  }
})

onMounted(() => {
  document.addEventListener('keydown', handleEscape)
  if (props.modelValue) {
    document.body.style.overflow = 'hidden'
  }
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleEscape)
  document.body.style.overflow = ''
})
</script>

<style scoped>
.bg-bg-elevated {
  background-color: var(--theme-bg-elevated, #1E293B);
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

.shadow-modal {
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}
</style>

