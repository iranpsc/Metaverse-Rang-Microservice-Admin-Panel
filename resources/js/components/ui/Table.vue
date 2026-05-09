<template>
  <div
    v-if="data && data.length > 0"
    :class="[
      'rounded-xl bg-[var(--theme-bg-elevated)] border border-[var(--theme-border)] overflow-hidden',
      containerClass
    ]"
  >
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-[var(--theme-bg-glass)] border-b border-[var(--theme-border)]">
          <tr>
            <th
              v-if="showRowNumber"
              :class="[
                'px-6 py-4 text-right text-sm font-semibold text-[var(--theme-text-primary)]',
                rowNumberHeaderClass
              ]"
            >
              <slot name="header-row-number">
                #
              </slot>
            </th>
            <th
              v-for="column in columns"
              :key="column.key"
              :class="[
                'px-6 py-4 text-right text-sm font-semibold text-[var(--theme-text-primary)]',
                column.headerClass
              ]"
            >
              <slot :name="`header-${column.key}`">
                {{ column.label }}
              </slot>
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[var(--theme-border)]">
          <tr
            v-for="(row, rowIndex) in data"
            :key="getRowKey(row, rowIndex)"
            :class="[
              'hover:bg-[var(--theme-bg-glass)] transition-colors duration-150',
              rowClass
            ]"
          >
            <td
              v-if="showRowNumber"
              :class="[
                'px-6 py-4 text-sm text-[var(--theme-text-secondary)]',
                rowNumberCellClass
              ]"
            >
              <slot
                name="cell-row-number"
                :row="row"
                :rowNumber="getRowNumber(rowIndex)"
                :rowIndex="rowIndex"
              >
                {{ getRowNumber(rowIndex) }}
              </slot>
            </td>
            <td
              v-for="column in columns"
              :key="column.key"
              :class="[
                'px-6 py-4 text-sm',
                column.textSecondary ? 'text-[var(--theme-text-secondary)]' : 'text-[var(--theme-text-primary)]',
                column.cellClass
              ]"
            >
              <slot
                :name="`cell-${column.key}`"
                :row="row"
                :value="getValue(row, column.key)"
                :rowIndex="rowIndex"
              >
                {{ formatValue(getValue(row, column.key), column) }}
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <div
    v-else-if="showEmptyState"
    :class="[
      'rounded-xl bg-[var(--theme-bg-elevated)] border border-[var(--theme-border)] p-12 text-center',
      emptyStateClass
    ]"
  >
    <slot name="empty">
      <div class="text-yellow-400 mb-4">
        <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
          />
        </svg>
      </div>
      <p class="text-[var(--theme-text-secondary)] text-lg">
        {{ emptyStateMessage || 'داده‌ای یافت نشد' }}
      </p>
    </slot>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  // Array of column definitions
  columns: {
    type: Array,
    required: true,
    validator: (columns) => {
      return columns.every(
        (col) => col.key && (col.label || col.slot)
      )
    }
  },
  // Array of data rows
  data: {
    type: Array,
    default: () => []
  },
  // Function or string to get row key for v-for
  rowKey: {
    type: [String, Function],
    default: 'id'
  },
  // Custom classes
  containerClass: {
    type: String,
    default: ''
  },
  rowClass: {
    type: String,
    default: ''
  },
  emptyStateClass: {
    type: String,
    default: ''
  },
  // Empty state options
  showEmptyState: {
    type: Boolean,
    default: true
  },
  emptyStateMessage: {
    type: String,
    default: ''
  },
  // Row number column
  showRowNumber: {
    type: Boolean,
    default: true
  },
  rowNumberHeaderClass: {
    type: String,
    default: ''
  },
  rowNumberCellClass: {
    type: String,
    default: ''
  },
  // Pagination info for calculating row numbers
  pagination: {
    type: Object,
    default: null
  }
})

const getRowKey = (row, index) => {
  if (typeof props.rowKey === 'function') {
    return props.rowKey(row, index)
  }
  return row[props.rowKey] || index
}

const getValue = (row, key) => {
  // Support nested keys like 'user.name'
  const keys = key.split('.')
  let value = row
  for (const k of keys) {
    value = value?.[k]
    if (value === undefined || value === null) break
  }
  return value
}

const formatValue = (value, column) => {
  // If value is null/undefined, show default or empty
  if (value === null || value === undefined) {
    return column.defaultValue ?? '-'
  }

  // Use custom formatter if provided
  if (column.formatter && typeof column.formatter === 'function') {
    return column.formatter(value, column)
  }

  return value
}

const getRowNumber = (rowIndex) => {
  // If pagination info is provided, calculate row number based on current page
  if (props.pagination) {
    const perPage = props.pagination.per_page || props.data.length
    const currentPage = props.pagination.current_page || 1
    return (currentPage - 1) * perPage + rowIndex + 1
  }

  // Otherwise, just return index + 1
  return rowIndex + 1
}
</script>

<style scoped>
/* Additional styles if needed */
</style>

