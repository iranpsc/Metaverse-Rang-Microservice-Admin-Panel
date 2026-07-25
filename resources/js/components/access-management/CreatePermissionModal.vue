<template>
  <Modal
    :model-value="show"
    @update:model-value="$emit('close')"
    title="ایجاد دسترسی"
    size="md"
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
        label="عنوان دسترسی"
        required
        :error="errors.title"
      />

      <Input
        v-model="formData.name"
        label="نام دسترسی"
        required
        :error="errors.name"
      />

      <p class="text-sm text-[var(--theme-text-secondary)] mb-4">
        به کدام مسئولیت ها این دسترسی را می دهید؟
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
            :value="role.name"
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
import { Modal, Input, Button, Spinner, Alert } from '../ui'
import { notifySuccess } from '../../utils/notifications'
import { useModalForm } from '../../composables/useModalForm'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'created'])

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
const roles = ref([])
const selectedRoles = ref([])

const fetchRoles = async () => {
  try {
    loading.value = true
    error.value = null

    const response = await apiClient.get('/permissions/roles')

    if (response.data.success) {
      roles.value = response.data.data.roles
    } else {
      error.value = 'خطا در دریافت مسئولیت‌ها'
    }
  } catch (err) {
    console.error('Roles fetch error:', err)
    error.value = err.response?.data?.message || 'خطا در بارگذاری مسئولیت‌ها'
  } finally {
    loading.value = false
  }
}

const validateForm = () => {
  errors.value = {}

  if (!formData.value.title || formData.value.title.trim() === '') {
    errors.value.title = 'عنوان دسترسی الزامی است'
    return false
  }

  if (!formData.value.name || formData.value.name.trim() === '') {
    errors.value.name = 'نام دسترسی الزامی است'
    return false
  }

  return true
}

const submitPermissionCreate = async () => {
  try {
    saving.value = true
    error.value = null

    const response = await apiClient.post('/permissions', {
      title: formData.value.title.trim(),
      name: formData.value.name.trim(),
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
    console.error('Create permission error:', err)

    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors
    } else {
      error.value = err.response?.data?.message || 'خطا در ثبت اطلاعات'
    }
  } finally {
    saving.value = false
  }
}

const handleSave = async () => {
  if (!validateForm()) {
    return
  }

  await submitPermissionCreate()
}

const onMainModalClose = () => {
  emit('close')
}

const resetForm = () => {
  formData.value = {
    title: '',
    name: ''
  }
  selectedRoles.value = []
  errors.value = {}
  error.value = null
}

watch(() => props.show, (newVal) => {
  if (newVal) {
    resetForm()
    fetchRoles()
  }
})
</script>

<style scoped>
/* Additional styles if needed */
</style>
