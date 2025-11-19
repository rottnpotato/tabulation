<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between md:items-center space-y-3 md:space-y-0">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Edit Access Requests</h1>
        <p class="mt-1 text-sm text-gray-500">
          Review and manage organizer requests for editing restricted pageants
        </p>
      </div>
      
      <!-- Filter Tabs -->
      <div class="flex space-x-2">
        <button
          v-for="filter in filters"
          :key="filter.value"
          @click="activeFilter = filter.value"
          :class="[
            activeFilter === filter.value
              ? 'bg-orange-100 text-orange-700 border-orange-300'
              : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
            'px-4 py-2 border rounded-md text-sm font-medium transition-colors'
          ]"
        >
          {{ filter.label }}
          <span
            v-if="getFilterCount(filter.value) > 0"
            :class="[
              activeFilter === filter.value
                ? 'bg-orange-200 text-orange-800'
                : 'bg-gray-200 text-gray-700',
              'ml-2 px-2 py-0.5 rounded-full text-xs font-semibold'
            ]"
          >
            {{ getFilterCount(filter.value) }}
          </span>
        </button>
      </div>
    </div>

    <!-- Requests List -->
    <div v-if="filteredRequests.length === 0" class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
      <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
        <FileText class="h-8 w-8 text-gray-400" />
      </div>
      <h3 class="text-lg font-medium text-gray-900 mb-1">No Requests Found</h3>
      <p class="text-gray-500">
        {{ getEmptyStateMessage() }}
      </p>
    </div>

    <div v-else class="space-y-4">
      <div
        v-for="request in filteredRequests"
        :key="request.id"
        class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow"
      >
        <div class="p-6">
          <!-- Header -->
          <div class="flex items-start justify-between mb-4">
            <div class="flex-1">
              <div class="flex items-center space-x-3 mb-2">
                <h3 class="text-lg font-semibold text-gray-900">
                  {{ request.pageant_name }}
                </h3>
                <span
                  :class="[
                    request.status === 'pending'
                      ? 'bg-yellow-100 text-yellow-800'
                      : request.status === 'approved'
                      ? 'bg-green-100 text-green-800'
                      : 'bg-red-100 text-red-800',
                    'px-2.5 py-0.5 rounded-full text-xs font-medium'
                  ]"
                >
                  {{ getStatusLabel(request.status) }}
                </span>
              </div>
              
              <!-- Organizer Info -->
              <div class="flex items-center text-sm text-gray-500 space-x-4">
                <div class="flex items-center">
                  <User class="h-4 w-4 mr-1.5" />
                  <span>{{ request.organizer_name }}</span>
                </div>
                <div class="flex items-center">
                  <Mail class="h-4 w-4 mr-1.5" />
                  <span>{{ request.organizer_email }}</span>
                </div>
                <div class="flex items-center">
                  <Calendar class="h-4 w-4 mr-1.5" />
                  <span>{{ formatDate(request.pageant_start_date) }}</span>
                </div>
              </div>
            </div>
            
            <div class="text-right text-sm text-gray-500">
              <Clock class="h-4 w-4 inline mr-1" />
              {{ formatRelativeTime(request.created_at) }}
            </div>
          </div>

          <!-- Reason -->
          <div class="mb-4 p-4 bg-gray-50 rounded-md border border-gray-200">
            <p class="text-sm font-medium text-gray-700 mb-1">Reason for Request:</p>
            <p class="text-sm text-gray-600">{{ request.reason }}</p>
          </div>

          <!-- Review Info (if reviewed) -->
          <div v-if="request.status !== 'pending'" class="mb-4 p-4 rounded-md border"
            :class="[
              request.status === 'approved'
                ? 'bg-green-50 border-green-200'
                : 'bg-red-50 border-red-200'
            ]"
          >
            <div class="flex items-start justify-between">
              <div>
                <p class="text-sm font-medium mb-1"
                  :class="[
                    request.status === 'approved' ? 'text-green-800' : 'text-red-800'
                  ]"
                >
                  {{ request.status === 'approved' ? 'Approved' : 'Rejected' }} by {{ request.reviewer_name }}
                </p>
                <p v-if="request.admin_notes" class="text-sm"
                  :class="[
                    request.status === 'approved' ? 'text-green-700' : 'text-red-700'
                  ]"
                >
                  <span class="font-medium">Notes:</span> {{ request.admin_notes }}
                </p>
              </div>
              <span class="text-xs"
                :class="[
                  request.status === 'approved' ? 'text-green-600' : 'text-red-600'
                ]"
              >
                {{ formatRelativeTime(request.reviewed_at) }}
              </span>
            </div>
          </div>

          <!-- Actions (only for pending requests) -->
          <div v-if="request.status === 'pending'" class="flex items-center space-x-3">
            <button
              @click="openApproveModal(request)"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors"
            >
              <CheckCircle class="h-4 w-4 mr-2" />
              Approve
            </button>
            <button
              @click="openRejectModal(request)"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-colors"
            >
              <XCircle class="h-4 w-4 mr-2" />
              Reject
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Approve Modal -->
    <TransitionRoot appear :show="showApproveModal" as="template">
      <Dialog as="div" @close="closeApproveModal" class="relative z-30">
        <TransitionChild
          enter="ease-out duration-300"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="ease-in duration-200"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" />
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
              <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all">
                <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900 mb-4">
                  Approve Edit Access Request
                </DialogTitle>
                
                <div v-if="selectedRequest" class="mt-2">
                  <p class="text-sm text-gray-500 mb-4">
                    You are about to grant edit access to <span class="font-semibold">{{ selectedRequest.organizer_name }}</span> for the pageant "<span class="font-semibold">{{ selectedRequest.pageant_name }}</span>".
                  </p>
                  
                  <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-md">
                    <p class="text-sm text-green-800">
                      <strong>Note:</strong> This will allow the organizer to edit the pageant even though the start date has been reached.
                    </p>
                  </div>

                  <div class="space-y-4">
                    <div>
                      <label for="approve-notes" class="block text-sm font-medium text-gray-700 mb-1">
                        Admin Notes (Optional)
                      </label>
                      <textarea
                        id="approve-notes"
                        v-model="approveForm.notes"
                        rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 text-sm"
                        placeholder="Add any notes about this approval..."
                      ></textarea>
                    </div>
                  </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                  <button
                    type="button"
                    @click="closeApproveModal"
                    :disabled="approveForm.processing"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    Cancel
                  </button>
                  <button
                    type="button"
                    @click="approveRequest"
                    :disabled="approveForm.processing"
                    class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    <span v-if="approveForm.processing">Approving...</span>
                    <span v-else>Approve Request</span>
                  </button>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- Reject Modal -->
    <TransitionRoot appear :show="showRejectModal" as="template">
      <Dialog as="div" @close="closeRejectModal" class="relative z-30">
        <TransitionChild
          enter="ease-out duration-300"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="ease-in duration-200"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" />
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
              <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all">
                <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900 mb-4">
                  Reject Edit Access Request
                </DialogTitle>
                
                <div v-if="selectedRequest" class="mt-2">
                  <p class="text-sm text-gray-500 mb-4">
                    You are about to reject the edit access request from <span class="font-semibold">{{ selectedRequest.organizer_name }}</span> for the pageant "<span class="font-semibold">{{ selectedRequest.pageant_name }}</span>".
                  </p>

                  <div class="space-y-4">
                    <div>
                      <label for="reject-notes" class="block text-sm font-medium text-gray-700 mb-1">
                        Reason for Rejection (Optional)
                      </label>
                      <textarea
                        id="reject-notes"
                        v-model="rejectForm.notes"
                        rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500 text-sm"
                        placeholder="Explain why this request is being rejected..."
                      ></textarea>
                    </div>
                  </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                  <button
                    type="button"
                    @click="closeRejectModal"
                    :disabled="rejectForm.processing"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    Cancel
                  </button>
                  <button
                    type="button"
                    @click="rejectRequest"
                    :disabled="rejectForm.processing"
                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    <span v-if="rejectForm.processing">Rejecting...</span>
                    <span v-else>Reject Request</span>
                  </button>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue'
