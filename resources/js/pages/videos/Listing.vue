<template>
  <div class="p-6 space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
      <div>
        <h1 class="text-3xl font-bold text-[var(--theme-text-primary)]">مدیریت ویدیوها</h1>
        <p class="text-[var(--theme-text-secondary)]">بارگذاری، ویرایش و مشاهده ویدیوهای آموزشی</p>
      </div>
      <Button
        variant="primary"
        size="lg"
        rounded="full"
        class="self-start md:self-auto"
        @click="openCreateModal"
      >
        بارگذاری ویدیو جدید
      </Button>
    </div>

    <div class="max-w-xl">
      <SearchBox
        v-model="searchTerm"
        placeholder="جستجو بر اساس عنوان ویدیو"
        :debounce-ms="500"
        @search="handleSearch"
        @clear="handleClear"
      />
    </div>

    <LoadingState v-if="loading" />

    <ErrorState v-else-if="error" :message="error" variant="error" />

    <div v-else class="space-y-6">
      <Table
        :columns="tableColumns"
        :data="videos"
        :pagination="pagination"
        :show-row-number="true"
        empty-state-message="ویدیویی ثبت نشده است."
      >
        <template #cell-creator_code="{ row }">
          <span class="text-sm text-[var(--theme-text-primary)]">{{ row.creator_code || '-' }}</span>
        </template>

        <template #cell-created_at="{ row }">
          <div class="flex flex-col text-xs leading-tight text-[var(--theme-text-secondary)]">
            <span>{{ row.created_at_formatted?.date || '-' }}</span>
            <span>{{ row.created_at_formatted?.time || '' }}</span>
          </div>
        </template>

        <template #cell-actions="{ row }">
          <div class="flex flex-wrap gap-2">
            <Button
              variant="glass"
              size="sm"
              rounded="full"
              class="!p-2 !gap-0 min-w-[2.25rem]"
              title="مشاهده جزئیات"
              aria-label="مشاهده جزئیات"
              @click="openDetailsModal(row)"
            >
              <template #icon-left>
                <TableActionIcon name="details" />
              </template>
            </Button>
            <Button
              variant="glass"
              size="sm"
              rounded="full"
              class="!p-2 !gap-0 min-w-[2.25rem]"
              title="ویرایش"
              aria-label="ویرایش"
              @click="openEditModal(row)"
            >
              <template #icon-left>
                <TableActionIcon name="edit" />
              </template>
            </Button>
            <Button
              variant="danger"
              size="sm"
              rounded="full"
              class="!p-2 !gap-0 min-w-[2.25rem]"
              title="حذف"
              aria-label="حذف"
              :loading="deletingId === row.id"
              @click="confirmDelete(row)"
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

    <Modal
      v-model="createModalOpen"
      title="بارگذاری ویدیو جدید"
      size="xl"
      close-on-backdrop
      @close="resetCreateForm"
    >
      <form class="space-y-5" @submit.prevent="submitCreate">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <Input
            v-model="createForm.title"
            label="عنوان"
            placeholder="عنوان ویدیو"
            required
            :error="createErrors.title"
          />
          <Input
            v-model="createForm.creator_code"
            label="کد شهروندی بارگذار"
            placeholder="hm-..."
            required
            :error="createErrors.creator_code"
          />
        </div>

        <RichTextEditor
          v-model="createForm.description"
          label="توضیحات"
          required
          :error="createErrors.description"
        />

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <Select
            v-model="createForm.video_category_id"
            label="دسته بندی"
            placeholder="انتخاب کنید"
            :options="categoryOptions"
            required
            :disabled="metaLoading"
            :error="createErrors.video_category_id"
          />
          <Select
            v-model="createForm.video_sub_category_id"
            label="زیر دسته"
            placeholder="ابتدا دسته بندی را انتخاب کنید"
            :options="createSubCategoryOptions"
            required
            :disabled="!createForm.video_category_id"
            :error="createErrors.video_sub_category_id"
          />
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <FileInput
            v-model="createForm.image"
            label="تصویر ویدیو"
            accept="image/*"
            required
            :error="createErrors.image"
          />

          <div class="space-y-2">
            <label class="block text-sm font-medium text-[var(--theme-text-primary)]">
              فایل ویدیو
              <span class="text-error">*</span>
            </label>
            <div
              ref="createVideoBrowseRef"
              class="flex cursor-pointer items-center justify-between rounded-xl border border-dashed border-[var(--theme-border)] bg-[var(--theme-bg-glass)] px-4 py-3 transition hover:border-primary-400 hover:shadow-[0_0_18px_rgba(124,58,237,0.35)]"
            >
              <div class="flex flex-col">
                <span class="text-sm font-medium text-[var(--theme-text-primary)]">
                  {{ uploadStates.create.fileName || 'انتخاب فایل MP4 (حداکثر 1 گیگابایت)' }}
                </span>
                <span class="text-xs text-[var(--theme-text-muted)]">
                  با کلیک، انتخاب فایل و بارگذاری تکه‌ای آغاز می‌شود.
                </span>
              </div>
              <span class="shrink-0 text-sm font-medium text-primary-300">انتخاب فایل</span>
            </div>
            <div
              v-if="uploadStates.create.isUploading"
              class="h-2 w-full overflow-hidden rounded-full bg-[var(--theme-border)] transition-shadow duration-300"
              :class="uploadStates.create.connectionLost ? 'shadow-[inset_0_0_0_1px_rgba(239,68,68,0.45)]' : ''"
            >
              <div
                class="h-2 rounded-full transition-all duration-300"
                :class="
                  uploadStates.create.connectionLost
                    ? 'bg-gradient-to-r from-red-600 to-red-500 shadow-[0_0_14px_rgba(239,68,68,0.35)]'
                    : 'bg-gradient-to-r from-primary-500 to-secondary-500'
                "
                :style="{ width: `${uploadStates.create.progress}%` }"
              />
            </div>
            <p
              v-if="uploadStates.create.connectionLost && uploadStates.create.isUploading"
              class="text-xs font-medium text-error"
            >
              قطع اتصال اینترنت — بارگذاری متوقف شد. پس از برقراری مجدد اتصال، به‌صورت خودکار از همان نقطه ادامه می‌یابد.
            </p>
            <p v-if="uploadStates.create.error" class="text-xs text-error">
              {{ uploadStates.create.error }}
            </p>
            <p v-else-if="createErrors.video_file_name" class="text-xs text-error">
              {{ createErrors.video_file_name }}
            </p>
          </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4">
          <Button variant="ghost" rounded="full" @click.prevent="closeCreateModal">
            انصراف
          </Button>
          <Button type="submit" variant="primary" rounded="full" :loading="creating">
            ثبت ویدیو
          </Button>
        </div>
      </form>
    </Modal>

    <Modal
      v-model="editModalOpen"
      title="ویرایش ویدیو"
      size="xl"
      close-on-backdrop
      @close="resetEditForm"
    >
      <form class="space-y-5" @submit.prevent="submitEdit">
        <Input
          v-model="editForm.title"
          label="عنوان"
          required
          :error="editErrors.title"
        />

        <RichTextEditor
          v-model="editForm.description"
          label="توضیحات"
          required
          :error="editErrors.description"
        />

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div class="space-y-3">
            <FileInput
              v-model="editForm.image"
              label="تصویر جدید (اختیاری)"
              accept="image/*"
              :error="editErrors.image"
            />
            <div
              v-if="selectedVideo?.image_url"
              class="rounded-lg border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)]/60 p-3 text-sm text-[var(--theme-text-secondary)]"
            >
              تصویر فعلی:
              <a
                :href="selectedVideo.image_url"
                target="_blank"
                rel="noopener"
                class="inline-flex items-center gap-1 text-primary-300 hover:underline"
                title="مشاهده تصویر"
                aria-label="مشاهده تصویر"
              >
                <TableActionIcon name="image" icon-class="w-4 h-4 shrink-0" />
              </a>
            </div>
          </div>

          <div class="space-y-3">
            <label class="block text-sm font-medium text-[var(--theme-text-primary)]">
              فایل ویدیو (اختیاری)
            </label>
            <div
              ref="editVideoBrowseRef"
              class="flex cursor-pointer items-center justify-between rounded-xl border border-dashed border-[var(--theme-border)] bg-[var(--theme-bg-glass)] px-4 py-3 transition hover:border-primary-400 hover:shadow-[0_0_18px_rgba(124,58,237,0.35)]"
            >
              <div class="flex flex-col">
                <span class="text-sm font-medium text-[var(--theme-text-primary)]">
                  {{ uploadStates.edit.fileName || 'انتخاب فایل جدید برای جایگزینی' }}
                </span>
                <span class="text-xs text-[var(--theme-text-muted)]">
                  بارگذاری تکه‌ای با کلیک آغاز می‌شود.
                </span>
              </div>
              <span class="shrink-0 text-sm font-medium text-primary-300">انتخاب فایل</span>
            </div>
            <div
              v-if="uploadStates.edit.isUploading"
              class="h-2 w-full overflow-hidden rounded-full bg-[var(--theme-border)] transition-shadow duration-300"
              :class="uploadStates.edit.connectionLost ? 'shadow-[inset_0_0_0_1px_rgba(239,68,68,0.45)]' : ''"
            >
              <div
                class="h-2 rounded-full transition-all duration-300"
                :class="
                  uploadStates.edit.connectionLost
                    ? 'bg-gradient-to-r from-red-600 to-red-500 shadow-[0_0_14px_rgba(239,68,68,0.35)]'
                    : 'bg-gradient-to-r from-primary-500 to-secondary-500'
                "
                :style="{ width: `${uploadStates.edit.progress}%` }"
              />
            </div>
            <p
              v-if="uploadStates.edit.connectionLost && uploadStates.edit.isUploading"
              class="text-xs font-medium text-error"
            >
              قطع اتصال اینترنت — بارگذاری متوقف شد. پس از برقراری مجدد اتصال، به‌صورت خودکار از همان نقطه ادامه می‌یابد.
            </p>
            <p v-if="uploadStates.edit.error" class="text-xs text-error">
              {{ uploadStates.edit.error }}
            </p>
            <div
              v-if="selectedVideo?.file_url"
              class="rounded-lg border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)]/60 p-3 text-sm text-[var(--theme-text-secondary)]"
            >
              فایل فعلی:
              <a
                :href="selectedVideo.file_url"
                target="_blank"
                rel="noopener"
                class="inline-flex items-center gap-1 text-primary-300 hover:underline"
                title="مشاهده فایل"
                aria-label="مشاهده فایل"
              >
                <TableActionIcon name="file" icon-class="w-4 h-4 shrink-0" />
              </a>
            </div>
            <p v-if="editErrors.video_file_name" class="text-xs text-error">
              {{ editErrors.video_file_name }}
            </p>
          </div>
        </div>

        <Input
          v-model="editForm.creator_code"
          label="کد شهروندی بارگذار"
          placeholder="hm-..."
          :error="editErrors.creator_code"
        />

        <div class="rounded-lg border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)]/60 p-4 text-sm text-[var(--theme-text-secondary)]">
          <div>دسته فعلی: {{ selectedVideoCategoryDisplay }}</div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4">
          <Button variant="ghost" rounded="full" @click.prevent="closeEditModal">
            انصراف
          </Button>
          <Button type="submit" variant="primary" rounded="full" :loading="updating">
            ذخیره تغییرات
          </Button>
        </div>
      </form>
    </Modal>
    <Modal
      v-model="detailsModalOpen"
      title="جزئیات ویدیو"
      size="lg"
      close-on-backdrop
      @close="closeDetailsModal"
    >
      <div v-if="selectedVideoDetails" class="space-y-6">
        <div class="grid gap-4 md:grid-cols-2">
          <div class="rounded-2xl border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)]/80 p-4">
            <h3 class="text-sm font-medium text-[var(--theme-text-secondary)]">تصویر ویدیو</h3>
            <div class="mt-3 flex items-center justify-center rounded-xl bg-[var(--theme-bg-glass)] p-3">
              <img
                v-if="selectedVideoDetails.image_url"
                :src="selectedVideoDetails.image_url"
                alt="تصویر ویدیو"
                class="max-h-56 w-full rounded-xl object-cover shadow-[0_12px_30px_rgba(124,58,237,0.18)]"
              />
              <span v-else class="text-sm text-[var(--theme-text-muted)]">تصویری ثبت نشده است</span>
            </div>
          </div>
          <div class="rounded-2xl border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)]/80 p-4">
            <h3 class="text-sm font-medium text-[var(--theme-text-secondary)]">ویدیو</h3>
            <div class="mt-3 flex items-center justify-center rounded-xl bg-[var(--theme-bg-glass)] p-3">
              <video
                v-if="selectedVideoDetails.file_url"
                :src="selectedVideoDetails.file_url"
                controls
                class="max-h-56 w-full rounded-xl shadow-[0_12px_30px_rgba(14,116,144,0.2)]"
              >
                مرورگر شما از پخش ویدیو پشتیبانی نمی‌کند.
              </video>
              <span v-else class="text-sm text-[var(--theme-text-muted)]">فایل ویدیو ثبت نشده است</span>
            </div>
          </div>
        </div>
        <div class="grid gap-4 md:grid-cols-2">
          <div class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-bg-glass)] p-4">
            <p class="text-xs text-[var(--theme-text-muted)]">عنوان</p>
            <p class="mt-1 text-sm font-semibold text-[var(--theme-text-primary)]">
              {{ selectedVideoDetails.title || '-' }}
            </p>
          </div>
          <div class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-bg-glass)] p-4">
            <p class="text-xs text-[var(--theme-text-muted)]">کد شهروندی</p>
            <p class="mt-1 text-sm font-semibold text-[var(--theme-text-primary)]">
              {{ selectedVideoDetails.creator_code || '-' }}
            </p>
          </div>
          <div class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-bg-glass)] p-4 md:col-span-2">
            <p class="text-xs text-[var(--theme-text-muted)]">توضیحات</p>
            <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-[var(--theme-text-secondary)]">
              {{ selectedVideoDescription || 'توضیحاتی ثبت نشده است.' }}
            </p>
          </div>
        </div>
      </div>
      <div v-else class="text-center text-sm text-[var(--theme-text-muted)]">ویدیو یافت نشد.</div>
    </Modal>

    <PhoneVerificationModal :phone-verification="phoneVerification" />
  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import Resumable from 'resumablejs'
