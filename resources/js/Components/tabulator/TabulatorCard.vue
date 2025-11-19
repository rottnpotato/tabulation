<template>
  <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
    <div class="p-6">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
          <p v-if="subtitle" class="text-sm text-gray-500 mt-1">{{ subtitle }}</p>
        </div>
        <div v-if="icon" class="p-3 rounded-full" :class="iconBgClass">
          <component :is="icon" class="h-6 w-6" :class="iconClass" />
        </div>
      </div>
      
      <div v-if="value !== undefined" class="mt-4">
        <p class="text-3xl font-bold text-gray-900">{{ formatValue(value) }}</p>
        <p v-if="description" class="text-sm text-gray-500 mt-1">{{ description }}</p>
      </div>
      
      <div v-if="slots.content" class="mt-4">
        <slot name="content" />
      </div>
    </div>
    
    <div v-if="slots.footer" class="border-t border-gray-100 bg-gray-50 px-6 py-3">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, useSlots } from 'vue'

const slots = useSlots()

interface Props {
  title: string
  subtitle?: string
  value?: number | string
  description?: string
  icon?: any
  color?: 'blue' | 'green' | 'purple' | 'orange' | 'red' | 'gray'
}

const props = withDefaults(defineProps<Props>(), {
  color: 'blue'
})

const iconBgClass = computed(() => {
  const colorMap = {
    blue: 'bg-blue-100',
    green: 'bg-sky-100',
    purple: 'bg-indigo-100',
    orange: 'bg-blue-100',
    red: 'bg-slate-100',
    gray: 'bg-slate-100',
  }
  return colorMap[props.color]
})

const iconClass = computed(() => {
  const colorMap = {
    blue: 'text-blue-600',
    green: 'text-sky-600',
    purple: 'text-indigo-600',
    orange: 'text-blue-600',
    red: 'text-slate-600',
    gray: 'text-slate-600',
  }
  return colorMap[props.color]
})

const formatValue = (value: number | string) => {
  if (typeof value === 'number') {
    return value.toLocaleString()
  }
  return value
}
</script>
