<template>
  <div class="p-6 space-y-6">
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-[var(--theme-text-primary)] mb-2">مدیریت دسترسی کارمندان</h1>
      <p class="text-[var(--theme-text-secondary)]">ایجاد و مدیریت دسترسی‌های کارمندان</p>
    </div>

    <!-- Create Button -->
    <div class="mb-6">
      <Button
        variant="primary"
        @click="showCreateModal = true"
      >
        ایجاد کاربر
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
      :data="admins"
      empty-state-message="کاربری تعریف نشده است"
    >
      <template #cell-roles="{ row }">
        <div class="flex flex-wrap gap-2">
          <Badge
            v-for="role in row.roles"
            :key="role.id"
            variant="info"
            size="sm"
          >
            {{ role.title }}
          </Badge>
        </div>
      </template>
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
            title="ویرایش"
            aria-label="ویرایش"
            @click="openUpdateModal(row.id)"
          >
            <template #icon-left>
              <TableActionIcon name="edit" />
            </template>
          </Button>
        </div>
      </template>
    </Table>

    <!-- Create Admin Modal -->
    <CreateAdminModal
      :show="showCreateModal"
      @close="showCreateModal = false"
      @created="handleAdminCreated"
    />

    <!-- Update Admin Modal -->
    <UpdateAdminModal
      v-if="selectedAdminId"
      :show="showUpdateModal"
      :admin-id="selectedAdminId"
      @close="closeUpdateModal"
      @updated="handleAdminUpdated"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import apiClient from '../../utils/api'
import { Table, LoadingState, ErrorState, Button, Badge } from '../../components/ui'
import CreateAdminModal from '../../components/access-management/CreateAdminModal.vue'
import UpdateAdminModal from '../../components/access-management/UpdateAdminModal.vue'
import { useToast } from '../../composables/useToast'
import { confirm } from '../../utils/notifications'
import TableActionIcon from '../../components/icons/TableActionIcon.vue'

const { showToast } = useToast()

const loading = ref(true)
const error = ref(null)
const admins = ref([])
const showCreateModal = ref(false)
const showUpdateModal = ref(false)
const selectedAdminId = ref(null)

// Table columns configuration
const tableColumns = [
  {
    key: 'id',
    label: 'شناسه'
  },
  {
    key: 'name',
    label: 'نام کاربر'
  },
  {
    key: 'roles',
    label: 'مسئولیت ها'
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

const openUpdateModal = (id) => {
  selectedAdminId.value = id
  showUpdateModal.value = true
}

const closeUpdateModal = () => {
  showUpdateModal.value = false
  selectedAdminId.value = null
}

const handleAdminCreated = () => {
  showCreateModal.value = false
  fetchAdmins()
}

const handleAdminUpdated = () => {
  closeUpdateModal()
  fetchAdmins()
}

const handleDelete = async (id) => {
  const result = await confirm(
    'آیا می خواهید این کاربر را حذف کنید؟',
    'تایید حذف',
    {
      confirmText: 'بله، حذف شود',
      cancelText: 'لغو'
    }
  )

  if (!result.isConfirmed) {
    return
  }

  try {
    await apiClient.delete(`/admins/${id}`)
    showToast('کاربر با موفقیت حذف شد', 'success')
    fetchAdmins()
  } catch (err) {
    console.error('Delete admin error:', err)
    showToast(err.response?.data?.message || 'خطا در حذف کاربر', 'error')
  }
}

const fetchAdmins = async () => {
  try {
    loading.value = true
    error.value = null

    const response = await apiClient.get('/admins')

    if (response.data.success) {
      admins.value = response.data.data.admins
    } else {
      error.value = 'خطا در دریافت اطلاعات'
    }
  } catch (err) {
    console.error('Admins fetch error:', err)

    if (err.response && (err.response.status === 401 || err.response.status === 403)) {
      admins.value = []
      loading.value = false
      return
    }

    error.value = err.response?.data?.message || 'خطا در بارگذاری اطلاعات'
    admins.value = []
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchAdmins()
})
</script>

<style scoped>
/* Additional styles if needed */
</style>

