<template>
  <div class="space-y-8 pb-12" dir="rtl">
    <Card
      variant="glass"
      rounded="xl"
      padding="lg"
      gradient-border
      :hover-glow="false"
    >
      <div class="flex flex-col gap-6 md:flex-row md:items-center">
        <div class="relative">
          <Avatar
            :src="displayAvatar"
            :initials="avatarInitials"
            size="xl"
            ring
            gradient
            status="online"
          />
          <span
            class="absolute -bottom-2 left-1/2 -translate-x-1/2 rounded-full bg-[var(--theme-bg-elevated)] px-3 py-1 text-xs text-secondary border border-[var(--theme-border)] shadow-lg"
          >
            {{
              activeTab === 'info'
                ? 'ویرایش اطلاعات'
                : 'بروزرسانی رمز عبور'
            }}
          </span>
        </div>

        <div class="flex-1">
          <div class="flex flex-col gap-2">
            <h1 class="text-2xl font-semibold text-primary">
              {{ infoForm.name || currentUser.name || 'کاربر متاورس' }}
            </h1>
            <p class="text-sm text-secondary">
              {{ currentUser.email }}
            </p>
          </div>

          <div class="mt-4 flex flex-wrap gap-3 text-xs text-muted">
            <span class="rounded-full border border-[var(--theme-border)] bg-[var(--theme-bg-glass)] px-4 py-1">
              شناسه کاربری: {{ currentUser.id || '---' }}
            </span>
            <span
              v-for="role in userRoles"
              :key="role"
              class="rounded-full border border-[var(--theme-border)] bg-[var(--theme-bg-glass)] px-4 py-1"
            >
              {{ role }}
            </span>
          </div>
        </div>
      </div>
    </Card>

    <Card variant="elevated" rounded="xl" padding="lg">
      <div class="flex flex-col gap-8">
        <div
          class="flex flex-wrap items-center gap-2 rounded-full border border-[var(--theme-border)] bg-[var(--theme-bg-glass)]/70 p-1 backdrop-blur"
        >
          <button
            v-for="tab in tabs"
            :key="tab.id"
            type="button"
            :class="[
              'flex flex-col rounded-full px-6 py-3 transition-all duration-200',
              'text-sm font-medium text-right',
              activeTab === tab.id
                ? 'bg-gradient-to-r from-primary-500 to-secondary-500 text-white shadow-[0_0_18px_rgba(124,58,237,0.35)]'
                : 'text-secondary hover-text-primary hover:bg-white/5'
            ]"
            @click="setActiveTab(tab.id)"
          >
            <span class="text-sm font-semibold">{{ tab.label }}</span>
            <span class="text-xs opacity-80">{{ tab.description }}</span>
          </button>
        </div>

        <Transition
          enter-active-class="transition duration-300 ease-out"
          enter-from-class="opacity-0 translate-y-4"
          enter-to-class="opacity-100 translate-y-0"
          leave-active-class="transition duration-200 ease-in"
          leave-from-class="opacity-100 translate-y-0"
          leave-to-class="opacity-0 translate-y-4"
          mode="out-in"
        >
          <form
            v-if="activeTab === 'info'"
            key="info"
            class="space-y-8"
            @submit.prevent="handleInfoSubmit"
          >
            <div class="grid gap-8 md:grid-cols-[260px_minmax(0,1fr)]">
              <div class="flex flex-col items-center gap-3">
                <div class="relative inline-flex">
                  <Avatar
                    :src="displayAvatar"
                    :initials="avatarInitials"
                    size="xl"
                    ring
                    gradient
                  />
                  <input
                    ref="avatarFileInputRef"
                    type="file"
                    accept="image/*"
                    class="sr-only"
                    tabindex="-1"
                    @change="handleAvatarFileSelect"
                  />
                  <button
                    type="button"
                    class="absolute -bottom-1 -end-1 flex h-10 w-10 items-center justify-center rounded-full border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)] text-secondary shadow-lg transition-colors hover:border-primary-500/40 hover:text-primary-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500/50"
                    aria-label="انتخاب تصویر پروفایل"
                    @click="avatarFileInputRef?.click()"
                  >
                    <svg
                      class="h-5 w-5"
                      xmlns="http://www.w3.org/2000/svg"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke-width="1.5"
                      stroke="currentColor"
                      aria-hidden="true"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"
                      />
                    </svg>
                  </button>
                </div>
                <span
                  class="rounded-full bg-[var(--theme-bg-elevated)] px-3 py-1 text-xs text-secondary border border-[var(--theme-border)]"
                >
                  تصویر کنونی
                </span>
                <p v-if="infoErrors.image" class="text-center text-xs text-red-400">
                  {{ infoErrors.image }}
                </p>
                <p v-else class="text-center text-xs text-muted">
                  حداکثر حجم فایل ۲ مگابایت
                </p>
              </div>

              <div class="space-y-6">
                <Input
                  v-model="infoForm.name"
                  label="نام و نام خانوادگی"
                  placeholder="نام و نام خانوادگی را وارد کنید"
                  :error="infoErrors.name"
                  required
                />

                <Input
                  v-model="infoForm.email"
                  label="ایمیل"
                  readonly
                  helper-text="ایمیل قابل ویرایش نیست."
                />
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
              <Button
                variant="primary"
                rounded="full"
                type="submit"
                :loading="isSavingInfo"
              >
                ذخیره تغییرات
              </Button>
            </div>
          </form>

          <form
            v-else
            key="password"
            class="space-y-6"
            @submit.prevent="handlePasswordSubmit"
          >
            <Card
              variant="glass"
              rounded="lg"
              padding="lg"
              :hover-glow="false"
            >
              <div class="grid gap-6">
                <Input
                  v-model="passwordForm.current_password"
                  type="password"
                  label="رمز عبور فعلی"
                  placeholder="رمز عبور فعلی را وارد کنید"
                  :error="passwordErrors.current_password"
                  required
                />

                <Input
                  v-model="passwordForm.password"
                  type="password"
                  label="رمز عبور جدید"
                  placeholder="رمز عبور جدید"
                  :error="passwordErrors.password"
                  required
                />

                <Input
                  v-model="passwordForm.password_confirmation"
                  type="password"
                  label="تایید رمز عبور جدید"
                  placeholder="رمز عبور جدید را مجدداً وارد کنید"
                  :error="passwordErrors.password_confirmation"
                  required
                />

                <div class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-bg-glass)]/50 p-4 text-xs leading-6 text-secondary">
                  <strong class="block text-primary">راهنمایی امنیتی:</strong>
                  <ul class="mt-2 space-y-1 list-disc pr-5">
                    <li>تنها پس از تایید رمز عبور فعلی، کد تایید برای شما ارسال می‌شود.</li>
                    <li>کد تایید ۶ رقمی را در کمتر از ۶۰ ثانیه وارد کنید.</li>
                    <li>رمز عبور جدید باید حداقل شامل ۸ کاراکتر و ترکیبی از حروف و اعداد باشد.</li>
                  </ul>
                </div>

                <div class="flex flex-col gap-2 text-xs text-muted">
                  <span>
                    پس از تایید رمز عبور جدید، برای ورودهای بعدی از آن استفاده کنید.
                  </span>
                  <Button
                    type="submit"
                    variant="primary"
                    rounded="full"
                    :loading="isRequestingPassword"
                    class="self-start"
                  >
                    {{ isProduction ? 'ارسال کد تایید' : 'ثبت تغییر رمز عبور' }}
                  </Button>
                </div>
              </div>
            </Card>
          </form>
        </Transition>
      </div>
    </Card>

    <Modal
      v-model="verificationModalVisible"
      title="تایید تغییر رمز عبور"
      size="sm"
      :close-on-backdrop="false"
      @close="handleVerificationClose"
    >
      <div class="relative">
        <div
          v-if="verificationLoading"
          class="absolute inset-0 z-10 flex items-center justify-center rounded-xl bg-slate-900/60 backdrop-blur-sm"
        >
          <Spinner size="lg" />
        </div>
        <div :class="{ 'opacity-50 pointer-events-none': verificationLoading }">
          <VerificationForm
            ref="verificationFormRef"
            :auto-start="false"
            @verified="handleVerificationVerified"
          />
        </div>
      </div>
    </Modal>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onBeforeUnmount, nextTick } from 'vue'
