<template>
  <div class="p-6 space-y-6" dir="rtl">
    <header class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
      <div>
        <h1 class="text-3xl font-bold text-[var(--theme-text-primary)]">
          مدیریت تب‌ها
        </h1>
        <p class="text-[var(--theme-text-secondary)]">
          ساختار تب‌های مربوط به بخش {{ modal?.name || '' }} برای زبان {{ translation?.name || '' }}
        </p>
      </div>
      <div class="flex flex-wrap items-center gap-3">
        <Badge v-if="translation" :variant="translation.status ? 'success' : 'warning'">
          {{ translation.status ? 'فعال' : 'غیرفعال' }}
        </Badge>
        <Button variant="glass" rounded="full" @click="navigateBack">
          بازگشت به بخش‌ها
        </Button>
      </div>
    </header>

    <section
      class="rounded-2xl border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)] p-6 backdrop-blur-md shadow-[var(--theme-elevation-glass)]"
    >
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
          <h2 class="text-xl font-semibold text-[var(--theme-text-primary)]">ایجاد تب جدید</h2>
          <p class="text-sm text-[var(--theme-text-secondary)]">
            تب‌ها بر اساس نام به تمام زبان‌ها تعمیم داده می‌شوند.
          </p>
        </div>
        <Button variant="primary" rounded="full" @click="showCreateModal = true">
          ایجاد تب
        </Button>
      </div>
    </section>

    <LoadingState v-if="loading" />

    <ErrorState
      v-else-if="error"
      :message="error"
      variant="error"
    />

    <section v-else class="space-y-4">
      <Table
        :columns="columns"
        :data="tabs"
        :pagination="pagination"
        empty-state-message="تب‌ای برای این بخش ثبت نشده است."
      >
        <template #cell-name="{ row }">
          <div class="flex flex-col gap-1">
            <span class="font-semibold text-[var(--theme-text-primary)]">
              {{ row.name }}
            </span>
            <span class="text-xs text-[var(--theme-text-muted)]">
              شناسه: {{ row.id }}
            </span>
          </div>
        </template>

        <template #cell-progress="{ row }">
          <div class="flex flex-col gap-1">
            <div class="h-2 w-full rounded-full bg-white/10">
              <div
                class="h-2 rounded-full bg-gradient-to-r from-primary-500 to-secondary-400 transition-all duration-500"
                :style="{ width: `${row.progress}%` }"
              />
            </div>
            <span class="text-xs text-[var(--theme-text-secondary)]">
              {{ row.translated_fields_count }} / {{ row.fields_count }} ({{ row.progress }}%)
            </span>
          </div>
        </template>

        <template #cell-actions="{ row }">
          <div class="flex flex-wrap items-center justify-end gap-2">
            <Button
              size="sm"
              variant="secondary"
              rounded="full"
              @click="() => navigateToFields(row)"
            >
              مدیریت عبارات
            </Button>
            <Button
              size="sm"
              variant="glass"
              rounded="full"
              class="!p-2 !gap-0 min-w-[2.25rem]"
              title="ویرایش نام"
              aria-label="ویرایش نام"
              @click="() => openEditModal(row)"
            >
              <template #icon-left>
                <TableActionIcon name="edit" />
              </template>
            </Button>
            <Button
              size="sm"
              variant="danger"
              rounded="full"
              class="!p-2 !gap-0 min-w-[2.25rem]"
              title="حذف"
              aria-label="حذف"
              @click="() => handleDelete(row)"
            >
              <template #icon-left>
                <TableActionIcon name="delete" />
              </template>
            </Button>
          </div>
        </template>
      </Table>

      <Pagination
        v-if="pagination?.total"
        :pagination="pagination"
        @page-change="goToPage"
      />
    </section>

    <Modal
      v-model="showCreateModal"
      title="ایجاد تب جدید"
      size="md"
      @close="resetCreateForm"
    >
      <div class="space-y-4" dir="rtl">
        <Input
          v-model="createForm.name"
          label="نام تب"
          placeholder="مثال: general-info"
          :error="createErrors.name"
          required
        />
      </div>
      <template #footer>
        <div class="flex items-center justify-end gap-3">
          <Button variant="glass" rounded="full" @click="showCreateModal = false">
            انصراف
          </Button>
          <Button
            variant="primary"
            rounded="full"
            :loading="creating"
            :disabled="creating"
            @click="handleCreate"
          >
            ذخیره
          </Button>
        </div>
      </template>
    </Modal>

    <Modal
      v-model="showEditModal"
      title="ویرایش تب"
      size="md"
      @close="resetEditForm"
    >
      <div class="space-y-4" dir="rtl">
        <Input
          v-model="editForm.name"
          label="نام جدید تب"
          :error="editErrors.name"
          required
        />
      </div>
      <template #footer>
        <div class="flex items-center justify-end gap-3">
          <Button variant="glass" rounded="full" @click="showEditModal = false">
            انصراف
          </Button>
          <Button
            variant="primary"
            rounded="full"
            class="!p-2 !gap-0 min-w-[2.25rem]"
            title="بروزرسانی"
            aria-label="بروزرسانی"
            :loading="updating"
            :disabled="updating"
            @click="handleUpdate"
          >
            <template #icon-left>
              <TableActionIcon name="edit" />
            </template>
          </Button>
        </div>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Table from '../../components/ui/Table.vue'
