<template>
  <div class="p-6 space-y-6" dir="rtl">
    <Breadcrumb :items="breadcrumbItems" />

    <header class="space-y-2">
      <h1 class="text-3xl font-bold text-[var(--theme-text-primary)]">مدیریت ترجمه‌ها</h1>
      <p class="text-[var(--theme-text-secondary)]">
        افزودن زبان‌های جدید، مدیریت وضعیت و صادرات فایل‌های ترجمه در محیط متاورس
      </p>
    </header>

    <section
      class="space-y-4 rounded-2xl border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)] p-6 backdrop-blur-md"
    >
      <div class="flex flex-col gap-4 lg:flex-row lg:items-end">
        <Select2
          v-model="selectedLanguageCode"
          label="انتخاب زبان"
          placeholder="یک زبان را انتخاب کنید"
          :options="languageOptions"
          :disabled="languagesLoading"
          wrapper-class="lg:flex-1"
        />
        <Button
          variant="primary"
          rounded="full"
          class="w-full lg:w-auto"
          :loading="creating"
          :disabled="!selectedLanguageCode || creating"
          @click="handleCreateTranslation"
        >
          ثبت
        </Button>
      </div>
      <Alert
        v-if="languagesError"
        variant="warning"
        class="border border-yellow-500/40 bg-yellow-500/10 text-yellow-300"
      >
        {{ languagesError }}
      </Alert>
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
        :columns="tableColumns"
        :data="translations"
        :pagination="pagination"
        empty-state-message="هنوز هیچ ترجمه‌ای اضافه نشده است."
      >
        <template #cell-flag="{ row }">
          <div class="flex items-center gap-3">
            <img
              :src="row.icon || getFlagSrc(row)"
              :alt="row.code"
              class="h-8 w-8 rounded-full border border-white/20 shadow-[0_0_10px_rgba(124,58,237,0.35)]"
            />
            <div class="flex flex-col">
              <span class="font-semibold text-[var(--theme-text-primary)]">
                {{ row.name }}
              </span>
              <span class="text-xs text-[var(--theme-text-secondary)]">
                {{ row.native_name }}
              </span>
            </div>
          </div>
        </template>

        <template #cell-status="{ row }">
          <Badge :variant="translationStatus(row) ? 'success' : 'warning'">
            {{ translationStatus(row) ? 'فعال' : 'غیرفعال' }}
          </Badge>
        </template>

        <template #cell-modals_count="{ row }">
          <span class="text-[var(--theme-text-secondary)]">
            {{ row.modals_count != null ? row.modals_count : '—' }}
          </span>
        </template>

        <template #cell-actions="{ row }">
          <div class="flex flex-wrap items-center justify-end gap-2">
            <Checkbox
              :model-value="translationStatus(row)"
              aria-label="تغییر وضعیت"
              class="rounded-full border-[var(--theme-border)] bg-[var(--theme-bg-glass)] px-3 py-2"
              @update:model-value="() => handleToggleStatus(row)"
            >
              <template #label>
                <span class="text-xs text-[var(--theme-text-secondary)]">فعال‌سازی</span>
              </template>
            </Checkbox>
            <Button
              size="sm"
              variant="secondary"
              rounded="full"
              @click="navigateToModals(row)"
            >
              مدیریت بخش‌ها
            </Button>
            <Button
              size="sm"
              variant="glass"
              rounded="full"
              @click="() => handleExport(row)"
            >
              خروجی JSON
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
        :disabled="loading"
        @page-change="goToPage"
      />
    </section>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { translationApi } from '../../api/translations'
import Table from '../../components/ui/Table.vue'
import Button from '../../components/ui/Button.vue'
import Badge from '../../components/ui/Badge.vue'
import Pagination from '../../components/ui/Pagination.vue'
import Select2 from '../../components/ui/Select2.vue'
import Checkbox from '../../components/ui/Checkbox.vue'
import Alert from '../../components/ui/Alert.vue'
import LoadingState from '../../components/ui/LoadingState.vue'
import ErrorState from '../../components/ui/ErrorState.vue'
import Breadcrumb from '../../components/ui/Breadcrumb.vue'
import { useToast } from '../../composables/useToast'
import { confirm } from '../../utils/notifications'
import TableActionIcon from '../../components/icons/TableActionIcon.vue'

const { showToast } = useToast()


const setTitle = (title) => {
  document.title = title ? `${title} - متارنگ` : 'متارنگ'
}
setTitle('مدیریت ترجمه‌ها')

const router = useRouter()

const loading = ref(false)
const creating = ref(false)
const error = ref('')
const translations = ref([])
const pagination = ref(null)
const page = ref(1)

const languagesLoading = ref(false)
const languagesError = ref('')
const languages = ref([])
const selectedLanguageCode = ref('')

const breadcrumbItems = [
  { label: 'داشبورد', to: { name: 'dashboard' } },
  { label: 'مدیریت ترجمه‌ها', active: true }
]

const tableColumns = [
  { key: 'flag', label: 'زبان', headerClass: 'text-right', cellClass: 'text-right' },
  { key: 'code', label: 'کد', cellClass: 'font-mono text-sm text-[var(--theme-text-secondary)]' },
  { key: 'direction', label: 'جهت' },
  { key: 'version', label: 'نسخه' },
  { key: 'modals_count', label: 'تعداد بخش‌ها' },
  { key: 'status', label: 'وضعیت' },
  { key: 'actions', label: 'اقدامات', cellClass: 'text-left whitespace-nowrap' }
]

