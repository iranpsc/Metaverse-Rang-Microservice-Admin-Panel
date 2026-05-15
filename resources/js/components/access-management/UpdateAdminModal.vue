<template>
  <Modal
    :model-value="show"
    @update:model-value="$emit('close')"
    title="ویرایش دسترسی ها و مسئولیت های کارمند"
    size="md"
  >
    <div v-if="loading" class="flex justify-center py-8">
      <Spinner size="lg" />
    </div>

    <div v-else-if="fetchError" class="py-4">
      <Alert variant="danger" :message="fetchError" />
    </div>

    <div v-else-if="admin" class="space-y-4" dir="rtl">
      <div>
        <p class="text-sm font-medium text-[var(--theme-text-primary)] mb-2">
          مسئولیت های اختصاص داده شده به این کارمند:
        </p>

        <div v-if="admin.roles.length === 0" class="py-4">
          <Alert variant="info" message="هیچ مسئولیتی به این کارمند اختصاص داده نشده است!" />
        </div>

        <ul v-else class="space-y-2 mb-4">
          <li
            v-for="role in admin.roles"
            :key="role.id"
            class="flex items-center justify-between p-3 bg-[var(--theme-bg-glass)] rounded-lg border border-[var(--theme-border)]"
          >
            <span class="text-sm text-[var(--theme-text-primary)]">{{ role.title }}</span>
            <Button
              size="sm"
              variant="danger"
              rounded="full"
              class="!p-2 !gap-0 min-w-[2.25rem]"
              title="حذف"
              aria-label="حذف مسئولیت از کارمند"
              @click="handleRemoveRole(role.id)"
            >
              <template #icon-left>
                <TableActionIcon name="delete" />
              </template>
            </Button>
          </li>
        </ul>
      </div>

      <p class="text-sm text-[var(--theme-text-secondary)] mb-4">
        کدام مسئولیت ها را به این کارمند اضافه می کنید؟
      </p>

      <div v-if="availableRoles.length === 0" class="py-4">
        <Alert variant="warning" message="مسئولیتی تعریف نشده است" />
      </div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
        <div
          v-for="role in availableRoles"
          :key="role.id"
          class="flex items-center space-x-2 space-x-reverse"
        >
          <input
            :id="`role-${role.id}`"
            v-model="selectedRoles"
            :value="role.id"
            type="checkbox"
            class="w-4 h-4 rounded border-[var(--theme-border)] focus:ring-primary-500 text-primary-600 bg-[var(--theme-bg-elevated)]"
          />
          <label
            :for="`role-${role.id}`"
            class="text-sm text-[var(--theme-text-primary)] cursor-pointer"
          >
            {{ role.title }}
          </label>
        </div>
      </div>
    </div>

    <template #footer>
      <div class="flex gap-3 justify-end" dir="rtl">
        <Button
          variant="primary"
          :loading="saving"
          @click="handleSave"
        >
          ثبت
        </Button>
        <Button
          variant="danger"
          @click="$emit('close')"
        >
          بستن
        </Button>
      </div>
    </template>

    <!-- Verification Dialog -->
    <Modal
      :model-value="showVerificationDialog"
      @update:model-value="handleCloseVerificationDialog"
      @close="handleCloseVerificationDialog"
      title="تایید نهایی"
      size="md"
    >
      <div dir="rtl">
        <VerificationForm
          ref="verificationFormRef"
          :auto-start="true"
          @verified="handleAutoVerifyAndSubmit"
        />
      </div>
    </Modal>
  </Modal>
</template>

<script setup>
import { ref, watch, computed, nextTick, onMounted } from 'vue'
import apiClient from '../../utils/api'
import { Modal, Button, Spinner, Alert } from '../ui'
import VerificationForm from '../VerificationForm.vue'
import { notifySuccess, notifyError, confirm } from '../../utils/notifications'
import TableActionIcon from '../icons/TableActionIcon.vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  adminId: {
    type: Number,
    required: true
  }
})

const emit = defineEmits(['close', 'updated'])

const loading = ref(false)
const saving = ref(false)
const fetchError = ref(null)
const admin = ref(null)
const availableRoles = ref([])
const selectedRoles = ref([])
const verificationFormRef = ref(null)
const showVerificationDialog = ref(false)

const isProduction = computed(() => {
  const metaEnv = document.querySelector('meta[name="app-env"]')?.getAttribute('content')
  return metaEnv === 'production' || import.meta.env.MODE === 'production'
})

const fetchAdminDetails = async () => {
  if (!props.adminId) {
    return
  }

  try {
    loading.value = true
    fetchError.value = null

    const response = await apiClient.get(`/admins/${props.adminId}`)

    if (response.data.success) {
      admin.value = response.data.data.admin
      availableRoles.value = response.data.data.available_roles
      selectedRoles.value = []
    } else {
      fetchError.value = 'خطا در دریافت اطلاعات'
    }
  } catch (err) {
    console.error('Admin fetch error:', err)
    fetchError.value = err.response?.data?.message || 'خطا در بارگذاری اطلاعات'
  } finally {
    loading.value = false
  }
}

