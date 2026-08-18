<template>
  <div
    :class="[
      'min-h-screen relative flex items-center justify-center p-4 transition-colors duration-300 ease-out',
      themeBackground
    ]"
    dir="rtl"
  >
    <div class="absolute top-4 right-4 z-20">
      <ThemeToggleButton size="sm" variant="glass" />
    </div>
    <div class="w-full max-w-md">
      <!-- Logo/Title -->
      <div class="text-center mb-8">
        <div class="flex justify-center mb-4">
          <img
            src="/assets/images/logo-compact.png"
            alt="Metaverse Admin logo"
            class="w-16 h-16 drop-shadow-[0_0_18px_rgba(124,58,237,0.45)]"
          />
        </div>
        <h1 class="text-3xl font-bold bg-gradient-to-r from-primary-400 to-secondary-400 bg-clip-text text-transparent mb-2">
          متارنگ
        </h1>
        <p class="text-[var(--theme-text-secondary)]">وارد حساب کاربری خود شوید</p>
      </div>

      <!-- Login Card -->
      <Card variant="glass" rounded="xl" padding="lg" class="shadow-2xl">
        <div class="space-y-6">
          <!-- Error Message -->
          <Alert
            v-model="errorMessage"
            variant="danger"
            :message="errorMessageText"
            :dismissible="true"
          />

          <!-- Success Message -->
          <Alert
            v-model="successMessage"
            variant="success"
            :message="successMessageText"
            :dismissible="true"
          />

          <!-- Login Form -->
          <form @submit.prevent="handleLogin" class="space-y-5">
            <!-- Email Field -->
            <Input
              v-model="form.email"
              type="email"
              label="آدرس ایمیل"
              placeholder="ایمیل خود را وارد کنید"
              required
              autocomplete="email"
              :disabled="loading || authLoading"
              size="lg"
              class="w-full"
            >
              <template #iconLeft>
                <EmailIcon />
              </template>
            </Input>

            <!-- Password Field -->
            <Input
              v-model="form.password"
              type="password"
              label="رمز عبور"
              placeholder="رمز عبور خود را وارد کنید"
              required
              autocomplete="current-password"
              :disabled="loading || authLoading"
              size="lg"
              class="w-full"
            >
              <template #iconLeft>
                <LockIcon />
              </template>
            </Input>

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between">
              <label class="flex items-center gap-2 cursor-pointer">
                <input
                  v-model="form.remember"
                  type="checkbox"
                  :class="checkboxClasses"
                  :disabled="loading || authLoading"
                />
                <span class="text-sm text-[var(--theme-text-secondary)]">مرا به خاطر بسپار</span>
              </label>
              <router-link
                to="/forgot-password"
                class="text-sm text-primary-400 hover:text-primary-300 transition-colors"
              >
                رمز عبور را فراموش کرده‌اید؟
              </router-link>
            </div>

            <!-- Submit Button -->
            <Button
              type="submit"
              variant="primary"
              size="lg"
              rounded="lg"
              :loading="loading || authLoading"
              full-width
              class="w-full"
            >
              {{ (loading || authLoading) ? 'در حال ورود...' : 'ورود' }}
            </Button>
          </form>
        </div>
      </Card>

      <!-- Footer -->
      <div class="text-center mt-6 text-sm text-[var(--theme-text-muted)]">
        <p>© {{ new Date().getFullYear() }} متارنگ. تمامی حقوق محفوظ است.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, h, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../../composables/useAuth'
import { Card, Input, Button, Alert } from '../../components/ui'
import ThemeToggleButton from '../../components/ThemeToggleButton.vue'
import { useTheme } from '../../composables/useTheme'

const router = useRouter()
const { login: loginUser, isLoading: authLoading } = useAuth()
const { currentTheme } = useTheme()

const themeBackground = computed(() =>
  currentTheme.value === 'light'
    ? 'bg-gradient-to-br from-slate-100 via-white to-slate-200'
    : 'bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900'
)

const checkboxClasses = computed(() =>
  currentTheme.value === 'light'
    ? 'w-4 h-4 rounded border-slate-300 text-primary-500 focus:ring-primary-400/50 focus:ring-2 bg-white'
    : 'w-4 h-4 rounded bg-white/5 border-white/10 text-primary-400 focus:ring-primary-400/50 focus:ring-2'
)

// Icon Components
const EmailIcon = () => h('svg', {
  class: 'w-5 h-5',
  fill: 'none',
  stroke: 'currentColor',
  viewBox: '0 0 24 24'
}, [
  h('path', {
    'stroke-linecap': 'round',
    'stroke-linejoin': 'round',
    'stroke-width': '2',
    d: 'M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207'
  })
])

const LockIcon = () => h('svg', {
  class: 'w-5 h-5',
  fill: 'none',
  stroke: 'currentColor',
  viewBox: '0 0 24 24'
}, [
  h('path', {
    'stroke-linecap': 'round',
    'stroke-linejoin': 'round',
    'stroke-width': '2',
    d: 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'
  })
])

const form = reactive({
  email: '',
  password: '',
  remember: false
})

const loading = ref(false)
const errorMessage = ref(false)
const successMessage = ref(false)
const errorMessageText = ref('')
const successMessageText = ref('')

// Check if redirected from password reset
onMounted(() => {
  const params = new URLSearchParams(window.location.search)
  if (params.get('reset') === 'success') {
    successMessageText.value = 'رمز عبور شما با موفقیت تغییر کرد. لطفا وارد شوید.'
    successMessage.value = true
    setTimeout(() => {
      successMessage.value = false
      successMessageText.value = ''
    }, 5000)
  }
})

const handleLogin = async () => {
  errorMessage.value = false
  errorMessageText.value = ''
  loading.value = true

  try {
    const result = await loginUser({
      email: form.email,
      password: form.password,
      remember: form.remember
    })

    if (result.success) {
      // Redirect to dashboard
      router.push({ name: 'dashboard' })
    } else {
      errorMessageText.value = result.error || 'ایمیل یا رمز عبور نامعتبر است. لطفا دوباره تلاش کنید.'
      errorMessage.value = true
    }
  } catch (error) {
    errorMessageText.value = error.message || 'خطا در ورود به سیستم. لطفا دوباره تلاش کنید.'
    errorMessage.value = true
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
/* Metaverse theme colors */
:root {
  --primary-400: #A78BFA;
  --secondary-400: #22D3EE;
}
</style>

