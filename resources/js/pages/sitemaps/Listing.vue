<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import apiClient from '../../utils/api'
import { confirm, notifyError, notifySuccess } from '../../utils/notifications'
import { Badge, Breadcrumb, Button, ErrorState, FileInput, LoadingState, Modal, PageHeader, Select, Table } from '../../components/ui'
import TableActionIcon from '../../components/icons/TableActionIcon.vue'

const router = useRouter()
const sitemaps = ref([])
const language = ref('')
const type = ref('')
const loading = ref(true)
const error = ref('')
const file = ref(null)
const uploadLanguage = ref('fa')
const activeAction = ref('')
const showUploadModal = ref(false)

const breadcrumbItems = [{ label: 'مدیریت محتوا' }, { label: 'نقشه‌های سایت' }]
const languageLabels = { ar: 'عربی', en: 'انگلیسی', fa: 'فارسی' }
const languageOptions = computed(() => [...new Set(['fa', 'en', ...sitemaps.value.map((item) => item.language)])].filter(Boolean).sort().map((code) => ({ value: code, label: languageLabels[code] || code.toUpperCase() })))
const languageLabel = (code) => languageLabels[code] || code.toUpperCase()
const publicSitemapBase = (window.__APP_CONFIG__?.frontEndUrl || '').replace(/\/$/, '') + '/sitemaps'
const publicSitemapUrl = (item) => `${publicSitemapBase}/${encodeURIComponent(item.language)}/${encodeURIComponent(item.filename)}`
const filterLanguageOptions = computed(() => [{ value: '', label: 'همه زبان‌ها' }, ...languageOptions.value])
const typeOptions = [{ value: '', label: 'همه انواع' }, { value: 'dynamic', label: 'پویا' }, { value: 'static', label: 'ایستا' }]
const columns = [
  { key: 'filename', label: 'نام فایل', cellClass: 'font-medium', textSecondary: false },
  { key: 'public_url', label: 'لینک نقشه سایت' },
  { key: 'language', label: 'زبان' },
  { key: 'type', label: 'نوع' },
  { key: 'url_count', label: 'تعداد لینک', textSecondary: true },
  { key: 'status', label: 'وضعیت' },
  { key: 'actions', label: 'عملیات' }
]

const actionKey = (action, item = null) => item ? `${action}:${item.language}/${item.filename}` : action
const isActionLoading = (action, item = null) => activeAction.value === actionKey(action, item)
const getErrorMessage = (requestError, fallback = 'ارتباط با سرویس نقشه‌های سایت برقرار نشد.') => requestError?.response?.data?.message || fallback
const statusVariant = (status) => ({ available: 'success', succeeded: 'success', running: 'warning', failed: 'danger' }[status] || 'glass')
const statusLabel = (status) => ({ available: 'در دسترس', succeeded: 'موفق', running: 'در حال تولید', failed: 'ناموفق' }[status] || status || '—')

const load = async () => {
  loading.value = true
  error.value = ''
  try {
    const response = await apiClient.get('/sitemaps', { params: { language: language.value || undefined, type: type.value || undefined } })
    sitemaps.value = response.data?.data?.sitemaps || []
  } catch (requestError) {
    error.value = getErrorMessage(requestError)
  } finally {
    loading.value = false
  }
}

const generate = async (item = null) => {
  if (!item) {
    const result = await confirm(
      'تمام نقشه‌های سایت پویا برای همه زبان‌های فعال دوباره تولید می‌شوند. این فرایند ممکن است چند لحظه طول بکشد.',
      'تایید بازسازی همه نقشه‌های سایت',
      { confirmText: 'بله، بازسازی شود', cancelText: 'انصراف' }
    )
    if (!result.isConfirmed) return
  }

  const key = actionKey('generate', item)
  activeAction.value = key
  try {
    await apiClient.post('/sitemaps/generate', { target: item?.filename, language: item?.language })
    await load()
    notifySuccess('تولید نقشه‌های سایت آغاز شد.')
  } catch (requestError) {
    notifyError(getErrorMessage(requestError, 'بازسازی نقشه‌های سایت انجام نشد.'))
  } finally {
    if (activeAction.value === key) activeAction.value = ''
  }
}

const upload = async () => {
  if (!file.value) return
  const key = actionKey('upload')
  activeAction.value = key
  try {
    const data = new FormData()
    data.append('language', uploadLanguage.value)
    data.append('file', file.value)
    await apiClient.post('/sitemaps/upload', data, { headers: { 'Content-Type': 'multipart/form-data' } })
    showUploadModal.value = false
    file.value = null
    await load()
    notifySuccess('فایل نقشه سایت با موفقیت بارگذاری شد.')
  } catch (requestError) {
    notifyError(getErrorMessage(requestError, 'بارگذاری فایل نقشه سایت انجام نشد.'))
  } finally {
    if (activeAction.value === key) activeAction.value = ''
  }
}

