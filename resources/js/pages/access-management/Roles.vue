<template>
  <div class="p-6 space-y-6">
    <PageHeader
      title="مدیریت نقش ها"
      subtitle="ایجاد و مدیریت نقش‌های دسترسی"
    />

    <!-- Create Button -->
    <div class="mb-6">
      <Button
        variant="primary"
        @click="showCreateModal = true"
      >
        ایجاد مسئولیت
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
      :data="roles"
      :pagination="pagination"
      empty-state-message="مسئولیتی تعریف نشده است"
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

    <!-- Create Role Modal -->
    <CreateRoleModal
      :show="showCreateModal"
      @close="showCreateModal = false"
      @created="handleRoleCreated"
    />

    <!-- Update Role Modal -->
    <UpdateRoleModal
      v-if="selectedRoleId"
      :show="showUpdateModal"
      :role-id="selectedRoleId"
      @close="closeUpdateModal"
      @updated="handleRoleUpdated"
    />

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import apiClient from '../../utils/api'
import { Table, Pagination, LoadingState, ErrorState, Button, PageHeader } from '../../components/ui'
import { usePaginatedList } from '../../composables/usePaginatedList'
import CreateRoleModal from '../../components/access-management/CreateRoleModal.vue'
import UpdateRoleModal from '../../components/access-management/UpdateRoleModal.vue'
import { useToast } from '../../composables/useToast'
import TableActionIcon from '../../components/icons/TableActionIcon.vue'
import { confirm } from '../../utils/notifications'

const { showToast } = useToast()

const {
  loading,
  error,
  pagination,
  currentPage,
  execute,
  goToPage,
  buildParams
} = usePaginatedList()

const roles = ref([])
const showCreateModal = ref(false)
const showUpdateModal = ref(false)
const selectedRoleId = ref(null)

// Table columns configuration
const tableColumns = [
  {
    key: 'id',
    label: 'ردیف'
  },
  {
    key: 'title',
    label: 'عنوان مسئولیت'
  },
  {
    key: 'name',
    label: 'نام مسئولیت'
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

const onPageChange = (page) => goToPage(page, fetchRoles)

const openUpdateModal = (id) => {
  selectedRoleId.value = id
  showUpdateModal.value = true
}

const closeUpdateModal = () => {
  showUpdateModal.value = false
  selectedRoleId.value = null
}

const handleRoleCreated = () => {
  showCreateModal.value = false
  fetchRoles()
}

const handleRoleUpdated = () => {
  closeUpdateModal()
  fetchRoles()
}

const handleDelete = async (id) => {
  const result = await confirm(
    'آیا می خواهید این مسئولیت را حذف کنید؟',
    'حذف مسئولیت',
    { confirmText: 'بله، حذف شود', cancelText: 'انصراف' }
  )
  if (!result.isConfirmed) return

  try {
    await apiClient.delete(`/roles/${id}`)
    showToast('مسئولیت با موفقیت حذف شد', 'success')

    if (pagination.value && pagination.value.current_page > 1) {
      const itemsOnCurrentPage = roles.value.length
      if (itemsOnCurrentPage === 1) {
        currentPage.value = pagination.value.current_page - 1
      }
    }

    fetchRoles()
  } catch (err) {
    console.error('Delete role error:', err)
    showToast(err.response?.data?.message || 'خطا در حذف مسئولیت', 'error')
  }
}

const clearRoles = () => {
  roles.value = []
  pagination.value = null
}

const fetchRoles = () => execute(async () => {
  const response = await apiClient.get('/roles', { params: buildParams() })

  if (response.data.success) {
    roles.value = response.data.data.roles
    pagination.value = response.data.data.pagination
  } else {
    error.value = 'خطا در دریافت اطلاعات'
    clearRoles()
  }
}, {
  onClear: clearRoles,
  logLabel: 'Roles fetch error:',
  fallbackMessage: 'خطا در بارگذاری اطلاعات'
})

onMounted(() => {
  fetchRoles()
})
</script>

<style scoped>
/* Additional styles if needed */
</style>