import { Card, Button, Input, Avatar, Modal, Spinner } from '../../components/ui'
import VerificationForm from '../../components/VerificationForm.vue'
import { useAuth } from '../../composables/useAuth'
import { profileApi } from '../../api/profile'
import { useToast } from '../../composables/useToast'

const tabs = [
  {
    id: 'info',
    label: 'اطلاعات کاربری',
    description: 'ویرایش نام و تصویر پروفایل'
  },
  {
    id: 'password',
    label: 'رمز عبور',
    description: 'تغییر رمز عبور با تایید امنیتی'
  }
]

const activeTab = ref('info')
const { user, refreshUser } = useAuth()
const { showToast } = useToast()

const appEnvMeta = document.querySelector('meta[name="app-env"]')?.getAttribute('content') || ''
const isProduction = computed(() => {
  if (appEnvMeta) {
    return appEnvMeta === 'production'
  }
  return import.meta.env.MODE === 'production'
})

const currentUser = computed(() => user.value || {})
const userRoles = computed(() => currentUser.value?.roles || [])

const infoForm = reactive({
  name: '',
  email: ''
})

const infoErrors = reactive({
  name: '',
  image: ''
})

const selectedImageFile = ref(null)
const imagePreview = ref('')
const avatarFileInputRef = ref(null)
let previewObjectUrl = null

