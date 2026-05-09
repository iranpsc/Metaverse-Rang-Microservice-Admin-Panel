<template>
  <div class="p-6 space-y-6">
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-[var(--theme-text-primary)] mb-2">دسترسی ها</h1>
      <p class="text-[var(--theme-text-secondary)]">مدیریت دسترسی‌های سلسله خانوادگی</p>
    </div>

    <!-- Loading State -->
    <LoadingState v-if="loading" />

    <!-- Error State -->
    <ErrorState
      v-else-if="error"
      :message="error"
      variant="error"
    />

    <!-- Permissions Table -->
    <div v-else class="rounded-xl bg-[var(--theme-bg-elevated)] border border-[var(--theme-border)] overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <tbody class="divide-y divide-[var(--theme-border)]">
            <tr class="hover:bg-[var(--theme-bg-glass)] transition-colors">
              <td class="px-6 py-4">
                <Checkbox
                  v-model="permissions.BFR"
                  label="قابیلیت خرید از فروشگاه متارنگ"
                />
              </td>
              <td class="px-6 py-4">
                <Checkbox
                  v-model="permissions.PIUP"
                  label="قابلیت شرکت در پروژه های اتحادی"
                />
              </td>
            </tr>
            <tr class="hover:bg-[var(--theme-bg-glass)] transition-colors">
              <td class="px-6 py-4">
                <Checkbox
                  v-model="permissions.SF"
                  label="قابلیت فروش املاک و مسغلات در متارنگ"
                />
              </td>
              <td class="px-6 py-4">
                <Checkbox
                  v-model="permissions.PITC"
                  label="قابلیت شرکت در چالش ها"
                />
              </td>
            </tr>
            <tr class="hover:bg-[var(--theme-bg-glass)] transition-colors">
              <td class="px-6 py-4">
                <Checkbox
                  v-model="permissions.W"
                  label="خارج کردن سرمایه از متارنگ"
                />
              </td>
              <td class="px-6 py-4">
                <Checkbox
                  v-model="permissions.PIC"
                  label="قابلیت شرکت در مسابقات"
                />
              </td>
            </tr>
            <tr class="hover:bg-[var(--theme-bg-glass)] transition-colors">
              <td class="px-6 py-4">
                <Checkbox
                  v-model="permissions.JU"
                  label="قابلیت ورود به اتحاد ها"
                />
              </td>
              <td class="px-6 py-4">
                <Checkbox
                  v-model="permissions.ESOO"
                  label="قابلیت تاسیس فروشگاه یا دفتر کار"
                />
              </td>
            </tr>
            <tr class="hover:bg-[var(--theme-bg-glass)] transition-colors">
              <td class="px-6 py-4">
                <Checkbox
                  v-model="permissions.DM"
                  label="قابیلت مدیریت سلسله"
                />
              </td>
              <td class="px-6 py-4">
                <Checkbox
                  v-model="permissions.COTB"
                  label="قابیلت هم کاری در ساخت بنا"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Save Button -->
    <div class="flex justify-end">
      <Button variant="primary" :loading="saving" @click="handleUpdate">
        ثبت
      </Button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import apiClient from '../../utils/api'
import { Button, Checkbox, LoadingState, ErrorState } from '../../components/ui'

const loading = ref(true)
const error = ref(null)
const saving = ref(false)

const permissions = ref({
  BFR: 0,
  SF: 0,
  W: 0,
  JU: 0,
  DM: 0,
  PIUP: 0,
  PITC: 0,
  PIC: 0,
  ESOO: 0,
  COTB: 0
})

const fetchPermissions = async () => {
  try {
    loading.value = true
    error.value = null

    const response = await apiClient.get('/dynasty/permissions')

    if (response.data.success) {
      const data = response.data.data
      if (data) {
        permissions.value = {
          BFR: Boolean(data.BFR),
          SF: Boolean(data.SF),
          W: Boolean(data.W),
          JU: Boolean(data.JU),
          DM: Boolean(data.DM),
          PIUP: Boolean(data.PIUP),
          PITC: Boolean(data.PITC),
          PIC: Boolean(data.PIC),
          ESOO: Boolean(data.ESOO),
          COTB: Boolean(data.COTB)
        }
      }
    } else {
      error.value = 'خطا در دریافت اطلاعات دسترسی‌ها'
    }
  } catch (err) {
    console.error('Fetch permissions error:', err)

    if (err.response && (err.response.status === 401 || err.response.status === 403)) {
      loading.value = false
      return
    }

    error.value = err.response?.data?.message || 'خطا در بارگذاری اطلاعات'
  } finally {
    loading.value = false
  }
}

const handleUpdate = async () => {
  try {
    saving.value = true
    error.value = null

    const response = await apiClient.put('/dynasty/permissions', {
      BFR: permissions.value.BFR ? 1 : 0,
      SF: permissions.value.SF ? 1 : 0,
      W: permissions.value.W ? 1 : 0,
      JU: permissions.value.JU ? 1 : 0,
      DM: permissions.value.DM ? 1 : 0,
      PIUP: permissions.value.PIUP ? 1 : 0,
      PITC: permissions.value.PITC ? 1 : 0,
      PIC: permissions.value.PIC ? 1 : 0,
      ESOO: permissions.value.ESOO ? 1 : 0,
      COTB: permissions.value.COTB ? 1 : 0
    })

    if (response.data.success) {
      if (window.showToast) {
        window.showToast('اطلاعات با موفقیت ثبت شد', 'success')
      }
    } else {
      error.value = response.data.message || 'خطا در ثبت اطلاعات'
    }
  } catch (err) {
    console.error('Update permissions error:', err)
    error.value = err.response?.data?.message || 'خطا در ثبت اطلاعات'
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  fetchPermissions()
})
</script>

<style scoped>
/* Additional styles if needed */
</style>

