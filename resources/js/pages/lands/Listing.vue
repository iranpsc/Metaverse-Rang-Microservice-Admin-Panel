<template>
  <div class="p-6 space-y-6">
    <PageHeader
      title="لیست املاک"
      subtitle="مدیریت و مشاهده اطلاعات املاک"
    />

    <!-- Search and Actions Row -->
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
      <div class="w-full lg:max-w-md">
        <SearchBox
          v-model="searchTerm"
          placeholder="شناسه ملک یا کد کاربری را وارد کنید"
          :debounce-ms="500"
          container-class="w-full"
          @search="handleSearch"
          @clear="handleClear"
        />
      </div>
      <Button variant="primary" class="shrink-0" @click="showTransferModal = true">
        انتقال مالکیت
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
      v-else-if="properties && properties.length > 0"
      :columns="tableColumns"
      :data="properties"
      :pagination="pagination"
      show-row-number
    >
    </Table>

    <!-- Empty State -->
    <Alert
      v-else
      variant="danger"
      message="ملکی یافت نشد"
      :dismissible="false"
    />

    <!-- Pagination -->
    <Pagination
      v-if="pagination && pagination.total > 0"
      :pagination="pagination"
      :disabled="loading"
      @page-change="onPageChange"
    />

    <TransferOwnerModal
      v-model="showTransferModal"
      @transferred="fetchProperties"
    />

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import apiClient from '../../utils/api'
import { Table, Pagination, SearchBox, LoadingState, ErrorState, Alert, Button, PageHeader } from '../../components/ui'
import TransferOwnerModal from '../../components/lands/TransferOwnerModal.vue'
import { formatGregorianSlashDate } from '../../utils/dateFormatter'
import { getKarbariLabel } from '../../utils/lands/karbariLabels'
import { truncateWithEllipsis } from '../../utils/text'
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

const properties = ref([])
const showTransferModal = ref(false)

// Table columns configuration
const tableColumns = [
  {
    key: 'id',
    label: 'کد زمین'
  },
  {
    key: 'area',
    label: 'مساحت'
  },
  {
    key: 'density',
    label: 'تراکم'
  },
  {
    key: 'application_title',
    label: 'نوع کاربری'
  },
  {
    key: 'address',
    label: 'آدرس'
  },
  {
    key: 'date',
    label: 'تاریخ ثبت'
  },
  {
    key: 'publisher_name',
    label: 'ثبت کننده'
  },
  {
    key: 'owner_name',
    label: 'مالک'
  }
]

const clearProperties = () => {
  properties.value = []
  pagination.value = null
}

const fetchProperties = () => execute(async () => {
  const response = await apiClient.get('/lands', { params: buildParams() })

  if (response.data.success) {
    properties.value = response.data.data.properties.map(property => ({
      id: property.id,
      area: property.area,
      density: property.density,
      application_title: getKarbariLabel(property.karbari),
      address: property.address?.length > 15
        ? truncateWithEllipsis(property.address, 15, '...')
        : property.address,
      date: property.date ? formatGregorianSlashDate(property.date) : '-',
      publisher_name: property.feature?.map?.publisher_name || '-',
      owner_name: getOwnerName(property.feature),
      feature: property.feature
    }))
    pagination.value = response.data.data.pagination
  } else {
    error.value = 'خطا در دریافت اطلاعات املاک'
    clearProperties()
  }
}, {
  onClear: clearProperties,
  logLabel: 'Properties fetch error:',
  fallbackMessage: 'خطا در بارگذاری اطلاعات'
})

const handleSearch = () => search(fetchProperties)
const handleClear = () => clear(fetchProperties)
const onPageChange = (page) => goToPage(page, fetchProperties)

const getOwnerName = (feature) => {
  if (!feature) return '-'

  if (feature.owner_id === 1) {
    return 'سیستم'
  }

  return feature.owner?.name || '-'
}

onMounted(() => {
  fetchProperties()
})
</script>

<style scoped>
/* Additional styles if needed */
</style>

