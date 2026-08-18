<template>
  <div class="p-6 space-y-6" dir="rtl">
    <!-- Page Header -->
    <div class="space-y-3">
      <h1 class="text-3xl font-bold text-[var(--theme-text-primary)]">مدیریت کدهای ISIC</h1>
      <p class="text-[var(--theme-text-secondary)] max-w-2xl leading-6">
        ایجاد، جستجو و مدیریت وضعیت تایید کدهای ISIC با تجربه‌ای یکپارچه و مدرن در محیط متاورس.
      </p>
    </div>

    <!-- Toolbar -->
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
      <SearchBox
        v-model="searchTerm"
        placeholder="جستجو بر اساس نام یا کد..."
        :debounce-ms="400"
        container-class="w-full lg:flex-1 lg:max-w-3xl"
        @search="handleSearch"
        @clear="handleClear"
      />
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end lg:flex-row lg:gap-4 lg:flex-none">
        <Button
          variant="primary"
          rounded="full"
          class="w-full sm:w-auto lg:w-auto"
          @click="openCreateModal"
        >
          ایجاد کد جدید
        </Button>
        <Button
          variant="glass"
          rounded="full"
          class="w-full sm:w-auto lg:w-auto"
          @click="openImportModal"
        >
          درون‌ریزی فایل اکسل
        </Button>
      </div>
    </div>

    <!-- Loading / Error States -->
    <LoadingState v-if="loading" />

    <ErrorState
      v-else-if="error"
      :message="error"
      variant="error"
    />

    <!-- Data Table -->
    <div v-else class="space-y-4">
      <Table
        :columns="tableColumns"
        :data="isicCodes"
        :pagination="pagination"
        empty-state-message="کد ISIC ثبت نشده است."
      >
        <template #cell-verified="{ value }">
          <Badge :variant="value ? 'success' : 'warning'">
            {{ value ? 'تایید شده' : 'در انتظار تایید' }}
          </Badge>
        </template>

        <template #cell-actions="{ row }">
          <div class="flex flex-wrap items-center justify-end gap-2">
            <Button
              v-if="!row.verified"
              size="sm"
              variant="success"
              :loading="actionLoading.approve === row.id"
              @click="handleApprove(row)"
            >
              تایید
            </Button>
            <Button
              v-if="!row.verified"
              size="sm"
              variant="warning"
              :loading="actionLoading.deny === row.id"
              @click="handleDeny(row)"
            >
              عدم تایید
            </Button>
            <Button
              size="sm"
              variant="danger"
              rounded="full"
              class="!p-2 !gap-0 min-w-[2.25rem]"
              title="حذف"
              aria-label="حذف"
              :loading="actionLoading.delete === row.id"
              @click="handleDelete(row)"
            >
              <template #icon-left>
                <TableActionIcon name="delete" />
              </template>
            </Button>
          </div>
        </template>
      </Table>

      <Pagination
        v-if="pagination && pagination.total > 0"
        :pagination="pagination"
        :disabled="loading"
        @page-change="goToPage"
      />
    </div>

    <!-- Create Modal -->
    <Modal
      :model-value="createModalOpen"
      @update:model-value="closeCreateModal"
      title="ایجاد کد ISIC"
      size="md"
    >
      <div class="space-y-5">
        <Input
          v-model="form.name"
          label="نام"
          placeholder="نام کد ISIC"
          required
          :error="formErrors.name"
        />
        <Input
          v-model="form.code"
          label="کد"
          placeholder="کد عددی ISIC"
          required
          inputmode="numeric"
          :pattern="codePattern"
          :error="formErrors.code"
        />
      </div>

      <template #footer>
        <div class="flex justify-end gap-3">
          <Button
            variant="primary"
            :loading="saving"
            @click="handleCreateSubmit"
          >
            ثبت
          </Button>
          <Button
            variant="danger"
            :disabled="saving"
            @click="closeCreateModal"
          >
            بستن
          </Button>
        </div>
      </template>
    </Modal>

    <!-- Import Modal -->
    <Modal
      :model-value="importModalOpen"
      @update:model-value="closeImportModal"
      title="درون‌ریزی کدهای ISIC"
      size="md"
    >
      <div class="space-y-5">
        <FileInput
          v-model="importFile"
          label="انتخاب فایل"
          placeholder="فایل اکسل شامل کدها را انتخاب کنید"
          accept=".xlsx,.xls"
          :error="importError"
          helper-text="فرمت‌های مجاز: xlsx، xls"
        />

        <p class="text-xs text-[var(--theme-text-secondary)] leading-6">
          فایل انتخابی به صورت صف پردازش شده و نتایج آن پس از اتمام در لیست نمایش داده می‌شود. لطفاً تا پایان پردازش منتظر بمانید.
        </p>
      </div>

      <template #footer>
        <div class="flex justify-end gap-3">
          <Button
            variant="primary"
            :loading="importing"
            @click="submitImport"
          >
            شروع درون‌ریزی
          </Button>
          <Button
            variant="danger"
            :disabled="importing"
            @click="closeImportModal"
          >
            بستن
          </Button>
        </div>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref, computed } from 'vue'