const remove = async (item) => {
  const result = await confirm(`آیا از حذف فایل «${item.filename}» در زبان ${languageLabel(item.language)} مطمئن هستید؟`, 'تایید حذف نقشه سایت', { confirmText: 'بله، حذف شود', cancelText: 'انصراف' })
  if (!result.isConfirmed) return
  const key = actionKey('delete', item)
  activeAction.value = key
  try {
    await apiClient.delete(`/sitemaps/${encodeURIComponent(item.language)}/${encodeURIComponent(item.filename)}`)
    await load()
    notifySuccess('فایل نقشه سایت حذف شد.')
  } catch (requestError) {
    notifyError(getErrorMessage(requestError, 'حذف نقشه سایت انجام نشد.'))
  } finally {
    if (activeAction.value === key) activeAction.value = ''
  }
}

const openUploadModal = () => { showUploadModal.value = true }
const closeUploadModal = () => { if (!activeAction.value) { showUploadModal.value = false; file.value = null } }
onMounted(load)
</script>

<template>
  <div class="space-y-6 p-6" dir="rtl">
    <Breadcrumb :items="breadcrumbItems" />
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
      <PageHeader title="مدیریت نقشه‌های سایت" subtitle="مشاهده، تولید و مدیریت نقشه‌های سایت چندزبانه" dense />
      <div class="flex flex-wrap gap-2">
        <Button variant="secondary" rounded="full" @click="router.push({ name: 'sitemap-logs' })">گزارش تولید</Button>
        <Button rounded="full" :loading="isActionLoading('generate')" :disabled="loading || !!activeAction" @click="generate()">بازسازی همه</Button>
      </div>
    </div>

    <section class="rounded-2xl border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)] p-5 shadow-lg backdrop-blur-md">
      <div class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between md:gap-10">
        <div class="grid grid-cols-1 gap-6 md:min-w-0 md:flex-1 md:grid-cols-2 md:gap-x-8">
          <Select v-model="language" label="فیلتر زبان" placeholder="" :options="filterLanguageOptions" :disabled="loading || !!activeAction" @change="load" />
          <Select v-model="type" label="فیلتر نوع فایل" placeholder="" :options="typeOptions" :disabled="loading || !!activeAction" @change="load" />
        </div>
        <div class="flex shrink-0 justify-center md:justify-start"><Button variant="secondary" rounded="full" :disabled="!!activeAction" @click="openUploadModal">بارگذاری فایل ایستا</Button></div>
      </div>
    </section>

    <ErrorState v-if="error" :message="error" variant="error" class="rounded-2xl border border-red-500/40 bg-red-500/5 p-5" />
    <LoadingState v-else-if="loading" message="در حال دریافت نقشه‌های سایت..." />

    <Table v-else :columns="columns" :data="sitemaps" empty-state-message="نقشه سایتی برای نمایش یافت نشد.">
      <template #cell-filename="{ value }"><span dir="ltr">{{ value }}</span></template>
      <template #cell-public_url="{ row }"><a :href="publicSitemapUrl(row)" target="_blank" rel="noopener noreferrer" class="text-primary-400 underline decoration-dotted underline-offset-4 hover:text-primary-300" dir="ltr">مشاهده</a></template>
      <template #cell-language="{ value }">{{ languageLabel(value) }}</template>
      <template #cell-type="{ value }">{{ value === 'dynamic' ? 'پویا' : 'ایستا' }}</template>
      <template #cell-status="{ value }"><Badge :variant="statusVariant(value)">{{ statusLabel(value) }}</Badge></template>
      <template #cell-actions="{ row }"><div class="flex flex-wrap justify-end gap-2"><Button v-if="row.type === 'dynamic'" size="xs" variant="secondary" rounded="full" :loading="isActionLoading('generate', row)" :disabled="!!activeAction" @click="generate(row)">بازسازی</Button><Button size="xs" variant="danger" rounded="full" :loading="isActionLoading('delete', row)" :disabled="!!activeAction" title="حذف" aria-label="حذف" @click="remove(row)"><template #icon-left><TableActionIcon name="delete" /></template></Button></div></template>
    </Table>

    <Modal :model-value="showUploadModal" title="بارگذاری فایل ایستای نقشه سایت" size="md" @update:model-value="closeUploadModal">
      <div class="space-y-5" dir="rtl">
        <Select v-model="uploadLanguage" label="زبان فایل" :options="languageOptions" required :disabled="!!activeAction" />
        <FileInput v-model="file" label="فایل XML" accept=".xml,text/xml,application/xml" required placeholder="فایل XML نقشه سایت را انتخاب کنید" helper-text="حداکثر حجم فایل: ۲۰ مگابایت" :loading="isActionLoading('upload')" :disabled="!!activeAction" />
      </div>
      <template #footer><div class="flex justify-end gap-3" dir="rtl"><Button variant="primary" :loading="isActionLoading('upload')" :disabled="!file || !!activeAction" @click="upload">بارگذاری</Button><Button variant="danger" :disabled="!!activeAction" @click="closeUploadModal">انصراف</Button></div></template>
    </Modal>
  </div>
</template>
