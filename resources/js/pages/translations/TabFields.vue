<template>
  <div class="p-6 space-y-6" dir="rtl">
    <header class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
      <div>
        <h1 class="text-3xl font-bold text-[var(--theme-text-primary)]">
          مدیریت عبارات ترجمه
        </h1>
        <p class="text-[var(--theme-text-secondary)]">
          مدیریت عبارت‌های تب {{ tab?.name || '' }} در بخش {{ modal?.name || '' }} برای زبان {{ translation?.name || '' }}
        </p>
      </div>
      <div class="flex flex-wrap items-center gap-3">
        <Badge v-if="translation" :variant="translation.status ? 'success' : 'warning'">
          {{ translation.status ? 'فعال' : 'غیرفعال' }}
        </Badge>
        <Button variant="glass" rounded="full" @click="navigateBack">
          بازگشت به تب‌ها
        </Button>
      </div>
    </header>

    <section
      class="rounded-2xl border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)] p-6 backdrop-blur-md shadow-[var(--theme-elevation-glass)]"
    >
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
          <h2 class="text-xl font-semibold text-[var(--theme-text-primary)]">ثبت عبارت جدید</h2>
          <p class="text-sm text-[var(--theme-text-secondary)]">
            شناسه یکتا به صورت خودکار تولید شده و در تمام زبان‌ها همگام می‌شود.
          </p>
        </div>
        <Button variant="primary" rounded="full" @click="showCreateModal = true">
          ایجاد عبارت
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
        :data="fields"
        :pagination="pagination"
        empty-state-message="هیچ عبارتی ثبت نشده است."
      >
        <template #cell-unique_id="{ value }">
          <Badge variant="glass" class="font-mono text-sm">
            {{ value }}
          </Badge>
        </template>

        <template #cell-translation="{ row }">
          <div class="space-y-1">
            <p class="text-[var(--theme-text-primary)] whitespace-pre-line">
              {{ row.translation || '---' }}
            </p>
            <p v-if="!row.translation" class="text-xs text-[var(--theme-text-secondary)]">
              هنوز ترجمه‌ای برای این زبان ثبت نشده است.
            </p>
          </div>
        </template>

        <template #cell-actions="{ row }">
          <div class="flex flex-wrap items-center justify-end gap-2">
            <Button
              size="sm"
              variant="glass"
              rounded="full"
              class="!p-2 !gap-0 min-w-[2.25rem]"
              title="ویرایش"
              aria-label="ویرایش"
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
      title="ایجاد عبارت جدید"
      size="lg"
      @close="resetCreateForm"
    >
      <div class="space-y-4" dir="rtl">
        <Textarea
          v-model="createForm.value"
          label="متن ترجمه"
          placeholder="متن ترجمه را وارد کنید..."
          :error="createErrors.value"
          rows="5"
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
      title="ویرایش ترجمه عبارت"
      size="lg"
      @close="resetEditForm"
    >
      <div class="space-y-4" dir="rtl">
        <Textarea
          v-model="editForm.translation"
          label="ترجمه"
          placeholder="متن ترجمه را وارد کنید..."
          :error="editErrors.translation"
          rows="6"
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

    <PhoneVerificationModal :phone-verification="phoneVerification" />
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
import Textarea from '../../components/ui/Textarea.vue'
import LoadingState from '../../components/ui/LoadingState.vue'
import ErrorState from '../../components/ui/ErrorState.vue'
import { translationApi } from '../../api/translations'
import PhoneVerificationModal from '../../components/PhoneVerificationModal.vue'
import { useToast } from '../../composables/useToast'
import { usePhoneVerification } from '../../composables/usePhoneVerification'
import TableActionIcon from '../../components/icons/TableActionIcon.vue'

const { showToast } = useToast()
const phoneVerification = usePhoneVerification()

const route = useRoute()
const router = useRouter()
const setTitle = (title) => {
  document.title = title ? `${title} - متارنگ` : 'متارنگ'
}

const translationId = Number(route.params.translationId)
const modalId = Number(route.params.modalId)
const tabId = Number(route.params.tabId)

const loading = ref(false)
const error = ref('')
const translation = ref(null)
const modal = ref(null)
const tab = ref(null)
const fields = ref([])
const pagination = ref(null)
const page = ref(1)

const showCreateModal = ref(false)
const showEditModal = ref(false)
const creating = ref(false)
const updating = ref(false)
const activeFieldId = ref(null)

const createForm = reactive({
  value: ''
})
const createErrors = reactive({
  value: ''
})

