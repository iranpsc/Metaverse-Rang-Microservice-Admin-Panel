<template>
  <div class="min-h-screen bg-[var(--theme-bg-base)] text-[var(--theme-text-primary)]" dir="rtl">
    <!-- Toast Container -->
    <ToastContainer />

    <!-- Header -->
    <Header
      :sidebar-collapsed="sidebarCollapsed"
      @toggle-sidebar="handleSidebarToggle"
    />

    <!-- Layout Container -->
    <div class="relative">
      <!-- Sidebar -->
      <Sidebar :is-collapsed="sidebarCollapsed" @toggle-sidebar="handleSidebarToggle" />

      <!-- Content Area -->
      <ContentArea :sidebar-collapsed="sidebarCollapsed">
        <!-- Router view for dashboard and other pages -->
        <router-view v-slot="{ Component }">
          <component :is="Component" />
        </router-view>
      </ContentArea>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'
import { useRoute } from 'vue-router'
import Header from './Header.vue'
import Sidebar from './Sidebar.vue'
import ContentArea from './ContentArea.vue'
import ToastContainer from './ui/ToastContainer.vue'
import { useBreakpoint } from '../composables/useBreakpoint'

// Default to open (false) - this prevents CLS on desktop
// The sidebar will be open by default, and CSS media queries ensure it's visible on desktop
const sidebarCollapsed = ref(false)
const { windowWidth, isDesktop, stopBreakpoint } = useBreakpoint()
const route = useRoute()

const handleSidebarToggle = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value
  // Save preference to localStorage (only on large screens)
  if (isDesktop.value) {
    localStorage.setItem('sidebarCollapsed', sidebarCollapsed.value.toString())
  }
}

// Track previous window width to detect screen size changes
const previousWidth = ref(windowWidth.value)

const handleResize = () => {
  const currentWidth = windowWidth.value
  const wasSmallScreen = previousWidth.value < 1024
  const wasLargeScreen = previousWidth.value >= 1024
  const isSmallScreen = currentWidth < 1024
  const isLargeScreen = currentWidth >= 1024

  // Only reset to open when transitioning from small to large screen
  // This allows users to collapse/expand on large screens without interference
  if (wasSmallScreen && isLargeScreen) {
    // Transitioning from mobile/tablet to desktop: restore saved preference or default to open
    let savedSidebarState = null
    try {
      savedSidebarState = localStorage.getItem('sidebarCollapsed')
    } catch (error) {
      savedSidebarState = null
    }
    sidebarCollapsed.value = savedSidebarState !== null ? savedSidebarState === 'true' : false
  }

  // Transitioning from desktop to mobile/tablet: always collapse for better usability
  if (wasLargeScreen && isSmallScreen) {
    sidebarCollapsed.value = true
  }
  // Otherwise, preserve the current state (user preference)

  previousWidth.value = currentWidth
}

// Initialize on mount
onMounted(() => {
  if (typeof window === 'undefined') return

  const savedTheme = localStorage.getItem('theme') || 'dark'
  document.documentElement.setAttribute('data-theme', savedTheme)

  // Initialize sidebar state based on screen size
  if (!isDesktop.value) {
    sidebarCollapsed.value = true // Closed on mobile
  } else {
    // On desktop, check for saved preference or default to open
    const savedSidebarState = localStorage.getItem('sidebarCollapsed')
    if (savedSidebarState !== null) {
      sidebarCollapsed.value = savedSidebarState === 'true'
    } else {
      sidebarCollapsed.value = false // Default to open on desktop
    }
  }

  previousWidth.value = windowWidth.value
})

onBeforeUnmount(() => {
  stopBreakpoint()
})

watch(windowWidth, handleResize)
</script>

<style scoped>
/* App uses theme CSS variables defined in app.css */
</style>
