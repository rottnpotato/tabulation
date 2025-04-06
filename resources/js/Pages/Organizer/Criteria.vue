<template>
  <div class="space-y-6">
    <div class="bg-white shadow-sm rounded-lg p-6">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-900">Pageant Criteria</h2>
        <button
          @click="ShowAddModal = true"
          class="bg-gradient-to-r from-orange-500 to-rose-500 hover:from-orange-600 hover:to-rose-600 text-white px-4 py-2 rounded-lg flex items-center"
        >
          <Plus class="h-5 w-5 mr-2" />
          Add Criteria
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="space-y-4">
        <div v-for="i in 3" :key="i" class="bg-white border rounded-lg p-4 animate-pulse">
          <div class="flex items-center justify-between">
            <div class="w-full">
              <div class="h-5 bg-gray-200 rounded w-1/3 mb-2"></div>
              <div class="h-4 bg-gray-200 rounded w-2/3"></div>
            </div>
            <div class="flex items-center space-x-4">
              <div class="h-6 w-12 bg-gray-200 rounded"></div>
              <div class="flex space-x-2">
                <div class="h-5 w-5 bg-gray-200 rounded"></div>
                <div class="h-5 w-5 bg-gray-200 rounded"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-300 text-red-800 px-4 py-3 rounded-md mb-4">
        <div class="flex items-center">
          <AlertTriangle class="h-5 w-5 mr-2 text-red-500" />
          <span>{{ error }}</span>
        </div>
        <button 
          @click="fetchCriteria" 
          class="mt-2 text-sm text-red-700 underline hover:text-red-800"
        >
          Try again
        </button>
      </div>

      <!-- Empty State -->
      <div v-else-if="Criteria.length === 0" class="text-center py-12">
        <div class="flex items-center justify-center h-16 w-16 mx-auto bg-orange-100 rounded-full mb-4">
          <ListChecks class="h-8 w-8 text-orange-500" />
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-1">No criteria defined yet</h3>
        <p class="text-gray-500 mb-4 max-w-md mx-auto">Define judging criteria to determine how contestants will be evaluated during the pageant</p>
        <button
          @click="ShowAddModal = true"
          class="bg-gradient-to-r from-orange-500 to-rose-500 hover:from-orange-600 hover:to-rose-600 text-white px-4 py-2 rounded-lg flex items-center mx-auto"
        >
          <Plus class="h-5 w-5 mr-2" />
          Add First Criterion
        </button>
      </div>

      <!-- Criteria List -->
      <div v-else class="space-y-4">
        <div
          v-for="criterion in Criteria"
          :key="criterion.id"
          class="bg-white border rounded-lg p-4"
        >
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-medium text-gray-900">{{ criterion.name }}</h3>
              <p class="text-gray-600">{{ criterion.description }}</p>
            </div>
            <div class="flex items-center space-x-4">
              <div class="text-lg font-semibold text-orange-600">
                {{ criterion.weight }}%
              </div>
              <div class="flex space-x-2">
                <button
                  @click="EditCriterion(criterion)"
                  class="text-gray-600 hover:text-orange-600"
                >
                  <Edit2 class="h-5 w-5" />
                </button>
                <button
                  @click="DeleteCriterion(criterion)"
                  class="text-red-600 hover:text-red-700"
                >
                  <Trash2 class="h-5 w-5" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <TransitionRoot appear :show="ShowAddModal" as="template">
      <Dialog as="div" @close="ShowAddModal = false" class="relative z-10">
        <TransitionChild
          enter="ease-out duration-300"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="ease-in duration-200"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-black bg-opacity-25" />
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
                <div class="bg-gradient-to-r from-orange-500 to-rose-500 -m-6 mb-6 p-6 text-white">
                  <DialogTitle as="h3" class="text-lg font-medium leading-6 mb-1">
                    {{ EditingCriterion ? 'Edit Criterion' : 'Add New Criterion' }}
                  </DialogTitle>
                  <p class="text-orange-100 text-sm">{{ EditingCriterion ? 'Update the details of this criterion' : 'Add a new judging criterion to your pageant' }}</p>
                </div>

                <form @submit.prevent="HandleSubmit" class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Criterion Name</label>
                    <input
                      type="text"
                      v-model="Form.name"
                      class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500"
                      required
                    />
                    <p v-if="formErrors.name" class="mt-1 text-sm text-red-600">{{ formErrors.name }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea
                      v-model="Form.description"
                      rows="3"
                      class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500"
                      required
                    ></textarea>
                    <p v-if="formErrors.description" class="mt-1 text-sm text-red-600">{{ formErrors.description }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Weight (%)</label>
                    <input
                      type="number"
                      v-model="Form.weight"
                      min="0"
                      max="100"
                      class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500"
                      required
                    />
                    <p v-if="formErrors.weight" class="mt-1 text-sm text-red-600">{{ formErrors.weight }}</p>
                    <p v-else class="mt-1 text-xs text-gray-500">Enter a value between 0 and 100</p>
                  </div>

                  <!-- Additional settings if needed - could be expanded later -->
                  <div v-if="formErrors.general" class="p-3 bg-red-50 text-red-700 text-sm rounded-lg">
                    {{ formErrors.general }}
                  </div>

                  <div class="mt-6 flex justify-end space-x-3">
                    <button
                      type="button"
                      @click="ShowAddModal = false"
                      class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                    >
                      Cancel
                    </button>
                    <button
                      type="submit"
                      class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-orange-500 to-rose-500 rounded-lg hover:from-orange-600 hover:to-rose-600 shadow-sm transition-colors flex items-center"
                      :disabled="isSubmitting"
                    >
                      <Loader2 v-if="isSubmitting" class="mr-2 h-4 w-4 animate-spin" />
                      {{ EditingCriterion ? 'Save Changes' : 'Add Criterion' }}
                    </button>
                  </div>
                </form>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- Delete Confirmation Modal -->
    <TransitionRoot appear :show="showDeleteModal" as="template">
      <Dialog as="div" @close="showDeleteModal = false" class="relative z-10">
        <TransitionChild
          enter="ease-out duration-300"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="ease-in duration-200"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-black bg-opacity-25" />
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
                <div class="bg-red-500 -m-6 mb-6 p-6 text-white">
                  <DialogTitle as="h3" class="text-lg font-medium leading-6 mb-1">
                    Delete Criterion
                  </DialogTitle>
                </div>

                <div class="mb-6">
                  <p class="text-gray-700 mb-3">
                    Are you sure you want to delete this criterion?
                  </p>
                  <p class="font-medium text-gray-900">{{ criterionToDelete?.name }}</p>
                  <p class="text-sm text-gray-500 mt-1">This action cannot be undone.</p>
                </div>

                <div class="flex justify-end space-x-3">
                  <button
                    type="button"
                    @click="showDeleteModal = false"
                    class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                  >
                    Cancel
                  </button>
                  <button
                    type="button"
                    @click="ConfirmDelete"
                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg shadow-sm transition-colors flex items-center"
                    :disabled="isDeleting"
                  >
                    <Loader2 v-if="isDeleting" class="mr-2 h-4 w-4 animate-spin" />
                    <Trash2 v-else class="mr-2 h-4 w-4" />
                    Delete
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
import { ref, onMounted } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue'
import { Plus, Edit2, Trash2, AlertTriangle, Loader2 } from 'lucide-vue-next'
import OrganizerLayout from '@/Layouts/OrganizerLayout.vue'
import axios from 'axios'
import { ListChecks } from 'lucide-vue-next'

defineOptions({
  layout: OrganizerLayout
})

// Props
const props = defineProps({
  pageantId: {
    type: Number,
    required: true
  }
})

// State
const ShowAddModal = ref(false)
const EditingCriterion = ref(null)
const Criteria = ref([])
const isLoading = ref(true)
const isSubmitting = ref(false)
const isDeleting = ref(false)
const error = ref(null)
const formErrors = ref({})
const showDeleteModal = ref(false)
const criterionToDelete = ref(null)

const Form = ref({
  name: '',
  description: '',
  weight: 0,
  min_score: 0,
  max_score: 100
})

// Fetch criteria on component mount
onMounted(() => {
  fetchCriteria()
})

const fetchCriteria = async () => {
  isLoading.value = true
  error.value = null
  
  try {
    const response = await axios.get(`/organizer/pageant/${props.pageantId}/criteria`)
    Criteria.value = response.data.criteria
  } catch (err) {
    console.error('Error fetching criteria:', err)
    error.value = 'Failed to load criteria. Please try again.'
  } finally {
    isLoading.value = false
  }
}

const resetForm = () => {
  Form.value = {
    name: '',
    description: '',
    weight: 0,
    min_score: 0,
    max_score: 100
  }
  formErrors.value = {}
}

const EditCriterion = (criterion) => {
  EditingCriterion.value = criterion
  Form.value = { ...criterion }
  ShowAddModal.value = true
}

const DeleteCriterion = (criterion) => {
  criterionToDelete.value = criterion
  showDeleteModal.value = true
}

const ConfirmDelete = async () => {
  if (!criterionToDelete.value) return
  
  isDeleting.value = true
  try {
    await axios.delete(`/organizer/pageant/${props.pageantId}/criteria/${criterionToDelete.value.id}`)
    
    // Remove from list
    Criteria.value = Criteria.value.filter(c => c.id !== criterionToDelete.value.id)
    
    // Close modal
    showDeleteModal.value = false
    criterionToDelete.value = null
  } catch (err) {
    console.error('Error deleting criterion:', err)
    // Could add error handling in modal
  } finally {
    isDeleting.value = false
  }
}

const HandleSubmit = async () => {
  formErrors.value = {}
  isSubmitting.value = true
  
  try {
    let response
    
    if (EditingCriterion.value) {
      // Update existing criterion
      response = await axios.put(
        `/organizer/pageant/${props.pageantId}/criteria/${EditingCriterion.value.id}`, 
        Form.value
      )
      
      // Update in list
      const index = Criteria.value.findIndex(c => c.id === EditingCriterion.value.id)
      if (index !== -1) {
        Criteria.value[index] = response.data.criterion
      }
    } else {
      // Create new criterion
      response = await axios.post(
        `/organizer/pageant/${props.pageantId}/criteria`, 
        Form.value
      )
      
      // Add to list
      Criteria.value.push(response.data.criterion)
    }
    
    // Close modal and reset form
    ShowAddModal.value = false
    EditingCriterion.value = null
    resetForm()
  } catch (err) {
    console.error('Error submitting criterion:', err)
    
    if (err.response && err.response.data && err.response.data.errors) {
      // Set validation errors
      formErrors.value = err.response.data.errors
    } else {
      // Set generic error
      formErrors.value.general = 'An error occurred while saving the criterion. Please try again.'
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>