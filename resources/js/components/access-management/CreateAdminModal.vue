<template>
  <Modal
    :model-value="show"
    @update:model-value="$emit('close')"
    title="ایجاد کاربر"
    size="md"
  >
    <div v-if="loading" class="flex justify-center py-8">
      <Spinner size="lg" />
    </div>

    <div v-else-if="error" class="py-4">
      <Alert variant="danger" :message="error" />
    </div>

    <div v-else class="space-y-4" dir="rtl">
      <Select
        v-model="formData.employee"
        label="انتخاب کارمند"
        :options="employees"
        option-label="name"
        option-value="id"
        required
        :error="errors.employee"
      >
        <option value="">انتخاب کنید</option>
      </Select>

      <div v-if="errors.roles" class="text-sm text-error">
        {{ errors.roles }}
      </div>

      <p class="text-sm text-[var(--theme-text-secondary)] mb-4">
        کدام مسئولیت ها را به این کارمند می دهید؟
      </p>

      <div v-if="roles.length === 0" class="py-4">
        <Alert variant="warning" message="مسئولیتی تعریف نشده است" />
      </div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
        <div
          v-for="role in roles"
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
import { ref, watch, computed, nextTick } from 'vue'
import apiClient from '../../utils/api'
import { Modal, Select, Button, Spinner, Alert } from '../ui'
import VerificationForm from '../VerificationForm.vue'
import { notifySuccess, notifyError } from '../../utils/notifications'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'created'])

const loading = ref(false)
const saving = ref(false)
const error = ref(null)
const employees = ref([])
const roles = ref([])
const formData = ref({
  employee: ''
})
const selectedRoles = ref([])
const errors = ref({})
const verificationFormRef = ref(null)
const showVerificationDialog = ref(false)

const isProduction = computed(() => {
  const metaEnv = document.querySelector('meta[name="app-env"]')?.getAttribute('content')
  return metaEnv === 'production' || import.meta.env.MODE === 'production'
})

const fetchEmployees = async () => {
  try {
    const response = await apiClient.get('/admins/employees')
    if (response.data.success) {
      employees.value = response.data.data.employees
    }
  } catch (err) {
    console.error('Employees fetch error:', err)
  }
}

const fetchRoles = async () => {
  try {
    const response = await apiClient.get('/admins/roles')
    if (response.data.success) {
      roles.value = response.data.data.roles
    }
  } catch (err) {
    console.error('Roles fetch error:', err)
  }
}

const sendVerificationCode = async () => {
  try {
    const response = await apiClient.post('/send-verification-sms')

    if (response.data.success) {
      showVerificationDialog.value = true
      return true
    } else {
      await notifyError('خطا در ارسال کد تایید')
      return false
    }
  } catch (err) {
    console.error('Verification SMS send error:', err)
    await notifyError(err.response?.data?.message || 'خطا در ارسال کد تایید')
    return false
  } finally {
    saving.value = false
  }
}

const submitAdminCreate = async (verificationData = {}) => {
  try {
    saving.value = true
    error.value = null

    const response = await apiClient.post('/admins', {
      employee: formData.value.employee,
      roles: selectedRoles.value,
      ...verificationData
    })

    if (response.data.success) {
      await notifySuccess('اطلاعات با موفقیت ثبت شد')
      showVerificationDialog.value = false
      if (verificationFormRef.value && verificationFormRef.value.setErrors) {
        verificationFormRef.value.setErrors({})
      }
      resetForm()
      emit('created')
    } else {
      error.value = 'خطا در ثبت اطلاعات'
    }
  } catch (err) {
    console.error('Create admin error:', err)
    error.value = err.response?.data?.message || 'خطا در ثبت اطلاعات'

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
  errors.value = {}

  // Validation
  if (!formData.value.employee) {
    errors.value.employee = 'انتخاب کارمند الزامی است'
    return
  }

  if (selectedRoles.value.length === 0) {
    errors.value.roles = 'حداقل یک مسئولیت باید انتخاب شود'
    return
  }

  saving.value = true

  if (isProduction.value) {
    const codeSent = await sendVerificationCode()
    if (!codeSent) {
      return
    }
  } else {
    await submitAdminCreate()
  }
}

const handleAutoVerifyAndSubmit = async (verificationData) => {
  if (saving.value) {
    return
  }

  if (!verificationFormRef.value) {
    await notifyError('خطا در تایید')
    return
  }

  const data = verificationData || verificationFormRef.value.getData()
  await submitAdminCreate(data)
}

const handleCloseVerificationDialog = () => {
  if (verificationFormRef.value && verificationFormRef.value.setErrors) {
    verificationFormRef.value.setErrors({})
  }
  showVerificationDialog.value = false
}

const resetForm = () => {
  formData.value = {
    employee: ''
  }
  selectedRoles.value = []
  errors.value = {}
  error.value = null
}

watch(() => props.show, (newVal) => {
  if (newVal) {
    resetForm()
    fetchEmployees()
    fetchRoles()
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

