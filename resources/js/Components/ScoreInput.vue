<template>
  <div class="flex items-center gap-3">
    <!-- Decrement Button -->
    <button
      type="button"
      :disabled="disabled || currentValue <= min"
      @click="step(-stepSize)"
      class="h-10 w-10 flex items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:border-indigo-300 hover:text-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm transition-all duration-200"
      :aria-label="`Decrease to ${Math.max(min, (Number(currentValue) - stepSize).toFixed(displayStep))}`"
    >
      −
    </button>

    <!-- Slider -->
    <input
      type="range"
      class="w-40 accent-indigo-600 disabled:opacity-50 cursor-pointer"
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
        class="w-24 text-center rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-slate-100 disabled:opacity-50 font-bold text-slate-700"
        :min="min"
        :max="max"
        :step="step"
        :disabled="disabled"
        :value="currentValue"
        @change="onNumberChange"
      />
      <div class="absolute inset-y-0 right-2 flex items-center text-slate-400 text-xs font-medium">pts</div>
    </div>

    <!-- Increment Button -->
    <button
      type="button"
      :disabled="disabled || currentValue >= max"
      @click="step(stepSize)"
      class="h-10 w-10 flex items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:border-indigo-300 hover:text-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm transition-all duration-200"
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
