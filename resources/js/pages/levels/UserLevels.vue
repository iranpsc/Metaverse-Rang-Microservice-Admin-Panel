<template>
  <div class="p-6 space-y-6" dir="rtl">
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-[var(--theme-text-primary)] mb-2">سطوح کاربران</h1>
      <p class="text-[var(--theme-text-secondary)]">مشاهده سطوح کاربران و ارتقاء امتیاز</p>
    </div>

    <!-- Search and Actions Row -->
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
      <div class="w-full lg:max-w-md">
        <SearchBox
          v-model="searchTerm"
          placeholder="جستجو بر اساس نام یا کد..."
          :debounce-ms="500"
          container-class="w-full"
          @search="handleSearch"
          @clear="handleClear"
        />
      </div>
      <Button variant="primary" class="shrink-0" @click="openPromoteModal">
        ارتقاء سطح کاربر
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
      v-else-if="users.length > 0"
      :columns="tableColumns"
      :data="users"
      :pagination="pagination"
      :show-row-number="true"
      empty-state-message="کاربری یافت نشد"
    >
      <template #cell-score="{ value }">
        {{ formatScore(value) }}
      </template>

      <template #cell-current_level="{ value }">
        <span v-if="value">{{ value.name }}</span>
        <span v-else class="text-[var(--theme-text-muted)]">-</span>
      </template>

      <template #cell-achieved_levels="{ value }">
        <div v-if="value && value.length" class="flex flex-wrap gap-1 justify-start" dir="rtl">
          <span
            v-for="level in sortAchievedLevels(value)"
            :key="level.id"
            class="inline-flex items-center rounded-full bg-[var(--theme-bg-glass)] border border-[var(--theme-border)] px-2 py-0.5 text-xs text-[var(--theme-text-secondary)]"
          >
            {{ level.name }}
          </span>
        </div>
        <span v-else class="text-[var(--theme-text-muted)]">-</span>
      </template>
    </Table>

    <!-- Empty State -->
    <Alert
      v-else
      variant="danger"
      message="کاربری یافت نشد"
      :dismissible="false"
    />

    <!-- Pagination -->
    <Pagination
      v-if="pagination && pagination.total > 0"
      :pagination="pagination"
      :disabled="loading"
      @page-change="goToPage"
    />

    <PromoteUserLevelModal
      v-model="showPromoteModal"
      @promoted="fetchUsers"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Table, Pagination, SearchBox, LoadingState, ErrorState, Alert, Button } from '../../components/ui'
import PromoteUserLevelModal from '../../components/levels/PromoteUserLevelModal.vue'
import { useUserLevels } from '../../composables/useUserLevels'

const { fetchUsers: fetchUsersApi } = useUserLevels()

const loading = ref(true)
const error = ref(null)
const users = ref([])
const pagination = ref(null)
const searchTerm = ref('')
const currentPage = ref(1)
const showPromoteModal = ref(false)

const tableColumns = [
  { key: 'name', label: 'نام' },
  { key: 'code', label: 'کد' },
  { key: 'score', label: 'امتیاز' },
  { key: 'current_level', label: 'سطح فعلی' },
  { key: 'achieved_levels', label: 'سطح های کسب‌شده' }
]

const sortAchievedLevels = (levels) =>
  [...levels].sort((a, b) => a.score - b.score)

const formatScore = (score) => Number(score ?? 0).toLocaleString('fa-IR')

const handleSearch = () => {
  currentPage.value = 1
  fetchUsers()
}

const handleClear = () => {
  currentPage.value = 1
  fetchUsers()
}

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value?.last_page) {
    currentPage.value = page
    fetchUsers()
  }
}

const openPromoteModal = () => {
  showPromoteModal.value = true
}

const fetchUsers = async () => {
  try {
    loading.value = true
    error.value = null

    const params = {
      page: currentPage.value,
      per_page: 10
    }

    if (searchTerm.value) {
      params.search = searchTerm.value
    }

    const response = await fetchUsersApi(params)

    if (response.data.success) {
      users.value = response.data.data.users
      pagination.value = response.data.data.pagination
    } else {
      error.value = response.data.message || 'خطا در دریافت لیست کاربران'
    }
  } catch (err) {
    console.error('Fetch user levels error:', err)
    error.value = err.response?.data?.message || 'خطا در دریافت لیست کاربران'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchUsers()
})
</script>
