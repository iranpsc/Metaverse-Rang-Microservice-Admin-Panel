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
          @click="onClose"
        >
          بستن
        </Button>
      </div>
    </template>
  </Modal>

  <PhoneVerificationModal :phone-verification="phoneVerification" title="تایید نهایی" />
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import apiClient from '../../utils/api'
import { Modal, Button, Spinner, Alert } from '../ui'
import PhoneVerificationModal from '../PhoneVerificationModal.vue'
import { usePhoneVerification } from '../../composables/usePhoneVerification'
import { notifySuccess, notifyError } from '../../utils/notifications'
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

const phoneVerification = usePhoneVerification()
const {
  isProduction,
  isVerified,
  sendingVerification,
  beginVerifyForSubmit,
  getSubmitPayload,
  confirmThenVerify,
  handleApiVerificationError,
  resetVerificationState
} = phoneVerification

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
  await confirmThenVerify(
    {
      message: 'آیا می خواهید این مسیولیت را حذف کنید؟',
      title: 'تایید حذف مسئولیت',
      confirmText: 'بله، حذف شود',
      cancelText: 'انصراف'
    },
    async (payload) => {
      try {
        await apiClient.delete(`/admins/${props.adminId}/roles/${roleId}`, { data: payload })
        await notifySuccess('مسئولیت با موفقیت حذف شد')
        fetchAdminDetails()
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

const submitAdminUpdate = async (verificationPayload = {}) => {
  try {
    saving.value = true
    fetchError.value = null

    const response = await apiClient.put(`/admins/${props.adminId}`, {
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
    console.error('Update admin error:', err)

    if (await handleApiVerificationError(err)) {
      return
    }

    fetchError.value = err.response?.data?.message || 'خطا در ثبت اطلاعات'
  } finally {
    saving.value = false
  }
}

const handleSendCode = async () => {
  await beginVerifyForSubmit()
}

const handleSave = async () => {
  if (isProduction.value) {
    await submitAdminUpdate(getSubmitPayload())
  } else {
    await submitAdminUpdate()
  }
}

const onClose = () => {
  resetVerificationState()
  emit('close')
}

watch(() => props.show, (newVal) => {
  if (newVal && props.adminId) {
    resetVerificationState()
    fetchAdminDetails()
  } else if (!newVal) {
    admin.value = null
    availableRoles.value = []
    selectedRoles.value = []
    fetchError.value = null
    resetVerificationState()
  }
})

watch(() => props.adminId, (newVal) => {
  if (newVal && props.show) {
    resetVerificationState()
    fetchAdminDetails()
  }
})

onMounted(() => {
  if (props.show && props.adminId) {
    fetchAdminDetails()
  }
})
</script>