const editForm = reactive({
  translation: ''
})
const editErrors = reactive({
  translation: ''
})

const columns = [
  { key: 'unique_id', label: 'شناسه یکتا' },
  { key: 'translation', label: 'متن ترجمه', cellClass: 'whitespace-pre-line' },
  { key: 'actions', label: 'اقدامات', cellClass: 'text-left whitespace-nowrap' }
]

const fetchMeta = async () => {
  try {
    translation.value = await translationApi.getTranslation(translationId)
    modal.value = await translationApi.getModal(translationId, modalId)
    tab.value = await translationApi.getTab(translationId, modalId, tabId)
    setTitle(`عبارات - ${tab.value.name}`)
  } catch (err) {
    error.value = err?.response?.data?.message || 'دریافت اطلاعات پایه امکان‌پذیر نبود.'
  }
}

const fetchFields = async (requestedPage = 1) => {
  loading.value = true
  error.value = ''
  try {
    page.value = requestedPage
    const { fields: items, pagination: meta } = await translationApi.getFields(translationId, modalId, tabId, {
      page: requestedPage
    })
    fields.value = items
    pagination.value = meta
  } catch (err) {
    error.value = err?.response?.data?.message || 'خطا در دریافت عبارات.'
  } finally {
    loading.value = false
  }
}

const goToPage = (nextPage) => {
  fetchFields(nextPage)
}

const resetCreateForm = () => {
  createForm.value = ''
  createErrors.value = ''
}

const resetEditForm = () => {
  editForm.translation = ''
  editErrors.translation = ''
  activeFieldId.value = null
}

const handleCreate = async () => {
  if (!createForm.value?.trim()) {
    createErrors.value = 'متن ترجمه الزامی است.'
    return
  }
  createErrors.value = ''
  creating.value = true
  try {
    await translationApi.createField(translationId, modalId, tabId, {
      value: createForm.value.trim()
    })
    showToast('عبارت جدید به ساختار تمام زبان‌ها اضافه شد.', 'success')
    showCreateModal.value = false
    resetCreateForm()
    await fetchFields(page.value)
  } catch (err) {
    const message = err?.response?.data?.errors?.value?.[0] || err?.response?.data?.message || 'ایجاد عبارت امکان‌پذیر نبود.'
    createErrors.value = message
  } finally {
    creating.value = false
  }
}

const openEditModal = (field) => {
  activeFieldId.value = field.id
  editForm.translation = field.translation || ''
  editErrors.translation = ''
  showEditModal.value = true
}

const handleUpdate = async () => {
  if (!editForm.translation?.trim()) {
    editErrors.translation = 'متن ترجمه الزامی است.'
    return
  }
  editErrors.translation = ''
  updating.value = true
  try {
    const response = await translationApi.updateField(translationId, modalId, tabId, activeFieldId.value, {
      translation: editForm.translation.trim()
    })
    const updatedField = response.data.field
    fields.value = fields.value.map((item) =>
      item.id === activeFieldId.value ? updatedField : item
    )
    showToast('عبارت مربوط به این زبان بروزرسانی گردید.', 'success')
    showEditModal.value = false
    resetEditForm()
  } catch (err) {
    const message = err?.response?.data?.errors?.translation?.[0] || err?.response?.data?.message || 'ویرایش عبارت امکان‌پذیر نبود.'
    editErrors.translation = message
  } finally {
    updating.value = false
  }
}

const handleDelete = async (field) => {
  await phoneVerification.confirmThenVerify(
    {
      message: 'آیا از حذف این عبارت مطمئن هستید؟ با این کار عبارت از تمامی زبان‌ها حذف می‌شود.',
      title: 'حذف عبارت',
      confirmText: 'بله، حذف شود',
      cancelText: 'انصراف'
    },
    async (payload) => {
      try {
        await translationApi.deleteField(translationId, modalId, tabId, field.id, payload)
        showToast('تمامی نسخه‌های این عبارت حذف گردید.', 'success')
        phoneVerification.resetVerificationState()
        await fetchFields(page.value)
      } catch (err) {
        if (await phoneVerification.handleApiVerificationError(err)) {
          return
        }
        showToast(err?.response?.data?.message || 'حذف عبارت امکان‌پذیر نبود.', 'error')
      }
    }
  )
}

const navigateBack = () => {
  router.push({
    name: 'translations-tabs',
    params: {
      translationId,
      modalId
    }
  })
}

onMounted(async () => {
  await fetchMeta()
  await fetchFields()
})
</script>


