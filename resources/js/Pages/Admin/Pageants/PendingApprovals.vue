<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-teal-500 to-teal-600 rounded-xl shadow-md overflow-hidden">
      <div class="p-6">
        <div class="flex items-center justify-between">
          <div class="text-white">
            <h1 class="text-3xl font-bold">Pending Approvals</h1>
            <p class="mt-1 text-teal-100">Review and approve pageant submissions from organizers</p>
          </div>
          <div class="bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg px-4 py-2">
            <div class="flex items-center text-white font-medium">
              <Clock class="h-5 w-5 mr-2" />
              {{ pendingPageants.length }} Pending
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters and Actions -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-3 sm:space-y-0">
        <div class="flex items-center space-x-3">
          <div class="relative">
            <Search class="h-4 w-4 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search pageants..."
              class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-sm"
            />
          </div>
        </div>
        
        <div class="flex items-center space-x-3">
          <button
            @click="refreshData"
            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500"
            :disabled="isLoading"
          >
            <RefreshCw :class="['h-4 w-4 mr-2', {'animate-spin': isLoading}]" />
            Refresh
          </button>
        </div>
      </div>
    </div>

    <!-- Pending Pageants List -->
    <div v-if="filteredPageants.length === 0" class="bg-white rounded-lg shadow-sm border border-gray-100 p-12">
      <div class="text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-teal-50 mb-4">
          <CheckCircle class="h-8 w-8 text-teal-400" />
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No Pending Approvals</h3>
        <p class="text-gray-500">
          All pageant submissions have been reviewed. New submissions will appear here for approval.
        </p>
      </div>
    </div>

    <div v-else class="space-y-4">
      <div
        v-for="pageant in filteredPageants"
        :key="pageant.id"
        class="bg-white rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-all"
      >
        <div class="p-6">
          <!-- Pageant Header -->
          <div class="flex items-start justify-between mb-4">
            <div class="flex-1">
              <div class="flex items-center space-x-3 mb-2">
                <h3 class="text-xl font-semibold text-gray-900">{{ pageant.name }}</h3>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                  <Clock class="h-3 w-3 mr-1" />
                  Pending Approval
                </span>
              </div>
              
              <div class="flex items-center space-x-4 text-sm text-gray-500 mb-3">
                <div class="flex items-center">
                  <User class="h-4 w-4 mr-1" />
                  <span>{{ pageant.creator?.name || 'Unknown Organizer' }}</span>
                </div>
                <div class="flex items-center">
                  <Calendar class="h-4 w-4 mr-1" />
                  <span>Submitted {{ formatDate(pageant.created_at) }}</span>
                </div>
              </div>
            </div>
            
            <div class="flex items-center space-x-2 z-10">
              <button
                @click="approvePageant(pageant)"
                :disabled="processingPageants.includes(pageant.id)"
                class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <template v-if="processingPageants.includes(pageant.id) && lastAction === 'approve'">
                  <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                  Approving...
                </template>
                <template v-else>
                  <CheckCircle class="h-4 w-4 mr-2" />
                  Approve
                </template>
              </button>
              
              <button
                @click="rejectPageant(pageant)"
                :disabled="processingPageants.includes(pageant.id)"
                class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <template v-if="processingPageants.includes(pageant.id) && lastAction === 'reject'">
                  <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                  Rejecting...
                </template>
                <template v-else>
                  <XCircle class="h-4 w-4 mr-2" />
                  Reject
                </template>
              </button>
            </div>
          </div>

          <!-- Pageant Details -->
          <div class="bg-gray-50 rounded-lg p-4 space-y-3">
            <div v-if="pageant.description" class="text-sm text-gray-700">
              <span class="font-medium text-gray-900">Description:</span>
              {{ pageant.description }}
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
              <div v-if="pageant.start_date" class="flex items-center">
                <Calendar class="h-4 w-4 text-gray-400 mr-2" />
                <span class="text-gray-600">Start:</span>
                <span class="ml-1 font-medium">{{ formatDateOnly(pageant.start_date) }}</span>
              </div>
              
              <div v-if="pageant.end_date" class="flex items-center">
                <Calendar class="h-4 w-4 text-gray-400 mr-2" />
                <span class="text-gray-600">End:</span>
                <span class="ml-1 font-medium">{{ formatDateOnly(pageant.end_date) }}</span>
              </div>
              
              <div v-if="pageant.venue" class="flex items-center">
                <MapPin class="h-4 w-4 text-gray-400 mr-2" />
                <span class="text-gray-600">Venue:</span>
                <span class="ml-1 font-medium">{{ pageant.venue }}</span>
              </div>
              
              <div v-if="pageant.location" class="flex items-center">
                <MapPin class="h-4 w-4 text-gray-400 mr-2" />
                <span class="text-gray-600">Location:</span>
                <span class="ml-1 font-medium">{{ pageant.location }}</span>
              </div>
            </div>
            
            <div class="flex items-center">
              <BarChart3 class="h-4 w-4 text-gray-400 mr-2" />
              <span class="text-gray-600 text-sm">Scoring System:</span>
              <span class="ml-1 font-medium text-sm">{{ getScoringSystemLabel(pageant.scoring_system) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirmation Modal -->
    <Modal :show="showConfirmModal" @close="closeConfirmModal">
      <div class="p-6">
        <div class="flex items-center space-x-3 mb-4">
          <div :class="[
            'w-12 h-12 rounded-full flex items-center justify-center',
            confirmAction === 'approve' ? 'bg-green-100' : 'bg-red-100'
          ]">
            <CheckCircle v-if="confirmAction === 'approve'" class="h-6 w-6 text-green-600" />
            <XCircle v-else class="h-6 w-6 text-red-600" />
          </div>
          <div>
            <h3 class="text-lg font-medium text-gray-900">
              {{ confirmAction === 'approve' ? 'Approve Pageant' : 'Reject Pageant' }}
            </h3>
            <p class="text-sm text-gray-500">{{ selectedPageant?.name }}</p>
          </div>
        </div>
        
        <p class="text-gray-700 mb-6">
          <template v-if="confirmAction === 'approve'">
            Are you sure you want to approve this pageant? The organizer will be able to start managing contestants, criteria, and other pageant details.
          </template>
          <template v-else>
            Are you sure you want to reject this pageant? This action cannot be undone and the organizer will need to resubmit.
          </template>
        </p>
        
        <div class="flex justify-end space-x-3">
          <button
            @click="closeConfirmModal"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
          >
            Cancel
          </button>
          <button
            @click="confirmActionHandler"
            :disabled="isProcessing"
            :class="[
              'px-4 py-2 text-sm font-medium text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed',
              confirmAction === 'approve' 
                ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500' 
                : 'bg-red-600 hover:bg-red-700 focus:ring-red-500'
            ]"
          >
            <template v-if="isProcessing">
              <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2 inline-block"></div>
              Processing...
            </template>
            <template v-else>
              {{ confirmAction === 'approve' ? 'Approve' : 'Reject' }}
            </template>
          </button>
        </div>
      </div>
    </Modal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Modal from '@/Components/Modal.vue'
