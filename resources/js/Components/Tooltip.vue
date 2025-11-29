<template>
  <div ref="triggerRef" class="inline-block" @mouseenter="showTooltip" @mouseleave="hideTooltip">
    <!-- Slot for the element that triggers the tooltip -->
    <slot />
    
    <!-- Tooltip content - teleported to body for proper z-index stacking -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
      >
        <div
          v-show="isVisible"
          ref="tooltipRef"
          :style="tooltipStyles"
          class="fixed px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-lg whitespace-nowrap pointer-events-none"
          style="z-index: 99999;"
          role="tooltip"
        >
          {{ text }}
          <!-- Tooltip arrow -->
          <div :class="arrowClasses"></div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onUnmounted, nextTick } from 'vue'

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
const triggerRef = ref(null)
const tooltipRef = ref(null)
const tooltipPosition = ref({ top: 0, left: 0 })
let timeoutId = null

const tooltipStyles = computed(() => ({
  top: `${tooltipPosition.value.top}px`,
  left: `${tooltipPosition.value.left}px`,
}))

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

const calculatePosition = async () => {
  if (!triggerRef.value) return

  await nextTick()

  const triggerRect = triggerRef.value.getBoundingClientRect()
  const tooltipEl = tooltipRef.value
  
  // Get tooltip dimensions (estimate if not yet visible)
  let tooltipWidth = 0
  let tooltipHeight = 0
  
  if (tooltipEl) {
    tooltipWidth = tooltipEl.offsetWidth || 100
    tooltipHeight = tooltipEl.offsetHeight || 32
  } else {
    // Estimate based on text length
    tooltipWidth = Math.min(props.text.length * 8 + 24, 300)
    tooltipHeight = 32
  }

  const gap = 8 // Gap between trigger and tooltip
  let top = 0
  let left = 0

  switch (props.position) {
    case 'top':
      top = triggerRect.top - tooltipHeight - gap
      left = triggerRect.left + (triggerRect.width / 2) - (tooltipWidth / 2)
      break
    case 'bottom':
      top = triggerRect.bottom + gap
      left = triggerRect.left + (triggerRect.width / 2) - (tooltipWidth / 2)
      break
    case 'left':
      top = triggerRect.top + (triggerRect.height / 2) - (tooltipHeight / 2)
      left = triggerRect.left - tooltipWidth - gap
      break
    case 'right':
      top = triggerRect.top + (triggerRect.height / 2) - (tooltipHeight / 2)
      left = triggerRect.right + gap
      break
    default:
      top = triggerRect.top - tooltipHeight - gap
      left = triggerRect.left + (triggerRect.width / 2) - (tooltipWidth / 2)
  }

  // Ensure tooltip stays within viewport bounds
  const viewportWidth = window.innerWidth
  const viewportHeight = window.innerHeight
  const padding = 8

  // Horizontal bounds
  if (left < padding) {
    left = padding
  } else if (left + tooltipWidth > viewportWidth - padding) {
    left = viewportWidth - tooltipWidth - padding
  }

  // Vertical bounds
  if (top < padding) {
    top = padding
  } else if (top + tooltipHeight > viewportHeight - padding) {
    top = viewportHeight - tooltipHeight - padding
  }

  tooltipPosition.value = { top, left }
}

const showTooltip = () => {
  if (props.disabled) return
  
  clearTimeout(timeoutId)
  timeoutId = setTimeout(async () => {
    await calculatePosition()
    isVisible.value = true
    // Recalculate after render to get accurate dimensions
    await nextTick()
    await calculatePosition()
  }, props.delay)
}

const hideTooltip = () => {
  clearTimeout(timeoutId)
  isVisible.value = false
}

onUnmounted(() => {
  clearTimeout(timeoutId)
})
</script>
