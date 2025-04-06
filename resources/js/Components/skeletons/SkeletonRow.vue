<template>
  <div class="flex items-center space-x-4 w-full animate-pulse">
    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 shimmer"></div>
    <div class="flex-1 space-y-1">
      <div class="h-4 w-3/4 bg-gray-200 rounded shimmer"></div>
      <div class="h-3 w-1/2 bg-gray-200 rounded shimmer"></div>
    </div>
    <div class="h-4 w-16 bg-gray-200 rounded shimmer"></div>
    <div class="h-4 w-12 bg-gray-200 rounded shimmer"></div>
    <div class="h-5 w-20 bg-gray-200 rounded-full shimmer"></div>
    <div class="flex space-x-2">
      <div class="h-5 w-5 bg-gray-200 rounded shimmer"></div>
      <div class="h-5 w-5 bg-gray-200 rounded shimmer"></div>
    </div>
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

@keyframes shimmer {
  0% {
    background-position: -200% 0;
  }
  100% {
    background-position: 200% 0;
  }
}
</style> 