<template>
  <div class="p-6 space-y-6" dir="rtl">
    <Breadcrumb :items="breadcrumbItems" />

    <PageHeader
      title="مدیریت ترجمه‌ها"
      subtitle="افزودن زبان‌های جدید، مدیریت وضعیت و صادرات فایل‌های ترجمه در محیط متاورس"
    />

    <section
      v-if="runningAutoTranslationRuns.length"
      class="space-y-4 rounded-2xl border border-cyan-400/30 bg-cyan-400/5 p-5"
    >
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h2 class="text-base font-semibold text-[var(--theme-text-primary)]">صف‌های ترجمه خودکار در حال اجرا</h2>
          <p class="mt-1 text-sm text-[var(--theme-text-secondary)]">وضعیت صف‌ها هر ۵ ثانیه به‌روزرسانی می‌شود.</p>
        </div>
        <Badge variant="info">{{ formatPersianNumber(runningAutoTranslationRuns.length) }} صف فعال</Badge>
      </div>

      <div class="grid gap-3 xl:grid-cols-2">
        <article
          v-for="run in runningAutoTranslationRuns"
          :key="run.id"
          class="space-y-3 rounded-xl border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)] p-4"
        >
          <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
              <p class="font-medium text-[var(--theme-text-primary)]">{{ autoTranslationLanguageName(run) }}</p>
              <p class="mt-1 text-xs text-[var(--theme-text-secondary)]">{{ autoTranslationProviderName(run.provider) }}</p>
            </div>
            <Badge :variant="run.status === 'publishing' ? 'info' : 'warning'">
              {{ autoTranslationStatusName(run.status) }}
            </Badge>
          </div>

          <div class="space-y-1">
            <div class="flex items-center justify-between text-xs text-[var(--theme-text-secondary)]">
              <span>{{ formatPersianNumber(run.processed) }} از {{ formatPersianNumber(run.total) }} عبارت</span>
              <span>{{ formatPersianNumber(autoTranslationProgress(run)) }}٪</span>
            </div>
            <div class="h-2 overflow-hidden rounded-full bg-[var(--theme-bg-glass)]">
              <div
                class="h-full rounded-full bg-cyan-400 transition-[width] duration-300"
                :style="{ width: `${autoTranslationProgress(run)}%` }"
              />
            </div>
          </div>

          <p v-if="run.error" class="text-xs text-amber-300">{{ run.error }}</p>

          <div class="flex justify-end">
            <Button
              size="sm"
              variant="danger"
              rounded="full"
              :loading="stoppingRunId === run.id"
              :disabled="stoppingRunId !== null"
              @click="stopAutoTranslationRun(run)"
            >
              توقف صف
            </Button>
          </div>
        </article>
      </div>
    </section>

    <section
      class="space-y-4 rounded-2xl border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)] p-6 backdrop-blur-md"
    >
      <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:flex-1">
          <Select2
            v-model="selectedLanguageCode"
            label="انتخاب زبان"
            placeholder="یک زبان را انتخاب کنید"
            :options="languageOptions"
            :disabled="languagesLoading || creating || importing"
            wrapper-class="lg:flex-1"
          />
          <Button
            variant="primary"
            rounded="full"
            class="w-full lg:w-auto"
            :loading="creating"
            :disabled="!selectedLanguageCode || creating || importing"
            @click="handleCreateTranslation"
          >
            ثبت
          </Button>
        </div>

        <Button
          variant="secondary"
          rounded="full"
          class="w-full lg:w-auto"
          :disabled="creating || importing || languagesLoading"
          @click="openImportModal"
        >
          ورود ترجمه
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
              @click="handleAutoTranslate(row)"
            >
              ترجمه خودکار
            </Button>
            <Button
              size="sm"
              variant="glass"
              rounded="full"
              :loading="exportingId === row.id"
              :disabled="exportingId !== null || importing"
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
        @page-change="onPageChange"
      />
    </section>

    <Modal
      v-model="importModalOpen"
      title="ورود ترجمه جدید"
      subtitle="انتخاب زبان و بارگذاری فایل JSON مطابق الگوی fa.json"
      size="md"
    >
      <div class="space-y-4" dir="rtl">
        <Alert variant="info">
          فایل باید مانند
          <span class="font-mono text-xs">public/lang/fa.json</span>
          یک شیء تخت از
          <span class="font-mono text-xs">unique_id</span>
          به متن باشد. جایگاه هر کلید بر اساس ساختار فارسی (مدال ← تب ← فیلد) تعیین می‌شود.
        </Alert>

        <Select2
          v-model="importLanguageCode"
          label="انتخاب زبان"
          placeholder="یک زبان را انتخاب کنید"
          :options="languageOptions"
          :disabled="languagesLoading || importing"
        />

        <FileInput
          v-model="importFile"
          label="فایل JSON"
          accept=".json,application/json"
          placeholder="انتخاب فایل ترجمه"
          helper-text="فقط فایل JSON — حداکثر ۱۰ مگابایت"
          :error="importFileError"
          :loading="importing"
          :disabled="importing"
        />

        <Alert
          v-if="importResultSummary"
          variant="success"
          class="border border-emerald-500/40 bg-emerald-500/10"
        >
          {{ importResultSummary }}
        </Alert>
      </div>

      <template #footer>
        <Button
          variant="secondary"
          rounded="full"
          :disabled="importing"
          @click="closeImportModal"
        >
          انصراف
        </Button>
        <Button
          variant="primary"
          rounded="full"
          :loading="importing"
          :disabled="!importLanguageCode || !importFile || importing"
          @click="handleImport"
        >
          شروع ورود
        </Button>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
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
import PageHeader from '../../components/ui/PageHeader.vue'
import Modal from '../../components/ui/Modal.vue'
import FileInput from '../../components/ui/FileInput.vue'
import { useToast } from '../../composables/useToast'
import { confirm } from '../../utils/notifications'
import { useAutoTranslationDialog } from '../../composables/useAutoTranslationDialog'
import TableActionIcon from '../../components/icons/TableActionIcon.vue'
import { usePaginatedList } from '../../composables/usePaginatedList'
import { formatPersianNumber } from '../../utils/numberFormatter'

