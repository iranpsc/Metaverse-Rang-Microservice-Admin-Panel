<template>
  <div class="app-root">
    <NavigationProgress />
    <router-view />
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import NavigationProgress from './NavigationProgress.vue'
import { useAuth } from '../composables/useAuth'

// Initialize auth state when app loads
const { init } = useAuth()

onMounted(() => {
  Promise.resolve(init()).catch(console.error)
})
</script>

