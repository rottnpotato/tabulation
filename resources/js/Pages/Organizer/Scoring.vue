<template>
  <div class="space-y-6">
    <div class="bg-white shadow-sm rounded-lg p-6">
      <h2 class="text-2xl font-semibold text-gray-900 mb-6">Scoring System</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Scoring Method Selection -->
        <div class="bg-white border rounded-lg p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Scoring Method</h3>
          <div class="space-y-4">
            <label class="flex items-center space-x-3 p-3 rounded-lg hover:bg-orange-50 transition-colors">
              <input
                type="radio"
                v-model="scoringMethod"
                value="points"
                class="h-4 w-4 text-orange-600 focus:ring-orange-500"
              />
              <div>
                <span class="font-medium text-gray-900">Points System</span>
                <p class="text-sm text-gray-600">Assign points from 1-100 for each criterion</p>
              </div>
            </label>
            <label class="flex items-center space-x-3 p-3 rounded-lg hover:bg-orange-50 transition-colors">
              <input
                type="radio"
                v-model="scoringMethod"
                value="rank"
                class="h-4 w-4 text-orange-600 focus:ring-orange-500"
              />
              <div>
                <span class="font-medium text-gray-900">Ranking System</span>
                <p class="text-sm text-gray-600">Rank contestants from 1st to last for each criterion</p>
              </div>
            </label>
            <label class="flex items-center space-x-3 p-3 rounded-lg hover:bg-orange-50 transition-colors">
              <input
                type="radio"
                v-model="scoringMethod"
                value="percentage"
                class="h-4 w-4 text-orange-600 focus:ring-orange-500"
              />
              <div>
                <span class="font-medium text-gray-900">Percentage System</span>
                <p class="text-sm text-gray-600">Score contestants as a percentage of perfection</p>
              </div>
            </label>
            <label class="flex items-center space-x-3 p-3 rounded-lg hover:bg-orange-50 transition-colors">
              <input
                type="radio"
                v-model="scoringMethod"
                value="scale"
                class="h-4 w-4 text-orange-600 focus:ring-orange-500"
              />
              <div>
                <span class="font-medium text-gray-900">Scale System</span>
                <p class="text-sm text-gray-600">Use a 1-5 or 1-10 scale with half-point increments</p>
              </div>
            </label>
          </div>
        </div>

        <!-- Judge Configuration -->
        <div class="bg-white border rounded-lg p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Judges</h3>
            <button
              @click="showAddJudgeModal = true"
              class="text-orange-600 hover:text-orange-700 font-medium flex items-center bg-orange-50 hover:bg-orange-100 px-3 py-1.5 rounded-lg transition-colors"
            >
              <Plus class="h-5 w-5 mr-1" />
              Add Judge
            </button>
          </div>
          <div class="space-y-3">
            <div
              v-for="judge in judges"
              :key="judge.id"
              class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-orange-50 transition-colors group"
            >
              <div class="flex items-center space-x-3">
                <UserCircle class="h-8 w-8 text-gray-400 group-hover:text-orange-500 transition-colors" />
                <div>
                  <p class="font-medium text-gray-900">{{ judge.name }}</p>
                  <p class="text-sm text-gray-600">{{ judge.title }}</p>
                </div>
              </div>
              <button
                @click="removeJudge(judge.id)"
                class="text-red-600 hover:text-red-700"
              >
                <X class="h-5 w-5" />
              </button>
            </div>
          </div>
        </div>

        <!-- Scoring Rules -->
        <div class="bg-white border rounded-lg p-6 md:col-span-2">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Scoring Rules</h3>
          <div class="space-y-4">
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-orange-50 transition-colors">
              <div>
                <h4 class="font-medium text-gray-900">Decimal Points</h4>
                <p class="text-sm text-gray-600">Allow decimal points in scoring</p>
              </div>
              <Switch
                v-model="rules.allowDecimals"
                class="relative inline-flex h-6 w-11 items-center rounded-full"
                :class="rules.allowDecimals ? 'bg-orange-600' : 'bg-gray-200'"
              >
                <span class="sr-only">Allow decimal points</span>
                <span
                  class="inline-block h-4 w-4 transform rounded-full bg-white transition"
                  :class="rules.allowDecimals ? 'translate-x-6' : 'translate-x-1'"
                />
              </Switch>
            </div>
            
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-orange-50 transition-colors">
              <div>
                <h4 class="font-medium text-gray-900">Tie Resolution</h4>
                <p class="text-sm text-gray-600">Enable automatic tie-breaking</p>
              </div>
              <Switch
                v-model="rules.tieBreaker"
                class="relative inline-flex h-6 w-11 items-center rounded-full"
                :class="rules.tieBreaker ? 'bg-orange-600' : 'bg-gray-200'"
              >
                <span class="sr-only">Enable tie-breaking</span>
                <span
                  class="inline-block h-4 w-4 transform rounded-full bg-white transition"
                  :class="rules.tieBreaker ? 'translate-x-6' : 'translate-x-1'"
                />
              </Switch>
            </div>

            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-orange-50 transition-colors">
              <div>
                <h4 class="font-medium text-gray-900">Judge Anonymity</h4>
                <p class="text-sm text-gray-600">Hide judge names from scoring results</p>
              </div>
              <Switch
                v-model="rules.judgeAnonymity"
                class="relative inline-flex h-6 w-11 items-center rounded-full"
                :class="rules.judgeAnonymity ? 'bg-orange-600' : 'bg-gray-200'"
              >
                <span class="sr-only">Enable judge anonymity</span>
                <span
                  class="inline-block h-4 w-4 transform rounded-full bg-white transition"
                  :class="rules.judgeAnonymity ? 'translate-x-6' : 'translate-x-1'"
                />
              </Switch>
            </div>

            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-orange-50 transition-colors">
              <div>
                <h4 class="font-medium text-gray-900">Round Weighting</h4>
                <p class="text-sm text-gray-600">Allow different rounds to have different weights</p>
              </div>
              <Switch
                v-model="rules.roundWeighting"
                class="relative inline-flex h-6 w-11 items-center rounded-full"
                :class="rules.roundWeighting ? 'bg-orange-600' : 'bg-gray-200'"
              >
                <span class="sr-only">Enable round weighting</span>
                <span
                  class="inline-block h-4 w-4 transform rounded-full bg-white transition"
                  :class="rules.roundWeighting ? 'translate-x-6' : 'translate-x-1'"
                />
              </Switch>
            </div>
          </div>
        </div>

        <!-- Scale Configuration (Conditionally Rendered) -->
        <div v-if="scoringMethod === 'scale'" class="bg-white border rounded-lg p-6 md:col-span-2">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Scale Configuration</h3>
          <div class="space-y-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Scale Range</label>
              <div class="flex items-center space-x-4">
                <label class="flex items-center space-x-2">
                  <input
                    type="radio"
                    v-model="scaleConfig.range"
                    value="5"
                    class="h-4 w-4 text-orange-600 focus:ring-orange-500"
                  />
                  <span class="text-gray-900">1-5 Scale</span>
                </label>
                <label class="flex items-center space-x-2">
                  <input
                    type="radio"
                    v-model="scaleConfig.range"
                    value="10"
                    class="h-4 w-4 text-orange-600 focus:ring-orange-500"
                  />
                  <span class="text-gray-900">1-10 Scale</span>
                </label>
                <label class="flex items-center space-x-2">
                  <input
                    type="radio"
                    v-model="scaleConfig.range"
                    value="custom"
                    class="h-4 w-4 text-orange-600 focus:ring-orange-500"
                  />
                  <span class="text-gray-900">Custom</span>
                </label>
              </div>
            </div>

            <div v-if="scaleConfig.range === 'custom'" class="grid grid-cols-2 gap-4">
              <div>
                <label for="min-value" class="block text-sm font-medium text-gray-700 mb-1">Minimum Value</label>
                <input
                  type="number"
                  id="min-value"
                  v-model="scaleConfig.minValue"
                  class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500"
                />
              </div>
              <div>
                <label for="max-value" class="block text-sm font-medium text-gray-700 mb-1">Maximum Value</label>
                <input
                  type="number"
                  id="max-value"
                  v-model="scaleConfig.maxValue"
                  class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500"
                />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Increment Options</label>
              <div class="flex items-center space-x-4">
                <label class="flex items-center space-x-2">
                  <input
                    type="radio"
                    v-model="scaleConfig.increment"
                    value="whole"
                    class="h-4 w-4 text-orange-600 focus:ring-orange-500"
                  />
                  <span class="text-gray-900">Whole Numbers Only</span>
                </label>
                <label class="flex items-center space-x-2">
                  <input
                    type="radio"
                    v-model="scaleConfig.increment"
                    value="half"
                    class="h-4 w-4 text-orange-600 focus:ring-orange-500"
                  />
                  <span class="text-gray-900">Allow Half Points</span>
                </label>
                <label class="flex items-center space-x-2">
                  <input
                    type="radio"
                    v-model="scaleConfig.increment"
                    value="decimal"
                    class="h-4 w-4 text-orange-600 focus:ring-orange-500"
                  />
                  <span class="text-gray-900">Allow Decimals</span>
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- Round Weights Configuration (Conditionally Rendered) -->
        <div v-if="rules.roundWeighting" class="bg-white border rounded-lg p-6 md:col-span-2">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Round Weighting</h3>
          <p class="text-sm text-gray-600 mb-4">Adjust the weight of each round in the final score calculation.</p>
          
          <div class="space-y-4">
            <div v-for="round in rounds" :key="round.id" class="flex items-center">
              <div class="w-1/3">
                <span class="font-medium text-gray-900">{{ round.name }}</span>
              </div>
              <div class="w-1/3 px-4">
                <input
                  type="range"
                  v-model="round.weight"
                  min="0"
                  max="100"
                  class="w-full accent-orange-500"
                />
              </div>
              <div class="w-1/3 flex items-center">
                <input
                  type="number"
                  v-model="round.weight"
                  min="0"
                  max="100"
                  class="w-16 rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500"
                />
                <span class="ml-2 text-gray-700">%</span>
              </div>
            </div>
          </div>

          <div class="mt-4 p-3 bg-orange-50 rounded-lg flex items-center justify-between">
            <span class="text-sm text-orange-700">Total weight must equal 100%</span>
            <span class="font-medium" :class="totalWeight === 100 ? 'text-green-600' : 'text-red-600'">
              {{ totalWeight }}%
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Judge Modal -->
    <TransitionRoot appear :show="showAddJudgeModal" as="template">
      <Dialog as="div" @close="showAddJudgeModal = false" class="relative z-10">
        <TransitionChild
          enter="ease-out duration-300"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="ease-in duration-200"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-black bg-opacity-25 backdrop-blur-sm" />
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
              <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all border border-orange-100">
                <div class="absolute top-0 right-0 pt-4 pr-4">
                  <button type="button" @click="showAddJudgeModal = false" class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-orange-500">
                    <span class="sr-only">Close</span>
                    <X class="h-6 w-6" />
                  </button>
                </div>

                <div class="bg-gradient-to-r from-orange-500 to-orange-700 -m-6 mb-6 p-6">
                  <DialogTitle as="h3" class="text-lg font-medium leading-6 text-white mb-1">
                    Add New Judge
                  </DialogTitle>
                  <p class="text-orange-100 text-sm">Enter the details of the judge to add to your panel</p>
                </div>

                <form @submit.prevent="handleAddJudge" class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Judge Name</label>
                    <input
                      type="text"
                      v-model="newJudge.name"
                      class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500"
                      required
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title/Position</label>
                    <input
                      type="text"
                      v-model="newJudge.title"
                      class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500"
                      required
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input
                      type="email"
                      v-model="newJudge.email"
                      class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500"
                      required
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Specialized Category</label>
                    <CustomSelect
                      v-model="newJudge.category"
                      :options="categoryOptions"
                      variant="orange"
                    />
                  </div>

                  <div class="mt-6 flex justify-end space-x-3">
                    <button
                      type="button"
                      @click="showAddJudgeModal = false"
                      class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                    >
                      Cancel
                    </button>
                    <button
                      type="submit"
                      class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-orange-500 to-orange-700 rounded-lg hover:from-orange-600 hover:to-orange-800 shadow-sm transition-all"
                    >
                      Add Judge
                    </button>
                  </div>
                </form>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- Scoring Analytics Component -->
    <ScoringAnalytics />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild, Switch } from '@headlessui/vue'
