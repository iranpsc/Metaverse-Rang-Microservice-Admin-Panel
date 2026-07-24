<template>
  <Modal
    :model-value="modelValue"
    :title="title"
    :subtitle="subtitle"
    :size="size"
    :closable="!isUploading"
    :close-on-escape="!isUploading"
    :close-on-backdrop="false"
    @update:model-value="handleVisibilityChange"
    @close="handleClose"
  >
    <div class="space-y-5" dir="rtl">
      <!-- Dropzone -->
      <div
        :class="[
          'relative flex flex-col items-center justify-center gap-4 rounded-xl border-2 border-dashed px-6 py-10 text-center transition-all duration-200',
          isDragging
            ? 'border-primary-500 bg-primary-500/10 shadow-[0_0_30px_rgba(124,58,237,0.25)]'
            : 'border-[var(--theme-border)] bg-[var(--theme-bg-glass)] hover:border-primary-500/60 hover:shadow-[0_0_18px_rgba(124,58,237,0.2)]',
          (disabled || isUploading) ? 'opacity-60 pointer-events-none' : 'cursor-pointer'
        ]"
        role="button"
        tabindex="0"
        :aria-disabled="disabled || isUploading"
        @click="openFileBrowser"
        @keydown.enter.prevent="openFileBrowser"
        @keydown.space.prevent="openFileBrowser"
        @dragenter.prevent="handleDragEnter"
        @dragover.prevent="handleDragOver"
        @dragleave.prevent="handleDragLeave"
        @drop.prevent="handleDrop"
      >
        <div
          :class="[
            'flex h-14 w-14 items-center justify-center rounded-2xl border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)] text-primary-300',
            isDragging ? 'border-primary-500/60 text-primary-200' : ''
          ]"
        >
          <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M7 16a4 4 0 01-.88-7.903 5 5 0 019.76-1.14A4.5 4.5 0 1118.5 16H16"
            />
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 12v9m0 0l-3-3m3 3l3-3"
            />
          </svg>
        </div>

        <div class="space-y-1">
          <p class="text-sm font-medium text-[var(--theme-text-primary)]">
            {{ isDragging ? 'فایل‌ها را رها کنید' : dropzoneTitle }}
          </p>
          <p class="text-xs text-[var(--theme-text-secondary)]">
            {{ dropzoneHint }}
          </p>
        </div>

        <Button
          variant="primary"
          size="sm"
          rounded="full"
          type="button"
          :disabled="disabled || isUploading"
          @click.stop="openFileBrowser"
        >
          انتخاب فایل
        </Button>

        <input
          ref="inputRef"
          type="file"
          class="hidden"
          :accept="accept"
          :multiple="multiple"
          :disabled="disabled || isUploading"
          @change="handleInputChange"
        />
      </div>

      <p v-if="helperText" class="text-xs text-[var(--theme-text-muted)]">
        {{ helperText }}
      </p>

      <p v-if="internalError" class="text-xs text-error">
        {{ internalError }}
      </p>

      <p v-if="isUploading" class="text-xs text-secondary-300">
        در حال بارگذاری فایل‌ها… لطفاً تا پایان بارگذاری صبر کنید.
      </p>

      <!-- Selected files -->
      <div v-if="fileItems.length > 0" class="space-y-3">
        <div class="flex items-center justify-between gap-3">
          <h4 class="text-sm font-medium text-[var(--theme-text-primary)]">
            فایل‌های انتخاب‌شده
            <span class="text-[var(--theme-text-muted)]">
              ({{ successCount }}/{{ fileItems.length }})
            </span>
          </h4>
          <button
            type="button"
            class="text-xs font-medium text-error transition-colors hover:text-error/80 disabled:opacity-50"
            :disabled="disabled || isUploading"
            @click="clearFiles"
          >
            پاک کردن همه
          </button>
        </div>

        <ul class="max-h-64 space-y-2 overflow-y-auto pr-1">
          <li
            v-for="(item, index) in fileItems"
            :key="item.id"
            class="flex items-center gap-3 rounded-xl border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)]/80 px-3 py-2.5"
          >
            <div
              class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg border border-[var(--theme-border)] bg-[var(--theme-bg-glass)] text-secondary-300"
            >
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                />
              </svg>
            </div>

            <div class="min-w-0 flex-1 text-right">
              <p class="truncate text-sm font-medium text-[var(--theme-text-primary)]" :title="item.file.name">
                {{ item.file.name }}
              </p>
              <p class="text-xs text-[var(--theme-text-muted)]">
                <template v-if="item.status === 'uploading'">
                  در حال بارگذاری {{ item.progress }}٪
                </template>
                <template v-else-if="item.status === 'success'">
                  بارگذاری موفق
                </template>
                <template v-else-if="item.status === 'error'">
                  {{ item.error || 'خطا در بارگذاری' }}
                </template>
                <template v-else>
                  {{ formatFileSize(item.file.size) }}
                </template>
              </p>
              <div
                v-if="item.status === 'uploading'"
                class="mt-1 h-1.5 overflow-hidden rounded-full bg-[var(--theme-border)]"
              >
                <div
                  class="h-full rounded-full bg-gradient-to-r from-primary-500 to-secondary-500 transition-all duration-200"
                  :style="{ width: `${item.progress}%` }"
                />
              </div>
            </div>

            <div class="flex shrink-0 items-center gap-1">
              <span
                v-if="item.status === 'success'"
                class="flex h-8 w-8 items-center justify-center rounded-lg bg-success/15 text-success"
                title="بارگذاری موفق"
                aria-label="بارگذاری موفق"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
              </span>

              <span
                v-else-if="item.status === 'error'"
                class="flex h-8 w-8 items-center justify-center rounded-lg bg-error/15 text-error"
                title="خطا"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </span>

              <button
                v-if="!isUploading && item.status !== 'success'"
                type="button"
                class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg text-[var(--theme-text-secondary)] transition-colors hover:bg-error/10 hover:text-error disabled:opacity-50"
                :disabled="disabled"
                :aria-label="`حذف ${item.file.name}`"
                @click="removeFile(index)"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
              </button>
            </div>
          </li>
        </ul>
      </div>
    </div>

    <template #footer>
      <div class="flex w-full items-center justify-end gap-3" dir="rtl">
        <Button
          variant="danger"
          rounded="full"
          type="button"
          :disabled="isUploading"
          @click="handleClose"
        >
          {{ allUploadsSucceeded ? 'بستن' : 'انصراف' }}
        </Button>
        <Button
          variant="primary"
          rounded="full"
          type="button"
          :loading="isUploading"
          :disabled="disabled || fileItems.length === 0 || allUploadsSucceeded"
          @click="handleUpload"
        >
          {{ isUploading ? 'در حال بارگذاری...' : uploadLabel }}
        </Button>
      </div>
    </template>
  </Modal>
