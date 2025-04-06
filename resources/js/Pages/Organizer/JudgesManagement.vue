<template>
  <div class="space-y-6">
    <!-- Header Section with Gradient Background -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
      <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-orange-50 to-rose-50">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-semibold text-gray-800">{{ pageant.name }}</h1>
            <p class="mt-1 text-sm text-gray-600">Manage judges and tabulators for this pageant</p>
          </div>
          <div class="flex gap-3">
            <Link
              :href="route('organizer.pageant.view', pageant.id)"
              class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 bg-white border border-gray-300 rounded-lg transition-colors shadow-sm hover:shadow flex items-center"
            >
              <ArrowLeft class="h-4 w-4 mr-2 text-orange-500" />
              Back to Pageant
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left Column - Judges Settings & Configuration -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Required Judges Setting Section -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
          <div class="p-4 sm:p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
              <Scale class="h-5 w-5 mr-2 text-orange-500" />
              Required Judges
            </h2>
            <p class="text-sm text-gray-500 mb-4">
              Set the number of judges needed for this pageant. The assigned tabulator will be responsible for creating judge accounts.
            </p>

            <form @submit.prevent="updateRequiredJudges" class="space-y-4">
              <div>
                <label for="requiredJudges" class="block text-sm font-medium text-gray-700">Number of Judges Required</label>
                <input 
                  type="number" 
                  id="requiredJudges" 
                  v-model="requiredJudgesForm.required_judges" 
                  min="0" 
                  max="20"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm"
                />
              </div>
              <div class="flex justify-end">
                <button 
                  type="submit"
                  :disabled="requiredJudgesForm.processing"
                  class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                  <Loader2 v-if="requiredJudgesForm.processing" class="h-4 w-4 mr-1.5 animate-spin" />
                  <Save v-else class="h-4 w-4 mr-1.5" />
                  Save
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Judges List -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
          <div class="p-4 sm:p-6">
            <div class="flex justify-between items-center mb-4">
              <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                <Users class="h-5 w-5 mr-2 text-orange-500" />
                Judges
              </h2>
              <div class="flex items-center">
                <span class="text-sm text-gray-500 mr-2">Required: {{ pageant.required_judges || 0 }}</span>
                <span class="text-sm text-gray-500">Assigned: {{ pageant.judges?.length || 0 }}</span>
              </div>
            </div>

            <!-- Empty State for Judges -->
            <div v-if="!pageant.judges || pageant.judges.length === 0" class="bg-gray-50 rounded-lg py-6 px-4 text-center">
              <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 mb-3">
                <Scale class="h-6 w-6 text-gray-400" />
              </div>
              <h3 class="text-base font-medium text-gray-900 mb-1">No Judges Assigned</h3>
              <p class="text-sm text-gray-500 max-w-md mx-auto">
                No judges have been assigned to this pageant yet. The tabulator will handle judge assignments.
              </p>
            </div>

            <!-- Judges List -->
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
              <div 
                v-for="judge in pageant.judges" 
                :key="judge.id"
                class="bg-gray-50 rounded-lg p-4 hover:shadow-sm transition-shadow"
              >
                <div class="flex items-center">
                  <div class="h-10 w-10 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0">
                    <User2 class="h-5 w-5 text-orange-600" />
                  </div>
                  <div class="ml-3">
                    <h3 class="font-medium text-gray-900">{{ judge.name }}</h3>
                    <p class="text-sm text-gray-500">@{{ judge.username }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Scoring System -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
          <div class="p-4 sm:p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
              <Calculator class="h-5 w-5 mr-2 text-orange-500" />
              Scoring System
            </h2>
            <p class="text-sm text-gray-500 mb-4">
              Choose the scoring system for this pageant. This will determine how judges score contestants across all criteria.
            </p>

            <form @submit.prevent="updateScoringSystem" class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div 
                  v-for="system in scoringSystems" 
                  :key="system.type" 
                  @click="scoringSystemForm.scoring_system = system.type"
                  class="relative border rounded-lg p-4 cursor-pointer hover:bg-orange-50 transition-colors"
                  :class="scoringSystemForm.scoring_system === system.type ? 'border-orange-500 bg-orange-50 ring-2 ring-orange-200' : 'border-gray-300'"
                >
                  <div class="flex items-start">
                    <div class="flex items-center h-5">
                      <input 
                        type="radio" 
                        :id="system.type" 
                        :value="system.type" 
                        v-model="scoringSystemForm.scoring_system"
                        class="h-4 w-4 text-orange-600 border-gray-300 focus:ring-orange-500"
                      />
                    </div>
                    <div class="ml-3 text-sm">
                      <label :for="system.type" class="font-medium text-gray-900">{{ system.name }}</label>
                      <p class="text-gray-500">{{ system.description }}</p>
                    </div>
                  </div>
                  <div v-if="scoringSystemForm.scoring_system === system.type" class="absolute top-2 right-2 text-orange-600">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                    </svg>
                  </div>
                </div>
              </div>

              <div class="flex justify-end">
                <button 
                  type="submit"
                  :disabled="scoringSystemForm.processing"
                  class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                  <Loader2 v-if="scoringSystemForm.processing" class="h-4 w-4 mr-1.5 animate-spin" />
                  <Save v-else class="h-4 w-4 mr-1.5" />
                  Save Scoring System
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Right Column - Tabulator Management -->
      <div class="space-y-6">
        <!-- Tabulator Assignment Section -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
          <div class="p-4 sm:p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
              <Calculator class="h-5 w-5 mr-2 text-orange-500" />
              Assign Tabulator
            </h2>
            <p class="text-sm text-gray-500 mb-4">
              Assign a tabulator to this pageant. Tabulators are responsible for creating judge accounts and managing the scoring process.
            </p>

            <form @submit.prevent="assignTabulator" class="space-y-4">
              <div>
                <label for="tabulatorId" class="block text-sm font-medium text-gray-700">Select Tabulator</label>
                <select 
                  id="tabulatorId" 
                  v-model="tabulatorForm.tabulator_id"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm"
                >
                  <option value="" disabled>Select a tabulator</option>
                  <option 
                    v-for="tabulator in availableTabulators" 
                    :key="tabulator.id" 
                    :value="tabulator.id"
                  >
                    {{ tabulator.name }} (@{{ tabulator.username }})
                  </option>
                </select>
              </div>
              <div>
                <label for="tabulatorNotes" class="block text-sm font-medium text-gray-700">Notes (Optional)</label>
                <textarea 
                  id="tabulatorNotes" 
                  v-model="tabulatorForm.notes" 
                  rows="2"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm"
                  placeholder="Add any notes about this tabulator assignment"
                ></textarea>
              </div>
              <div class="flex justify-end">
                <button 
                  type="submit"
                  :disabled="tabulatorForm.processing || !tabulatorForm.tabulator_id"
                  class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                  <Loader2 v-if="tabulatorForm.processing" class="h-4 w-4 mr-1.5 animate-spin" />
                  <Save v-else class="h-4 w-4 mr-1.5" />
                  Assign Tabulator
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Assigned Tabulators Section -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
          <div class="p-4 sm:p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
              <Calculator class="h-5 w-5 mr-2 text-orange-500" />
              Assigned Tabulators
            </h2>

            <!-- Empty State for Tabulators -->
            <div v-if="!pageant.tabulators || pageant.tabulators?.length === 0" class="bg-gray-50 rounded-lg py-6 px-4 text-center">
              <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 mb-3">
                <Calculator class="h-6 w-6 text-gray-400" />
              </div>
              <h3 class="text-base font-medium text-gray-900 mb-1">No Tabulators Assigned</h3>
              <p class="text-sm text-gray-500 max-w-md mx-auto">
                No tabulators have been assigned to this pageant yet. Use the form above to assign a tabulator.
              </p>
            </div>

            <!-- Tabulators List -->
            <div v-else class="space-y-3">
              <div 
                v-for="tabulator in pageant.tabulators" 
                :key="tabulator.id"
                class="flex items-center justify-between bg-gray-50 p-3 rounded-lg"
              >
                <div class="flex items-center">
                  <div class="h-10 w-10 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0">
                    <Calculator class="h-5 w-5 text-orange-600" />
                  </div>
                  <div class="ml-3">
                    <h3 class="font-medium text-gray-900">{{ tabulator.name }}</h3>
                    <p class="text-sm text-gray-500">@{{ tabulator.username }}</p>
                  </div>
                </div>
                <div>
                  <button 
                    @click="confirmRemoveTabulator(tabulator)"
                    class="p-2 rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50"
                  >
                    <Trash class="h-5 w-5" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirm Delete Modal -->
    <ConfirmDeleteModal 
      :show="showDeleteModal" 
      @close="showDeleteModal = false"
      @confirm="removeTabulator"
      :title="`Remove ${tabulatorToDelete?.name}`"
      :message="`Are you sure you want to remove this tabulator from the pageant? This action will remove their access to manage this pageant.`"
      :processing="deleteTabulatorProcessing"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import { 
  ArrowLeft, 
  Scale, 
  Users, 
  User2, 
  Calculator, 
  Save, 
  Loader2, 
  Trash 
} from 'lucide-vue-next'
import { useForm } from '@inertiajs/vue3'
import OrganizerLayout from '@/Layouts/OrganizerLayout.vue'
import ConfirmDeleteModal from '@/Components/ConfirmDeleteModal.vue'

defineOptions({
  layout: OrganizerLayout
})

const props = defineProps({
  pageant: {
    type: Object,
    required: true
  },
  pageantId: {
    type: Number,
    required: true
  },
  availableTabulators: {
    type: Array,
    default: () => []
  }
})

// Form state
const requiredJudgesForm = useForm({
  required_judges: props.pageant.required_judges || 0
})

const scoringSystemForm = useForm({
  scoring_system: props.pageant.scoring_system || 'percentage'
})

const tabulatorForm = useForm({
  tabulator_id: '',
  notes: ''
})

// Modal states
const showDeleteModal = ref(false)
const tabulatorToDelete = ref(null)
const deleteTabulatorProcessing = ref(false)

// Scoring systems
const scoringSystems = [
  {
    type: 'percentage',
    name: 'Percentage System',
    description: 'Scores are given as a percentage from 0-100'
  },
  {
    type: 'points_1_10',
    name: 'Points System (1-10)',
    description: 'Scores are given as points from 1 to 10'
  },
  {
    type: 'points_1_5',
    name: 'Points System (1-5)',
    description: 'Scores are given as points from 1 to 5'
  },
  {
    type: 'ranking',
    name: 'Ranking System',
    description: 'Contestants are ranked from 1st to last'
  }
]

const updateRequiredJudges = () => {
  requiredJudgesForm.put(route('organizer.pageant.required-judges.update', props.pageant.id), {
    onSuccess: () => {
      // Success notification could be added here
    }
  })
}

const updateScoringSystem = () => {
  scoringSystemForm.put(route('organizer.pageant.scoring-system.update', props.pageant.id), {
    onSuccess: () => {
      // Success notification could be added here
    }
  })
}

const assignTabulator = () => {
  tabulatorForm.post(route('organizer.pageant.tabulators.assign', props.pageant.id), {
    onSuccess: () => {
      tabulatorForm.reset()
      // Success notification could be added here
    }
  })
}

const confirmRemoveTabulator = (tabulator) => {
  tabulatorToDelete.value = tabulator
  showDeleteModal.value = true
}

const removeTabulator = () => {
  deleteTabulatorProcessing.value = true
  
  axios.delete(route('organizer.pageant.tabulators.remove', [props.pageant.id, tabulatorToDelete.value.id]))
    .then(() => {
      showDeleteModal.value = false
      tabulatorToDelete.value = null
      // Reload or refresh data
      window.location.reload()
    })
    .catch(error => {
      console.error('Error removing tabulator:', error)
    })
    .finally(() => {
      deleteTabulatorProcessing.value = false
    })
}
</script> 