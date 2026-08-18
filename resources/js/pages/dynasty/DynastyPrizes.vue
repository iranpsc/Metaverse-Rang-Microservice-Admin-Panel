<template>
  <div class="p-6 space-y-6">
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-[var(--theme-text-primary)] mb-2">جوایز سلسله</h1>
      <p class="text-[var(--theme-text-secondary)]">مدیریت جوایز سلسله خانوادگی</p>
    </div>

    <!-- Create Button -->
    <div class="mb-6">
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
      @page-change="goToPage"
    />

    <!-- Create Modal -->
    <Modal
      v-model="showCreateModal"
      title="تعریف جوایز سلسله خانوادگی"
      size="xl"
      @close="resetCreateForm"
    >
      <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Member Relation -->
          <Select
            v-model="form.member"
            label="نسبت خانوادگی"
            placeholder="انتخاب کنید"
            :options="memberOptions"
            :error="errors.member"
            required
          />

          <!-- Introduction Profit Increase -->
          <Input
            v-model.number="form.introduction_profit_increase"
            type="number"
            label="افزایش سود پاداش معرفی(%)"
            :error="errors.introduction_profit_increase"
            :min="0"
            step="0.01"
            required
          />

          <!-- Accumulated Capital Reserve -->
          <Input
            v-model.number="form.accumulated_capital_reserve"
            type="number"
            label="ذخیره سرمایه انباشته(%)"
            :error="errors.accumulated_capital_reserve"
            :min="0"
            step="0.01"
            required
          />

          <!-- Data Storage -->
          <Input
            v-model.number="form.data_storage"
            type="number"
            label="ذخیره دیتا(%)"
            :error="errors.data_storage"
            :min="0"
            step="0.01"
            required
          />

          <!-- PSC -->
          <Input
            v-model.number="form.psc"
            type="number"
            label="پاداش معرفی PSC (ریال)"
            :error="errors.psc"
            :min="0"
            step="1"
            required
          />

          <!-- Satisfaction -->
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
          @click="handleCreate"
        >
          ثبت
        </Button>
        <Button variant="danger" @click="closeCreateModal">
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

    <!-- Edit Modal -->
    <Modal
      v-model="showEditModal"
      :title="`ویرایش پاداشهای معرفی ${getMemberTitle(editingPrize?.member)}`"
      size="xl"
      @close="resetEditForm"
    >
      <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Introduction Profit Increase -->
          <Input
            v-model.number="editForm.introduction_profit_increase"
            type="number"
            label="افزایش سود پاداش معرفی(%)"
            :error="errors.introduction_profit_increase"
            :min="0"
            step="0.01"
            required
          />

          <!-- Accumulated Capital Reserve -->
          <Input
            v-model.number="editForm.accumulated_capital_reserve"
            type="number"
            label="ذخیره سرمایه انباشته(%)"
            :error="errors.accumulated_capital_reserve"
            :min="0"
            step="0.01"
            required
          />

          <!-- Data Storage -->
          <Input
            v-model.number="editForm.data_storage"
            type="number"
            label="ذخیره دیتا(%)"
            :error="errors.data_storage"
            :min="0"
            step="0.01"
            required
          />

          <!-- PSC -->
          <Input
            v-model.number="editForm.psc"
            type="number"
            label="پاداش معرفی PSC (ریال)"
            :error="errors.psc"
            :min="0"
            step="1"
            required
          />

          <!-- Satisfaction -->
          <Input
            v-model.number="editForm.satisfaction"
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
          :loading="updating"
          @click="handleUpdate"
        >
          ذخیره تغییرات
        </Button>
        <Button variant="danger" @click="closeEditModal">
          بستن
        </Button>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import apiClient from '../../utils/api'
import { Table, Modal, Button, Input, Select, LoadingState, ErrorState, Pagination } from '../../components/ui'
import { useToast } from '../../composables/useToast'
import { confirm } from '../../utils/notifications'
import TableActionIcon from '../../components/icons/TableActionIcon.vue'

const { showToast } = useToast()


const loading = ref(true)
const error = ref(null)
const prizes = ref([])
const pagination = ref(null)
const saving = ref(false)
const updating = ref(false)
const currentPage = ref(1)

