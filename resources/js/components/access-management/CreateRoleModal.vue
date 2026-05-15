<template>
  <Modal
    :model-value="show"
    @update:model-value="$emit('close')"
    title="ایجاد مسئولیت"
    size="xl"
  >
    <div v-if="loading" class="flex justify-center py-8">
      <Spinner size="lg" />
    </div>

    <div v-else-if="error" class="py-4">
      <Alert variant="danger" :message="error" />
    </div>

    <div v-else class="space-y-4" dir="rtl">
      <Input
        v-model="formData.title"
        label="عنوان مسئولیت"
        required
        :error="errors.title"
      />

      <Input
        v-model="formData.name"
        label="نام مسئولیت"
        required
        :error="errors.name"
      />

      <p class="text-sm text-[var(--theme-text-secondary)] mb-4">
        کدام دسترسی ها را به این مسئولیت می دهید؟
      </p>

      <div v-if="permissions.length === 0" class="py-4">
        <Alert variant="warning" message="دسترسی ای تعریف نشده است" />
      </div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <div
          v-for="permission in permissions"
          :key="permission.id"
          class="flex items-center space-x-2 space-x-reverse"
        >
          <input
            :id="`permission-${permission.id}`"
            v-model="selectedPermissions"
            :value="permission.name"
            type="checkbox"
            class="w-4 h-4 rounded border-[var(--theme-border)] focus:ring-primary-500 text-primary-600 bg-[var(--theme-bg-elevated)]"
          />
          <label
            :for="`permission-${permission.id}`"
            class="text-sm text-[var(--theme-text-primary)] cursor-pointer"
          >
            {{ permission.title }}
          </label>
        </div>
      </div>
    </div>

    <template #footer>
      <div class="flex gap-3 justify-end" dir="rtl">
        <Button
          v-if="isProduction && !isVerified"
          variant="primary"
          :loading="sendingVerification"
          @click="handleSendCode"
        >
          ارسال کد تایید
        </Button>
        <Button
          v-if="!isProduction || isVerified"
          variant="primary"
          :loading="saving"
          @click="handleSave"
        >
          ثبت
        </Button>
        <Button
          variant="danger"
          @click="onMainModalClose"
        >
          بستن
        </Button>
      </div>
    </template>
  </Modal>

  <PhoneVerificationModal :phone-verification="phoneVerification" />
</template>

<script setup>
import { ref, watch } from 'vue'
import apiClient from '../../utils/api'
import { Modal, Input, Button, Spinner, Alert } from '../ui'
import PhoneVerificationModal from '../PhoneVerificationModal.vue'
import { usePhoneVerification } from '../../composables/usePhoneVerification'
import { notifySuccess } from '../../utils/notifications'
import { useModalForm } from '../../composables/useModalForm'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'created'])

const phoneVerification = usePhoneVerification()
const {
  isProduction,
  isVerified,
  sendingVerification,
  beginVerifyForSubmit,
  getSubmitPayload,
  handleApiVerificationError,
  resetVerificationState
} = phoneVerification

const {
  loading,
  saving,
  error,
  errors,
  form: formData
} = useModalForm(() => ({
  title: '',
  name: ''
}))
const permissions = ref([])
const selectedPermissions = ref([])

const fetchPermissions = async () => {
  try {
    loading.value = true
    error.value = null

    const response = await apiClient.get('/roles/permissions')

    if (response.data.success) {
      permissions.value = response.data.data.permissions
    } else {
      error.value = 'خطا در دریافت دسترسی‌ها'
    }
  } catch (err) {
    console.error('Permissions fetch error:', err)
    error.value = err.response?.data?.message || 'خطا در بارگذاری دسترسی‌ها'
  } finally {
    loading.value = false
  }
}

const validateForm = () => {
  errors.value = {}

  if (!formData.value.title || formData.value.title.trim() === '') {
    errors.value.title = 'عنوان مسئولیت الزامی است'
    return false
  }

  if (!formData.value.name || formData.value.name.trim() === '') {
    errors.value.name = 'نام مسئولیت الزامی است'
    return false
  }

  return true
}

const submitRoleCreate = async (verificationPayload = {}) => {
  try {
    saving.value = true
    error.value = null

    const response = await apiClient.post('/roles', {
      title: formData.value.title.trim(),
      name: formData.value.name.trim(),
      permissions: selectedPermissions.value,
      ...verificationPayload
    })

    if (response.data.success) {
      await notifySuccess('اطلاعات با موفقیت ثبت شد')
      resetVerificationState()
      resetForm()
      emit('created')
    } else {
      error.value = 'خطا در ثبت اطلاعات'
    }
  } catch (err) {
    console.error('Create role error:', err)

    if (await handleApiVerificationError(err)) {
      return
    }

    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors
    } else {
      error.value = err.response?.data?.message || 'خطا در ثبت اطلاعات'
    }
  } finally {
    saving.value = false
  }
}

const handleSendCode = async () => {
  if (!validateForm()) {
    return
  }

  await beginVerifyForSubmit()
}

const handleSave = async () => {
  if (!validateForm()) {
    return
  }

  if (isProduction.value) {
    await submitRoleCreate(getSubmitPayload())
  } else {
    await submitRoleCreate()
  }
}

const onMainModalClose = () => {
  resetVerificationState()
  emit('close')
}

const resetForm = () => {
  selectedPermissions.value = []
  formData.value = {
    title: '',
    name: ''
  }
  errors.value = {}
  error.value = null
}

watch(() => props.show, (newVal) => {
  if (newVal) {
    resetForm()
    resetVerificationState()
    fetchPermissions()
  } else {
    resetVerificationState()
  }
})
</script>

<style scoped>
/* Additional styles if needed */
</style>

