<template>
  <div class="p-6 space-y-6">
    <PageHeader
      title="احراز هویت"
      subtitle="مدیریت و بررسی درخواست‌های احراز هویت کاربران"
    />

    <!-- Search and Actions Row -->
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between mb-6">
      <div class="flex-1 max-w-md">
        <SearchBox
          v-model="searchTerm"
          placeholder="جستجو با نام کامل، نام، کد ملی یا کد شهروندی"
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
      @page-change="onPageChange"
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
import { Table, Pagination, SearchBox, LoadingState, ErrorState, Button, Badge, PageHeader } from '../../components/ui'
import KycDetailsModal from '../../components/citizens/KycDetailsModal.vue'
import KycVideoTextModal from '../../components/citizens/KycVideoTextModal.vue'
import TableActionIcon from '../../components/icons/TableActionIcon.vue'
import { stripHtml } from '../../utils/sanitize'
import { usePaginatedList } from '../../composables/usePaginatedList'

const {
  loading,
  error,
  pagination,
  searchTerm,
  execute,
  search,
  clear,
  goToPage,
  buildParams
} = usePaginatedList()

const kycs = ref([])
const selectedKycId = ref(null)
const showDetailsModal = ref(false)
const showVideoTextModal = ref(false)

// Table columns configuration
const tableColumns = [
  {
    key: 'citizen_code',
    label: 'کد شهروندی',
    defaultValue: '-'
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

const handleSearch = () => search(fetchKycs)
const handleClear = () => clear(fetchKycs)
const onPageChange = (page) => goToPage(page, fetchKycs)

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

const clearKycs = () => {
  kycs.value = []
  pagination.value = null
}

const fetchKycs = () => execute(async () => {
  const params = buildParams()
  const normalizedSearch = searchTerm.value.trim().replace(/\s+/g, ' ')
  if (normalizedSearch) {
    params.search = normalizedSearch
  } else {
    delete params.search
  }

  const response = await apiClient.get('/kycs', { params })

  if (response.data.success) {
    kycs.value = response.data.data.kycs
    pagination.value = response.data.data.pagination
  } else {
    error.value = 'خطا در دریافت اطلاعات احراز هویت'
    clearKycs()
  }
}, {
  onClear: clearKycs,
  logLabel: 'KYC fetch error:',
  fallbackMessage: 'خطا در بارگذاری اطلاعات'
})

onMounted(() => {
  fetchKycs()
})
</script>

<style scoped>
/* Additional styles if needed */
</style>

