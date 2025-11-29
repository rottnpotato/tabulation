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
          class="w-20 text-center rounded-xl border focus:ring-teal-500 disabled:bg-slate-100 disabled:opacity-50 font-bold transition-colors"
          :class="isOutOfRange && validateRange ? 'border-red-400 focus:border-red-500 text-red-600 bg-red-50' : 'border-slate-300 focus:border-teal-500 text-slate-700'"
          :min="min"
          :max="max"
          :step="step"
          :disabled="disabled"
          :value="currentValue"
          @input="onNumberInput"
          @change="onNumberChange"
          @blur="onNumberBlur"
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
          class="w-full text-center rounded-xl border-2 focus:ring-0 disabled:bg-slate-100 disabled:opacity-50 font-black text-2xl h-12 transition-all"
          :class="isOutOfRange && validateRange ? 'border-red-300 bg-red-50 focus:bg-red-50 focus:border-red-500 text-red-600' : 'border-slate-100 bg-slate-50 focus:bg-white focus:border-teal-500 text-slate-800'"
          :min="min"
          :max="max"
          :step="step"
          :disabled="disabled"
          :value="currentValue"
          @input="onNumberInput"
          @change="onNumberChange"
          @blur="onNumberBlur"
          @keypress="validateNumberInput"
        />
        <div class="absolute bottom-0 left-0 h-1 transition-all duration-300 rounded-b-md opacity-20 group-focus-within:opacity-100"
             :class="isOutOfRange && validateRange ? 'bg-red-500' : 'bg-teal-500'"
             :style="{ width: `${Math.min(100, Math.max(0, ((currentValue - min) / (max - min)) * 100))}%` }">
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
import { computed, ref } from 'vue'

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
  },
  validateRange: {
    type: Boolean,
    default: true,
  }
})

const emit = defineEmits(['update:modelValue', 'change'])

const displayStep = computed(() => (props.allowDecimals ? props.decimalPlaces : 0))
const stepSize = computed(() => (props.allowDecimals ? props.step : Math.max(1, Math.round(props.step))))

// Track if there's a validation error for styling
const hasValidationError = ref(false)

const currentValue = computed({
  get() {
    const val = Number(props.modelValue)
    if (Number.isNaN(val)) {
      return props.min
    }
    // Only clamp for display if validateRange is false
    if (!props.validateRange) {
      return clamp(roundTo(val))
    }
    return roundTo(val)
  },
  set(v) {
    const num = Number(v)
    const rounded = roundTo(num)
    
    // If validateRange is true, emit the raw value for parent validation
    // If validateRange is false, clamp the value as before
    if (props.validateRange) {
      emit('update:modelValue', rounded)
      emit('change', rounded)
    } else {
      const clamped = clamp(rounded)
      emit('update:modelValue', clamped)
      emit('change', clamped)
    }
  },
})

// Check if current value is out of range (for visual feedback)
const isOutOfRange = computed(() => {
  const val = Number(props.modelValue)
  if (Number.isNaN(val)) return false
  return val < props.min || val > props.max
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
  const newValue = Number(currentValue.value) + Number(delta)
  // For step buttons, always clamp to prevent going out of bounds
  currentValue.value = clamp(newValue)
}

function onRangeInput(e) {
  // Slider always stays in range
  currentValue.value = clamp(Number(e.target.value))
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
  
  // When validateRange is false, immediately clamp values
  if (!props.validateRange) {
    const numValue = Number(value)
    if (!Number.isNaN(numValue) && value !== '' && value !== '-') {
      if (numValue > props.max) {
        currentValue.value = props.max
        e.target.value = props.max
      }
    }
  }
}

function onNumberChange(e) {
  const value = e.target.value
  
  // If empty, set to min value
  if (value === '' || value === null || value === undefined) {
    currentValue.value = props.min
    e.target.value = props.min
    return
  }
  
  const numValue = Number(value)
  
  if (Number.isNaN(numValue)) {
    currentValue.value = props.min
    e.target.value = props.min
    return
  }
  
  // When validateRange is true, emit the raw value for parent validation
  if (props.validateRange) {
    currentValue.value = numValue
  } else {
    // When validateRange is false, clamp the value
    if (numValue < props.min) {
      currentValue.value = props.min
      e.target.value = props.min
      return
    }
    
    if (numValue > props.max) {
      currentValue.value = props.max
      e.target.value = props.max
      return
    }
    
    currentValue.value = numValue
  }
}

function onNumberBlur(e) {
  const value = e.target.value
  
  // If empty, set to min value
  if (value === '' || value === null || value === undefined) {
    currentValue.value = props.min
    e.target.value = props.min
    return
  }
  
  const numValue = Number(value)
  
  // Ensure value is valid on blur
  if (Number.isNaN(numValue)) {
    currentValue.value = props.min
    e.target.value = props.min
    return
  }
  
  // When validateRange is true, emit raw value and let parent validate
  if (props.validateRange) {
    currentValue.value = numValue
  } else {
    // When validateRange is false, clamp and update if out of bounds
    const clampedValue = clamp(roundTo(numValue))
    if (clampedValue !== numValue) {
      currentValue.value = clampedValue
      e.target.value = clampedValue
    }
  }
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
