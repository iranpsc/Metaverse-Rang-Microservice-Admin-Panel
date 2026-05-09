<template>
  <div :class="['select2-wrapper w-full', wrapperClass]">
    <label
      v-if="label"
      :for="id"
      class="mb-2 block text-sm font-medium text-[var(--theme-text-secondary)]"
    >
      {{ label }}
      <span v-if="required" class="text-error">*</span>
    </label>
    <select
      :id="id"
      ref="selectRef"
      class="w-full"
      :disabled="disabled"
    >
      <option v-if="placeholder" value="">{{ placeholder }}</option>
      <option
        v-for="option in options"
        :key="option.value ?? option"
        :value="option.value ?? option"
      >
        {{ option.label ?? option }}
      </option>
    </select>
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
    <p v-else-if="helperText" class="mt-2 text-xs text-[var(--theme-text-secondary)]">
      {{ helperText }}
    </p>
  </div>
</template>

<script setup>
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import 'select2/dist/css/select2.min.css'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  options: {
    type: Array,
    default: () => []
  },
  placeholder: {
    type: String,
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  },
  wrapperClass: {
    type: String,
    default: ''
  },
  allowClear: {
    type: Boolean,
    default: true
  },
  id: {
    type: String,
    default: () => `select2-${Math.random().toString(36).slice(2)}`
  },
  label: {
    type: String,
    default: ''
  },
  required: {
    type: Boolean,
    default: false
  },
  error: {
    type: String,
    default: ''
  },
  helperText: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:modelValue', 'change'])

const selectRef = ref(null)
let initialized = false
let select2Loader

const ensureSelect2 = () => {
  if (!select2Loader) {
    select2Loader = (async () => {
      const { default: jQuery } = await import('jquery')
      window.$ = window.jQuery = jQuery
      const select2Module = await import('select2/dist/js/select2.full.min.js')
      if (typeof select2Module === 'function') {
        select2Module(window, jQuery)
      } else if (select2Module && typeof select2Module.default === 'function') {
        select2Module.default(window, jQuery)
      }
      if (typeof jQuery.fn.select2 !== 'function') {
        console.error('Select2 plugin failed to register on jQuery')
      }
      return jQuery
    })()
  }
  return select2Loader
}

const initSelect2 = async () => {
  if (!selectRef.value) return

  const jQuery = await ensureSelect2()
  const $el = jQuery(selectRef.value)

  if ($el.hasClass('select2-hidden-accessible')) {
    return
  }

  $el.select2({
    placeholder: props.placeholder,
    allowClear: props.allowClear,
    dir: 'rtl',
    width: '100%'
  })

  $el.on('change.select2Component', () => {
    const value = $el.val()
    emit('update:modelValue', value)
    emit('change', value)
  })

  if (props.modelValue !== undefined && props.modelValue !== null && props.modelValue !== '') {
    $el.val(props.modelValue).trigger('change.select2Component')
  } else if (props.placeholder) {
    $el.val('').trigger('change.select2Component')
  }

  initialized = true
}

const destroySelect2 = async () => {
  if (!initialized || !selectRef.value) return
  const jQuery = await ensureSelect2()
  const $el = jQuery(selectRef.value)
  $el.off('.select2Component')
  if ($el.hasClass('select2-hidden-accessible')) {
    $el.select2('destroy')
  }
  initialized = false
}

onMounted(() => {
  initSelect2().catch((error) => {
    console.error('Failed to initialise Select2', error)
  })
})

onBeforeUnmount(() => {
  destroySelect2().catch(() => {})
})

watch(
  () => props.options,
  async () => {
    if (!selectRef.value) return
    await destroySelect2()
    await nextTick()
    await initSelect2()
  },
  { deep: true }
)

watch(
  () => props.modelValue,
  async (value) => {
    if (!initialized || !selectRef.value) return
    const jQuery = await ensureSelect2()
    const $el = jQuery(selectRef.value)
    if ($el.val() !== value) {
      $el.val(value ?? '').trigger('change.select2Component')
    }
  }
)