import { useNotification } from '@/Composables/useNotification'
import { 
  Clock, Search, RefreshCw, CheckCircle, XCircle, User, 
  Calendar, MapPin, BarChart3
} from 'lucide-vue-next'

defineOptions({
  layout: AdminLayout
})

const props = defineProps({
  pendingPageants: Array
})

// Composables
const notify = useNotification()

// State
const searchQuery = ref('')
const isLoading = ref(false)
const processingPageants = ref([])
const lastAction = ref('')
const showConfirmModal = ref(false)
const confirmAction = ref('')
const selectedPageant = ref(null)
const isProcessing = ref(false)

// Computed
const filteredPageants = computed(() => {
  if (!searchQuery.value) return props.pendingPageants
  
  return props.pendingPageants.filter(pageant => 
    pageant.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    pageant.description?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    pageant.location?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    pageant.venue?.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

// Methods
const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(date)
}

const formatDateOnly = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: '2-digit',
    year: 'numeric'
  }).format(date)
}

const getScoringSystemLabel = (system) => {
  const labels = {
    'percentage': 'Percentage (0-100%)',
    '1-10': 'Scale 1-10',
    '1-5': 'Scale 1-5',
    'points': 'Points (0-50)'
  }
  return labels[system] || system
}

const approvePageant = (pageant) => {
  selectedPageant.value = pageant
  confirmAction.value = 'approve'
  showConfirmModal.value = true
}

const rejectPageant = (pageant) => {
  selectedPageant.value = pageant
  confirmAction.value = 'reject'
  showConfirmModal.value = true
}

const closeConfirmModal = () => {
  showConfirmModal.value = false
  selectedPageant.value = null
  confirmAction.value = ''
  isProcessing.value = false
}

const confirmActionHandler = () => {
  if (!selectedPageant.value) return
  
  isProcessing.value = true
  processingPageants.value.push(selectedPageant.value.id)
  lastAction.value = confirmAction.value
  
  const action = confirmAction.value === 'approve' ? 'approve' : 'reject'
  const url = `/admin/pageants/${selectedPageant.value.id}/${action}`
  
  router.post(url, {}, {
    onSuccess: (page) => {
      notify.success(
        `Pageant "${selectedPageant.value.name}" has been ${confirmAction.value}d successfully!`
      )
      closeConfirmModal()
      
      // Refresh the data to show updated list
      refreshData()
    },
    onError: (errors) => {
      console.error('Error processing pageant:', errors)
      
      // Check for specific error types
      if (errors && errors.message) {
        notify.error(errors.message)
      } else if (typeof errors === 'string') {
        notify.error(errors)
      } else if (errors && Object.keys(errors).length > 0) {
        const firstError = Object.values(errors)[0]
        notify.error(Array.isArray(firstError) ? firstError[0] : firstError)
      } else {
        notify.error(`Failed to ${confirmAction.value} pageant. Please try again.`)
      }
      
      closeConfirmModal()
    },
    onFinish: () => {
      isProcessing.value = false
      const index = processingPageants.value.indexOf(selectedPageant.value.id)
      if (index > -1) {
        processingPageants.value.splice(index, 1)
      }
    }
  })
}

const refreshData = () => {
  isLoading.value = true
  router.reload({ 
    only: ['pendingPageants'],
    onFinish: () => {
      isLoading.value = false
    }
  })
}

onMounted(() => {
  // Any initialization logic
})
</script>
