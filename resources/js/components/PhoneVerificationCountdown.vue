<template>
  <div
    v-if="isProduction && verified && formattedRemaining"
    class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium border border-[var(--theme-border)] bg-[var(--theme-bg-glass)] text-[var(--theme-text-secondary)]"
    title="زمان باقیمانده جلسه تایید موبایل"
  >
    <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
      />
    </svg>
    <span class="tabular-nums text-[var(--theme-text-primary)]">{{ formattedRemaining }}</span>
  </div>
</template>

<script setup>
import { usePhoneVerificationSession } from '../composables/usePhoneVerification'

const { isProduction, verified, formattedRemaining } = usePhoneVerificationSession()
</script>
