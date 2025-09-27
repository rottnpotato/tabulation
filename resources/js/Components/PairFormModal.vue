<template>
  <TransitionRoot appear :show="show" as="template">
    <Dialog as="div" @close="onClose" class="relative z-40">
      <TransitionChild enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
          <TransitionChild enter="ease-out duration-300" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="ease-in duration-200" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
            <DialogPanel class="w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white shadow-xl transition-all">
              <div class="relative bg-gradient-to-r from-emerald-600 to-teal-500 p-6 text-white">
                <DialogTitle as="h3" class="text-2xl font-bold leading-6">Create Pair</DialogTitle>
                <p class="mt-2 text-emerald-100">Select exactly two contestants to form a pair entry for this pageant.</p>
                <!-- Pageant Type Context -->
                <div v-if="pageant && pageant.contestant_type" class="mt-3 p-3 bg-emerald-500/30 rounded-lg border border-emerald-400/30">
                  <p class="text-sm text-emerald-100 font-medium mb-1">{{ getPageantTypeTitle() }}</p>
                  <p class="text-xs text-emerald-200">{{ getPageantTypeDescription() }}</p>
                </div>
                <button @click="onClose" class="absolute top-4 right-4 text-white/90 hover:text-white">
                  <XCircle class="h-6 w-6" />
                </button>
              </div>

              <form @submit.prevent="handleSubmit" class="p-6 space-y-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Pair Number <span class="text-red-500">*</span></label>
                  <input v-model="form.number" type="number" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-200" required />
                  <p v-if="errors.number" class="text-sm text-red-600 mt-1">{{ errors.number }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Pair Name (optional)</label>
                  <input v-model="form.name" type="text" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-200" placeholder="e.g. Alice & Bob" />
                  <p class="text-xs text-gray-500 mt-1">If left blank, the pair name will be auto-generated from the selected members.</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Select Members <span class="text-red-500">*</span></label>
                  
                  <!-- Warning when not enough available contestants -->
                  <div v-if="availableContestants.filter(c => !c.disabled).length < 2" class="mb-3 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                    <div class="flex items-start">
                      <div class="text-amber-600 font-medium text-sm">Not enough individual contestants</div>
                    </div>
                    <p class="text-xs text-amber-700 mt-1">
                      You need at least 2 individual contestants to create a pair. 
                      {{ availableContestants.filter(c => !c.disabled).length }} available, {{ 2 - availableContestants.filter(c => !c.disabled).length }} more needed.
                      <span class="block mt-1">Add more individual contestants first, then return to create pairs.</span>
                    </p>
                  </div>
                  
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label v-for="c in availableContestants" :key="c.id" class="flex items-center gap-3 p-3 border rounded-lg hover:bg-gray-50 cursor-pointer" :class="{ 'opacity-50 cursor-not-allowed bg-gray-50': c.disabled }">
                      <input type="checkbox" :value="c.id" v-model="form.member_ids" class="rounded text-emerald-600 focus:ring-emerald-500" :disabled="c.disabled" />
                      <img :src="c.photo || '/images/placeholders/placeholder-contestant.jpg'" class="h-10 w-10 rounded object-cover" />
                      <div>
                        <div class="font-medium text-gray-900">#{{ c.number }} - {{ c.name }}</div>
                        <div class="text-xs text-gray-500" v-if="c.origin">{{ c.origin }}</div>
                        <div class="text-[11px] text-red-600 mt-0.5" v-if="c.disabled">Already in a pair</div>
                      </div>
                    </label>
                  </div>
                  <p class="text-xs text-gray-500 mt-2">Pick exactly two single contestants. Contestants already in a pair are disabled.</p>
                  <p v-if="errors.member_ids" class="text-sm text-red-600 mt-1">{{ errors.member_ids }}</p>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t">
                  <button type="button" @click="onClose" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                  <button type="submit" :disabled="isSubmitting || form.member_ids.length !== 2" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg disabled:opacity-50">
                    <span v-if="isSubmitting" class="flex items-center"><Loader2 class="h-4 w-4 mr-2 animate-spin" />Creating...</span>
                    <span v-else>Create Pair</span>
                  </button>
                </div>
              </form>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
  
</template>

<script setup>
import { ref, reactive, watch, computed } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { XCircle, Loader2 } from 'lucide-vue-next'
import axios from 'axios'

const props = defineProps({
  show: { type: Boolean, required: true },
  pageantId: { type: Number, required: true },
  pageant: { type: Object, default: null },
  contestants: { type: Array, default: () => [] },
})

const emit = defineEmits(['close', 'created'])

const form = reactive({ number: '', name: '', member_ids: [] })
const errors = reactive({})
const isSubmitting = ref(false)

const availableContestants = computed(() => {
  return props.contestants
    .filter(c => !c.is_pair) // only single contestants can be members
    .map(c => ({ ...c, disabled: !!c.in_pair }))
})

watch(() => props.show, (val) => {
  if (val) {
    form.number = ''
    form.name = ''
    form.member_ids = []
    Object.keys(errors).forEach(k => delete errors[k])
  }
})

const onClose = () => emit('close')

const handleSubmit = async () => {
  Object.keys(errors).forEach(k => delete errors[k])
  if (form.member_ids.length !== 2) {
    errors.member_ids = 'Please select exactly two members.'
    return
  }
  isSubmitting.value = true
  try {
    const res = await axios.post(`/organizer/pageant/${props.pageantId}/pairs`, {
      number: Number(form.number),
      name: form.name || null,
      member_ids: form.member_ids,
    })
    if (res.data?.success) {
      emit('created', res.data.contestant)
      onClose()
    }
  } catch (e) {
    if (e.response?.data?.errors) {
      Object.assign(errors, e.response.data.errors)
    } else {
      errors.member_ids = e.response?.data?.message || 'Failed to create pair.'
    }
  } finally {
    isSubmitting.value = false
  }
}

// Helper functions for pageant type context
const getPageantTypeTitle = () => {
  if (!props.pageant || !props.pageant.contestant_type) return ''
  
  switch (props.pageant.contestant_type) {
    case 'pairs':
      return 'Pairs-Only Competition'
    case 'both':
      return 'Mixed Competition - Creating Pair'
    default:
      return 'Creating Pair Entry'
  }
}

const getPageantTypeDescription = () => {
  if (!props.pageant || !props.pageant.contestant_type) return ''
  
  switch (props.pageant.contestant_type) {
    case 'pairs':
      return 'This pair will compete as the main entry type in this pairs-only pageant.'
    case 'both':
      return 'This pair will compete alongside individual contestants in this mixed pageant.'
    default:
      return 'Creating a pair entry for this pageant.'
  }
}
</script>

<style scoped>
</style>
