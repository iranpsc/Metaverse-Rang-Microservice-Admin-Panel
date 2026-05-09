<template>
  <div class="p-6 space-y-6">
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-[var(--theme-text-primary)] mb-2">احراز هویت</h1>
      <p class="text-[var(--theme-text-secondary)]">مدیریت و بررسی درخواست‌های احراز هویت کاربران</p>
    </div>

    <!-- Search and Actions Row -->
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between mb-6">
      <div class="flex-1 max-w-md">
        <SearchBox
          v-model="searchTerm"
          placeholder="کد ملی را وارد کنید"
          :debounce-ms="500"
          @search="handleSearch"
          @clear="handleClear"
        />
      </div>
      <div class="flex gap-2">
        <Button
          @click="showVideoTextModal = true"
          variant="primary"
        >
          بارگذاری متن احراز ویدیویی
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
      :data="kycs"
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

    <!-- KYC Details Modal -->
    <KycDetailsModal
      v-if="selectedKycId"
      :kyc-id="selectedKycId"
      :show="showDetailsModal"
      @close="closeDetailsModal"
      @updated="handleKycUpdated"
    />

    <!-- Video Text Modal -->
    <KycVideoTextModal
      :show="showVideoTextModal"
      @close="showVideoTextModal = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import apiClient from '../../utils/api'
import { Table, Pagination, SearchBox, LoadingState, ErrorState, Button, Badge } from '../../components/ui'
import KycDetailsModal from '../../components/citizens/KycDetailsModal.vue'
import KycVideoTextModal from '../../components/citizens/KycVideoTextModal.vue'
import TableActionIcon from '../../components/icons/TableActionIcon.vue'
import { stripHtml } from '../../utils/sanitize'

const loading = ref(true)
const error = ref(null)
const kycs = ref([])
const pagination = ref(null)
const searchTerm = ref('')
const currentPage = ref(1)
const selectedKycId = ref(null)
const showDetailsModal = ref(false)
const showVideoTextModal = ref(false)

// Table columns configuration
const tableColumns = [
  {
    key: 'id',
    label: 'شناسه'
  },
  {
    key: 'fname',
    label: 'نام'
  },
  {
    key: 'lname',
    label: 'نام خانوادگی'
  },
  {
    key: 'melli_code',
    label: 'کد ملی'
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
  fetchKycs()
}

const handleClear = () => {
  currentPage.value = 1
  fetchKycs()
}

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value?.last_page) {
    currentPage.value = page
    fetchKycs()
  }
}

const openDetailsModal = (id) => {
  selectedKycId.value = id
  showDetailsModal.value = true
}

const closeDetailsModal = () => {
  showDetailsModal.value = false
  selectedKycId.value = null
}

const handleKycUpdated = () => {
  closeDetailsModal()
  fetchKycs()
}

const fetchKycs = async () => {
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

    const response = await apiClient.get('/kycs', { params })

    if (response.data.success) {
      kycs.value = response.data.data.kycs
      pagination.value = response.data.data.pagination
    } else {
      error.value = 'خطا در دریافت اطلاعات احراز هویت'
    }
  } catch (err) {
    console.error('KYC fetch error:', err)

    // If 401/403, don't set error message - axios interceptor will handle redirect
    if (err.response && (err.response.status === 401 || err.response.status === 403)) {
      // Auth failed - let the interceptor handle redirect
      // Don't set error message as redirect will happen
      kycs.value = []
      pagination.value = null
      loading.value = false
      return
    }

    error.value = err.response?.data?.message || 'خطا در بارگذاری اطلاعات'
    kycs.value = []
    pagination.value = null
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchKycs()
})
</script>

<style scoped>
/* Additional styles if needed */
</style>

