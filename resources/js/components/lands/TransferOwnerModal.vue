<template>
  <Modal
    :model-value="modelValue"
    title="انتقال مالکیت زمین"
    size="md"
    @update:model-value="handleClose"
  >
    <div class="space-y-6" dir="rtl">
      <Alert variant="info" :dismissible="false">
        فقط زمین‌هایی که هنوز به کاربری اختصاص داده نشده‌اند (مالک سیستم) قابل انتقال هستند.
        زمین‌های دارای مالک در لیست غیرفعال نمایش داده می‌شوند.
      </Alert>

      <Select2
        v-model="selectedLandId"
        label="انتخاب زمین"
        placeholder="زمین را انتخاب کنید"
        :remote-fetch="fetchLandOptions"
        :error="errors.feature_id"
        required
        :allow-clear="true"
        @option-select="handleLandSelect"
      />

      <Select2
        v-model="selectedUserId"
        label="انتخاب کاربر جدید"
        placeholder="کاربر را انتخاب کنید"
        :remote-fetch="fetchUserOptions"
        :error="errors.new_owner_id"
        required
        :allow-clear="true"
      />
    </div>

    <template #footer>
      <Button
        variant="primary"
        :loading="saving"
        @click="handleTransfer"
      >
        انتقال مالکیت
      </Button>
      <Button variant="ghost" @click="handleClose">
        بستن
      </Button>
    </template>
  </Modal>
</template>

<script setup>
import { ref, watch } from 'vue'
import apiClient from '../../utils/api'
import { Modal, Button, Alert } from '../ui'
import Select2 from '../ui/Select2.vue'
import { notifySuccess, notifyError } from '../../utils/notifications'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'transferred'])

const OPTIONS_PER_PAGE = 20

const saving = ref(false)
const selectedLandId = ref('')
const selectedUserId = ref('')
const selectedLandDisabled = ref(false)
const errors = ref({})

const resetForm = () => {
  selectedLandId.value = ''
  selectedUserId.value = ''
  selectedLandDisabled.value = false
  errors.value = {}
}

const fetchTransferOptions = async (type, { search, page }) => {
  const response = await apiClient.get('/lands/owner-transfer/options', {
    params: {
      type,
      search,
      page,
      per_page: OPTIONS_PER_PAGE
    }
  })

  if (!response.data.success) {
    throw new Error('خطا در دریافت اطلاعات')
  }

  return {
    results: response.data.data.options,
    more: response.data.data.pagination.more
  }
}

const fetchLandOptions = ({ search, page }) => fetchTransferOptions('lands', { search, page })

const fetchUserOptions = ({ search, page }) => fetchTransferOptions('users', { search, page })

const handleLandSelect = (option) => {
  selectedLandDisabled.value = Boolean(option.disabled)
}

const validateForm = () => {
  errors.value = {}

  if (!selectedLandId.value) {
    errors.value.feature_id = 'انتخاب زمین الزامی است'
    return false
  }

  if (selectedLandDisabled.value) {
    errors.value.feature_id = 'این زمین قابل انتقال نیست'
    return false
  }

  if (!selectedUserId.value) {
    errors.value.new_owner_id = 'انتخاب کاربر الزامی است'
    return false
  }

  return true
}

const handleTransfer = async () => {
  if (!validateForm()) {
    return
  }

  try {
    saving.value = true
    errors.value = {}

    const response = await apiClient.post('/lands/owner-transfer', {
      feature_id: Number(selectedLandId.value),
      new_owner_id: Number(selectedUserId.value)
    })

    if (response.data.success) {
      notifySuccess(response.data.message || 'مالکیت زمین با موفقیت منتقل شد')
      emit('transferred')
      handleClose()
    } else {
      notifyError(response.data.message || 'خطا در انتقال مالکیت')
    }
  } catch (err) {
    console.error('Owner transfer error:', err)

    if (err.response?.status === 422) {
      const message = err.response.data?.message
      const validationErrors = err.response.data?.errors

      if (validationErrors?.feature_id) {
        errors.value.feature_id = validationErrors.feature_id[0]
      }
      if (validationErrors?.new_owner_id) {
        errors.value.new_owner_id = validationErrors.new_owner_id[0]
      }

      notifyError(message || 'اطلاعات وارد شده معتبر نیست')
    } else {
      notifyError(err.response?.data?.message || 'خطا در انتقال مالکیت')
    }
  } finally {
    saving.value = false
  }
}

const handleClose = () => {
  resetForm()
  emit('update:modelValue', false)
}

watch(
  () => props.modelValue,
  (isOpen) => {
    if (isOpen) {
      resetForm()
    }
  }
)
</script>
