<template>
  <!-- Mobile Backdrop Overlay -->
  <div
    v-if="!isCollapsed"
    @click="$emit('toggle-sidebar')"
    :class="[
      'fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity duration-300 ease-in-out',
      'z-[60] lg:hidden'
    ]"
    aria-hidden="true"
  />

  <Transition name="sidebar-slide" appear>
    <aside
      v-show="!isCollapsed || isDesktop"
      :class="[
        'sidebar-panel fixed right-0 top-0 bottom-0 flex flex-col',
        'transition-[transform,opacity] duration-300 ease-in-out will-change-transform',
        'backdrop-blur-md bg-[var(--theme-bg-glass)]',
        'border-l border-[var(--theme-border)]',
        'shadow-[0_0_30px_rgba(124,58,237,0.2)]',
        'w-64',
        isCollapsed ? 'lg:w-16' : 'lg:w-64',
        'overflow-hidden',
        'z-[70] lg:z-40'
      ]"
      :style="sidebarWidthStyle"
    >
    <!-- Sidebar Header: Logo (swapped for RTL) -->
    <div
      :class="[
        'flex items-center justify-between border-b border-[var(--theme-border)] transition-all duration-300',
        isCollapsed ? 'lg:justify-center lg:px-2 lg:py-4' : 'gap-4 p-4'
      ]"
    >
      <!-- Logo Image (center in RTL) -->
      <div
        :class="[
          'flex items-center justify-center transition-all duration-300 flex-shrink-0',
          isCollapsed ? 'lg:flex-1' : 'max-w-full flex-1',
          isDesktopCollapsed ? 'lg:hidden' : ''
        ]"
      >
        <img
          src="/assets/images/logo.png"
          alt="Color Metaverse, National Metaverse of Iran"
          :class="[
            'w-auto transition-all duration-300',
            isCollapsed ? 'lg:max-h-10 lg:max-w-full' : 'max-h-14'
          ]"
          style="object-fit: contain; height: auto; max-width: 100%;"
        />
      </div>

      <!-- Close Button (left side in RTL) - Only on mobile when sidebar is open -->
      <button
        v-if="!isCollapsed"
        @click="$emit('toggle-sidebar')"
        class="p-2 rounded-lg text-[var(--theme-text-secondary)] hover:text-primary-400 hover:bg-[var(--theme-bg-glass)] transition-colors lg:hidden flex-shrink-0"
        aria-label="Close sidebar"
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
            d="M6 18L18 6M6 6l12 12"
          />
        </svg>
      </button>

      <!-- Collapse Toggle Button (right side in RTL) - Only on large screens -->
      <button
        @click="$emit('toggle-sidebar')"
        class="hidden lg:flex p-2 rounded-lg text-[var(--theme-text-secondary)] hover:text-primary-400 hover:bg-[var(--theme-bg-glass)] transition-colors flex-shrink-0 order-3"
        aria-label="Toggle sidebar"
      >
        <svg
          v-if="!isCollapsed"
          class="w-5 h-5"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M9 5l7 7-7 7"
          />
        </svg>
        <svg
          v-else
          class="w-5 h-5"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M15 19l-7-7 7-7"
          />
        </svg>
      </button>

    </div>

    <!-- Search Bar - Hidden when collapsed on large screens -->
    <div
      :class="[
        'px-4 py-3 border-b border-[var(--theme-border)] transition-all duration-300',
        isCollapsed ? 'lg:hidden' : ''
      ]"
    >
      <div class="relative">
        <input
          type="text"
          v-model="searchQuery"
          placeholder="جستجو..."
          :class="[
            'w-full px-4 py-2 pr-10 bg-[var(--theme-bg-glass)] border border-[var(--theme-border)] rounded-lg',
            'text-[var(--theme-text-primary)] placeholder-[var(--theme-text-muted)]',
            'focus:outline-none focus:ring-2 focus:ring-primary-400/50 focus:border-primary-400/50',
            'transition-all duration-200'
          ]"
        />
        <svg
          v-if="!searchQuery"
          class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-[var(--theme-text-muted)] pointer-events-none"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
          />
        </svg>
        <button
          v-if="searchQuery"
          @click="searchQuery = ''"
          class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-[var(--theme-text-muted)] hover:text-[var(--theme-text-primary)] transition-colors"
          aria-label="Clear search"
        >
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

    <nav
      :class="[
        'flex-1 p-4 space-y-2 overflow-y-auto transition-all duration-300',
        isDesktopCollapsed ? 'lg:flex lg:flex-col lg:items-center lg:space-y-3 lg:p-3' : ''
      ]"
    >
      <!-- No results message -->
      <div
        v-if="searchQuery && filteredMenuItems.length === 0"
        class="text-center py-8 text-[var(--theme-text-muted)]"
      >
        <svg class="w-12 h-12 mx-auto mb-3 text-[var(--theme-text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="text-sm">نتیجه‌ای یافت نشد</p>
      </div>

      <!-- Menu items -->
      <template v-for="menu in filteredMenuItems" :key="menu.id">
        <!-- Menu Item with Children -->
        <div v-if="menu.children && menu.children.length > 0" class="menu-group">
          <button
            @click="toggleMenu(menu.id)"
            @mouseenter="handleMenuHover(menu.id, $event)"
            @mouseleave="handleMenuLeave"
            class="nav-item group w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200"
            :class="[
              isActiveMenu(menu) ? 'active' : '',
              isDesktopCollapsed ? 'lg:justify-center lg:gap-0 lg:px-0 lg:py-3 lg:h-12 lg:rounded-full' : ''
            ]"
            :title="isDesktopCollapsed ? menu.label : ''"
            :aria-label="menu.label"
          >
            <svg
              :class="[
                getIconClass(menu),
                isDesktopCollapsed ? 'lg:w-6 lg:h-6' : ''
              ]"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path v-for="(path, index) in getIconPaths(menu.icon)" :key="index" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="path" />
            </svg>
            <span
              :class="[
                getLabelClass(menu),
                isDesktopCollapsed ? 'lg:hidden' : ''
              ]"
            >
              {{ menu.label }}
            </span>
            <svg
              v-if="!isDesktopCollapsed"
              :class="getArrowClass(menu)"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <Teleport to="body">
            <div
              v-if="shouldShowHoverPanel(menu.id)"
              class="hidden lg:flex fixed z-[200] w-60 max-w-xs flex-col gap-2 rounded-xl border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)]/95 px-4 py-3 shadow-[0_12px_35px_var(--theme-shadow)] backdrop-blur-md"
              :style="hoverPanelStyle"
              @mouseenter="handleHoverPanelEnter"
              @mouseleave="handleHoverPanelLeave"
            >
              <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-[var(--theme-text-primary)]">
                  {{ menu.label }}
                </span>
                <svg class="w-4 h-4 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </div>
              <div class="flex flex-col gap-1">
                <router-link
                  v-for="child in menu.children"
                  :key="child.id"
                  :to="child.route"
                  class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-[var(--theme-text-secondary)] transition-colors hover:bg-[var(--theme-bg-glass)] hover:text-primary-400"
                  @click="handleHoverPanelLeave"
                >
                  <svg :class="getIconClass(child)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path v-for="(path, index) in getIconPaths(child.icon)" :key="index" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="path" />
                  </svg>
                  <span>{{ child.label }}</span>
                </router-link>
              </div>
            </div>
          </Teleport>
          <!-- Submenu -->
          <div
            :class="[
              'overflow-hidden transition-all duration-200 space-y-1 pr-4',
              expandedMenus.includes(menu.id) ? 'max-h-[1000px] opacity-100' : 'max-h-0 opacity-0',
              isDesktopCollapsed ? 'lg:hidden' : ''
            ]"
          >
            <template v-for="child in menu.children" :key="child.id">
              <router-link
                v-if="child.route !== '#'"
                :to="child.route"
                class="nav-item submenu-item flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-200 text-sm"
                :class="isActive(child.route) ? 'active' : ''"
                :title="child.label"
                :aria-label="child.label"
              >
                <svg :class="getIconClass(child)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path v-for="(path, index) in getIconPaths(child.icon)" :key="index" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="path" />
                </svg>
                <span
                  class="text-[var(--theme-text-secondary)] group-hover:text-primary-400 transition-colors whitespace-nowrap"
                >
                  {{ child.label }}
                </span>
              </router-link>
            </template>
          </div>
        </div>

        <!-- Single Menu Item -->
        <router-link
          v-else-if="menu.route !== '#'"
          :to="menu.route"
          @mouseenter="handleMenuHover(menu.id, $event)"
          @mouseleave="handleMenuLeave"
          class="nav-item group flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200"
          :class="[
            isActive(menu.route) ? 'active' : '',
            isDesktopCollapsed ? 'lg:justify-center lg:gap-0 lg:px-0 lg:py-3 lg:h-12 lg:rounded-full' : ''
          ]"
          :title="isDesktopCollapsed ? menu.label : ''"
          :aria-label="menu.label"
        >
          <svg
            :class="[
              getIconClass(menu),
              isDesktopCollapsed ? 'lg:w-6 lg:h-6' : ''
            ]"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path v-for="(path, index) in getIconPaths(menu.icon)" :key="index" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="path" />
          </svg>
          <span
            :class="[
              getLabelClass(menu),
              isDesktopCollapsed ? 'lg:hidden' : ''
            ]"
          >
            {{ menu.label }}
          </span>
        </router-link>
        <Teleport to="body">
          <div
            v-if="shouldShowHoverPanel(menu.id) && (!menu.children || menu.children.length === 0)"
            class="hidden lg:flex fixed z-[200] w-56 max-w-xs flex-col gap-2 rounded-xl border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)]/95 px-4 py-3 shadow-[0_12px_35px_var(--theme-shadow)] backdrop-blur-md"
            :style="hoverPanelStyle"
            @mouseenter="handleHoverPanelEnter"
            @mouseleave="handleHoverPanelLeave"
          >
            <router-link
              :to="menu.route"
              class="flex items-center gap-3 text-sm font-medium text-[var(--theme-text-primary)] hover:text-primary-400"
              @click="handleHoverPanelLeave"
            >
              <svg :class="getIconClass(menu)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path v-for="(path, index) in getIconPaths(menu.icon)" :key="index" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="path" />
              </svg>
              <span>{{ menu.label }}</span>
            </router-link>
          </div>
        </Teleport>
      </template>
    </nav>
    </aside>
  </Transition>

  <!-- Hover Panel -->
  <div
    v-if="hoveredMenuId"
    :style="hoverPanelStyle"
    class="fixed z-50 bg-[var(--theme-bg-glass)] border border-[var(--theme-border)] rounded-lg shadow-lg p-3 max-w-sm"
    @mouseenter="handleHoverPanelEnter"
    @mouseleave="handleHoverPanelLeave"
  >
    <div v-if="shouldShowHoverPanel(hoveredMenuId)">
      <h4 class="text-sm font-medium text-[var(--theme-text-primary)] mb-1">{{ getMenuById(hoveredMenuId).label }}</h4>
      <p class="text-xs text-[var(--theme-text-muted)]">{{ getMenuById(hoveredMenuId).description || 'No description available.' }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, toRefs } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useBreakpoint } from '../composables/useBreakpoint'
