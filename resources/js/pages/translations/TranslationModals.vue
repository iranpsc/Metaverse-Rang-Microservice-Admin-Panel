<template>
  <div class="p-6 space-y-6" dir="rtl">
    <header class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
      <div>
        <h1 class="text-3xl font-bold text-[var(--theme-text-primary)]">
          بخش‌های ترجمه {{ translation?.name ? `- ${translation.name}` : '' }}
        </h1>
        <p class="text-[var(--theme-text-secondary)]">
          مدیریت ساختار بخش‌ها برای زبان انتخابی و همگام‌سازی با سایر زبان‌ها
        </p>
      </div>
      <div class="flex items-center gap-3">
        <Badge v-if="translation" :variant="translation.status ? 'success' : 'warning'">
          {{ translation.status ? 'فعال' : 'غیرفعال' }}
        </Badge>
        <Button variant="glass" rounded="full" @click="navigateBack">
          بازگشت به ترجمه‌ها
        </Button>
      </div>
    </header>

    <section
      class="rounded-2xl border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)] p-6 backdrop-blur-md shadow-[var(--theme-elevation-glass)]"
    >
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
          <h2 class="text-xl font-semibold text-[var(--theme-text-primary)]">افزودن بخش جدید</h2>
          <p class="text-sm text-[var(--theme-text-secondary)]">
            هر بخش به صورت خودکار برای تمام زبان‌های فعال ایجاد می‌شود.
          </p>
        </div>
        <Button variant="primary" rounded="full" @click="showCreateModal = true">
          ایجاد بخش
        </Button>
      </div>
    </section>

    <LoadingState v-if="loading" />

    <ErrorState
      v-else-if="error"
      :message="error"
      variant="error"
      class="rounded-2xl border border-red-500/40 bg-red-500/5 p-8"
    />

    <section v-else class="space-y-4">
      <Table
        :columns="columns"
        :data="modals"
        :pagination="pagination"
        empty-state-message="هیچ بخشی برای این زبان ثبت نشده است."
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

        <template #cell-tabs_count="{ value }">
          <Badge variant="glass">
            {{ value }}
          </Badge>
        </template>

        <template #cell-actions="{ row }">
          <div class="flex flex-wrap items-center justify-end gap-2">
            <Button
              size="sm"
              variant="secondary"
              rounded="full"
              @click="() => navigateToTabs(row)"
            >
              مدیریت تب‌ها
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
      title="ایجاد بخش جدید"
      size="md"
      @close="resetCreateForm"
    >
      <div class="space-y-4" dir="rtl">
        <Input
          v-model="createForm.name"
          label="نام بخش"
          placeholder="مثال: profile-settings"
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
            :loading="isProduction && !isVerified ? sendingVerification : creating"
            :disabled="creating"
            @click="handleCreate"
          >
            {{ createSubmitLabel }}
          </Button>
        </div>
      </template>
    </Modal>

    <Modal
      v-model="showEditModal"
      title="ویرایش نام بخش"
      size="md"
      @close="resetEditForm"
    >
      <div class="space-y-4" dir="rtl">
        <Input
          v-model="editForm.name"
          label="نام جدید بخش"
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
            :loading="isProduction && !isVerified ? sendingVerification : updating"
            :disabled="updating"
            @click="handleUpdate"
          >
            {{ editSubmitLabel }}
          </Button>
        </div>
      </template>
    </Modal>

    <PhoneVerificationModal :phone-verification="phoneVerification" />
  </div>
</template>

<script setup>
import { onMounted, reactive, ref, computed } from 'vue'
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
import PhoneVerificationModal from '../../components/PhoneVerificationModal.vue'
import { useToast } from '../../composables/useToast'
import { usePhoneVerification, applyVerificationPayload } from '../../composables/usePhoneVerification'
import TableActionIcon from '../../components/icons/TableActionIcon.vue'

const { showToast } = useToast()
const phoneVerification = usePhoneVerification()
const {
  isProduction,
  isVerified,
  sendingVerification,
  beginVerifyForSubmit,
  getSubmitPayload,
  confirmThenVerify,
  handleApiVerificationError,
  resetVerificationState
} = phoneVerification

const createSubmitLabel = computed(() =>
  isProduction.value && !isVerified.value ? 'ارسال کد تایید' : 'ثبت'
)
const editSubmitLabel = computed(() =>
  isProduction.value && !isVerified.value ? 'ارسال کد تایید' : 'بروزرسانی'
)

const route = useRoute()
const router = useRouter()

const translationId = Number(route.params.translationId)
const setTitle = (title) => {
  document.title = title ? `${title} - متارنگ` : 'متارنگ'
}
setTitle('بخش‌های ترجمه')

const loading = ref(false)
const error = ref('')
const translation = ref(null)
const modals = ref([])
const pagination = ref(null)
const page = ref(1)

