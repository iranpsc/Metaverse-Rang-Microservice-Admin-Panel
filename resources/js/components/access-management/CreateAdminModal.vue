<template>
  <Modal
    :model-value="show"
    @update:model-value="onMainModalClose"
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
          @click="onMainModalClose"
        >
          بستن
        </Button>
      </div>
    </template>
  </Modal>
</template>

<script setup>
import { ref, watch } from 'vue'
import apiClient from '../../utils/api'
import { Modal, Select, Button, Spinner, Alert } from '../ui'
import { notifySuccess } from '../../utils/notifications'

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

const validateForm = () => {
  errors.value = {}

  if (!formData.value.employee) {
    errors.value.employee = 'انتخاب کارمند الزامی است'
    return false
  }

  if (selectedRoles.value.length === 0) {
    errors.value.roles = 'حداقل یک مسئولیت باید انتخاب شود'
    return false
  }

  return true
}

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

const submitAdminCreate = async () => {
  try {
    saving.value = true
    error.value = null

    const response = await apiClient.post('/admins', {
      employee: formData.value.employee,
      roles: selectedRoles.value
    })

    if (response.data.success) {
      await notifySuccess('اطلاعات با موفقیت ثبت شد')
      resetForm()
      emit('created')
    } else {
      error.value = 'خطا در ثبت اطلاعات'
    }
  } catch (err) {
    console.error('Create admin error:', err)
    error.value = err.response?.data?.message || 'خطا در ثبت اطلاعات'
  } finally {
    saving.value = false
  }
}

const handleSave = async () => {
  if (!validateForm()) {
    return
  }

  await submitAdminCreate()
}

const resetForm = () => {
  formData.value = {
    employee: ''
  }
  selectedRoles.value = []
  errors.value = {}
  error.value = null
}

const onMainModalClose = () => {
  emit('close')
}

watch(() => props.show, (newVal) => {
  if (newVal) {
    resetForm()
    fetchEmployees()
    fetchRoles()
  }
})
</script>