watch(
  () => props.disabled,
  async (disabled) => {
    if (!selectRef.value) return
    const jQuery = await ensureSelect2()
    const $el = jQuery(selectRef.value)
    $el.prop('disabled', disabled)
  }
)
</script>

<style scoped>
:global(.select2-container) {
  width: 100% !important;
}

:global(.select2-container--default .select2-selection--single) {
  background-color: var(--theme-bg-elevated);
  border: 1px solid var(--theme-border);
  border-radius: 12px;
  height: 44px;
  padding: 6px 12px;
  display: flex;
  align-items: center;
  transition: border 0.2s ease, box-shadow 0.2s ease;
  box-shadow: inset 0 1px 3px rgba(15, 23, 42, 0.18);
}

:global(.select2-container--default .select2-selection--single:hover) {
  border-color: rgba(124, 58, 237, 0.45);
}

:global(.select2-container--default .select2-selection--single .select2-selection__rendered) {
  color: var(--theme-text-primary);
  line-height: 1.5;
  padding-right: 0;
  padding-left: 24px;
}

:global(.select2-container--default .select2-selection--single .select2-selection__placeholder) {
  color: var(--theme-text-muted);
}

:global(.select2-container--default .select2-selection--single .select2-selection__arrow) {
  right: auto;
  left: 12px;
}

:global(.select2-container--default .select2-selection--single .select2-selection__arrow b) {
  border-color: var(--theme-text-secondary) transparent transparent transparent;
}

:global(.select2-container--default.select2-container--open .select2-selection--single) {
  border-color: rgba(124, 58, 237, 0.7);
  box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.2);
}

:global(.select2-dropdown) {
  background-color: var(--theme-bg-elevated);
  border: 1px solid var(--theme-border);
  border-radius: 12px;
  box-shadow: 0 18px 40px rgba(15, 23, 42, 0.35);
  overflow: hidden;
}

:global(.select2-dropdown .select2-search__field) {
  border-radius: 10px;
  border: 1px solid var(--theme-border);
  background: var(--theme-bg-base);
  color: var(--theme-text-primary);
}

:global(.select2-results__option) {
  padding: 10px 16px;
  color: var(--theme-text-primary);
  transition: background 0.2s ease, color 0.2s ease;
}

:global(.select2-results__option--highlighted) {
  background: linear-gradient(135deg, rgba(124, 58, 237, 0.22), rgba(6, 182, 212, 0.22));
  color: var(--theme-text-primary);
}

:global([data-theme='light'] .select2-dropdown) {
  box-shadow: 0 18px 40px rgba(15, 23, 42, 0.18);
}

:global([data-theme='light'] .select2-container--default .select2-selection--single) {
  box-shadow: inset 0 1px 3px rgba(15, 23, 42, 0.08);
}

:global([data-theme='light'] .select2-results__option--highlighted) {
  background: linear-gradient(135deg, rgba(124, 58, 237, 0.16), rgba(6, 182, 212, 0.16));
}

:global([data-theme='dark'] .select2-container--default .select2-selection--single) {
  box-shadow: inset 0 1px 3px rgba(8, 12, 24, 0.4);
}

:global([data-theme='dark'] .select2-dropdown) {
  box-shadow: 0 18px 40px rgba(8, 12, 24, 0.55);
}

:global([data-theme='dark'] .select2-results__option--highlighted) {
  background: linear-gradient(135deg, rgba(124, 58, 237, 0.3), rgba(6, 182, 212, 0.3));
}

:global(.select2-container--default .select2-selection--single .select2-selection__clear) {
  color: var(--theme-text-secondary);
  margin-left: 8px;
  transition: color 0.2s ease;
}

:global(.select2-container--default .select2-selection--single .select2-selection__clear:hover) {
  color: rgba(239, 68, 68, 0.9);
}
</style>