const handleRemoveRole = async (roleId) => {
  const result = await confirm(
    'آیا می خواهید این مسیولیت را حذف کنید؟',
    'تایید حذف مسئولیت',
    {
      confirmText: 'بله، حذف شود',
      cancelText: 'انصراف'
    }
  )

  if (!result.isConfirmed) {
    return
  }

  try {
    await apiClient.delete(`/admins/${props.adminId}/roles/${roleId}`)
    await notifySuccess('مسئولیت با موفقیت حذف شد')
    fetchAdminDetails()
  } catch (err) {
    console.error('Remove role error:', err)
    await notifyError(err.response?.data?.message || 'خطا در حذف مسئولیت')
  }
}

const sendVerificationCode = async () => {
  try {
    saving.value = true
    const response = await apiClient.post('/send-verification-sms')

    if (response.data.success) {
      showVerificationDialog.value = true
      // Don't set saving.value to false here - keep it true until verification completes
      return true
    } else {
      await notifyError('خطا در ارسال کد تایید')
      saving.value = false
      return false
    }
  } catch (err) {
    console.error('Verification SMS send error:', err)
    await notifyError(err.response?.data?.message || 'خطا در ارسال کد تایید')
    saving.value = false
    return false
  }
}

const submitAdminUpdate = async (verificationData = {}) => {
  try {
    saving.value = true
    fetchError.value = null

    const response = await apiClient.put(`/admins/${props.adminId}`, {
      roles: selectedRoles.value,
      ...verificationData
    })

    if (response.data.success) {
      await notifySuccess('اطلاعات با موفقیت ثبت شد')
      showVerificationDialog.value = false
      if (verificationFormRef.value && verificationFormRef.value.setErrors) {
        verificationFormRef.value.setErrors({})
      }
      emit('updated')
    } else {
      fetchError.value = 'خطا در ثبت اطلاعات'
    }
  } catch (err) {
    console.error('Update admin error:', err)
    fetchError.value = err.response?.data?.message || 'خطا در ثبت اطلاعات'

    if (err.response?.data?.errors && verificationFormRef.value && verificationFormRef.value.setErrors) {
      const validationErrors = {}
      if (err.response.data.errors.phone_verification) {
        validationErrors.phone_verification = Array.isArray(err.response.data.errors.phone_verification)
          ? err.response.data.errors.phone_verification[0]
          : err.response.data.errors.phone_verification
      }
      verificationFormRef.value.setErrors(validationErrors)
    }
  } finally {
    saving.value = false
  }
}

const handleSave = async () => {
  if (isProduction.value) {
    const codeSent = await sendVerificationCode()
    if (!codeSent) {
      return
    }
    // saving.value is set to true in sendVerificationCode and kept true until verification completes
  } else {
    await submitAdminUpdate()
  }
}

const handleAutoVerifyAndSubmit = async (verificationData) => {
  // Prevent double submission - only proceed if we're in the verification flow (saving is true from sendVerificationCode)
  if (!saving.value) {
    console.warn('handleAutoVerifyAndSubmit called but saving is false - possible duplicate call')
    return
  }

  if (!verificationFormRef.value && !verificationData) {
    await notifyError('خطا در تایید')
    saving.value = false
    return
  }

  const data = verificationData || verificationFormRef.value?.getData()
  await submitAdminUpdate(data)
}

const handleCloseVerificationDialog = () => {
  if (verificationFormRef.value && verificationFormRef.value.setErrors) {
    verificationFormRef.value.setErrors({})
  }
  showVerificationDialog.value = false
  // Reset saving state if user closes dialog without completing verification
  saving.value = false
}

// Watch for when modal opens and fetch data
watch(() => props.show, (newVal) => {
  if (newVal && props.adminId) {
    fetchAdminDetails()
  } else if (!newVal) {
    // Reset data when modal closes
    admin.value = null
    availableRoles.value = []
    selectedRoles.value = []
    fetchError.value = null
  }
})

// Watch for adminId changes (when switching between admins)
watch(() => props.adminId, (newVal) => {
  if (newVal && props.show) {
    fetchAdminDetails()
  }
})

// Fetch data when component is mounted (since parent uses v-if, component only exists when needed)
onMounted(() => {
  // Fetch immediately when component mounts if show is true
  if (props.show && props.adminId) {
    fetchAdminDetails()
  }
})

watch(() => showVerificationDialog, async (newVal) => {
  if (newVal) {
    await nextTick()
    setTimeout(() => {
      if (verificationFormRef.value && verificationFormRef.value.startTimer) {
        verificationFormRef.value.startTimer()
      }
    }, 100)
    setTimeout(() => {
      if (verificationFormRef.value && verificationFormRef.value.focusFirstInput) {
        verificationFormRef.value.focusFirstInput()
      }
    }, 400)
    setTimeout(() => {
      if (verificationFormRef.value && verificationFormRef.value.focusFirstInput) {
        verificationFormRef.value.focusFirstInput()
      }
    }, 600)
  } else {
    if (verificationFormRef.value && verificationFormRef.value.reset) {
      verificationFormRef.value.reset()
    }
  }
})
</script>

<style scoped>
/* Additional styles if needed */
</style>

