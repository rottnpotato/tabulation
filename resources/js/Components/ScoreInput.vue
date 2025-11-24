<template>
  <div class="w-full" :class="{ 'flex items-center gap-3': showSlider, 'flex items-center justify-between gap-4': !showSlider }">
    <!-- Decrement Button -->
    <button
      type="button"
      :disabled="disabled || currentValue <= min"
      @click="step(-stepSize)"
      class="flex-none flex items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:border-teal-300 hover:text-teal-600 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm transition-all duration-200 active:scale-95"
      :class="showSlider ? 'h-10 w-10' : 'h-12 w-12'"
      :aria-label="`Decrease to ${Math.max(min, (Number(currentValue) - stepSize).toFixed(displayStep))}`"
    >
      <span class="font-bold" :class="showSlider ? 'text-lg' : 'text-xl'">−</span>
    </button>

    <!-- Slider Mode -->
    <template v-if="showSlider">
      <input
        type="range"
        class="flex-1 min-w-0 accent-teal-600 disabled:opacity-50 cursor-pointer h-2 bg-slate-200 rounded-lg appearance-none"
        :min="min"
        :max="max"
        :step="step"
        :disabled="disabled"
        :value="currentValue"
        @input="onRangeInput"
      />
      <div class="relative flex-none">
        <input
          type="number"
          inputmode="decimal"
          class="w-20 text-center rounded-xl border border-slate-300 focus:border-teal-500 focus:ring-teal-500 disabled:bg-slate-100 disabled:opacity-50 font-bold text-slate-700"
          :min="min"
          :max="max"
          :step="step"
          :disabled="disabled"
          :value="currentValue"
          @input="onNumberInput"
          @change="onNumberChange"
          @keypress="validateNumberInput"
        />
      </div>
    </template>

    <!-- Numeric Mode (No Slider) -->
    <template v-else>
      <div class="flex-1 relative group">
        <input
          type="number"
          inputmode="decimal"
          class="w-full text-center rounded-xl border-2 border-slate-100 bg-slate-50 focus:bg-white focus:border-teal-500 focus:ring-0 disabled:bg-slate-100 disabled:opacity-50 font-black text-slate-800 text-2xl h-12 transition-all"
          :min="min"
          :max="max"
          :step="step"
          :disabled="disabled"
          :value="currentValue"
          @input="onNumberInput"
          @change="onNumberChange"
          @keypress="validateNumberInput"
        />
        <div class="absolute bottom-0 left-0 h-1 bg-teal-500 transition-all duration-300 rounded-b-md opacity-20 group-focus-within:opacity-100"
             :style="{ width: `${((currentValue - min) / (max - min)) * 100}%` }">
        </div>
      </div>
    </template>

    <!-- Increment Button -->
    <button
      type="button"
      :disabled="disabled || currentValue >= max"
      @click="step(stepSize)"
      class="flex-none flex items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:border-teal-300 hover:text-teal-600 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm transition-all duration-200 active:scale-95"
      :class="showSlider ? 'h-10 w-10' : 'h-12 w-12'"
      :aria-label="`Increase to ${Math.min(max, (Number(currentValue) + stepSize).toFixed(displayStep))}`"
    >
      <span class="font-bold" :class="showSlider ? 'text-lg' : 'text-xl'">+</span>
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
  showSlider: {
    type: Boolean,
    default: true,
  }
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

function onNumberInput(e) {
  // Remove any non-numeric characters except decimal point and minus
  let value = e.target.value
  
  // Allow only numbers, decimal point, and minus sign
  value = value.replace(/[^0-9.-]/g, '')
  
  // Ensure only one decimal point
  const parts = value.split('.')
  if (parts.length > 2) {
    value = parts[0] + '.' + parts.slice(1).join('')
  }
  
  // Ensure only one minus sign at the start
  if (value.indexOf('-') > 0) {
    value = value.replace(/-/g, '')
  }
  
  e.target.value = value
}

function onNumberChange(e) {
  const value = e.target.value
  
  // If empty, set to min value
  if (value === '' || value === null || value === undefined) {
    currentValue.value = props.min
    e.target.value = props.min
    return
  }
  
  currentValue.value = Number(value)
}

function validateNumberInput(e) {
  // Prevent non-numeric characters from being typed
  const char = String.fromCharCode(e.which || e.keyCode)
  const currentValue = e.target.value
  
  // Allow: numbers, decimal point (if decimals allowed), minus sign, backspace, delete, arrow keys
  const isNumber = /[0-9]/.test(char)
  const isDecimal = char === '.' && props.allowDecimals && !currentValue.includes('.')
  const isMinus = char === '-' && currentValue.length === 0
  const isControl = e.keyCode === 8 || e.keyCode === 46 || e.keyCode === 37 || e.keyCode === 39
  
  if (!isNumber && !isDecimal && !isMinus && !isControl) {
    e.preventDefault()
    return false
  }
}
</script>

<style scoped>
/* Remove spinner buttons from number input */
input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
  -webkit-appearance: none; 
  margin: 0; 
}
input[type=number] {
  -moz-appearance: textfield;
}
</style>
