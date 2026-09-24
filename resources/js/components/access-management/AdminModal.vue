<template>
  <Modal
    :model-value="show"
    :title="modalTitle"
    size="md"
    @update:model-value="onClose"
  >
    <div v-if="loading" class="flex justify-center py-8">
      <Spinner size="lg" />
    </div>

    <div v-else-if="error" class="py-4">
      <Alert variant="danger" :message="error" />
    </div>

    <div v-else class="space-y-4" dir="rtl">
      <Select2
        v-if="!isEdit"
        v-model="formData.user_id"
        label="انتخاب کاربر"
        placeholder="جستجو با کد کاربر..."
        :remote-fetch="fetchUserOptions"
        :minimum-input-length="0"
        :error="errors.user_id"
        required
        allow-clear
        helper-text="کاربر را با کد جستجو و انتخاب کنید"
      />

      <div v-else-if="admin" class="space-y-4">
        <p class="text-sm font-medium text-[var(--theme-text-primary)]">
          کاربر: {{ admin.name }}
        </p>

        <div>
          <p class="text-sm font-medium text-[var(--theme-text-primary)] mb-2">
            مسئولیت های اختصاص داده شده به این مدیر:
          </p>

          <div v-if="admin.roles.length === 0" class="py-4">
            <Alert variant="info" message="هیچ مسئولیتی به این مدیر اختصاص داده نشده است!" />
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
                aria-label="حذف مسئولیت از مدیر"
                @click="handleRemoveRole(role.id)"
              >
                <template #icon-left>
                  <TableActionIcon name="delete" />
                </template>
              </Button>
            </li>
          </ul>
        </div>
      </div>

      <div v-if="errors.roles" class="text-sm text-error">
        {{ errors.roles }}
      </div>

      <p class="text-sm text-[var(--theme-text-secondary)] mb-4">
        {{ isEdit ? 'کدام مسئولیت ها را به این مدیر اضافه می کنید؟' : 'کدام مسئولیت ها را به این مدیر می دهید؟' }}
      </p>

      <div v-if="roleOptions.length === 0" class="py-4">
        <Alert variant="warning" message="مسئولیتی تعریف نشده است" />
      </div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
        <div
          v-for="role in roleOptions"
          :key="role.id"
          class="flex items-center gap-2"
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
import { computed, ref, watch } from 'vue'
import apiClient from '../../utils/api'
import { Modal, Button, Spinner, Alert, Select2 } from '../ui'
import { notifySuccess, notifyError, confirm } from '../../utils/notifications'
import TableActionIcon from '../icons/TableActionIcon.vue'

const OPTIONS_PER_PAGE = 5

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  adminId: {
    type: Number,
    default: null
  }
})

const emit = defineEmits(['close', 'saved'])

const loading = ref(false)
const saving = ref(false)
const error = ref(null)
const admin = ref(null)
const roles = ref([])
const availableRoles = ref([])
const formData = ref({
  user_id: ''
})
const selectedRoles = ref([])
const errors = ref({})

const isEdit = computed(() => Boolean(props.adminId))
const modalTitle = computed(() => (isEdit.value ? 'ویرایش مسئولیت های مدیر' : 'ایجاد مدیر'))
const roleOptions = computed(() => (isEdit.value ? availableRoles.value : roles.value))

const fetchUserOptions = async ({ search, page }) => {
  const response = await apiClient.get('/admins/users', {
    params: {
      search,
      page,
      per_page: OPTIONS_PER_PAGE
    }
  })

  if (!response.data.success) {
    throw new Error('خطا در دریافت لیست کاربران')
  }

  return {
    results: response.data.data.options,
    more: response.data.data.pagination.more
  }
}

const validateForm = () => {
  errors.value = {}

  if (!isEdit.value && !formData.value.user_id) {
    errors.value.user_id = 'انتخاب کاربر الزامی است'
    return false
  }

  if (!isEdit.value && selectedRoles.value.length === 0) {
    errors.value.roles = 'حداقل یک مسئولیت باید انتخاب شود'
    return false
  }

  return true
}

const fetchRoles = async () => {
  const response = await apiClient.get('/admins/roles')
  if (response.data.success) {
    roles.value = response.data.data.roles
  }
}

const fetchAdminDetails = async () => {
  if (!props.adminId) {
    return
  }

  try {
    loading.value = true
    error.value = null

    const response = await apiClient.get(`/admins/${props.adminId}`)

    if (response.data.success) {
      admin.value = response.data.data.admin
      availableRoles.value = response.data.data.available_roles
      selectedRoles.value = []
    } else {
      error.value = 'خطا در دریافت اطلاعات'
    }
  } catch (err) {
    console.error('Admin fetch error:', err)
    error.value = err.response?.data?.message || 'خطا در بارگذاری اطلاعات'
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

const submitCreate = async () => {
  try {
    saving.value = true
    error.value = null

    const response = await apiClient.post('/admins', {
      user_id: Number(formData.value.user_id),
      roles: selectedRoles.value
    })

    if (response.data.success) {
      await notifySuccess('اطلاعات با موفقیت ثبت شد')
      resetForm()
      emit('saved')
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

const submitUpdate = async () => {
  try {
    saving.value = true
    error.value = null

    const response = await apiClient.put(`/admins/${props.adminId}`, {
      roles: selectedRoles.value
    })

    if (response.data.success) {
      await notifySuccess('اطلاعات با موفقیت ثبت شد')
      emit('saved')
    } else {
      error.value = 'خطا در ثبت اطلاعات'
    }
  } catch (err) {
    console.error('Update admin error:', err)
    error.value = err.response?.data?.message || 'خطا در ثبت اطلاعات'
  } finally {
    saving.value = false
  }
}

const handleSave = async () => {
  if (!validateForm()) {
    return
  }

  if (isEdit.value) {
    await submitUpdate()
    return
  }

  await submitCreate()
}

const resetForm = () => {
  formData.value = {
    user_id: ''
  }
  selectedRoles.value = []
  errors.value = {}
  error.value = null
  admin.value = null
  roles.value = []
  availableRoles.value = []
}

const loadModalData = async () => {
  resetForm()

  if (isEdit.value) {
    await fetchAdminDetails()
    return
  }

  try {
    loading.value = true
    error.value = null
    await fetchRoles()
  } catch (err) {
    console.error('Roles fetch error:', err)
    error.value = err.response?.data?.message || 'خطا در بارگذاری اطلاعات'
  } finally {
    loading.value = false
  }
}

const onClose = () => {
  emit('close')
}

watch(() => props.show, (newVal) => {
  if (newVal) {
    loadModalData()
  } else {
    resetForm()
  }
})
</script>
