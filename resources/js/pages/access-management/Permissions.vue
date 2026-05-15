<template>
  <div class="p-6 space-y-6">
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-[var(--theme-text-primary)] mb-2">مدیریت دسترسی ها</h1>
      <p class="text-[var(--theme-text-secondary)]">ایجاد و مدیریت دسترسی‌های سیستم</p>
    </div>

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
      @page-change="goToPage"
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

    <PhoneVerificationModal :phone-verification="phoneVerification" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import apiClient from '../../utils/api'
import { Table, Pagination, LoadingState, ErrorState, Button } from '../../components/ui'
import CreatePermissionModal from '../../components/access-management/CreatePermissionModal.vue'
import UpdatePermissionModal from '../../components/access-management/UpdatePermissionModal.vue'
import { useToast } from '../../composables/useToast'
import PhoneVerificationModal from '../../components/PhoneVerificationModal.vue'
import { usePhoneVerification } from '../../composables/usePhoneVerification'
import TableActionIcon from '../../components/icons/TableActionIcon.vue'

const { showToast } = useToast()
const phoneVerification = usePhoneVerification()

const loading = ref(true)
const error = ref(null)
const permissions = ref([])
const pagination = ref(null)
const currentPage = ref(1)
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

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value?.last_page) {
    currentPage.value = page
    fetchPermissions()
  }
}

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
  await phoneVerification.confirmThenVerify(
    {
      message: 'آیا می خواهید این دسترسی را حذف کنید؟',
      title: 'حذف دسترسی',
      confirmText: 'بله، حذف شود',
      cancelText: 'انصراف'
    },
    async (payload) => {
      try {
        await apiClient.delete(`/permissions/${id}`, { data: payload })
        showToast('دسترسی با موفقیت حذف شد', 'success')
        phoneVerification.resetVerificationState()
        fetchPermissions()
      } catch (err) {
        console.error('Delete permission error:', err)

        if (await phoneVerification.handleApiVerificationError(err)) {
          return
        }

        showToast(err.response?.data?.message || 'خطا در حذف دسترسی', 'error')
      }
    }
  )
}

const fetchPermissions = async () => {
  try {
    loading.value = true
    error.value = null

    const params = {
      page: currentPage.value,
      per_page: 10,
    }

    const response = await apiClient.get('/permissions', { params })

    if (response.data.success) {
      permissions.value = response.data.data.permissions
      pagination.value = response.data.data.pagination
    } else {
      error.value = 'خطا در دریافت اطلاعات'
    }
  } catch (err) {
    console.error('Permissions fetch error:', err)

    if (err.response && (err.response.status === 401 || err.response.status === 403)) {
      permissions.value = []
      pagination.value = null
      loading.value = false
      return
    }

    error.value = err.response?.data?.message || 'خطا در بارگذاری اطلاعات'
    permissions.value = []
    pagination.value = null
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchPermissions()
})
</script>

<style scoped>
/* Additional styles if needed */
</style>

