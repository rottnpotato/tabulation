<template>
  <div 
    :class="[
      'skeleton-loader shimmer',
      `skeleton-${type}`,
      className,
      { 'rounded-full': circle, 'rounded': !circle }
    ]"
    :style="{
      width: width,
      height: height
    }"
  ></div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps({
  /**
   * Type of skeleton to display
   */
  type: {
    type: String,
    default: 'rect',
    validator: (value: string) => ['rect', 'text', 'title', 'avatar', 'button', 'badge'].includes(value)
  },
  /**
   * Width of the skeleton
   */
  width: {
    type: String,
    default: '100%'
  },
  /**
   * Height of the skeleton
   */
  height: {
    type: String,
    default: null
  },
  /**
   * Whether the skeleton should be a circle
   */
  circle: {
    type: Boolean,
    default: false
  },
  /**
   * Additional class names
   */
  className: {
    type: String,
    default: ''
  }
})

// Compute height based on type if not provided
const computedHeight = computed(() => {
  if (props.height) return props.height

  switch (props.type) {
    case 'text':
      return '1rem'
    case 'title':
      return '1.5rem'
    case 'avatar':
      return '2.5rem'
    case 'button':
      return '2rem'
    case 'badge':
      return '1.25rem'
    default:
      return '1rem'
  }
})
</script>

<style scoped>
.skeleton-loader {
  background-color: #e2e8f0;
  display: inline-block;
  position: relative;
  overflow: hidden;
  opacity: 0.7;
}

.skeleton-text {
  height: 1rem;
  margin-bottom: 0.5rem;
  border-radius: 0.25rem;
}

.skeleton-title {
  height: 1.5rem;
  margin-bottom: 1rem;
  border-radius: 0.25rem;
}

.skeleton-avatar {
  height: 2.5rem;
  width: 2.5rem;
  border-radius: 50%;
}

.skeleton-button {
  height: 2rem;
  border-radius: 0.375rem;
}

.skeleton-badge {
  height: 1.25rem;
  border-radius: 9999px;
}

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