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
      :multiple="multiple"
    >
      <option v-if="placeholder && !remoteFetch" value="">{{ placeholder }}</option>
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
    type: [String, Number, Array],
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
  },
  remoteFetch: {
    type: Function,
    default: null
  },
  multiple: {
    type: Boolean,
    default: false
  },
  minimumInputLength: {
    type: Number,
    default: null
  },
  dropdownParent: {
    type: [String, Object],
    default: null
  }
})

const emit = defineEmits(['update:modelValue', 'change', 'option-select'])

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

const buildSelect2Config = () => {
  const config = {
    placeholder: props.placeholder,
    allowClear: props.allowClear && !props.multiple,
    dir: 'rtl',
    width: '100%',
    closeOnSelect: !props.multiple,
    language: {
      inputTooShort: () => 'برای جستجو تایپ کنید',
      searching: () => 'در حال جستجو...',
      noResults: () => 'نتیجه‌ای یافت نشد',
      loadingMore: () => 'در حال بارگذاری...'
    }
  }

  if (props.remoteFetch) {
    config.minimumInputLength = props.minimumInputLength ?? 0
    config.minimumResultsForSearch = 0
    config.ajax = {
      delay: 300,
      transport: (params, success, failure) => {
        const search = params.data?.search ?? ''
        const page = params.data?.page ?? 1

        props.remoteFetch({ search, page })
          .then((data) => success(data))
          .catch((error) => failure(error))
      },
      data: (params) => ({
        search: params.term ?? '',
        page: params.page ?? 1
      }),
      processResults: (data, params) => {
        params.page = params.page || 1

        return {
          results: (data.results || []).map((item) => ({
            id: String(item.value),
            text: item.label,
            disabled: Boolean(item.disabled)
          })),
          pagination: {
            more: Boolean(data.more)
          }
        }
      }
    }
  }

  return config
}

const resolveDropdownParent = (jQuery, $el) => {
  if (props.dropdownParent) {
    if (typeof props.dropdownParent === 'string') {
      const matched = jQuery(props.dropdownParent)
      if (matched.length) return matched
    } else if (props.dropdownParent instanceof HTMLElement) {
      return jQuery(props.dropdownParent)
    }
  }

  const modalPanel = $el.closest('.fixed.inset-0').find('> .relative').first()
  if (modalPanel.length) {
    return modalPanel
  }

  return jQuery(document.body)
}

const initSelect2 = async () => {
  if (!selectRef.value) return

  const jQuery = await ensureSelect2()
  const $el = jQuery(selectRef.value)

  if ($el.hasClass('select2-hidden-accessible')) {
    return
  }

  const config = buildSelect2Config()
  config.dropdownParent = resolveDropdownParent(jQuery, $el)

  $el.select2(config)

  const syncModelFromDom = () => {
    const raw = $el.val()

    if (props.multiple) {
      const value = Array.isArray(raw) ? raw.map(String) : (raw ? [String(raw)] : [])
      const current = Array.isArray(props.modelValue) ? props.modelValue.map(String) : []
      if (JSON.stringify(value) !== JSON.stringify(current)) {
        emit('update:modelValue', value)
        emit('change', value)
      }
      return
    }

    const value = raw == null || raw === '' ? '' : String(raw)
    if (value !== String(props.modelValue ?? '')) {
      emit('update:modelValue', value)
      emit('change', value)
    }
  }

  $el.on('change.select2Component', syncModelFromDom)
  $el.on('select2:select.select2Component', (event) => {
    syncModelFromDom()
    const selected = event.params?.data
    if (selected) {
      emit('option-select', {
        value: selected.id,
        label: selected.text,
        disabled: Boolean(selected.disabled)
      })
    }
  })
  $el.on('select2:clear.select2Component', syncModelFromDom)

  if (!props.remoteFetch) {
    if (props.multiple) {
      const values = Array.isArray(props.modelValue) ? props.modelValue.map(String) : []
      $el.val(values).trigger('change')
    } else if (props.modelValue !== undefined && props.modelValue !== null && props.modelValue !== '') {
      $el.val(String(props.modelValue)).trigger('change')
    } else if (props.placeholder) {
      $el.val('').trigger('change')
    }
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
    if (props.remoteFetch || !selectRef.value) return
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

    if (props.multiple) {
      const next = Array.isArray(value) ? value.map(String) : []
      const current = $el.val() || []
      const currentArr = Array.isArray(current) ? current.map(String) : []
      if (JSON.stringify(next) !== JSON.stringify(currentArr)) {
        $el.val(next).trigger('change')
      }
      return
    }

    if (props.remoteFetch) return

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
