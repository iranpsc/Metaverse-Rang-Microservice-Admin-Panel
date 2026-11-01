<template>
  <div
    :class="[
      'relative overflow-hidden rounded-xl p-6 transition-all duration-300',
      'backdrop-blur-md border border-[var(--theme-border)] hover:scale-[1.02]',
      containerThemeClasses,
      cardClasses[type]
    ]"
  >
    <a href="#" class="block">
      <div class="flex items-center justify-between">
        <div class="flex-1">
          <div
            :class="[
              'text-3xl font-bold mb-2 transition-all duration-500',
              textColorClasses[type]
            ]"
            :data-value="formattedValue"
          >
            {{ displayValue }}
          </div>
          <div
            :class="[
              'text-sm font-medium',
              textSecondaryClasses[type]
            ]"
          >
            {{ label }}
          </div>
        </div>
        <div
          :class="[
            'w-16 h-16 rounded-full flex items-center justify-center',
            iconContainerClasses,
            iconBgClasses[type]
          ]"
        >
          <i :class="[props.icon, 'text-2xl']"></i>
        </div>
      </div>
    </a>
    <!-- Glow effect -->
    <div
      :class="[
        'absolute inset-0 opacity-0 transition-opacity duration-300',
        'pointer-events-none',
        glowClasses[type]
      ]"
      :style="{ background: glowGradient[type] }"
    ></div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useTheme } from '../composables/useTheme'

const props = defineProps({
  type: {
    type: String,
    required: true,
    validator: (value) => ['cyan', 'blue', 'orange', 'red'].includes(value)
  },
  value: {
    type: [Number, String],
    required: true
  },
  label: {
    type: String,
    required: true
  },
  icon: {
    type: String,
    default: 'icon-people'
  },
  isCurrency: {
    type: Boolean,
    default: false
  }
})

const animatedValue = ref(0)

const { currentTheme } = useTheme()

const containerThemeClasses = computed(() => {
  if (currentTheme.value === 'light') {
    return 'bg-[var(--theme-bg-elevated)]/95 text-[var(--theme-text-primary)] hover:shadow-[0_18px_38px_rgba(15,23,42,0.12)]'
  }
  return 'bg-[var(--theme-bg-glass)] text-[var(--theme-text-primary)] hover:shadow-[0_0_30px_rgba(124,58,237,0.3)]'
})

const cardClasses = computed(() => {
  if (currentTheme.value === 'light') {
    return {
      cyan: 'bg-gradient-to-br from-cyan-200/80 via-sky-100/80 to-blue-200/60',
      blue: 'bg-gradient-to-br from-blue-200/80 via-indigo-100/80 to-indigo-200/60',
      orange: 'bg-gradient-to-br from-orange-200/80 via-amber-100/80 to-amber-200/60',
      red: 'bg-gradient-to-br from-rose-200/80 via-pink-100/80 to-pink-200/60'
    }
  }
  return {
    cyan: 'bg-gradient-to-br from-cyan-500/20 to-blue-600/20 bg-cyan-900/10',
    blue: 'bg-gradient-to-br from-blue-500/20 to-indigo-600/20 bg-blue-900/10',
    orange: 'bg-gradient-to-br from-orange-500/20 to-amber-600/20 bg-orange-900/10',
    red: 'bg-gradient-to-br from-red-500/20 to-pink-600/20 bg-red-900/10'
  }
})

const textColorClasses = computed(() => {
  if (currentTheme.value === 'light') {
    return {
      cyan: 'text-cyan-700',
      blue: 'text-blue-700',
      orange: 'text-orange-700',
      red: 'text-rose-600'
    }
  }
  return {
    cyan: 'text-cyan-300',
    blue: 'text-blue-300',
    orange: 'text-orange-300',
    red: 'text-red-300'
  }
})

const textSecondaryClasses = computed(() => {
  if (currentTheme.value === 'light') {
    return {
      cyan: 'text-cyan-700/80',
      blue: 'text-blue-700/80',
      orange: 'text-orange-700/80',
      red: 'text-rose-600/80'
    }
  }
  return {
    cyan: 'text-cyan-400/80',
    blue: 'text-blue-400/80',
    orange: 'text-orange-400/80',
    red: 'text-red-400/80'
  }
})