// Modal states
const showCreateModal = ref(false)
const showViewModal = ref(false)
const showEditModal = ref(false)
const viewingPrize = ref(null)
const editingPrize = ref(null)

// Form data
const form = ref({
  member: '',
  satisfaction: 0,
  introduction_profit_increase: 0,
  accumulated_capital_reserve: 0,
  data_storage: 0,
  psc: 0
})

const editForm = ref({
  satisfaction: 0,
  introduction_profit_increase: 0,
  accumulated_capital_reserve: 0,
  data_storage: 0,
  psc: 0
})

const errors = ref({})

// Member options
const memberOptions = [
  { value: 'father', label: 'پدر' },
  { value: 'mother', label: 'مادر' },
  { value: 'husband', label: 'شوهر' },
  { value: 'wife', label: 'زن' },
  { value: 'sister', label: 'خواهر' },
  { value: 'brother', label: 'برادر' },
  { value: 'offspring', label: 'فرزند' }
]

// Table columns
const tableColumns = [
  {
    key: 'member',
    label: 'نسبت خانوادگی',
    formatter: (value) => getMemberTitle(value)
  },
  {
    key: 'actions',
    label: 'مدیریت'
  }
]

// Helper functions
const getMemberTitle = (member) => {
  const option = memberOptions.find(opt => opt.value === member)
  return option ? option.label : member
}

const formatPercentage = (value) => {
  if (value === null || value === undefined) return '-'
  return `${(value * 100).toFixed(2)}%`
}

const formatNumber = (value) => {
  if (value === null || value === undefined) return '-'
  return new Intl.NumberFormat('fa-IR').format(value)
}

const openCreateModal = () => {
  showCreateModal.value = true
}

const closeCreateModal = () => {
  showCreateModal.value = false
  resetCreateForm()
}

const resetCreateForm = () => {
  form.value = {
    member: '',
    satisfaction: 0,
    introduction_profit_increase: 0,
    accumulated_capital_reserve: 0,
    data_storage: 0,
    psc: 0
  }
  errors.value = {}
}

const openViewModal = (prize) => {
  viewingPrize.value = prize
  showViewModal.value = true
}

const openEditModal = (prize) => {
  editingPrize.value = prize
  editForm.value = {
    satisfaction: prize.satisfaction || 0,
    introduction_profit_increase: prize.introduction_profit_increase ? (prize.introduction_profit_increase * 100) : 0,
    accumulated_capital_reserve: prize.accumulated_capital_reserve ? (prize.accumulated_capital_reserve * 100) : 0,
    data_storage: prize.data_storage ? (prize.data_storage * 100) : 0,
    psc: prize.psc || 0
  }
  showEditModal.value = true
}

const closeEditModal = () => {
  showEditModal.value = false
  resetEditForm()
}

const resetEditForm = () => {
  editingPrize.value = null
  editForm.value = {
    satisfaction: 0,
    introduction_profit_increase: 0,
    accumulated_capital_reserve: 0,
    data_storage: 0,
    psc: 0
  }
  errors.value = {}
}

