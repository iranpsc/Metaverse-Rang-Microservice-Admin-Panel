<template>
  <div class="w-full">
    <!-- Input Wrapper -->
    <div class="relative">
      <!-- Digit Inputs Container -->
      <div
        :class="[
          'flex w-full max-w-full gap-4 items-center',
          'bg-bg-elevated',
          'transition-all duration-200',
          { 'disabled:opacity-50 disabled:cursor-not-allowed': disabled },
          paddingClasses,
          'input-container'
        ]"
        dir="ltr"
      >
        <!-- Individual Digit Input Boxes -->
        <input
          v-for="(digit, index) in digitInputs"
          :key="index"
          :ref="el => inputRefs[index] = el"
          :id="index === 0 ? inputId : undefined"
          type="text"
          :value="digit"
          :disabled="disabled || isInputDisabled(index)"
          :readonly="readonly"
          :required="required && index === 0"
          maxlength="1"
          inputmode="numeric"
          pattern="[0-9]*"
          :class="[
            'flex-1 h-12 text-center font-mono text-xl',
            'bg-transparent border-b outline-none',
            'text-text-primary',
            'transition-all duration-200',
            'input-bottom-border',
            'input-field',
            { 'input-border-error': error && index < currentLength },
            { 'input-border-primary': !error && (focusedInputIndex === index || digit) }
          ]"
          dir="ltr"
          @input="handleDigitInput(index, $event)"
          @keydown="handleKeyDown(index, $event)"
          @paste="handlePaste($event)"
          @focus="handleFocus($event, index)"
          @blur="handleBlur($event)"
        />
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
import { ref, computed, watch, nextTick } from 'vue'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
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
  id: {
    type: String,
    default: ''
  },
  maxLength: {
    type: Number,
    default: 6
  }
})

const emit = defineEmits(['update:modelValue', 'blur', 'focus', 'complete', 'submit'])

const inputId = computed(() => props.id || `masked-input-${Math.random().toString(36).substr(2, 9)}`)
const inputRefs = ref([])
const isFocused = ref(false)
const focusedInputIndex = ref(-1)

// Get current value as string and pad with empty strings to maxLength
const currentValue = computed(() => {
  const value = props.modelValue ? props.modelValue.toString() : ''
  // Return array of digits, padded with empty strings
  const digits = value.split('').slice(0, props.maxLength)
  while (digits.length < props.maxLength) {
    digits.push('')
  }
  return digits
})

const digitInputs = computed(() => {
  return currentValue.value
})

const currentLength = computed(() => {
  const value = props.modelValue ? props.modelValue.toString() : ''
  return value.length
})

// Check if an input should be disabled (if it already has a digit)
// Keep the last input enabled so users can clear digits
const isInputDisabled = (index) => {
  // Last input should always be enabled
  if (index === props.maxLength - 1) {
    return false
  }
  return digitInputs.value[index] !== '' && digitInputs.value[index] !== undefined
}

// Find the rightmost filled digit index
const findRightmostFilledIndex = () => {
  for (let i = props.maxLength - 1; i >= 0; i--) {
    if (digitInputs.value[i] && digitInputs.value[i] !== '') {
      return i
    }
  }
  return -1
}

const paddingClasses = computed(() => {
  const padding = {
    sm: 'px-3 py-2',
    md: 'px-4 py-2.5',
    lg: 'px-5 py-3'
  }
  return padding[props.size]
})

const updateValue = (digits) => {
  // Remove empty strings and join
  const value = digits.filter(d => d !== '').join('')
  emit('update:modelValue', value)

  // Emit complete and submit events when all digits are filled
  if (value.length === props.maxLength) {
    emit('complete', value)
    // Emit submit event to trigger automatic submission
    emit('submit', value)
  }
}

const handleDigitInput = (index, event) => {
  const input = event.target.value

  // Only allow digits
  if (input && !/^\d$/.test(input)) {
    event.target.value = digitInputs.value[index]
    return
  }

  // Prevent input if this field is already disabled (has a value)
  // Exception: allow editing the last input
  if (isInputDisabled(index) && index !== props.maxLength - 1) {
    event.target.value = digitInputs.value[index]
    return
  }

  const newDigits = [...digitInputs.value]
  newDigits[index] = input

  updateValue(newDigits)

  // Auto-focus next input if digit was entered
  if (input && index < props.maxLength - 1) {
    nextTick(() => {
      // Find the next enabled input
      for (let i = index + 1; i < props.maxLength; i++) {
        if (!isInputDisabled(i)) {
          if (inputRefs.value[i]) {
            inputRefs.value[i].focus()
          }
          break
        }
      }
    })
  }
}