</template>

<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import Resumable from 'resumablejs'
import Modal from './Modal.vue'
import Button from './Button.vue'
import { ensureCsrfCookie, getSanctumStatefulHeaders } from '../../utils/api'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: 'بارگذاری فایل'
  },
  subtitle: {
    type: String,
    default: 'فایل‌ها را بکشید و رها کنید یا از طریق دکمه انتخاب کنید'
  },
  dropzoneTitle: {
    type: String,
    default: 'فایل‌ها را اینجا رها کنید'
  },
  dropzoneHint: {
    type: String,
    default: 'یا برای انتخاب از مرورگر فایل کلیک کنید'
  },
  helperText: {
    type: String,
    default: ''
  },
  uploadLabel: {
    type: String,
    default: 'بارگذاری'
  },
  accept: {
    type: String,
    default: ''
  },
  multiple: {
    type: Boolean,
    default: true
  },
  maxFiles: {
    type: Number,
    default: 20
  },
  maxFileSize: {
    type: Number,
    default: 0
  },
  disabled: {
    type: Boolean,
    default: false
  },
  clearOnClose: {
    type: Boolean,
    default: true
  },
  chunkUpload: {
    type: Boolean,
    default: false
  },
  chunkTarget: {
    type: String,
    default: '/api/upload/chunk'
  },
  size: {
    type: String,
    default: 'lg',
    validator: (value) => ['sm', 'md', 'lg', 'xl', 'full'].includes(value)
  }
})

