<template>
  <span 
    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
    :class="$attrs.class || 'bg-gray-100 text-gray-800'"
  >
    <slot></slot>
  </span>
</template>

<script setup>
// This component accepts its styling through class prop
// Default is gray if no class is provided
</script>
