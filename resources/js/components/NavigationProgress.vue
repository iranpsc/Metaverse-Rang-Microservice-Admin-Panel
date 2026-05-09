<template>
  <transition name="fade">
    <div
      v-if="isVisible"
      class="navigation-progress fixed top-0 left-0 right-0 z-[9999]"
    >
      <div class="progress-bar">
        <div
          class="progress-fill"
          :style="{ width: `${progress}%` }"
        />
      </div>
    </div>
  </transition>
</template>

<script setup>
import { useNavigationProgress } from '../composables/useNavigationProgress'

const { progress, isVisible } = useNavigationProgress()
</script>

<style scoped>
.navigation-progress {
  height: 3px;
  overflow: hidden;
  pointer-events: none;
}

.progress-bar {
  width: 100%;
  height: 100%;
  background: transparent;
  position: relative;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(
    90deg,
    #7c3aed 0%,
    #a78bfa 25%,
    #06b6d4 50%,
    #22d3ee 75%,
    #7c3aed 100%
  );
  background-size: 200% 100%;
  transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  animation: shimmer 2s infinite;
  box-shadow: 0 0 10px rgba(124, 58, 237, 0.6),
    0 0 20px rgba(6, 182, 212, 0.4);
}

@keyframes shimmer {
  0% {
    background-position: -200% 0;
  }
  100% {
    background-position: 200% 0;
  }
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
