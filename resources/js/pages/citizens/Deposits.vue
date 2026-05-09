<template>
  <div class="p-6 space-y-6">
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-[var(--theme-text-primary)] mb-2">واریزی ها</h1>
      <p class="text-[var(--theme-text-secondary)]">مشاهده و مدیریت تراکنش‌های واریزی کاربران</p>
    </div>

    <!-- Search and Actions Row -->
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between mb-6">
      <div class="flex-1 max-w-md">
        <SearchBox
          v-model="searchTerm"
          placeholder="شماره مرجع بانک را وارد کنید"
          :debounce-ms="500"
          @search="handleSearch"
          @clear="handleClear"
        />
      </div>
      <div class="flex gap-2">
        <Button
          variant="primary"
          @click="handleExport"
          :loading="exporting"
        >
          دانلود خروجی اکسل
        </Button>
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
      :data="payments"
      :pagination="pagination"
      empty-state-message="تراکنشی یافت نشد"
    />

    <!-- Pagination -->
    <Pagination
      v-if="pagination && pagination.total > 0"
      :pagination="pagination"
      :disabled="loading"
      @page-change="goToPage"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import apiClient from '../../utils/api'
import { Table, Pagination, SearchBox, LoadingState, ErrorState, Button } from '../../components/ui'

const loading = ref(true)
const error = ref(null)
const payments = ref([])
const pagination = ref(null)
const searchTerm = ref('')
const currentPage = ref(1)
const exporting = ref(false)

// Table columns configuration
const tableColumns = [
  {
    key: 'id',
    label: 'شناسه'
  },
  {
    key: 'user_name',
    label: 'نام کاربر'
  },
  {
    key: 'amount',
    label: 'مبلغ تراکنش'
  },
  {
    key: 'ref_id',
    label: 'شماره مرجع بانک'
  },
  {
    key: 'card_pan',
    label: 'شماره کارت یا حساب مبدا'
  },
  {
    key: 'gateway',
    label: 'نام درگاه'
  },
  {
    key: 'product_title',
    label: 'محصول خریداری شده'
  },
  {
    key: 'created_at',
    label: 'تاریخ واریز'
  },
  {
    key: 'created_at_time',
    label: 'ساعت واریز'
  }
]

const handleSearch = () => {
  currentPage.value = 1
  fetchDeposits()
}

const handleClear = () => {
  currentPage.value = 1
  fetchDeposits()
}

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value?.last_page) {
    currentPage.value = page
    fetchDeposits()
  }
}

const handleExport = async () => {
  try {
    exporting.value = true
    const response = await apiClient.get('/deposits/export', {
      responseType: 'blob'
    })

    // Create blob link to download
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', 'transactions.xlsx')
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
  } catch (err) {
    console.error('Export error:', err)
    error.value = 'خطا در دانلود فایل اکسل'
  } finally {
    exporting.value = false
  }
}

const fetchDeposits = async () => {
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

    const response = await apiClient.get('/deposits', { params })

    if (response.data.success) {
      payments.value = response.data.data.payments
      pagination.value = response.data.data.pagination
    } else {
      error.value = 'خطا در دریافت اطلاعات واریزی‌ها'
    }
  } catch (err) {
    console.error('Deposits fetch error:', err)

    // If 401/403, don't set error message - axios interceptor will handle redirect
    if (err.response && (err.response.status === 401 || err.response.status === 403)) {
      payments.value = []
      pagination.value = null
      loading.value = false
      return
    }

    error.value = err.response?.data?.message || 'خطا در بارگذاری اطلاعات'
    payments.value = []
    pagination.value = null
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchDeposits()
})
</script>

<style scoped>
/* Additional styles if needed */
</style>

