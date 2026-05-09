<template>
  <div
    v-if="url"
    class="flex items-center justify-between rounded-lg border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)]/50 px-3 py-2 text-xs"
  >
    <span class="text-[var(--theme-text-secondary)]">{{ label }}:</span>
    <a
      :href="url"
      target="_blank"
      rel="noopener"
      class="inline-flex items-center text-primary-300 hover:text-primary-200"
      title="مشاهده فایل"
      aria-label="مشاهده فایل"
    >
      <TableActionIcon name="file" icon-class="w-4 h-4 shrink-0" />
    </a>
  </div>
</template>

<script setup>
import TableActionIcon from '../icons/TableActionIcon.vue'

defineProps({
  url: {
    type: String,
    default: null
  },
  label: {
    type: String,
    default: ''
  }
})
</script>
