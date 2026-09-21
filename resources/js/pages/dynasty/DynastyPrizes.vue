<template>
  <div class="p-6 space-y-6">
    <PageHeader
      title="جوایز سلسله"
      subtitle="مدیریت جوایز سلسله خانوادگی"
    />

    <!-- Action bar: total paid (left) + create (right) -->
    <div class="mb-6 flex items-center justify-between gap-4" dir="ltr">
      <div class="text-sm text-[var(--theme-text-secondary)]" dir="rtl">
        مجموع جوایز پرداخت‌شده به کاربران:
        <span class="ms-1 text-base font-semibold text-[var(--theme-text-primary)]">
          {{ formatNumber(totalPaidAmount) }}
        </span>
        <span class="ms-1 text-xs text-[var(--theme-text-muted)]">ریال</span>
      </div>
      <Button variant="primary" @click="openCreateModal">
        تعریف جوایز
      </Button>
    </div>

    <!-- Loading State -->
    <LoadingState v-if="loading" />

    <!-- Error State -->
    <ErrorState
      v-else-if="error"
      :message="error"
      variant="error"
    />

    <!-- Table -->
    <Table
      v-else
      :columns="tableColumns"
      :data="prizes"
      :pagination="pagination"
      empty-state-message="جزئیاتی برای پاداش تعریف نشده است"
    >
      <template #cell-actions="{ row }">
        <div class="flex items-center gap-2">
          <Button
            size="sm"
            variant="secondary"
            rounded="full"
            class="!p-2 !gap-0 min-w-[2.25rem]"
            title="مشاهده"
            aria-label="مشاهده"
            @click="openViewModal(row)"
          >
            <template #icon-left>
              <TableActionIcon name="view" />
            </template>
          </Button>
          <Button
            size="sm"
            variant="primary"
            rounded="full"
            class="!p-2 !gap-0 min-w-[2.25rem]"
            title="ویرایش"
            aria-label="ویرایش"
            @click="openEditModal(row)"
          >
            <template #icon-left>
              <TableActionIcon name="edit" />
            </template>
          </Button>
          <Button
            size="sm"
            variant="danger"
            rounded="full"
            class="!p-2 !gap-0 min-w-[2.25rem]"
            title="حذف"
            aria-label="حذف"
            @click="handleDelete(row)"
          >
            <template #icon-left>
              <TableActionIcon name="delete" />
            </template>
          </Button>
        </div>
      </template>
    </Table>

    <!-- Pagination -->
    <Pagination
      v-if="pagination && pagination.total > 0 && !loading && !error"
      :pagination="pagination"
      :disabled="loading"
      @page-change="onPageChange"
    />

    <!-- Create / Edit Modal -->
    <Modal
      v-model="showFormModal"
      :title="formModalTitle"
      size="xl"
      @close="resetForm"
    >
      <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <Select
            v-if="!isEditMode"
            v-model="form.member"
            label="نسبت خانوادگی"
            placeholder="انتخاب کنید"
            :options="memberOptions"
            :error="errors.member"
            required
          />

          <Input
            v-model.number="form.introduction_profit_increase"
            type="number"
            label="افزایش سود پاداش معرفی(%)"
            :error="errors.introduction_profit_increase"
            :min="0"
            step="0.01"
            required
          />

          <Input
            v-model.number="form.accumulated_capital_reserve"
            type="number"
            label="ذخیره سرمایه انباشته(%)"
            :error="errors.accumulated_capital_reserve"
            :min="0"
            step="0.01"
            required
          />

          <Input
            v-model.number="form.data_storage"
            type="number"
            label="ذخیره دیتا(%)"
            :error="errors.data_storage"
            :min="0"
            step="0.01"
            required
          />

          <Input
            v-model.number="form.psc"
            type="number"
            label="پاداش معرفی PSC (ریال)"
            :error="errors.psc"
            :min="0"
            step="1"
            required
          />

          <Input
            v-model.number="form.satisfaction"
            type="number"
            label="رضایت"
            :error="errors.satisfaction"
            :min="0"
            step="0.01"
            required
          />
        </div>
      </div>

      <template #footer>
        <Button
          variant="primary"
          :loading="saving"
          @click="handleSubmit"
        >
          {{ submitButtonLabel }}
        </Button>
        <Button variant="danger" @click="closeFormModal">
          بستن
        </Button>
      </template>
    </Modal>

    <!-- View Modal -->
    <Modal
      v-model="showViewModal"
      title="جزئیات پاداش"
      size="xl"
    >
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-[var(--theme-bg-glass)] border-b border-[var(--theme-border)]">
            <tr>
              <th class="px-6 py-4 text-right text-sm font-semibold text-[var(--theme-text-primary)]">
                نسبت خانوادگی
              </th>
              <th class="px-6 py-4 text-right text-sm font-semibold text-[var(--theme-text-primary)]">
                افزایش سود پاداش معرفی(%)
              </th>
              <th class="px-6 py-4 text-right text-sm font-semibold text-[var(--theme-text-primary)]">
                ذخیره سرمایه انباشته(%)
              </th>
              <th class="px-6 py-4 text-right text-sm font-semibold text-[var(--theme-text-primary)]">
                ذخیره دیتا(%)
              </th>
              <th class="px-6 py-4 text-right text-sm font-semibold text-[var(--theme-text-primary)]">
                پاداش معرفی PSC (ریال)
              </th>
              <th class="px-6 py-4 text-right text-sm font-semibold text-[var(--theme-text-primary)]">
                رضایت
              </th>
              <th class="px-6 py-4 text-right text-sm font-semibold text-[var(--theme-text-primary)]">
                تعداد دریافت‌کنندگان
              </th>
              <th class="px-6 py-4 text-right text-sm font-semibold text-[var(--theme-text-primary)]">
                مجموع پرداختی
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[var(--theme-border)]">
            <tr class="hover:bg-[var(--theme-bg-glass)] transition-colors">
              <td class="px-6 py-4 text-sm text-[var(--theme-text-primary)]">
                {{ getMemberTitle(viewingPrize?.member) }}
              </td>
              <td class="px-6 py-4 text-sm text-[var(--theme-text-primary)]">
                {{ formatPercentage(viewingPrize?.introduction_profit_increase) }}
              </td>
              <td class="px-6 py-4 text-sm text-[var(--theme-text-primary)]">
                {{ formatPercentage(viewingPrize?.accumulated_capital_reserve) }}
              </td>
              <td class="px-6 py-4 text-sm text-[var(--theme-text-primary)]">
                {{ formatPercentage(viewingPrize?.data_storage) }}
              </td>
              <td class="px-6 py-4 text-sm text-[var(--theme-text-primary)]">
                {{ formatNumber(viewingPrize?.psc) }}
              </td>
              <td class="px-6 py-4 text-sm text-[var(--theme-text-primary)]">
                {{ formatNumber(viewingPrize?.satisfaction) }}
              </td>
              <td class="px-6 py-4 text-sm text-[var(--theme-text-primary)]">
                {{ formatNumber(viewingPrize?.recipients_count ?? 0) }}
              </td>
              <td class="px-6 py-4 text-sm text-[var(--theme-text-primary)]">
                {{ formatNumber(viewingPrize?.total_paid_amount ?? 0) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <template #footer>
        <Button variant="danger" @click="showViewModal = false">
          بستن
        </Button>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import apiClient from '../../utils/api'
import { Table, Modal, Button, Input, Select, LoadingState, ErrorState, Pagination, PageHeader } from '../../components/ui'
import { usePaginatedList } from '../../composables/usePaginatedList'
import { useToast } from '../../composables/useToast'
import { confirm } from '../../utils/notifications'
import TableActionIcon from '../../components/icons/TableActionIcon.vue'
import { formatPersianNumber } from '../../utils/numberFormatter'

const formatNumber = formatPersianNumber

const { showToast } = useToast()

const {
  loading,
  error,
  pagination,
  execute,
  goToPage,
  buildParams
} = usePaginatedList({ perPage: 10 })

const prizes = ref([])
const totalPaidAmount = ref(0)
const saving = ref(false)

const showFormModal = ref(false)
const showViewModal = ref(false)
const isEditMode = ref(false)
const viewingPrize = ref(null)
const editingPrize = ref(null)

const emptyForm = () => ({
  member: '',
  satisfaction: 0,
  introduction_profit_increase: 0,
  accumulated_capital_reserve: 0,
  data_storage: 0,
  psc: 0
})

const form = ref(emptyForm())
const errors = ref({})

const memberOptions = [
  { value: 'father', label: 'پدر' },
  { value: 'mother', label: 'مادر' },
  { value: 'husband', label: 'شوهر' },
  { value: 'wife', label: 'زن' },
  { value: 'sister', label: 'خواهر' },
  { value: 'brother', label: 'برادر' },
  { value: 'offspring', label: 'فرزند' }
]

const formModalTitle = computed(() => {
  if (!isEditMode.value) {
    return 'تعریف جوایز سلسله خانوادگی'
  }

  return `ویرایش پاداشهای معرفی ${getMemberTitle(editingPrize.value?.member)}`
})

const submitButtonLabel = computed(() => (isEditMode.value ? 'ذخیره تغییرات' : 'ثبت'))

const tableColumns = [
  {
    key: 'member',
    label: 'نسبت خانوادگی',
    formatter: (value) => getMemberTitle(value)
  },
  {
    key: 'recipients_count',
    label: 'تعداد دریافت‌کنندگان',
    formatter: (value) => formatNumber(value ?? 0)
  },
  {
    key: 'total_paid_amount',
    label: 'مجموع پرداختی (ریال)',
    formatter: (value) => formatNumber(value ?? 0)
  },
  {
    key: 'actions',
    label: 'مدیریت'
  }
]

const getMemberTitle = (member) => {
  const option = memberOptions.find(opt => opt.value === member)
  return option ? option.label : member
}

const formatPercentage = (value) => {
  if (value === null || value === undefined) return '-'
  return `${(value * 100).toFixed(2)}%`
}

const fieldError = (val) => {
  if (val == null || val === '') return ''
  return Array.isArray(val) ? val[0] : String(val)
}

const openCreateModal = () => {
  isEditMode.value = false
  editingPrize.value = null
  form.value = emptyForm()
  errors.value = {}
  showFormModal.value = true
}

const openEditModal = (prize) => {
  isEditMode.value = true
  editingPrize.value = prize
  form.value = {
    member: prize.member || '',
    satisfaction: prize.satisfaction || 0,
    introduction_profit_increase: prize.introduction_profit_increase
      ? (prize.introduction_profit_increase * 100)
      : 0,
    accumulated_capital_reserve: prize.accumulated_capital_reserve
      ? (prize.accumulated_capital_reserve * 100)
      : 0,
    data_storage: prize.data_storage ? (prize.data_storage * 100) : 0,
    psc: prize.psc || 0
  }
  errors.value = {}
  showFormModal.value = true
}

const closeFormModal = () => {
  showFormModal.value = false
  resetForm()
}

const resetForm = () => {
  isEditMode.value = false
  editingPrize.value = null
  form.value = emptyForm()
  errors.value = {}
}

const openViewModal = (prize) => {
  viewingPrize.value = prize
  showViewModal.value = true
}

const validateForm = () => {
  errors.value = {}
  let isValid = true

  if (!isEditMode.value && !form.value.member) {
    errors.value.member = 'نسبت خانوادگی الزامی است'
    isValid = false
  }

  if (form.value.satisfaction === null || form.value.satisfaction < 0) {
    errors.value.satisfaction = 'رضایت باید عددی مثبت باشد'
    isValid = false
  }

  if (form.value.introduction_profit_increase === null || form.value.introduction_profit_increase < 0) {
    errors.value.introduction_profit_increase = 'افزایش سود پاداش معرفی باید عددی مثبت باشد'
    isValid = false
  }

  if (form.value.accumulated_capital_reserve === null || form.value.accumulated_capital_reserve < 0) {
    errors.value.accumulated_capital_reserve = 'ذخیره سرمایه انباشته باید عددی مثبت باشد'
    isValid = false
  }

  if (form.value.data_storage === null || form.value.data_storage < 0) {
    errors.value.data_storage = 'ذخیره دیتا باید عددی مثبت باشد'
    isValid = false
  }

  if (form.value.psc === null || form.value.psc < 0) {
    errors.value.psc = 'پاداش معرفی PSC باید عددی مثبت باشد'
    isValid = false
  }

  return isValid
}

const buildPayload = () => {
  const payload = {
    satisfaction: form.value.satisfaction,
    introduction_profit_increase: form.value.introduction_profit_increase,
    accumulated_capital_reserve: form.value.accumulated_capital_reserve,
    data_storage: form.value.data_storage,
    psc: form.value.psc
  }

  if (!isEditMode.value) {
    payload.member = form.value.member
  }

  return payload
}

const normalizeValidationErrors = (apiErrors) => {
  const normalized = {}
  Object.entries(apiErrors || {}).forEach(([key, value]) => {
    normalized[key] = fieldError(value)
  })
  return normalized
}

const handleSubmit = async () => {
  if (!validateForm()) {
    return
  }

  if (isEditMode.value && !editingPrize.value) {
    return
  }

  try {
    saving.value = true
    errors.value = {}

    const response = isEditMode.value
      ? await apiClient.put(`/dynasty/prizes/${editingPrize.value.id}`, buildPayload())
      : await apiClient.post('/dynasty/prizes', buildPayload())

    if (response.data.success) {
      await fetchPrizes()
      closeFormModal()
      showToast('اطلاعات با موفقیت ثبت شد', 'success')
    } else {
      showToast(response.data.message || 'خطا در ثبت اطلاعات', 'error')
    }
  } catch (err) {
    console.error('Submit prize error:', err)

    if (err.response?.data?.errors) {
      errors.value = normalizeValidationErrors(err.response.data.errors)
    } else {
      showToast(err.response?.data?.message || 'خطا در ثبت اطلاعات', 'error')
    }
  } finally {
    saving.value = false
  }
}

const handleDelete = async (row) => {
  const result = await confirm(
    'آیا می‌خواهید این پاداش را حذف کنید؟',
    'تایید حذف',
    { confirmText: 'بله، حذف شود', cancelText: 'انصراف' }
  )
  if (!result.isConfirmed) return

  try {
    const response = await apiClient.delete(`/dynasty/prizes/${row.id}`)

    if (response.data.success) {
      await fetchPrizes()
      showToast('پاداش با موفقیت حذف شد', 'success')
    } else {
      showToast(response.data.message || 'خطا در حذف پاداش', 'error')
    }
  } catch (err) {
    console.error('Delete prize error:', err)
    showToast(err.response?.data?.message || 'خطا در حذف پاداش', 'error')
  }
}

const clearPrizes = () => {
  prizes.value = []
  totalPaidAmount.value = 0
  pagination.value = null
}

const fetchPrizes = () => execute(async () => {
  const response = await apiClient.get('/dynasty/prizes', { params: buildParams() })

  if (response.data.success) {
    prizes.value = response.data.data.prizes.map((prize, index) => {
      const perPage = response.data.data.pagination?.per_page || 10
      const currentPageNum = response.data.data.pagination?.current_page || 1
      return {
        ...prize,
        rowId: (currentPageNum - 1) * perPage + index + 1
      }
    })
    totalPaidAmount.value = response.data.data.total_paid_amount ?? 0
    pagination.value = response.data.data.pagination
  } else {
    error.value = 'خطا در دریافت اطلاعات جوایز'
    clearPrizes()
  }
}, {
  onClear: clearPrizes,
  logLabel: 'Fetch prizes error:',
  fallbackMessage: 'خطا در بارگذاری اطلاعات'
})

const onPageChange = (page) => goToPage(page, fetchPrizes)

onMounted(() => {
  fetchPrizes()
})
</script>