const iconBgClasses = computed(() => {
  if (currentTheme.value === 'light') {
    return {
      cyan: 'bg-cyan-500/15',
      blue: 'bg-blue-500/15',
      orange: 'bg-orange-500/15',
      red: 'bg-red-500/15'
    }
  }
  return {
    cyan: 'bg-cyan-500/20',
    blue: 'bg-blue-500/20',
    orange: 'bg-orange-500/20',
    red: 'bg-red-500/20'
  }
})

const iconContainerClasses = computed(() => {
  if (currentTheme.value === 'light') {
    return 'backdrop-blur-md border border-[rgba(148,163,184,0.35)] shadow-[0_12px_25px_rgba(15,23,42,0.12)]'
  }
  return 'backdrop-blur-md border border-[rgba(255,255,255,0.18)] shadow-[0_0_18px_rgba(124,58,237,0.25)]'
})

const glowClasses = computed(() => {
  if (currentTheme.value === 'light') {
    return {
      cyan: 'bg-gradient-to-br from-cyan-300/25 to-sky-400/20',
      blue: 'bg-gradient-to-br from-blue-300/25 to-indigo-400/20',
      orange: 'bg-gradient-to-br from-orange-300/25 to-amber-400/20',
      red: 'bg-gradient-to-br from-rose-300/25 to-pink-400/20'
    }
  }
  return {
    cyan: 'bg-gradient-to-br from-cyan-400/30 to-blue-500/30',
    blue: 'bg-gradient-to-br from-blue-400/30 to-indigo-500/30',
    orange: 'bg-gradient-to-br from-orange-400/30 to-amber-500/30',
    red: 'bg-gradient-to-br from-red-400/30 to-pink-500/30'
  }
})

const glowGradient = computed(() => {
  if (currentTheme.value === 'light') {
    return {
      cyan: 'radial-gradient(circle at 50% 50%, rgba(14, 165, 233, 0.18), transparent 70%)',
      blue: 'radial-gradient(circle at 50% 50%, rgba(59, 130, 246, 0.18), transparent 70%)',
      orange: 'radial-gradient(circle at 50% 50%, rgba(251, 146, 60, 0.18), transparent 70%)',
      red: 'radial-gradient(circle at 50% 50%, rgba(244, 63, 94, 0.18), transparent 70%)'
    }
  }
  return {
    cyan: 'radial-gradient(circle at 50% 50%, rgba(6, 182, 212, 0.3), transparent 70%)',
    blue: 'radial-gradient(circle at 50% 50%, rgba(59, 130, 246, 0.3), transparent 70%)',
    orange: 'radial-gradient(circle at 50% 50%, rgba(251, 146, 60, 0.3), transparent 70%)',
    red: 'radial-gradient(circle at 50% 50%, rgba(239, 68, 68, 0.3), transparent 70%)'
  }
})

const formattedValue = computed(() => {
  if (props.isCurrency && typeof props.value === 'number') {
    // Format as currency with Persian numerals
    return new Intl.NumberFormat('fa-IR', {
      style: 'currency',
      currency: 'IRR',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(props.value)
  }
  if (typeof props.value === 'number') {
    return props.value.toLocaleString('fa-IR')
  }
  return props.value
})

const displayValue = computed(() => {
  if (props.isCurrency && typeof animatedValue.value === 'number') {
    // Format animated value as currency
    return new Intl.NumberFormat('fa-IR', {
      style: 'currency',
      currency: 'IRR',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(animatedValue.value)
  }
  // For non-currency numbers, use Persian locale
  if (typeof animatedValue.value === 'number') {
    return animatedValue.value.toLocaleString('fa-IR')
  }
  return animatedValue.value
})

// Animate number counting
const animateValue = () => {
  const start = 0
  // For currency, always parse as number
  const end = typeof props.value === 'number'
    ? props.value
    : parseFloat(String(props.value).replace(/[^\d.-]/g, '')) || 0

  // Skip animation if value is 0 or NaN
  if (!end || isNaN(end)) {
    animatedValue.value = end
    return
  }

  const duration = 1500
  const startTime = performance.now()

  const animate = (currentTime) => {
    const elapsed = currentTime - startTime
    const progress = Math.min(elapsed / duration, 1)

    // Easing function (ease-out)
    const easeOut = 1 - Math.pow(1 - progress, 3)

    animatedValue.value = Math.floor(start + (end - start) * easeOut)

    if (progress < 1) {
      requestAnimationFrame(animate)
    } else {
      animatedValue.value = end
    }
  }

  requestAnimationFrame(animate)
}

onMounted(() => {
  animateValue()
})
</script>

<style scoped>
/* Icon font will be loaded globally from public/assets */
</style>

