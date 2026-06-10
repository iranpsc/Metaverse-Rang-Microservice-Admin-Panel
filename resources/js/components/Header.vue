<template>
  <header
    :class="[
      'fixed top-0 left-0 h-16',
      'backdrop-blur-md bg-[var(--theme-bg-glass)]',
      'border-b border-[var(--theme-border)]',
      'shadow-[0_2px_8px_var(--theme-shadow)]',
      'transition-all duration-300 ease-in-out',
      'right-0',
      // Z-index: same as other components on large screens, lower on small screens
      'z-[50] lg:z-[45]'
    ]"
    :style="headerOffsetStyle"
  >
    <div class="flex items-center justify-between h-full px-6">
      <!-- Sidebar toggle slot: button on mobile, placeholder on desktop -->
      <div class="flex items-center">
        <button
          v-if="sidebarCollapsed"
          @click="$emit('toggle-sidebar')"
          class="p-2 rounded-lg text-[var(--theme-text-secondary)] hover:text-primary-400 hover:bg-[var(--theme-bg-glass)] transition-colors lg:hidden"
          aria-label="Open sidebar"
        >
          <svg
            class="w-6 h-6"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 6h16M4 12h16M4 18h16"
            />
          </svg>
        </button>
        <!-- Placeholder keeps spacing for header content on large screens -->
        <div class="hidden lg:block w-12" aria-hidden="true"></div>
      </div>

      <!-- Actions (Right side in RTL) -->
      <div class="flex items-center gap-4">
        <PhoneVerificationCountdown />

        <!-- Theme Toggle (Metaverse Mode Switch) -->
        <ThemeToggleButton size="sm" variant="glass" />

        <!-- Notifications -->
        <button
          class="relative p-2 rounded-lg text-[var(--theme-text-secondary)] hover:text-primary-400 hover:bg-[var(--theme-bg-glass)] transition-colors"
          aria-label="Notifications"
        >
          <svg
            class="w-5 h-5"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
            />
          </svg>
          <span
            v-if="notificationCount > 0"
            class="absolute top-1 right-1 w-2 h-2 rounded-full ring-2 ring-[var(--theme-bg-base)]"
            style="background-color: var(--theme-error);"
          />
        </button>

        <!-- User Profile -->
        <div ref="userMenuRef" class="relative">
          <button
            @click="toggleUserMenu"
            class="flex items-center gap-3 p-2 rounded-lg hover:bg-[var(--theme-bg-glass)] transition-colors"
            aria-label="User menu"
          >
            <!-- Avatar with status ring and gradient border -->
            <div class="relative w-9 h-9 rounded-full bg-gradient-to-r from-primary-500 to-secondary-500 p-0.5">
              <div class="w-full h-full rounded-full bg-[var(--theme-bg-elevated)] flex items-center justify-center overflow-hidden ring-2 ring-[var(--theme-bg-base)]">
                <img
                  v-if="userImageUrl"
                  :src="userImageUrl"
                  :alt="displayName"
                  class="w-full h-full object-cover"
                  @error="handleImageError"
                />
                <span
                  v-else
                  class="text-xs font-semibold text-primary-400"
                >
                  {{ userInitials }}
                </span>
              </div>
              <!-- Online status ring -->
              <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full border-2 border-[var(--theme-bg-base)]" style="background-color: var(--theme-success);"></span>
            </div>
            <span class="text-sm font-medium text-[var(--theme-text-primary)] hidden md:block">
              {{ displayName }}
            </span>
            <svg
              :class="[
                'w-4 h-4 text-[var(--theme-text-muted)] transition-transform duration-200 ease-in-out',
                { 'rotate-180': showUserMenu }
              ]"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M19 9l-7 7-7-7"
              />
            </svg>
          </button>

          <!-- User Dropdown Menu - Glassmorphic -->
          <Transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0 scale-95 -translate-y-2"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition-all duration-150 ease-in"
            leave-from-class="opacity-100 scale-100 translate-y-0"
            leave-to-class="opacity-0 scale-95 -translate-y-2"
          >
            <div
              v-if="showUserMenu"
              :class="[
                'absolute left-0 mt-2 w-48 rounded-xl backdrop-blur-md py-2 transition-colors duration-200 ease-out',
                dropdownMenuClasses
              ]"
            >
            <router-link
              to="/profile"
              class="block px-4 py-2 text-sm text-[var(--theme-text-primary)] hover:bg-[var(--theme-bg-glass)] hover:text-primary-400 transition-colors"
              @click="showUserMenu = false"
            >
              تنظیمات پروفایل
            </router-link>
            <hr class="my-2 border-[var(--theme-border)]" />
            <button
              @click="handleLogout"
              :disabled="isLoggingOut"
              :class="[
                'w-full text-right block px-4 py-2 text-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed',
                'hover:bg-[var(--theme-bg-glass)]'
              ]"
              style="color: var(--theme-error);"
            >
              {{ isLoggingOut ? 'در حال خروج...' : 'خروج' }}
            </button>
            </div>
          </Transition>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, toRefs } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../composables/useAuth'
