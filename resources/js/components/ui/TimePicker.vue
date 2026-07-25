<template>
  <div class="w-full">
    <label
      v-if="label"
      :for="inputId"
      :class="[
        'block text-sm font-medium mb-2 text-[var(--theme-text-primary)]',
        { 'text-error': error }
      ]"
    >
      {{ label }}
      <span v-if="required" class="text-error">*</span>
    </label>

    <flat-pickr
      :id="inputId"
      v-model="internalValue"
      :config="flatpickrConfig"
      :placeholder="placeholder"
      :disabled="disabled"
      :class="inputClasses"
      @on-open="handleOpen"
      @on-close="handleClose"
    />

    <p v-if="internalValue && !helperText && !error" class="mt-1 text-xs text-[var(--theme-text-secondary)]">
      {{ internalValue }}
    </p>

    <p v-if="helperText && !error" class="mt-1.5 text-xs text-[var(--theme-text-secondary)]">
      {{ helperText }}
    </p>
    <p v-if="error" class="mt-1.5 text-xs text-error flex items-center gap-1">
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
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  label: {
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
  required: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  mode: {
    type: [String, Number],
    default: 24
  },
  minuteStep: {
    type: Number,
    default: 5
  },
  placeholder: {
    type: String,
    default: 'انتخاب ساعت'
  },
  id: {
    type: String,
    default: ''
  }
})

defineOptions({
  components: {
    FlatPickr
  }
})

const emit = defineEmits(['update:modelValue', 'focus', 'blur'])

const inputId = computed(() => props.id || `timepicker-${Math.random().toString(36).slice(2, 10)}`)
const normalizedMode = computed(() => (Number(props.mode) === 12 ? 12 : 24))
const minuteStep = computed(() => Math.min(60, Math.max(1, Number(props.minuteStep) || 1)))
const internalValue = ref(normalizeValue(props.modelValue))

const flatpickrConfig = computed(() => ({
  enableTime: true,
  noCalendar: true,
  dateFormat: 'H:i',
  altInput: normalizedMode.value === 12,
  altFormat: 'h:i K',
  time_24hr: normalizedMode.value === 24,
  minuteIncrement: minuteStep.value,
  allowInput: true
}))

const inputClasses = computed(() => [
  'time-picker-input',
  'w-full rounded-lg border transition-all duration-200',
  'px-4 py-2.5 text-sm',
  'bg-[var(--theme-bg-elevated)] border-[var(--theme-border)] text-[var(--theme-text-primary)]',
  'placeholder:text-[var(--theme-text-muted)]',
  'focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500',
  'disabled:opacity-60 disabled:cursor-not-allowed',
  { 'border-error focus:ring-error focus:border-error': props.error }
])

watch(
  () => props.modelValue,
  (value) => {
    const normalized = normalizeValue(value)
    if (normalized !== internalValue.value) {
      internalValue.value = normalized
    }
  }
)

watch(internalValue, (value) => {
  if (value !== props.modelValue) {
    emit('update:modelValue', value)
  }
})

function normalizeValue(value) {
  if (!value || typeof value !== 'string') {
    return ''
  }
  const [h, m] = value.split(':').map((part) => Number.parseInt(part, 10))
  if (Number.isNaN(h) || Number.isNaN(m)) {
    return '00:00'
  }
  const hour = clamp(h, 0, 23)
  const minute = clamp(m, 0, 59)
  return `${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`
}

function clamp(value, min, max) {
  return Math.min(Math.max(value, min), max)
}

function handleOpen() {
  emit('focus', { type: 'focus' })
}

function handleClose() {
  emit('blur', { type: 'blur' })
}
</script>

<style scoped>
.text-error {
  color: var(--color-error, #EF4444);
}

.border-error {
  border-color: var(--color-error, #EF4444) !important;
}

:deep(.flatpickr-calendar) {
  background: rgba(15, 23, 42, 0.94);
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 24px 48px rgba(15, 23, 42, 0.45);
  color: var(--theme-text-primary);
}

:deep(.flatpickr-time) {
  border: none;
  padding-inline: 1rem;
}

:deep(.flatpickr-time .numInputWrapper),
:deep(.flatpickr-time .flatpickr-am-pm) {
  background: rgba(124, 58, 237, 0.12);
  border-radius: 10px;
  border: 1px solid rgba(124, 58, 237, 0.25);
  color: var(--theme-text-primary);
}

:deep(.flatpickr-time input) {
  color: var(--theme-text-primary);
}

:deep(.flatpickr-time input:focus) {
  outline: none;
}

:deep(.flatpickr-alt-input) {
  width: 100%;
  border-radius: 12px;
  border: 1px solid var(--theme-border);
  background: linear-gradient(135deg, rgba(124, 58, 237, 0.08), rgba(6, 182, 212, 0.08));
  color: var(--theme-text-primary);
  padding: 0.6rem 1rem;
  font-size: 0.95rem;
  font-weight: 500;
  letter-spacing: 0.04em;
}

:deep(.flatpickr-alt-input:focus) {
  border-color: rgba(124, 58, 237, 0.65);
  box-shadow: 0 0 0 2px rgba(124, 58, 237, 0.35);
  outline: none;
}
</style>