const MAX_AVATAR_BYTES = 2 * 1024 * 1024

const isSavingInfo = ref(false)

const passwordForm = reactive({
  current_password: '',
  password: '',
  password_confirmation: ''
})

const passwordErrors = reactive({
  current_password: '',
  password: '',
  password_confirmation: ''
})

const isRequestingPassword = ref(false)
const verificationModalVisible = ref(false)
const verificationLoading = ref(false)
const verificationFormRef = ref(null)

const avatarInitials = computed(() => {
  const name = infoForm.name || currentUser.value?.name || ''
  if (!name) return 'AD'
  const parts = name.trim().split(' ')
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase()
  }
  return parts[0][0].toUpperCase()
})

const existingAvatar = computed(() => {
  const image = currentUser.value?.image
  if (!image || image === 'noimage.png') {
    return ''
  }

  if (typeof image === 'string' && (image.startsWith('http://') || image.startsWith('https://'))) {
    return image
  }

  return `/uploads/${image}`
})

const displayAvatar = computed(() => imagePreview.value || existingAvatar.value)

watch(
  () => currentUser.value,
  (userData) => {
    infoForm.name = userData?.name || ''
    infoForm.email = userData?.email || ''
  },
  { immediate: true }
)

watch(selectedImageFile, (file) => {
  if (previewObjectUrl) {
    URL.revokeObjectURL(previewObjectUrl)
    previewObjectUrl = null
  }

  if (file instanceof File) {
    previewObjectUrl = URL.createObjectURL(file)
    imagePreview.value = previewObjectUrl
  } else {
    imagePreview.value = ''
  }
})

watch(verificationModalVisible, (isOpen) => {
  if (!isOpen) {
    verificationLoading.value = false
    verificationFormRef.value?.reset()
  }
})

const setActiveTab = (tabId) => {
  activeTab.value = tabId
}

const resetInfoErrors = () => {
  infoErrors.name = ''
  infoErrors.image = ''
}

const resetPasswordErrors = () => {
  passwordErrors.current_password = ''
  passwordErrors.password = ''
  passwordErrors.password_confirmation = ''
}

const resetInfoForm = () => {
  infoForm.name = currentUser.value?.name || ''
  infoErrors.name = ''
  infoErrors.image = ''
  selectedImageFile.value = null
}

const handleAvatarFileSelect = (event) => {
  const input = event.target
  const file = input.files?.[0]
  infoErrors.image = ''
  if (!file) {
    return
  }
  if (!file.type.startsWith('image/')) {
    infoErrors.image = 'فقط فایل تصویری مجاز است.'
    input.value = ''
    return
  }
  if (file.size > MAX_AVATAR_BYTES) {
    infoErrors.image = 'حداکثر حجم فایل ۲ مگابایت است.'
    input.value = ''
    return
  }
  selectedImageFile.value = file
  input.value = ''
}

const handleInfoSubmit = async () => {
  resetInfoErrors()

  if (!infoForm.name || !infoForm.name.trim()) {
    infoErrors.name = 'وارد کردن نام الزامی است.'
    return
  }

  const formData = new FormData()
  formData.append('name', infoForm.name.trim())

  if (selectedImageFile.value instanceof File) {
    formData.append('image', selectedImageFile.value)
  }

  isSavingInfo.value = true

  try {
    const response = await profileApi.updateProfileInfo(formData)

    if (response.success) {
      await refreshUser()
      selectedImageFile.value = null
      showToast(response.message || 'اطلاعات پروفایل با موفقیت بروزرسانی شد.', 'success')
    } else {
      showToast(response.message || 'خطا در بروزرسانی اطلاعات پروفایل.', 'error')
    }
  } catch (error) {
    if (error.response?.status === 422) {
      const errors = error.response.data?.errors || {}
      infoErrors.name = errors.name?.[0] || ''
      infoErrors.image = errors.image?.[0] || ''
    } else {
      const message = error.response?.data?.message || error.message || 'خطای غیرمنتظره‌ای رخ داد.'
      showToast(message, 'error')
    }
  } finally {
    isSavingInfo.value = false
  }
}