import { ensureCsrfCookie, getSanctumStatefulHeaders } from '../../utils/api'
import PhoneVerificationModal from '../../components/PhoneVerificationModal.vue'
import { useVideos } from '../../composables/useVideos'
import { useToast } from '../../composables/useToast'
import { usePhoneVerification } from '../../composables/usePhoneVerification'
import {
  Button,
  ErrorState,
  FileInput,
  Input,
  LoadingState,
  Modal,
  Pagination,
  SearchBox,
  Select,
  Table
} from '../../components/ui'
import RichTextEditor from '../../components/ui/RichTextEditor.vue'
import TableActionIcon from '../../components/icons/TableActionIcon.vue'

const { showToast } = useToast()
const phoneVerification = usePhoneVerification()
const {
  fetchVideoMeta,
  fetchVideos: fetchVideosApi,
  createVideo,
  updateVideo,
  deleteVideo
} = useVideos()

const loading = ref(true)
const error = ref(null)
const videos = ref([])
const pagination = ref(null)
const currentPage = ref(1)
const perPage = 10
const searchTerm = ref('')

const deletingId = ref(null)
const creating = ref(false)
const updating = ref(false)

const categoriesMeta = ref([])
const metaLoading = ref(false)

const createModalOpen = ref(false)
const editModalOpen = ref(false)
const selectedVideo = ref(null)
const detailsModalOpen = ref(false)
const selectedVideoDetails = ref(null)
const selectedVideoDescription = computed(() => {
  const rawDescription = selectedVideoDetails.value?.description
  if (!rawDescription) {
    return ''
  }

  try {
    if (typeof window !== 'undefined' && window.DOMParser) {
      const parser = new window.DOMParser()
      const doc = parser.parseFromString(rawDescription, 'text/html')
      return doc.body.textContent?.trim() || ''
    }
  } catch (err) {
    console.error('Description parse error:', err)
  }

  return rawDescription.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim()
})