import Button from '../../components/ui/Button.vue'
import Badge from '../../components/ui/Badge.vue'
import Pagination from '../../components/ui/Pagination.vue'
import Modal from '../../components/ui/Modal.vue'
import Input from '../../components/ui/Input.vue'
import LoadingState from '../../components/ui/LoadingState.vue'
import ErrorState from '../../components/ui/ErrorState.vue'
import { translationApi } from '../../api/translations'
import { useToast } from '../../composables/useToast'
import { confirm } from '../../utils/notifications'
import TableActionIcon from '../../components/icons/TableActionIcon.vue'

const { showToast } = useToast()

const route = useRoute()
const router = useRouter()
const setTitle = (title) => {
  document.title = title ? `${title} - متارنگ` : 'متارنگ'
}

const translationId = Number(route.params.translationId)
const modalId = Number(route.params.modalId)

const loading = ref(false)
const error = ref('')
const translation = ref(null)
const modal = ref(null)
const tabs = ref([])
const pagination = ref(null)
const page = ref(1)

const showCreateModal = ref(false)
const showEditModal = ref(false)
const creating = ref(false)
const updating = ref(false)
const activeTabId = ref(null)

const createForm = reactive({
  name: ''
})
const createErrors = reactive({
  name: ''
})

const editForm = reactive({
  name: ''
})
const editErrors = reactive({
  name: ''
})

const columns = [
  { key: 'name', label: 'نام تب' },
  { key: 'fields_count', label: 'کل عبارات' },
  { key: 'translated_fields_count', label: 'عبارات ترجمه شده' },
  { key: 'progress', label: 'پیشرفت', cellClass: 'min-w-[200px]' },
  { key: 'actions', label: 'اقدامات', cellClass: 'text-left whitespace-nowrap' }
]

const fetchMeta = async () => {
  try {
    translation.value = await translationApi.getTranslation(translationId)
    modal.value = await translationApi.getModal(translationId, modalId)
    setTitle(`تب‌ها - ${modal.value.name} / ${translation.value.name}`)
  } catch (err) {
    error.value = err?.response?.data?.message || 'دریافت اطلاعات پایه امکان‌پذیر نبود.'
  }
}

const fetchTabs = async (requestedPage = 1) => {
  loading.value = true
  error.value = ''
  try {
    page.value = requestedPage
    const { tabs: items, pagination: meta } = await translationApi.getTabs(translationId, modalId, {
      page: requestedPage
    })
    tabs.value = items
    pagination.value = meta
  } catch (err) {
    error.value = err?.response?.data?.message || 'خطا در دریافت تب‌ها.'
  } finally {
    loading.value = false
  }
}

const goToPage = (nextPage) => {
  fetchTabs(nextPage)
}

const resetCreateForm = () => {
  createForm.name = ''
  createErrors.name = ''
}

const resetEditForm = () => {
  editForm.name = ''
  editErrors.name = ''
  activeTabId.value = null
}

const handleCreate = async () => {
  if (!createForm.name?.trim()) {
    createErrors.name = 'نام تب الزامی است.'
    return
  }
  createErrors.name = ''
  creating.value = true
  try {
    await translationApi.createTab(translationId, modalId, {
      name: createForm.name.trim()
    })
    showToast('تب جدید برای تمامی زبان‌ها ثبت شد.', 'success')
    showCreateModal.value = false
    resetCreateForm()
    await fetchTabs(page.value)
  } catch (err) {
    const message = err?.response?.data?.errors?.name?.[0] || err?.response?.data?.message || 'امکان ایجاد تب وجود ندارد.'
    createErrors.name = message
  } finally {
    creating.value = false
  }
}

const openEditModal = (tab) => {
  activeTabId.value = tab.id
  editForm.name = tab.name
  editErrors.name = ''
  showEditModal.value = true
}

const handleUpdate = async () => {
  if (!editForm.name?.trim()) {
    editErrors.name = 'نام تب الزامی است.'
    return
  }
  editErrors.name = ''
  updating.value = true
  try {
    const response = await translationApi.updateTab(translationId, modalId, activeTabId.value, {
      name: editForm.name.trim()
    })
    const updatedTab = response.data.tab
    tabs.value = tabs.value.map((item) =>
      item.id === activeTabId.value ? updatedTab : item
    )
    showToast('نام تب در تمامی زبان‌ها بروزرسانی گردید.', 'success')
    showEditModal.value = false
    resetEditForm()
  } catch (err) {
    const message = err?.response?.data?.errors?.name?.[0] || err?.response?.data?.message || 'ویرایش تب امکان‌پذیر نبود.'
    editErrors.name = message
  } finally {
    updating.value = false
  }
}

const handleDelete = async (tab) => {
  const result = await confirm(`آیا از حذف تب ${tab.name} مطمئن هستید؟`, 'حذف تب', {
    confirmText: 'بله، حذف شود',
    cancelText: 'انصراف'
  })
  if (!result.isConfirmed) return

  try {
    await translationApi.deleteTab(translationId, modalId, tab.id)
    showToast('تب و تمامی نگاشت‌های زبان حذف گردید.', 'success')
    await fetchTabs(page.value)
  } catch (err) {
    showToast(err?.response?.data?.message || 'حذف تب امکان‌پذیر نبود.', 'error')
  }
}

const navigateToFields = (tab) => {
  router.push({
    name: 'translations-fields',
    params: {
      translationId,
      modalId,
      tabId: tab.id
    },
    query: {
      tabName: tab.name,
      modalName: modal.value?.name,
      translationName: translation.value?.name
    }
  })
}

const navigateBack = () => {
  router.push({
    name: 'translations-modals',
    params: { translationId }
  })
}

onMounted(async () => {
  await fetchMeta()
  await fetchTabs()
})
</script>


