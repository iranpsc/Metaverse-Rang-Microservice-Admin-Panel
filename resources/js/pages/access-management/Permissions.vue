<template>
  <div class="p-6 space-y-6">
    <PageHeader
      title="مدیریت دسترسی ها"
      subtitle="ایجاد و مدیریت دسترسی‌های سیستم"
    />

    <!-- Create Button -->
    <div class="mb-6">
      <Button
        variant="primary"
        @click="showCreateModal = true"
      >
        ایجاد دسترسی
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
      :data="permissions"
      :pagination="pagination"
      empty-state-message="دسترسی تعریف نشده است"
    >
      <template #cell-actions="{ row }">
        <div class="flex gap-2">
          <Button
            size="sm"
            variant="danger"
            rounded="full"
            class="!p-2 !gap-0 min-w-[2.25rem]"
            title="حذف"
            aria-label="حذف"
            @click="handleDelete(row.id)"
          >
            <template #icon-left>
              <TableActionIcon name="delete" />
            </template>
          </Button>
          <Button
            size="sm"
            variant="primary"
            rounded="full"
            class="!p-2 !gap-0 min-w-[2.25rem]"
            title="بروزرسانی"
            aria-label="بروزرسانی"
            @click="openUpdateModal(row.id)"
          >
            <template #icon-left>
              <TableActionIcon name="edit" />
            </template>
          </Button>
        </div>
      </template>
    </Table>

    <!-- Pagination -->
    <Pagination
      v-if="pagination && pagination.total > 0"
      :pagination="pagination"
      :disabled="loading"
      @page-change="onPageChange"
    />

    <!-- Create Permission Modal -->
    <CreatePermissionModal
      :show="showCreateModal"
      @close="showCreateModal = false"
      @created="handlePermissionCreated"
    />

    <!-- Update Permission Modal -->
    <UpdatePermissionModal
      v-if="selectedPermissionId"
      :show="showUpdateModal"
      :permission-id="selectedPermissionId"
      @close="closeUpdateModal"
      @updated="handlePermissionUpdated"
    />

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import apiClient from '../../utils/api'
import { Table, Pagination, LoadingState, ErrorState, Button, PageHeader } from '../../components/ui'
import { usePaginatedList } from '../../composables/usePaginatedList'
import CreatePermissionModal from '../../components/access-management/CreatePermissionModal.vue'
import UpdatePermissionModal from '../../components/access-management/UpdatePermissionModal.vue'
import { useToast } from '../../composables/useToast'
import TableActionIcon from '../../components/icons/TableActionIcon.vue'
import { confirm } from '../../utils/notifications'

const { showToast } = useToast()

const {
  loading,
  error,
  pagination,
  execute,
  goToPage,
  buildParams
} = usePaginatedList()

const permissions = ref([])
const showCreateModal = ref(false)
const showUpdateModal = ref(false)
const selectedPermissionId = ref(null)

// Table columns configuration
const tableColumns = [
  {
    key: 'id',
    label: 'ردیف'
  },
  {
    key: 'title',
    label: 'عنوان دسترسی'
  },
  {
    key: 'name',
    label: 'نام دسترسی'
  },
  {
    key: 'created_at_shamsi',
    label: 'تاریخ ایجاد'
  },
  {
    key: 'created_at_time',
    label: 'ساعت ایجاد'
  },
  {
    key: 'actions',
    label: 'مدیریت'
  }
]

const onPageChange = (page) => goToPage(page, fetchPermissions)

const openUpdateModal = (id) => {
  selectedPermissionId.value = id
  showUpdateModal.value = true
}

const closeUpdateModal = () => {
  showUpdateModal.value = false
  selectedPermissionId.value = null
}

const handlePermissionCreated = () => {
  showCreateModal.value = false
  fetchPermissions()
}

const handlePermissionUpdated = () => {
  closeUpdateModal()
  fetchPermissions()
}

const handleDelete = async (id) => {
  const result = await confirm(
    'آیا می خواهید این دسترسی را حذف کنید؟',
    'حذف دسترسی',
    { confirmText: 'بله، حذف شود', cancelText: 'انصراف' }
  )
  if (!result.isConfirmed) return

  try {
    await apiClient.delete(`/permissions/${id}`)
    showToast('دسترسی با موفقیت حذف شد', 'success')
    fetchPermissions()
  } catch (err) {
    console.error('Delete permission error:', err)
    showToast(err.response?.data?.message || 'خطا در حذف دسترسی', 'error')
  }
}

const clearPermissions = () => {
  permissions.value = []
  pagination.value = null
}

const fetchPermissions = () => execute(async () => {
  const response = await apiClient.get('/permissions', { params: buildParams() })

  if (response.data.success) {
    permissions.value = response.data.data.permissions
    pagination.value = response.data.data.pagination
  } else {
    error.value = 'خطا در دریافت اطلاعات'
    clearPermissions()
  }
}, {
  onClear: clearPermissions,
  logLabel: 'Permissions fetch error:',
  fallbackMessage: 'خطا در بارگذاری اطلاعات'
})

onMounted(() => {
  fetchPermissions()
})
</script>

<style scoped>
/* Additional styles if needed */
</style>

