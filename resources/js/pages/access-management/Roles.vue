<template>
  <div class="p-6 space-y-6">
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-[var(--theme-text-primary)] mb-2">مدیریت نقش ها</h1>
      <p class="text-[var(--theme-text-secondary)]">ایجاد و مدیریت نقش‌های دسترسی</p>
    </div>

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
      @page-change="goToPage"
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
import { Table, Pagination, LoadingState, ErrorState, Button } from '../../components/ui'
import CreateRoleModal from '../../components/access-management/CreateRoleModal.vue'
import UpdateRoleModal from '../../components/access-management/UpdateRoleModal.vue'
import { useToast } from '../../composables/useToast'
import TableActionIcon from '../../components/icons/TableActionIcon.vue'
import { confirm } from '../../utils/notifications'

const { showToast } = useToast()

const loading = ref(true)
const error = ref(null)
const roles = ref([])
const pagination = ref(null)
const currentPage = ref(1)
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

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value?.last_page) {
    currentPage.value = page
    fetchRoles()
  }
}

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

const fetchRoles = async () => {
  try {
    loading.value = true
    error.value = null

    const params = {
      page: currentPage.value,
      per_page: 10,
    }

    const response = await apiClient.get('/roles', { params })

    if (response.data.success) {
      roles.value = response.data.data.roles
      pagination.value = response.data.data.pagination
    } else {
      error.value = 'خطا در دریافت اطلاعات'
    }
  } catch (err) {
    console.error('Roles fetch error:', err)

    if (err.response && (err.response.status === 401 || err.response.status === 403)) {
      roles.value = []
      pagination.value = null
      loading.value = false
      return
    }

    error.value = err.response?.data?.message || 'خطا در بارگذاری اطلاعات'
    roles.value = []
    pagination.value = null
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchRoles()
})
</script>

<style scoped>
/* Additional styles if needed */
</style>

