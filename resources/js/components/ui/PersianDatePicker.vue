<template>
  <div class="w-full">
    <label
      v-if="label"
      :for="inputId"
      :class="[
        'block text-sm font-medium mb-2',
        'text-[var(--theme-text-primary)]',
        { 'text-error': error }
      ]"
    >
      {{ label }}
      <span v-if="required" class="text-error">*</span>
    </label>

    <input
      :id="inputId"
      ref="dateInputRef"
      type="text"
      :placeholder="placeholder || 'روز / ماه / سال'"
      :disabled="disabled"
      :readonly="readonly || false"
      :required="required"
      :class="[
        'w-full transition-all duration-200',
        'bg-[var(--theme-bg-elevated)] border border-[var(--theme-border)] rounded-lg',
        'text-[var(--theme-text-primary)]',
        'px-4 py-2.5 text-sm',
        'focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500',
        'disabled:opacity-50 disabled:cursor-not-allowed',
        'readonly:bg-[var(--theme-bg-base)] readonly:cursor-default',
        'cursor-pointer',
        { 'border-error focus:ring-error focus:border-error': error }
      ]"
      @input="handleInput"
      @blur="handleBlur"
      @focus="handleFocus"
      @click="handleClick"
    />

    <p
      v-if="error"
      class="mt-1.5 text-xs text-error flex items-center gap-1"
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
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'

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
    default: 'روز / ماه / سال'
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
  id: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:modelValue', 'blur', 'focus'])

const dateInputRef = ref(null)
let datePickerInstance = null
const stableInputId = `persian-date-${Math.random().toString(36).slice(2, 11)}`

const persianDigitMap = {
  '۰': '0',
  '۱': '1',
  '۲': '2',
  '۳': '3',
  '۴': '4',
  '۵': '5',
  '۶': '6',
  '۷': '7',
  '۸': '8',
  '۹': '9'
}

const toEnglishDigits = (value) => {
  if (!value) return ''
  return value.toString().replace(/[۰-۹]/g, (digit) => persianDigitMap[digit] ?? digit)
}

const normalizeDateValue = (value) => {
  if (!value) return ''
  const sanitized = toEnglishDigits(value)
  // Accept both dash and slash separators, normalize to slash to match backend expectation
  return sanitized.replace(/-/g, '/').trim()
}

