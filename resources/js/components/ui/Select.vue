<template>
  <div class="w-full">
    <!-- Label -->
    <label
      v-if="label"
      :for="selectId"
      :class="[
        'block text-sm font-medium mb-2 text-text-primary',
        { 'text-error': error }
      ]"
    >
      {{ label }}
      <span v-if="required" class="text-error">*</span>
    </label>

    <!-- Select Wrapper -->
    <div class="relative">
      <!-- Select Field -->
      <select
        :id="selectId"
        :value="modelValue"
        :disabled="disabled"
        :required="required"
        :class="[
          'w-full appearance-none transition-all duration-200',
          'bg-bg-elevated border border-border rounded-lg',
          'text-text-primary',
          'focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500',
          'disabled:opacity-50 disabled:cursor-not-allowed',
          sizeClasses,
          paddingClasses,
          { 'border-error focus:ring-error focus:border-error': error }
        ]"
        @change="handleChange"
        @blur="handleBlur"
        @focus="handleFocus"
      >
        <option
          v-if="placeholder"
          value=""
          disabled
          class="text-text-muted"
        >
          {{ placeholder }}
        </option>
        <option
          v-for="option in options"
          :key="getOptionValue(option)"
          :value="getOptionValue(option)"
          class="bg-bg-elevated text-text-primary"
        >
          {{ getOptionLabel(option) }}
        </option>
      </select>

      <!-- Dropdown Arrow Icon -->
      <div
        class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none text-text-secondary"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M19 9l-7 7-7-7"
          />
        </svg>
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
import { computed } from 'vue'

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
    default: 'Select an option'
  },
  options: {
    type: Array,
    required: true
  },
  optionValue: {
    type: String,
    default: 'value'
  },
  optionLabel: {
    type: String,
    default: 'label'
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
  }
})

const emit = defineEmits(['update:modelValue', 'change', 'blur', 'focus'])

const selectId = computed(() => props.id || `select-${Math.random().toString(36).substr(2, 9)}`)

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
    sm: 'px-3 py-2 pr-10',
    md: 'px-4 py-2.5 pr-10',
    lg: 'px-5 py-3 pr-10'
  }
  return padding[props.size]
})

const getOptionValue = (option) => {
  if (typeof option === 'string' || typeof option === 'number') {
    return option
  }
  return option[props.optionValue]
}

const getOptionLabel = (option) => {
  if (typeof option === 'string' || typeof option === 'number') {
    return option
  }
  return option[props.optionLabel]
}

const handleChange = (event) => {
  emit('update:modelValue', event.target.value)
  emit('change', event.target.value)
}

const handleBlur = (event) => {
  emit('blur', event)
}

const handleFocus = (event) => {
  emit('focus', event)
}
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

.text-text-muted {
  color: var(--theme-text-muted, #64748B);
}

.text-error {
  color: var(--color-error, #EF4444);
}
</style>