const languageOptions = computed(() =>
  languages.value.map((language) => ({
    label: `${language.name} (${language.nativeName})`,
    value: language.code
  }))
)

const fetchLanguages = async () => {
  languagesLoading.value = true
  languagesError.value = ''
  try {
    languages.value = await translationApi.getLanguages()
  } catch (err) {
    languagesError.value = err?.response?.data?.message || 'خطا در دریافت لیست زبان‌ها'
  } finally {
    languagesLoading.value = false
  }
}

/** API sends boolean `status`; missing status (legacy payloads) defaults to inactive-looking false only if numeric 0. */
const translationStatus = (row) => {
  if (typeof row.status === 'boolean') return row.status
  if (row.status === 1 || row.status === '1') return true
  if (row.status === 0 || row.status === '0') return false
  return true
}

const normalizeTranslationRow = (row) => ({
  ...row,
  status: translationStatus(row)
})

const fetchTranslations = async (requestedPage = page.value) => {
  loading.value = true
  error.value = ''
  try {
    page.value = requestedPage
    const payload = await translationApi.getTranslations({
      page: requestedPage
    })
    // Index: `{ data: [ ... ] }` → Axios `response.data.data` is an array (see translationApi).
    if (Array.isArray(payload)) {
      translations.value = payload.map(normalizeTranslationRow)
      pagination.value = null
    } else {
      translations.value = (payload?.translations ?? []).map(normalizeTranslationRow)
      pagination.value = payload?.pagination ?? null
    }
  } catch (err) {
    error.value = err?.response?.data?.message || 'خطا در دریافت ترجمه‌ها'
  } finally {
    loading.value = false
  }
}

const goToPage = (nextPage) => {
  fetchTranslations(nextPage)
}

const submitCreateTranslation = async () => {
  creating.value = true
  try {
    await translationApi.createTranslation(
      { code: selectedLanguageCode.value }
    )
    showToast('ساختار ترجمه بر اساس زبان انتخابی ایجاد شد.', 'success')
    selectedLanguageCode.value = ''
    await fetchTranslations(1)
  } catch (err) {
    const messages = err?.response?.data?.errors?.code
    showToast(Array.isArray(messages) ? messages[0] : (err?.response?.data?.message || 'امکان افزودن ترجمه وجود ندارد.'), 'error')
  } finally {
    creating.value = false
  }
}

const handleCreateTranslation = async () => {
  if (!selectedLanguageCode.value) return

  await submitCreateTranslation()
}

const handleDelete = async (row) => {
  const result = await confirm(
    `آیا از حذف ترجمه ${row.name} مطمئن هستید؟`,
    'حذف ترجمه',
    { confirmText: 'بله، حذف شود', cancelText: 'انصراف' }
  )
  if (!result.isConfirmed) return

  try {
        await translationApi.deleteTranslation(row.id)
        showToast('ترجمه انتخابی حذف شد.', 'success')
        await fetchTranslations(page.value)
      } catch (err) {
        showToast(err?.response?.data?.message || 'حذف ترجمه امکان‌پذیر نبود.', 'error')
      }
}

const handleToggleStatus = async () => {
  try {
    const response = await translationApi.toggleTranslationStatus(translation.id)
    const updated = response.data.translation
    translations.value = translations.value.map((item) =>
      item.id === updated.id ? normalizeTranslationRow(updated) : item
    )
    showToast(`ترجمه ${updated.status ? 'فعال' : 'غیرفعال'} شد.`, 'success')
  } catch (err) {
    showToast(err?.response?.data?.message || 'امکان تغییر وضعیت وجود ندارد.', 'error')
  }
}

const handleExport = async () => {
  try {
    const result = await translationApi.exportTranslation(translation.id)

    if (result.type === 'file') {
      const url = window.URL.createObjectURL(result.blob)
      const link = document.createElement('a')
      link.href = url
      link.setAttribute('download', result.fileName)
      document.body.appendChild(link)
      link.click()
      link.remove()
      window.URL.revokeObjectURL(url)

      showToast(`فایل JSON برای ${translation.name} دانلود شد.`, 'success')
    } else if (result.type === 'message') {
      showToast(result.data?.message || 'صادرات با موفقیت انجام شد.', 'success')
    }
  } catch (err) {
    showToast(err?.response?.data?.message || 'امکان صادرات ترجمه وجود ندارد.', 'error')
  }
}

const navigateToModals = (translation) => {
  router.push({
    name: 'translations-modals',
    params: { translationId: translation.id }
  })
}

const getFlagSrc = (translation) => `/assets/images/flags/${String(translation.code || '').toUpperCase()}.svg`

onMounted(async () => {
  await Promise.all([fetchLanguages(), fetchTranslations()])
})
</script>

<style scoped>
.glass-panel {
  background: linear-gradient(135deg, rgba(15, 23, 42, 0.75), rgba(30, 41, 59, 0.9));
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 0 30px rgba(124, 58, 237, 0.2);
  border-radius: 20px;
  backdrop-filter: blur(16px);
}
</style>


