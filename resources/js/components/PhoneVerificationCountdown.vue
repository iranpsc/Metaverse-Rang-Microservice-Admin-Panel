<template>
  <div
    v-if="isProduction && verified && formattedRemaining"
    class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium border border-[var(--theme-border)] bg-[var(--theme-bg-glass)] text-[var(--theme-text-secondary)]"
  >
    <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
        d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"
      />
    </svg>
    <span class="tabular-nums text-[var(--theme-text-primary)]">{{ formattedRemaining }}</span>
  </div>
</template>

<script setup>
import { usePhoneVerificationSession } from '../composables/usePhoneVerification'

const { isProduction, verified, formattedRemaining } = usePhoneVerificationSession()
</script>