const buildPasswordPayload = () => ({
  current_password: passwordForm.current_password,
  password: passwordForm.password,
  password_confirmation: passwordForm.password_confirmation
})

const requestPasswordChange = async () => {
  resetPasswordErrors()
  try {
    const response = await profileApi.requestPasswordChange(buildPasswordPayload())
    return { success: true, data: response }
  } catch (error) {
    if (error.response?.status === 422) {
      const errors = error.response.data?.errors || {}
      passwordErrors.current_password = errors.current_password?.[0] || ''
      passwordErrors.password = errors.password?.[0] || ''
      passwordErrors.password_confirmation = errors.password_confirmation?.[0] || ''

      return {
        success: false,
        message: error.response.data?.message || 'لطفاً خطاهای زیر را اصلاح کنید.'
      }
    }

    return {
      success: false,
      message: error.response?.data?.message || error.message || 'خطا در ارسال درخواست تغییر رمز عبور.'
    }
  }
}

const openVerificationModal = async () => {
  verificationModalVisible.value = true
  await nextTick()
  if (verificationFormRef.value) {
    verificationFormRef.value.reset()
    verificationFormRef.value.startTimer()
    verificationFormRef.value.focusFirstInput()
  }
}

const handlePasswordSubmit = async () => {
  isRequestingPassword.value = true

  const result = await requestPasswordChange()

  if (result.success) {
    const requiresVerification = result.data?.requires_verification !== false
    showToast(
      result.data?.message ||
        (requiresVerification ? 'کد تایید برای شما ارسال شد.' : 'رمز عبور با موفقیت بروزرسانی شد.'),
      'success'
    )

    if (requiresVerification) {
      await openVerificationModal()
    } else {
      passwordForm.current_password = ''
      passwordForm.password = ''
      passwordForm.password_confirmation = ''
      await refreshUser()
    }
  } else {
    showToast(result.message, 'error')
  }

  isRequestingPassword.value = false
}

const handleVerificationVerified = async (payload) => {
  const code = payload?.phone_verification

  if (!code) {
    verificationFormRef.value?.setErrors?.({
      phone_verification: 'کد تایید را وارد کنید.'
    })
    return
  }

  verificationLoading.value = true

  try {
    const response = await profileApi.verifyPasswordChange({ code })

    if (response.success) {
      showToast(response.message || 'رمز عبور با موفقیت بروزرسانی شد.', 'success')
      passwordForm.current_password = ''
      passwordForm.password = ''
      passwordForm.password_confirmation = ''
      handleVerificationClose()
    } else {
      const message = response.message || 'کد تایید نامعتبر است.'
      verificationFormRef.value?.setErrors?.({
        phone_verification: message
      })
    }
  } catch (error) {
    if (error.response?.status === 422) {
      const message =
        error.response.data?.errors?.code?.[0] ||
        error.response.data?.message ||
        'کد تایید نامعتبر است.'
      verificationFormRef.value?.setErrors?.({
        phone_verification: message
      })
    } else {
      const message = error.response?.data?.message || error.message || 'خطایی در تایید کد رخ داد.'
      showToast(message, 'error')
    }
  } finally {
    verificationLoading.value = false
  }
}

const handleVerificationClose = () => {
  verificationModalVisible.value = false
  verificationLoading.value = false
  verificationFormRef.value?.reset()
}

onBeforeUnmount(() => {
  if (previewObjectUrl) {
    URL.revokeObjectURL(previewObjectUrl)
  }
})
</script>

<style scoped>
.text-primary {
  color: var(--theme-text-primary, #F8FAFC);
}

.text-secondary {
  color: var(--theme-text-secondary, #CBD5E1);
}

.text-muted {
  color: var(--theme-text-muted, #64748B);
}

.hover-text-primary:hover {
  color: var(--theme-text-primary, #F8FAFC);
}
</style>