const emit = defineEmits(['update:modelValue', 'close', 'upload', 'change', 'remove', 'clear', 'complete'])

const inputRef = ref(null)
const fileItems = ref([])
const isDragging = ref(false)
const internalError = ref('')
const isUploading = ref(false)
let dragDepth = 0
let resumableInstance = null

const hasMaxFilesLimit = computed(() => props.maxFiles > 0)
const hasMaxSizeLimit = computed(() => props.maxFileSize > 0)

const successCount = computed(() => fileItems.value.filter((item) => item.status === 'success').length)

const allUploadsSucceeded = computed(() => (
  fileItems.value.length > 0 && fileItems.value.every((item) => item.status === 'success')
))

const formatFileSize = (bytes) => {
  if (!Number.isFinite(bytes) || bytes <= 0) return '0 B'
  const units = ['B', 'KB', 'MB', 'GB']
  const exponent = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1)
  const value = bytes / 1024 ** exponent
  return `${value.toFixed(value >= 10 || exponent === 0 ? 0 : 1)} ${units[exponent]}`
}

const getExtension = (fileName) => {
  const parts = String(fileName || '').toLowerCase().split('.')
  return parts.length > 1 ? parts.pop() : ''
}

const createFileItem = (file) => ({
  id: `${file.name}-${file.size}-${file.lastModified}-${Math.random().toString(36).slice(2, 8)}`,
  file,
  status: 'pending',
  progress: 0,
  url: null,
  fileType: getExtension(file.name),
  error: null
})

const resetInput = () => {
  if (inputRef.value) {
    inputRef.value.value = ''
  }
}

const destroyResumable = ({ cancel = true } = {}) => {
  if (!resumableInstance) return

  const instance = resumableInstance
  resumableInstance = null

  if (!cancel) return

  try {
    instance.cancel()
  } catch {
    // ignore
  }
}

const clearFiles = () => {
  if (isUploading.value) return
  destroyResumable()
  fileItems.value = []
  internalError.value = ''
  resetInput()
  emit('clear', {})
  emit('change', {})
}

const openFileBrowser = () => {
  if (props.disabled || isUploading.value) return
  inputRef.value?.click()
}

const isAcceptedFile = (file) => {
  if (!props.accept) return true

  const acceptTokens = props.accept
    .split(',')
    .map((token) => token.trim().toLowerCase())
    .filter(Boolean)

  if (acceptTokens.length === 0) return true

  const fileName = file.name.toLowerCase()
  const fileType = (file.type || '').toLowerCase()

  return acceptTokens.some((token) => {
    if (token.startsWith('.')) {
      return fileName.endsWith(token)
    }

    if (token.endsWith('/*')) {
      const prefix = token.slice(0, -1)
      return fileType.startsWith(prefix)
    }

    return fileType === token
  })
}

const validateIncomingFiles = (incoming) => {
  const accepted = []
  const rejected = []

  for (const file of incoming) {
    if (!isAcceptedFile(file)) {
      rejected.push(file.name)
      continue
    }

    if (hasMaxSizeLimit.value && file.size > props.maxFileSize) {
      rejected.push(`${file.name} (حجم بیش از حد)`)
      continue
    }

    accepted.push(file)
  }

  return { accepted, rejected }
}

const emitChange = () => {
  emit('change', buildLinksMap())
}

