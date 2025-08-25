<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-100 ease-out"
      enter-from-class="transform scale-95 opacity-0"
      enter-to-class="transform scale-100 opacity-100"
      leave-active-class="transition duration-75 ease-in"
      leave-from-class="transform scale-100 opacity-100"
      leave-to-class="transform scale-95 opacity-0"
    >
      <div v-if="isVisible" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4">
          <!-- Background overlay -->
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="Close"></div>

          <!-- Modal panel -->
          <div class="w-full max-w-2xl bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all">
            <div class="absolute top-0 right-0 pt-4 pr-4">
              <button type="button" @click="Close" class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500">
                <span class="sr-only">Close</span>
                <X class="h-6 w-6" />
              </button>
            </div>
            
            <div class="bg-white p-6 sm:p-8">
              <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                  <Settings class="w-6 h-6 mr-2 text-purple-600" />
                  Pageant Settings
                </h2>
                <p class="text-gray-600">Configure your pageant preferences and options</p>
              </div>

              <div class="space-y-6">
                <!-- Tabs -->
                <div class="border-b border-gray-200">
                  <nav class="-mb-px flex space-x-6 overflow-x-auto pb-1" aria-label="Tabs">
                    <button 
                      v-for="tab in tabs" 
                      :key="tab.id"
                      @click="activeTab = tab.id"
                      :class="[
                        activeTab === tab.id 
                          ? 'border-purple-500 text-purple-600' 
                          : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                        'whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm flex items-center'
                      ]"
                    >
                      <component :is="tab.icon" class="mr-2 h-5 w-5" />
                      {{ tab.name }}
                    </button>
                  </nav>
                </div>

                <!-- General Settings Tab -->
                <div v-if="activeTab === 'general'" class="space-y-6">
                  <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                      <label for="pageant-name" class="block text-sm font-medium text-gray-700 mb-1">Pageant Name</label>
                      <input 
                        type="text" 
                        id="pageant-name" 
                        v-model="settings.name"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                      />
                    </div>
                    <div>
                      <label for="pageant-date" class="block text-sm font-medium text-gray-700 mb-1">Event Date</label>
                      <input 
                        type="datetime-local" 
                        id="pageant-date" 
                        v-model="settings.date"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                      />
                    </div>
                    <div>
                      <label for="pageant-venue" class="block text-sm font-medium text-gray-700 mb-1">Venue</label>
                      <input 
                        type="text" 
                        id="pageant-venue" 
                        v-model="settings.venue"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                      />
                    </div>
                    <div>
                      <label for="pageant-status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                      <select 
                        id="pageant-status" 
                        v-model="settings.status"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                      >
                        <option value="planning">Planning</option>
                        <option value="upcoming">Upcoming</option>
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                      </select>
                    </div>
                    <div class="sm:col-span-2">
                      <label for="pageant-description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                      <textarea 
                        id="pageant-description" 
                        v-model="settings.description"
                        rows="3"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                      ></textarea>
                    </div>
                  </div>
                </div>

                <!-- Notifications Tab -->
                <div v-if="activeTab === 'notifications'" class="space-y-6">
                  <h3 class="text-lg font-medium text-gray-900">Notification Preferences</h3>
                  <div class="space-y-4">
                    <div class="flex items-start">
                      <div class="flex items-center h-5">
                        <input 
                          id="notifications-contestants" 
                          type="checkbox" 
                          v-model="settings.notifications.contestants"
                          class="h-4 w-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                        />
                      </div>
                      <div class="ml-3 text-sm">
                        <label for="notifications-contestants" class="font-medium text-gray-700">Contestant Updates</label>
                        <p class="text-gray-500">Get notified when contestants update their profiles or new ones join</p>
                      </div>
                    </div>
                    <div class="flex items-start">
                      <div class="flex items-center h-5">
                        <input 
                          id="notifications-judges" 
                          type="checkbox" 
                          v-model="settings.notifications.judges"
                          class="h-4 w-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                        />
                      </div>
                      <div class="ml-3 text-sm">
                        <label for="notifications-judges" class="font-medium text-gray-700">Judge Activities</label>
                        <p class="text-gray-500">Receive alerts when judges submit scores or provide feedback</p>
                      </div>
                    </div>
                    <div class="flex items-start">
                      <div class="flex items-center h-5">
                        <input 
                          id="notifications-system" 
                          type="checkbox" 
                          v-model="settings.notifications.system"
                          class="h-4 w-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                        />
                      </div>
                      <div class="ml-3 text-sm">
                        <label for="notifications-system" class="font-medium text-gray-700">System Notifications</label>
                        <p class="text-gray-500">Important system alerts and updates about the platform</p>
                      </div>
                    </div>
                    <div class="flex items-start">
                      <div class="flex items-center h-5">
                        <input 
                          id="notifications-email" 
                          type="checkbox" 
                          v-model="settings.notifications.email"
                          class="h-4 w-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                        />
                      </div>
                      <div class="ml-3 text-sm">
                        <label for="notifications-email" class="font-medium text-gray-700">Email Notifications</label>
                        <p class="text-gray-500">Receive email copies of all important notifications</p>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Appearance Tab -->
                <div v-if="activeTab === 'appearance'" class="space-y-6">
                  <h3 class="text-lg font-medium text-gray-900">Appearance & Branding</h3>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Primary Color</label>
                    <div class="grid grid-cols-6 gap-2">
                      <button 
                        v-for="color in colorOptions" 
                        :key="color.value"
                        @click="settings.appearance.primaryColor = color.value"
                        :class="`h-8 w-8 rounded-full ${color.bg} hover:ring-2 hover:ring-offset-2 hover:ring-gray-500 flex items-center justify-center`"
                        :aria-selected="settings.appearance.primaryColor === color.value"
                      >
                        <Check v-if="settings.appearance.primaryColor === color.value" class="h-5 w-5 text-white" />
                      </button>
                    </div>
                  </div>
                  
                  <div>
                    <label for="logo-upload" class="block text-sm font-medium text-gray-700 mb-2">Pageant Logo</label>
                    <div class="flex items-center">
                      <div class="h-16 w-16 rounded-lg border border-gray-300 overflow-hidden mr-4 flex items-center justify-center bg-gray-100">
                        <img v-if="settings.appearance.logo" :src="settings.appearance.logo" alt="Pageant logo" class="h-full w-full object-cover" />
                        <Image v-else class="h-8 w-8 text-gray-400" />
                      </div>
                      <div>
                        <button type="button" class="bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                          Upload new logo
                        </button>
                        <p class="mt-1 text-xs text-gray-500">
                          PNG, JPG or SVG up to 2MB
                        </p>
                      </div>
                    </div>
                  </div>
                  
                  <div>
                    <label for="banner-style" class="block text-sm font-medium text-gray-700 mb-1">Banner Style</label>
                    <select 
                      id="banner-style" 
                      v-model="settings.appearance.bannerStyle"
                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                    >
                      <option value="gradient">Gradient</option>
                      <option value="solid">Solid Color</option>
                      <option value="image">Image Background</option>
                      <option value="minimal">Minimal</option>
                    </select>
                  </div>
                </div>

                <!-- Access Control Tab -->
                <div v-if="activeTab === 'access'" class="space-y-6">
                  <h3 class="text-lg font-medium text-gray-900">Access & Permissions</h3>
                  
                  <div class="space-y-4">
                    <div>
                      <label for="contestant-registration" class="block text-sm font-medium text-gray-700 mb-1">Contestant Registration</label>
                      <select 
                        id="contestant-registration" 
                        v-model="settings.access.contestantRegistration"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                      >
                        <option value="open">Open (Anyone can register)</option>
                        <option value="approval">Approval Required</option>
                        <option value="invite">Invitation Only</option>
                        <option value="closed">Closed</option>
                      </select>
                    </div>
                    
                    <div>
                      <label for="judge-access" class="block text-sm font-medium text-gray-700 mb-1">Judge Access</label>
                      <select 
                        id="judge-access" 
                        v-model="settings.access.judgeAccess"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                      >
                        <option value="all-rounds">All Scoring Rounds</option>
                        <option value="assigned-only">Assigned Rounds Only</option>
                        <option value="view-only">View Only Until Activated</option>
                      </select>
                    </div>
                    
                    <div>
                      <label for="results-visibility" class="block text-sm font-medium text-gray-700 mb-1">Results Visibility</label>
                      <select 
                        id="results-visibility" 
                        v-model="settings.access.resultsVisibility"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                      >
                        <option value="private">Organizers Only</option>
                        <option value="judges">Organizers and Judges</option>
                        <option value="contestants">All Participants</option>
                        <option value="public">Public</option>
                      </select>
                    </div>
                    
                    <div class="flex items-start">
                      <div class="flex items-center h-5">
                        <input 
                          id="enable-sms" 
                          type="checkbox" 
                          v-model="settings.access.enableSmsNotifications"
                          class="h-4 w-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                        />
                      </div>
                      <div class="ml-3 text-sm">
                        <label for="enable-sms" class="font-medium text-gray-700">Enable SMS Notifications</label>
                        <p class="text-gray-500">Send important updates to contestants and judges via SMS</p>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Add a new Scoring Criteria Tab -->
                <div v-if="activeTab === 'scoring'" class="space-y-6">
                  <h3 class="text-lg font-medium text-gray-900 mb-3">Scoring Criteria</h3>
                  <p class="text-sm text-gray-600 mb-4">Define the criteria judges will use to evaluate contestants</p>
                  
                  <div class="space-y-4">
                    <div v-for="(criterion, index) in settings.scoringCriteria" :key="index" 
                      class="bg-white border border-gray-200 rounded-lg p-4 group hover:border-purple-300 hover:shadow-sm transition-all">
                      <div class="flex items-start justify-between">
                        <div class="space-y-2 flex-grow">
                          <div class="flex items-center">
                            <input type="text" v-model="criterion.name" 
                              class="text-gray-900 font-medium bg-transparent border-0 focus:ring-0 p-0 w-full"
                              placeholder="Criterion Name" />
                          </div>
                          <textarea v-model="criterion.description" rows="2" 
                            class="w-full text-sm text-gray-600 bg-transparent border-0 focus:ring-0 p-0 resize-none"
                            placeholder="Description of this scoring criterion..."></textarea>
                          
                          <div class="flex items-center space-x-6 pt-2">
                            <div class="space-x-2 flex items-center">
                              <span class="text-xs text-gray-500">Weight:</span>
                              <input type="number" v-model="criterion.weight" min="1" max="100"
                                class="w-16 rounded-md border-gray-300 focus:border-purple-500 focus:ring-purple-500 text-sm" />
                              <span class="text-xs text-gray-500">%</span>
                            </div>
                            
                            <div class="space-x-2 flex items-center">
                              <span class="text-xs text-gray-500">Round:</span>
                              <div class="min-w-[120px]">
                                <CustomSelect
                                  v-model="criterion.round"
                                  :options="roundOptions"
                                  variant="purple"
                                  placeholder="Select Round"
                                />
                              </div>
                            </div>
                          </div>
                        </div>
                        
                        <div class="flex items-start space-x-2 mt-1">
                          <button @click="moveCriterionUp(index)" 
                            class="text-gray-400 hover:text-gray-600 p-1 rounded"
                            :disabled="index === 0"
                            :class="{ 'opacity-30 cursor-not-allowed': index === 0 }">
                            <ChevronUp class="h-4 w-4" />
                          </button>
                          <button @click="moveCriterionDown(index)" 
                            class="text-gray-400 hover:text-gray-600 p-1 rounded"
                            :disabled="index === settings.scoringCriteria.length - 1"
                            :class="{ 'opacity-30 cursor-not-allowed': index === settings.scoringCriteria.length - 1 }">
                            <ChevronDown class="h-4 w-4" />
                          </button>
                          <button @click="removeCriterion(index)" 
                            class="text-red-400 hover:text-red-600 p-1 rounded">
                            <Trash2 class="h-4 w-4" />
                          </button>
                        </div>
                      </div>
                    </div>
                    
                    <button @click="addCriterion" 
                      class="w-full py-3 border border-dashed border-gray-300 rounded-lg flex items-center justify-center text-purple-600 hover:text-purple-700 hover:border-purple-400 hover:bg-purple-50 transition-colors">
                      <Plus class="h-5 w-5 mr-2" />
                      <span>Add Scoring Criterion</span>
                    </button>
                  </div>
                  
                  <div v-if="totalCriteriaWeight !== 100" class="p-3 bg-amber-50 border border-amber-200 rounded-lg mt-4">
                    <div class="flex items-center">
                      <AlertTriangle class="h-5 w-5 text-amber-500 mr-2" />
                      <p class="text-sm text-amber-800">
                        Total weight should add up to 100%. Current total: <span class="font-medium">{{ totalCriteriaWeight }}%</span>
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mt-8 flex justify-end space-x-3">
                <button 
                  type="button" 
                  class="px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
                  @click="Close"
                >
                  Cancel
                </button>
                <button 
                  type="button" 
                  class="px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
                  @click="SaveSettings"
                >
                  Save Changes
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, defineProps, defineEmits, computed } from 'vue'
import { 
  Settings, X, Check, Image, Bell, Palette, Shield, ChevronUp, ChevronDown, Trash2, Plus, AlertTriangle
} from 'lucide-vue-next'
import CustomSelect from '../CustomSelect.vue'

