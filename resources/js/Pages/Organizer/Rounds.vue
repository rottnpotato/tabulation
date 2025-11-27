<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Rounds Management</h1>
        <p class="text-gray-600 mt-2">
          Manage rounds and their scoring criteria for {{ pageant.name }}
        </p>
      </div>

      <!-- Add Round Button -->
      <div class="mb-6">
        <button
          @click="showAddRoundModal = true"
          class="inline-flex items-center px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-lg transition duration-150 ease-in-out"
        >
          <Plus class="w-5 h-5 mr-2" />
          Add Round
        </button>
      </div>

      <!-- Rounds List -->
      <div class="space-y-6">
        <div
          v-for="round in pageant.rounds"
          :key="round.id"
          class="bg-white rounded-lg shadow-sm border border-gray-200"
        >
          <!-- Round Header -->
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-4">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">
                    {{ round.name }}
                  </h3>
                  <p class="text-sm text-gray-500 mt-1">
                    {{ round.description || 'No description' }}
                  </p>
                  <div class="flex items-center space-x-4 mt-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-100 text-teal-800">
                      {{ round.type }}
                    </span>
                    <span class="text-sm text-gray-500">
                      Weight: {{ round.weight }}%
                    </span>
                    <span class="text-sm text-gray-500">
                      {{ round.criteria_count }} criteria
                    </span>
                  </div>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <button
                  @click="editRound(round)"
                  class="inline-flex items-center p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded-lg transition duration-150 ease-in-out"
                >
                  <Edit2 class="w-4 h-4" />
                </button>
                <button
                  @click="deleteRound(round)"
                  class="inline-flex items-center p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition duration-150 ease-in-out"
                >
                  <Trash2 class="w-4 h-4" />
                </button>
                <button
                  @click="toggleRoundCriteria(round.id!)"
                  class="inline-flex items-center p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded-lg transition duration-150 ease-in-out"
                >
                  <ChevronDown class="w-4 h-4 transform transition-transform duration-200" :class="{ 'rotate-180': expandedRounds.includes(round.id!) }" />
                </button>
              </div>
            </div>
          </div>

          <!-- Criteria Section -->
          <div v-show="expandedRounds.includes(round.id!)" class="p-6 bg-gray-50">
            <div class="flex items-center justify-between mb-4">
              <h4 class="text-md font-medium text-gray-900">Scoring Criteria</h4>
              <button
                @click="showAddCriteriaModal(round)"
                class="inline-flex items-center px-3 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-lg transition duration-150 ease-in-out"
              >
                <Plus class="w-4 h-4 mr-1" />
                Add Criteria
              </button>
            </div>

            <div v-if="round.criteria && round.criteria.length > 0" class="space-y-3">
              <div
                v-for="criteria in round.criteria"
                :key="criteria.id"
                class="bg-white rounded-lg border border-gray-200 p-4"
              >
                <div class="flex items-center justify-between">
                  <div class="flex-1">
                    <h5 class="font-medium text-gray-900">{{ criteria.name }}</h5>
                    <p class="text-sm text-gray-500 mt-1">{{ criteria.description || 'No description' }}</p>
                    <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500">
                      <span>Weight: {{ criteria.weight }}%</span>
                      <span>Score Range: {{ criteria.min_score }} - {{ criteria.max_score }}</span>
                      <span v-if="criteria.allow_decimals">Decimals: {{ criteria.decimal_places }} places</span>
                    </div>
                  </div>
                  <div class="flex items-center space-x-2">
                    <button
                      @click="editCriteria(round, criteria)"
                      class="inline-flex items-center p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded transition duration-150 ease-in-out"
                    >
                      <Edit2 class="w-3.5 h-3.5" />
                    </button>
                    <button
                      @click="deleteCriteria(round, criteria)"
                      class="inline-flex items-center p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded transition duration-150 ease-in-out"
                    >
                      <Trash2 class="w-3.5 h-3.5" />
                    </button>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8 text-gray-500">
              No criteria added yet. Click "Add Criteria" to get started.
            </div>
          </div>
        </div>
      </div>

      <div v-if="pageant.rounds.length === 0" class="text-center py-12">
        <h3 class="text-lg font-medium text-gray-900 mb-2">No rounds added yet</h3>
        <p class="text-gray-500 mb-4">Start by adding your first competition round.</p>
        <button
          @click="showAddRoundModal = true"
          class="inline-flex items-center px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-lg transition duration-150 ease-in-out"
        >
          <Plus class="w-5 h-5 mr-2" />
          Add First Round
        </button>
      </div>
    </div>

    <!-- Add/Edit Round Modal -->
    <Modal v-model:show="showAddRoundModal" title="Add Round" max-width="lg">
      <form @submit.prevent="submitRound" class="space-y-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Round Name</label>
          <input
            v-model="roundForm.name"
            type="text"
            placeholder="e.g. Evening Gown, Q&A, Production Number"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
            required
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
          <textarea
            v-model="roundForm.description"
            rows="3"
            placeholder="Brief description of this round"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
          ></textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
            <select
              v-model="roundForm.type"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
              required
            >
              <option value="semi-final">Semi-Final</option>
              <option value="final">Final</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Weight (%)</label>
            <input
              v-model.number="roundForm.weight"
              type="number"
              min="1"
              max="100"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
              required
            />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Display Order</label>
          <input
            v-model.number="roundForm.display_order"
            type="number"
            min="0"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
            required
          />
        </div>

        <div>
          <label class="flex items-center space-x-2 cursor-pointer">
            <input
              v-model="roundForm.use_for_minor_awards"
              type="checkbox"
              class="form-checkbox h-4 w-4 text-teal-600 rounded focus:ring-teal-500"
            />
            <span class="text-sm font-medium text-gray-700">Use for Minor Awards</span>
          </label>
          <p class="mt-1 ml-6 text-xs text-gray-500">
            Check this if you want to display winners from this round in the Minor Awards section
          </p>
        </div>

        <div class="flex justify-end space-x-3 pt-4">
          <button
            type="button"
            @click="cancelRoundForm"
            class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-150 ease-in-out"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="submitLoading"
            class="px-4 py-2 bg-teal-600 hover:bg-teal-700 disabled:opacity-50 text-white font-medium rounded-lg transition duration-150 ease-in-out"
          >
            {{ roundForm.id ? 'Update' : 'Add' }} Round
          </button>
        </div>
      </form>
    </Modal>

    <!-- Add/Edit Criteria Modal -->
    <Modal v-model:show="showAddCriteriaModalFlag" title="Add Criteria" max-width="lg">
      <form @submit.prevent="submitCriteria" class="space-y-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Criteria Name</label>
          <input
            v-model="criteriaForm.name"
            type="text"
            placeholder="e.g. Poise, Presentation, Overall Impact"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
            required
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
          <textarea
            v-model="criteriaForm.description"
            rows="3"
            placeholder="What judges should evaluate for this criteria"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
          ></textarea>
        </div>

        <div class="grid grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Weight (%)</label>
            <input
              v-model.number="criteriaForm.weight"
              type="number"
              min="1"
              max="100"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
              required
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Min Score</label>
            <input
              v-model.number="criteriaForm.min_score"
              type="number"
              min="0"
              step="0.01"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
              required
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Max Score</label>
            <input
              v-model.number="criteriaForm.max_score"
              type="number"
              min="1"
              step="0.01"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
              required
            />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="flex items-center">
              <input
                v-model="criteriaForm.allow_decimals"
                type="checkbox"
                class="form-checkbox h-4 w-4 text-teal-600"
              />
              <span class="ml-2 text-sm text-gray-700">Allow decimal scores</span>
            </label>
          </div>

          <div v-if="criteriaForm.allow_decimals">
            <label class="block text-sm font-medium text-gray-700 mb-2">Decimal Places</label>
            <input
              v-model.number="criteriaForm.decimal_places"
              type="number"
              min="0"
              max="4"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
              required
            />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Display Order</label>
          <input
            v-model.number="criteriaForm.display_order"
            type="number"
            min="0"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
            required
          />
        </div>

        <div class="flex justify-end space-x-3 pt-4">
          <button
            type="button"
            @click="cancelCriteriaForm"
            class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-150 ease-in-out"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="submitLoading"
            class="px-4 py-2 bg-teal-600 hover:bg-teal-700 disabled:opacity-50 text-white font-medium rounded-lg transition duration-150 ease-in-out"
          >
            {{ criteriaForm.id ? 'Update' : 'Add' }} Criteria
          </button>
        </div>
      </form>
    </Modal>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import { Plus, Edit2, Trash2, ChevronDown } from 'lucide-vue-next'
