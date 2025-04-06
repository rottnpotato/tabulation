<template>
  <Modal :show="show" @close="$emit('close')">
    <div class="p-6">
      <div class="flex items-center justify-center w-12 h-12 mx-auto rounded-full mb-4" :class="iconBgClass">
        <component :is="icon" class="h-6 w-6" :class="iconClass" />
      </div>
      <h3 class="text-lg font-medium text-center text-gray-900 mb-2">{{ title }}</h3>
      <p class="text-sm text-gray-500 text-center mb-4">
        <slot></slot>
      </p>
      <div class="flex justify-end space-x-3">
        <button
          @click="$emit('close')"
          class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition-colors duration-150"
        >
          {{ cancelText }}
        </button>
        <button
          @click="$emit('confirm')"
          class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white border border-transparent rounded-md shadow-sm transition-colors duration-150"
          :class="confirmButtonClass"
        >
          {{ confirmText }}
        </button>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { computed } from 'vue'
import Modal from '@/Components/Modal.vue'
import { 
  AlertTriangle,
  AlertCircle,
  CheckCircle,
  HelpCircle,
  Info
} from 'lucide-vue-next'

const props = defineProps({
  show: {
    type: Boolean,
    required: true
  },
  title: {
    type: String,
    default: 'Confirm Action'
  },
  confirmText: {
    type: String,
    default: 'Confirm'
  },
  cancelText: {
    type: String,
    default: 'Cancel'
  },
  type: {
    type: String,
    default: 'warning',
    validator: (value) => ['success', 'warning', 'danger', 'info', 'question'].includes(value)
  }
})

defineEmits(['close', 'confirm'])

// Computed properties for styling based on type
const icon = computed(() => {
  switch (props.type) {
    case 'success': return CheckCircle
    case 'warning': return AlertCircle
    case 'danger': return AlertTriangle
    case 'info': return Info
    case 'question': return HelpCircle
    default: return AlertTriangle
  }
})

const iconBgClass = computed(() => {
  switch (props.type) {
    case 'success': return 'bg-green-100'
    case 'warning': return 'bg-amber-100'
    case 'danger': return 'bg-red-100'
    case 'info': return 'bg-blue-100'
    case 'question': return 'bg-indigo-100'
    default: return 'bg-red-100'
  }
})

const iconClass = computed(() => {
  switch (props.type) {
    case 'success': return 'text-green-600'
    case 'warning': return 'text-amber-600'
    case 'danger': return 'text-red-600'
    case 'info': return 'text-blue-600'
    case 'question': return 'text-indigo-600'
    default: return 'text-red-600'
  }
})

const confirmButtonClass = computed(() => {
  switch (props.type) {
    case 'success': return 'bg-green-600 hover:bg-green-700 focus:ring-green-500'
    case 'warning': return 'bg-amber-600 hover:bg-amber-700 focus:ring-amber-500'
    case 'danger': return 'bg-red-600 hover:bg-red-700 focus:ring-red-500'
    case 'info': return 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500'
    case 'question': return 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500'
    default: return 'bg-red-600 hover:bg-red-700 focus:ring-red-500'
  }
})
</script> 