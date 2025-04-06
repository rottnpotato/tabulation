<template>
  <div class="skeleton-content" :class="{ 'animate-pulse': animate }">
    <template v-if="type === 'card'">
      <div class="bg-white overflow-hidden shadow-md rounded-xl border border-gray-100 p-6">
        <SkeletonLoader type="title" width="60%" class="mb-4" />
        <div class="space-y-3">
          <SkeletonLoader type="text" width="100%" v-for="i in lines" :key="i" />
        </div>
        <div class="flex justify-between mt-4" v-if="showFooter">
          <SkeletonLoader type="button" width="30%" />
          <SkeletonLoader type="badge" width="15%" />
        </div>
      </div>
    </template>

    <template v-else-if="type === 'list'">
      <div class="space-y-4">
        <div v-for="i in items" :key="i" class="flex items-center space-x-4 bg-white p-4 rounded-lg border border-gray-100 shadow-sm">
          <SkeletonLoader v-if="showAvatar" type="avatar" />
          <div class="flex-1 space-y-2">
            <SkeletonLoader type="title" width="60%" />
            <SkeletonLoader type="text" width="90%" v-for="j in Math.min(i % 3 + 1, 2)" :key="j" />
          </div>
          <SkeletonLoader v-if="showAction" type="button" width="20%" />
        </div>
      </div>
    </template>

    <template v-else-if="type === 'table'">
      <div class="overflow-hidden bg-white rounded-lg border border-gray-100 shadow-sm">
        <div class="bg-gray-50 p-4 flex border-b border-gray-100">
          <SkeletonLoader v-for="i in columns" :key="i" type="text" 
            :width="`${Math.floor(100 / columns - 2)}%`" 
            class="mx-1" />
        </div>
        <div v-for="i in rows" :key="i" class="p-4 flex border-b border-gray-100">
          <SkeletonLoader v-for="j in columns" :key="j" type="text" 
            :width="`${Math.floor(100 / columns - 2)}%`" 
            class="mx-1" />
        </div>
      </div>
    </template>

    <template v-else-if="type === 'grid'">
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <div v-for="i in items" :key="i" class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
          <SkeletonLoader v-if="showAvatar" type="avatar" circle class="mx-auto mb-4" />
          <SkeletonLoader type="title" width="70%" class="mx-auto mb-3" />
          <div class="space-y-2">
            <SkeletonLoader type="text" width="100%" v-for="j in Math.min(i % 3 + 1, 3)" :key="j" />
          </div>
          <div class="mt-4" v-if="showFooter">
            <SkeletonLoader type="button" width="100%" />
          </div>
        </div>
      </div>
    </template>

    <template v-else>
      <div class="space-y-4">
        <SkeletonLoader type="title" width="70%" />
        <div class="space-y-2">
          <SkeletonLoader type="text" width="100%" v-for="i in lines" :key="i" />
        </div>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import SkeletonLoader from './SkeletonLoader.vue'

defineProps({
  /**
   * Type of content to display
   */
  type: {
    type: String,
    default: 'default',
    validator: (value: string) => ['default', 'card', 'list', 'table', 'grid'].includes(value)
  },
  /**
   * Number of lines for text skeletons
   */
  lines: {
    type: Number,
    default: 3
  },
  /**
   * Number of items for list or grid skeletons
   */
  items: {
    type: Number,
    default: 4
  },
  /**
   * Number of columns for table skeletons
   */
  columns: {
    type: Number,
    default: 4
  },
  /**
   * Number of rows for table skeletons
   */
  rows: {
    type: Number,
    default: 3
  },
  /**
   * Whether to show avatar in list or grid items
   */
  showAvatar: {
    type: Boolean,
    default: true
  },
  /**
   * Whether to show action buttons
   */
  showAction: {
    type: Boolean,
    default: true
  },
  /**
   * Whether to show footer in card or grid items
   */
  showFooter: {
    type: Boolean,
    default: true
  },
  /**
   * Whether to animate the skeleton
   */
  animate: {
    type: Boolean,
    default: true
  }
})
</script>

<style scoped>
.skeleton-content {
  width: 100%;
}
</style> 