const { showToast } = useToast()


const setTitle = (title) => {
  document.title = title ? `${title} - متارنگ` : 'متارنگ'
}
setTitle('مدیریت ترجمه‌ها')

const router = useRouter()

const {
  loading,
  error,
  pagination,
  execute,
  goToPage,
  buildParams,
  resetToFirstPage
} = usePaginatedList()

const creating = ref(false)
const exportingId = ref(null)
const importing = ref(false)
const translations = ref([])
const activeAutoTranslationRuns = ref([])
const stoppingRunId = ref(null)
let activeRunsInterval

const languagesLoading = ref(false)
const languagesError = ref('')
const languages = ref([])
const selectedLanguageCode = ref('')

const importModalOpen = ref(false)
const importLanguageCode = ref('')
const importFile = ref(null)
const importFileError = ref('')
const importResultSummary = ref('')

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

const runningAutoTranslationRuns = computed(() => activeAutoTranslationRuns.value.filter((run) =>
  ['queued', 'translating', 'publishing'].includes(run.status)
))

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

const clearTranslations = () => {
  translations.value = []
  pagination.value = null
}

const fetchTranslations = () => execute(async () => {
  const payload = await translationApi.getTranslations(buildParams())
  // Index: `{ data: [ ... ] }` → Axios `response.data.data` is an array (see translationApi).
  if (Array.isArray(payload)) {
    translations.value = payload.map(normalizeTranslationRow)
    pagination.value = null
  } else {
    translations.value = (payload?.translations ?? []).map(normalizeTranslationRow)
    pagination.value = payload?.pagination ?? null
  }
}, {
  onClear: clearTranslations,
  logLabel: 'Translations fetch error:',
  fallbackMessage: 'خطا در دریافت ترجمه‌ها'
})

/**
 * Read active automatic translation runs without interrupting the page's primary translation-list request.
 *
 * @returns {Promise<void>} Updates the in-memory active queue list, or clears it when the status request fails.
 */
const fetchActiveAutoTranslationRuns = async () => {
  try {
    activeAutoTranslationRuns.value = await translationApi.getActiveAutoTranslationRuns()
  } catch (err) {
    activeAutoTranslationRuns.value = []
    console.error('Active auto-translation runs fetch error:', err)
  }
}

/**
 * Resolve a run's language name from the currently loaded translation rows.
 *
 * @param {AutoTranslationRun} run Active automatic translation run.
 * @returns {string} Localized language name, or a stable fallback when its row is not on the current page.
 */
const autoTranslationLanguageName = (run) => {
  const translation = translations.value.find((item) => item.id === run.translation_id)
  return translation?.name || `زبان #${formatPersianNumber(run.translation_id)}`
}

/**
 * Format the persisted provider identifier for the running-queue panel.
 *
 * @param {'openai'|'cloudflare'} provider Translation provider identifier.
 * @returns {string} Provider name suitable for the Persian admin UI.
 */
const autoTranslationProviderName = (provider) => provider === 'cloudflare' ? 'Cloudflare Workers AI' : 'OpenAI'

/**
 * Translate a run status identifier into its Persian display label.
 *
 * @param {'queued'|'translating'|'publishing'} status Running queue status.
 * @returns {string} Persian status label.
 */
const autoTranslationStatusName = (status) => ({
  queued: 'در صف',
  translating: 'در حال ترجمه',
  publishing: 'در حال انتشار'
}[status] || status)

/**
 * Calculate a bounded whole-number progress percentage for a run.
 *
 * @param {AutoTranslationRun} run Active automatic translation run.
 * @returns {number} Progress percentage from zero through one hundred.
 */
const autoTranslationProgress = (run) => {
  if (!run.total) return 0
  return Math.min(100, Math.round((run.processed / run.total) * 100))
}

/**
 * Confirm and stop an active run through the phone-verification-protected API.
 *
 * @param {AutoTranslationRun} run Active automatic translation run to stop.
 * @returns {Promise<void>} Removes the cancelled run from the live queue panel after a successful response.
 */