const props = defineProps({
  isVisible: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'update'])

// Tabs configuration
const tabs = [
  { id: 'general', name: 'General', icon: Settings },
  { id: 'notifications', name: 'Notifications', icon: Bell },
  { id: 'appearance', name: 'Appearance', icon: Palette },
  { id: 'access', name: 'Access Control', icon: Shield },
  { id: 'scoring', name: 'Scoring Criteria', icon: AlertTriangle },
]

const activeTab = ref('general')

// Color options for appearance
const colorOptions = [
  { name: 'Purple', value: 'purple', bg: 'bg-purple-600' },
  { name: 'Indigo', value: 'indigo', bg: 'bg-indigo-600' },
  { name: 'Blue', value: 'blue', bg: 'bg-blue-600' },
  { name: 'Teal', value: 'teal', bg: 'bg-teal-600' },
  { name: 'Emerald', value: 'emerald', bg: 'bg-emerald-600' },
  { name: 'Pink', value: 'pink', bg: 'bg-pink-600' },
]

// Settings state
const settings = ref({
  name: 'Miss Universe 2025',
  date: '2025-05-15T19:00',
  venue: 'Grand Plaza Hotel, Crystal Ballroom',
  status: 'upcoming',
  description: 'The 74th Miss Universe pageant, featuring contestants from over 90 countries competing for the coveted crown.',
  notifications: {
    contestants: true,
    judges: true,
    system: true,
    email: false
  },
  appearance: {
    primaryColor: 'purple',
    logo: null,
    bannerStyle: 'gradient'
  },
  access: {
    contestantRegistration: 'approval',
    judgeAccess: 'all-rounds',
    resultsVisibility: 'private',
    enableSmsNotifications: false
  },
  scoringCriteria: [
    { 
      name: 'Beauty', 
      description: 'Overall beauty and grace of the contestant', 
      weight: 30,
      round: 'all'
    },
    { 
      name: 'Talent', 
      description: 'Quality and uniqueness of the talent performance', 
      weight: 30,
      round: 'talent'
    },
    { 
      name: 'Poise', 
      description: 'Confidence and elegance in presentation', 
      weight: 20,
      round: 'all'
    },
    { 
      name: 'Q&A Response', 
      description: 'Articulation, content and delivery of answers', 
      weight: 20,
      round: 'qa'
    }
  ]
})

