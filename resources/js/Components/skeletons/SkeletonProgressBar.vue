<template>
  <div>
    <div class="mb-2 flex justify-between items-center">
      <div class="h-4 w-32 bg-gray-200 rounded shimmer"></div>
      <div class="h-4 w-8 bg-gray-200 rounded shimmer"></div>
    </div>
    <div class="w-full bg-gray-200 rounded-full h-2 shimmer"></div>
  </div>
</template>

<script setup>
// Import any skeleton CSS if not globally available
import './skeleton.css'
</script>

<style scoped>
.shimmer {
  background-image: linear-gradient(
    90deg,
    rgba(255, 255, 255, 0) 0%,
    rgba(255, 255, 255, 0.5) 50%,
    rgba(255, 255, 255, 0) 100%
  );
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.shimmer-progress {
  background-image: linear-gradient(
    90deg,
    rgba(203, 213, 225, 0.7) 0%,
    rgba(148, 163, 184, 0.9) 50%,
    rgba(203, 213, 225, 0.7) 100%
  );
  background-size: 200% 100%;
  animation: shimmer-progress 2s infinite;
  width: 70%;
}

@keyframes shimmer {
  0% {
    background-position: -200% 0;
  }
  100% {
    background-position: 200% 0;
  }
}

@keyframes shimmer-progress {
  0% {
    background-position: -200% 0;
  }
  100% {
    background-position: 200% 0;
  }
}
</style> 