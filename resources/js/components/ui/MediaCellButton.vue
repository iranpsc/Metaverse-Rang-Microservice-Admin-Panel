<template>
  <Button
    v-if="url"
    :type="buttonType"
    variant="glass"
    size="sm"
    rounded="full"
    class="!p-2 !gap-0 min-w-[2.25rem]"
    :title="title"
    :aria-label="title"
    @click="$emit('open', url)"
  >
    <template #icon-left>
      <TableActionIcon name="image" />
    </template>
  </Button>
  <span v-else class="text-sm text-[var(--theme-text-muted)]">-</span>
</template>

<script setup>
import Button from './Button.vue'
import TableActionIcon from '../icons/TableActionIcon.vue'

defineProps({
  url: {
    type: String,
    default: ''
  },
  title: {
    type: String,
    default: 'مشاهده رسانه'
  },
  buttonType: {
    type: String,
    default: 'button'
  }
})

defineEmits(['open'])
</script>
