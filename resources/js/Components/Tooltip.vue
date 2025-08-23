<template>
  <div class="relative inline-block" @mouseenter="showTooltip" @mouseleave="hideTooltip">
    <!-- Slot for the element that triggers the tooltip -->
    <slot />
    
    <!-- Tooltip content -->
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 translate-y-1"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 translate-y-1"
    >
      <div
        v-show="isVisible"
        :class="[
          'absolute z-50 px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm transition-opacity duration-300',
          positionClasses
        ]"
        role="tooltip"
      >
        {{ text }}
        <!-- Tooltip arrow -->
        <div :class="arrowClasses"></div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  text: {
    type: String,
    required: true
  },
  position: {
    type: String,
    default: 'top',
    validator: (value) => ['top', 'bottom', 'left', 'right'].includes(value)
  },
  delay: {
    type: Number,
    default: 500
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

const isVisible = ref(false)
let timeoutId = null

const positionClasses = computed(() => {
  switch (props.position) {
    case 'top':
      return 'bottom-full left-1/2 transform -translate-x-1/2 mb-2'
    case 'bottom':
      return 'top-full left-1/2 transform -translate-x-1/2 mt-2'
    case 'left':
      return 'right-full top-1/2 transform -translate-y-1/2 mr-2'
    case 'right':
      return 'left-full top-1/2 transform -translate-y-1/2 ml-2'
    default:
      return 'bottom-full left-1/2 transform -translate-x-1/2 mb-2'
  }
})

const arrowClasses = computed(() => {
  switch (props.position) {
    case 'top':
      return 'absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900'
    case 'bottom':
      return 'absolute bottom-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-b-gray-900'
    case 'left':
      return 'absolute left-full top-1/2 transform -translate-y-1/2 border-4 border-transparent border-l-gray-900'
    case 'right':
      return 'absolute right-full top-1/2 transform -translate-y-1/2 border-4 border-transparent border-r-gray-900'
    default:
      return 'absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900'
  }
})

const showTooltip = () => {
  if (props.disabled) return
  
  clearTimeout(timeoutId)
  timeoutId = setTimeout(() => {
    isVisible.value = true
  }, props.delay)
}

const hideTooltip = () => {
  clearTimeout(timeoutId)
  isVisible.value = false
}
</script>