const validateForm = () => {
  errors.value = {}
  let isValid = true

  if (!form.value.member) {
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

const validateEditForm = () => {
  errors.value = {}
  let isValid = true

  if (editForm.value.satisfaction === null || editForm.value.satisfaction < 0) {
    errors.value.satisfaction = 'رضایت باید عددی مثبت باشد'
    isValid = false
  }

  if (editForm.value.introduction_profit_increase === null || editForm.value.introduction_profit_increase < 0) {
    errors.value.introduction_profit_increase = 'افزایش سود پاداش معرفی باید عددی مثبت باشد'
    isValid = false
  }

  if (editForm.value.accumulated_capital_reserve === null || editForm.value.accumulated_capital_reserve < 0) {
    errors.value.accumulated_capital_reserve = 'ذخیره سرمایه انباشته باید عددی مثبت باشد'
    isValid = false
  }

  if (editForm.value.data_storage === null || editForm.value.data_storage < 0) {
    errors.value.data_storage = 'ذخیره دیتا باید عددی مثبت باشد'
    isValid = false
  }

  if (editForm.value.psc === null || editForm.value.psc < 0) {
    errors.value.psc = 'پاداش معرفی PSC باید عددی مثبت باشد'
    isValid = false
  }

  return isValid
}

const buildCreatePayload = () => ({
  member: form.value.member,
  satisfaction: form.value.satisfaction,
  introduction_profit_increase: form.value.introduction_profit_increase,
  accumulated_capital_reserve: form.value.accumulated_capital_reserve,
  data_storage: form.value.data_storage,
  psc: form.value.psc
})

const buildUpdatePayload = () => ({
  satisfaction: editForm.value.satisfaction,
  introduction_profit_increase: editForm.value.introduction_profit_increase,
  accumulated_capital_reserve: editForm.value.accumulated_capital_reserve,
  data_storage: editForm.value.data_storage,
  psc: editForm.value.psc
})

const submitCreate = async () => {
  try {
    saving.value = true
    errors.value = {}

    const response = await apiClient.post(
      '/dynasty/prizes',
      buildCreatePayload()
    )

    if (response.data.success) {
      await fetchPrizes()
      closeCreateModal()
      showToast('اطلاعات با موفقیت ثبت شد', 'success')
    } else {
      showToast(response.data.message || 'خطا در ثبت اطلاعات', 'error')
    }
  } catch (err) {
    console.error('Create prize error:', err)

    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors
    } else {
      showToast(err.response?.data?.message || 'خطا در ثبت اطلاعات', 'error')
    }
  } finally {
    saving.value = false
  }
}

const handleCreate = async () => {
  if (!validateForm()) {
    return
  }

  await submitCreate()
}

const submitUpdate = async () => {
  if (!editingPrize.value) {
    return
  }

  try {
    updating.value = true
    errors.value = {}

    const response = await apiClient.put(
      `/dynasty/prizes/${editingPrize.value.id}`,
      buildUpdatePayload()
    )

    if (response.data.success) {
      await fetchPrizes()
      closeEditModal()
      showToast('اطلاعات با موفقیت ثبت شد', 'success')
    } else {
      showToast(response.data.message || 'خطا در به‌روزرسانی اطلاعات', 'error')
    }
  } catch (err) {
    console.error('Update prize error:', err)

    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors
    } else {
      showToast(err.response?.data?.message || 'خطا در به‌روزرسانی اطلاعات', 'error')
    }
  } finally {
    updating.value = false
  }
}

const handleUpdate = async () => {
  if (!editingPrize.value || !validateEditForm()) {
    return
  }

  await submitUpdate()
}

const handleDelete = async (row) => {
  const result = await confirm(
    'آیا می‌خواهید این پاداش را حذف کنید؟',
    'تایید حذف',
    { confirmText: 'بله، حذف شود', cancelText: 'انصراف' }
  )
  if (!result.isConfirmed) return

  try {
        const response = await apiClient.delete(`/dynasty/prizes/${prize.id}`)

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

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value?.last_page) {
    currentPage.value = page
    fetchPrizes()
  }
}

const fetchPrizes = async () => {
  try {
    loading.value = true
    error.value = null

    const response = await apiClient.get('/dynasty/prizes', {
      params: {
        page: currentPage.value,
        per_page: 10
      }
    })

    if (response.data.success) {
      prizes.value = response.data.data.prizes.map((prize, index) => {
        const perPage = response.data.data.pagination?.per_page || 10
        const currentPageNum = response.data.data.pagination?.current_page || 1
        return {
          ...prize,
          rowId: (currentPageNum - 1) * perPage + index + 1
        }
      })
      pagination.value = response.data.data.pagination
    } else {
      error.value = 'خطا در دریافت اطلاعات جوایز'
      prizes.value = []
      pagination.value = null
    }
  } catch (err) {
    console.error('Fetch prizes error:', err)

    if (err.response && (err.response.status === 401 || err.response.status === 403)) {
      prizes.value = []
      pagination.value = null
      loading.value = false
      return
    }

    error.value = err.response?.data?.message || 'خطا در بارگذاری اطلاعات'
    prizes.value = []
    pagination.value = null
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchPrizes()
})
</script>

<style scoped>
/* Additional styles if needed */
</style>

