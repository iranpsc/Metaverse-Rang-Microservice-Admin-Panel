<template>
  <Modal
    :model-value="show"
    @update:model-value="onMainModalClose"
    :title="modalTitle"
    size="xl"
  >
    <div v-if="loading" class="flex justify-center py-8">
      <Spinner size="lg" />
    </div>

    <div v-else-if="error" class="py-4">
      <Alert variant="danger" :message="error" />
    </div>

    <div v-else-if="bankAccount" class="space-y-4" dir="rtl">
      <!-- Bank Account Details Table -->
      <div class="overflow-x-auto">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-[var(--theme-bg-glass)] border-b-2 border-[var(--theme-border)]">
              <th class="px-4 py-3 text-sm font-semibold text-[var(--theme-text-primary)] border border-[var(--theme-border)] text-right">عنوان</th>
              <th class="px-4 py-3 text-sm font-semibold text-[var(--theme-text-primary)] border border-[var(--theme-border)] text-right">مقدار</th>
              <th v-if="bankAccount.status === 0" class="px-4 py-3 text-sm font-semibold text-[var(--theme-text-primary)] border border-[var(--theme-border)] text-right">بررسی</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[var(--theme-border)]">
            <!-- Bank Name -->
            <tr class="hover:bg-[var(--theme-bg-glass)]">
              <td class="px-4 py-3 text-sm font-medium text-[var(--theme-text-primary)] border border-[var(--theme-border)] text-right">نام بانک</td>
              <td class="px-4 py-3 text-sm text-[var(--theme-text-secondary)] border border-[var(--theme-border)] text-right">{{ bankAccount.bank_name }}</td>
              <td v-if="bankAccount.status === 0" class="px-4 py-3 border border-[var(--theme-border)]">
                <ErrorInputField
                  field-name="bank_name_err"
                  :existing-error="getExistingError('bank_name_err')"
                  @save-error="handleSaveError"
                />
              </td>
            </tr>

            <!-- Shaba Number -->
            <tr class="hover:bg-[var(--theme-bg-glass)]">
              <td class="px-4 py-3 text-sm font-medium text-[var(--theme-text-primary)] border border-[var(--theme-border)] text-right">شماره شبا</td>
              <td class="px-4 py-3 text-sm text-[var(--theme-text-secondary)] border border-[var(--theme-border)] text-right">{{ bankAccount.shaba_num }}</td>
              <td v-if="bankAccount.status === 0" class="px-4 py-3 border border-[var(--theme-border)]">
                <ErrorInputField
                  field-name="shaba_num_err"
                  :existing-error="getExistingError('shaba_num_err')"
                  @save-error="handleSaveError"
                />
              </td>
            </tr>

            <!-- Card Number -->
            <tr class="hover:bg-[var(--theme-bg-glass)]">
              <td class="px-4 py-3 text-sm font-medium text-[var(--theme-text-primary)] border border-[var(--theme-border)] text-right">شماره کارت</td>
              <td class="px-4 py-3 text-sm text-[var(--theme-text-secondary)] border border-[var(--theme-border)] text-right">{{ bankAccount.card_num }}</td>
              <td v-if="bankAccount.status === 0" class="px-4 py-3 border border-[var(--theme-border)]">
                <ErrorInputField
                  field-name="card_num_err"
                  :existing-error="getExistingError('card_num_err')"
                  @save-error="handleSaveError"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <template #footer>
      <div class="flex gap-3 justify-end" dir="rtl">
        <Button
          v-if="bankAccount && bankAccount.status === 0"
          variant="primary"
          :loading="saving"
          @click="handleSave"
          class="w-1/2"
        >
          ثبت
        </Button>
        <Button
          variant="danger"
          @click="onMainModalClose"
          :class="bankAccount && bankAccount.status === 0 ? 'w-1/2' : 'w-full'"
        >
          بستن
        </Button>
      </div>
    </template>
  </Modal>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import apiClient from '../../utils/api'
import { Modal, Button, Spinner, Alert } from '../ui'
import ErrorInputField from './ErrorInputField.vue'
import { notifySuccess } from '../../utils/notifications'

const props = defineProps({
  bankAccountId: {
    type: Number,
    required: true
  },
  show: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'updated'])


const loading = ref(false)
const saving = ref(false)
const error = ref(null)
const bankAccount = ref(null)
const bankAccountErrors = ref([])

const modalTitle = computed(() => {
  if (!bankAccount.value || !bankAccount.value.bankable) {
    return 'جزئیات حساب بانکی'
  }

  const bankable = bankAccount.value.bankable
  const userName = bankable.fname && bankable.lname
    ? `${bankable.fname} ${bankable.lname}`
    : bankable.name || 'کاربر'

  return `جزئیات حساب بانکی - ${userName}`
})

const getExistingError = (fieldName) => {
  if (!bankAccount.value?.errors) return null
  const errorItem = bankAccountErrors.value.find(e => e.name === fieldName)
  return errorItem ? errorItem.message : null
}

const handleSaveError = (fieldName, errorMessage) => {
  bankAccountErrors.value = bankAccountErrors.value.filter(e => e.name !== fieldName)

  if (errorMessage && errorMessage.trim()) {
    bankAccountErrors.value.push({
      name: fieldName,
      message: errorMessage
    })
  }
}

const submitBankAccountUpdate = async () => {
  try {
    saving.value = true
    error.value = null

    const response = await apiClient.put(`/bank-accounts/${props.bankAccountId}`, {
      bank_account_errors: bankAccountErrors.value,
          })

    if (response.data.success) {
      await notifySuccess('اطلاعات با موفقیت ثبت شد')
      emit('updated')
    } else {
      error.value = 'خطا در ثبت اطلاعات'
    }
  } catch (err) {
    console.error('Bank Account update error:', err)

    error.value = err.response?.data?.message || 'خطا در ثبت اطلاعات'
  } finally {
    saving.value = false
  }
}


const handleSave = async () => {
  await submitBankAccountUpdate()
}

const onMainModalClose = () => {
  emit('close')
}

const fetchBankAccountDetails = async () => {
  try {
    loading.value = true
    error.value = null

    const response = await apiClient.get(`/bank-accounts/${props.bankAccountId}`)

    if (response.data.success) {
      bankAccount.value = response.data.data
      if (bankAccount.value.errors && Array.isArray(bankAccount.value.errors)) {
        bankAccountErrors.value = [...bankAccount.value.errors]
      }
    } else {
      error.value = 'خطا در دریافت اطلاعات'
    }
  } catch (err) {
    console.error('Bank Account details fetch error:', err)
    error.value = err.response?.data?.message || 'خطا در بارگذاری اطلاعات'
  } finally {
    loading.value = false
  }
}

watch(() => props.show, (newVal) => {
  if (newVal && props.bankAccountId) {
    bankAccountErrors.value = []
    fetchBankAccountDetails()
  } else {
  }
})

watch(() => props.bankAccountId, (newVal) => {
  if (newVal && props.show) {
    bankAccountErrors.value = []
    fetchBankAccountDetails()
  }
})

onMounted(() => {
  if (props.show && props.bankAccountId) {
    fetchBankAccountDetails()
  }
})
</script>
