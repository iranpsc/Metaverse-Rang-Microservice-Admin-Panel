<template>
  <div class="p-6 space-y-6">
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-[var(--theme-text-primary)] mb-2">داشبورد</h1>
      <p class="text-[var(--theme-text-secondary)]">خلاصه آمار و اطلاعات سیستم</p>
    </div>

    <!-- Loading State -->
    <LoadingState v-if="loading" />

    <!-- Error State -->
    <ErrorState
      v-else-if="error"
      :message="error"
      variant="error"
    />

    <!-- Dashboard Content -->
    <div v-else-if="dashboardData" class="space-y-6">
      <!-- Users Row -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <DashboardCard
          type="cyan"
          :value="dashboardData.users.all"
          label="اعضای ثبت نام کرده"
          icon="icon-people"
        />
        <DashboardCard
          type="blue"
          :value="dashboardData.users.verified"
          label="اعضای تایید شده"
          icon="icon-check"
        />
        <DashboardCard
          type="orange"
          :value="dashboardData.users.verified_phone"
          label="اعضای احراز شده مرحله اول"
          icon="icon-phone"
        />
        <DashboardCard
          type="red"
          :value="dashboardData.users.kyc_verified"
          label="اعضای احراز شده مرحله ۲"
          icon="icon-shield"
        />
      </div>

      <!-- Features & Dynasties Row -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <DashboardCard
          type="cyan"
          :value="dashboardData.dynasties"
          label="سلسله های تاسیس شده"
          icon="icon-organization"
        />
        <DashboardCard
          type="blue"
          :value="dashboardData.features.all"
          label="کل املاک"
          icon="icon-home"
        />
        <DashboardCard
          type="blue"
          :value="dashboardData.features.sold"
          label="کل املاک فروخته شده"
          icon="icon-basket-loaded"
        />
        <DashboardCard
          type="orange"
          :value="dashboardData.referrals"
          label="کل ورودی با رفرال"
          icon="icon-user-follow"
        />
      </div>

      <!-- Financial Row -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <DashboardCard
          type="red"
          :value="dashboardData.referral_amount"
          label="کل پاداش های دریافتی"
          icon="icon-trophy"
          :is-currency="true"
        />
        <DashboardCard
          type="cyan"
          :value="dashboardData.deposited_rial_amount"
          label="مقدار ریال وارد شده"
          icon="icon-wallet"
          :is-currency="true"
        />
      </div>

      <!-- Sold Assets (Optional - if you want to show these) -->
      <div v-if="showSoldAssets" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <DashboardCard
          type="blue"
          :value="dashboardData.sold_assets.psc"
          label="دارایی فروخته شده PSC"
          icon="icon-diamond"
          :is-currency="true"
        />
        <DashboardCard
          type="red"
          :value="dashboardData.sold_assets.red"
          label="دارایی فروخته شده RED"
          icon="icon-fire"
          :is-currency="true"
        />
        <DashboardCard
          type="blue"
          :value="dashboardData.sold_assets.blue"
          label="دارایی فروخته شده BLUE"
          icon="icon-drop"
          :is-currency="true"
        />
        <DashboardCard
          type="orange"
          :value="dashboardData.sold_assets.yellow"
          label="دارایی فروخته شده YELLOW"
          icon="icon-energy"
          :is-currency="true"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import DashboardCard from '../components/DashboardCard.vue'
import apiClient from '../utils/api'
import { LoadingState, ErrorState } from '../components/ui'

const loading = ref(true)
const error = ref(null)
const dashboardData = ref(null)
const showSoldAssets = ref(false) // Toggle to show/hide sold assets section

const fetchDashboardData = async () => {
  try {
    loading.value = true
    error.value = null

    const response = await apiClient.get('/dashboard')

    if (response.data.success) {
      dashboardData.value = response.data.data
    } else {
      error.value = 'خطا در دریافت اطلاعات داشبورد'
    }
  } catch (err) {
    console.error('Dashboard fetch error:', err)
    error.value = err.response?.data?.message || 'خطا در بارگذاری اطلاعات'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchDashboardData()
})
</script>

<style scoped>
/* Additional styles if needed */
</style>