const mergeFiles = (incoming) => {
  if (isUploading.value) return

  const { accepted, rejected } = validateIncomingFiles(incoming)

  if (rejected.length > 0) {
    internalError.value = `برخی فایل‌ها پذیرفته نشدند: ${rejected.join('، ')}`
  } else {
    internalError.value = ''
  }

  if (accepted.length === 0) return

  const existingKeys = new Set(
    fileItems.value.map((item) => `${item.file.name}-${item.file.size}-${item.file.lastModified}`)
  )

  let nextItems = [...fileItems.value]

  if (props.multiple) {
    const uniqueIncoming = accepted
      .filter((file) => !existingKeys.has(`${file.name}-${file.size}-${file.lastModified}`))
      .map(createFileItem)
    nextItems = [...nextItems, ...uniqueIncoming]

    if (hasMaxFilesLimit.value && nextItems.length > props.maxFiles) {
      nextItems = nextItems.slice(0, props.maxFiles)
      internalError.value = `حداکثر ${props.maxFiles} فایل می‌توانید انتخاب کنید`
    }
  } else {
    nextItems = accepted.slice(0, 1).map(createFileItem)
  }

  fileItems.value = nextItems
  emitChange()
  resetInput()
}

const handleInputChange = (event) => {
  const selected = event.target.files ? Array.from(event.target.files) : []
  mergeFiles(selected)
}

const handleDragEnter = () => {
  if (props.disabled || isUploading.value) return
  dragDepth += 1
  isDragging.value = true
}

const handleDragOver = () => {
  if (props.disabled || isUploading.value) return
  isDragging.value = true
}

const handleDragLeave = () => {
  dragDepth = Math.max(0, dragDepth - 1)
  if (dragDepth === 0) {
    isDragging.value = false
  }
}

const handleDrop = (event) => {
  dragDepth = 0
  isDragging.value = false

  if (props.disabled || isUploading.value) return

  const dropped = event.dataTransfer?.files ? Array.from(event.dataTransfer.files) : []
  mergeFiles(dropped)
}

const removeFile = (index) => {
  if (isUploading.value) return
  const removed = fileItems.value[index]
  fileItems.value = fileItems.value.filter((_, i) => i !== index)
  emit('remove', removed, buildLinksMap())
  emitChange()

  if (fileItems.value.length === 0) {
    resetInput()
  }
}

const extractUploadInfo = (payload) => {
  if (typeof payload === 'object' && payload !== null && typeof payload.response === 'string') {
    payload = payload.response
  }

  if (typeof payload === 'string') {
    try {
      payload = JSON.parse(payload)
    } catch {
      return { url: null, fileType: null, message: payload }
    }
  }

  if (payload && typeof payload === 'object') {
    const data = payload.data ?? payload
    const filePath = data?.file_path ?? data?.filePath ?? payload.file_path ?? payload.filePath ?? null
    let url = data?.file_url ?? data?.fileUrl ?? payload.file_url ?? payload.fileUrl ?? null

    // Fallback when backend returns storage path without absolute URL
    if (!url && typeof filePath === 'string' && filePath.length > 0) {
      url = filePath.startsWith('http')
        ? filePath
        : `${window.location.origin}/uploads/${filePath.replace(/^\/+/, '')}`
    }

    return {
      url,
      fileType: data?.file_type ?? data?.fileType ?? payload.file_type ?? payload.fileType ?? null,
      message: payload.message ?? data?.message ?? null
    }
  }

  return { url: null, fileType: null, message: null }
}

const buildLinksMap = () => {
  const links = {}
  const typeCounts = {}

  fileItems.value
    .filter((item) => item.status === 'success' && item.url)
    .forEach((item) => {
      const baseType = (item.fileType || getExtension(item.file.name) || 'file').toLowerCase()
      typeCounts[baseType] = (typeCounts[baseType] || 0) + 1
      const key = typeCounts[baseType] === 1 ? baseType : `${baseType}_${typeCounts[baseType]}`
      links[key] = item.url
    })

  return links
}

