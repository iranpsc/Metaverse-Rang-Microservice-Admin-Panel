<template>
  <div :class="['relative', containerClass]">
    <input
      :id="inputId"
      type="text"
      :value="modelValue"
      :placeholder="placeholder"
      :disabled="disabled"
      :class="[
        'w-full px-4 py-2 pr-10 bg-[var(--theme-bg-glass)] border border-[var(--theme-border)] rounded-lg',
        'text-[var(--theme-text-primary)] placeholder-[var(--theme-text-muted)]',
        'focus:outline-none focus:ring-2 focus:ring-primary-400/50 focus:border-primary-400/50',
        'transition-all duration-200',
        'disabled:opacity-50 disabled:cursor-not-allowed',
        inputClass
      ]"
      @input="handleInput"
      @focus="handleFocus"
      @blur="handleBlur"
    />
    <!-- Search Icon -->
    <div
      v-if="showSearchIcon && !modelValue"
      class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"
      :class="iconClass"
    >
      <slot name="search-icon">
        <svg
          class="w-5 h-5 text-[var(--theme-text-muted)]"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
          />
        </svg>
      </slot>
    </div>
    <!-- Clear Button -->
    <button
      v-if="showClearButton && modelValue"
      @click="handleClear"
      :class="[
        'absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-[var(--theme-text-muted)] hover:text-[var(--theme-text-primary)] transition-colors focus:outline-none focus:ring-2 focus:ring-primary-400/50 rounded',
        clearButtonClass
      ]"
      :aria-label="clearButtonLabel"
      :disabled="disabled"
    >
      <slot name="clear-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M6 18L18 6M6 6l12 12"
          />
        </svg>
      </slot>
    </button>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { debounce } from 'lodash'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'جستجو...'
  },
  disabled: {
    type: Boolean,
    default: false
  },
  debounceMs: {
    type: Number,
    default: 500
  },
  showSearchIcon: {
    type: Boolean,
    default: true
  },
  showClearButton: {
    type: Boolean,
    default: true
  },
  clearButtonLabel: {
    type: String,
    default: 'Clear search'
  },
  containerClass: {
    type: String,
    default: ''
  },
  inputClass: {
    type: String,
    default: ''
  },
  iconClass: {
    type: String,
    default: ''
  },
  clearButtonClass: {
    type: String,
    default: ''
  },
  id: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:modelValue', 'search', 'clear', 'focus', 'blur'])

const inputId = computed(() => props.id || `searchbox-${Math.random().toString(36).substr(2, 9)}`)

// Debounced search function
const debouncedSearch = debounce((value) => {
  emit('search', value)
}, props.debounceMs)

const handleInput = (event) => {
  const value = event.target.value
  emit('update:modelValue', value)
  debouncedSearch(value)
}

const handleClear = () => {
  emit('update:modelValue', '')
  emit('clear')
  // Cancel any pending debounced search
  debouncedSearch.cancel()
  emit('search', '')
}

const handleFocus = (event) => {
  emit('focus', event)
}

const handleBlur = (event) => {
  emit('blur', event)
}
</script>

<style scoped>
/* Additional styles if needed */
</style>

