<template>
  <div class="space-y-6">
    <!-- Delete Confirmation Modal -->
    <ConfirmDeleteModal
      :show="showDeleteConfirm"
      :message="'this item'"
      :isLoading="deleteProcessing"
      @confirm="confirmDeleteItem"
      @cancel="showDeleteConfirm = false"
    />
    
    <!-- Tabulator Delete Confirmation Modal -->
    <ConfirmDeleteModal
      :show="showDeleteTabulatorConfirm"
      :message="selectedTabulator ? `tabulator '${selectedTabulator.name}'` : 'this tabulator'"
      :isLoading="deleteTabulatorProcessing"
      @confirm="removeTabulator"
      @cancel="showDeleteTabulatorConfirm = false"
    />
    
    <!-- Back Button and Page Header -->
    <div class="flex flex-col md:flex-row justify-between md:items-center space-y-3 md:space-y-0">
      <Link 
        :href="route('organizer.my-pageants', {}, false)"
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
          @error="handleCoverImageError"
        />
        
        <!-- Gradient Overlay -->
        <div class="absolute inset-0 bg-gradient-to-r from-orange-900/70 via-orange-600/50 to-transparent"></div>
        
        <!-- Pageant Logo (if exists) -->
        <div v-if="pageant.logo" class="absolute top-4 right-4 h-16 w-16 sm:h-24 sm:w-24 md:h-32 md:w-32 bg-white/80 rounded-lg p-2 shadow-lg">
          <img :src="pageant.logo" alt="Pageant logo" class="w-full h-full object-contain" @error="handleLogoImageError" />
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
            <Tooltip v-if="pageant.contestant_type" :text="getContestantTypeTooltipText(pageant.contestant_type)" position="top">
              <span :class="[
                getContestantTypeClass(pageant.contestant_type).badge,
                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium hover:shadow-md transition-shadow cursor-help'
              ]">
                <component :is="getContestantTypeClass(pageant.contestant_type).icon" class="h-3 w-3 mr-1" />
                {{ getContestantTypeLabel(pageant.contestant_type) }}
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
          
          <!-- Quick Settings Panel -->
          <div v-if="canEdit" class="bg-gradient-to-r from-orange-50 to-amber-50 rounded-lg border border-orange-200 p-6">
            <h4 class="text-base font-medium text-gray-900 mb-4 flex items-center">
              <Calculator class="h-5 w-5 text-orange-600 mr-2" />
              Quick Settings
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Current Scoring System -->
              <div class="bg-white rounded-lg p-4 border border-orange-100">
                <label class="block text-sm font-medium text-gray-700 mb-2">Current Scoring System</label>
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-900 font-medium">{{ getScoringSystemName(pageant.scoring_system) }}</span>
                  <button 
                    @click="activeTab = 'scoring'"
                    class="text-xs text-orange-600 hover:text-orange-800 font-medium"
                  >
                    Change
                  </button>
                </div>
                <p class="text-xs text-gray-500 mt-1">{{ getScoringSystemDescription(pageant.scoring_system) }}</p>
              </div>
              
              <!-- Required Judges Quick Setting -->
              <div class="bg-white rounded-lg p-4 border border-orange-100">
                <label class="block text-sm font-medium text-gray-700 mb-2">Required Judges</label>
                <div class="flex items-center space-x-2">
                  <input 
                    v-model="quickRequiredJudges" 
                    type="number" 
                    min="0" 
                    max="20"
                    @change="updateRequiredJudgesQuick"
                    class="w-20 text-sm border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500"
                  />
                  <span class="text-sm text-gray-500">judges</span>
                  <button 
                    @click="activeTab = 'judges'"
                    class="text-xs text-orange-600 hover:text-orange-800 font-medium ml-auto"
                  >
                    Manage
                  </button>
                </div>
                <p class="text-xs text-gray-500 mt-1">Current: {{ pageant.judges.length }} assigned</p>
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
            
            <!-- Rounds Card -->
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
              <div class="p-4">
                <div class="flex items-center justify-between">
                  <h3 class="text-lg font-semibold text-gray-900">Rounds</h3>
                  <div class="p-2 bg-purple-100 rounded-full">
                    <Target class="h-5 w-5 text-purple-600" />
                  </div>
                </div>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ pageant.rounds?.length || 0 }}</p>
                <p class="text-sm text-gray-500">Competition rounds</p>
              </div>
              <div class="border-t border-gray-100 bg-gray-50 px-4 py-3">
                <button 
                  @click="activeTab = 'rounds'"
                  class="text-sm font-medium text-orange-600 hover:text-orange-800 flex items-center"
                >
                  View Rounds <ChevronRight class="h-4 w-4 ml-1" />
                </button>
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
            
            <!-- Rounds Card -->
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
              <div class="p-4">
                <div class="flex items-center justify-between">
                  <h3 class="text-lg font-semibold text-gray-900">Rounds</h3>
                  <div class="p-2 bg-purple-100 rounded-full">
                    <Target class="h-5 w-5 text-purple-600" />
                  </div>
                </div>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ pageant.rounds?.length || 0 }}</p>
                <p class="text-sm text-gray-500">Competition rounds</p>
              </div>
              <div class="border-t border-gray-100 bg-gray-50 px-4 py-3">
                <Link 
                  :href="route('organizer.pageant.rounds-management', pageant.id)"
                  class="text-sm font-medium text-orange-600 hover:text-orange-800 flex items-center"
                >
                  Manage Rounds <ChevronRight class="h-4 w-4 ml-1" />
                </Link>
              </div>
            </div>
          </div>
        </div>
        


        
        <!-- Contestants Tab -->
        <div v-else-if="activeTab === 'contestants'" class="space-y-6">
          <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">Contestants</h3>
            <div v-if="canEdit" class="flex items-center space-x-2">
              <button
                @click="openAddContestantModal"
                class="inline-flex items-center px-3 py-2 bg-orange-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-colors"
              >
                <Plus class="h-4 w-4 mr-1.5" />
                Add Contestant
              </button>
              <Link 
                :href="route('organizer.pageant.contestants-management', pageant.id)"
                class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium bg-white text-gray-700 hover:bg-gray-50 btn-transition"
              >
                <Users class="h-4 w-4 mr-1.5" />
                Manage All
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
          <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            <div 
              v-for="contestant in pageant.contestants" 
              :key="`contestant-${contestant.id}`"
              :data-contestant-id="contestant.id"
              class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden hover:shadow-md transition-shadow"
            >
              <div class="aspect-[4/5] bg-gray-100 relative">
                <img 
                  :src="getContestantDisplayImage(contestant)" 
                  :alt="`${contestant.name} - Contestant #${contestant.number}`"
                  class="w-full h-full object-cover object-center"
                  @error="(event) => handleImageError(event, contestant.id)"
                  @load="(event) => handleImageLoad(event, contestant.id)"
                  loading="lazy"
                  :data-contestant-id="contestant.id"
                />
                <div class="absolute inset-0 bg-gray-200 flex items-center justify-center" v-if="imageLoadingStates[contestant.id] === 'error'">
                  <div class="text-center text-gray-500">
                    <Users class="h-8 w-8 mx-auto mb-2" />
                    <span class="text-sm">No Image</span>
                  </div>
                </div>
                <div class="absolute top-2 left-2 bg-white rounded-full h-8 w-8 flex items-center justify-center shadow-md">
                  <span class="font-bold text-orange-600">{{ contestant.number || '?' }}</span>
                </div>
                <!-- Debug info (remove in production) -->
                <div v-if="isDevelopment" class="absolute bottom-2 left-2 bg-black bg-opacity-75 text-white text-xs p-1 rounded">
                  ID: {{ contestant.id }}
                </div>
              </div>
              <div class="p-3">
                <h4 class="font-medium text-gray-900 truncate">{{ contestant.name }}</h4>
                <div class="mt-1 flex items-center justify-between text-sm text-gray-500">
                  <span>{{ contestant.age ? `${contestant.age} years` : 'Age not specified' }}</span>
                  <span>{{ contestant.origin || 'Unknown origin' }}</span>
                </div>
                <div v-if="canEdit" class="mt-3 flex justify-end space-x-2">
                  <Tooltip text="Edit contestant information" position="top">
                    <button 
                      @click="openEditContestantModal(contestant)"
                      class="p-1 rounded-md text-gray-400 hover:text-orange-600 hover:bg-orange-50 transition-all transform hover:scale-110"
                    >
                      <Edit class="h-4 w-4" />
                    </button>
                  </Tooltip>
                  <Tooltip text="Remove contestant from pageant" position="top">
                    <button 
                      @click="confirmDeleteContestant(contestant)"
                      class="p-1 rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50 transition-all transform hover:scale-110"
                    >
                      <Trash class="h-4 w-4" />
                    </button>
                  </Tooltip>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Rounds Tab -->
        <div v-else-if="activeTab === 'rounds'" class="space-y-6">
          <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">Competition Rounds</h3>
            <div v-if="canEdit" class="flex items-center space-x-2">
              <button
                @click="openAddRoundModal"
                class="inline-flex items-center px-3 py-2 bg-orange-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-colors"
              >
                <Plus class="h-4 w-4 mr-1.5" />
                Add Round
              </button>
              <Link 
                :href="route('organizer.pageant.rounds-management', pageant.id)"
                class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium bg-white text-gray-700 hover:bg-gray-50 btn-transition"
              >
                <Target class="h-4 w-4 mr-1.5" />
                Manage All
              </Link>
            </div>
          </div>
          
          <!-- Empty State -->
          <div v-if="!pageant.rounds || pageant.rounds.length === 0" class="bg-gray-50 rounded-lg py-12 px-4 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
              <Target class="h-8 w-8 text-gray-400" />
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-1">No Rounds Defined</h3>
            <p class="text-gray-500 max-w-md mx-auto">
              No competition rounds have been defined for this pageant yet.
              {{ canEdit ? 'Click the "Add Round" button to set up your competition structure.' : '' }}
            </p>
          </div>
          
          <!-- Rounds List -->
          <div v-else class="space-y-4">
            <div 
              v-for="round in pageant.rounds" 
              :key="round.id"
              class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden hover:shadow-md transition-shadow"
            >
              <!-- Round Header -->
              <div class="p-4 border-b border-gray-100">
                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-3">
                    <div class="p-2 bg-purple-100 rounded-lg">
                      <Target class="h-5 w-5 text-purple-600" />
                    </div>
                    <div>
                      <h4 class="font-semibold text-gray-900">{{ round.name }}</h4>
                      <p class="text-sm text-gray-500">{{ round.description || 'No description provided' }}</p>
                    </div>
                  </div>
                  <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                      {{ round.weight }}% Weight
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                      {{ round.type }}
                    </span>
                    <button 
                      @click="toggleRoundExpansion(round.id)"
                      class="p-1 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition-all"
                    >
                      <ChevronDown v-if="expandedRounds.includes(round.id)" class="h-5 w-5" />
                      <ChevronRight v-else class="h-5 w-5" />
                    </button>
                    <div v-if="canEdit" class="flex space-x-1">
                      <Tooltip text="Edit round" position="top">
                        <button 
                          @click="openEditRoundModal(round)"
                          class="p-1 rounded-md text-gray-400 hover:text-orange-600 hover:bg-orange-50 transition-all transform hover:scale-110"
                        >
                          <Edit class="h-4 w-4" />
                        </button>
                      </Tooltip>
                      <Tooltip text="Delete round" position="top">
                        <button 
                          @click="confirmDeleteRound(round)"
                          class="p-1 rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50 transition-all transform hover:scale-110"
                        >
                          <Trash class="h-4 w-4" />
                        </button>
                      </Tooltip>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Round Criteria (Expanded) -->
              <div v-if="expandedRounds.includes(round.id)" class="p-4 bg-gray-50">
                <div class="flex justify-between items-center mb-4">
                  <h5 class="text-sm font-medium text-gray-900">Scoring Criteria</h5>
                  <button
                    v-if="canEdit"
                    @click="openAddCriteriaModal(round)"
                    class="inline-flex items-center px-2 py-1 bg-orange-600 border border-transparent rounded text-xs font-medium text-white hover:bg-orange-700 transition-colors"
                  >
                    <Plus class="h-3 w-3 mr-1" />
                    Add Criteria
                  </button>
                </div>
                
                <!-- Criteria Empty State -->
                <div v-if="!round.criteria || round.criteria.length === 0" class="text-center py-6">
                  <ListChecks class="h-8 w-8 text-gray-400 mx-auto mb-2" />
                  <p class="text-sm text-gray-500">No criteria defined for this round</p>
                </div>
                
                <!-- Criteria List -->
                <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-3">
                  <div 
                    v-for="criteria in round.criteria" 
                    :key="criteria.id"
                    class="bg-white rounded-lg border border-gray-200 p-3 hover:shadow-sm transition-shadow"
                  >
                    <div class="flex items-center justify-between mb-2">
                      <h6 class="font-medium text-gray-900">{{ criteria.name }}</h6>
                      <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800">
                        {{ criteria.weight }}%
                      </span>
                    </div>
                    <p class="text-xs text-gray-500 mb-2">{{ criteria.description || 'No description' }}</p>
                    <div class="flex items-center justify-between text-xs text-gray-500">
                      <span>Score: {{ criteria.min_score || 0 }} - {{ criteria.max_score || 10 }}</span>
                      <div v-if="canEdit" class="flex space-x-1">
                        <button 
                          @click="openEditCriteriaModal(round, criteria)"
                          class="p-1 rounded text-gray-400 hover:text-orange-600 transition-colors"
                        >
                          <Edit class="h-3 w-3" />
                        </button>
                        <button 
                          @click="confirmDeleteCriteria(round, criteria)"
                          class="p-1 rounded text-gray-400 hover:text-red-600 transition-colors"
                        >
                          <Trash class="h-3 w-3" />
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Judges Tab -->
        <div v-else-if="activeTab === 'judges'" class="space-y-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Judges</h3>
            <div class="flex items-center">
              <span class="text-sm text-gray-500 mr-2">Required: {{ pageant.required_judges }}</span>
              <span class="text-sm text-gray-500">Assigned: {{ pageant.judges.length }}</span>
            </div>
          </div>
          

          

          
          <!-- Judges Section -->
          <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-4 sm:p-6">
              
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
        
        <!-- Management Tab -->
        <div v-else-if="activeTab === 'management'" class="space-y-6">
          <h3 class="text-lg font-semibold text-gray-900">Pageant Management</h3>
          
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
          
          <!-- Current Scoring System Settings -->
          <div v-if="canEdit" class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-4 sm:p-6">
              <h4 class="text-base font-medium text-gray-900 mb-4">Scoring System Settings</h4>
              <p class="text-sm text-gray-500 mb-4">
                Choose the scoring system for this pageant. This will determine how judges score contestants across all criteria.
              </p>
              
              <form @submit.prevent="updateScoringSystem" class="space-y-4 mb-6">
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
          
          <!-- Detailed Scoring System Information -->
          <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-4 sm:p-6">
              <h4 class="text-base font-medium text-gray-900 mb-4">Detailed System Information</h4>
              <p class="text-sm text-gray-500 mb-4">
                Explore detailed information about each scoring system to make the best choice for your pageant.
              </p>
              
              <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div 
                    v-for="system in scoringSystems" 
                    :key="system.type" 
                    class="relative border rounded-lg p-4 cursor-pointer hover:bg-orange-50 transition-colors"
                    :class="scoringSystemForm.scoring_system === system.type ? 'border-orange-500 bg-orange-50 ring-2 ring-orange-200' : 'border-gray-300'"
                  >
                    <div class="flex flex-col">
                      <!-- Basic info -->
                      <div class="flex items-start">
                        <div class="text-sm">
                          <h5 class="font-medium text-gray-900">{{ system.name }}</h5>
                          <p class="text-gray-500">{{ system.description }}</p>
                        </div>
                      </div>
                      
                      <!-- Details toggle button -->
                      <div class="mt-2">
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
                        class="mt-3 pt-3 border-t border-gray-100"
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
                    
                    <!-- Current system indicator -->
                    <div v-if="pageant.scoring_system === system.type" class="absolute top-2 right-2 text-orange-600">
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
              </div>
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
    
    <!-- Add/Edit Contestant Modal -->
    <TransitionRoot appear :show="showContestantModal" as="template">
      <Dialog as="div" @close="closeContestantModal" class="relative z-30">
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
              <DialogPanel class="w-full max-w-3xl transform overflow-hidden rounded-2xl bg-white shadow-xl transition-all">
                <div class="relative border-b border-gray-200 p-6">
                  <DialogTitle as="h3" class="text-2xl font-bold text-gray-800 leading-6">
                    {{ editingContestant ? 'Edit Contestant' : 'Add New Contestant' }}
                  </DialogTitle>
                  <p class="mt-2 text-gray-600 max-w-2xl">
                    {{ editingContestant ? 'Update contestant information' : 'Add a new contestant to your pageant' }}
                  </p>
                  <button 
                    @click="closeContestantModal" 
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors"
                  >
                    <XCircle class="h-6 w-6" />
                  </button>
                </div>

                <form @submit.prevent="submitContestantForm" class="p-6">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div class="space-y-6">
                      <div>
                        <label for="contestNumber" class="block text-sm font-medium text-gray-700 mb-1">
                          Contestant Number <span class="text-red-500">*</span>
                        </label>
                        <input
                          id="contestNumber"
                          v-model="contestantForm.number"
                          type="text"
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-200 focus:ring-opacity-50 transition-colors"
                          placeholder="e.g. 001"
                          required
                        />
                        <p class="mt-1 text-xs text-gray-500">Enter the contestant's competition number</p>
                      </div>

                      <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                          Full Name <span class="text-red-500">*</span>
                        </label>
                        <input
                          id="name"
                          v-model="contestantForm.name"
                          type="text"
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-200 focus:ring-opacity-50 transition-colors"
                          placeholder="e.g. Jane Smith"
                          required
                        />
                        <p class="mt-1 text-xs text-gray-500">Enter the contestant's full name</p>
                      </div>

                      <div>
                        <label for="age" class="block text-sm font-medium text-gray-700 mb-1">
                          Age <span class="text-red-500">*</span>
                        </label>
                        <input
                          id="age"
                          v-model="contestantForm.age"
                          type="number"
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-200 focus:ring-opacity-50 transition-colors"
                          placeholder="e.g. 24"
                          required
                        />
                        <p class="mt-1 text-xs text-gray-500">Enter the contestant's age</p>
                      </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6">
                      <div>
                        <label for="gender" class="block text-sm font-medium text-gray-700 mb-1">
                          Gender <span class="text-red-500">*</span>
                        </label>
                        <select
                          id="gender"
                          v-model="contestantForm.gender"
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-200 focus:ring-opacity-50 transition-colors"
                          required
                        >
                          <option value="" disabled>Select gender</option>
                          <option value="male">Male</option>
                          <option value="female">Female</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Select the contestant's gender</p>
                      </div>
                      <div>
                        <label for="origin" class="block text-sm font-medium text-gray-700 mb-1">
                          Location <span class="text-red-500">*</span>
                        </label>
                        <input
                          id="origin"
                          v-model="contestantForm.origin"
                          type="text"
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-200 focus:ring-opacity-50 transition-colors"
                          placeholder="e.g. New York, USA"
                          required
                        />
                        <p class="mt-1 text-xs text-gray-500">Enter the contestant's hometown or representation</p>
                      </div>

                      <div>
                        <label for="bio" class="block text-sm font-medium text-gray-700 mb-1">
                          Biography <span class="text-red-500">*</span>
                        </label>
                        <textarea
                          id="bio"
                          v-model="contestantForm.bio"
                          rows="4"
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-200 focus:ring-opacity-50 transition-colors"
                          placeholder="Share the contestant's background, achievements, interests..."
                          required
                        ></textarea>
                        <p class="mt-1 text-xs text-gray-500">Add a brief biography for the contestant</p>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Photos Section (Full Width) -->
                  <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Photos <span class="text-red-500">*</span>
                    </label>
                    
                    <!-- Current Photos Preview (if editing) -->
                    <div v-if="contestantForm.photos && contestantForm.photos.length > 0" class="mb-4">
                      <p class="text-sm text-gray-600 mb-2">
                        Current photos for {{ editingContestant?.name || 'this contestant' }} (ID: {{ contestantForm.contestant_id }})
                      </p>
                      <div class="flex flex-wrap gap-3">
                        <div 
                          v-for="(photo, index) in contestantForm.photos" 
                          :key="`${contestantForm.contestant_id}-${index}`" 
                          class="relative w-24 h-24 rounded-lg overflow-hidden group"
                        >
                          <img :src="photo" class="w-full h-full object-cover" />
                          <button 
                            @click.prevent="removePhoto(index)"
                            class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white"
                          >
                            <Trash class="h-5 w-5" />
                          </button>
                        </div>
                      </div>
                    </div>
                    
                    <!-- Upload New Photos -->
                    <div class="flex flex-col w-full">
                      <label
                        class="flex flex-col w-full h-32 border-2 border-dashed rounded-lg border-gray-300 hover:border-orange-400 hover:bg-orange-50 transition-colors cursor-pointer"
                      >
                        <div class="flex flex-col items-center justify-center pt-7">
                          <Camera class="w-8 h-8 text-orange-400 group-hover:text-orange-600" />
                          <p class="pt-1 text-sm tracking-wider text-gray-600 group-hover:text-gray-600">
                            Upload contestant photos
                          </p>
                          <p class="text-xs text-gray-500 mt-1">
                            Drag & drop files here or click to browse
                          </p>
                        </div>
                        <input
                          type="file"
                          @change="handlePhotoChange"
                          class="opacity-0 absolute"
                          accept="image/*"
                          multiple
                        />
                      </label>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Upload photos of the contestant. First photo will be used as the main profile image.</p>
                  </div>

                  <div class="mt-8 pt-5 border-t border-gray-200 flex justify-end space-x-3">
                    <button
                      type="button"
                      @click="closeContestantModal"
                      class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300"
                    >
                      Cancel
                    </button>
                    <button
                      type="submit"
                      class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-orange-600 to-orange-500 hover:from-orange-700 hover:to-orange-600 rounded-lg shadow-sm hover:shadow transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
                    >
                      {{ editingContestant ? 'Save Changes' : 'Add Contestant' }}
                    </button>
                  </div>
                </form>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
    
    <!-- Delete Contestant Confirmation Modal -->
    <div v-if="showDeleteContestantModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
          <div class="flex items-center mb-4">
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
              <AlertCircle class="h-6 w-6 text-red-600" />
            </div>
          </div>
          <div class="text-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-2">Remove Contestant</h3>
            <p class="text-sm text-gray-500 mb-6">
              Are you sure you want to remove "{{ contestantToDelete?.name }}" from this pageant? This action cannot be undone.
            </p>
          </div>
          <div class="flex justify-end space-x-3">
            <button
              type="button"
              @click="closeDeleteContestantModal"
              class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
            >
              Cancel
            </button>
            <button
              type="button"
              @click="deleteContestant"
              :disabled="deleteContestantProcessing"
              class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ deleteContestantProcessing ? 'Removing...' : 'Remove Contestant' }}
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Add/Edit Round Modal -->
    <TransitionRoot appear :show="showRoundModal" as="template">
      <Dialog as="div" @close="closeRoundModal" class="relative z-30">
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
              <DialogPanel class="w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white shadow-xl transition-all">
                <div class="relative border-b border-gray-200 p-6">
                  <DialogTitle as="h3" class="text-2xl font-bold text-gray-800 leading-6">
                    {{ editingRound ? 'Edit Round' : 'Add New Round' }}
                  </DialogTitle>
                  <p class="mt-2 text-gray-600 max-w-2xl">
                    {{ editingRound ? 'Update round information' : 'Add a new competition round to your pageant' }}
                  </p>
                  <button 
                    @click="closeRoundModal" 
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors"
                  >
                    <XCircle class="h-6 w-6" />
                  </button>
                </div>

                <form @submit.prevent="submitRoundForm" class="p-6">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div class="space-y-6">
                      <div>
                        <label for="roundName" class="block text-sm font-medium text-gray-700 mb-1">
                          Round Name <span class="text-red-500">*</span>
                        </label>
                        <input
                          id="roundName"
                          v-model="roundForm.name"
                          type="text"
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-200 focus:ring-opacity-50 transition-colors"
                          placeholder="e.g. Production Number, Q&A, Evening Gown"
                          required
                        />
                        <p class="mt-1 text-xs text-gray-500">Enter the name of this competition round</p>
                      </div>

                      <div>
                        <label for="roundType" class="block text-sm font-medium text-gray-700 mb-1">
                          Round Type <span class="text-red-500">*</span>
                        </label>
                        <select
                          id="roundType"
                          v-model="roundForm.type"
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-200 focus:ring-opacity-50 transition-colors"
                          required
                        >
                          <option value="semi-final">Semi-Final</option>
                          <option value="final">Final</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Select the type of round</p>
                      </div>

                      <div>
                        <label for="roundWeight" class="block text-sm font-medium text-gray-700 mb-1">
                          Weight (%) <span class="text-red-500">*</span>
                        </label>
                        <input
                          id="roundWeight"
                          v-model.number="roundForm.weight"
                          type="number"
                          min="1"
                          max="100"
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-200 focus:ring-opacity-50 transition-colors"
                          placeholder="e.g. 40"
                          required
                        />
                        <p class="mt-1 text-xs text-gray-500">Percentage weight of this round in overall scoring</p>
                      </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6">
                      <div>
                        <label for="roundDescription" class="block text-sm font-medium text-gray-700 mb-1">
                          Description
                        </label>
                        <textarea
                          id="roundDescription"
                          v-model="roundForm.description"
                          rows="4"
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-200 focus:ring-opacity-50 transition-colors"
                          placeholder="Describe what this round involves..."
                        ></textarea>
                        <p class="mt-1 text-xs text-gray-500">Optional description of this round</p>
                      </div>

                      <div>
                        <label for="roundDisplayOrder" class="block text-sm font-medium text-gray-700 mb-1">
                          Display Order
                        </label>
                        <input
                          id="roundDisplayOrder"
                          v-model.number="roundForm.display_order"
                          type="number"
                          min="0"
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-200 focus:ring-opacity-50 transition-colors"
                          placeholder="e.g. 1"
                        />
                        <p class="mt-1 text-xs text-gray-500">Order in which this round appears (0 = first)</p>
                      </div>
                    </div>
                  </div>

                  <div class="mt-8 pt-5 border-t border-gray-200 flex justify-end space-x-3">
                    <button
                      type="button"
                      @click="closeRoundModal"
                      class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300"
                    >
                      Cancel
                    </button>
                    <button
                      type="submit"
                      :disabled="roundForm.processing"
                      class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-orange-600 to-orange-500 hover:from-orange-700 hover:to-orange-600 rounded-lg shadow-sm hover:shadow transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      {{ roundForm.processing ? 'Saving...' : (editingRound ? 'Save Changes' : 'Add Round') }}
                    </button>
                  </div>
                </form>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
    
    <!-- Add/Edit Criteria Modal -->
    <TransitionRoot appear :show="showCriteriaModal" as="template">
      <Dialog as="div" @close="closeCriteriaModal" class="relative z-30">
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
              <DialogPanel class="w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white shadow-xl transition-all">
                <div class="relative border-b border-gray-200 p-6">
                  <DialogTitle as="h3" class="text-2xl font-bold text-gray-800 leading-6">
                    {{ editingCriteria ? 'Edit Criteria' : 'Add New Criteria' }}
                  </DialogTitle>
                  <p class="mt-2 text-gray-600 max-w-2xl">
                    {{ editingCriteria ? 'Update criteria information' : `Add a new scoring criteria to ${selectedRound?.name}` }}
                  </p>
                  <button 
                    @click="closeCriteriaModal" 
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors"
                  >
                    <XCircle class="h-6 w-6" />
                  </button>
                </div>

                <form @submit.prevent="submitCriteriaForm" class="p-6">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div class="space-y-6">
                      <div>
                        <label for="criteriaName" class="block text-sm font-medium text-gray-700 mb-1">
                          Criteria Name <span class="text-red-500">*</span>
                        </label>
                        <input
                          id="criteriaName"
                          v-model="criteriaForm.name"
                          type="text"
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-200 focus:ring-opacity-50 transition-colors"
                          placeholder="e.g. Beauty, Poise, Intelligence"
                          required
                        />
                        <p class="mt-1 text-xs text-gray-500">Enter the name of this scoring criteria</p>
                      </div>

                      <div>
                        <label for="criteriaWeight" class="block text-sm font-medium text-gray-700 mb-1">
                          Weight (%) <span class="text-red-500">*</span>
                        </label>
                        <input
                          id="criteriaWeight"
                          v-model.number="criteriaForm.weight"
                          type="number"
                          min="1"
                          max="100"
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-200 focus:ring-opacity-50 transition-colors"
                          placeholder="e.g. 30"
                          required
                        />
                        <p class="mt-1 text-xs text-gray-500">Percentage weight within this round</p>
                      </div>

                      <div class="grid grid-cols-2 gap-3">
                        <div>
                          <label for="criteriaMinScore" class="block text-sm font-medium text-gray-700 mb-1">
                            Min Score <span class="text-red-500">*</span>
                          </label>
                          <input
                            id="criteriaMinScore"
                            v-model.number="criteriaForm.min_score"
                            type="number"
                            step="0.01"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-200 focus:ring-opacity-50 transition-colors"
                            placeholder="0"
                            required
                          />
                        </div>
                        <div>
                          <label for="criteriaMaxScore" class="block text-sm font-medium text-gray-700 mb-1">
                            Max Score <span class="text-red-500">*</span>
                          </label>
                          <input
                            id="criteriaMaxScore"
                            v-model.number="criteriaForm.max_score"
                            type="number"
                            step="0.01"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-200 focus:ring-opacity-50 transition-colors"
                            placeholder="100"
                            required
                          />
                        </div>
                      </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6">
                      <div>
                        <label for="criteriaDescription" class="block text-sm font-medium text-gray-700 mb-1">
                          Description
                        </label>
                        <textarea
                          id="criteriaDescription"
                          v-model="criteriaForm.description"
                          rows="4"
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-200 focus:ring-opacity-50 transition-colors"
                          placeholder="Describe what judges should evaluate..."
                        ></textarea>
                        <p class="mt-1 text-xs text-gray-500">Optional description for judges</p>
                      </div>

                      <div>
                        <label class="flex items-center">
                          <input
                            v-model="criteriaForm.allow_decimals"
                            type="checkbox"
                            class="rounded border-gray-300 text-orange-600 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-200 focus:ring-opacity-50"
                          />
                          <span class="ml-2 text-sm text-gray-700">Allow decimal scores</span>
                        </label>
                      </div>

                      <div v-if="criteriaForm.allow_decimals">
                        <label for="criteriaDecimalPlaces" class="block text-sm font-medium text-gray-700 mb-1">
                          Decimal Places
                        </label>
                        <input
                          id="criteriaDecimalPlaces"
                          v-model.number="criteriaForm.decimal_places"
                          type="number"
                          min="1"
                          max="4"
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-200 focus:ring-opacity-50 transition-colors"
                          placeholder="2"
                        />
                        <p class="mt-1 text-xs text-gray-500">Number of decimal places allowed</p>
                      </div>

                      <div>
                        <label for="criteriaDisplayOrder" class="block text-sm font-medium text-gray-700 mb-1">
                          Display Order
                        </label>
                        <input
                          id="criteriaDisplayOrder"
                          v-model.number="criteriaForm.display_order"
                          type="number"
                          min="0"
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-200 focus:ring-opacity-50 transition-colors"
                          placeholder="0"
                        />
                        <p class="mt-1 text-xs text-gray-500">Order in which this criteria appears</p>
                      </div>
                    </div>
                  </div>

                  <div class="mt-8 pt-5 border-t border-gray-200 flex justify-end space-x-3">
                    <button
                      type="button"
                      @click="closeCriteriaModal"
                      class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300"
                    >
                      Cancel
                    </button>
                    <button
                      type="submit"
                      :disabled="criteriaForm.processing"
                      class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-orange-600 to-orange-500 hover:from-orange-700 hover:to-orange-600 rounded-lg shadow-sm hover:shadow transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      {{ criteriaForm.processing ? 'Saving...' : (editingCriteria ? 'Save Changes' : 'Add Criteria') }}
                    </button>
                  </div>
                </form>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
    
    <!-- Delete Round Confirmation Modal -->
    <ConfirmDeleteModal
      :show="showDeleteRoundModal"
      :message="roundToDelete ? `round '${roundToDelete.name}' and all its criteria` : 'this round'"
      :isLoading="deleteRoundProcessing"
      @confirm="deleteRound"
      @cancel="closeDeleteRoundModal"
    />
    
    <!-- Delete Criteria Confirmation Modal -->
    <ConfirmDeleteModal
      :show="showDeleteCriteriaModal"
      :message="criteriaToDelete ? `criteria '${criteriaToDelete.name}'` : 'this criteria'"
      :isLoading="deleteCriteriaProcessing"
      @confirm="deleteCriteria"
      @cancel="closeDeleteCriteriaModal"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import axios from 'axios'
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue'
import { 
  ChevronLeft, 
  ChevronRight,
  ChevronDown,
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
  User,
  Scale,
  Plus,
  Save,
  Calculator,
  ChartBar,
  Flag,
  Tag,
  XCircle,
  Camera
, Target} from 'lucide-vue-next'
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