import { 
  User, 
  Mail, 
  Calendar, 
  Clock, 
  CheckCircle, 
  XCircle, 
  FileText 
} from 'lucide-vue-next'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineOptions({
  layout: AdminLayout
})

const props = defineProps({
  requests: {
    type: Array,
    required: true
  }
})

// State
const activeFilter = ref('all')
const showApproveModal = ref(false)
const showRejectModal = ref(false)
const selectedRequest = ref(null)

const approveForm = ref({
  notes: '',
  processing: false
})

const rejectForm = ref({
  notes: '',
  processing: false
})

// Filters
const filters = [
  { value: 'all', label: 'All' },
  { value: 'pending', label: 'Pending' },
  { value: 'approved', label: 'Approved' },
  { value: 'rejected', label: 'Rejected' }
]

// Computed
const filteredRequests = computed(() => {
  if (activeFilter.value === 'all') {
    return props.requests
  }
  return props.requests.filter(request => request.status === activeFilter.value)
})

// Methods
const getFilterCount = (filterValue) => {
  if (filterValue === 'all') {
    return props.requests.length
  }
  return props.requests.filter(request => request.status === filterValue).length
}

const getStatusLabel = (status) => {
  const labels = {
    pending: 'Pending Review',
    approved: 'Approved',
    rejected: 'Rejected'
  }
  return labels[status] || status
}