import { useTheme } from '../composables/useTheme'
import { useBreakpoint } from '../composables/useBreakpoint'
import ThemeToggleButton from './ThemeToggleButton.vue'
import PhoneVerificationCountdown from './PhoneVerificationCountdown.vue'

const props = defineProps({
  sidebarCollapsed: {
    type: Boolean,
    default: false
  }
})

defineEmits(['toggle-sidebar'])

// Destructure for template access with reactivity
const { sidebarCollapsed } = toRefs(props)

const router = useRouter()
const { user, logout: logoutUser } = useAuth()

const userMenuRef = ref(null)
const showUserMenu = ref(false)
const notificationCount = ref(0)
const isLoggingOut = ref(false)
const imageError = ref(false)
const { currentTheme } = useTheme()
const { isDesktop, stopBreakpoint } = useBreakpoint()
const headerOffsetStyle = computed(() => {
  if (isDesktop.value) {
    return {
      right: sidebarCollapsed.value ? '4rem' : '16rem'
    }
  }
  return { right: '0' }
})

const dropdownMenuClasses = computed(() => {
  if (currentTheme.value === 'light') {
    return 'bg-white/90 border border-[var(--theme-border)] shadow-[0_20px_45px_rgba(15,23,42,0.18)]'
  }
  return 'bg-[var(--theme-bg-elevated)]/95 border border-[var(--theme-border)] shadow-[0_12px_35px_var(--theme-shadow)]'
})

// Display user name
const displayName = computed(() => {
  return user.value?.name || 'کاربر'
})

// Get user image URL
const userImageUrl = computed(() => {
  if (!user.value?.image || user.value.image === 'noimage.png' || imageError.value) {
    return null
  }
  // Handle both relative paths and full URLs
  if (user.value.image.startsWith('http://') || user.value.image.startsWith('https://')) {
    return user.value.image
  }
  // Default to uploads directory
  return `/uploads/${user.value.image}`
})

// Get user initials for avatar fallback
const userInitials = computed(() => {
  if (!user.value?.name) return 'AD'

  const names = user.value.name.trim().split(' ')
  if (names.length >= 2) {
    return (names[0][0] + names[1][0]).toUpperCase()
  }
  return names[0][0].toUpperCase()
})

// Handle image load error
const handleImageError = () => {
  imageError.value = true
}

const toggleUserMenu = () => {
  showUserMenu.value = !showUserMenu.value
}

// Handle logout
const handleLogout = async () => {
  isLoggingOut.value = true
  try {
    const result = await logoutUser()

    if (result.success) {
      // Close menu
      showUserMenu.value = false
      // Redirect to login page
      router.push({ name: 'login' })
    } else {
      // Even if logout fails, redirect to login
      showUserMenu.value = false
      router.push({ name: 'login' })
    }
  } catch (error) {
    // On error, still redirect to login
    showUserMenu.value = false
    router.push({ name: 'login' })
  } finally {
    isLoggingOut.value = false
  }
}

// Close user menu when clicking outside
const handleClickOutside = (event) => {
  if (userMenuRef.value && !userMenuRef.value.contains(event.target)) {
    showUserMenu.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  stopBreakpoint()
})
</script>

<style scoped>
/* Header uses theme CSS variables defined in app.css */
</style>
