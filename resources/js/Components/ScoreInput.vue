<template>
  <div class="flex items-center gap-3">
    <!-- Decrement Button -->
    <button
      type="button"
      :disabled="disabled || currentValue <= min"
      @click="step(-stepSize)"
      class="h-10 w-10 flex items-center justify-center rounded-lg border border-gray-200 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
      :aria-label="`Decrease to ${Math.max(min, (Number(currentValue) - stepSize).toFixed(displayStep))}`"
    >
      −
    </button>

    <!-- Slider -->
    <input
      type="range"
      class="w-40 accent-amber-500 disabled:opacity-50"
      :min="min"
      :max="max"
      :step="step"
      :disabled="disabled"
      :value="currentValue"
      @input="onRangeInput"
    />

    <!-- Number Input with unit -->
    <div class="relative">
      <input
        type="number"
        class="w-24 text-center rounded-lg border border-gray-300 focus:border-amber-500 focus:ring-amber-500 disabled:bg-gray-100 disabled:opacity-50"
        :min="min"
        :max="max"
        :step="step"
        :disabled="disabled"
        :value="currentValue"
        @change="onNumberChange"
      />
      <div class="absolute inset-y-0 right-2 flex items-center text-gray-400 text-xs">pts</div>
    </div>

    <!-- Increment Button -->
    <button
      type="button"
      :disabled="disabled || currentValue >= max"
      @click="step(stepSize)"
      class="h-10 w-10 flex items-center justify-center rounded-lg border border-gray-200 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
      :aria-label="`Increase to ${Math.min(max, (Number(currentValue) + stepSize).toFixed(displayStep))}`"
    >
      +
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: [Number, String],
    default: null,
  },
  min: {
    type: Number,
    required: true,
  },
  max: {
    type: Number,
    required: true,
  },
  step: {
    type: Number,
    default: 1,
  },
  allowDecimals: {
    type: Boolean,
    default: false,
  },
  decimalPlaces: {
    type: Number,
    default: 1,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:modelValue', 'change'])

const displayStep = computed(() => (props.allowDecimals ? props.decimalPlaces : 0))
const stepSize = computed(() => (props.allowDecimals ? props.step : Math.max(1, Math.round(props.step))))

const currentValue = computed({
  get() {
    const val = Number(props.modelValue)
    if (Number.isNaN(val)) {
      return props.min
    }
    return clamp(roundTo(val))
  },
  set(v) {
    const num = Number(v)
    const next = clamp(roundTo(num))
    emit('update:modelValue', next)
    emit('change', next)
  },
})

function roundTo(value) {
  if (!props.allowDecimals) {
    return Math.round(value)
  }
  const p = Math.pow(10, props.decimalPlaces)
  return Math.round(value * p) / p
}

function clamp(value) {
  return Math.min(props.max, Math.max(props.min, value))
}

function step(delta) {
  currentValue.value = Number(currentValue.value) + Number(delta)
}

function onRangeInput(e) {
  currentValue.value = Number(e.target.value)
}

function onNumberChange(e) {
  currentValue.value = Number(e.target.value)
}
</script>

<style scoped>
/* No dark mode styles as requested */
</style>