const handleKeyDown = (index, event) => {
  const { key } = event

  // Handle backspace - always clear from right to left
  if (key === 'Backspace') {
    event.preventDefault()
    const rightmostIndex = findRightmostFilledIndex()

    if (rightmostIndex >= 0) {
      const newDigits = [...digitInputs.value]
      newDigits[rightmostIndex] = ''
      updateValue(newDigits)

      // Focus the next input after the cleared one (if any)
      const nextIndex = rightmostIndex < props.maxLength - 1 ? rightmostIndex : Math.max(0, rightmostIndex - 1)
      nextTick(() => {
        if (inputRefs.value[nextIndex]) {
          inputRefs.value[nextIndex].focus()
        }
      })
    }
    return
  }

  // Handle arrow keys - only allow navigation to enabled inputs
  if (key === 'ArrowLeft' && index > 0) {
    event.preventDefault()
    // Find the previous enabled input
    for (let i = index - 1; i >= 0; i--) {
      if (!isInputDisabled(i)) {
        inputRefs.value[i]?.focus()
        break
      }
    }
    return
  }

  if (key === 'ArrowRight' && index < props.maxLength - 1) {
    event.preventDefault()
    // Find the next enabled input
    for (let i = index + 1; i < props.maxLength; i++) {
      if (!isInputDisabled(i)) {
        inputRefs.value[i]?.focus()
        break
      }
    }
    return
  }

  // Handle Delete key - clear from right to left
  if (key === 'Delete') {
    event.preventDefault()
    const rightmostIndex = findRightmostFilledIndex()

    if (rightmostIndex >= 0) {
      const newDigits = [...digitInputs.value]
      newDigits[rightmostIndex] = ''
      updateValue(newDigits)
    }
    return
  }
}

const handlePaste = async (event) => {
  event.preventDefault()
  const pastedData = event.clipboardData.getData('text')
  const digits = pastedData.replace(/\D/g, '').slice(0, props.maxLength)

  const newDigits = Array(props.maxLength).fill('')
  for (let i = 0; i < digits.length && i < props.maxLength; i++) {
    newDigits[i] = digits[i]
  }

  updateValue(newDigits)

  // Focus the last filled input or next empty
  const focusIndex = Math.min(digits.length, props.maxLength - 1)
  await nextTick()
  if (inputRefs.value[focusIndex]) {
    inputRefs.value[focusIndex].focus()
  }
}

const handleFocus = (event, index) => {
  isFocused.value = true
  focusedInputIndex.value = index
  emit('focus', event)
}

const handleBlur = (event) => {
  // Check if focus moved to another input in the group
  const relatedTarget = event.relatedTarget
  if (relatedTarget && inputRefs.value.includes(relatedTarget)) {
    return // Still within the input group
  }

  isFocused.value = false
  focusedInputIndex.value = -1
  emit('blur', event)
}

// Focus the first input (leftmost)
const focusFirstInput = () => {
  let attempts = 0
  const maxAttempts = 5

  const tryFocus = () => {
    attempts++
    // Try to find the first enabled input (from left)
    for (let i = 0; i < props.maxLength; i++) {
      const input = inputRefs.value[i]
      if (input && !input.disabled && input.offsetParent !== null) {
        // Check if element is actually visible (offsetParent !== null)
        try {
          input.focus()
          // Verify focus was successful
          if (document.activeElement === input) {
            return true
          }
        } catch (e) {
          console.warn('Failed to focus input:', e)
        }
      }
    }

    // Retry if not successful and haven't exceeded max attempts
    if (attempts < maxAttempts) {
      setTimeout(() => {
        nextTick(() => {
          tryFocus()
        })
      }, 100)
    }
    return false
  }

  // Start the focus attempt with multiple ticks to ensure DOM is ready
  nextTick(() => {
    requestAnimationFrame(() => {
      nextTick(() => {
        tryFocus()
      })
    })
  })
}

// Expose methods to parent
defineExpose({
  focusFirstInput
})
</script>

<style scoped>
.bg-bg-elevated {
  background-color: var(--theme-bg-elevated, #1E293B);
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

.input-bottom-border {
  border-bottom-width: 1px;
  border-bottom-color: var(--theme-border, rgba(255, 255, 255, 0.1));
  border-top: none;
  border-left: none;
  border-right: none;
}

.input-border-primary {
  border-bottom-color: var(--color-primary, #7C3AED);
}

.input-border-error {
  border-bottom-color: var(--color-error, #EF4444);
}

.input-container {
  box-sizing: border-box;
  min-width: 0;
  overflow: hidden;
  direction: ltr;
}

.input-field {
  box-sizing: border-box;
  min-width: 0;
  flex-shrink: 1;
  direction: ltr;
  text-align: center;
}
</style>
