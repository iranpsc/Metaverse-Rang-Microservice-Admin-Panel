<template>
  <Modal
    :model-value="show"
    @update:model-value="onClose"
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
          @click="onClose"
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
import { Modal, Button, Spinner, Alert } from '../ui'
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
    { confirmText: 'بله، حذف شود', cancelText: 'انصراف' }
  )
  if (!result.isConfirmed) return

  try {
    await apiClient.delete(`/admins/${props.adminId}/roles/${roleId}`)
    await notifySuccess('مسئولیت با موفقیت حذف شد')
    fetchAdminDetails()
  } catch (err) {
    console.error('Remove role error:', err)
    await notifyError(err.response?.data?.message || 'خطا در حذف مسئولیت')
  }
}

const submitAdminUpdate = async () => {
  try {
    saving.value = true
    fetchError.value = null

    const response = await apiClient.put(`/admins/${props.adminId}`, {
      roles: selectedRoles.value
    })

    if (response.data.success) {
      await notifySuccess('اطلاعات با موفقیت ثبت شد')
      emit('updated')
    } else {
      fetchError.value = 'خطا در ثبت اطلاعات'
    }
  } catch (err) {
    console.error('Update admin error:', err)
    fetchError.value = err.response?.data?.message || 'خطا در ثبت اطلاعات'
  } finally {
    saving.value = false
  }
}

const handleSave = async () => {
  await submitAdminUpdate()
}

const onClose = () => {
  emit('close')
}

watch(() => props.show, (newVal) => {
  if (newVal && props.adminId) {
    fetchAdminDetails()
  } else if (!newVal) {
    admin.value = null
    availableRoles.value = []
    selectedRoles.value = []
    fetchError.value = null
  }
})

watch(() => props.adminId, (newVal) => {
  if (newVal && props.show) {
    fetchAdminDetails()
  }
})

onMounted(() => {
  if (props.show && props.adminId) {
    fetchAdminDetails()
  }
})
</script>
