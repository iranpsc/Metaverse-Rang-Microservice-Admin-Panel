<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import apiClient from '../../utils/api'
import { Badge, Breadcrumb, Button, ErrorState, LoadingState, PageHeader, Pagination, Table } from '../../components/ui'
import { normalizePagination } from '../../utils/pagination'
import { formatPersianDate } from '../../utils/dateFormatter'

const router = useRouter()
const logs = ref([])
const pagination = ref(null)
const loading = ref(true)
const error = ref('')
const triggerLabels = {
  manual: 'دستی',
  scheduled: 'زمان‌بندی‌شده',
  kyc_verified: 'تأیید احراز هویت',
  ownership_changed: 'تغییر مالکیت',
  building_changed: 'تغییر ساختمان',
  static_upload: 'بارگذاری فایل ایستا',
  static_delete: 'حذف فایل ایستا'
}
const columns = [
  { key: 'trigger', label: 'محرک' },
  { key: 'target', label: 'هدف' },
  { key: 'language', label: 'زبان' },
  { key: 'status', label: 'وضعیت' },
  { key: 'latest_success_date', label: 'تاریخ' },
  { key: 'latest_success_time', label: 'ساعت' },
  { key: 'duration_ms', label: 'مدت' }
]
const breadcrumbItems = [{ label: 'مدیریت محتوا' }, { label: 'نقشه‌های سایت', to: { name: 'sitemaps' } }, { label: 'گزارش تولید' }]
const triggerLabel = (value) => triggerLabels[value] || value || '—'
const statusLabel = (value) => ({ succeeded: 'موفق', failed: 'ناموفق', running: 'در حال اجرا' }[value] || value || '—')
const statusVariant = (value) => ({ succeeded: 'success', failed: 'danger', running: 'warning' }[value] || 'glass')
const languageLabel = (value) => ({ ar: 'عربی', en: 'انگلیسی', fa: 'فارسی' }[value] || value || 'همه زبان‌ها')
const successfulRun = (row) => row?.status === 'succeeded' && row?.finished_at ? new Date(row.finished_at) : null
const successDate = (row) => {
  const date = successfulRun(row)
  return date ? formatPersianDate(date) : '—'
}
const successTime = (row) => {
  const date = successfulRun(row)
  return date ? date.toLocaleTimeString('fa-IR', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) : '—'
}
const formatDuration = (durationMs) => {
  if (durationMs == null || Number.isNaN(Number(durationMs))) return '—'

  const totalMilliseconds = Math.max(0, Math.round(Number(durationMs)))
  if (totalMilliseconds <= 1_000) return `${totalMilliseconds} میلی‌ثانیه`

  const seconds = (totalMilliseconds / 1_000).toFixed(3).replace(/0+$/, '').replace(/\.$/, '')
  return `${seconds} ثانیه`
}

const load = async (page = 1) => {
  loading.value = true
  error.value = ''
  try {
    const response = await apiClient.get('/sitemaps/logs', { params: { page, per_page: 20 } })
    const data = response.data?.data || {}
    logs.value = data.logs || []
    pagination.value = normalizePagination(data.pagination)
  } catch (requestError) {
    error.value = requestError?.response?.data?.message || 'دریافت گزارش‌های نقشه سایت انجام نشد.'
  } finally {
    loading.value = false
  }
}

onMounted(() => load())
</script>

<template>
  <div class="space-y-6 p-6" dir="rtl">
    <Breadcrumb :items="breadcrumbItems" />
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
      <PageHeader title="گزارش تولید نقشه‌های سایت" subtitle="سوابق تولید، بارگذاری و حذف فایل‌های نقشه سایت" dense />
      <Button variant="secondary" rounded="full" @click="router.push({ name: 'sitemaps' })">بازگشت به فهرست</Button>
    </div>

    <ErrorState v-if="error" :message="error" variant="error" class="rounded-2xl border border-red-500/40 bg-red-500/5 p-5" />
    <LoadingState v-else-if="loading" message="در حال دریافت گزارش‌ها..." />
    <template v-else>
      <Table :columns="columns" :data="logs" empty-state-message="گزارشی برای نمایش وجود ندارد.">
        <template #cell-trigger="{ value }">{{ triggerLabel(value) }}</template>
        <template #cell-target="{ value }"><span dir="ltr">{{ value || 'همه' }}</span></template>
        <template #cell-language="{ value }">{{ languageLabel(value) }}</template>
        <template #cell-status="{ value }"><Badge :variant="statusVariant(value)">{{ statusLabel(value) }}</Badge></template>
        <template #cell-latest_success_date="{ row }">{{ successDate(row) }}</template>
        <template #cell-latest_success_time="{ row }">{{ successTime(row) }}</template>
        <template #cell-duration_ms="{ value }">{{ formatDuration(value) }}</template>
      </Table>
      <Pagination v-if="pagination && pagination.total > 0" :pagination="pagination" :disabled="loading" @page-change="load" />
    </template>
  </div>
</template>
