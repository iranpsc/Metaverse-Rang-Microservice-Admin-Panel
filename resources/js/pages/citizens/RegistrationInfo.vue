<template>
  <div class="p-6 space-y-6">
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-[var(--theme-text-primary)] mb-2">اطلاعات ثبت نام</h1>
      <p class="text-[var(--theme-text-secondary)]">مدیریت و مشاهده اطلاعات ثبت نام کاربران</p>
    </div>

    <!-- Search Box -->
    <div class="mb-6">
      <SearchBox
        v-model="searchTerm"
        placeholder="جستجو بر اساس نام یا ایمیل..."
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
      empty-state-message="کاربری تعریف نشده است"
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
import { ref, computed, onMounted } from 'vue'
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
    label: 'نام کاربری'
  },
  {
    key: 'name',
    label: 'نام'
  },
  {
    key: 'email',
    label: 'ایمیل'
  },
  {
    key: 'email_verified_at',
    label: 'تاریخ وریفای ایمیل',
    textSecondary: true,
    defaultValue: '-'
  },
  {
    key: 'ip',
    label: 'آی پی ثبت نام',
    textSecondary: true,
    defaultValue: '-'
  }
]

// Search handler (called by SearchBox with debounce)
const handleSearch = () => {
  currentPage.value = 1
  fetchRegistrationInfo()
}

const handleClear = () => {
  currentPage.value = 1
  fetchRegistrationInfo()
}

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value?.last_page) {
    currentPage.value = page
    fetchRegistrationInfo()
  }
}

const fetchRegistrationInfo = async () => {
  try {
    loading.value = true
    error.value = null

    const params = {
      page: currentPage.value,
      per_page: 10,
    }

    if (searchTerm.value) {
      params.search = searchTerm.value
    }

    const response = await apiClient.get('/registration-info', { params })

    if (response.data.success) {
      users.value = response.data.data.users
      pagination.value = response.data.data.pagination
    } else {
      error.value = 'خطا در دریافت اطلاعات ثبت نام'
    }
  } catch (err) {
    console.error('Registration info fetch error:', err)

    // If 401/403, don't set error message - axios interceptor will handle redirect
    if (err.response && (err.response.status === 401 || err.response.status === 403)) {
      // Auth failed - let the interceptor handle redirect
      // Don't set error message as redirect will happen
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
  fetchRegistrationInfo()
})
</script>

<style scoped>
/* Additional styles if needed */
</style>

