<template>
  <div class="metaverse-not-found" dir="rtl">
    <div class="ambient ambient--primary" aria-hidden="true"></div>
    <div class="ambient ambient--secondary" aria-hidden="true"></div>
    <div class="ambient ambient--grid" aria-hidden="true"></div>

    <div class="relative z-10 w-full max-w-3xl space-y-6">
      <!-- 404 Error Card -->
      <Card
        variant="glass"
        rounded="xl"
        padding="xl"
        class="error-card text-center"
      >
        <div class="space-y-9">
          <!-- Animated 404 Number -->
          <div class="status-code" aria-hidden="true">
            <span class="status-code__backdrop">404</span>
            <span class="status-code__number">404</span>
          </div>

          <!-- Error Message -->
          <div class="space-y-5">
            <div class="error-chip">
              <span class="error-chip__pulse" aria-hidden="true"></span>
              <span>کد خطا • ۴۰۴</span>
            </div>
            <h1 class="error-title">
              صفحه‌ای که به دنبال آن هستید پیدا نشد
            </h1>
            <p class="error-subtitle">
              لینک ممکن است تغییر کرده باشد یا دیگر در دسترس نباشد. می‌توانید به داشبورد بازگردید یا مسیر قبلی را امتحان کنید.
            </p>
          </div>

          <!-- Action Buttons -->
          <div class="flex flex-col sm:flex-row gap-4 justify-center items-center pt-2">
            <Button
              variant="primary"
              size="lg"
              rounded="lg"
              @click="goHome"
              class="action-button"
            >
              <template #icon-left>
                <HomeIcon />
              </template>
              بازگشت به داشبورد
            </Button>
            <Button
              variant="secondary"
              size="lg"
              rounded="lg"
              @click="goBack"
              class="action-button"
            >
              <template #icon-left>
                <ArrowIcon />
              </template>
              بازگشت به صفحه قبل
            </Button>
          </div>

          <!-- Helper Tip -->
          <div class="helper-tip">
            <span class="helper-tip__label">پیشنهاد متاورسی</span>
            <span class="helper-tip__content">از منوی سمت راست برای پیمایش به ماژول‌های اصلی یا از نوار جستجو برای پیدا کردن داده‌ها استفاده کنید.</span>
          </div>
        </div>
      </Card>

      <!-- Footer -->
      <div class="footer-note">
        <p>© {{ currentYear }} متارنگ. تمامی حقوق محفوظ است.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { h } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../../composables/useAuth'
import { Card, Button } from '../ui'

const router = useRouter()
const { isAuthenticated } = useAuth()
const currentYear = new Date().getFullYear()

// Icon Components
const HomeIcon = () => h('svg', {
  class: 'w-5 h-5',
  fill: 'none',
  stroke: 'currentColor',
  viewBox: '0 0 24 24'
}, [
  h('path', {
    'stroke-linecap': 'round',
    'stroke-linejoin': 'round',
    'stroke-width': '2',
    d: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'
  })
])

const ArrowIcon = () => h('svg', {
  class: 'w-5 h-5',
  fill: 'none',
  stroke: 'currentColor',
  viewBox: '0 0 24 24'
}, [
  h('path', {
    'stroke-linecap': 'round',
    'stroke-linejoin': 'round',
    'stroke-width': '2',
    d: 'M10 19l-7-7m0 0l7-7m-7 7h18'
  })
])

const goHome = () => {
  // Redirect to dashboard if authenticated, otherwise to login
  if (isAuthenticated.value) {
    router.push({ name: 'dashboard' })
  } else {
    router.push({ name: 'login' })
  }
}

const goBack = () => {
  if (window.history.length > 1) {
    router.go(-1)
  } else {
    // If no history, redirect to home or login
    if (isAuthenticated.value) {
      router.push({ name: 'dashboard' })
    } else {
      router.push({ name: 'login' })
    }
  }
}
</script>

<style scoped>
.metaverse-not-found {
  position: relative;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 3.5rem 1.5rem;
  overflow: hidden;
  background: radial-gradient(circle at 10% 20%, rgba(124, 58, 237, 0.25), transparent 55%),
    radial-gradient(circle at 90% 15%, rgba(6, 182, 212, 0.25), transparent 60%),
    linear-gradient(135deg, rgba(15, 23, 42, 0.92), rgba(15, 23, 42, 0.88));
  color: var(--theme-text-primary);
}

:global(:root[data-theme='light']) .metaverse-not-found,
:global(body.light) .metaverse-not-found {
  background: radial-gradient(circle at 15% 25%, rgba(124, 58, 237, 0.12), transparent 55%),
    radial-gradient(circle at 85% 20%, rgba(6, 182, 212, 0.14), transparent 60%),
    linear-gradient(135deg, rgba(248, 250, 252, 0.95), rgba(226, 232, 240, 0.9));
  color: var(--theme-text-primary);
}

.ambient {
  position: absolute;
  border-radius: 9999px;
  filter: blur(120px);
  opacity: 0.8;
  pointer-events: none;
  mix-blend-mode: screen;
  animation: float 12s ease-in-out infinite;
}

