<template>
  <div class="relative" v-click-outside="closeDropdown">
    <button
      type="button"
      @click="toggleDropdown"
      :class="[
        'relative w-full cursor-pointer rounded-xl border-0 py-3 pl-4 pr-12 text-left transition-all duration-200',
        'bg-white/80 backdrop-blur-sm',
        'shadow-sm hover:shadow-md',
        'ring-1 ring-slate-200 hover:ring-teal-300',
        'focus:outline-none focus:ring-2 focus:ring-offset-2',
        focusRingColor,
        disabled ? 'opacity-50 cursor-not-allowed' : 'hover:bg-white'
      ]"
      :disabled="disabled"
      :aria-expanded="isOpen"
      :aria-haspopup="true"
    >
      <span class="flex items-center">
        <span 
          :class="[
            'block truncate font-medium',
            selectedOption ? 'text-slate-900' : 'text-slate-500'
          ]"
        >
          {{ selectedOption ? selectedOption.label : placeholder }}
        </span>
      </span>
      <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
        <ChevronUpDownIcon 
          :class="[
            'h-5 w-5 transition-transform duration-200',
            isOpen ? 'rotate-180 text-teal-500' : 'text-slate-400'
          ]"
          aria-hidden="true" 
        />
      </span>
    </button>

    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 translate-y-1 scale-95"
      enter-to-class="opacity-100 translate-y-0 scale-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 translate-y-0 scale-100"
      leave-to-class="opacity-0 translate-y-1 scale-95"
    >
      <div
        v-show="isOpen"
        :class="[
          'absolute left-0 top-full mt-2 origin-top rounded-xl',
          'bg-white/95 backdrop-blur-md border border-gray-200/60',
          'shadow-2xl shadow-gray-900/20 ring-1 ring-black ring-opacity-5',
          'min-w-[100%]',
          dropdownPosition
        ]"
        style="z-index: 99999;"
        role="listbox"
        aria-orientation="vertical"
      >
        <div class="max-h-64 overflow-auto rounded-xl py-2">
          <div
            v-for="option in options"
            :key="option.value"
            @click="selectOption(option)"
            :class="[
              'relative cursor-pointer select-none py-3 px-4 mx-2 rounded-lg transition-all duration-150',
              option.value === modelValue 
                ? 'bg-teal-600 text-white shadow-md shadow-teal-600/20' 
                : 'text-slate-700 hover:bg-slate-50 hover:text-teal-600'
            ]"
            role="option"
            :aria-selected="option.value === modelValue"
          >
            <div class="flex items-center">
              <span 
                :class="[
                  'block truncate font-medium',
                  option.value === modelValue ? 'text-white' : ''
                ]"
              >
                {{ option.label }}
              </span>
            </div>
            
            <!-- Selected checkmark -->
            <span 
              v-if="option.value === modelValue"
              class="absolute inset-y-0 right-0 flex items-center pr-4"
            >
              <CheckIcon class="h-5 w-5 text-white" aria-hidden="true" />
            </span>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { ChevronUpDownIcon, CheckIcon } from '@heroicons/vue/20/solid'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  options: {
    type: Array,
    required: true,
    validator: (options) => {
      return options.every(option => 
        typeof option === 'object' && 
        'value' in option && 
        'label' in option
      )
    }
  },
  placeholder: {
    type: String,
    default: 'Select an option'
  },
  disabled: {
    type: Boolean,
    default: false
  },
  variant: {
    type: String,
    default: 'teal',
    validator: (value) => ['indigo', 'blue', 'slate', 'emerald', 'orange', 'teal'].includes(value)
  },
  dropdownPosition: {
    type: String,
    default: 'left-0',
    validator: (value) => ['left-0', 'right-0'].includes(value)
  }
})

const emit = defineEmits(['update:modelValue', 'change'])

const isOpen = ref(false)

const selectedOption = computed(() => {
  return props.options.find(option => option.value === props.modelValue)
})

const focusRingColor = computed(() => {
  switch (props.variant) {
    case 'blue':
      return 'focus:ring-blue-500'
    case 'emerald':
      return 'focus:ring-emerald-500'
    case 'orange':
      return 'focus:ring-orange-500'
    case 'slate':
      return 'focus:ring-slate-500'
    case 'teal':
      return 'focus:ring-teal-500'
    default:
      return 'focus:ring-teal-500'
  }
})

const toggleDropdown = () => {
  if (!props.disabled) {
    isOpen.value = !isOpen.value
  }
}

const closeDropdown = () => {
  isOpen.value = false
}

const selectOption = (option) => {
  emit('update:modelValue', option.value)
  emit('change', option)
  closeDropdown()
}

// Close dropdown when clicking outside
const vClickOutside = {
  beforeMount(el, binding) {
    el.clickOutsideEvent = (event) => {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value()
      }
    }
    document.addEventListener('click', el.clickOutsideEvent)
  },
  unmounted(el) {
    document.removeEventListener('click', el.clickOutsideEvent)
  }
}
</script>

<style scoped>
/* Custom scrollbar for dropdown */
.overflow-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-auto::-webkit-scrollbar-track {
  background: transparent;
}

.overflow-auto::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.5);
  border-radius: 3px;
}

.overflow-auto::-webkit-scrollbar-thumb:hover {
  background: rgba(156, 163, 175, 0.7);
}
</style>
