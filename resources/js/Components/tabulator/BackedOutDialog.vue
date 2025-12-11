<template>
  <TransitionRoot appear :show="show" as="template">
    <Dialog as="div" @close="$emit('close')" class="relative z-50">
      <TransitionChild
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
          <TransitionChild
            enter="ease-out duration-300"
            enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95"
          >
            <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white shadow-xl transition-all">
              <!-- Header -->
              <div class="bg-gradient-to-r from-red-500 to-red-600 p-6 text-white">
                <div class="flex items-center gap-3">
                  <div class="p-2 bg-white/20 rounded-lg">
                    <UserX class="h-6 w-6" />
                  </div>
                  <div>
                    <DialogTitle as="h3" class="text-xl font-bold leading-6">
                      Mark as Backed Out
                    </DialogTitle>
                    <p class="mt-1 text-red-100 text-sm">
                      Contestant #{{ contestant?.number }} - {{ contestant?.name }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Content -->
              <div class="p-6">
                <!-- Warning Message -->
                <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-xl">
                  <div class="flex items-start gap-3">
                    <AlertTriangle class="h-5 w-5 text-amber-500 flex-shrink-0 mt-0.5" />
                    <div class="text-sm text-amber-800">
                      <p class="font-medium mb-1">Important Notice</p>
                      <ul class="list-disc list-inside space-y-1 text-amber-700">
                        <li>Judges will be notified immediately</li>
                        <li>Scoring will be disabled for this contestant</li>
                        <li>This action can be undone later</li>
                      </ul>
                    </div>
                  </div>
                </div>

                <!-- Reason Input -->
                <div class="mb-6">
                  <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                    Reason for backing out <span class="text-red-500">*</span>
                  </label>
                  <textarea
                    id="reason"
                    v-model="reason"
                    rows="3"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all resize-none"
                    placeholder="Enter the reason why this contestant is backing out..."
                    :class="{ 'border-red-300 bg-red-50': error }"
                  ></textarea>
                  <p v-if="error" class="mt-2 text-sm text-red-600 flex items-center gap-1">
                    <AlertCircle class="h-4 w-4" />
                    {{ error }}
                  </p>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3">
                  <button
                    @click="$emit('close')"
                    :disabled="isLoading"
                    class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors disabled:opacity-50"
                  >
                    Cancel
                  </button>
                  <button
                    @click="handleConfirm"
                    :disabled="isLoading || !reason.trim()"
                    class="px-6 py-2.5 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                  >
                    <Loader2 v-if="isLoading" class="h-4 w-4 animate-spin" />
                    <UserX v-else class="h-4 w-4" />
                    <span>{{ isLoading ? 'Processing...' : 'Confirm Back Out' }}</span>
                  </button>
                </div>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue'
import { UserX, AlertTriangle, AlertCircle, Loader2 } from 'lucide-vue-next'

interface Contestant {
  id: number
  number: number
  name: string
}

interface Props {
  show: boolean
  contestant: Contestant | null
  isLoading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  isLoading: false
})

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'confirm', reason: string): void
}>()

const reason = ref('')
const error = ref('')

// Reset form when dialog opens/closes
watch(() => props.show, (newValue) => {
  if (newValue) {
    reason.value = ''
    error.value = ''
  }
})

const handleConfirm = () => {
  if (!reason.value.trim()) {
    error.value = 'Please provide a reason for backing out'
    return
  }
  
  if (reason.value.trim().length < 5) {
    error.value = 'Reason must be at least 5 characters'
    return
  }
  
  error.value = ''
  emit('confirm', reason.value.trim())
}
</script>