import Modal from '../../Components/Modal.vue'
import OrganizerLayout from '../../Layouts/OrganizerLayout.vue'

defineOptions({
  layout: OrganizerLayout
})

interface Criteria {
  id?: number
  name: string
  description: string
  weight: number
  min_score: number
  max_score: number
  allow_decimals: boolean
  decimal_places: number
  display_order: number
}

interface Round {
  id?: number
  name: string
  description: string
  type: string
  weight: number
  display_order: number
  is_active: boolean
  criteria: Criteria[]
  criteria_count: number
  top_n_proceed?: number
  use_for_minor_awards?: boolean
}

interface Pageant {
  id: number
  name: string
  description: string
  status: string
  rounds: Round[]
}

const props = defineProps<{
  pageant: Pageant
  pageantId: number
}>()

// State
const showAddRoundModal = ref(false)
const showAddCriteriaModalFlag = ref(false)
const expandedRounds = ref<number[]>([])
const submitLoading = ref(false)
const currentRound = ref<Round | null>(null)

// Forms
const roundForm = reactive({
  id: null as number | null,
  name: '',
  description: '',
  type: 'semi-final',
  weight: 100,
  display_order: 0,
  top_n_proceed: null as number | null,
  use_for_minor_awards: false,
})

const criteriaForm = reactive({
  id: null as number | null,
  name: '',
  description: '',
  weight: 100,
  min_score: 0,
  max_score: 100,
  allow_decimals: true,
  decimal_places: 2,
  display_order: 0,
})

