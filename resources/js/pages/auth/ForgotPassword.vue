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
          فراموشی رمز عبور
        </h1>
        <p class="text-[var(--theme-text-secondary)]">ایمیل خود را وارد کنید تا لینک بازنشانی رمز عبور را دریافت کنید</p>
      </div>

      <!-- Forgot Password Card -->
      <Card variant="glass" rounded="xl" padding="lg" class="shadow-2xl">
        <div class="space-y-6">
          <!-- Success Message -->
          <Alert
            v-if="successMessage"
            v-model="showSuccessMessage"
            variant="success"
            :message="successMessageText"
            :dismissible="true"
          >
            <template #default>
              <p class="font-medium mb-1 text-[var(--theme-text-primary)]">{{ successMessageText }}</p>
              <p class="text-xs text-[var(--theme-text-secondary)]">لطفا صندوق ورودی ایمیل خود را برای دستورالعمل‌های بیشتر بررسی کنید.</p>
            </template>
          </Alert>

          <!-- Error Message -->
          <Alert
            v-model="showErrorMessage"
            variant="danger"
            :message="errorMessageText"
            :dismissible="true"
          />

          <!-- Form (only show if success message not shown) -->
          <form v-if="!successMessage" @submit.prevent="handleForgotPassword" class="space-y-5">
            <!-- Email Field -->
            <Input
              v-model="form.email"
              type="email"
              label="آدرس ایمیل"
              placeholder="آدرس ایمیل خود را وارد کنید"
              helper-text="لینک بازنشانی رمز عبور برای شما ارسال خواهد شد."
              required
              autocomplete="email"
              :disabled="loading"
              size="lg"
              class="w-full"
            >
              <template #iconLeft>
                <EmailIcon />
              </template>
            </Input>

            <!-- Submit Button -->
            <Button
              type="submit"
              variant="primary"
              size="lg"
              rounded="lg"
              :loading="loading"
              full-width
              class="w-full"
            >
              {{ loading ? 'در حال ارسال...' : 'ارسال لینک بازنشانی' }}
            </Button>
          </form>
        </div>
        <template #footer>
          <div class="text-center">
            <router-link
              to="/login"
              class="text-sm text-primary-400 hover:text-primary-300 transition-colors"
            >
              بازگشت به ورود
            </router-link>
          </div>
        </template>
      </Card>

      <!-- Footer -->
      <div class="text-center mt-6 text-sm text-[var(--theme-text-muted)]">
        <p>© {{ new Date().getFullYear() }} متارنگ. تمامی حقوق محفوظ است.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, h, computed } from 'vue'
import { authApi } from '../../utils/api'
import { Card, Input, Button, Alert } from '../../components/ui'
import ThemeToggleButton from '../../components/ThemeToggleButton.vue'
import { useTheme } from '../../composables/useTheme'

const { currentTheme } = useTheme()

const themeBackground = computed(() =>
  currentTheme.value === 'light'
    ? 'bg-gradient-to-br from-slate-100 via-white to-slate-200'
    : 'bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900'
)

// Icon Component
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

const form = reactive({
  email: ''
})

const loading = ref(false)
const showErrorMessage = ref(false)
const showSuccessMessage = ref(false)
const errorMessageText = ref('')
const successMessage = ref(false)
const successMessageText = ref('')

const handleForgotPassword = async () => {
  showErrorMessage.value = false
  errorMessageText.value = ''
  successMessage.value = false
  successMessageText.value = ''
  loading.value = true

  try {
    await authApi.forgotPassword(form.email)
    successMessageText.value = 'لینک بازنشانی رمز عبور به آدرس ایمیل شما ارسال شد.'
    successMessage.value = true
    showSuccessMessage.value = true
  } catch (error) {
    if (error.errors) {
      // Laravel validation errors
      const firstError = Object.values(error.errors)[0]
      errorMessageText.value = Array.isArray(firstError) ? firstError[0] : firstError
    } else if (error.message) {
      errorMessageText.value = error.message
    } else {
      errorMessageText.value = 'ارسال لینک بازنشانی رمز عبور امکان‌پذیر نیست. لطفا بعدا تلاش کنید.'
    }
    showErrorMessage.value = true
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

