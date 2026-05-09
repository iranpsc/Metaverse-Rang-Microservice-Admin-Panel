<template>
  <div class="p-6 space-y-6">
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-[var(--theme-text-primary)] mb-2">حساب های بانکی</h1>
      <p class="text-[var(--theme-text-secondary)]">مدیریت و بررسی درخواست‌های حساب بانکی کاربران</p>
    </div>

    <!-- Search and Actions Row -->
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between mb-6">
      <div class="flex-1 max-w-md">
        <SearchBox
          v-model="searchTerm"
          placeholder="شماره کارت یا شبا را وارد کنید"
          :debounce-ms="500"
          @search="handleSearch"
          @clear="handleClear"
        />
      </div>
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
      :data="bankAccounts"
      :pagination="pagination"
      empty-state-message="اطلاعاتی تعریف نشده است"
    >
      <template #cell-details="{ row }">
        <Button
          size="sm"
          variant="primary"
          rounded="full"
          class="!p-2 !gap-0 min-w-[2.25rem]"
          title="مشاهده جزئیات"
          aria-label="مشاهده جزئیات"
          @click="openDetailsModal(row.id)"
        >
          <template #icon-left>
            <TableActionIcon name="details" />
          </template>
        </Button>
      </template>
      <template #cell-status="{ row }">
        <Badge
          :variant="getStatusVariant(row.status)"
          size="md"
        >
          <span>{{ getStatusLabel(row.status_badge) }}</span>
        </Badge>
      </template>
    </Table>

    <!-- Pagination -->
    <Pagination
      v-if="pagination && pagination.total > 0"
      :pagination="pagination"
      :disabled="loading"
      @page-change="goToPage"
    />

    <!-- Bank Account Details Modal -->
    <BankAccountDetailsModal
      v-if="selectedBankAccountId"
      :bank-account-id="selectedBankAccountId"
      :show="showDetailsModal"
      @close="closeDetailsModal"
      @updated="handleBankAccountUpdated"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import apiClient from '../../utils/api'
import { Table, Pagination, SearchBox, LoadingState, ErrorState, Button, Badge } from '../../components/ui'
import BankAccountDetailsModal from '../../components/citizens/BankAccountDetailsModal.vue'
import TableActionIcon from '../../components/icons/TableActionIcon.vue'
import { stripHtml } from '../../utils/sanitize'

const loading = ref(true)
const error = ref(null)
const bankAccounts = ref([])
const pagination = ref(null)
const searchTerm = ref('')
const currentPage = ref(1)
const selectedBankAccountId = ref(null)
const showDetailsModal = ref(false)

// Table columns configuration
const tableColumns = [
  {
    key: 'id',
    label: 'شناسه'
  },
  {
    key: 'bank_name',
    label: 'نام بانک'
  },
  {
    key: 'shaba_num',
    label: 'شماره شبا'
  },
  {
    key: 'card_num',
    label: 'شماره کارت'
  },
  {
    key: 'created_at',
    label: 'تاریخ ثبت'
  },
  {
    key: 'details',
    label: 'مشاهده جزئیات'
  },
  {
    key: 'status',
    label: 'وضعیت'
  }
]

const getStatusVariant = (status) => {
  switch (status) {
    case 0:
      return 'info'
    case 1:
      return 'success'
    case -1:
      return 'danger'
    default:
      return 'warning'
  }
}

const getStatusLabel = (badgeHtml) => stripHtml(badgeHtml) || '-'

const handleSearch = () => {
  currentPage.value = 1
  fetchBankAccounts()
}

const handleClear = () => {
  currentPage.value = 1
  fetchBankAccounts()
}

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value?.last_page) {
    currentPage.value = page
    fetchBankAccounts()
  }
}

const openDetailsModal = (id) => {
  selectedBankAccountId.value = id
  showDetailsModal.value = true
}

const closeDetailsModal = () => {
  showDetailsModal.value = false
  selectedBankAccountId.value = null
}

const handleBankAccountUpdated = () => {
  closeDetailsModal()
  fetchBankAccounts()
}

const fetchBankAccounts = async () => {
  try {
    loading.value = true
    error.value = null

    const params = {
      page: currentPage.value,
      per_page: 10,
    }

    if (searchTerm.value) {
      params.search = searchTerm.value.trim()
    }

    const response = await apiClient.get('/bank-accounts', { params })

    if (response.data.success) {
      bankAccounts.value = response.data.data.bankAccounts
      pagination.value = response.data.data.pagination
    } else {
      error.value = 'خطا در دریافت اطلاعات حساب بانکی'
    }
  } catch (err) {
    console.error('Bank Account fetch error:', err)

    // If 401/403, don't set error message - axios interceptor will handle redirect
    if (err.response && (err.response.status === 401 || err.response.status === 403)) {
      // Auth failed - let the interceptor handle redirect
      // Don't set error message as redirect will happen
      bankAccounts.value = []
      pagination.value = null
      loading.value = false
      return
    }

    error.value = err.response?.data?.message || 'خطا در بارگذاری اطلاعات'
    bankAccounts.value = []
    pagination.value = null
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchBankAccounts()
})
</script>

<style scoped>
/* Additional styles if needed */
</style>

