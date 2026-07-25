<template>
  <div class="w-full">
    <!-- Static Label (only shown when label is provided and placeholder is not provided) -->
    <label
      v-if="label && !placeholder"
      :for="inputId"
      :class="[
        'block text-sm font-medium mb-2',
        labelClasses,
        { 'text-error': error }
      ]"
    >
      {{ label }}
      <span v-if="required" class="text-error">*</span>
    </label>

    <!-- Input Wrapper -->
    <div class="relative">
      <!-- Floating Label (when placeholder is provided) -->
      <label
        v-if="placeholder"
        :for="inputId"
        :class="[
          'absolute pointer-events-none transition-all duration-300 ease-in-out z-10',
          'select-none',
          floatingLabelClasses,
          { 'text-error': error },
          { 'text-primary-500': isFocused && !error }
        ]"
      >
        {{ placeholder }}
        <span v-if="required" class="text-error">*</span>
      </label>

      <!-- Input Field -->
      <input
        :id="inputId"
        :type="type === 'password' && showPassword ? 'text' : type"
        :value="modelValue"
        :placeholder="placeholder ? '' : undefined"
        :disabled="disabled"
        :readonly="readonly"
        :required="required"
        :min="min"
        :max="max"
        :step="step"
        :inputmode="inputmode"
        :pattern="pattern"
        :class="[
          'w-full transition-all duration-200',
          'bg-bg-elevated border border-border rounded-lg',
          'text-text-primary',
          'focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500',
          'disabled:opacity-50 disabled:cursor-not-allowed',
          'readonly:bg-bg-base readonly:cursor-default',
          sizeClasses,
          paddingClasses,
          { 'border-error focus:ring-error focus:border-error': error },
          { 'pr-10': $slots.iconLeft || iconLeft },
          { 'pl-10': ($slots.iconRight || iconRight) || (type === 'password') },
          { 'pt-5': isFloating && placeholder }
        ]"
        @input="handleInput"
        @blur="handleBlur"
        @focus="handleFocus"
      />

      <!-- Icon Left (now positioned on right for RTL) -->
      <div
        v-if="$slots.iconLeft || iconLeft"
        class="absolute right-3 top-1/2 -translate-y-1/2 text-text-secondary pointer-events-none z-10 transition-all duration-300"
        :class="{ 'top-6': isFloating && placeholder }"
      >
        <slot name="iconLeft">
          <component v-if="iconLeft" :is="iconLeft" class="w-5 h-5" />
        </slot>
      </div>

      <!-- Right Icon (password toggle, etc.) -->
      <div
        v-if="($slots.iconRight || iconRight) && !(type === 'password')"
        class="absolute left-3 top-1/2 -translate-y-1/2 text-text-secondary cursor-pointer hover:text-text-primary transition-colors z-10"
        :class="{ 'top-6': isFloating && placeholder }"
        @click="handleIconClick"
      >
        <slot name="iconRight">
          <component v-if="iconRight" :is="iconRight" class="w-5 h-5" />
        </slot>
      </div>

      <!-- Password Toggle Button -->
      <div
        v-if="type === 'password'"
        class="absolute left-3 top-1/2 -translate-y-1/2 text-text-secondary hover:text-text-primary transition-colors z-10"
        :class="{ 'top-6': isFloating && placeholder }"
      >
        <button
          type="button"
          class="focus:outline-none focus:ring-2 focus:ring-primary-500/50 rounded p-1 transition-all"
          :disabled="disabled"
          :class="{ 'opacity-50 cursor-not-allowed': disabled }"
          @click.stop="togglePassword"
          :aria-label="showPassword ? 'Hide password' : 'Show password'"
        >
          <!-- Eye Off Icon (when password is visible) -->
          <svg
            v-if="showPassword"
            class="w-5 h-5"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.29 3.29m13.42 13.42l-3.29-3.29M3 3l18 18"
            />
          </svg>
          <!-- Eye Icon (when password is hidden) -->
          <svg
            v-else
            class="w-5 h-5"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
            />
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
            />
          </svg>
        </button>
      </div>
    </div>

    <!-- Helper Text / Error Message -->
    <div v-if="helperText || error" class="mt-1.5">
      <p
        v-if="helperText && !error"
        class="text-xs text-text-secondary"
      >
        {{ helperText }}
      </p>
      <p
        v-if="error"
        class="text-xs text-error flex items-center gap-1"
      >
        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
          <path
            fill-rule="evenodd"
            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
            clip-rule="evenodd"
          />
        </svg>
        {{ error }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, useSlots } from 'vue'

