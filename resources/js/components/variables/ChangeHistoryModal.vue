<template>
  <Modal
    :model-value="modelValue"
    :title="title"
    size="xl"
    @update:model-value="emit('update:modelValue', $event)"
  >
    <div
      v-if="hasEntries"
      class="space-y-4 overflow-x-auto"
      dir="rtl"
    >
      <Table
        :columns="columns"
        :data="entries"
        :show-row-number="true"
        :empty-state-message="emptyTableMessage"
      >
        <template
          v-for="(_, slotName) in $slots"
          #[slotName]="slotProps"
        >
          <slot
            :name="slotName"
            v-bind="slotProps"
          />
        </template>
      </Table>
    </div>
    <div
      v-else
      class="py-8 text-center text-[var(--theme-text-secondary)]"
    >
      {{ emptyMessage }}
    </div>

    <template #footer>
      <div
        class="flex justify-end"
        dir="rtl"
      >
        <Button
          variant="danger"
          @click="emit('update:modelValue', false)"
        >
          بستن
        </Button>
      </div>
    </template>
  </Modal>
</template>

<script setup>
import { computed } from 'vue'
import { Button, Modal, Table } from '../ui'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    required: true
  },
  columns: {
    type: Array,
    required: true
  },
  entries: {
    type: Array,
    default: () => []
  },
  emptyMessage: {
    type: String,
    default: 'تاریخچه تغییرات یافت نشد'
  },
  emptyTableMessage: {
    type: String,
    default: 'تاریخچه تغییرات یافت نشد'
  }
})

const emit = defineEmits(['update:modelValue'])

const hasEntries = computed(() => Array.isArray(props.entries) && props.entries.length > 0)
</script>
