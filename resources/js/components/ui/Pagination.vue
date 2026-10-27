<template>
  <div
    v-if="pagination && pagination.total > 0"
    :class="['flex items-center justify-between pt-4', containerClass]"
  >
    <!-- Pagination Info -->
    <div :class="['text-sm text-[var(--theme-text-secondary)]', infoClass]">
      <slot name="info">
        نمایش
        <span class="font-medium text-[var(--theme-text-primary)]">{{ pagination.from }}</span>
        تا
        <span class="font-medium text-[var(--theme-text-primary)]">{{ pagination.to }}</span>
        از
        <span class="font-medium text-[var(--theme-text-primary)]">{{ pagination.total }}</span>
        نتیجه
      </slot>
    </div>

    <!-- Pagination Controls -->
    <div :class="['flex items-center gap-2', controlsClass]">
      <!-- First Button -->
      <button
        type="button"
        @click="handleFirst"
        :disabled="pagination.current_page <= 1 || disabled"
        aria-label="اولین صفحه"
        :class="[
          'px-4 py-2 rounded-lg bg-[var(--theme-bg-elevated)] border border-[var(--theme-border)]',
          'text-[var(--theme-text-primary)] hover:bg-[var(--theme-bg-glass)]',
          'disabled:opacity-50 disabled:cursor-not-allowed transition-colors',
          buttonClass
        ]"
      >
        <slot name="first">
          اول
        </slot>
      </button>

      <!-- Previous Button -->
      <button
        type="button"
        @click="handlePrevious"
        :disabled="pagination.current_page <= 1 || disabled"
        aria-label="صفحه قبلی"
        :class="[
          'px-4 py-2 rounded-lg bg-[var(--theme-bg-elevated)] border border-[var(--theme-border)]',
          'text-[var(--theme-text-primary)] hover:bg-[var(--theme-bg-glass)]',
          'disabled:opacity-50 disabled:cursor-not-allowed transition-colors',
          buttonClass
        ]"
      >
        <slot name="previous">
          <svg viewBox="0 0 24 24" aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
            <path d="m11 5 7 7-7 7" />
            <path d="m4 5 7 7-7 7" />
          </svg>
        </slot>
      </button>

      <!-- Page Numbers -->
      <div class="flex items-center gap-1">
        <button
          v-for="page in visiblePages"
          :key="page"
          @click="handlePageChange(page)"
          :disabled="disabled"
          :class="[
            'px-4 py-2 rounded-lg transition-colors',
            page === pagination.current_page
              ? 'bg-[var(--theme-primary)] text-white'
              : 'bg-[var(--theme-bg-elevated)] border border-[var(--theme-border)] text-[var(--theme-text-primary)] hover:bg-[var(--theme-bg-glass)]',
            buttonClass
          ]"
        >
          {{ page }}
        </button>
      </div>

      <!-- Next Button -->
      <button
        type="button"
        @click="handleNext"
        :disabled="pagination.current_page >= pagination.last_page || disabled"
        aria-label="صفحه بعدی"
        :class="[
          'px-4 py-2 rounded-lg bg-[var(--theme-bg-elevated)] border border-[var(--theme-border)]',
          'text-[var(--theme-text-primary)] hover:bg-[var(--theme-bg-glass)]',
          'disabled:opacity-50 disabled:cursor-not-allowed transition-colors',
          buttonClass
        ]"
      >
        <slot name="next">
          <svg viewBox="0 0 24 24" aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
            <path d="m13 5-7 7 7 7" />
            <path d="m20 5-7 7 7 7" />
          </svg>
        </slot>
      </button>

      <!-- Last Button -->
      <button
        type="button"
        @click="handleLast"
        :disabled="pagination.current_page >= pagination.last_page || disabled"
        aria-label="آخرین صفحه"
        :class="[
          'px-4 py-2 rounded-lg bg-[var(--theme-bg-elevated)] border border-[var(--theme-border)]',
          'text-[var(--theme-text-primary)] hover:bg-[var(--theme-bg-glass)]',
          'disabled:opacity-50 disabled:cursor-not-allowed transition-colors',
          buttonClass
        ]"
      >
        <slot name="last">
          آخر
        </slot>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  pagination: {
    type: Object,
    required: true,
    validator: (pagination) => {
      return (
        pagination.current_page !== undefined &&
        pagination.last_page !== undefined &&
        pagination.total !== undefined
      )
    }
  },
  disabled: {
    type: Boolean,
    default: false
  },
  maxVisiblePages: {
    type: Number,
    default: 5
  },
  containerClass: {
    type: String,
    default: ''
  },
  infoClass: {
    type: String,
    default: ''
  },
  controlsClass: {
    type: String,
    default: ''
  },
  buttonClass: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['page-change'])

const visiblePages = computed(() => {
  if (!props.pagination) return []

  const current = props.pagination.current_page
  const last = props.pagination.last_page
  const pages = []
  
  // Calculate start and end pages
  const half = Math.floor(props.maxVisiblePages / 2)
  let start = Math.max(1, current - half)
  let end = Math.min(last, current + half)
  
  // Adjust if we're near the start or end
  if (end - start < props.maxVisiblePages - 1) {
    if (start === 1) {
      end = Math.min(last, start + props.maxVisiblePages - 1)
    } else if (end === last) {
      start = Math.max(1, end - props.maxVisiblePages + 1)
    }
  }

  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  
  return pages
})

const handlePrevious = () => {
  if (props.pagination.current_page > 1) {
    handlePageChange(props.pagination.current_page - 1)
  }
}

const handleFirst = () => {
  if (props.pagination.current_page > 1) {
    handlePageChange(1)
  }
}

const handleNext = () => {
  if (props.pagination.current_page < props.pagination.last_page) {
    handlePageChange(props.pagination.current_page + 1)
  }
}

const handleLast = () => {
  if (props.pagination.current_page < props.pagination.last_page) {
    handlePageChange(props.pagination.last_page)
  }
}

const handlePageChange = (page) => {
  if (page >= 1 && page <= props.pagination.last_page && !props.disabled) {
    emit('page-change', page)
  }
}
</script>

<style scoped>
/* Additional styles if needed */
</style>