const slots = useSlots()

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  type: {
    type: String,
    default: 'text'
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: ''
  },
  helperText: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  },
  readonly: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  iconLeft: {
    type: [String, Object],
    default: null
  },
  iconRight: {
    type: [String, Object],
    default: null
  },
  id: {
    type: String,
    default: ''
  },
  min: {
    type: [String, Number],
    default: undefined
  },
  max: {
    type: [String, Number],
    default: undefined
  },
  step: {
    type: [String, Number],
    default: undefined
  },
  inputmode: {
    type: String,
    default: undefined
  },
  pattern: {
    type: String,
    default: undefined
  }
})

const emit = defineEmits(['update:modelValue', 'blur', 'focus', 'iconClick'])

const inputId = computed(() => props.id || `input-${Math.random().toString(36).substr(2, 9)}`)
const showPassword = ref(false)
const isFocused = ref(false)

// Check if label should be floating (when focused or has value)
const isFloating = computed(() => {
  if (!props.placeholder) return false
  return isFocused.value || (props.modelValue && props.modelValue.toString().length > 0)
})

const labelClasses = computed(() => {
  return 'text-text-primary'
})

const sizeClasses = computed(() => {
  const sizes = {
    sm: 'text-sm',
    md: 'text-sm',
    lg: 'text-base'
  }
  return sizes[props.size]
})

const paddingClasses = computed(() => {
  const padding = {
    sm: 'px-3 py-2',
    md: 'px-4 py-2.5',
    lg: 'px-5 py-3'
  }
  return padding[props.size]
})

const floatingLabelClasses = computed(() => {
  if (!props.placeholder) return ''

  const hasIconLeft = slots.iconLeft || props.iconLeft
  const hasIconRight = slots.iconRight || props.iconRight
  const hasPasswordToggle = props.type === 'password'

  if (isFloating.value) {
    // Floating position - far right on border (RTL support)
    // Positioned on the border, half above it, at the far right edge
    // Adjust right position to account for icons on the right side
    const rightPos = (hasIconLeft || hasIconRight || hasPasswordToggle) ? 'right-10' : 'right-0'
    return `top-0 ${rightPos} text-xs font-medium px-3 py-0.5 bg-bg-elevated -translate-y-1/2 rounded-tl-lg rounded-tr-lg`
  } else {
    // Initial position - inside input, aligned with placeholder text
    const positions = {
      sm: 'top-1/2 text-sm text-text-muted -translate-y-1/2',
      md: 'top-1/2 text-sm text-text-muted -translate-y-1/2',
      lg: 'top-1/2 text-base text-text-muted -translate-y-1/2'
    }
    // Adjust right position based on icon presence - matches input padding
    const rightOffset = (hasIconLeft || hasIconRight || hasPasswordToggle)
      ? (props.size === 'lg' ? 'right-12' : props.size === 'md' ? 'right-10' : 'right-10')
      : (props.size === 'lg' ? 'right-5' : props.size === 'md' ? 'right-4' : 'right-3')
    return `${positions[props.size]} ${rightOffset}`
  }
})

const handleInput = (event) => {
  emit('update:modelValue', event.target.value)
}

const handleBlur = (event) => {
  isFocused.value = false
  emit('blur', event)
}

const handleFocus = (event) => {
  isFocused.value = true
  emit('focus', event)
}

const handleIconClick = () => {
  emit('iconClick')
}

const togglePassword = () => {
  showPassword.value = !showPassword.value
}
</script>

<style scoped>
.bg-bg-elevated {
  background-color: var(--theme-bg-elevated, #1E293B);
}

.bg-bg-base {
  background-color: var(--theme-bg-base, #0F172A);
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

.text-text-muted {
  color: var(--theme-text-muted, #64748B);
}

.text-error {
  color: var(--color-error, #EF4444);
}
</style>

