<template>
  <Modal
    :model-value="show"
    @update:model-value="$emit('close')"
    title="ویرایش مسئولیت"
    size="xl"
  >
    <div v-if="loading" class="flex justify-center py-8">
      <Spinner size="lg" />
    </div>

    <div v-else-if="fetchError" class="py-4">
      <Alert variant="danger" :message="fetchError" />
    </div>

    <div v-else-if="role" class="space-y-4" dir="rtl">
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

      <div>
        <p class="text-sm font-medium text-[var(--theme-text-primary)] mb-2">
          دسترسی های اختصاص داده شده به این مسئولیت:
        </p>

        <div v-if="role.permissions.length === 0" class="py-4">
          <Alert variant="warning" message="دسترسی ای تعریف نشده است!" />
        </div>

        <ul v-else class="space-y-2 mb-4">
          <li
            v-for="permission in role.permissions"
            :key="permission.id"
            class="flex items-center justify-between p-3 bg-[var(--theme-bg-glass)] rounded-lg border border-[var(--theme-border)]"
          >
            <span class="text-sm text-[var(--theme-text-primary)]">{{ permission.title }}</span>
            <Button
              size="sm"
              variant="danger"
              rounded="full"
              class="!p-2 !gap-0 min-w-[2.25rem]"
              title="حذف"
              aria-label="حذف دسترسی از مسئولیت"
              @click="handleRemovePermission(permission.id)"
            >
              <template #icon-left>
                <TableActionIcon name="delete" />
              </template>
            </Button>
          </li>
        </ul>
      </div>

      <p class="text-sm text-[var(--theme-text-secondary)] mb-4">
        کدام دسترسی ها را به این مسئولیت می دهید؟
      </p>

      <div v-if="availablePermissions.length === 0" class="py-4">
        <Alert variant="warning" message="دسترسی ای تعریف نشده است!" />
      </div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <div
          v-for="permission in availablePermissions"
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
  </Modal>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import apiClient from '../../utils/api'
import { Modal, Input, Button, Spinner, Alert } from '../ui'
import { notifySuccess, notifyError, confirm } from '../../utils/notifications'
import TableActionIcon from '../icons/TableActionIcon.vue'
import { useModalForm } from '../../composables/useModalForm'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  roleId: {
    type: Number,
    required: true
  }
})

const emit = defineEmits(['close', 'updated'])

const {
  loading,
  saving,
  error: fetchError,
  errors,
  form: formData
} = useModalForm(() => ({
  title: '',
  name: ''
}))
const role = ref(null)
const availablePermissions = ref([])
const selectedPermissions = ref([])

const fetchRoleDetails = async () => {
  if (!props.roleId) {
    return
  }

  try {
    loading.value = true
    fetchError.value = null

    const response = await apiClient.get(`/roles/${props.roleId}`)

    if (response.data.success) {
      role.value = response.data.data.role
      availablePermissions.value = response.data.data.available_permissions
      formData.value = {
        title: role.value.title,
        name: role.value.name
      }
      selectedPermissions.value = []
    } else {
      fetchError.value = 'خطا در دریافت اطلاعات'
    }
  } catch (err) {
    console.error('Role fetch error:', err)
    fetchError.value = err.response?.data?.message || 'خطا در بارگذاری اطلاعات'
  } finally {
    loading.value = false
  }
}

const handleRemovePermission = async (permissionId) => {
  const result = await confirm(
    'آیا می خواهید این دسترسی را حذف کنید؟',
    'حذف دسترسی',
    { confirmText: 'بله، حذف شود', cancelText: 'انصراف' }
  )
  if (!result.isConfirmed) return

  try {
    await apiClient.delete(`/roles/${props.roleId}/permissions/${permissionId}`)
    await notifySuccess('دسترسی با موفقیت حذف شد')
    fetchRoleDetails()
  } catch (err) {
    console.error('Remove permission error:', err)
    await notifyError(err.response?.data?.message || 'خطا در حذف دسترسی')
  }
}

const submitRoleUpdate = async () => {
  const existingPermissionNames = role.value.permissions.map(p => p.name)
  const newPermissionNames = selectedPermissions.value
  const allPermissions = [...new Set([...existingPermissionNames, ...newPermissionNames])]

  try {
    saving.value = true
    fetchError.value = null

    const response = await apiClient.put(`/roles/${props.roleId}`, {
      title: formData.value.title.trim(),
      name: formData.value.name.trim(),
      permissions: allPermissions
    })

    if (response.data.success) {
      await notifySuccess('اطلاعات با موفقیت ثبت شد')
      emit('updated')
    } else {
      fetchError.value = 'خطا در ثبت اطلاعات'
    }
  } catch (err) {
    console.error('Update role error:', err)

    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors
    } else {
      fetchError.value = err.response?.data?.message || 'خطا در ثبت اطلاعات'
    }
  } finally {
    saving.value = false
  }
}

const handleSave = async () => {
  errors.value = {}

  if (!formData.value.title || formData.value.title.trim() === '') {
    errors.value.title = 'عنوان مسئولیت الزامی است'
    return
  }

  if (!formData.value.name || formData.value.name.trim() === '') {
    errors.value.name = 'نام مسئولیت الزامی است'
    return
  }

  await submitRoleUpdate()
}

watch(() => props.show, (newVal) => {
  if (newVal && props.roleId) {
    fetchRoleDetails()
  } else if (!newVal) {
    role.value = null
    availablePermissions.value = []
    formData.value = { title: '', name: '' }
    selectedPermissions.value = []
    errors.value = {}
    fetchError.value = null
  }
})

watch(() => props.roleId, (newVal) => {
  if (newVal && props.show) {
    fetchRoleDetails()
  }
})

onMounted(() => {
  if (props.show && props.roleId) {
    fetchRoleDetails()
  }
})
</script>

<style scoped>
/* Additional styles if needed */
</style>
