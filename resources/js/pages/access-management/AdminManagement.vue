<template>
  <div class="p-6 space-y-6">
    <PageHeader
      title="مدیریت مدیران"
      subtitle="ایجاد و مدیریت مدیران سیستم"
    />

    <!-- Create Button -->
    <div class="mb-6">
      <Button
        variant="primary"
        @click="openCreateModal"
      >
        ایجاد مدیر
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
      empty-state-message="مدیری تعریف نشده است"
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

    <AdminModal
      :show="showModal"
      :admin-id="selectedAdminId"
      @close="closeModal"
      @saved="handleAdminSaved"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import apiClient from '../../utils/api'
import { Table, LoadingState, ErrorState, Button, Badge, PageHeader } from '../../components/ui'
import AdminModal from '../../components/access-management/AdminModal.vue'
import { useToast } from '../../composables/useToast'
import { confirm } from '../../utils/notifications'
import TableActionIcon from '../../components/icons/TableActionIcon.vue'

const { showToast } = useToast()

const loading = ref(true)
const error = ref(null)
const admins = ref([])
const showModal = ref(false)
const selectedAdminId = ref(null)

const tableColumns = [
  {
    key: 'code',
    label: 'کد شهروندی',
    defaultValue: '-'
  },
  {
    key: 'phone',
    label: 'تلفن',
    defaultValue: '-'
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

const openCreateModal = () => {
  selectedAdminId.value = null
  showModal.value = true
}

const openUpdateModal = (id) => {
  selectedAdminId.value = id
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  selectedAdminId.value = null
}

const handleAdminSaved = () => {
  closeModal()
  fetchAdmins()
}

const handleDelete = async (id) => {
  const result = await confirm(
    'آیا می خواهید این مدیر را حذف کنید؟',
    'تایید حذف',
    { confirmText: 'بله، حذف شود', cancelText: 'انصراف' }
  )
  if (!result.isConfirmed) return

  try {
    await apiClient.delete(`/admins/${id}`)
    showToast('مدیر با موفقیت حذف شد', 'success')
    fetchAdmins()
  } catch (err) {
    console.error('Delete admin error:', err)

    showToast(err.response?.data?.message || 'خطا در حذف مدیر', 'error')
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
