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
        :disabled="Boolean(option.disabled)"
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

  const syncModelFromDom = () => {
    const raw = $el.val()
    const value = raw == null || raw === '' ? '' : String(raw)
    if (value !== String(props.modelValue ?? '')) {
      emit('update:modelValue', value)
      emit('change', value)
    }
  }

  // Select2: ensure v-model updates on user selection (change + explicit select handlers).
  $el.on('change.select2Component', syncModelFromDom)
  $el.on('select2:select.select2Component', syncModelFromDom)
  $el.on('select2:clear.select2Component', syncModelFromDom)

  if (props.modelValue !== undefined && props.modelValue !== null && props.modelValue !== '') {
    $el.val(String(props.modelValue)).trigger('change')
  } else if (props.placeholder) {
    $el.val('').trigger('change')
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
    const nextStr = value == null || value === '' ? '' : String(value)
    const current = $el.val()
    const currentStr = current == null || current === '' ? '' : String(current)
    if (currentStr !== nextStr) {
      $el.val(nextStr === '' ? '' : nextStr).trigger('change')
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
