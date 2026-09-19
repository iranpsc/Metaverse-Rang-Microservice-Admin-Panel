<template>
  <div :class="dense ? 'mb-2' : 'mb-8'">
    <h1 class="text-3xl font-bold text-[var(--theme-text-primary)] mb-2">
      {{ title }}
    </h1>
    <p
      v-if="subtitle"
      class="text-[var(--theme-text-secondary)]"
    >
      {{ subtitle }}
    </p>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true
  },
  subtitle: {
    type: String,
    default: ''
  },
  dense: {
    type: Boolean,
    default: false
  }
})
</script>
