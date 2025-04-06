<template>
  <div class="fixed inset-0 overflow-y-auto z-50" v-if="visible">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="$emit('close')">
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
      </div>

      <!-- Modal panel -->
      <div 
        class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modal-headline"
      >
        <div>
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-headline">
              {{ event ? 'Edit Event' : 'Add Event' }}
            </h3>
            <button 
              @click="$emit('close')" 
              type="button"
              class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none"
            >
              <span class="sr-only">Close</span>
              <X class="h-5 w-5" />
            </button>
          </div>
          
          <form @submit.prevent="submitForm">
            <div class="space-y-4">
              <!-- Event Name -->
              <div>
                <label for="event-name" class="block text-sm font-medium text-gray-700">
                  Event Name
                </label>
                <input 
                  type="text" 
                  id="event-name" 
                  v-model="form.name"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                  required
                />
              </div>
              
              <!-- Event Description -->
              <div>
                <label for="event-description" class="block text-sm font-medium text-gray-700">
                  Description
                </label>
                <textarea 
                  id="event-description" 
                  v-model="form.description"
                  rows="2"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                ></textarea>
              </div>
              
              <!-- Event Type -->
              <div>
                <label for="event-type" class="block text-sm font-medium text-gray-700">
                  Event Type
                </label>
                <select 
                  id="event-type" 
                  v-model="form.type"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                  required
                >
                  <option value="">Select Event Type</option>
                  <option value="Preliminary">Preliminary Round</option>
                  <option value="Final">Final Round</option>
                  <option value="Registration">Registration</option>
                  <option value="Rehearsal">Rehearsal</option>
                  <option value="Photoshoot">Photoshoot</option>
                  <option value="Interview">Interview</option>
                  <option value="Press">Press Conference</option>
                  <option value="Other">Other</option>
                </select>
              </div>
              
              <!-- Event Dates & Times -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label for="event-start" class="block text-sm font-medium text-gray-700">
                    Start Date & Time
                  </label>
                  <input 
                    type="datetime-local" 
                    id="event-start" 
                    v-model="form.start_datetime"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                    required
                  />
                </div>
                <div>
                  <label for="event-end" class="block text-sm font-medium text-gray-700">
                    End Date & Time
                  </label>
                  <input 
                    type="datetime-local" 
                    id="event-end" 
                    v-model="form.end_datetime"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                    required
                  />
                </div>
              </div>
              
              <!-- Event Location -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label for="event-venue" class="block text-sm font-medium text-gray-700">
                    Venue
                  </label>
                  <input 
                    type="text" 
                    id="event-venue" 
                    v-model="form.venue"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                  />
                </div>
                <div>
                  <label for="event-location" class="block text-sm font-medium text-gray-700">
                    Location
                  </label>
                  <input 
                    type="text" 
                    id="event-location" 
                    v-model="form.location"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                  />
                </div>
              </div>
              
              <!-- Event Status (Only for editing) -->
              <div v-if="event">
                <label for="event-status" class="block text-sm font-medium text-gray-700">
                  Status
                </label>
                <select 
                  id="event-status" 
                  v-model="form.status"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                  required
                >
                  <option value="Pending">Pending</option>
                  <option value="In Progress">In Progress</option>
                  <option value="Completed">Completed</option>
                  <option value="Cancelled">Cancelled</option>
                </select>
              </div>
              
              <!-- Milestone -->
              <div class="flex items-center">
                <input 
                  id="event-milestone" 
                  type="checkbox" 
                  v-model="form.is_milestone"
                  class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded"
                />
                <label for="event-milestone" class="ml-2 block text-sm text-gray-700">
                  Mark as Milestone Event
                </label>
              </div>
            </div>
            
            <div class="mt-6 flex justify-end space-x-3">
              <button 
                type="button" 
                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
                @click="$emit('close')"
              >
                Cancel
              </button>
              <button 
                type="submit" 
                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
                :disabled="processing"
              >
                <div v-if="processing" class="flex items-center">
                  <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Saving...
                </div>
                <span v-else>{{ event ? 'Update Event' : 'Add Event' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { X } from 'lucide-vue-next'

const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  },
  pageantId: {
    type: Number,
    required: true
  },
  event: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'saved'])

// Form processing state
const processing = ref(false)

// Format date for datetime-local input
const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toISOString().slice(0, 16) // Format as YYYY-MM-DDThh:mm
}

// Initialize form with empty values or event data if editing
const form = useForm({
  name: '',
  description: '',
  type: '',
  start_datetime: '',
  end_datetime: '',
  venue: '',
  location: '',
  status: 'Pending',
  is_milestone: false,
  display_order: 0,
})

// Update form when event prop changes
watch(() => props.event, (newEvent) => {
  if (newEvent) {
    form.name = newEvent.name
    form.description = newEvent.description || ''
    form.type = newEvent.type
    form.start_datetime = formatDate(newEvent.raw_start_datetime)
    form.end_datetime = formatDate(newEvent.raw_end_datetime)
    form.venue = newEvent.venue || ''
    form.location = newEvent.location || ''
    form.status = newEvent.status
    form.is_milestone = newEvent.is_milestone
    form.display_order = newEvent.display_order || 0
  } else {
    form.reset()
    form.status = 'Pending'
    form.is_milestone = false
  }
}, { immediate: true })

// Submit the form
const submitForm = () => {
  processing.value = true
  
  if (props.event) {
    // Update existing event
    form.put(route('organizer.pageant.events.update', {
      id: props.pageantId,
      eventId: props.event.id
    }), {
      onSuccess: () => {
        processing.value = false
        emit('saved')
        emit('close')
      },
      onError: () => {
        processing.value = false
      }
    })
  } else {
    // Create new event
    form.post(route('organizer.pageant.events.store', props.pageantId), {
      onSuccess: () => {
        processing.value = false
        emit('saved')
        emit('close')
      },
      onError: () => {
        processing.value = false
      }
    })
  }
}
</script> 