// Methods
const toggleRoundCriteria = (roundId: number) => {
  const index = expandedRounds.value.indexOf(roundId)
  if (index > -1) {
    expandedRounds.value.splice(index, 1)
  } else {
    expandedRounds.value.push(roundId)
  }
}

const editRound = (round: Round) => {
  roundForm.id = round.id || null
  roundForm.name = round.name
  roundForm.description = round.description
  roundForm.type = round.type
  roundForm.weight = round.weight
  roundForm.display_order = round.display_order
  roundForm.top_n_proceed = round.top_n_proceed || null
  roundForm.use_for_minor_awards = round.use_for_minor_awards || false
  showAddRoundModal.value = true
}

const showAddCriteriaModal = (round: Round) => {
  currentRound.value = round
  criteriaForm.id = null
  criteriaForm.name = ''
  criteriaForm.description = ''
  criteriaForm.weight = 100
  criteriaForm.min_score = 0
  criteriaForm.max_score = 100
  criteriaForm.allow_decimals = true
  criteriaForm.decimal_places = 2
  criteriaForm.display_order = round.criteria ? round.criteria.length : 0
  showAddCriteriaModalFlag.value = true
}

const editCriteria = (round: Round, criteria: Criteria) => {
  currentRound.value = round
  criteriaForm.id = criteria.id || null
  criteriaForm.name = criteria.name
  criteriaForm.description = criteria.description
  criteriaForm.weight = criteria.weight
  criteriaForm.min_score = criteria.min_score
  criteriaForm.max_score = criteria.max_score
  criteriaForm.allow_decimals = criteria.allow_decimals
  criteriaForm.decimal_places = criteria.decimal_places
  criteriaForm.display_order = criteria.display_order
  showAddCriteriaModalFlag.value = true
}