const findItemByResumableFile = (resumableFile) => {
  const nativeFile = resumableFile?.file
  if (!nativeFile) return null

  return fileItems.value.find((item) => (
    item.file === nativeFile
    || (
      item.file.name === nativeFile.name
      && item.file.size === nativeFile.size
      && item.file.lastModified === nativeFile.lastModified
    )
  )) || null
}

const finishUploadSession = () => {
  // Snapshot before tearing down Resumable — cancel() re-fires fileProgress
  // and would overwrite success statuses back to "uploading".
  const succeeded = fileItems.value.length > 0
    && fileItems.value.every((item) => item.status === 'success' && item.url)
  const links = succeeded ? buildLinksMap() : {}

  isUploading.value = false
  destroyResumable({ cancel: false })

  if (!succeeded) {
    fileItems.value.forEach((item) => {
      if (item.status === 'uploading' || item.status === 'pending') {
        item.status = 'error'
        item.error = item.error || 'بارگذاری کامل نشد'
      }
    })
    internalError.value = 'برخی فایل‌ها بارگذاری نشدند. می‌توانید دوباره تلاش کنید.'
    return
  }

  internalError.value = ''
  emit('upload', links)
  emit('complete', links)
  emitChange()
}

const waitForChunkingComplete = (resumable, expectedCount) => new Promise((resolve) => {
  if (expectedCount <= 0) {
    resolve()
    return
  }

  let remaining = expectedCount
  let settled = false

  const settle = () => {
    if (settled) return
    settled = true
    resolve()
  }

  resumable.on('chunkingComplete', () => {
    remaining -= 1
    if (remaining <= 0) {
      settle()
    }
  })

  // Absolute fallback if chunkingComplete events were missed
  window.setTimeout(() => {
    const chunkSize = resumable.getOpt('chunkSize') || (1 * 1024 * 1024)
    const allChunked = resumable.files.every((file) => {
      const expected = Math.max(Math.floor(file.size / chunkSize), 1)
      return file.chunks.length >= expected
    })
    if (allChunked) {
      settle()
    }
  }, 2000)
})