import menuConfig from '../router/menuConfig'

const props = defineProps({
  isCollapsed: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['toggle-sidebar'])

// Destructure for template access with reactivity
const { isCollapsed } = toRefs(props)

const { windowWidth, isDesktop, stopBreakpoint } = useBreakpoint()
const isDesktopCollapsed = computed(() => isDesktop.value && isCollapsed.value)

// Computed width style for sidebar - ensures reactive updates
const sidebarWidthStyle = computed(() => {
  if (isDesktop.value) {
    // On large screens, use collapsed width (4rem) when collapsed, expanded width (16rem) when not
    return isCollapsed.value ? { width: '4rem' } : { width: '16rem' }
  }
  // On small screens, don't set width via style (let Tailwind handle it)
  return {}
})

const hoveredMenuId = ref(null)
const hoveredMenuElement = ref(null)
const hoverPanelStyle = ref({})
let hoverHideTimeout = null

const clearHoverTimeout = () => {
  if (hoverHideTimeout) {
    window.clearTimeout(hoverHideTimeout)
    hoverHideTimeout = null
  }
}

const updateHoverPanelPosition = () => {
  if (!isDesktopCollapsed.value || !hoveredMenuElement.value) return
  const rect = hoveredMenuElement.value.getBoundingClientRect()
  const offsetRight = Math.max(16, window.innerWidth - rect.left + 16)
  const offsetTop = Math.max(16, Math.min(window.innerHeight - 16, rect.top))
  hoverPanelStyle.value = {
    top: `${offsetTop}px`,
    right: `${offsetRight}px`
  }
}

const handleMenuHover = (menuId, event) => {
  if (!isDesktopCollapsed.value) return
  clearHoverTimeout()
  hoveredMenuId.value = menuId
  hoveredMenuElement.value = event.currentTarget
  updateHoverPanelPosition()
}

const scheduleHoverHide = () => {
  clearHoverTimeout()
  if (!isDesktopCollapsed.value) return
  hoverHideTimeout = window.setTimeout(() => {
    hoveredMenuId.value = null
    hoveredMenuElement.value = null
  }, 120)
}

const handleMenuLeave = () => {
  if (!isDesktopCollapsed.value) return
  scheduleHoverHide()
}

const handleHoverPanelEnter = () => {
  clearHoverTimeout()
}

const handleHoverPanelLeave = () => {
  scheduleHoverHide()
}

const shouldShowHoverPanel = (menuId) => isDesktopCollapsed.value && hoveredMenuId.value === menuId

const router = useRouter()
const route = useRoute()

const expandedMenus = ref([])
const searchQuery = ref('')

// Icon path data
const iconPaths = {
  home: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
  user: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
  cube: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
  key: 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z',
  phone: 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z',
  shoppingCart: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
  users: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
  map: 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.447 2.224A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7',
  wifi: 'M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0',
  calendar: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
  list: 'M4 6h16M4 12h16M4 18h16',
  eye: 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
  puzzle: 'M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 011-1h1a2 2 0 100-4H7a1 1 0 01-1-1V7a1 1 0 011-1h3a1 1 0 001-1V4z',
  question: 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
  video: 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z',
  levelUp: 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'
}

const getIconPath = (iconName) => {
  return iconPaths[iconName] || iconPaths.list
}

const getIconPaths = (iconName) => {
  const path = getIconPath(iconName)
  // Handle multi-path icons (eye icon has 2 paths)
  if (iconName === 'eye') {
    const paths = path.split(' M')
    if (paths.length === 2) {
      return [paths[0], 'M' + paths[1]]
    }
  }
  return [path]
}

// Search function to check if menu item matches search query
const matchesSearch = (text, query) => {
  if (!query || !query.trim()) return true
  const normalizedText = text.toLowerCase().trim()
  const normalizedQuery = query.toLowerCase().trim()
  return normalizedText.includes(normalizedQuery)
}

const menuItems = ref(menuConfig)

// Filtered menu items based on search query
const filteredMenuItems = computed(() => {
  if (!searchQuery.value || !searchQuery.value.trim()) {
    return menuItems.value
  }

  return menuItems.value
    .map(menu => {
      // Check if parent menu matches
      const parentMatches = matchesSearch(menu.label, searchQuery.value)

      // Check if any child matches
      if (menu.children && menu.children.length > 0) {
        const filteredChildren = menu.children.filter(child =>
          matchesSearch(child.label, searchQuery.value)
        )

        // If parent matches or any child matches, include the menu
        if (parentMatches || filteredChildren.length > 0) {
          return {
            ...menu,
            children: parentMatches ? menu.children : filteredChildren
          }
        }
        return null
      } else {
        // Single menu item - check if it matches
        return parentMatches ? menu : null
      }
    })
    .filter(menu => menu !== null)
})

// Auto-expand menus when searching
watch(searchQuery, (newQuery) => {
  if (newQuery && newQuery.trim()) {
    // Expand all menus with matching children
    filteredMenuItems.value.forEach(menu => {
      if (menu.children && menu.children.length > 0) {
        if (!expandedMenus.value.includes(menu.id)) {
          expandedMenus.value.push(menu.id)
        }
      }
    })
  }
})

const isActive = (path) => {
  if (path === '#' || !path) return false
  const currentPath = route.path
  return currentPath === path || currentPath.startsWith(path + '/')
}

const isActiveMenu = (menu) => {
  if (menu.children) {
    return menu.children.some(child => isActive(child.route))
  }
  return isActive(menu.route)
}

const toggleMenu = (menuId) => {
  const index = expandedMenus.value.indexOf(menuId)
  if (index > -1) {
    expandedMenus.value.splice(index, 1)
  } else {
    expandedMenus.value.push(menuId)
  }
}

const getIconClass = (menu) => {
  const menuIsActive = isActiveMenu(menu) || (menu.route !== '#' && isActive(menu.route))
  const hoverColor = getColorClass(menu.color)
  const colorClass = menuIsActive
    ? hoverColor
    : `text-[var(--theme-text-muted)] group-hover:${hoverColor}`
  return `w-5 h-5 flex-shrink-0 ${colorClass}`
}

const getArrowClass = (menu) => {
  const isExpanded = expandedMenus.value.includes(menu.id)
  const menuIsActive = isActiveMenu(menu)
  const hoverColor = getColorClass(menu.color)

  // Use menu color when active or expanded, otherwise slate with hover
  let colorClass
  if (menuIsActive || isExpanded) {
    colorClass = hoverColor
  } else {
    // Use computed class name for hover states
    const hoverMap = {
      'text-primary-400': 'group-hover:text-primary-400',
      'text-secondary-400': 'group-hover:text-secondary-400',
      'text-emerald-400': 'group-hover:text-emerald-400',
      'text-yellow-400': 'group-hover:text-yellow-400',
      'text-blue-400': 'group-hover:text-blue-400',
      'text-rose-400': 'group-hover:text-rose-400'
    }
    colorClass = `text-[var(--theme-text-muted)] ${hoverMap[hoverColor] || 'group-hover:text-primary-400'}`
  }

  return `w-4 h-4 mr-auto transition-all duration-200 ${colorClass} ${isExpanded ? 'rotate-180' : ''}`
}

const getLabelClass = (menu, isChild = false) => {
  const menuIsActive = menu.route !== '#' ? isActive(menu.route) : isActiveMenu(menu)
  const hoverColor = getColorClass(menu.color)
  const colorClass = menuIsActive
    ? `${hoverColor} font-medium`
    : `text-[var(--theme-text-secondary)] group-hover:${hoverColor}`
  return `${colorClass} transition-opacity duration-300 whitespace-nowrap`.trim()
}

const getColorClass = (color) => {
  const colorMap = {
    primary: 'text-primary-400',
    secondary: 'text-secondary-400',
    emerald: 'text-emerald-400',
    yellow: 'text-yellow-400',
    blue: 'text-blue-400',
    rose: 'text-rose-400'
  }
  return colorMap[color] || 'text-primary-400'
}

const getTextClass = (menu) => {
  return getColorClass(menu.color) + ' font-medium'
}

// Navigation is now handled by router-link, so this function is no longer needed
// Keeping it for backwards compatibility in case it's called elsewhere
const navigate = (path) => {
  if (path && path !== '#') {
    router.push(path)
  }
}

// Watch for window resize
onMounted(() => {
  updateHoverPanelPosition()
})

onBeforeUnmount(() => {
  stopBreakpoint()
  clearHoverTimeout()
})

watch(isDesktopCollapsed, (value) => {
  if (!value) {
    hoveredMenuId.value = null
    hoveredMenuElement.value = null
    clearHoverTimeout()
  }
})

watch(() => route.path, () => {
  hoveredMenuId.value = null
  hoveredMenuElement.value = null
  clearHoverTimeout()
})

watch(windowWidth, () => {
  updateHoverPanelPosition()
})

const getMenuById = (id) => {
  return menuItems.value.find(menu => menu.id === id)
}
</script>

<style scoped>
.nav-item {
  position: relative;
}

/* Active menu item with gradient border left + glow */
.nav-item.active {
  background: rgba(124, 58, 237, 0.1);
  border-right: 3px solid;
  border-image: linear-gradient(to bottom, #7C3AED, #06B6D4) 1;
}

.nav-item.active::before {
  content: '';
  position: absolute;
  right: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 3px;
  height: 60%;
  background: linear-gradient(to bottom, #7C3AED, #06B6D4);
  border-radius: 4px 0 0 4px;
  box-shadow: 0 0 10px rgba(124, 58, 237, 0.5);
}

.nav-item:hover:not(.active) {
  background: var(--theme-bg-glass);
}

.submenu-item {
  margin-right: 1rem;
}

.submenu-item:hover:not(.active) {
  background: var(--theme-bg-glass);
}

.menu-group .nav-item.active + div {
  margin-top: 0.25rem;
}

/* Custom scrollbar - Metaverse theme */
aside::-webkit-scrollbar {
  width: 6px;
}

aside::-webkit-scrollbar-track {
  background: var(--theme-bg-glass);
}

aside::-webkit-scrollbar-thumb {
  background: rgba(124, 58, 237, 0.3);
  border-radius: 3px;
}

aside::-webkit-scrollbar-thumb:hover {
  background: rgba(124, 58, 237, 0.5);
}

.sidebar-slide-enter-active,
.sidebar-slide-leave-active {
  transition: transform 0.35s ease, opacity 0.3s ease;
}

.sidebar-slide-enter-from,
.sidebar-slide-leave-to {
  transform: translateX(100%);
  opacity: 0;
}

.sidebar-slide-enter-to,
.sidebar-slide-leave-from {
  transform: translateX(0);
  opacity: 1;
}

@media (min-width: 1024px) {
  .sidebar-slide-enter-from,
  .sidebar-slide-leave-to,
  .sidebar-slide-enter-to,
  .sidebar-slide-leave-from {
    transform: translateX(0);
  }
}
</style>

