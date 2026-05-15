<template>
  <Modal
    :model-value="show"
    @update:model-value="$emit('close')"
    title="ویرایش دسترسی"
    size="md"
  >
    <div v-if="loading" class="flex justify-center py-8">
      <Spinner size="lg" />
    </div>

    <div v-else-if="fetchError" class="py-4">
      <Alert variant="danger" :message="fetchError" />
    </div>

    <div v-else-if="permission" class="space-y-4" dir="rtl">
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

      <div>
        <p class="text-sm font-medium text-[var(--theme-text-primary)] mb-2">
          مسئولیت های دارای این دسترسی:
        </p>

        <div v-if="permission.roles.length === 0" class="py-4">
          <Alert variant="warning" message="مسئولیتی تعریف نشده است" />
        </div>

        <ul v-else class="space-y-2 mb-4">
          <li
            v-for="role in permission.roles"
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
              aria-label="حذف مسئولیت از دسترسی"
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
        به کدام مسئولیت ها این دسترسی را اضافه می کنید؟
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
          @click="$emit('close')"
        >
          بستن
        </Button>
      </div>
    </template>
  </Modal>

  <PhoneVerificationModal :phone-verification="phoneVerification" />
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import apiClient from '../../utils/api'
import { Modal, Input, Button, Spinner, Alert } from '../ui'
import PhoneVerificationModal from '../PhoneVerificationModal.vue'
import { usePhoneVerification } from '../../composables/usePhoneVerification'
import { notifySuccess, notifyError } from '../../utils/notifications'
import TableActionIcon from '../icons/TableActionIcon.vue'
import { useModalForm } from '../../composables/useModalForm'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  permissionId: {
    type: Number,
    required: true
  }
})

const emit = defineEmits(['close', 'updated'])

const phoneVerification = usePhoneVerification()
const {
  isProduction,
  isVerified,
  beginVerifyForSubmit,
  getSubmitPayload,
  confirmThenVerify,
  handleApiVerificationError,
  resetVerificationState
} = phoneVerification

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
const permission = ref(null)
const availableRoles = ref([])
const selectedRoles = ref([])

const fetchPermissionDetails = async () => {
  try {
    loading.value = true
    fetchError.value = null

    const response = await apiClient.get(`/permissions/${props.permissionId}`)

    if (response.data.success) {
      permission.value = response.data.data.permission
      availableRoles.value = response.data.data.available_roles
      formData.value = {
        title: permission.value.title,
        name: permission.value.name
      }
      selectedRoles.value = []
    } else {
      fetchError.value = 'خطا در دریافت اطلاعات'
    }
  } catch (err) {
    console.error('Permission fetch error:', err)
    fetchError.value = err.response?.data?.message || 'خطا در بارگذاری اطلاعات'
  } finally {
    loading.value = false
  }
}

const handleRemoveRole = async (roleId) => {
  await confirmThenVerify(
    {
      message: 'آیا می خواهید این مسئولیت را حذف کنید؟',
      title: 'تایید حذف مسئولیت',
      confirmText: 'بله، حذف شود',
      cancelText: 'انصراف'
    },
    async (payload) => {
      try {
        await apiClient.delete(`/permissions/${props.permissionId}/roles/${roleId}`, { data: payload })
        await notifySuccess('مسئولیت با موفقیت حذف شد')
        fetchPermissionDetails()
      } catch (err) {
        console.error('Remove role error:', err)

        if (await handleApiVerificationError(err)) {
          return
        }

        await notifyError(err.response?.data?.message || 'خطا در حذف مسئولیت')
      }
    }
  )
}

const submitPermissionUpdate = async (verificationPayload = {}) => {
  try {
    saving.value = true
    fetchError.value = null

    const response = await apiClient.put(`/permissions/${props.permissionId}`, {
      title: formData.value.title.trim(),
      name: formData.value.name.trim(),
      roles: selectedRoles.value,
      ...verificationPayload
    })

    if (response.data.success) {
      await notifySuccess('اطلاعات با موفقیت ثبت شد')
      resetVerificationState()
      emit('updated')
    } else {
      fetchError.value = 'خطا در ثبت اطلاعات'
    }
  } catch (err) {
    console.error('Update permission error:', err)

    if (await handleApiVerificationError(err)) {
      return
    }

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
    errors.value.title = 'عنوان دسترسی الزامی است'
    return
  }

  if (!formData.value.name || formData.value.name.trim() === '') {
    errors.value.name = 'نام دسترسی الزامی است'
    return
  }

  if (isProduction.value && !isVerified.value) {
    await beginVerifyForSubmit()
    return
  }

  await submitPermissionUpdate(getSubmitPayload())
}

watch(() => props.show, (newVal) => {
  if (newVal && props.permissionId) {
    fetchPermissionDetails()
  } else if (!newVal) {
    permission.value = null
    availableRoles.value = []
    formData.value = { title: '', name: '' }
    selectedRoles.value = []
    errors.value = {}
    fetchError.value = null
    resetVerificationState()
  }
})

watch(() => props.permissionId, (newVal) => {
  if (newVal && props.show) {
    fetchPermissionDetails()
  }
})

// Fetch data when component is mounted if modal is already open
onMounted(() => {
  if (props.show && props.permissionId) {
    fetchPermissionDetails()
  }
})
</script>

<style scoped>
/* Additional styles if needed */
</style>