const startChunkUpload = async () => {
  if (typeof Resumable === 'undefined') {
    internalError.value = 'امکان بارگذاری فایل وجود ندارد. لطفا صفحه را مجددا بارگذاری کنید.'
    return
  }

  const pendingItems = fileItems.value.filter((item) => item.status !== 'success')
  if (pendingItems.length === 0) {
    finishUploadSession()
    return
  }

  isUploading.value = true
  internalError.value = ''

  pendingItems.forEach((item) => {
    item.status = 'pending'
    item.progress = 0
    item.error = null
    item.url = null
  })

  try {
    await ensureCsrfCookie()
  } catch {
    isUploading.value = false
    internalError.value = 'امکان آماده‌سازی نشست امن نیست. صفحه را مجددا بارگذاری کنید.'
    return
  }

  destroyResumable()

  const acceptExtensions = props.accept
    .split(',')
    .map((token) => token.trim().toLowerCase())
    .filter((token) => token.startsWith('.'))
    .map((token) => token.slice(1))

  const resumable = new Resumable({
    target: props.chunkTarget,
    chunkSize: 1 * 1024 * 1024,
    fileType: acceptExtensions,
    headers: () => getSanctumStatefulHeaders(),
    testChunks: false,
    throttleProgressCallbacks: 1,
    maxFiles: props.maxFiles || 20,
    maxFileSize: props.maxFileSize > 0 ? props.maxFileSize : undefined,
    minFileSize: 0,
    withCredentials: true,
    simultaneousUploads: 3,
    maxChunkRetries: 8,
    chunkRetryInterval: 2000,
    fileTypeErrorCallback: (file) => {
      const item = pendingItems.find((entry) => (
        entry.file.name === file?.name && entry.file.size === file?.size
      ))
      if (item) {
        item.status = 'error'
        item.error = 'فرمت فایل مجاز نیست'
      }
      internalError.value = `فرمت فایل مجاز نیست: ${file?.name || ''}`
    },
    maxFileSizeErrorCallback: (file) => {
      const item = pendingItems.find((entry) => (
        entry.file.name === file?.name && entry.file.size === file?.size
      ))
      if (item) {
        item.status = 'error'
        item.error = 'حجم فایل بیش از حد مجاز است'
      }
      internalError.value = `حجم فایل بیش از حد مجاز است: ${file?.name || ''}`
    }
  })

  if (!resumable.support) {
    isUploading.value = false
    internalError.value = 'مرورگر شما از بارگذاری تکه‌ای فایل پشتیبانی نمی‌کند.'
    return
  }

  resumableInstance = resumable

  resumable.on('fileAdded', (file) => {
    const item = findItemByResumableFile(file)
    if (item) {
      item.status = 'uploading'
      item.progress = 0
    }
  })

  resumable.on('fileProgress', (file) => {
    const item = findItemByResumableFile(file)
    if (!item) return

    // Never overwrite terminal states — Resumable cancel/complete can re-fire progress
    if (item.status === 'success' || item.status === 'error') {
      return
    }

    item.status = 'uploading'
    item.progress = Math.floor(file.progress() * 100)
  })

  resumable.on('fileSuccess', (file, response) => {
    const item = findItemByResumableFile(file)
    const { url, fileType, message } = extractUploadInfo(response)

    if (!item) return

    if (!url) {
      item.status = 'error'
      item.error = message || 'پاسخ نامعتبر از سرور دریافت شد.'
      return
    }

    item.status = 'success'
    item.progress = 100
    item.url = url
    item.fileType = (fileType || item.fileType || getExtension(item.file.name)).toLowerCase()
    item.error = null
  })

  resumable.on('fileError', (file, response) => {
    const item = findItemByResumableFile(file)
    if (!item) return

    const { message } = extractUploadInfo(response)
    item.status = 'error'
    item.error = message || 'خطا در بارگذاری فایل'
  })

  pendingItems.forEach((item) => {
    resumable.addFile(item.file)
  })

  if (resumable.files.length === 0) {
    isUploading.value = false
    internalError.value = internalError.value || 'هیچ فایلی برای بارگذاری آماده نشد. فرمت فایل را بررسی کنید.'
    destroyResumable()
    return
  }

  // Resumable builds chunks asynchronously via setTimeout(0).
  // Calling upload() before chunkingComplete makes isComplete() true
  // (empty chunks) and fires "complete" without uploading anything.
  await waitForChunkingComplete(resumable, resumable.files.length)

  if (!resumableInstance) {
    isUploading.value = false
    return
  }

  resumable.on('complete', () => {
    finishUploadSession()
  })

  resumable.upload()
}

const handleUpload = async () => {
  if (fileItems.value.length === 0 || props.disabled || isUploading.value || allUploadsSucceeded.value) {
    return
  }

  if (props.chunkUpload) {
    await startChunkUpload()
    return
  }

  const files = fileItems.value.map((item) => item.file)
  emit('upload', props.multiple ? files : files[0])
}

const handleClose = () => {
  if (isUploading.value) return
  emit('update:modelValue', false)
  emit('close')
}

const handleVisibilityChange = (value) => {
  if (!value && isUploading.value) {
    return
  }
  emit('update:modelValue', value)
  if (!value) {
    emit('close')
  }
}

watch(
  () => props.modelValue,
  (isOpen) => {
    if (!isOpen) {
      isDragging.value = false
      dragDepth = 0
      internalError.value = ''
      isUploading.value = false
      destroyResumable()
      if (props.clearOnClose) {
        fileItems.value = []
        resetInput()
      }
    }
  }
)

onBeforeUnmount(() => {
  destroyResumable()
})
</script>

<style scoped>
.text-error {
  color: var(--color-error, #EF4444);
}

.text-success {
  color: var(--color-success, #22C55E);
}

.bg-success\/15 {
  background-color: rgba(34, 197, 94, 0.15);
}

.bg-error\/15 {
  background-color: rgba(239, 68, 68, 0.15);
}
</style>
