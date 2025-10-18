<template>
  <div class="skeleton-provider">
    <slot v-if="!isLoading" />
    <slot v-else name="skeleton">
      <SkeletonContent :type="type" v-bind="skeletonProps" />
    </slot>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, defineEmits, watch } from 'vue'
import SkeletonContent from './SkeletonContent.vue'

const props = defineProps({
  /**
   * Loading state
   */
  loading: {
    type: Boolean,
    default: false
  },
  /**
   * Type of skeleton to display
   */
  type: {
    type: String,
    default: 'default'
  },
  /**
   * Minimum loading time in milliseconds
   */
  minLoadingTime: {
    type: Number,
    default: 800
  },
  /**
   * Props to pass to the skeleton component
   */
  skeletonProps: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['loaded'])

const isLoading = ref(props.loading)
let loadingStartTime = 0

// Watch for loading state changes
watch(() => props.loading, (newValue) => {
  if (newValue) {
    // Start loading
    isLoading.value = true
    loadingStartTime = Date.now()
  } else {
    // Calculate remaining time to satisfy minimum loading time
    const currentTime = Date.now()
    const elapsedTime = currentTime - loadingStartTime
    const remainingTime = Math.max(0, props.minLoadingTime - elapsedTime)
    
    // Only hide skeleton after minimum loading time has passed
    if (remainingTime > 0) {
      setTimeout(() => {
        isLoading.value = false
        emit('loaded')
      }, remainingTime)
    } else {
      isLoading.value = false
      emit('loaded')
    }
  }
})

onMounted(() => {
  if (props.loading) {
    loadingStartTime = Date.now()
  }
})
</script> 