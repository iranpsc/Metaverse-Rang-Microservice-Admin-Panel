<template>
  <div class="flex items-center gap-3">
    <input
      :id="checkboxId"
      :checked="modelValue"
      :disabled="disabled"
      :required="required"
      type="checkbox"
      :class="[
        'w-5 h-5 rounded border-2 transition-all duration-200',
        'focus:outline-none focus:ring-2 focus:ring-primary-500/50',
        'disabled:opacity-50 disabled:cursor-not-allowed',
        'cursor-pointer',
        checkboxClasses
      ]"
      @change="handleChange"
    />
    <label
      v-if="label"
      :for="checkboxId"
      :class="[
        'text-sm font-medium cursor-pointer select-none',
        labelClasses,
        { 'opacity-50 cursor-not-allowed': disabled }
      ]"
    >
      {{ label }}
      <span v-if="required" class="text-error">*</span>
    </label>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  label: {
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
  id: {
    type: String,
    default: ''
  },
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'success', 'warning', 'danger'].includes(value)
  }
})

const emit = defineEmits(['update:modelValue', 'change'])

const checkboxId = computed(() => props.id || `checkbox-${Math.random().toString(36).substr(2, 9)}`)

const checkboxClasses = computed(() => {
  const variants = {
    primary: 'border-primary-500 checked:bg-primary-500 checked:border-primary-500',
    secondary: 'border-secondary-500 checked:bg-secondary-500 checked:border-secondary-500',
    success: 'border-success checked:bg-success checked:border-success',
    warning: 'border-warning checked:bg-warning checked:border-warning',
    danger: 'border-error checked:bg-error checked:border-error'
  }
  return variants[props.variant]
})

const labelClasses = computed(() => {
  return 'text-text-primary'
})

const handleChange = (event) => {
  emit('update:modelValue', event.target.checked)
  emit('change', event.target.checked)
}
</script>

<style scoped>
input[type="checkbox"] {
  appearance: none;
  -webkit-appearance: none;
  background-color: var(--theme-bg-elevated, #1E293B);
  border-color: var(--theme-border, rgba(255, 255, 255, 0.1));
}

input[type="checkbox"]:checked {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='white'%3E%3Cpath fill-rule='evenodd' d='M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z' clip-rule='evenodd'/%3E%3C/svg%3E");
  background-size: contain;
  background-position: center;
  background-repeat: no-repeat;
}

.text-text-primary {
  color: var(--theme-text-primary, #F8FAFC);
}

.text-error {
  color: var(--color-error, #EF4444);
}
</style>

