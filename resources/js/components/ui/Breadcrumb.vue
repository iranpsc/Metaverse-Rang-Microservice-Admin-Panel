<template>
  <nav aria-label="Breadcrumb" class="mb-4">
    <ol class="flex flex-wrap items-center gap-2 text-sm" dir="rtl">
      <li
        v-for="(item, index) in items"
        :key="index"
        class="flex items-center gap-2"
      >
        <router-link
          v-if="!item.active && item.to"
          :to="item.to"
          class="text-[var(--theme-text-secondary)] transition-all duration-200 hover:text-primary-400 hover:underline focus:outline-none focus:ring-2 focus:ring-primary-400/50 focus:ring-offset-2 focus:ring-offset-[var(--theme-bg-base)] rounded-md px-1"
        >
          {{ item.label }}
        </router-link>
        <span
          v-else
          class="text-[var(--theme-text-primary)] font-semibold px-1"
          :aria-current="item.active ? 'page' : undefined"
        >
          {{ item.label }}
        </span>
        <svg
          v-if="index < items.length - 1"
          class="w-4 h-4 text-[var(--theme-text-muted)] opacity-60"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M15 19l-7-7 7-7"
          />
        </svg>
      </li>
    </ol>
  </nav>
</template>

<script setup>
defineProps({
  items: {
    type: Array,
    required: true,
    validator: (items) => {
      return items.every(
        (item) =>
          typeof item === 'object' &&
          item !== null &&
          typeof item.label === 'string' &&
          (item.active === undefined || typeof item.active === 'boolean') &&
          (item.to === undefined || typeof item.to === 'string' || typeof item.to === 'object')
      )
    }
  }
})
</script>

<style scoped>
/* Additional styles can be added if needed */
</style>