const formatUnixToString = (unixDate) => {
  if (typeof unixDate !== 'number' || Number.isNaN(unixDate)) {
    return ''
  }

  try {
    const PD = typeof window !== 'undefined' ? window.persianDate : null
    if (typeof PD === 'function') {
      const instance = new PD(unixDate)
      if (instance && typeof instance.format === 'function') {
        return toEnglishDigits(instance.format('YYYY/MM/DD'))
      }
    }
  } catch (error) {
    console.warn('Failed to format persian date:', error)
  }

  const date = new Date(unixDate)
  if (Number.isNaN(date.getTime())) {
    return ''
  }

  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}/${month}/${day}`
}

const inputId = computed(() => (props.id ? props.id : stableInputId))

const ensureJQuery = async () => {
  if (typeof window.jQuery !== 'undefined' && window.jQuery.fn) {
    return
  }
  const jq = (await import('jquery')).default
  window.jQuery = window.$ = jq
}

const initializeDatePicker = async () => {
  if (!dateInputRef.value) {
    return
  }

  await ensureJQuery()

  if (!window.jQuery.fn.pDatepicker) {
    await loadPersianDatePicker()
  }

  if (dateInputRef.value && typeof window.jQuery !== 'undefined' && window.jQuery.fn.pDatepicker) {
    try {
      const $el = window.jQuery(dateInputRef.value)

      // Destroy existing instance if any
      if (datePickerInstance) {
        try {
          if (typeof datePickerInstance.destroy === 'function') {
            datePickerInstance.destroy()
          } else if ($el.data('pDatepicker')) {
            $el.pDatepicker('destroy')
          }
        } catch (e) {
          // Ignore
        }
        datePickerInstance = null
      }

      const datePickerOptions = {
        calendarType: 'persian',
        format: 'YYYY/MM/DD',
        initialValue: !!props.modelValue,
        initialValueType: 'persian',
        autoClose: true,
        observer: true,
        inputDelay: 800,
        position: 'auto',
        toolbox: {
          enabled: true,
          todayButton: {
            enabled: true
          },
          submitButton: {
            enabled: true
          },
          calendarSwitch: {
            enabled: false
          }
        },
        navigator: {
          enabled: true,
          scroll: {
            enabled: true
          }
        },
        formatter: (unixDate) => {
          const formatted = formatUnixToString(unixDate)
          if (dateInputRef.value) {
            dateInputRef.value.value = formatted
          }
          return formatted
        },
        onSelect: (unixDate) => {
          const formatted = formatUnixToString(unixDate)
          if (formatted) {
            if (dateInputRef.value) {
              dateInputRef.value.value = formatted
            }
            emit('update:modelValue', formatted)
          }
        },
        onHide: () => {}
      }

      // Set initial value if provided
      if (props.modelValue) {
        const normalizedInitialValue = normalizeDateValue(props.modelValue)
        dateInputRef.value.value = normalizedInitialValue
      }

      // Initialize persian-datepicker
      $el.pDatepicker(datePickerOptions)

      // Get the instance from jQuery data
      datePickerInstance = $el.data('pDatepicker') || null

      if (dateInputRef.value) {
        dateInputRef.value.removeEventListener('change', handleDateChange)
        dateInputRef.value.addEventListener('change', handleDateChange)
        dateInputRef.value.value = normalizeDateValue(dateInputRef.value.value)
      }
    } catch (error) {
      console.error('Error initializing date picker:', error)
    }
  }
}

const PERSIAN_DATE_SRC = '/assets/vendor/persian-date/dist/persian-date.min.js'
const PERSIAN_DATEPICKER_CSS = '/assets/vendor/persian-datepicker/dist/css/persian-datepicker.min.css'
const PERSIAN_DATEPICKER_JS = '/assets/vendor/persian-datepicker/dist/js/persian-datepicker.min.js'

const findPersianDateScript = () =>
  Array.from(document.querySelectorAll('script[src*="persian-date"]')).find((el) => {
    const src = el.getAttribute('src') || ''
    return src.includes('persian-date') && !src.includes('persian-datepicker')
  })

const loadPersianDatePicker = () => {
  return new Promise((resolve, reject) => {
    ensureJQuery()
      .then(() => {
        if (window.jQuery && window.jQuery.fn.pDatepicker) {
          resolve()
          return
        }

        return new Promise((persianDateResolve, persianDateReject) => {
          const existingPersianDate = findPersianDateScript()
          if (existingPersianDate) {
            if (typeof window.persianDate === 'function') {
              persianDateResolve()
              return
            }
            existingPersianDate.addEventListener('load', persianDateResolve)
            existingPersianDate.addEventListener('error', persianDateReject)
            return
          }

          const persianDateScript = document.createElement('script')
          persianDateScript.src = PERSIAN_DATE_SRC
          persianDateScript.onload = persianDateResolve
          persianDateScript.onerror = persianDateReject
          document.body.appendChild(persianDateScript)
        })
      })
      .then(() => {
        if (window.jQuery && window.jQuery.fn.pDatepicker) {
          resolve()
          return
        }

        if (!document.querySelector(`link[href*="persian-datepicker"]`)) {
          const link = document.createElement('link')
          link.rel = 'stylesheet'
          link.href = PERSIAN_DATEPICKER_CSS
          document.head.appendChild(link)
        }

        const existingScript = document.querySelector(`script[src*="persian-datepicker"]`)
        if (existingScript) {
          let settled = false
          const finish = () => {
            if (settled) return
            settled = true
            if (window.jQuery && window.jQuery.fn.pDatepicker) {
              resolve()
            } else {
              reject(new Error('Persian datepicker failed to load'))
            }
          }
          if (window.jQuery && window.jQuery.fn.pDatepicker) {
            finish()
            return
          }
          existingScript.addEventListener('load', () => setTimeout(finish, 50))
          existingScript.addEventListener('error', reject)
          queueMicrotask(() => {
            if (window.jQuery && window.jQuery.fn.pDatepicker) {
              finish()
            }
          })
          return
        }

        const script = document.createElement('script')
        script.src = PERSIAN_DATEPICKER_JS
        script.onload = () => {
          setTimeout(() => {
            if (window.jQuery && window.jQuery.fn.pDatepicker) {
              resolve()
            } else {
              reject(new Error('Persian datepicker failed to load'))
            }
          }, 100)
        }
        script.onerror = reject
        document.body.appendChild(script)
      })
      .catch(reject)
  })
}

const handleDateChange = (event) => {
  const value = normalizeDateValue(event.target.value)
  if (event.target.value !== value) {
    event.target.value = value
  }
  emit('update:modelValue', value)
}

const handleInput = (event) => {
  const value = normalizeDateValue(event.target.value)
  if (event.target.value !== value) {
    event.target.value = value
  }
  emit('update:modelValue', value)
}

const handleBlur = (event) => {
  emit('blur', event)
}

const handleClick = () => {
  if (!dateInputRef.value || props.disabled || props.readonly) {
    return
  }

  if (datePickerInstance && typeof datePickerInstance.show === 'function') {
    setTimeout(() => datePickerInstance.show(), 10)
    return
  }

  if (window.jQuery && window.jQuery.fn.pDatepicker) {
    const $el = window.jQuery(dateInputRef.value)
    if ($el.data('pDatepicker')) {
      setTimeout(() => $el.pDatepicker('show'), 10)
    } else {
      setTimeout(() => initializeDatePicker(), 50)
    }
  }
}

const handleFocus = (event) => {
  emit('focus', event)
  // Ensure picker opens on focus (only if not disabled or readonly)
  if (dateInputRef.value && !props.disabled && !props.readonly) {
    if (datePickerInstance && typeof datePickerInstance.show === 'function') {
      setTimeout(() => {
        datePickerInstance.show()
      }, 50)
    } else if (window.jQuery && window.jQuery.fn.pDatepicker && dateInputRef.value) {
      const $el = window.jQuery(dateInputRef.value)
      if ($el.data('pDatepicker')) {
        setTimeout(() => {
          $el.pDatepicker('show')
        }, 50)
      }
    }
  }
}

watch(
  () => props.modelValue,
  async (newVal) => {
  await nextTick()
  const normalizedValue = normalizeDateValue(newVal)

  if (dateInputRef.value && normalizedValue !== dateInputRef.value.value) {
    const oldValue = dateInputRef.value.value
    dateInputRef.value.value = normalizedValue || ''

    // Update date picker value if initialized
    // Since observer is enabled, it should watch for input changes
    // But we can also manually update if needed
    if (window.jQuery && window.jQuery.fn.pDatepicker && dateInputRef.value) {
      const $el = window.jQuery(dateInputRef.value)
      if ($el.data('pDatepicker') && normalizedValue) {
        try {
          // Try to set the date - format should be YYYY/MM/DD
          // The observer option will also watch for manual input changes
          $el.pDatepicker('setDate', normalizedValue)
        } catch (e) {
          // If setDate fails, the observer should handle the value change
          // or we can trigger change event
          if (normalizedValue && normalizedValue !== oldValue) {
            dateInputRef.value.dispatchEvent(new Event('input', { bubbles: true }))
          }
        }
      }
    }
  }
  },
  { flush: 'post' }
)

onMounted(async () => {
  await nextTick()

  // Check if we're inside a modal (common modal class or parent check)
  const isInModal = dateInputRef.value?.closest('[role="dialog"], .modal, [class*="modal"], [class*="Modal"]') !== null

  // Wait longer if in a modal to allow modal animation to complete
  const delay = isInModal ? 400 : 100

  setTimeout(async () => {
    // Retry initialization if it fails the first time
    let retries = 3
    let initialized = false

    while (retries > 0 && !initialized) {
      try {
        await initializeDatePicker()
        // Verify initialization was successful
        if (dateInputRef.value && window.jQuery && window.jQuery.fn.pDatepicker) {
          // Check if the input has been initialized by persian-datepicker
          const $el = window.jQuery(dateInputRef.value)
          if ($el && $el.data('pDatepicker')) {
            initialized = true
            datePickerInstance = $el.data('pDatepicker')
          } else if (datePickerInstance) {
            initialized = true
          }
        }

        if (initialized) break
      } catch (error) {
        console.warn('Date picker initialization attempt failed:', error)
      }

      retries--
      if (!initialized && retries > 0) {
        // Wait before retrying
        await new Promise(resolve => setTimeout(resolve, 200))
      }
    }

    if (!initialized) {
      console.error('Failed to initialize date picker after retries')
    }
  }, delay)
})

onUnmounted(() => {
  if (dateInputRef.value) {
    dateInputRef.value.removeEventListener('change', handleDateChange)
  }

  // Clean up date picker instance
  if (datePickerInstance || (window.jQuery && dateInputRef.value)) {
    try {
      if (window.jQuery && dateInputRef.value) {
        const $el = window.jQuery(dateInputRef.value)
        if ($el.data('pDatepicker')) {
          $el.pDatepicker('destroy')
        }
      } else if (datePickerInstance && typeof datePickerInstance.destroy === 'function') {
        datePickerInstance.destroy()
      }
    } catch (e) {
      // Ignore destroy errors
      console.warn('Date picker cleanup warning:', e)
    }
    datePickerInstance = null
  }

})
</script>

<style scoped>
.text-error {
  color: var(--color-error, #EF4444);
}
</style>