const createForm = reactive({
  title: '',
  description: '',
  video_category_id: '',
  video_sub_category_id: '',
  image: null,
  video_file_name: '',
  creator_code: ''
})

const createErrors = reactive({
  title: null,
  description: null,
  video_category_id: null,
  video_sub_category_id: null,
  image: null,
  video_file_name: null,
  creator_code: null
})

const editForm = reactive({
  title: '',
  description: '',
  image: null,
  video_file_name: '',
  creator_code: ''
})

const editErrors = reactive({
  title: null,
  description: null,
  image: null,
  video_file_name: null,
  creator_code: null
})

const uploadStates = reactive({
  create: {
    progress: 0,
    isUploading: false,
    completed: false,
    error: null,
    fileName: '',
    connectionLost: false
  },
  edit: {
    progress: 0,
    isUploading: false,
    completed: false,
    error: null,
    fileName: '',
    connectionLost: false
  }
})

const createVideoBrowseRef = ref(null)
const editVideoBrowseRef = ref(null)

const resumableInstances = {
  create: null,
  edit: null
}

const tableColumns = computed(() => [
  { key: 'title', label: 'عنوان' },
  { key: 'category_display', label: 'دسته', textSecondary: true },
  { key: 'creator_code', label: 'کد شهروندی' },
  { key: 'created_at', label: 'تاریخ ایجاد', textSecondary: true },
  { key: 'actions', label: 'مدیریت' }
])