.ambient--primary {
  width: 420px;
  height: 420px;
  top: -120px;
  inset-inline-start: -160px;
  background: rgba(124, 58, 237, 0.55);
}

.ambient--secondary {
  width: 360px;
  height: 360px;
  bottom: -140px;
  inset-inline-end: -120px;
  background: rgba(6, 182, 212, 0.45);
  animation-delay: -4s;
}

.ambient--grid {
  position: absolute;
  inset: 0;
  background-image: linear-gradient(rgba(148, 163, 184, 0.08) 1px, transparent 1px),
    linear-gradient(90deg, rgba(148, 163, 184, 0.08) 1px, transparent 1px);
  background-size: 80px 80px;
  mask-image: radial-gradient(circle at center, rgba(0, 0, 0, 0.9), transparent 70%);
  opacity: 0.6;
}

.error-card {
  background-color: var(--theme-bg-glass);
  background: color-mix(in srgb, var(--theme-bg-glass) 85%, transparent);
  border: 1px solid var(--theme-border);
  backdrop-filter: blur(18px);
  box-shadow: 0 25px 70px rgba(14, 23, 42, 0.45), 0 0 35px rgba(124, 58, 237, 0.18);
}

.status-code {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.status-code__number {
  font-weight: 700;
  font-size: clamp(4.5rem, 12vw, 8.5rem);
  line-height: 1;
  letter-spacing: 0.12em;
  background: linear-gradient(120deg, var(--color-primary-400), var(--color-secondary-400), var(--color-primary-400));
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
  text-shadow: 0 0 36px rgba(34, 211, 238, 0.25);
}

.status-code__backdrop {
  position: absolute;
  inset: 0;
  font-weight: 700;
  font-size: clamp(4.5rem, 12vw, 8.5rem);
  line-height: 1;
  letter-spacing: 0.12em;
  color: transparent;
  background: linear-gradient(120deg, var(--color-primary-400), var(--color-secondary-400), var(--color-primary-400));
  -webkit-background-clip: text;
  background-clip: text;
  filter: blur(28px);
  opacity: 0.4;
  animation: pulseGlow 3.5s ease-in-out infinite;
}

.error-chip {
  display: inline-flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.55rem 1.3rem;
  border-radius: 9999px;
  font-size: 0.95rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--theme-text-secondary);
  background: rgba(124, 58, 237, 0.12);
  border: 1px solid rgba(124, 58, 237, 0.25);
}

.error-chip__pulse {
  display: inline-flex;
  width: 0.75rem;
  height: 0.75rem;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--color-primary-500), var(--color-secondary-500));
  box-shadow: 0 0 12px rgba(124, 58, 237, 0.65);
  animation: pulse 2.4s ease-in-out infinite;
}

.error-title {
  font-size: clamp(1.8rem, 4.2vw, 2.6rem);
  font-weight: 700;
  color: var(--theme-text-primary);
  line-height: 1.35;
}

.error-subtitle {
  max-width: 34rem;
  margin: 0 auto;
  font-size: 1.05rem;
  color: var(--theme-text-secondary);
  line-height: 1.8;
}

.action-button {
  min-width: 200px;
}

.helper-tip {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  justify-content: center;
  align-items: center;
  padding: 1.1rem 1.6rem;
  border-radius: 1.25rem;
  border: 1px solid var(--theme-border);
  background: rgba(255, 255, 255, 0.03);
  color: var(--theme-text-secondary);
  font-size: 0.95rem;
  line-height: 1.7;
}

:global(:root[data-theme='light']) .helper-tip,
:global(body.light) .helper-tip {
  background: rgba(15, 23, 42, 0.05);
  border-color: rgba(124, 58, 237, 0.18);
}

.helper-tip__label {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
  color: var(--theme-text-primary);
}

.helper-tip__label::before {
  content: '';
  width: 0.65rem;
  height: 0.65rem;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--color-secondary-500), var(--color-primary-500));
  box-shadow: 0 0 10px rgba(6, 182, 212, 0.5);
}

.helper-tip__content {
  text-align: center;
}

.footer-note {
  text-align: center;
  font-size: 0.9rem;
  color: var(--theme-text-muted);
}

@media (min-width: 640px) {
  .helper-tip {
    flex-direction: row;
  }

  .helper-tip__content {
    text-align: start;
  }
}

@keyframes pulse {
  0%,
  100% {
    transform: scale(1);
    opacity: 1;
  }
  50% {
    transform: scale(0.85);
    opacity: 0.55;
  }
}

@keyframes pulseGlow {
  0%,
  100% {
    opacity: 0.35;
    transform: scale(1);
  }
  50% {
    opacity: 0.6;
    transform: scale(1.05);
  }
}

@keyframes float {
  0% {
    transform: translate3d(0, 0, 0);
  }
  50% {
    transform: translate3d(12px, -18px, 0);
  }
  100% {
    transform: translate3d(0, 0, 0);
  }
}

@media (prefers-reduced-motion: reduce) {
  .ambient,
  .status-code__backdrop,
  .error-chip__pulse {
    animation: none !important;
  }
}
</style>

