<template>
  <div class="w-full">
    <label
      v-if="label"
      :for="textareaId"
      :class="[
        'block mb-2 text-sm font-medium',
        error ? 'text-error' : 'text-[var(--theme-text-secondary)]'
      ]"
    >
      {{ label }}
      <span v-if="required" class="text-error">*</span>
    </label>
    <textarea
      :id="textareaId"
      :value="modelValue"
      :placeholder="placeholder"
      :rows="rows"
      :disabled="disabled"
      :maxlength="maxlength"
      class="w-full rounded-xl border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)] px-4 py-3 text-sm text-[var(--theme-text-primary)] shadow-inner shadow-black/20 transition-all duration-200 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500/40"
      :class="[{ 'border-error focus:border-error focus:ring-error/40': error }]"
      @input="emitInput"
    />
    <p v-if="helperText && !error" class="mt-2 text-xs text-[var(--theme-text-secondary)]">
      {{ helperText }}
    </p>
    <p v-if="error" class="mt-2 text-xs text-error flex items-center gap-1">
      <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
        <path
          fill-rule="evenodd"
          d="M18 10A8 8 0 11 2 10a8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
          clip-rule="evenodd"
        />
      </svg>
      {{ error }}
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: String,
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
  rows: {
    type: [Number, String],
    default: 4
  },
  required: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  maxlength: {
    type: [Number, String],
    default: null
  },
  id: {
    type: String,
    default: null
  }
})

const emit = defineEmits(['update:modelValue'])

const textareaId = computed(() => props.id || `textarea-${Math.random().toString(36).slice(2)}`)

const emitInput = (event) => {
  emit('update:modelValue', event.target.value)
}
</script>


