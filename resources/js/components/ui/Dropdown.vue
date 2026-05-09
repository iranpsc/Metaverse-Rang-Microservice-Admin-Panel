<template>
  <div class="relative inline-block" ref="dropdownRef">
    <!-- Trigger -->
    <div @click="toggle" @keydown.enter.prevent="toggle" @keydown.space.prevent="toggle">
      <slot name="trigger">
        <button
          :class="[
            'flex items-center gap-2 px-4 py-2 rounded-lg',
            'bg-bg-elevated border border-border text-text-primary',
            'hover:bg-white/5 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500'
          ]"
        >
          {{ label }}
          <svg
            :class="[
              'w-4 h-4 transition-transform duration-200',
              { 'rotate-180': isOpen }
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
      </slot>
    </div>

    <!-- Dropdown Menu -->
    <Transition
      enter-active-class="transition-all duration-200 ease-out"
      enter-from-class="opacity-0 scale-95 -translate-y-2"
      enter-to-class="opacity-100 scale-100 translate-y-0"
      leave-active-class="transition-all duration-150 ease-in"
      leave-from-class="opacity-100 scale-100 translate-y-0"
      leave-to-class="opacity-0 scale-95 -translate-y-2"
    >
      <div
        v-if="isOpen"
        :class="[
          'absolute z-50 mt-2 min-w-[200px] rounded-lg',
          'bg-bg-elevated backdrop-blur-md border border-border shadow-xl py-2',
          placementClasses
        ]"
      >
        <slot />
        <div v-if="items.length > 0" class="py-1">
          <button
            v-for="(item, index) in items"
            :key="index"
            :class="[
              'w-full text-right px-4 py-2 text-sm transition-colors',
              itemClasses,
              { 'text-error': item.danger }
            ]"
            @click="handleItemClick(item, index)"
          >
            <div v-if="item.icon" class="flex items-center gap-3">
              <component :is="item.icon" class="w-4 h-4" />
              <span>{{ item.label }}</span>
            </div>
            <span v-else>{{ item.label }}</span>
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  label: {
    type: String,
    default: 'Options'
  },
  items: {
    type: Array,
    default: () => []
  },
  placement: {
    type: String,
    default: 'bottom-end',
    validator: (value) => [
      'top-start', 'top', 'top-end',
      'bottom-start', 'bottom', 'bottom-end',
      'left-start', 'left', 'left-end',
      'right-start', 'right', 'right-end'
    ].includes(value)
  },
  closeOnClick: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['update:modelValue', 'item-click'])

const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const dropdownRef = ref(null)

const placementClasses = computed(() => {
  const placements = {
    'top-start': 'bottom-full left-0 mb-2',
    'top': 'bottom-full left-1/2 -translate-x-1/2 mb-2',
    'top-end': 'bottom-full right-0 mb-2',
    'bottom-start': 'top-full left-0 mt-2',
    'bottom': 'top-full left-1/2 -translate-x-1/2 mt-2',
    'bottom-end': 'top-full right-0 mt-2',
    'left-start': 'right-full top-0 mr-2',
    'left': 'right-full top-1/2 -translate-y-1/2 mr-2',
    'left-end': 'right-full bottom-0 mr-2',
    'right-start': 'left-full top-0 ml-2',
    'right': 'left-full top-1/2 -translate-y-1/2 ml-2',
    'right-end': 'left-full bottom-0 ml-2'
  }
  return placements[props.placement]
})

const itemClasses = computed(() => {
  return 'text-text-primary hover:bg-white/5 hover:text-primary-400'
})

const toggle = () => {
  isOpen.value = !isOpen.value
}

const handleItemClick = (item, index) => {
  emit('item-click', item, index)
  if (props.closeOnClick && !item.preventClose) {
    isOpen.value = false
  }
}

const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    isOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.bg-bg-elevated {
  background-color: var(--theme-bg-elevated, #1E293B);
}

.border-border {
  border-color: var(--theme-border, rgba(255, 255, 255, 0.1));
}

.text-text-primary {
  color: var(--theme-text-primary, #F8FAFC);
}

.text-error {
  color: var(--color-error, #EF4444);
}
</style>