const categoryOptions = computed(() =>
  categoriesMeta.value.map((category) => ({
    value: String(category.id),
    label: category.name
  }))
)

const createSubCategoryOptions = computed(() => {
  if (!createForm.video_category_id) {
    return []
  }

  const category = categoriesMeta.value.find((item) => item.id === Number(createForm.video_category_id))
  if (!category) {
    return []
  }

  return (category.sub_categories || []).map((subCategory) => ({
    value: String(subCategory.id),
    label: subCategory.name
  }))
})

const selectedVideoCategoryDisplay = computed(() => {
  if (!selectedVideo.value) {
    return '-'
  }

  const subCategoryName = selectedVideo.value.category?.name
  const parentName = selectedVideo.value.category?.parent?.name

  if (subCategoryName && parentName) {
    return `${parentName} / ${subCategoryName}`
  }

  return subCategoryName || parentName || '-'
})

const resetErrors = (target) => {
  Object.keys(target).forEach((key) => {
    target[key] = null
  })
}

const normalizeErrors = (errors, target) => {
  Object.keys(target).forEach((key) => {
    target[key] = errors?.[key]?.[0] || null
  })
}

const resetUploadState = (context) => {
  uploadStates[context].progress = 0
  uploadStates[context].isUploading = false
  uploadStates[context].completed = false
  uploadStates[context].error = null
  uploadStates[context].fileName = ''
  uploadStates[context].connectionLost = false
}

