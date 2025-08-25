<template>
  <div class="space-y-6">
    <!-- Delete Confirmation Modal -->
    <ConfirmDeleteModal
      :visible="showDeleteConfirm"
      :message="'this item'"
      :processing="deleteProcessing"
      @confirm="confirmDelete"
      @cancel="showDeleteConfirm = false"
    />
    
    <!-- Tabulator Delete Confirmation Modal -->
    <ConfirmDeleteModal
      :visible="showDeleteTabulatorConfirm"
      :message="selectedTabulator ? `tabulator '${selectedTabulator.name}'` : 'this tabulator'"
      :processing="deleteTabulatorProcessing"
      @confirm="removeTabulator"
      @cancel="showDeleteTabulatorConfirm = false"
    />
    
    <!-- Back Button and Page Header -->
    <div class="flex flex-col md:flex-row justify-between md:items-center space-y-3 md:space-y-0">
      <Link 
        :href="route('organizer.my-pageants')"
        class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium bg-white text-gray-700 hover:bg-gray-50 transition-colors btn-transition w-max"
      >
        <ChevronLeft class="h-4 w-4 mr-1.5" />
        Back to My Pageants
      </Link>
      
      <div class="flex items-center space-x-2">
        <Link
          v-if="canEdit"
          :href="route('organizer.pageant.edit', pageant.id)"
          class="inline-flex items-center px-3 py-2 rounded-md text-sm font-medium bg-orange-50 text-orange-700 hover:bg-orange-100 transition-colors border border-orange-200 btn-transition"
        >
          <Edit class="h-4 w-4 mr-1.5" />
          Edit Pageant
        </Link>
      </div>
    </div>
    
    <!-- Hero Section with Pageant Info -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
      <div class="relative h-48 sm:h-64 md:h-72">
        <!-- Cover Image with Fallback -->
        <img 
          :src="pageant.coverImage || '/images/placeholders/pageant-cover.jpg'" 
          alt="Pageant cover" 
          class="w-full h-full object-cover"
        />
        
        <!-- Gradient Overlay -->
        <div class="absolute inset-0 bg-gradient-to-r from-orange-900/70 via-orange-600/50 to-transparent"></div>
        
        <!-- Pageant Logo (if exists) -->
        <div v-if="pageant.logo" class="absolute top-4 right-4 h-16 w-16 sm:h-24 sm:w-24 md:h-32 md:w-32 bg-white/80 rounded-lg p-2 shadow-lg">
          <img :src="pageant.logo" alt="Pageant logo" class="w-full h-full object-contain" />
        </div>
        
        <!-- Pageant Info Overlay -->
        <div class="absolute bottom-0 left-0 w-full p-4 sm:p-6 md:p-8">
          <div class="flex flex-wrap gap-2 mb-2">
            <Tooltip :text="getStatusTooltipText(pageant.status)" position="top">
              <span :class="[
                getStatusClass(pageant.status).badge,
                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium hover:shadow-md transition-shadow cursor-help'
              ]">
                {{ pageant.status }}
              </span>
            </Tooltip>
          </div>
          
          <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-white mb-2">{{ pageant.name }}</h1>
          
          <p class="text-sm sm:text-base text-orange-50 max-w-2xl">
            {{ pageant.description || 'No description provided.' }}
          </p>
          
          <div class="mt-3 sm:mt-4 flex flex-wrap gap-x-4 gap-y-2">
            <div class="flex items-center text-white/90 text-xs sm:text-sm">
              <Calendar class="h-4 w-4 sm:h-5 sm:w-5 mr-1.5 text-orange-300" />
              {{ pageant.start_date || 'Date not set' }}
            </div>
            <div class="flex items-center text-white/90 text-xs sm:text-sm">
              <MapPin class="h-4 w-4 sm:h-5 sm:w-5 mr-1.5 text-orange-300" />
              {{ pageant.venue || pageant.location || 'Venue not specified' }}
            </div>
            <div class="flex items-center text-white/90 text-xs sm:text-sm">
              <Users class="h-4 w-4 sm:h-5 sm:w-5 mr-1.5 text-orange-300" />
              {{ pageant.contestants.length }} Contestants
            </div>
          </div>
        </div>
      </div>
      
      <!-- Tab Navigation -->
      <div class="border-b border-gray-200">
        <nav class="flex overflow-x-auto -mb-px">
          <button 
            v-for="tab in tabs" 
            :key="tab.id"
            :class="[
              activeTab === tab.id 
                ? 'border-orange-500 text-orange-600' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
              'whitespace-nowrap flex items-center py-4 px-4 sm:px-6 border-b-2 font-medium text-sm'
            ]"
            @click="activeTab = tab.id"
          >
            <component :is="tab.icon" 
              class="h-5 w-5 mr-2" 
              :class="activeTab === tab.id ? 'text-orange-500' : 'text-gray-400'" 
            />
            {{ tab.name }}
          </button>
        </nav>
      </div>
      
      <!-- Tab Content -->
      <div class="p-4 sm:p-6">
        <!-- Overview Tab -->
        <div v-if="activeTab === 'overview'" class="space-y-6">
          <!-- Pageant Progress -->
          <div class="bg-orange-50 rounded-lg p-4 sm:p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Pageant Progress</h3>
            
            <div class="mb-4">
              <div class="flex items-center justify-between text-sm text-gray-500 mb-1">
                <span>Overall Completion</span>
                <span class="font-medium">{{ pageant.progress || 0 }}%</span>
              </div>
              <Tooltip :text="getProgressTooltip(pageant.progress || 0)" position="top">
                <div class="w-full bg-gray-200 rounded-full h-2.5 hover:h-3 transition-all cursor-help">
                  <div
                    class="bg-orange-600 h-2.5 hover:h-3 rounded-full transition-all"
                    :style="{ width: `${pageant.progress || 0}%` }"
                  ></div>
                </div>
              </Tooltip>
            </div>
            
            <div v-if="isCompleted" class="flex items-center text-sm text-green-700 bg-green-50 p-3 rounded-md">
              <CheckCircle class="h-5 w-5 mr-2 text-green-500" />
              This pageant has been completed.
            </div>
            
            <div v-else-if="isActive" class="flex items-center text-sm text-blue-700 bg-blue-50 p-3 rounded-md">
              <Activity class="h-5 w-5 mr-2 text-blue-500" />
              This pageant is currently active and ongoing.
            </div>
            
            <div v-else-if="isDraft || isSetup" class="flex items-center text-sm text-amber-700 bg-amber-50 p-3 rounded-md">
              <AlertCircle class="h-5 w-5 mr-2 text-amber-500" />
              This pageant is still in {{ isDraft ? 'draft' : 'setup' }} mode and requires configuration.
            </div>
          </div>
          
          <!-- Status Change Section -->
          <div v-if="canEdit || isActive || (isCompleted && isAdmin) || isUnlockedForEdit" class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-visible">
            <div class="p-4 sm:p-6">
              <h4 class="text-base font-medium text-gray-900 mb-4">Pageant Status</h4>
              <p class="text-sm text-gray-500 mb-4">
                Update the status of your pageant as it progresses through different phases.
              </p>
              
              <!-- Auto-completion warning -->
              <div v-if="isPageantDateElapsed && !isCompleted && !isUnlockedForEdit" class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-md">
                <div class="flex items-start">
                  <AlertCircle class="h-5 w-5 text-amber-500 mt-0.5 mr-2 flex-shrink-0" />
                  <div>
                    <h5 class="text-sm font-medium text-amber-800">Auto-completion Required</h5>
                    <p class="text-xs text-amber-700 mt-1">
                      This pageant's date has elapsed ({{ pageant.start_date }}). It should be marked as completed to finalize results.
                    </p>
                  </div>
                </div>
              </div>
              
              <!-- Admin-only notice -->
              <div v-if="!isAdmin && (isCompleted || isUnlockedForEdit)" class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
                <div class="flex items-start">
                  <Info class="h-5 w-5 text-blue-500 mt-0.5 mr-2 flex-shrink-0" />
                  <div>
                    <h5 class="text-sm font-medium text-blue-800">Administrative Status</h5>
                    <p class="text-xs text-blue-700 mt-1">
                      Only administrators can modify the status of completed pageants or unlock them for editing.
                    </p>
                  </div>
                </div>
              </div>
              
              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <span class="text-sm text-gray-600 mr-3">Current Status:</span>
                  <span :class="[
                    getStatusClass(pageant.status).badge,
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium'
                  ]">
                    {{ pageant.status }}
                  </span>
                </div>
                
                <div class="flex items-center space-x-3">
                  <div v-if="getAvailableStatusTransitions().length > 0">
                    <label class="block text-sm font-medium text-gray-700">Change to:</label>
                    <div class="mt-1 w-full">
                      <CustomSelect
                        v-model="selectedNewStatus"
                        :options="getAvailableStatusTransitions()"
                        placeholder="Select new status"
                        variant="orange"
                      />
                    </div>
                  </div>
                  
                  <button 
                    v-if="selectedNewStatus && getAvailableStatusTransitions().length > 0"
                    @click="updateStatus"
                    :disabled="statusUpdateForm.processing"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                  >
                    <AlertCircle class="h-4 w-4 mr-1.5" />
                    {{ statusUpdateForm.processing ? 'Updating...' : 'Update Status' }}
                  </button>
                  
                  <div v-else-if="getAvailableStatusTransitions().length === 0" class="text-sm text-gray-500">
                    {{ isCompleted && !isAdmin ? 'Completed pageants can only be modified by administrators.' : 'No status changes available from current status.' }}
                  </div>
                </div>
              </div>
              
              <!-- Status transition help -->
              <div v-if="selectedNewStatus" class="mt-3 p-3 bg-blue-50 rounded-md">
                <p class="text-sm text-blue-700">
                  <strong>{{ pageant.status }} → {{ selectedNewStatus }}:</strong>
                  {{ getStatusTransitionHelp(pageant.status, selectedNewStatus) }}
                </p>
              </div>
            </div>
          </div>
          
          <!-- Completed Pageant Status (Non-Admin View) -->
          <div v-if="isCompleted && !isAdmin" class="bg-gray-50 rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-4 sm:p-6">
              <h4 class="text-base font-medium text-gray-900 mb-4">Pageant Status</h4>
              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <span class="text-sm text-gray-600 mr-3">Current Status:</span>
                  <span :class="[
                    getStatusClass(pageant.status).badge,
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium'
                  ]">
                    {{ pageant.status }}
                  </span>
                </div>
                <div class="text-sm text-gray-500">
                  <CheckCircle class="h-4 w-4 inline mr-1" />
                  Pageant completed
                </div>
              </div>
              <div class="mt-3 p-3 bg-blue-50 rounded-md">
                <div class="flex items-start">
                  <Info class="h-5 w-5 text-blue-500 mt-0.5 mr-2 flex-shrink-0" />
                  <div>
                    <p class="text-sm text-blue-700">
                      <strong>This pageant has been completed.</strong> 
                      Only administrators can unlock completed pageants for editing or make status changes.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Stats Cards -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Contestants Card -->
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
              <div class="p-4">
                <div class="flex items-center justify-between">
                  <h3 class="text-lg font-semibold text-gray-900">Contestants</h3>
                  <div class="p-2 bg-orange-100 rounded-full">
                    <Users class="h-5 w-5 text-orange-600" />
                  </div>
                </div>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ pageant.contestants.length }}</p>
                <p class="text-sm text-gray-500">Total contestants registered</p>
              </div>
              <div class="border-t border-gray-100 bg-gray-50 px-4 py-3">
                <Link 
                  :href="route('organizer.pageant.contestants-management', pageant.id)"
                  class="text-sm font-medium text-orange-600 hover:text-orange-800 flex items-center"
                >
                  Manage Contestants <ChevronRight class="h-4 w-4 ml-1" />
                </Link>
              </div>
            </div>
            
            <!-- Criteria Card -->
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
              <div class="p-4">
                <div class="flex items-center justify-between">
                  <h3 class="text-lg font-semibold text-gray-900">Criteria</h3>
                  <div class="p-2 bg-orange-100 rounded-full">
                    <ListChecks class="h-5 w-5 text-orange-600" />
                  </div>
                </div>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ pageant.criteria.length }}</p>
                <p class="text-sm text-gray-500">Scoring criteria defined</p>
              </div>
              <div class="border-t border-gray-100 bg-gray-50 px-4 py-3">
                <Link 
                  :href="route('organizer.pageant.criteria-management', pageant.id)"
                  class="text-sm font-medium text-orange-600 hover:text-orange-800 flex items-center"
                >
                  Manage Criteria <ChevronRight class="h-4 w-4 ml-1" />
                </Link>
              </div>
            </div>
            
            <!-- Judges Card -->
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
              <div class="p-4">
                <div class="flex items-center justify-between">
                  <h3 class="text-lg font-semibold text-gray-900">Judges</h3>
                  <div class="p-2 bg-orange-100 rounded-full">
                    <Scale class="h-5 w-5 text-orange-600" />
                  </div>
                </div>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ pageant.judges.length }}</p>
                <p class="text-sm text-gray-500">Assigned judges</p>
              </div>
              <div class="border-t border-gray-100 bg-gray-50 px-4 py-3">
                <Link 
                  :href="route('organizer.pageant.judges-management', pageant.id)"
                  class="text-sm font-medium text-orange-600 hover:text-orange-800 flex items-center"
                >
                  Manage Judges <ChevronRight class="h-4 w-4 ml-1" />
                </Link>
              </div>
            </div>
          </div>
        </div>
        


        
        <!-- Contestants Tab -->
        <div v-else-if="activeTab === 'contestants'" class="space-y-6">
          <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">Contestants</h3>
            <div v-if="canEdit">
              <Link 
                :href="route('organizer.pageant.contestants-management', pageant.id)"
                class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium bg-white text-gray-700 hover:bg-gray-50 btn-transition"
              >
                <Plus class="h-4 w-4 mr-1.5" />
                Manage Contestants
              </Link>
            </div>
          </div>
          
          <!-- Empty State -->
          <div v-if="!pageant.contestants || pageant.contestants.length === 0" class="bg-gray-50 rounded-lg py-12 px-4 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
              <Users class="h-8 w-8 text-gray-400" />
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-1">No Contestants Added</h3>
            <p class="text-gray-500 max-w-md mx-auto">
              No contestants have been added to this pageant yet.
              {{ canEdit ? 'Click the "Manage Contestants" button to add your first contestant.' : '' }}
            </p>
          </div>
          
          <!-- Contestants Grid -->
          <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div 
              v-for="contestant in pageant.contestants" 
              :key="contestant.id"
              class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden hover:shadow-md transition-shadow"
            >
              <div class="aspect-[3/4] bg-gray-100 relative">
                <img 
                  :src="contestant.photo || '/images/placeholders/contestant.jpg'" 
                  :alt="contestant.name"
                  class="w-full h-full object-cover object-center"
                />
                <div class="absolute top-2 left-2 bg-white rounded-full h-8 w-8 flex items-center justify-center shadow-md">
                  <span class="font-bold text-orange-600">{{ contestant.number || '?' }}</span>
                </div>
              </div>
              <div class="p-4">
                <h4 class="font-medium text-gray-900">{{ contestant.name }}</h4>
                <div class="mt-1 flex items-center justify-between text-sm text-gray-500">
                  <span>{{ contestant.age ? `${contestant.age} years` : 'Age not specified' }}</span>
                  <span>{{ contestant.origin || 'Unknown origin' }}</span>
                </div>
                <div v-if="canEdit" class="mt-3 flex justify-end space-x-2">
                  <Tooltip text="Edit contestant information" position="top">
                    <button class="p-1 rounded-md text-gray-400 hover:text-orange-600 hover:bg-orange-50 transition-all transform hover:scale-110">
                      <Edit class="h-4 w-4" />
                    </button>
                  </Tooltip>
                  <Tooltip text="Remove contestant from pageant" position="top">
                    <button class="p-1 rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50 transition-all transform hover:scale-110">
                      <Trash class="h-4 w-4" />
                    </button>
                  </Tooltip>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Criteria Tab -->
        <div v-else-if="activeTab === 'criteria'" class="space-y-6">
          <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">Scoring Criteria</h3>
            <div v-if="canEdit">
              <Link 
                :href="route('organizer.pageant.criteria-management', pageant.id)"
                class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium bg-white text-gray-700 hover:bg-gray-50 btn-transition"
              >
                <Plus class="h-4 w-4 mr-1.5" />
                Manage Criteria
              </Link>
            </div>
          </div>
          
          <!-- Empty State -->
          <div v-if="!pageant.criteria || pageant.criteria.length === 0" class="bg-gray-50 rounded-lg py-12 px-4 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
              <ListChecks class="h-8 w-8 text-gray-400" />
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-1">No Criteria Defined</h3>
            <p class="text-gray-500 max-w-md mx-auto">
              No scoring criteria have been defined for this pageant yet.
              {{ canEdit ? 'Click the "Manage Criteria" button to set up your scoring system.' : '' }}
            </p>
          </div>
          
          <!-- Criteria List -->
          <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div 
              v-for="criterion in pageant.criteria" 
              :key="criterion.id"
              class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden hover:shadow-md transition-shadow group"
            >
              <div class="p-4">
                <div class="flex items-center justify-between">
                  <h4 class="font-medium text-gray-900 group-hover:text-orange-600">{{ criterion.name }}</h4>
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                    {{ criterion.weight }}%
                  </span>
                </div>
                <p class="mt-1 text-sm text-gray-500">
                  {{ criterion.description || 'No description provided' }}
                </p>
                <div class="mt-3 flex items-center text-xs text-gray-500">
                  <span>Score Range: </span>
                  <span class="font-medium ml-1">{{ criterion.min_score || 0 }} - {{ criterion.max_score || 10 }}</span>
                </div>
                <div v-if="canEdit" class="mt-3 flex justify-end space-x-2">
                  <Tooltip text="Edit scoring criterion details" position="top">
                    <button class="p-1 rounded-md text-gray-400 hover:text-orange-600 hover:bg-orange-50 transition-all transform hover:scale-110">
                      <Edit class="h-4 w-4" />
                    </button>
                  </Tooltip>
                  <Tooltip text="Remove this scoring criterion" position="top">
                    <button class="p-1 rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50 transition-all transform hover:scale-110">
                      <Trash class="h-4 w-4" />
                    </button>
                  </Tooltip>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Judges Tab -->
        <div v-else-if="activeTab === 'judges'" class="space-y-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Judges & Tabulators</h3>
          </div>
          
          <!-- Required Judges Setting Section -->
          <div v-if="canEdit" class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-4 sm:p-6">
              <h4 class="text-base font-medium text-gray-900 mb-4">Set Required Judges</h4>
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
                    <Save class="h-4 w-4 mr-1.5" />
                    Save
                  </button>
                </div>
              </form>
            </div>
          </div>
          
          <!-- Scoring System Section -->
          <div v-if="canEdit" class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-4 sm:p-6">
              <h4 class="text-base font-medium text-gray-900 mb-4">Scoring System</h4>
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
                    <Save class="h-4 w-4 mr-1.5" />
                    Save Scoring System
                  </button>
                </div>
              </form>
            </div>
          </div>
          
          <!-- Tabulator Assignment Section -->
          <div v-if="canEdit" class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-visible">
            <div class="p-4 sm:p-6">
              <h4 class="text-base font-medium text-gray-900 mb-4">Assign Tabulator</h4>
              <p class="text-sm text-gray-500 mb-4">
                Assign a tabulator to this pageant. Tabulators are responsible for creating judge accounts and managing the scoring process.
              </p>
              
              <form @submit.prevent="assignTabulator" class="space-y-4">
                <div>
                  <label for="tabulatorId" class="block text-sm font-medium text-gray-700">Select Tabulator</label>
                  <div class="mt-1">
                    <CustomSelect
                      v-model="tabulatorForm.tabulator_id"
                      :options="tabulatorOptions"
                      variant="orange"
                      placeholder="Select a tabulator"
                    />
                  </div>
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
                    <Save class="h-4 w-4 mr-1.5" />
                    Assign Tabulator
                  </button>
                </div>
              </form>
            </div>
          </div>
          
          <!-- Assigned Tabulators Section -->
          <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-4 sm:p-6">
              <h4 class="text-base font-medium text-gray-900 mb-4">Assigned Tabulators</h4>
              
              <!-- Empty State for Tabulators -->
              <div v-if="!pageant.tabulators || pageant.tabulators.length === 0" class="bg-gray-50 rounded-lg py-6 px-4 text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 mb-3">
                  <Calculator class="h-6 w-6 text-gray-400" />
                </div>
                <h5 class="text-base font-medium text-gray-900 mb-1">No Tabulators Assigned</h5>
                <p class="text-sm text-gray-500 max-w-md mx-auto">
                  No tabulators have been assigned to this pageant yet. 
                  {{ canEdit ? 'Use the form above to assign a tabulator.' : '' }}
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
                      <h5 class="font-medium text-gray-900">{{ tabulator.name }}</h5>
                      <p class="text-sm text-gray-500">@{{ tabulator.username }}</p>
                    </div>
                  </div>
                  <div v-if="canEdit">
                    <Tooltip text="Remove tabulator from this pageant" position="left">
                      <button 
                        @click="confirmRemoveTabulator(tabulator)"
                        class="p-2 rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50 transition-all transform hover:scale-110"
                      >
                        <Trash class="h-5 w-5" />
                      </button>
                    </Tooltip>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Judges Section -->
          <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-4 sm:p-6">
              <div class="flex justify-between items-center mb-4">
                <h4 class="text-base font-medium text-gray-900">Judges</h4>
                <div class="flex items-center">
                  <span class="text-sm text-gray-500 mr-2">Required: {{ pageant.required_judges }}</span>
                  <span class="text-sm text-gray-500">Assigned: {{ pageant.judges.length }}</span>
                </div>
              </div>
              
              <!-- Empty State for Judges -->
              <div v-if="!pageant.judges || pageant.judges.length === 0" class="bg-gray-50 rounded-lg py-6 px-4 text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 mb-3">
                  <Scale class="h-6 w-6 text-gray-400" />
                </div>
                <h5 class="text-base font-medium text-gray-900 mb-1">No Judges Assigned</h5>
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
                      <h5 class="font-medium text-gray-900">{{ judge.name }}</h5>
                      <p class="text-sm text-gray-500">@{{ judge.username }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Scoring System Tab -->
        <div v-else-if="activeTab === 'scoring'" class="space-y-6">
          <h3 class="text-lg font-semibold text-gray-900">Scoring System</h3>
          
          <!-- Current Scoring System Card -->
          <div class="bg-orange-50 rounded-lg p-6">
            <div class="flex items-start">
              <Calculator class="h-6 w-6 text-orange-500 mt-0.5" />
              <div class="ml-3">
                <h4 class="text-base font-medium text-gray-900">{{ getScoringSystemName(pageant.scoring_system) }}</h4>
                <p class="text-sm text-gray-500 mt-1">
                  {{ getScoringSystemDescription(pageant.scoring_system) }}
                </p>
                <button 
                  @click="toggleScoringDetails(pageant.scoring_system)"
                  class="mt-2 text-sm font-medium text-orange-600 hover:text-orange-800 inline-flex items-center"
                >
                  {{ showScoringDetails && selectedScoringSystem === pageant.scoring_system ? 'Hide details' : 'View details' }}
                  <ChevronRight v-if="!(showScoringDetails && selectedScoringSystem === pageant.scoring_system)" class="h-4 w-4 ml-1" />
                  <ChevronDown v-else class="h-4 w-4 ml-1" />
                </button>
              </div>
            </div>
            
            <!-- Expanded Details for Current System -->
            <div 
              v-if="showScoringDetails && selectedScoringSystem === pageant.scoring_system" 
              class="mt-4 border-t border-orange-100 pt-4"
            >
              <h5 class="text-sm font-medium text-gray-900 mb-2">Detailed Information</h5>
              <p class="text-sm text-gray-600 mb-4">{{ currentScoringSystem.details }}</p>
              
              <div class="grid grid-cols-1 gap-4">
                <div>
                  <h6 class="text-xs font-semibold uppercase text-gray-500 tracking-wider mb-2">Advantages</h6>
                  <ul class="space-y-1 text-sm text-gray-600">
                    <li v-for="(pro, index) in currentScoringSystem.pros" :key="index" class="flex items-start">
                      <CheckCircle class="h-4 w-4 text-green-500 mt-0.5 mr-1.5 flex-shrink-0" />
                      {{ pro }}
                    </li>
                  </ul>
                </div>
                <div>
                  <h6 class="text-xs font-semibold uppercase text-gray-500 tracking-wider mb-2">Disadvantages</h6>
                  <ul class="space-y-1 text-sm text-gray-600">
                    <li v-for="(con, index) in currentScoringSystem.cons" :key="index" class="flex items-start">
                      <AlertCircle class="h-4 w-4 text-red-500 mt-0.5 mr-1.5 flex-shrink-0" />
                      {{ con }}
                    </li>
                  </ul>
                </div>
              </div>
              
              <div class="mt-3">
                <h6 class="text-xs font-semibold uppercase text-gray-500 tracking-wider mb-2">Best Used For</h6>
                <p class="text-sm text-gray-600 mb-3">{{ currentScoringSystem.bestFor }}</p>
                
                <h6 class="text-xs font-semibold uppercase text-gray-500 tracking-wider mb-2">Scoring Method</h6>
                <p class="text-sm text-gray-600">{{ currentScoringSystem.scoringMethod }}</p>
              </div>
            </div>
            
            <div class="mt-4 border-t border-orange-100 pt-4">
              <h5 class="text-sm font-medium text-gray-900 mb-2">Scoring Method</h5>
              <ul class="space-y-2 text-sm text-gray-600">
                <li class="flex items-start">
                  <CheckCircle class="h-4 w-4 text-orange-500 mt-0.5 mr-2" />
                  Judges score each contestant on every criterion using the specified range
                </li>
                <li class="flex items-start">
                  <CheckCircle class="h-4 w-4 text-orange-500 mt-0.5 mr-2" />
                  Scores are weighted according to each criterion's importance
                </li>
                <li class="flex items-start">
                  <CheckCircle class="h-4 w-4 text-orange-500 mt-0.5 mr-2" />
                  Highest and lowest scores may be dropped to prevent bias
                </li>
                <li class="flex items-start">
                  <CheckCircle class="h-4 w-4 text-orange-500 mt-0.5 mr-2" />
                  Final ranking is determined by total weighted scores
                </li>
              </ul>
            </div>
          </div>
          
          <!-- Choose Scoring System Section (for editable pageants) -->
          <div v-if="canEdit" class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-4 sm:p-6">
              <h4 class="text-base font-medium text-gray-900 mb-4">Change Scoring System</h4>
              <p class="text-sm text-gray-500 mb-4">
                You can change the scoring system for this pageant. This will determine how judges score contestants across all criteria.
              </p>
              
              <form @submit.prevent="updateScoringSystem" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div 
                    v-for="system in scoringSystems" 
                    :key="system.type" 
                    class="relative border rounded-lg p-4 cursor-pointer hover:bg-orange-50 transition-colors"
                    :class="scoringSystemForm.scoring_system === system.type ? 'border-orange-500 bg-orange-50 ring-2 ring-orange-200' : 'border-gray-300'"
                  >
                    <div class="flex flex-col">
                      <!-- Radio button and basic info -->
                      <div class="flex items-start">
                        <div class="flex items-center h-5">
                          <input 
                            type="radio" 
                            :id="system.type" 
                            :value="system.type" 
                            v-model="scoringSystemForm.scoring_system"
                            class="h-4 w-4 text-orange-600 border-gray-300 focus:ring-orange-500"
                            @click="selectedScoringSystem = system.type"
                          />
                        </div>
                        <div class="ml-3 text-sm">
                          <label :for="system.type" class="font-medium text-gray-900">{{ system.name }}</label>
                          <p class="text-gray-500">{{ system.description }}</p>
                        </div>
                      </div>
                      
                      <!-- Details toggle button -->
                      <div class="ml-7 mt-2">
                        <button 
                          type="button"
                          @click.prevent="toggleScoringDetails(system.type)"
                          class="text-sm font-medium text-orange-600 hover:text-orange-800 inline-flex items-center"
                        >
                          {{ showScoringDetails && selectedScoringSystem === system.type ? 'Hide details' : 'View details' }}
                          <ChevronRight v-if="!(showScoringDetails && selectedScoringSystem === system.type)" class="h-4 w-4 ml-1" />
                          <ChevronDown v-else class="h-4 w-4 ml-1" />
                        </button>
                      </div>
                      
                      <!-- Expanded system details -->
                      <div 
                        v-if="showScoringDetails && selectedScoringSystem === system.type" 
                        class="mt-3 ml-7 pt-3 border-t border-gray-100"
                      >
                        <p class="text-sm text-gray-600 mb-3">{{ system.details }}</p>
                        
                        <div class="grid grid-cols-1 gap-4">
                          <div>
                            <h6 class="text-xs font-semibold uppercase text-gray-500 tracking-wider mb-2">Advantages</h6>
                            <ul class="space-y-1 text-sm text-gray-600">
                              <li v-for="(pro, index) in system.pros" :key="index" class="flex items-start">
                                <CheckCircle class="h-4 w-4 text-green-500 mt-0.5 mr-1.5 flex-shrink-0" />
                                {{ pro }}
                              </li>
                            </ul>
                          </div>
                          <div>
                            <h6 class="text-xs font-semibold uppercase text-gray-500 tracking-wider mb-2">Disadvantages</h6>
                            <ul class="space-y-1 text-sm text-gray-600">
                              <li v-for="(con, index) in system.cons" :key="index" class="flex items-start">
                                <AlertCircle class="h-4 w-4 text-red-500 mt-0.5 mr-1.5 flex-shrink-0" />
                                {{ con }}
                              </li>
                            </ul>
                          </div>
                        </div>
                        
                        <div class="mt-3">
                          <h6 class="text-xs font-semibold uppercase text-gray-500 tracking-wider mb-2">Best Used For</h6>
                          <p class="text-sm text-gray-600 mb-3">{{ system.bestFor }}</p>
                          
                          <h6 class="text-xs font-semibold uppercase text-gray-500 tracking-wider mb-2">Scoring Method</h6>
                          <p class="text-sm text-gray-600">{{ system.scoringMethod }}</p>
                        </div>
                      </div>
                    </div>
                    
                    <!-- Selected indicator -->
                    <div v-if="scoringSystemForm.scoring_system === system.type" class="absolute top-2 right-2 text-orange-600">
                      <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                      </svg>
                    </div>
                  </div>
                </div>
                
                <!-- Comparison table for all systems -->
                <div class="mt-6 border-t border-gray-200 pt-6">
                  <h5 class="text-sm font-medium text-gray-900 mb-3">Scoring System Comparison</h5>
                  
                  <div class="border rounded-lg border-gray-300 overflow-hidden">
                    <div class="overflow-x-auto">
                      <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                          <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Feature</th>
                            <th v-for="system in scoringSystems" :key="system.type" scope="col" 
                              class="px-3 py-3.5 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider"
                              :class="scoringSystemForm.scoring_system === system.type ? 'bg-orange-50 text-orange-700' : ''"
                            >
                              {{ system.name }}
                            </th>
                          </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                          <tr>
                            <td class="py-3 pl-4 pr-3 text-xs font-medium text-gray-700">Scoring Range</td>
                            <td v-for="system in scoringSystems" :key="`range-${system.type}`" class="px-3 py-3 text-center text-xs text-gray-600"
                              :class="scoringSystemForm.scoring_system === system.type ? 'bg-orange-50' : ''"
                            >
                              {{ system.type === 'percentage' ? '0-100%' : 
                                 system.type === '1-10' ? '1-10 points' : 
                                 system.type === '1-5' ? '1-5 points' : 
                                 'Ranking based' }}
                            </td>
                          </tr>
                          <tr>
                            <td class="py-3 pl-4 pr-3 text-xs font-medium text-gray-700">Scoring Precision</td>
                            <td v-for="system in scoringSystems" :key="`precision-${system.type}`" class="px-3 py-3 text-center text-xs text-gray-600"
                              :class="scoringSystemForm.scoring_system === system.type ? 'bg-orange-50' : ''"
                            >
                              {{ system.type === 'percentage' ? 'Very High' : 
                                 (system.type === '1-10' ? 'High' : 
                                 (system.type === '1-5' ? 'Medium' : 
                                 'Low (Rank-based)')) }}
                            </td>
                          </tr>
                          <tr>
                            <td class="py-3 pl-4 pr-3 text-xs font-medium text-gray-700">Scoring Speed</td>
                            <td v-for="system in scoringSystems" :key="`speed-${system.type}`" class="px-3 py-3 text-center text-xs text-gray-600"
                              :class="scoringSystemForm.scoring_system === system.type ? 'bg-orange-50' : ''"
                            >
                              {{ system.type === 'percentage' ? 'Moderate' : 
                                 (system.type === '1-10' ? 'Fast' : 
                                 (system.type === '1-5' ? 'Very Fast' : 
                                 'Slow (Requires Ranking)')) }}
                            </td>
                          </tr>
                          <tr>
                            <td class="py-3 pl-4 pr-3 text-xs font-medium text-gray-700">Score Inflation Risk</td>
                            <td v-for="system in scoringSystems" :key="`inflation-${system.type}`" class="px-3 py-3 text-center text-xs text-gray-600"
                              :class="scoringSystemForm.scoring_system === system.type ? 'bg-orange-50' : ''"
                            >
                              {{ system.type === 'percentage' ? 'High' : 
                                 (system.type === '1-10' ? 'Medium' : 
                                 (system.type === '1-5' ? 'Low' : 
                                 'None')) }}
                            </td>
                          </tr>
                          <tr>
                            <td class="py-3 pl-4 pr-3 text-xs font-medium text-gray-700">Ease of Understanding</td>
                            <td v-for="system in scoringSystems" :key="`ease-${system.type}`" class="px-3 py-3 text-center text-xs text-gray-600"
                              :class="scoringSystemForm.scoring_system === system.type ? 'bg-orange-50' : ''"
                            >
                              {{ system.type === 'percentage' ? 'High' : 
                                 (system.type === '1-10' ? 'Very High' : 
                                 (system.type === '1-5' ? 'Highest' : 
                                 'Moderate')) }}
                            </td>
                          </tr>
                          <tr>
                            <td class="py-3 pl-4 pr-3 text-xs font-medium text-gray-700">Suitability for Tie-Breaking</td>
                            <td v-for="system in scoringSystems" :key="`ties-${system.type}`" class="px-3 py-3 text-center text-xs text-gray-600"
                              :class="scoringSystemForm.scoring_system === system.type ? 'bg-orange-50' : ''"
                            >
                              {{ system.type === 'percentage' ? 'High' : 
                                 (system.type === '1-10' ? 'Medium' : 
                                 (system.type === '1-5' ? 'Low' : 
                                 'Very High')) }}
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
                
                <div class="flex justify-end">
                  <button 
                    type="submit"
                    :disabled="scoringSystemForm.processing"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                  >
                    <Save class="h-4 w-4 mr-1.5" />
                    Save Scoring System
                  </button>
                </div>
              </form>
            </div>
          </div>
          
          <div v-if="isCompleted || isActive" class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-4 sm:p-6">
              <h4 class="text-base font-medium text-gray-900 mb-4">View Results</h4>
              <p class="text-sm text-gray-500 mb-4">
                {{ isActive ? 'This pageant is currently active. You can view live scoring results as they come in.' : 'This pageant has been completed. You can view the final results and rankings.' }}
              </p>
              <button class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 btn-transition">
                <ChartBar class="h-4 w-4 mr-1.5" />
                {{ isActive ? 'View Live Results' : 'View Final Results' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { 
  ChevronLeft, 
  ChevronRight,
  Edit, 
  Info, 
  Calendar, 
  MapPin, 
  Users, 
  ListChecks, 
  Clock, 
  Activity, 
  CheckCircle, 
  Trash, 
  AlertCircle,
  User2,
  Scale,
  Plus,
  Save,
  Calculator,
  ChartBar,
  Flag,
  Tag
} from 'lucide-vue-next'
import OrganizerLayout from '@/Layouts/OrganizerLayout.vue'

import ConfirmDeleteModal from '@/Components/ConfirmDeleteModal.vue'
import Tooltip from '@/Components/Tooltip.vue'
import CustomSelect from '@/Components/CustomSelect.vue'

defineOptions({
  layout: OrganizerLayout
})

const props = defineProps({
  pageant: {
    type: Object,
    required: true
  },
  availableTabulators: {
    type: Array,
    default: () => []
  },
  auth: {
    type: Object,
    required: true
  }
})

// State
const activeTab = ref('overview')

const showDeleteConfirm = ref(false)
const deleteProcessing = ref(false)
const selectedTabulator = ref(null)
const showDeleteTabulatorConfirm = ref(false)
const deleteTabulatorProcessing = ref(false)

// Form state
const requiredJudgesForm = ref({
  required_judges: props.pageant.required_judges || 0,
  processing: false
})

const tabulatorForm = ref({
  tabulator_id: '',
  notes: '',
  processing: false
})

// Computed properties
const tabulatorOptions = computed(() => {
  return props.availableTabulators.map(tabulator => ({
    value: tabulator.id,
    label: `${tabulator.name} (@${tabulator.username})`
  }))
})

// Status update form
const statusUpdateForm = ref({
  processing: false
})
const selectedNewStatus = ref('')

// Tabs configuration
const tabs = [
  { id: 'overview', name: 'Overview', icon: Info },

  { id: 'contestants', name: 'Contestants', icon: Users },
  { id: 'criteria', name: 'Criteria', icon: ListChecks },
  { id: 'judges', name: 'Judges', icon: Scale },
  { id: 'scoring', name: 'Scoring System', icon: Calculator },
]

// Tabulator and Judge related functions
const updateRequiredJudges = () => {
  requiredJudgesForm.value.processing = true
  
  router.put(route('organizer.pageant.required-judges.update', props.pageant.id), {
    required_judges: requiredJudgesForm.value.required_judges
  }, {
    onSuccess: () => {
      requiredJudgesForm.value.processing = false
    },
    onError: () => {
      requiredJudgesForm.value.processing = false
    }
  })
}

// Scoring system options
const scoringSystems = [
  {
    type: 'percentage',
    name: 'Percentage (0-100%)',
    description: 'Traditional scoring where judges assign percentages (0-100%) for each criterion.',
    details: 'Judges assign scores from 0% to 100% for each contestant on each criterion. The percentage directly represents the quality of performance, with 100% being perfect.',
    pros: [
      'Intuitive for judges familiar with percentage scoring',
      'Provides a wide range for precise scoring differentiation',
      'Familiar to contestants and audience members',
      'Works well with weighted criteria systems'
    ],
    cons: [
      'May lead to score inflation with many judges scoring near 90-100%',
      'Large range can lead to inconsistent scoring between different judges',
      'Requires more mental calculation than simpler scales',
      'Differences between high scores (e.g., 95% vs 97%) may seem arbitrary'
    ],
    bestFor: 'Traditional pageants with experienced judges who can utilize the full percentage scale effectively.',
    scoringMethod: 'Final scores are calculated by taking the weighted average of all criteria percentages, with optional highest/lowest score dropping.'
  },
  {
    type: '1-10',
    name: 'Scale of 1-10',
    description: 'Judges score each contestant on a scale of 1-10 for each criterion.',
    details: 'A straightforward numerical scale where 1 represents poor performance and 10 represents excellence. Half-points may be allowed for more granular scoring.',
    pros: [
      'Simple and universally understood scoring system',
      'Provides enough range for meaningful differentiation',
      'Easy for judges to quickly decide on scores',
      'Works well for both novice and experienced judges'
    ],
    cons: [
      'Less granular than percentage scoring',
      'May still suffer from score clustering at the high end (8-10)',
      'Judges may be reluctant to use the full scale, especially lower scores',
      'May not capture subtle differences between top performers'
    ],
    bestFor: 'Balanced pageants where both judges and audience need to easily understand the scoring system.',
    scoringMethod: 'Scores are multiplied by criterion weights, then summed or averaged across all criteria to produce final scores.'
  },
  {
    type: '1-5',
    name: 'Scale of 1-5',
    description: 'Simplified scoring where judges assign 1-5 points for each criterion.',
    details: 'A compact scoring scale where judges rate contestants from 1 (poor) to 5 (excellent) on each criterion. This system prioritizes simplicity and speed.',
    pros: [
      'Very simple and quick to apply',
      'Reduces overthinking and score hesitation',
      'Forces judges to make clearer distinctions between contestants',
      'Faster scoring leads to better pacing of the pageant'
    ],
    cons: [
      'Limited range makes it harder to differentiate between similar performances',
      'May result in more ties that require additional tie-breakers',
      'Less precise than wider scales',
      'May not satisfy contestants looking for more detailed feedback'
    ],
    bestFor: 'Fast-paced pageants, preliminary rounds, or when using many criteria where scoring speed is important.',
    scoringMethod: 'Scores are weighted by criteria importance. Ties are common and resolved through predetermined tie-breaking criteria.'
  },
  {
    type: 'points',
    name: 'Points System',
    description: 'Custom points allocation based on performance and ranking in each criterion.',
    details: 'Instead of direct scoring, judges rank contestants for each criterion, and points are allocated based on rank (e.g., 1st place = 10 points, 2nd place = 9 points, etc.).',
    pros: [
      'Eliminates score inflation and forces differentiation',
      'Focuses on relative performance rather than absolute scores',
      'Reduces judge bias toward specific contestants',
      'Creates clear separation in the final standings'
    ],
    cons: [
      'More complex to tabulate and explain to audience',
      'Doesn\'t communicate how close performances were to each other',
      'May unfairly penalize contestants who are very close in quality',
      'Requires judges to rank all contestants for each criterion'
    ],
    bestFor: 'Competitive pageants where clear ranking differentiation is required and eliminating scoring bias is important.',
    scoringMethod: 'Points are assigned based on rank in each criterion, then weighted and summed across all criteria. The contestant with the highest point total wins.'
  }
]

// State for selected scoring system (for detailed view)
const selectedScoringSystem = ref(props.pageant.scoring_system || 'percentage')

// Track whether scoring system details are shown
const showScoringDetails = ref(false)

// Find the current scoring system
const currentScoringSystem = computed(() => {
  return scoringSystems.find(system => system.type === props.pageant.scoring_system) || scoringSystems[0]
})

// Find the selected scoring system (for detailed view)
const selectedScoringSystemDetails = computed(() => {
  return scoringSystems.find(system => system.type === selectedScoringSystem.value) || scoringSystems[0]
})

// Toggle detailed view for a scoring system
const toggleScoringDetails = (systemType) => {
  selectedScoringSystem.value = systemType
  showScoringDetails.value = !showScoringDetails.value
}

// Update scoring system form
const scoringSystemForm = ref({
  scoring_system: props.pageant.scoring_system || 'percentage',
  processing: false
})

// Update scoring system function
const updateScoringSystem = () => {
  scoringSystemForm.value.processing = true
  
  router.put(route('organizer.pageant.scoring-system.update', props.pageant.id), {
    scoring_system: scoringSystemForm.value.scoring_system
  }, {
    onSuccess: () => {
      scoringSystemForm.value.processing = false
      // Update the selected system to reflect the newly saved one
      selectedScoringSystem.value = scoringSystemForm.value.scoring_system
    },
    onError: () => {
      scoringSystemForm.value.processing = false
    }
  })
}

const assignTabulator = () => {
  tabulatorForm.value.processing = true
  
  router.post(route('organizer.pageant.tabulators.assign', props.pageant.id), {
    tabulator_id: tabulatorForm.value.tabulator_id,
    notes: tabulatorForm.value.notes
  }, {
    onSuccess: () => {
      tabulatorForm.value.processing = false
      tabulatorForm.value.tabulator_id = ''
      tabulatorForm.value.notes = ''
    },
    onError: () => {
      tabulatorForm.value.processing = false
    }
  })
}

const confirmRemoveTabulator = (tabulator) => {
  selectedTabulator.value = tabulator
  showDeleteTabulatorConfirm.value = true
}

const removeTabulator = () => {
  if (!selectedTabulator.value) return
  
  deleteTabulatorProcessing.value = true
  
  router.delete(route('organizer.pageant.tabulators.remove', {
    id: props.pageant.id,
    tabulatorId: selectedTabulator.value.id
  }), {
    onSuccess: () => {
      deleteTabulatorProcessing.value = false
      showDeleteTabulatorConfirm.value = false
      selectedTabulator.value = null
    },
    onError: () => {
      deleteTabulatorProcessing.value = false
    }
  })
}

// Computed properties for pageant status
const isDraft = computed(() => props.pageant.status === 'Draft')
const isSetup = computed(() => props.pageant.status === 'Setup')
const isActive = computed(() => props.pageant.status === 'Active')
const isCompleted = computed(() => props.pageant.status === 'Completed')
const isUnlockedForEdit = computed(() => props.pageant.status === 'Unlocked_For_Edit')

// Check if user is admin
const isAdmin = computed(() => props.auth?.user?.role === 'admin')

// Check if pageant date has elapsed
const isPageantDateElapsed = computed(() => {
  if (!props.pageant.start_date) return false
  const pageantDate = new Date(props.pageant.start_date)
  const today = new Date()
  today.setHours(0, 0, 0, 0) // Reset time to start of day
  return pageantDate < today
})

// Check if pageant can be edited
const canEdit = computed(() => 
  isDraft.value || 
  isSetup.value || 
  isUnlockedForEdit.value
)

// Get status styling
const getStatusClass = (status) => {
  switch (status) {
    case 'Draft':
      return { badge: 'bg-gray-100 text-gray-800' }
    case 'Setup':
      return { badge: 'bg-blue-100 text-blue-800' }
    case 'Active':
      return { badge: 'bg-green-100 text-green-800' }
    case 'Completed':
      return { badge: 'bg-purple-100 text-purple-800' }
    case 'Unlocked_For_Edit':
      return { badge: 'bg-yellow-100 text-yellow-800' }
    default:
      return { badge: 'bg-gray-100 text-gray-800' }
  }
}

// Helper functions to get scoring system name and description
const getScoringSystemName = (system) => {
  const systemInfo = scoringSystems.find(s => s.type === system)
  return systemInfo ? systemInfo.name : 'Unknown System'
}

const getScoringSystemDescription = (system) => {
  const systemInfo = scoringSystems.find(s => s.type === system)
  return systemInfo ? systemInfo.description : 'No description available'
}

// Helper function for status tooltips
const getStatusTooltipText = (status) => {
  switch (status) {
    case 'Draft':
      return 'This pageant is in draft mode. Continue adding contestants, criteria, and scheduling events.'
    case 'Setup':
      return 'Pageant setup is complete. Ready for contestant registration and judge assignments.'
    case 'Active':
      return 'This pageant is currently active. Scoring is in progress and results are being calculated.'
    case 'Completed':
      return 'This pageant has finished. All scoring is complete and final results are available.'
    case 'Unlocked_For_Edit':
      return 'This completed pageant is temporarily unlocked for editing and corrections.'
    default:
      return 'Current status of this pageant'
  }
}

// Helper function for progress tooltip
const getProgressTooltip = (progress) => {
  if (progress === 0) {
    return 'Pageant setup has not started. Begin by adding contestants, criteria, and events.'
  } else if (progress < 25) {
    return 'Pageant setup is in early stages. Continue adding basic information and participants.'
  } else if (progress < 50) {
    return 'Good progress! Keep adding contestants, criteria, and scheduling events.'
  } else if (progress < 75) {
    return 'Pageant is well configured. Consider finalizing judges and scoring systems.'
  } else if (progress < 100) {
    return 'Almost ready! Complete remaining setup items to activate the pageant.'
  } else {
    return 'Pageant setup is complete! All components are configured and ready.'
  }
}

// Status management functions
const getAvailableStatusTransitions = () => {
  const currentStatus = props.pageant.status
  
  // If pageant date has elapsed and it's not completed, auto-complete
  if (isPageantDateElapsed.value && !isCompleted.value && !isUnlockedForEdit.value) {
    return [{
      value: 'Completed',
      label: 'Completed (Auto-completion due to elapsed date)'
    }]
  }
  
  const baseTransitions = {
    'Draft': ['Setup', 'Active'],
    'Setup': ['Draft', 'Active'],
    'Active': ['Completed'],
    'Completed': [], // Completed pageants cannot be reverted
    'Unlocked_For_Edit': ['Completed'], // Can only go back to completed
  }
  
  let transitions = baseTransitions[currentStatus] || []
  
  // Only admins can set Unlocked_For_Edit status
  if (currentStatus === 'Completed' && isAdmin.value) {
    transitions = ['Unlocked_For_Edit']
  }
  
  // Only admins can transition from any status to Unlocked_For_Edit
  if (isAdmin.value && currentStatus !== 'Completed' && currentStatus !== 'Unlocked_For_Edit') {
    transitions = [...transitions, 'Unlocked_For_Edit']
  }
  
  return transitions.map(status => ({
    value: status,
    label: status
  }))
}

const getStatusTransitionHelp = (fromStatus, toStatus) => {
  const transitions = {
    'Draft->Setup': 'Lock the pageant configuration and prepare for contestant registration.',
    'Draft->Active': 'Start the pageant competition immediately (skips setup phase).',
    'Draft->Unlocked_For_Edit': 'Temporarily unlock the pageant for administrative corrections. (Admin only)',
    'Setup->Draft': 'Unlock the pageant to continue making changes.',
    'Setup->Active': 'Start the pageant competition and begin scoring.',
    'Setup->Unlocked_For_Edit': 'Temporarily unlock the pageant for administrative corrections. (Admin only)',
    'Active->Completed': 'End the pageant and finalize results.',
    'Active->Unlocked_For_Edit': 'Temporarily unlock the pageant for administrative corrections. (Admin only)',
    'Completed->Unlocked_For_Edit': 'Temporarily unlock the completed pageant to make corrections. (Admin only)',
    'Unlocked_For_Edit->Completed': 'Relock the pageant as completed.',
  }
  
  // Special case for auto-completion
  if (toStatus === 'Completed' && isPageantDateElapsed.value) {
    return 'This pageant will be automatically completed because the pageant date has elapsed.'
  }
  
  const key = `${fromStatus}->${toStatus}`
  return transitions[key] || 'Change pageant to this status.'
}

const updateStatus = () => {
  if (!selectedNewStatus.value) return
  
  statusUpdateForm.value.processing = true
  
  router.put(route('organizer.pageant.status.update', props.pageant.id), {
    status: selectedNewStatus.value
  }, {
    onFinish: () => {
      statusUpdateForm.value.processing = false
      selectedNewStatus.value = ''
    },
    onError: (errors) => {
      statusUpdateForm.value.processing = false
      console.error('Status update failed:', errors)
    }
  })
}

// Auto-completion logic
const checkForAutoCompletion = () => {
  if (isPageantDateElapsed.value && !isCompleted.value && !isUnlockedForEdit.value && !statusUpdateForm.value.processing) {
    console.log('Pageant date has elapsed, suggesting auto-completion')
    // You could automatically trigger the completion here, but it's better to let the user decide
    // by showing the auto-completion option in the UI
  }
}

// Check for auto-completion on mount
onMounted(() => {
  checkForAutoCompletion()
})

// Watch for changes in pageant status or date that might trigger auto-completion
watch([isPageantDateElapsed, isCompleted, isUnlockedForEdit], () => {
  checkForAutoCompletion()
})
</script> 