import { Plus, UserCircle, X, Settings } from 'lucide-vue-next'
import ScoringAnalytics from '../../components/scoring/ScoringAnalytics.vue'
import CustomSelect from '@/Components/CustomSelect.vue'
import OrganizerLayout from '@/Layouts/OrganizerLayout.vue'

defineOptions({
  layout: OrganizerLayout
})

const scoringMethod = ref('points')
const showAddJudgeModal = ref(false)

const rules = ref({
  allowDecimals: true,
  tieBreaker: true,
  judgeAnonymity: false,
  roundWeighting: false
})

const judges = ref([
  { id: 1, name: 'Dr. Sarah Wilson', title: 'Fashion Industry Expert' },
  { id: 2, name: 'Michael Chen', title: 'Celebrity Judge' },
  { id: 3, name: 'Amanda Rodriguez', title: 'Former Miss Universe' }
])

const newJudge = ref({
  name: '',
  title: '',
  email: '',
  category: ''
})

// Options for category select
const categoryOptions = [
  { value: '', label: 'All Categories' },
  { value: 'evening_gown', label: 'Evening Gown' },
  { value: 'swimsuit', label: 'Swimsuit' },
  { value: 'talent', label: 'Talent' },
  { value: 'qa', label: 'Q&A' }
]

const scaleConfig = ref({
  range: '10',
  minValue: 1,
  maxValue: 10,
  increment: 'half'
})

const rounds = ref([
  { id: 1, name: 'Evening Gown', weight: 25 },
  { id: 2, name: 'Swimsuit', weight: 25 },
  { id: 3, name: 'Talent', weight: 25 },
  { id: 4, name: 'Q&A', weight: 25 }
])

const totalWeight = computed(() => {
  return rounds.value.reduce((total, round) => total + Number(round.weight), 0)
})

const handleAddJudge = () => {
  judges.value.push({
    id: judges.value.length + 1,
    ...newJudge.value
  })
  showAddJudgeModal.value = false
  newJudge.value = { name: '', title: '', email: '', category: '' }
}

const removeJudge = (id) => {
  judges.value = judges.value.filter(judge => judge.id !== id)
}
</script>