const destroyResumable = (context) => {
  const instance = resumableInstances[context]
  if (instance) {
    instance.cancel()
    resumableInstances[context] = null
  }
}

const extractUploadInfo = (response) => {
  if (!response) {
    return { fileName: null, message: null }
  }

  let payload = response

  if (typeof payload === 'object' && payload !== null && typeof payload.response === 'string') {
    payload = payload.response
  }

  if (typeof payload === 'string') {
    try {
      payload = JSON.parse(payload)
    } catch (error) {
      return { fileName: null, message: payload }
    }
  }

  if (payload && typeof payload === 'object') {
    const data = payload.data ?? payload
    const fileName = data?.file_name ?? data?.fileName ?? null
    const message = payload.message ?? data?.message ?? null

    return { fileName, message }
  }

  return { fileName: null, message: null }
}

const setupResumable = (context) => {
  const browseElement = context === 'create' ? createVideoBrowseRef.value : editVideoBrowseRef.value
  if (!browseElement) {
    return
  }

  if (typeof Resumable === 'undefined') {
    uploadStates[context].error = 'امکان بارگذاری ویدیو وجود ندارد. لطفا صفحه را مجددا بارگذاری کنید.'
    return
  }

  if (resumableInstances[context]) {
    resumableInstances[context].assignBrowse(browseElement)
    return
  }

  const resumable = new Resumable({
    target: '/api/videos/chunk',
    chunkSize: 1 * 1024 * 1024,
    fileType: ['mp4'],
    headers: () => getSanctumStatefulHeaders(),
    testChunks: false,
    throttleProgressCallbacks: 1,
    maxFiles: 1,
    withCredentials: true,
    maxChunkRetries: 8,
    chunkRetryInterval: 2000
  })

  resumable.assignBrowse(browseElement)
  attachResumableEvents(resumable, context)
  resumableInstances[context] = resumable
}