// Contestant modal states
const showContestantModal = ref(false)
const showDeleteContestantModal = ref(false)
const editingContestant = ref(null)
const contestantToDelete = ref(null)
const deleteContestantProcessing = ref(false)
const photoPreview = ref(null)
const photoInput = ref(null)

// Rounds modal states
const showRoundModal = ref(false)
const showCriteriaModal = ref(false)
const showDeleteRoundModal = ref(false)
const showDeleteCriteriaModal = ref(false)
const editingRound = ref(null)
const editingCriteria = ref(null)
const selectedRound = ref(null)
const roundToDelete = ref(null)
const criteriaToDelete = ref(null)
const deleteRoundProcessing = ref(false)
const deleteCriteriaProcessing = ref(false)
const expandedRounds = ref([])

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

// Contestant form state
const contestantForm = ref({
  number: '',
  name: '',
  age: '',
  gender: '',
  origin: '',
  bio: '',
  photos: [], // Array to store photo URLs for display
  photoFiles: [], // Array to store actual File objects for upload
  photo: null,
  processing: false
})

// Round form state
const roundForm = ref({
  id: null,
  name: '',
  description: '',
  type: 'semi-final',
  weight: 100,
  display_order: 0,
  processing: false
})

// Criteria form state
const criteriaForm = ref({
  id: null,
  round_id: null,
  name: '',
  description: '',
  weight: 100,
  min_score: 0,
  max_score: 100,
  allow_decimals: true,
  decimal_places: 2,
  display_order: 0,
  processing: false
})