const submitRound = async () => {
  submitLoading.value = true
  
  const url = roundForm.id 
    ? route('organizer.pageant.rounds.update', { pageantId: props.pageantId, roundId: roundForm.id })
    : route('organizer.pageant.rounds.store', { pageantId: props.pageantId })
    
  const method = roundForm.id ? 'put' : 'post'
  
  router[method](url, {
    name: roundForm.name,
    description: roundForm.description,
    type: roundForm.type,
    weight: roundForm.weight,
    display_order: roundForm.display_order,
    top_n_proceed: roundForm.top_n_proceed,
    use_for_minor_awards: roundForm.use_for_minor_awards,
  }, {
    onSuccess: () => {
      showAddRoundModal.value = false
      resetRoundForm()
    },
    onFinish: () => {
      submitLoading.value = false
    }
  })
}

const submitCriteria = async () => {
  if (!currentRound.value) return
  
  submitLoading.value = true
  
  const url = criteriaForm.id 
    ? route('organizer.pageant.rounds.criteria.update', { 
        pageantId: props.pageantId, 
        roundId: currentRound.value.id, 
        criteriaId: criteriaForm.id 
      })
    : route('organizer.pageant.rounds.criteria.store', { 
        pageantId: props.pageantId, 
        roundId: currentRound.value.id 
      })
      
  const method = criteriaForm.id ? 'put' : 'post'
  
  router[method](url, {
    name: criteriaForm.name,
    description: criteriaForm.description,
    weight: criteriaForm.weight,
    min_score: criteriaForm.min_score,
    max_score: criteriaForm.max_score,
    allow_decimals: criteriaForm.allow_decimals,
    decimal_places: criteriaForm.decimal_places,
    display_order: criteriaForm.display_order,
  }, {
    onSuccess: () => {
      showAddCriteriaModalFlag.value = false
      resetCriteriaForm()
    },
    onFinish: () => {
      submitLoading.value = false
    }
  })
}

const deleteRound = (round: Round) => {
  if (confirm(`Are you sure you want to delete "${round.name}"? This action cannot be undone.`)) {
    router.delete(route('organizer.pageant.rounds.destroy', { 
      pageantId: props.pageantId, 
      roundId: round.id 
    }))
  }
}

const deleteCriteria = (round: Round, criteria: Criteria) => {
  if (confirm(`Are you sure you want to delete "${criteria.name}"? This action cannot be undone.`)) {
    router.delete(route('organizer.pageant.rounds.criteria.destroy', { 
      pageantId: props.pageantId, 
      roundId: round.id,
      criteriaId: criteria.id 
    }))
  }
}

const cancelRoundForm = () => {
  showAddRoundModal.value = false
  resetRoundForm()
}

const cancelCriteriaForm = () => {
  showAddCriteriaModalFlag.value = false
  resetCriteriaForm()
}

const resetRoundForm = () => {
  roundForm.id = null
  roundForm.name = ''
  roundForm.description = ''
  roundForm.type = 'semi-final'
  roundForm.weight = 100
  roundForm.display_order = 0
  roundForm.top_n_proceed = null
  roundForm.use_for_minor_awards = false
}

const resetCriteriaForm = () => {
  criteriaForm.id = null
  criteriaForm.name = ''
  criteriaForm.description = ''
  criteriaForm.weight = 100
  criteriaForm.min_score = 0
  criteriaForm.max_score = 100
  criteriaForm.allow_decimals = true
  criteriaForm.decimal_places = 2
  criteriaForm.display_order = 0
  currentRound.value = null
}
</script>