const attachResumableEvents = (resumable, context) => {
  const state = uploadStates[context]
  const form = context === 'create' ? createForm : editForm

  resumable.on('fileAdded', async () => {
    state.isUploading = true
    state.progress = 0
    state.error = null
    state.completed = false
    state.fileName = ''
    state.connectionLost = false
    form.video_file_name = ''
    try {
      await ensureCsrfCookie()
    } catch {
      state.error = 'امکان آماده‌سازی نشست امن نیست. صفحه را مجددا بارگذاری کنید.'
      state.isUploading = false
      resumable.cancel()
      return
    }
    resumable.upload()
  })

  resumable.on('fileProgress', (file) => {
    state.progress = Math.floor(file.progress() * 100)
  })

  resumable.on('fileSuccess', (file, response) => {
    const { fileName, message } = extractUploadInfo(response)

    if (!fileName) {
      state.error = message || 'پاسخ نامعتبر از سرور دریافت شد.'
      state.isUploading = false
      state.completed = false
      state.connectionLost = false
      return
    }

    form.video_file_name = fileName
    state.isUploading = false
    state.completed = true
    state.progress = 100
    state.fileName = fileName
    state.connectionLost = false
  })

  resumable.on('fileError', (file, response) => {
    if (typeof navigator !== 'undefined' && !navigator.onLine) {
      state.connectionLost = true
      resumable.pause()
      state.isUploading = true
      state.error = null
      return
    }

    state.isUploading = false
    state.completed = false

    const { message } = extractUploadInfo(response)

    if (message) {
      state.error = message
      return
    }

    if (typeof response === 'string') {
      state.error = response
    } else if (response?.message) {
      state.error = response.message
    } else {
      state.error = 'خطا در بارگذاری ویدیو. لطفا مجددا تلاش کنید.'
    }
  })
}

const syncUploadConnectionState = () => {
  if (typeof navigator === 'undefined') {
    return
  }
  if (!navigator.onLine) {
    handleOffline()
  }
}

const handleOffline = () => {
  Object.keys(resumableInstances).forEach((context) => {
    const instance = resumableInstances[context]
    const state = uploadStates[context]
    if (instance && state.isUploading && !state.completed) {
      instance.pause()
      state.connectionLost = true
    }
  })
}

const handleOnline = () => {
  Object.keys(resumableInstances).forEach((context) => {
    const instance = resumableInstances[context]
    const state = uploadStates[context]
    if (!instance || !state.isUploading || state.completed) {
      return
    }
    state.connectionLost = false
    instance.upload()
  })
}

const formatCategoryDisplay = (video) => {
  const subCategory = video.category
  const parent = subCategory?.parent

  if (subCategory?.name && parent?.name) {
    return `${parent.name} / ${subCategory.name}`
  }

  return subCategory?.name || parent?.name || '-'
}

const fetchMeta = async () => {
  try {
    metaLoading.value = true
    const response = await fetchVideoMeta()
    if (response.data.success) {
      categoriesMeta.value = response.data.data.categories || []
    }
  } catch (err) {
    console.error('Video meta fetch error:', err)
    showToast('خطا در دریافت اطلاعات دسته بندی و زیر دسته ها', 'error')
  } finally {
    metaLoading.value = false
  }
}

