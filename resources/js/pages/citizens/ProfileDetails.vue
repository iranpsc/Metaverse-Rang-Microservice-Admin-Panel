<template>
  <div class="p-6 space-y-6">
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-[var(--theme-text-primary)] mb-2">جزئیات پروفایل</h1>
      <p class="text-[var(--theme-text-secondary)]">مشاهده جزئیات و آمار پروفایل کاربران</p>
    </div>

    <!-- Search Box -->
    <div class="mb-6">
      <SearchBox
        v-model="searchTerm"
        placeholder="جستجو بر اساس نام، ایمیل یا کد..."
        :debounce-ms="500"
        container-class="max-w-md"
        @search="handleSearch"
        @clear="handleClear"
      />
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
      :data="users"
      :pagination="pagination"
      empty-state-message="کاربری یافت نشد"
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
import { Table, Pagination, SearchBox, LoadingState, ErrorState } from '../../components/ui'

const loading = ref(true)
const error = ref(null)
const users = ref([])
const pagination = ref(null)
const searchTerm = ref('')
const currentPage = ref(1)

// Table columns configuration
const tableColumns = [
  {
    key: 'id',
    label: 'شناسه شهروند'
  },
  {
    key: 'code',
    label: 'کد'
  },
  {
    key: 'created_at',
    label: 'تاریخ و ساعت ثبت نام'
  },
  {
    key: 'activities_sum_total',
    label: 'کل زمان حضور'
  },
  {
    key: 'followers_count',
    label: 'تعداد مشترکین'
  },
  {
    key: 'payments_count',
    label: 'خرید ابزار و PSC'
  },
  {
    key: 'more_than_a_million_payment',
    label: 'تعداد پرداخت های بالای ۱ میلیون تومان'
  },
  {
    key: 'score',
    label: 'کل امتیاز دریافتی'
  }
]

const handleSearch = () => {
  currentPage.value = 1
  fetchProfileDetails()
}

const handleClear = () => {
  currentPage.value = 1
  fetchProfileDetails()
}

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value?.last_page) {
    currentPage.value = page
    fetchProfileDetails()
  }
}

const fetchProfileDetails = async () => {
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

    const response = await apiClient.get('/profile-details', { params })

    if (response.data.success) {
      users.value = response.data.data.users
      pagination.value = response.data.data.pagination
    } else {
      error.value = 'خطا در دریافت اطلاعات جزئیات پروفایل'
    }
  } catch (err) {
    console.error('Profile details fetch error:', err)

    // If 401/403, don't set error message - axios interceptor will handle redirect
    if (err.response && (err.response.status === 401 || err.response.status === 403)) {
      users.value = []
      pagination.value = null
      loading.value = false
      return
    }

    error.value = err.response?.data?.message || 'خطا در بارگذاری اطلاعات'
    users.value = []
    pagination.value = null
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchProfileDetails()
})
</script>

<style scoped>
/* Additional styles if needed */
</style>

