<template>
  <Modal
    :model-value="modelValue"
    title="ارتقاء سطح کاربر"
    size="md"
    @update:model-value="handleClose"
  >
    <div class="space-y-6" dir="rtl">
      <Select2
        v-model="selectedUserId"
        label="انتخاب کاربر"
        placeholder="کاربر را جستجو و انتخاب کنید"
        :remote-fetch="fetchUserOptions"
        :error="errors.user_id"
        required
        :allow-clear="true"
      />

      <Input
        v-model="scoreIncrement"
        type="number"
        label="مقدار امتیاز"
        placeholder="مقدار امتیاز"
        :min="1"
        :step="1"
        inputmode="numeric"
        pattern="[0-9]*"
        :error="errors.score"
        required
        helper-text="فقط اعداد صحیح مثبت قابل قبول است"
        @update:model-value="handleScoreUpdate"
      />
    </div>

    <template #footer>
      <Button
        variant="primary"
        :loading="saving"
        @click="handleSubmit"
      >
        ثبت ارتقاء
      </Button>
      <Button variant="ghost" @click="handleClose">
        بستن
      </Button>
    </template>
  </Modal>
</template>

<script setup>
import { ref, watch } from 'vue'
import { Modal, Button, Input } from '../ui'
import Select2 from '../ui/Select2.vue'
import { useUserLevels } from '../../composables/useUserLevels'
import { notifySuccess, notifyError } from '../../utils/notifications'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  preselectedUserId: {
    type: [String, Number],
    default: ''
  }
})

const emit = defineEmits(['update:modelValue', 'promoted'])

const { searchUsersForSelect, promoteUser } = useUserLevels()

const OPTIONS_PER_PAGE = 10

const saving = ref(false)
const selectedUserId = ref('')
const scoreIncrement = ref('')
const errors = ref({})

const resetForm = () => {
  selectedUserId.value = props.preselectedUserId ? String(props.preselectedUserId) : ''
  scoreIncrement.value = ''
  errors.value = {}
}

const fetchUserOptions = async ({ search, page }) => {
  const response = await searchUsersForSelect({
    search,
    page,
    per_page: OPTIONS_PER_PAGE
  })

  if (!response.data.success) {
    throw new Error('خطا در دریافت لیست کاربران')
  }

  return {
    results: response.data.data.options,
    more: response.data.data.pagination.more
  }
}

const handleScoreUpdate = (value) => {
  const digitsOnly = String(value ?? '').replace(/\D/g, '')
  scoreIncrement.value = digitsOnly.replace(/^0+/, '') || (digitsOnly === '0' ? '' : digitsOnly)
}

const validateForm = () => {
  errors.value = {}

  if (!selectedUserId.value) {
    errors.value.user_id = 'انتخاب کاربر الزامی است'
    return false
  }

  const score = Number(scoreIncrement.value)
  if (!scoreIncrement.value || !Number.isInteger(score) || score < 1) {
    errors.value.score = 'مقدار امتیاز باید عدد صحیح مثبت باشد'
    return false
  }

  return true
}

const handleSubmit = async () => {
  if (!validateForm()) {
    return
  }

  try {
    saving.value = true
    errors.value = {}

    const response = await promoteUser(
      Number(selectedUserId.value),
      Number(scoreIncrement.value)
    )

    if (response.data.success) {
      notifySuccess(response.data.message || 'سطح کاربر با موفقیت ارتقاء یافت')
      emit('promoted')
      handleClose()
    } else {
      notifyError(response.data.message || 'خطا در ارتقاء سطح کاربر')
    }
  } catch (err) {
    console.error('User level promotion error:', err)

    if (err.response?.status === 422) {
      const validationErrors = err.response.data?.errors

      if (validationErrors?.user_id) {
        errors.value.user_id = validationErrors.user_id[0]
      }
      if (validationErrors?.score) {
        errors.value.score = validationErrors.score[0]
      }

      notifyError(err.response.data?.message || 'اطلاعات وارد شده معتبر نیست')
    } else {
      notifyError(err.response?.data?.message || 'خطا در ارتقاء سطح کاربر')
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

watch(
  () => props.preselectedUserId,
  (userId) => {
    if (props.modelValue && userId) {
      selectedUserId.value = String(userId)
    }
  }
)
</script>