const fetchVideos = async () => {
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

    const response = await fetchVideosApi(params)

    if (response.data.success) {
      const items = response.data.data.videos || []
      videos.value = items.map((video) => ({
        ...video,
        category_display: formatCategoryDisplay(video)
      }))
      pagination.value = response.data.data.pagination || null
    } else {
      videos.value = []
      pagination.value = null
      error.value = response.data.message || 'خطا در دریافت ویدیوها'
    }
  } catch (err) {
    console.error('Videos fetch error:', err)

    if (err.response && (err.response.status === 401 || err.response.status === 403)) {
      loading.value = false
      return
    }

    error.value = err.response?.data?.message || 'خطا در بارگذاری ویدیوها'
    videos.value = []
    pagination.value = null
  } finally {
    loading.value = false
  }
}

const handleSearch = () => {
  currentPage.value = 1
  fetchVideos()
}

const handleClear = () => {
  searchTerm.value = ''
  currentPage.value = 1
  fetchVideos()
}

const goToPage = (page) => {
  if (page >= 1 && (!pagination.value || page <= pagination.value.last_page)) {
    currentPage.value = page
    fetchVideos()
  }
}

const resetCreateForm = () => {
  createForm.title = ''
  createForm.description = ''
  createForm.video_category_id = ''
  createForm.video_sub_category_id = ''
  createForm.image = null
  createForm.video_file_name = ''
  createForm.creator_code = ''
  resetErrors(createErrors)
  resetUploadState('create')
}

const closeCreateModal = () => {
  createModalOpen.value = false
}

const openCreateModal = async () => {
  resetCreateForm()
  if (categoriesMeta.value.length === 0 && !metaLoading.value) {
    await fetchMeta()
  }
  createModalOpen.value = true
}

const resetEditForm = () => {
  editForm.title = ''
  editForm.description = ''
  editForm.image = null
  editForm.video_file_name = ''
  editForm.creator_code = ''
  selectedVideo.value = null
  resetErrors(editErrors)
  resetUploadState('edit')
}

const closeEditModal = () => {
  editModalOpen.value = false
}

const openEditModal = (video) => {
  if (!video) {
    return
  }

  selectedVideo.value = video
  editForm.title = video.title || ''
  editForm.description = video.description || ''
  editForm.image = null
  editForm.video_file_name = ''
  editForm.creator_code = video.creator_code || ''
  resetErrors(editErrors)
  resetUploadState('edit')
  editModalOpen.value = true
}

const openDetailsModal = (video) => {
  if (!video) {
    return
  }
  selectedVideoDetails.value = video
  detailsModalOpen.value = true
}

const closeDetailsModal = () => {
  detailsModalOpen.value = false
  selectedVideoDetails.value = null
}

const ensureVideoUploaded = (form, errorsTarget) => {
  if (!form.video_file_name) {
    errorsTarget.video_file_name = 'لطفا ابتدا فایل ویدیو را بارگذاری کنید.'
    return false
  }
  return true
}

const submitCreate = async () => {
  resetErrors(createErrors)

  if (uploadStates.create.isUploading) {
    showToast('منتظر بمانید تا بارگذاری ویدیو تکمیل شود.', 'warning')
    return
  }

  if (!ensureVideoUploaded(createForm, createErrors)) {
    showToast('لطفا فایل ویدیو را بارگذاری کنید.', 'warning')
    return
  }

  try {
    creating.value = true
    const formData = new FormData()
    formData.append('title', createForm.title || '')
    formData.append('description', createForm.description || '')
    formData.append('video_category_id', createForm.video_category_id || '')
    formData.append('video_sub_category_id', createForm.video_sub_category_id || '')
    formData.append('creator_code', createForm.creator_code || '')
    formData.append('video_file_name', createForm.video_file_name || '')

    if (createForm.image instanceof File) {
      formData.append('image', createForm.image)
    }

    const response = await createVideo(formData)

    if (response.data.success) {
      showToast('ویدیو با موفقیت ایجاد شد.', 'success')
      closeCreateModal()
      currentPage.value = 1
      await fetchVideos()
    }
  } catch (err) {
    if (err.response?.status === 422) {
      normalizeErrors(err.response.data.errors, createErrors)
      showToast('لطفا خطاهای فرم را بررسی کنید.', 'warning')
    } else {
      console.error('Video create error:', err)
      showToast(err.response?.data?.message || 'خطا در ایجاد ویدیو', 'error')
    }
  } finally {
    creating.value = false
  }
}

