<template>
  <div
    v-if="isLocked"
    class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/70 p-6 backdrop-blur-sm"
    dir="rtl"
  >
    <div class="w-full max-w-md rounded-2xl border border-[var(--theme-border)] bg-[var(--theme-bg-elevated)] p-6 text-center shadow-2xl">
      <h2 class="mb-3 text-lg font-semibold text-[var(--theme-text-primary)]">
        این تب غیرفعال است
      </h2>
      <p class="mb-5 text-sm text-[var(--theme-text-secondary)]">
        نشست شما در تب دیگری فعال است. برای ادامه، این تب را فعال کنید.
      </p>
      <button
        type="button"
        class="rounded-full bg-gradient-to-r from-[#7C3AED] to-[#06B6D4] px-5 py-2 text-sm font-medium text-white transition hover:opacity-90"
        @click="activate"
      >
        فعال‌سازی این تب
      </button>
    </div>
  </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'
import { confirm } from '../utils/notifications'
import { authApi } from '../utils/api'
import {
  activateCurrentAdminTab,
  initializeAdminTabSession,
  isAdminTabActive
} from '../utils/adminTabSession'

const isLocked = ref(false)
let dialogOpen = false

const showConflictDialog = async () => {
  if (dialogOpen || isAdminTabActive()) return

  isLocked.value = true
  dialogOpen = true

  try {
    const result = await confirm(
      'این نشست در تب دیگری فعال است. آیا می‌خواهید این تب را فعال کنید؟',
      'تب فعال دیگری وجود دارد',
      {
        confirmText: 'فعال‌سازی این تب',
        cancelText: 'لغو'
      }
    )

    if (result.isConfirmed) {
      await activateCurrentAdminTab()
      isLocked.value = false
    }
  } finally {
    dialogOpen = false
  }
}

const activate = () => showConflictDialog()

onMounted(() => {
  window.addEventListener('admin-tab-conflict', showConflictDialog)
  initializeAdminTabSession(() => authApi.claimTab())
    .catch(() => {
      isLocked.value = true
    })
})

onBeforeUnmount(() => {
  window.removeEventListener('admin-tab-conflict', showConflictDialog)
})
</script>
