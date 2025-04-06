<template>
  <div class="bg-white overflow-hidden shadow-md rounded-xl border border-gray-100 hover:shadow-lg transition-shadow">
    <div class="p-6">
      <div class="flex items-center justify-between">
        <div class="h-6 w-32 bg-gray-200 rounded shimmer"></div>
        <div class="p-2 bg-gray-200 rounded-full shimmer h-9 w-9"></div>
      </div>
      <div class="mt-4">
        <div class="h-8 w-24 bg-gray-200 rounded shimmer"></div>
        <div class="flex items-center mt-2">
          <div class="h-4 w-4 bg-gray-200 rounded shimmer mr-1"></div>
          <div class="h-4 w-32 bg-gray-200 rounded shimmer"></div>
        </div>
      </div>
    </div>
    <div class="h-1 w-full bg-gray-200 shimmer"></div>
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