const stopAutoTranslationRun = async (run) => {
  const result = await confirm(
    'آیا از توقف این صف ترجمه خودکار مطمئن هستید؟ ترجمه‌های ذخیره‌شده تا این لحظه حفظ می‌شوند.',
    'توقف صف ترجمه',
    { confirmText: 'توقف صف', cancelText: 'انصراف', confirmButtonColor: '#dc2626' }
  )
  if (!result.isConfirmed) return

  stoppingRunId.value = run.id
  try {
    await translationApi.cancelAutoTranslationRun(run.translation_id, run.id)
    activeAutoTranslationRuns.value = activeAutoTranslationRuns.value.filter((item) => item.id !== run.id)
    showToast('صف ترجمه خودکار متوقف شد.', 'success')
  } catch (err) {
    showToast(err?.response?.data?.message || 'توقف صف ترجمه امکان‌پذیر نبود.', 'error')
  } finally {
    stoppingRunId.value = null
  }
}

const onPageChange = (page) => goToPage(page, fetchTranslations)

const handleAutoTranslate = useAutoTranslationDialog(fetchTranslations)

const submitCreateTranslation = async () => {
  creating.value = true
  try {
    await translationApi.createTranslation(
      { code: selectedLanguageCode.value }
    )
    showToast('ساختار ترجمه بر اساس زبان انتخابی ایجاد شد.', 'success')
    selectedLanguageCode.value = ''
    resetToFirstPage()
    await fetchTranslations()
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
        await fetchTranslations()
      } catch (err) {
        showToast(err?.response?.data?.message || 'حذف ترجمه امکان‌پذیر نبود.', 'error')
      }
}

const handleToggleStatus = async (row) => {
  try {
    const response = await translationApi.toggleTranslationStatus(row.id)
    const updated = response.data.translation
    translations.value = translations.value.map((item) =>
      item.id === updated.id ? normalizeTranslationRow(updated) : item
    )
    showToast(`ترجمه ${updated.status ? 'فعال' : 'غیرفعال'} شد.`, 'success')
  } catch (err) {
    showToast(err?.response?.data?.message || 'امکان تغییر وضعیت وجود ندارد.', 'error')
  }
}

const handleExport = async (row) => {
  exportingId.value = row.id
  try {
    const result = await translationApi.exportTranslation(row.id)

    if (result.type === 'file') {
      const url = window.URL.createObjectURL(result.blob)
      const link = document.createElement('a')
      link.href = url
      link.setAttribute('download', result.fileName)
      document.body.appendChild(link)
      link.click()
      link.remove()
      window.URL.revokeObjectURL(url)

      showToast(`فایل JSON برای ${row.name} دانلود شد.`, 'success')
    } else if (result.type === 'message') {
      showToast(result.data?.message || 'صادرات با موفقیت انجام شد.', 'success')
    }
  } catch (err) {
    showToast(err?.response?.data?.message || 'امکان صادرات ترجمه وجود ندارد.', 'error')
  } finally {
    exportingId.value = null
  }
}

const openImportModal = () => {
  importLanguageCode.value = ''
  importFile.value = null
  importFileError.value = ''
  importResultSummary.value = ''
  importModalOpen.value = true
}

const closeImportModal = () => {
  if (importing.value) return
  importModalOpen.value = false
  importLanguageCode.value = ''
  importFile.value = null
  importFileError.value = ''
  importResultSummary.value = ''
}

const handleImport = async () => {
  if (!importLanguageCode.value || !importFile.value) return

  importing.value = true
  importFileError.value = ''
  importResultSummary.value = ''

  try {
    const response = await translationApi.importTranslation(importFile.value, {
      code: importLanguageCode.value
    })
    const data = response?.data ?? {}
    const updated = data.updated ?? 0
    const created = data.created ?? 0
    const skipped = data.skipped ?? 0
    const unknownCount = Array.isArray(data.unknown_ids) ? data.unknown_ids.length : 0

    importResultSummary.value = `به‌روزرسانی: ${updated} | ایجاد: ${created} | رد شده: ${skipped}${unknownCount ? ` (ناشناس: ${unknownCount})` : ''}`

    showToast(response?.message || 'ترجمه جدید ایجاد و وارد شد.', 'success')
    importLanguageCode.value = ''
    importFile.value = null
    resetToFirstPage()
    await fetchTranslations()
  } catch (err) {
    const errors = err?.response?.data?.errors ?? {}
    const messages = errors.file || errors.code
    importFileError.value = Array.isArray(messages)
      ? messages[0]
      : (err?.response?.data?.message || 'امکان ورود فایل ترجمه وجود ندارد.')
    showToast(importFileError.value, 'error')
  } finally {
    importing.value = false
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
  await Promise.all([fetchLanguages(), fetchTranslations(), fetchActiveAutoTranslationRuns()])
  activeRunsInterval = window.setInterval(fetchActiveAutoTranslationRuns, 5000)
})

onBeforeUnmount(() => {
  window.clearInterval(activeRunsInterval)
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
