<template>
  <div class="p-6 space-y-6">
    <PageHeader
      title="برداشت ها"
      subtitle="مشاهده و مدیریت برداشت‌های کاربران"
    />

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
import { LoadingState, ErrorState, PageHeader } from '../../components/ui'
import { getApiErrorMessage, handleAuthListError } from '../../utils/apiErrors'

const loading = ref(true)
const error = ref(null)

const fetchWithdraws = async () => {
  try {
    loading.value = true
    error.value = null

    const response = await apiClient.get('/withdraws')

    if (response.data.success) {
      // Withdraw data is not yet implemented on the backend
    } else {
      error.value = 'خطا در دریافت اطلاعات برداشت‌ها'
    }
  } catch (err) {
    console.error('Withdraws fetch error:', err)

    if (handleAuthListError(err, { loading })) {
      return
    }

    error.value = getApiErrorMessage(err, 'خطا در بارگذاری اطلاعات')
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

