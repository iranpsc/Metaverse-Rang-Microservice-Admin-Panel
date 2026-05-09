<template>
  <main
    :class="[
      'fixed top-16 bottom-0 left-0 transition-all duration-300 ease-in-out',
      'overflow-y-auto',
      'bg-[var(--theme-bg-base)]',
      // Mobile: full width when sidebar closed, overlay when open
      'right-0',
      // Z-index: same as header on all screens
      'z-40'
    ]"
    :style="contentOffsetStyle"
  >
    <div class="px-4 py-6 sm:px-6 lg:px-8">
      <slot />
    </div>
  </main>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, toRefs } from 'vue'

const props = defineProps({
  sidebarCollapsed: {
    type: Boolean,
    default: false
  }
})

// Destructure for template access with reactivity
const { sidebarCollapsed } = toRefs(props)

const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1024)
const updateWindowWidth = () => {
  windowWidth.value = window.innerWidth
}

const isDesktop = computed(() => windowWidth.value >= 1024)
const contentOffsetStyle = computed(() => {
  if (isDesktop.value) {
    return {
      right: sidebarCollapsed.value ? '4rem' : '16rem'
    }
  }
  return { right: '0' }
})

onMounted(() => {
  updateWindowWidth()
  window.addEventListener('resize', updateWindowWidth)
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', updateWindowWidth)
})
</script>

<style scoped>
/* Smooth transitions for content area */
main {
  transition: right 0.3s ease-in-out;
}

/* Custom scrollbar for content area */
main::-webkit-scrollbar {
  width: 8px;
}

main::-webkit-scrollbar-track {
  background: var(--theme-bg-elevated);
}

main::-webkit-scrollbar-thumb {
  background: rgba(124, 58, 237, 0.3);
  border-radius: 4px;
}

main::-webkit-scrollbar-thumb:hover {
  background: rgba(124, 58, 237, 0.5);
}
</style>