// Quick settings state
const quickRequiredJudges = ref(props.pageant.required_judges || 0)

// Image loading states
const imageLoadingStates = ref({})

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
  { id: 'rounds', name: 'Rounds', icon: Target },
  { id: 'judges', name: 'Judges', icon: Scale },
  { id: 'management', name: 'Management', icon: Calculator },
  { id: 'scoring', name: 'Scoring System', icon: ChartBar },
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

// Quick update for required judges from overview
const updateRequiredJudgesQuick = () => {
  router.put(route('organizer.pageant.required-judges.update', props.pageant.id), {
    required_judges: quickRequiredJudges.value
  }, {
    onSuccess: () => {
      // Update the main form as well
      requiredJudgesForm.value.required_judges = quickRequiredJudges.value
    },
    onError: () => {
      // Reset to original value on error
      quickRequiredJudges.value = props.pageant.required_judges || 0
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

// Development flag for debugging
const isDevelopment = computed(() => {
  return import.meta.env.DEV || false
})

// Get the correct display image for a contestant with validation
const getContestantDisplayImage = (contestant) => {
  // Validate that we have a contestant object with ID
  if (!contestant || !contestant.id) {
    console.warn('Invalid contestant object provided to getContestantDisplayImage')
    return '/images/placeholders/placeholder-contestant.jpg'
  }
  
  // Check if primary_image exists and is valid
  if (contestant.primary_image && typeof contestant.primary_image === 'string') {
    // Additional validation: check if photos array contains this image (extra safety)
    if (contestant.photos && Array.isArray(contestant.photos) && contestant.photos.length > 0) {
      if (contestant.photos.includes(contestant.primary_image)) {
        return contestant.primary_image
      }
    } else {
      // If no photos array, trust primary_image
      return contestant.primary_image
    }
  }
  
  // Fallback to first photo if available
  if (contestant.photos && Array.isArray(contestant.photos) && contestant.photos.length > 0) {
    return contestant.photos[0]
  }
  
  // Fallback to legacy photo field
  if (contestant.photo && typeof contestant.photo === 'string') {
    return contestant.photo
  }
  
  // Ultimate fallback
  return '/images/placeholders/placeholder-contestant.jpg'
}

// Image handling functions with enhanced validation
const handleImageError = (event, contestantId = null) => {
  const img = event.target
  
  // Get contestant ID from parameter or DOM
  const targetContestantId = contestantId || img.closest('[data-contestant-id]')?.dataset.contestantId
  
  if (targetContestantId) {
    imageLoadingStates.value[targetContestantId] = 'error'
    console.warn(`Image load error for contestant ID: ${targetContestantId}`)
  }
  
  // Set a fallback image
  img.src = '/images/placeholders/placeholder-contestant.jpg'
}

const handleImageLoad = (event, contestantId = null) => {
  const img = event.target
  
  // Get contestant ID from parameter or DOM
  const targetContestantId = contestantId || img.closest('[data-contestant-id]')?.dataset.contestantId
  
  if (targetContestantId) {
    imageLoadingStates.value[targetContestantId] = 'loaded'
  }
}

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
      router.reload()
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
      router.reload()
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

// Helper functions for contestant type
const getContestantTypeLabel = (type) => {
  switch (type) {
    case 'solo':
      return 'Solo Only'
    case 'pairs':
      return 'Pairs Only'
    case 'both':
      return 'Solo & Pairs'
    default:
      return 'Mixed'
  }
}

const getContestantTypeClass = (type) => {
  switch (type) {
    case 'solo':
      return { 
        badge: 'bg-blue-100 text-blue-800',
        icon: 'User'
      }
    case 'pairs':
      return { 
        badge: 'bg-emerald-100 text-emerald-800',
        icon: 'Users'
      }
    case 'both':
      return { 
        badge: 'bg-purple-100 text-purple-800',
        icon: 'Users'
      }
    default:
      return { 
        badge: 'bg-gray-100 text-gray-800',
        icon: 'Users'
      }
  }
}

const getContestantTypeTooltipText = (type) => {
  switch (type) {
    case 'solo':
      return 'This pageant only accepts individual solo contestants'
    case 'pairs':
      return 'This pageant only accepts paired contestants (Mr & Ms style)'
    case 'both':
      return 'This pageant accepts both individual contestants and pairs'
    default:
      return 'Contestant type configuration'
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

// Rounds management functions
const toggleRoundExpansion = (roundId) => {
  const index = expandedRounds.value.indexOf(roundId)
  if (index > -1) {
    expandedRounds.value.splice(index, 1)
  } else {
    expandedRounds.value.push(roundId)
  }
}

const openAddRoundModal = () => {
  editingRound.value = null
  resetRoundForm()
  showRoundModal.value = true
}

const openEditRoundModal = (round) => {
  editingRound.value = round
  roundForm.value = {
    id: round.id,
    name: round.name,
    description: round.description || '',
    type: round.type,
    weight: round.weight,
    display_order: round.display_order,
    processing: false
  }
  showRoundModal.value = true
}

const closeRoundModal = () => {
  showRoundModal.value = false
  editingRound.value = null
  resetRoundForm()
}

const resetRoundForm = () => {
  roundForm.value = {
    id: null,
    name: '',
    description: '',
    type: 'semi-final',
    weight: 100,
    display_order: 0,
    processing: false
  }
}

const submitRoundForm = () => {
  roundForm.value.processing = true
  
  const url = editingRound.value 
    ? route('organizer.pageant.rounds.update', { 
        pageantId: props.pageant.id, 
        roundId: editingRound.value.id 
      })
    : route('organizer.pageant.rounds.store', props.pageant.id)
  
  const method = editingRound.value ? 'put' : 'post'
  
  router[method](url, {
    name: roundForm.value.name,
    description: roundForm.value.description,
    type: roundForm.value.type,
    weight: roundForm.value.weight,
    display_order: roundForm.value.display_order
  }, {
    onSuccess: () => {
      closeRoundModal()
      router.reload({ only: ['pageant'] })
    },
    onError: (errors) => {
      console.error('Round form error:', errors)
      alert('Error saving round. Please try again.')
    },
    onFinish: () => {
      roundForm.value.processing = false
    }
  })
}

const confirmDeleteRound = (round) => {
  roundToDelete.value = round
  showDeleteRoundModal.value = true
}

const closeDeleteRoundModal = () => {
  showDeleteRoundModal.value = false
  roundToDelete.value = null
}

const deleteRound = () => {
  if (!roundToDelete.value) return
  
  deleteRoundProcessing.value = true
  
  router.delete(route('organizer.pageant.rounds.destroy', {
    pageantId: props.pageant.id,
    roundId: roundToDelete.value.id
  }), {
    onSuccess: () => {
      closeDeleteRoundModal()
      router.reload({ only: ['pageant'] })
    },
    onError: (errors) => {
      console.error('Delete round error:', errors)
      alert('Error deleting round. Please try again.')
    },
    onFinish: () => {
      deleteRoundProcessing.value = false
    }
  })
}

// Criteria management functions
const openAddCriteriaModal = (round) => {
  editingCriteria.value = null
  selectedRound.value = round
  resetCriteriaForm()
  criteriaForm.value.round_id = round.id
  showCriteriaModal.value = true
}

const openEditCriteriaModal = (round, criteria) => {
  editingCriteria.value = criteria
  selectedRound.value = round
  criteriaForm.value = {
    id: criteria.id,
    round_id: round.id,
    name: criteria.name,
    description: criteria.description || '',
    weight: criteria.weight,
    min_score: criteria.min_score,
    max_score: criteria.max_score,
    allow_decimals: criteria.allow_decimals,
    decimal_places: criteria.decimal_places,
    display_order: criteria.display_order,
    processing: false
  }
  showCriteriaModal.value = true
}

const closeCriteriaModal = () => {
  showCriteriaModal.value = false
  editingCriteria.value = null
  selectedRound.value = null
  resetCriteriaForm()
}

const resetCriteriaForm = () => {
  criteriaForm.value = {
    id: null,
    round_id: null,
    name: '',
    description: '',
    weight: 100,
    min_score: 0,
    max_score: 100,
    allow_decimals: true,
    decimal_places: 2,
    display_order: 0,
    processing: false
  }
}

const submitCriteriaForm = () => {
  criteriaForm.value.processing = true
  
  const url = editingCriteria.value 
    ? route('organizer.pageant.rounds.criteria.update', { 
        pageantId: props.pageant.id, 
        roundId: criteriaForm.value.round_id,
        criteriaId: editingCriteria.value.id 
      })
    : route('organizer.pageant.rounds.criteria.store', {
        pageantId: props.pageant.id,
        roundId: criteriaForm.value.round_id
      })
  
  const method = editingCriteria.value ? 'put' : 'post'
  
  router[method](url, {
    name: criteriaForm.value.name,
    description: criteriaForm.value.description,
    weight: criteriaForm.value.weight,
    min_score: criteriaForm.value.min_score,
    max_score: criteriaForm.value.max_score,
    allow_decimals: criteriaForm.value.allow_decimals,
    decimal_places: criteriaForm.value.decimal_places,
    display_order: criteriaForm.value.display_order
  }, {
    onSuccess: () => {
      closeCriteriaModal()
      router.reload({ only: ['pageant'] })
    },
    onError: (errors) => {
      console.error('Criteria form error:', errors)
      alert('Error saving criteria. Please try again.')
    },
    onFinish: () => {
      criteriaForm.value.processing = false
    }
  })
}

const confirmDeleteCriteria = (round, criteria) => {
  selectedRound.value = round
  criteriaToDelete.value = criteria
  showDeleteCriteriaModal.value = true
}

const closeDeleteCriteriaModal = () => {
  showDeleteCriteriaModal.value = false
  selectedRound.value = null
  criteriaToDelete.value = null
}

const deleteCriteria = () => {
  if (!criteriaToDelete.value || !selectedRound.value) return
  
  deleteCriteriaProcessing.value = true
  
  router.delete(route('organizer.pageant.rounds.criteria.destroy', {
    pageantId: props.pageant.id,
    roundId: selectedRound.value.id,
    criteriaId: criteriaToDelete.value.id
  }), {
    onSuccess: () => {
      closeDeleteCriteriaModal()
      router.reload({ only: ['pageant'] })
    },
    onError: (errors) => {
      console.error('Delete criteria error:', errors)
      alert('Error deleting criteria. Please try again.')
    },
    onFinish: () => {
      deleteCriteriaProcessing.value = false
    }
  })
}

// Contestant management functions
const openAddContestantModal = () => {
  editingContestant.value = null
  resetContestantForm()
  showContestantModal.value = true
}

const openEditContestantModal = (contestant) => {
  editingContestant.value = contestant
  
  // Ensure we only use photos that belong to this specific contestant with strict validation
  let contestantPhotos = []
  
  // First, try to get photos from photo_details with strict contestant ID filtering
  if (contestant.photo_details && Array.isArray(contestant.photo_details)) {
    contestantPhotos = contestant.photo_details
      .filter(detail => detail && detail.contestant_id === contestant.id)
      .map(detail => detail.url)
      .filter(url => url) // Remove any empty URLs
  }
  
  // If no photos from photo_details, try the photos array
  if (contestantPhotos.length === 0 && contestant.photos && Array.isArray(contestant.photos)) {
    contestantPhotos = contestant.photos.filter(photo => photo && typeof photo === 'string')
  }
  
  // If still no photos, check for legacy photo field
  if (contestantPhotos.length === 0 && contestant.photo && typeof contestant.photo === 'string') {
    contestantPhotos = [contestant.photo]
  }
  
  // Log for debugging (remove in production)
  if (isDevelopment.value) {
    console.log(`Opening edit modal for contestant ${contestant.id}:`, {
      contestant_id: contestant.id,
      photo_details_count: contestant.photo_details?.length || 0,
      photos_count: contestant.photos?.length || 0,
      filtered_photos_count: contestantPhotos.length,
      legacy_photo: contestant.photo
    })
  }
  
  contestantForm.value = {
    number: contestant.number || '',
    name: contestant.name || '',
    age: contestant.age || '',
    gender: contestant.gender || '',
    origin: contestant.origin || '',
    bio: contestant.bio || '',
    photos: contestantPhotos,
    photoFiles: [],
    photo: null,
    processing: false,
    contestant_id: contestant.id // Include contestant ID for validation
  }
  photoPreview.value = null
  showContestantModal.value = true
}

const closeContestantModal = () => {
  showContestantModal.value = false
  editingContestant.value = null
  resetContestantForm()
  photoPreview.value = null
  if (photoInput.value) {
    photoInput.value.value = ''
  }
}

const resetContestantForm = () => {
  contestantForm.value = {
    number: '',
    name: '',
    age: '',
    gender: '',
    origin: '',
    bio: '',
    photos: [],
    photoFiles: [],
    photo: null,
    processing: false,
    contestant_id: null
  }
}

const handlePhotoChange = (event) => {
  const files = event.target.files
  if (!files || files.length === 0) return
  
  // Process multiple files
  for (let i = 0; i < files.length; i++) {
    const file = files[i]
    
    // Store the actual file for upload
    contestantForm.value.photoFiles.push(file)
    
    // Create preview URL for display
    const reader = new FileReader()
    reader.onloadend = () => {
      contestantForm.value.photos.push(reader.result)
    }
    reader.readAsDataURL(file)
  }
}

const removePhoto = async (index) => {
  // If we're editing an existing contestant and this is an existing photo (not a new upload)
  if (editingContestant.value && contestantForm.value.contestant_id) {
    // Check if this is an existing photo (not in photoFiles array or photoFiles array is empty/shorter)
    const isExistingPhoto = !contestantForm.value.photoFiles ||
                            !contestantForm.value.photoFiles[index] ||
                            index >= contestantForm.value.photoFiles.length

    if (isExistingPhoto) {
      try {
        // Call the backend to delete the photo from the server
        const response = await axios.delete(route('organizer.pageant.contestants.photos.destroy', {
          pageantId: props.pageant.id,
          contestantId: contestantForm.value.contestant_id,
          photoIndex: index
        }))

        // Remove from local photos array after successful server deletion
        contestantForm.value.photos.splice(index, 1)
        console.log(`Photo at index ${index} deleted successfully`)
        
        // Close modal first, then show success message
        closeContestantModal()
        
        // Show success message after modal is closed
        setTimeout(() => {
          alert(response.data.message || 'Photo deleted successfully')
        }, 100)
        
      } catch (error) {
        console.error('Error deleting photo:', error)
        const errorMessage = error.response?.data?.message || 'Failed to delete photo. Please try again.'
        alert(errorMessage)
      }
      return
    }
  }

  // For new uploads or non-editing mode, just remove from local arrays
  contestantForm.value.photos.splice(index, 1)
  // Also remove from photoFiles if it exists (for new uploads)
  if (contestantForm.value.photoFiles && contestantForm.value.photoFiles[index]) {
    contestantForm.value.photoFiles.splice(index, 1)
  }
}

const submitContestantForm = async () => {
  contestantForm.value.processing = true
  
  try {
    // Validation: Ensure we have a valid contestant when editing
    if (editingContestant.value && contestantForm.value.contestant_id !== editingContestant.value.id) {
      console.error('Contestant ID mismatch detected!')
      alert('Error: Contestant data mismatch. Please close and reopen the form.')
      contestantForm.value.processing = false
      return
    }
    
    const formData = new FormData()
    formData.append('name', contestantForm.value.name)
    formData.append('number', contestantForm.value.number)
    formData.append('age', contestantForm.value.age)
    formData.append('gender', contestantForm.value.gender)
    formData.append('origin', contestantForm.value.origin)
    formData.append('bio', contestantForm.value.bio || '')
    
    // Add contestant ID for backend validation
    if (contestantForm.value.contestant_id) {
      formData.append('contestant_id', contestantForm.value.contestant_id)
    }
    
    // Handle new photo files
    if (contestantForm.value.photoFiles && contestantForm.value.photoFiles.length > 0) {
      contestantForm.value.photoFiles.forEach((file, index) => {
        formData.append(`images[]`, file)
      })
    }
    
    // Use axios to hit JSON API endpoints to avoid Inertia response mismatch
    let response
    if (editingContestant.value) {
      response = await axios.post(
        `/organizer/pageant/${props.pageant.id}/contestants/${editingContestant.value.id}`,
        formData,
        {
          headers: {
            'Content-Type': 'multipart/form-data',
            'X-HTTP-Method-Override': 'PUT'
          }
        }
      )
    } else {
      response = await axios.post(
        `/organizer/pageant/${props.pageant.id}/contestants`,
        formData,
        {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        }
      )
    }

    if (response.data?.success) {
      closeContestantModal()
      router.reload({ only: ['pageant'] })
    } else {
      alert('Unexpected response when saving contestant.')
    }
  } catch (error) {
    console.error('Form submission error:', error)
    alert('Error submitting form. Please try again.')
    contestantForm.value.processing = false
  }
}

const confirmDeleteContestant = (contestant) => {
  contestantToDelete.value = contestant
  showDeleteContestantModal.value = true
}

const closeDeleteContestantModal = () => {
  showDeleteContestantModal.value = false
  contestantToDelete.value = null
}

const deleteContestant = () => {
  if (!contestantToDelete.value) return

  deleteContestantProcessing.value = true

  router.delete(route('organizer.pageant.contestants.destroy', {
    pageantId: props.pageant.id,
    contestantId: contestantToDelete.value.id
  }), {
    onSuccess: () => {
      closeDeleteContestantModal()
      // Reload page to get updated data
      router.reload({ only: ['pageant'] })
    },
    onError: (errors) => {
      console.error('Delete contestant error:', errors)
      alert('Error removing contestant. Please try again.')
    },
    onFinish: () => {
      deleteContestantProcessing.value = false
    }
  })
}

// Generic delete method for the unused modal
const confirmDeleteItem = () => {
  // This is a placeholder for the generic delete modal
  // Currently not used, but prevents JavaScript errors
  console.log('Generic delete confirmed')
  showDeleteConfirm.value = false
}

// Image error handling
const handleCoverImageError = (event) => {
  console.warn('Cover image failed to load, using fallback')
  event.target.src = '/images/placeholders/pageant-cover.jpg'
}

const handleLogoImageError = (event) => {
  console.warn('Logo image failed to load, hiding logo')
  // Hide the logo container if the image fails to load
  event.target.parentElement.style.display = 'none'
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