import apiClient from '../../utils/api'
import { Badge, Button, ErrorState, FileInput, Input, LoadingState, Modal, Pagination, SearchBox, Table } from '../../components/ui'
import { useToast } from '../../composables/useToast'
import { confirm } from '../../utils/notifications'
import TableActionIcon from '../../components/icons/TableActionIcon.vue'

const { showToast } = useToast()


const loading = ref(true)
const error = ref(null)
const isicCodes = ref([])
const pagination = ref(null)
const currentPage = ref(1)
const searchTerm = ref('')
const perPage = 10

const createModalOpen = ref(false)
const importModalOpen = ref(false)

const form = reactive({
  name: '',
  code: ''
})

const formErrors = reactive({
  name: '',
  code: ''
})

const saving = ref(false)

const importFile = ref(null)
const importing = ref(false)
const importError = ref('')

const codePattern = '^\\d{1,20}$'

const actionLoading = reactive({
  approve: null,
  deny: null,
  delete: null
})

const tableColumns = [
  {
    key: 'name',
    label: 'نام'
  },
  {
    key: 'code',
    label: 'کد'
  },
  {
    key: 'verified',
    label: 'وضعیت'
  },
  {
    key: 'actions',
    label: 'اقدامات'
  }
]

const fetchIsicCodes = async () => {
  try {
    loading.value = true
    error.value = null

    const params = {
      page: currentPage.value,
      per_page: perPage
    }

    if (searchTerm.value.trim()) {
      params.search = searchTerm.value.trim()
    }

    const { data } = await apiClient.get('/isic-codes', { params })

    if (data?.success) {
      isicCodes.value = data.data?.isic_codes ?? []
      pagination.value = data.data?.pagination ?? null
    } else {
      throw new Error(data?.message || 'خطا در دریافت کدهای ISIC')
    }
  } catch (err) {
    console.error('ISIC codes fetch error:', err)
    error.value = err.response?.data?.message || err.message || 'خطا در بارگذاری کدهای ISIC'
    isicCodes.value = []
    pagination.value = null
  } finally {
    loading.value = false
  }
}

const resetForm = () => {
  form.name = ''
  form.code = ''
  formErrors.name = ''
  formErrors.code = ''
}

const validateCreateForm = () => {
  formErrors.name = ''
  formErrors.code = ''

  if (!form.name.trim()) {
    formErrors.name = 'لطفاً نام را وارد کنید.'
  }

  if (!form.code.trim()) {
    formErrors.code = 'لطفاً کد را وارد کنید.'
  } else if (!new RegExp(codePattern).test(form.code.trim())) {
    formErrors.code = 'کد باید فقط شامل اعداد و حداکثر ۲۰ رقم باشد.'
  }

  return !formErrors.name && !formErrors.code
}

const openCreateModal = () => {
  resetForm()
  createModalOpen.value = true
}

const closeCreateModal = () => {
  createModalOpen.value = false
  resetForm()
}

const submitCreateRequest = async () => {
  try {
    saving.value = true

    const payload = {
      name: form.name.trim(),
      code: form.code.trim()
    }

    const { data } = await apiClient.post('/isic-codes', payload)

    if (data?.success) {
      showToast(data.message || 'کد ISIC با موفقیت ایجاد شد.', 'success')
      closeCreateModal()
      currentPage.value = 1
      await fetchIsicCodes()
    } else {
      throw new Error(data?.message || 'خطا در ایجاد کد ISIC')
    }
  } catch (err) {
    console.error('ISIC code create error:', err)

    if (err.response?.status === 422) {
      const validationErrors = err.response?.data?.errors || {}
      formErrors.name = validationErrors.name?.[0] || ''
      formErrors.code = validationErrors.code?.[0] || ''
      showToast(err.response?.data?.message || 'ورودی‌های ارسالی معتبر نیست.', 'error')
    } else {
      const message = err.response?.data?.message || err.message || 'خطایی در ثبت کد ISIC رخ داده است.'
      showToast(message, 'error')
    }
  } finally {
    saving.value = false
  }
}

const handleCreateSubmit = async () => {
  if (!validateCreateForm()) {
    return
  }

  await submitCreateRequest()
}

const openImportModal = () => {
  importModalOpen.value = true
}

