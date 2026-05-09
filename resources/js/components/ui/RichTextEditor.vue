<template>
  <div class="space-y-2" :dir="rtl ? 'rtl' : 'ltr'">
    <label
      v-if="label"
      :for="editorId"
      class="block text-sm font-medium text-[var(--theme-text-primary)]"
      :class="{ 'text-error': error }"
    >
      {{ label }}
      <span v-if="required" class="text-error">*</span>
    </label>

    <div
      :id="editorId"
      class="metaverse-ckeditor rounded-xl border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)]/80 overflow-hidden"
      :class="editorClass"
      :style="{ '--rte-min-height': minHeight }"
    >
      <Ckeditor
        :editor="ClassicEditor"
        :model-value="modelValue"
        :config="mergedConfig"
        @update:model-value="onUpdate"
        @ready="onReady"
      />
    </div>

    <p v-if="helperText && !error" class="text-xs text-[var(--theme-text-secondary)]">
      {{ helperText }}
    </p>
    <p v-if="error" class="text-xs text-error">
      {{ error }}
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Ckeditor } from '@ckeditor/ckeditor5-vue'
import ClassicEditor from '@ckeditor/ckeditor5-build-classic'
import '@ckeditor/ckeditor5-build-classic/build/translations/fa.js'

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
  error: {
    type: String,
    default: ''
  },
  helperText: {
    type: String,
    default: ''
  },
  required: {
    type: Boolean,
    default: false
  },
  minHeight: {
    type: String,
    default: '220px'
  },
  /** Merged into CKEditor {@link https://ckeditor.com/docs/ckeditor5/latest/api/module_core_editor_editorconfig-EditorConfig.html EditorConfig} */
  editorConfig: {
    type: Object,
    default: () => ({})
  },
  rtl: {
    type: Boolean,
    default: true
  },
  editorClass: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:modelValue', 'ready'])

const editorId = `rich-text-${Math.random().toString(36).slice(2, 9)}`

function normalizeEmpty(html) {
  if (!html || !String(html).trim()) return ''
  const compact = String(html).replace(/\s/g, '')
  if (
    compact === '<p></p>' ||
    compact === '<p><br></p>' ||
    compact === '<p>&nbsp;</p>' ||
    compact === '<p><br/></p>'
  ) {
    return ''
  }
  return html
}

const mergedConfig = computed(() => {
  const base = {
    placeholder: props.placeholder,
    contentsLangDirection: props.rtl ? 'rtl' : 'ltr',
    language: props.rtl ? 'fa' : 'en'
  }
  return { ...base, ...props.editorConfig }
})

function onUpdate(html) {
  emit('update:modelValue', normalizeEmpty(html))
}

function onReady(editor) {
  emit('ready', editor)
}
</script>

<style scoped>
.metaverse-ckeditor :deep(.ck.ck-editor) {
  border: none;
}

.metaverse-ckeditor :deep(.ck.ck-toolbar) {
  background: var(--theme-bg-glass, rgba(255, 255, 255, 0.05));
  border: none;
  border-bottom: 1px solid var(--theme-border, rgba(255, 255, 255, 0.1));
  border-radius: 12px 12px 0 0;
}

.metaverse-ckeditor :deep(.ck.ck-toolbar .ck-toolbar__separator) {
  background: var(--theme-border, rgba(255, 255, 255, 0.1));
}

.metaverse-ckeditor :deep(.ck.ck-button:not(.ck-disabled):hover),
.metaverse-ckeditor :deep(.ck.ck-button.ck-on) {
  background: var(--theme-bg-glass, rgba(255, 255, 255, 0.08));
}

.metaverse-ckeditor :deep(.ck.ck-button .ck-button__label),
.metaverse-ckeditor :deep(.ck.ck-dropdown__button .ck-button__label) {
  color: var(--theme-text-primary, #f8fafc);
}

.metaverse-ckeditor :deep(.ck.ck-icon) {
  color: var(--theme-text-primary, #f8fafc);
}

.metaverse-ckeditor :deep(.ck.ck-dropdown__panel) {
  background: var(--theme-bg-elevated, #1e293b);
  border-color: var(--theme-border, rgba(255, 255, 255, 0.1));
}

.metaverse-ckeditor :deep(.ck.ck-editor__main > .ck-editor__editable) {
  background: var(--theme-bg-base, #0f172a);
  border: none;
  border-radius: 0 0 12px 12px;
  color: var(--theme-text-primary, #f8fafc);
  font-size: 0.95rem;
  line-height: 1.8;
  min-height: var(--rte-min-height, 220px);
}

.metaverse-ckeditor :deep(.ck.ck-editor__editable.ck-focused) {
  box-shadow: none;
}

.metaverse-ckeditor :deep(.ck.ck-editor__editable a) {
  color: var(--theme-colors-secondary, #06b6d4);
}

.metaverse-ckeditor :deep(.ck.ck-placeholder:before) {
  color: var(--theme-text-muted, #64748b);
}

.text-error {
  color: var(--color-error, #ef4444);
}
</style>