// Close the modal
const Close = () => {
  emit('close')
}

// Save settings
const SaveSettings = () => {
  emit('update', settings.value)
  Close()
}

// Add scoring criteria management functions
const addCriterion = () => {
  settings.value.scoringCriteria.push({
    name: '',
    description: '',
    weight: 10,
    round: 'all'
  })
}

const removeCriterion = (index: number) => {
  settings.value.scoringCriteria.splice(index, 1)
}

const moveCriterionUp = (index: number) => {
  if (index > 0) {
    const temp = settings.value.scoringCriteria[index]
    settings.value.scoringCriteria[index] = settings.value.scoringCriteria[index - 1]
    settings.value.scoringCriteria[index - 1] = temp
  }
}

const moveCriterionDown = (index: number) => {
  if (index < settings.value.scoringCriteria.length - 1) {
    const temp = settings.value.scoringCriteria[index]
    settings.value.scoringCriteria[index] = settings.value.scoringCriteria[index + 1]
    settings.value.scoringCriteria[index + 1] = temp
  }
}

const totalCriteriaWeight = computed(() => {
  return settings.value.scoringCriteria.reduce((total, criterion) => total + Number(criterion.weight), 0)
})

// Round options for CustomSelect
const roundOptions = [
  { value: 'all', label: 'All Rounds' },
  { value: 'evening_gown', label: 'Evening Gown' },
  { value: 'swimsuit', label: 'Swimsuit' },
  { value: 'talent', label: 'Talent' },
  { value: 'qa', label: 'Q&A' }
]
</script>

<style scoped>
.settings-modal {
  @apply rounded-xl;
}

.settings-modal-header {
  @apply -m-6 mb-6 p-6 bg-gradient-to-r from-orange-500 to-rose-500 text-white rounded-t-xl;
}

.settings-modal-content {
  @apply p-6;
}

.settings-modal-footer {
  @apply border-t border-gray-200 p-6 flex justify-end space-x-3;
}

.settings-tabs {
  @apply flex mb-6 border-b border-gray-200;
}

.settings-tab {
  @apply px-4 py-2 text-sm font-medium border-b-2 -mb-px;
}

.settings-tab.active {
  @apply border-orange-500 text-orange-600;
}

.settings-tab:not(.active) {
  @apply border-transparent text-gray-500 hover:text-gray-700;
}

.form-group {
  @apply mb-4;
}

.form-label {
  @apply block text-sm font-medium text-gray-700 mb-1;
}

.form-input {
  @apply w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500;
}

.form-textarea {
  @apply w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500;
}

.btn-primary {
  @apply px-4 py-2 bg-gradient-to-r from-orange-500 to-rose-500 text-white font-medium rounded-lg hover:from-orange-600 hover:to-rose-600;
}

.btn-secondary {
  @apply px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200;
}
</style> 