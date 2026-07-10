<template>
  <div class="min-h-[60vh] flex items-center justify-center p-6" dir="rtl">
    <div class="max-w-md text-center space-y-4">
      <div class="text-6xl font-bold text-primary-400">403</div>
      <h1 class="text-2xl font-bold text-[var(--theme-text-primary)]">دسترسی غیرمجاز</h1>
      <p class="text-[var(--theme-text-secondary)]">
        شما مجوز دسترسی به این بخش را ندارید.
      </p>
      <router-link
        to="/"
        class="inline-flex items-center justify-center px-5 py-2.5 rounded-full bg-gradient-to-r from-[#7C3AED] to-[#06B6D4] text-white font-medium"
      >
        بازگشت به داشبورد
      </router-link>
    </div>
  </div>
</template>

<script setup>
</script>
