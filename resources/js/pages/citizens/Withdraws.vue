<template>
  <div class="p-6 space-y-6">
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-[var(--theme-text-primary)] mb-2">برداشت ها</h1>
      <p class="text-[var(--theme-text-secondary)]">مشاهده و مدیریت برداشت‌های کاربران</p>
    </div>

    <!-- Loading State -->
    <LoadingState v-if="loading" />

    <!-- Error State -->
    <ErrorState
      v-else-if="error"
      :message="error"
      variant="error"
    />

    <!-- Empty State (since withdraws is currently empty) -->
    <div v-else class="text-center py-12">
      <p class="text-[var(--theme-text-secondary)]">در حال حاضر اطلاعاتی برای نمایش وجود ندارد.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import apiClient from '../../utils/api'
import { LoadingState, ErrorState } from '../../components/ui'

const loading = ref(true)
const error = ref(null)

const fetchWithdraws = async () => {
  try {
    loading.value = true
    error.value = null

    const response = await apiClient.get('/withdraws')

    if (response.data.success) {
      // Currently withdraws data is empty as per Livewire component
      // This can be implemented when withdraw functionality is added
    } else {
      error.value = 'خطا در دریافت اطلاعات برداشت‌ها'
    }
  } catch (err) {
    console.error('Withdraws fetch error:', err)

    // If 401/403, don't set error message - axios interceptor will handle redirect
    if (err.response && (err.response.status === 401 || err.response.status === 403)) {
      loading.value = false
      return
    }

    error.value = err.response?.data?.message || 'خطا در بارگذاری اطلاعات'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchWithdraws()
})
</script>

<style scoped>
/* Additional styles if needed */
</style>