const getEmptyStateMessage = () => {
  const messages = {
    all: 'No edit access requests have been submitted yet.',
    pending: 'No pending requests to review.',
    approved: 'No approved requests.',
    rejected: 'No rejected requests.'
  }
  return messages[activeFilter.value] || 'No requests found.'
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatRelativeTime = (date) => {
  if (!date) return ''
  
  const now = new Date()
  const then = new Date(date)
  const diffInSeconds = Math.floor((now - then) / 1000)
  
  if (diffInSeconds < 60) return 'Just now'
  if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`
  if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`
  if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)} days ago`
  
  return formatDate(date)
}

const openApproveModal = (request) => {
  selectedRequest.value = request
  approveForm.value.notes = ''
  showApproveModal.value = true
}

const closeApproveModal = () => {
  showApproveModal.value = false
  selectedRequest.value = null
  approveForm.value.notes = ''
}

const approveRequest = () => {
  if (!selectedRequest.value) return
  
  approveForm.value.processing = true
  
  router.post(route('admin.pageants.edit-access-requests.approve', selectedRequest.value.id), {
    notes: approveForm.value.notes
  }, {
    preserveScroll: true,
    onSuccess: () => {
      closeApproveModal()
      // Force page reload to ensure fresh data
      router.reload({ only: ['requests'] })
    },
    onError: (errors) => {
      console.error('Approval error:', errors)
      alert('Failed to approve request. Please try again.')
    },
    onFinish: () => {
      approveForm.value.processing = false
    }
  })
}

const openRejectModal = (request) => {
  selectedRequest.value = request
  rejectForm.value.notes = ''
  showRejectModal.value = true
}

const closeRejectModal = () => {
  showRejectModal.value = false
  selectedRequest.value = null
  rejectForm.value.notes = ''
}

const rejectRequest = () => {
  if (!selectedRequest.value) return
  
  rejectForm.value.processing = true
  
  router.post(route('admin.pageants.edit-access-requests.reject', selectedRequest.value.id), {
    notes: rejectForm.value.notes
  }, {
    preserveScroll: true,
    onSuccess: () => {
      closeRejectModal()
      // Force page reload to ensure fresh data
      router.reload({ only: ['requests'] })
    },
    onError: (errors) => {
      console.error('Rejection error:', errors)
      alert('Failed to reject request. Please try again.')
    },
    onFinish: () => {
      rejectForm.value.processing = false
    }
  })
}
</script>