const showCreateModal = ref(false)
const showEditModal = ref(false)
const creating = ref(false)
const updating = ref(false)
const activeModalId = ref(null)

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
  { key: 'name', label: 'نام بخش' },
  { key: 'tabs_count', label: 'تعداد تب‌ها' },
  { key: 'actions', label: 'اقدامات', cellClass: 'text-left whitespace-nowrap' }
]

const fetchTranslation = async () => {
  try {
    translation.value = await translationApi.getTranslation(translationId)
    setTitle(`بخش‌های ترجمه - ${translation.value.name}`)
  } catch (err) {
    error.value = err?.response?.data?.message || 'امکان دریافت اطلاعات ترجمه وجود ندارد.'
  }
}

const fetchModals = async (requestedPage = 1) => {
  loading.value = true
  error.value = ''
  try {
    page.value = requestedPage
    const { modals: items, pagination: meta } = await translationApi.getModals(translationId, {
      page: requestedPage
    })
    modals.value = items
    pagination.value = meta
  } catch (err) {
    error.value = err?.response?.data?.message || 'خطا در دریافت لیست بخش‌ها.'
  } finally {
    loading.value = false
  }
}

const goToPage = (nextPage) => {
  fetchModals(nextPage)
}

const resetCreateForm = () => {
  createForm.name = ''
  createErrors.name = ''
}

const resetEditForm = () => {
  editForm.name = ''
  editErrors.name = ''
  activeModalId.value = null
}

const submitCreate = async (verificationPayload = {}) => {
  creating.value = true
  try {
    await translationApi.createModal(
      translationId,
      applyVerificationPayload({ name: createForm.name.trim() }, verificationPayload)
    )
    showToast('بخش جدید برای تمامی زبان‌ها ثبت شد.', 'success')
    resetVerificationState()
    showCreateModal.value = false
    resetCreateForm()
    await fetchModals(page.value)
  } catch (err) {
    if (await handleApiVerificationError(err)) {
      return
    }
    const message = err?.response?.data?.errors?.name?.[0] || err?.response?.data?.message || 'ایجاد بخش امکان‌پذیر نبود.'
    createErrors.name = message
  } finally {
    creating.value = false
  }
}

const handleCreate = async () => {
  if (!createForm.name?.trim()) {
    createErrors.name = 'نام بخش الزامی است.'
    return
  }
  createErrors.name = ''

  if (isProduction.value && !isVerified.value) {
    await beginVerifyForSubmit()
    return
  }

  await submitCreate(getSubmitPayload())
}

const openEditModal = (modal) => {
  resetVerificationState()
  activeModalId.value = modal.id
  editForm.name = modal.name
  editErrors.name = ''
  showEditModal.value = true
}

const submitUpdate = async (verificationPayload = {}) => {
  updating.value = true
  try {
    const response = await translationApi.updateModal(
      translationId,
      activeModalId.value,
      applyVerificationPayload({ name: editForm.name.trim() }, verificationPayload)
    )
    const updatedModal = response.data.modal
    modals.value = modals.value.map((item) =>
      item.id === activeModalId.value ? updatedModal : item
    )
    showToast('نام بخش در تمامی زبان‌ها بروزرسانی گردید.', 'success')
    resetVerificationState()
    showEditModal.value = false
    resetEditForm()
  } catch (err) {
    if (await handleApiVerificationError(err)) {
      return
    }
    const message = err?.response?.data?.errors?.name?.[0] || err?.response?.data?.message || 'ویرایش بخش امکان‌پذیر نبود.'
    editErrors.name = message
  } finally {
    updating.value = false
  }
}

const handleUpdate = async () => {
  if (!editForm.name?.trim()) {
    editErrors.name = 'نام بخش الزامی است.'
    return
  }
  editErrors.name = ''

  if (isProduction.value && !isVerified.value) {
    await beginVerifyForSubmit()
    return
  }

  await submitUpdate(getSubmitPayload())
}

const handleDelete = async (modal) => {
  await phoneVerification.confirmThenVerify(
    {
      message: `آیا از حذف بخش ${modal.name} مطمئن هستید؟`,
      title: 'حذف بخش',
      confirmText: 'بله، حذف شود',
      cancelText: 'انصراف'
    },
    async (payload) => {
      try {
        await translationApi.deleteModal(translationId, modal.id, payload)
        showToast('تمامی نسخه‌های این بخش حذف گردید.', 'success')
        phoneVerification.resetVerificationState()
        await fetchModals(page.value)
      } catch (err) {
        if (await phoneVerification.handleApiVerificationError(err)) {
          return
        }
        showToast(err?.response?.data?.message || 'حذف بخش امکان‌پذیر نبود.', 'error')
      }
    }
  )
}

const navigateToTabs = (modal) => {
  router.push({
    name: 'translations-tabs',
    params: {
      translationId,
      modalId: modal.id
    },
    query: {
      modalName: modal.name,
      translationName: translation.value?.name
    }
  })
}

const navigateBack = () => {
  router.push({ name: 'translations-index' })
}

onMounted(async () => {
  await fetchTranslation()
  await fetchModals()
})
</script>