const submitEdit = async () => {
  if (!selectedVideo.value) {
    return
  }

  resetErrors(editErrors)

  if (uploadStates.edit.isUploading) {
    showToast('منتظر بمانید تا بارگذاری ویدیو تکمیل شود.', 'warning')
    return
  }

  try {
    updating.value = true
    const formData = new FormData()
    formData.append('title', editForm.title || '')
    formData.append('description', editForm.description || '')
    formData.append('_method', 'PUT')

    if (editForm.creator_code) {
      formData.append('creator_code', editForm.creator_code)
    }

    if (editForm.image instanceof File) {
      formData.append('image', editForm.image)
    }

    if (editForm.video_file_name) {
      formData.append('video_file_name', editForm.video_file_name)
    }

    const response = await updateVideo(selectedVideo.value.id, formData)

    if (response.data.success) {
      showToast('ویدیو با موفقیت به روزرسانی شد.', 'success')
      closeEditModal()
      await fetchVideos()
    }
  } catch (err) {
    if (err.response?.status === 422) {
      normalizeErrors(err.response.data.errors, editErrors)
      showToast('لطفا خطاهای فرم را بررسی کنید.', 'warning')
    } else {
      console.error('Video update error:', err)
      showToast(err.response?.data?.message || 'خطا در بروزرسانی ویدیو', 'error')
    }
  } finally {
    updating.value = false
  }
}

const confirmDelete = async (video) => {
  if (!video || deletingId.value) {
    return
  }

  await phoneVerification.confirmThenVerify(
    {
      message: 'آیا از حذف این ویدیو مطمئن هستید؟ این عملیات قابل بازگشت نیست.',
      title: 'حذف ویدیو',
      confirmText: 'بله، حذف شود',
      cancelText: 'انصراف',
      reverseButtons: true,
      customClass: {
        popup: 'rounded-2xl bg-[var(--theme-bg-elevated)] text-[var(--theme-text-primary)] border border-[var(--theme-border)]',
        title: 'text-[var(--theme-text-primary)] font-semibold',
        htmlContainer: 'text-[var(--theme-text-secondary)] text-sm'
      }
    },
    async (payload) => {
      try {
        deletingId.value = video.id
        await deleteVideo(video.id, payload)
        showToast('ویدیو با موفقیت حذف شد.', 'success')
        phoneVerification.resetVerificationState()
        await fetchVideos()

        if ((!videos.value || videos.value.length === 0) && currentPage.value > 1) {
          currentPage.value -= 1
          await fetchVideos()
        }
      } catch (err) {
        console.error('Video delete error:', err)

        if (await phoneVerification.handleApiVerificationError(err)) {
          return
        }

        showToast(err.response?.data?.message || 'خطا در حذف ویدیو', 'error')
      } finally {
        deletingId.value = null
      }
    }
  )
}

watch(createModalOpen, (isOpen) => {
  if (isOpen) {
    nextTick(() => {
      setupResumable('create')
      syncUploadConnectionState()
    })
  } else {
    destroyResumable('create')
    resetUploadState('create')
  }
})

watch(editModalOpen, (isOpen) => {
  if (isOpen) {
    nextTick(() => {
      setupResumable('edit')
      syncUploadConnectionState()
    })
  } else {
    destroyResumable('edit')
    resetUploadState('edit')
  }
})

watch(
  () => createForm.video_category_id,
  () => {
    createForm.video_sub_category_id = ''
  }
)

onMounted(async () => {
  window.addEventListener('offline', handleOffline)
  window.addEventListener('online', handleOnline)
  syncUploadConnectionState()

  await fetchMeta()
  await fetchVideos()
})

onBeforeUnmount(() => {
  window.removeEventListener('offline', handleOffline)
  window.removeEventListener('online', handleOnline)
  destroyResumable('create')
  destroyResumable('edit')
})
</script>

<style scoped>
.space-y-5 > :deep(* + *) {
  margin-top: 1.25rem;
}
</style>
