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
          بازنشانی رمز عبور
        </h1>
        <p class="text-[var(--theme-text-secondary)]">رمز عبور جدید خود را وارد کنید</p>
      </div>

      <!-- Reset Password Card -->
      <Card variant="glass" rounded="xl" padding="lg" class="shadow-2xl">
        <div class="space-y-6">
          <!-- Success Message -->
          <Alert
            v-if="successMessage"
            v-model="showSuccessMessage"
            variant="success"
            :message="successMessageText"
            :dismissible="false"
          >
            <template #default>
              <p class="font-medium mb-1 text-[var(--theme-text-primary)]">{{ successMessageText }}</p>
              <p class="text-xs text-[var(--theme-text-secondary)]">در حال هدایت به صفحه ورود...</p>
            </template>
          </Alert>

          <!-- Error Message -->
          <Alert
            v-model="showErrorMessage"
            variant="danger"
            :message="errorMessageText"
            :dismissible="true"
          />

          <!-- Invalid Token Message -->
          <Alert
            v-if="invalidToken"
            v-model="showInvalidToken"
            variant="danger"
            :message="invalidTokenMessage"
            :dismissible="false"
          >
            <template #default>
              <p class="mb-3">{{ invalidTokenMessage }}</p>
              <router-link
                to="/forgot-password"
                class="text-primary-400 hover:text-primary-300 transition-colors inline-flex items-center gap-2 text-sm font-medium"
              >
                درخواست لینک بازنشانی جدید
              </router-link>
            </template>
          </Alert>

          <!-- Form (only show if not invalid token and not success) -->
          <form
            v-if="!invalidToken && !successMessage"
            @submit.prevent="handleResetPassword"
            class="space-y-5"
          >
            <!-- Email Field -->
            <Input
              v-model="form.email"
              type="email"
              label="آدرس ایمیل"
              placeholder="آدرس ایمیل خود را وارد کنید"
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

            <!-- New Password Field -->
            <Input
              v-model="form.password"
              type="password"
              label="رمز عبور جدید"
              placeholder="رمز عبور جدید را وارد کنید"
              helper-text="باید حداقل ۸ کاراکتر باشد."
              required
              autocomplete="new-password"
              :disabled="loading"
              size="lg"
              class="w-full"
            >
              <template #iconLeft>
                <LockIcon />
              </template>
            </Input>

            <!-- Confirm Password Field -->
            <Input
              v-model="form.password_confirmation"
              type="password"
              label="تأیید رمز عبور جدید"
              placeholder="رمز عبور جدید را تأیید کنید"
              required
              autocomplete="new-password"
              :disabled="loading"
              size="lg"
              class="w-full"
            >
              <template #iconLeft>
                <CheckIcon />
              </template>
            </Input>

            <!-- Password Match Indicator -->
            <Transition
              enter-active-class="transition-all duration-200 ease-out"
              enter-from-class="opacity-0"
              enter-to-class="opacity-100"
              leave-active-class="transition-all duration-200 ease-in"
              leave-from-class="opacity-100"
              leave-to-class="opacity-0"
            >
              <Alert
                v-if="form.password && form.password_confirmation"
                :model-value="true"
                :variant="form.password === form.password_confirmation ? 'success' : 'danger'"
                :message="form.password === form.password_confirmation ? 'رمزهای عبور مطابقت دارند' : 'رمزهای عبور مطابقت ندارند'"
                :dismissible="false"
                class="text-sm"
              />
            </Transition>

            <!-- Submit Button -->
            <Button
              type="submit"
              variant="primary"
              size="lg"
              rounded="lg"
              :loading="loading"
              :disabled="form.password !== form.password_confirmation || !form.password || !form.password_confirmation"
              full-width
              class="w-full"
            >
              {{ loading ? 'در حال بازنشانی...' : 'بازنشانی رمز عبور' }}
            </Button>
          </form>

          <!-- Back to Login (only if not success) -->
          <div v-if="!successMessage && !invalidToken" class="text-center pt-4 border-t border-white/10">
            <router-link
              to="/login"
              class="text-sm text-primary-400 hover:text-primary-300 transition-colors"
            >
              بازگشت به ورود
            </router-link>
          </div>
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
import { useRouter, useRoute } from 'vue-router'
import { authApi } from '../../utils/api'
import { Card, Input, Button, Alert } from '../../components/ui'
import ThemeToggleButton from '../../components/ThemeToggleButton.vue'
import { useTheme } from '../../composables/useTheme'

const router = useRouter()
const route = useRoute()
const { currentTheme } = useTheme()

const themeBackground = computed(() =>
  currentTheme.value === 'light'
    ? 'bg-gradient-to-br from-slate-100 via-white to-slate-200'
    : 'bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900'
)

const props = defineProps({
  token: {
    type: String,
    required: true
  }
})

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

const CheckIcon = () => h('svg', {
  class: 'w-5 h-5',
  fill: 'none',
  stroke: 'currentColor',
  viewBox: '0 0 24 24'
}, [
  h('path', {
    'stroke-linecap': 'round',
    'stroke-linejoin': 'round',
    'stroke-width': '2',
    d: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'
  })
])

const form = reactive({
  email: '',
  password: '',
  password_confirmation: '',
  token: ''
})

const loading = ref(false)
const showErrorMessage = ref(false)
const showSuccessMessage = ref(false)
const showInvalidToken = ref(false)
const errorMessageText = ref('')
const successMessage = ref(false)
const successMessageText = ref('')
const invalidToken = ref(false)
const invalidTokenMessage = ref('این لینک بازنشانی رمز عبور نامعتبر است یا منقضی شده است.')

onMounted(() => {
  // Set token from route params
  if (props.token) {
    form.token = props.token
  } else if (route.params.token) {
    form.token = route.params.token
  }

  // Check if token exists
  if (!form.token) {
    invalidToken.value = true
    showInvalidToken.value = true
    errorMessageText.value = 'توکن بازنشانی رمز عبور نامعتبر است یا موجود نیست.'
  }
})

const handleResetPassword = async () => {
  errorMessageText.value = ''
  showErrorMessage.value = false
  loading.value = true

  // Validate passwords match
  if (form.password !== form.password_confirmation) {
    errorMessageText.value = 'رمزهای عبور مطابقت ندارند.'
    showErrorMessage.value = true
    loading.value = false
    return
  }

  try {
    await authApi.resetPassword({
      email: form.email,
      password: form.password,
      password_confirmation: form.password_confirmation,
      token: form.token
    })

    successMessageText.value = 'رمز عبور شما با موفقیت بازنشانی شد!'
    successMessage.value = true
    showSuccessMessage.value = true

    // Redirect to login after 2 seconds
    setTimeout(() => {
      router.push({ name: 'login', query: { reset: 'success' } })
    }, 2000)
  } catch (error) {
    if (error.errors) {
      // Laravel validation errors
      const firstError = Object.values(error.errors)[0]
      errorMessageText.value = Array.isArray(firstError) ? firstError[0] : firstError
    } else if (error.message) {
      errorMessageText.value = error.message

      // Check if token is invalid
      if (error.message.toLowerCase().includes('token') || error.message.toLowerCase().includes('invalid')) {
        invalidToken.value = true
        showInvalidToken.value = true
      }
    } else {
      errorMessageText.value = 'بازنشانی رمز عبور امکان‌پذیر نیست. لطفا دوباره تلاش کنید یا لینک بازنشانی جدیدی درخواست کنید.'
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