const closeImportModal = () => {
  importModalOpen.value = false
  importFile.value = null
  importError.value = ''
}

const submitImport = async () => {
  importError.value = ''

  if (!(importFile.value instanceof File)) {
    importError.value = 'لطفاً فایل معتبری انتخاب کنید.'
    return
  }

  const formData = new FormData()
  formData.append('file', importFile.value)

  try {
    importing.value = true

    const { data } = await apiClient.post('/isic-codes/import', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    if (data?.success) {
      showToast(data.message || 'درون‌ریزی کدهای ISIC آغاز شد.', 'success')
      closeImportModal()
      await fetchIsicCodes()
    } else {
      throw new Error(data?.message || 'خطا در درون‌ریزی کدهای ISIC')
    }
  } catch (err) {
    console.error('ISIC codes import error:', err)

    if (err.response?.status === 422) {
      importError.value = err.response?.data?.errors?.file?.[0] || 'فایل انتخابی معتبر نیست.'
    } else {
      importError.value = err.response?.data?.message || err.message || 'خطا در پردازش فایل درون‌ریزی.'
    }

    showToast(importError.value, 'error')
  } finally {
    importing.value = false
  }
}

const updateIsicCodeInList = (updated) => {
  const index = isicCodes.value.findIndex((item) => item.id === updated.id)
  if (index !== -1) {
    isicCodes.value[index] = {
      ...isicCodes.value[index],
      ...updated
    }
  }
}

const handleApprove = async (row) => {
  try {
    actionLoading.approve = row.id
    const { data } = await apiClient.post(`/isic-codes/${row.id}/approve`)

    if (data?.success) {
      showToast(data.message || 'کد ISIC تایید شد.', 'success')
      const updated = data.data?.isic_code
      if (updated) {
        updateIsicCodeInList(updated)
      } else {
        await fetchIsicCodes()
      }
    } else {
      throw new Error(data?.message || 'خطا در تایید کد ISIC')
    }
  } catch (err) {
    console.error('ISIC code approve error:', err)
    const message = err.response?.data?.message || err.message || 'خطا در تایید کد ISIC'
    showToast(message, 'error')
  } finally {
    actionLoading.approve = null
  }
}

const handleDeny = async (row) => {
  try {
    actionLoading.deny = row.id
    const { data } = await apiClient.post(`/isic-codes/${row.id}/deny`)

    if (data?.success) {
      showToast(data.message || 'کد ISIC در انتظار تایید قرار گرفت.', 'success')
      const updated = data.data?.isic_code
      if (updated) {
        updateIsicCodeInList(updated)
      } else {
        await fetchIsicCodes()
      }
    } else {
      throw new Error(data?.message || 'خطا در تغییر وضعیت کد ISIC')
    }
  } catch (err) {
    console.error('ISIC code deny error:', err)
    const message = err.response?.data?.message || err.message || 'خطا در تغییر وضعیت کد ISIC'
    showToast(message, 'error')
  } finally {
    actionLoading.deny = null
  }
}

const handleDelete = async (row) => {
  const result = await confirm(
    'آیا از حذف این کد ISIC اطمینان دارید؟',
    'حذف کد ISIC',
    { confirmText: 'بله، حذف شود', cancelText: 'انصراف' }
  )
  if (!result.isConfirmed) return

  try {
        actionLoading.delete = row.id
        const { data } = await apiClient.delete(`/isic-codes/${row.id}`)

        if (data?.success) {
          showToast(data.message || 'کد ISIC با موفقیت حذف شد.', 'success')

          const isLastItem = isicCodes.value.length === 1
          const isNotFirstPage = currentPage.value > 1

          if (isLastItem && isNotFirstPage) {
            currentPage.value -= 1
          }

          await fetchIsicCodes()
        } else {
          throw new Error(data?.message || 'خطا در حذف کد ISIC')
        }
      } catch (err) {
        console.error('ISIC code delete error:', err)

        const message = err.response?.data?.message || err.message || 'خطا در حذف کد ISIC'
        showToast(message, 'error')
      } finally {
        actionLoading.delete = null
      }
}

const goToPage = (page) => {
  if (!pagination.value) {
    return
  }

  if (page >= 1 && page <= pagination.value.last_page) {
    currentPage.value = page
    fetchIsicCodes()
  }
}

const handleSearch = () => {
  currentPage.value = 1
  fetchIsicCodes()
}

const handleClear = () => {
  searchTerm.value = ''
  currentPage.value = 1
  fetchIsicCodes()
}

onMounted(() => {
  fetchIsicCodes()
})
</script>

<style scoped>
.text-error {
  color: var(--color-error, #EF4444);
}
</style>


