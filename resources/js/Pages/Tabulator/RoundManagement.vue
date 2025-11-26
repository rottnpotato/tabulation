<template>
  <div class="min-h-screen bg-slate-50/50 pb-12">
    <!-- Completed Pageant Banner -->
    <div v-if="pageant?.is_completed" class="fixed top-0 left-0 right-0 z-50 bg-amber-100 border-b border-amber-200 px-4 py-2">
      <div class="max-w-7xl mx-auto flex items-center justify-center gap-2 text-amber-800 text-sm font-medium">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
        </svg>
        This pageant has been completed. Round settings are view-only.
      </div>
    </div>
    <div :class="pageant?.is_completed ? 'pt-12' : ''" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header Section -->
      <div class="relative overflow-hidden rounded-3xl bg-white shadow-xl mb-8 border border-teal-100">
        <!-- Abstract Background Pattern -->
        <div class="absolute inset-0">
          <div class="absolute inset-0 bg-gradient-to-br from-teal-50 via-teal-50/50 to-white opacity-90"></div>
          <div class="absolute -top-24 -left-24 w-96 h-96 bg-teal-100 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
          <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-teal-100 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
        </div>

        <div class="relative z-10 p-8">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="space-y-2">
              <h1 class="text-3xl font-bold tracking-tight font-display text-slate-900">
                Round Management
              </h1>
              <p class="text-slate-500 text-lg max-w-2xl font-light flex items-center gap-2">
                <Circle class="w-5 h-5 text-teal-500" />
                {{ pageant.name }}
              </p>
            </div>
            
            <div v-if="pageant.current_round" class="flex items-center bg-white/60 backdrop-blur-md rounded-2xl p-4 border border-teal-100 shadow-sm">
              <div class="text-teal-600 mr-3">
                <div class="p-2 bg-teal-50 rounded-lg">
                  <Activity class="w-5 h-5" />
                </div>
              </div>
              <div>
                <div class="text-xs font-bold text-teal-500 uppercase tracking-wider mb-0.5">Current Active Round</div>
                <div class="text-lg font-bold text-slate-900 leading-none">{{ pageant.current_round.name }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div v-if="pageant && !pageant.is_completed" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 animate-fade-in relative z-20">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 relative z-30 group hover:shadow-md transition-all">
          <div class="absolute inset-0 overflow-hidden rounded-2xl pointer-events-none">
            <div class="absolute top-0 right-0 w-32 h-32 bg-teal-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
          </div>
          <div class="relative">
            <h3 class="text-lg font-bold text-slate-900 mb-1">Set Current Round</h3>
            <p class="text-sm text-slate-500 mb-4">Direct judges to score a specific round</p>
            <div class="max-w-xs">
              <CustomSelect
                v-model="selectedRoundId"
                :options="roundOptions"
                placeholder="Select Round"
                :disabled="!isChannelReady || actionLoading"
                @change="setCurrentRound"
              />
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 relative overflow-hidden group hover:shadow-md transition-all">
          <div class="absolute top-0 right-0 w-32 h-32 bg-teal-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
          <div class="relative z-10 flex justify-between items-center h-full">
            <div>
              <h3 class="text-lg font-bold text-slate-900 mb-1">Round Status</h3>
              <p class="text-sm text-slate-500">Monitor round completion status</p>
            </div>
            <button 
              @click="showProgressModal = true"
              class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-xl transition-colors shadow-lg shadow-teal-200"
            >
              View Progress
            </button>
          </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 relative overflow-hidden group hover:shadow-md transition-all">
          <div class="absolute top-0 right-0 w-32 h-32 bg-purple-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
          <div class="relative z-10 flex justify-between items-center h-full">
            <div>
              <h3 class="text-lg font-bold text-slate-900 mb-1">Notify Judges</h3>
              <p class="text-sm text-slate-500">Alert judges to start scoring</p>
            </div>
            <button 
              @click="showNotifyModal = true"
              class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-xl transition-colors shadow-lg shadow-purple-200 flex items-center gap-2"
            >
              <Bell class="w-4 h-4" />
              Notify
            </button>
          </div>
        </div>
      </div>

      <!-- Rounds Table -->
      <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden mb-8 animate-fade-in">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
          <div>
            <h2 class="text-lg font-bold text-slate-900">All Rounds</h2>
            <p class="text-sm text-slate-500">Manage individual round settings and lock status</p>
          </div>
        </div>
        
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-50/50">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Round Details</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Type</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Weight</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Lock Status</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr 
                v-for="round in rounds" 
                :key="round.id"
                class="hover:bg-slate-50/80 transition-colors"
                :class="{ 'bg-teal-50/30': pageant.current_round_id === round.id }"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex flex-col">
                    <div class="text-sm font-bold text-slate-900 flex items-center gap-2">
                      {{ round.name }}
                      <span v-if="round.identifier" class="font-mono text-[10px] px-1.5 py-0.5 bg-slate-100 text-slate-500 rounded border border-slate-200">{{ round.identifier }}</span>
                      <span v-if="pageant.current_round_id === round.id" class="inline-flex items-center px-2 py-0.5 bg-teal-100 text-teal-700 text-[10px] font-bold uppercase tracking-wide rounded-full border border-teal-200">
                        Current
                      </span>
                    </div>
                    <div v-if="round.description" class="text-xs text-slate-500 mt-0.5">{{ round.description }}</div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border capitalize"
                    :class="[
                      round.type?.toLowerCase().includes('final') && !round.type?.toLowerCase().includes('semi') ? 'bg-purple-50 text-purple-700 border-purple-200' :
                      round.type?.toLowerCase().includes('semi') ? 'bg-blue-50 text-blue-700 border-blue-200' :
                      'bg-teal-50 text-teal-700 border-teal-200'
                    ]">
                    {{ getRoundTypeDisplay(round) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm font-medium text-slate-700">{{ round.weight }}%</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span 
                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border"
                    :class="round.is_active ? 'bg-teal-50 text-teal-700 border-teal-200' : 'bg-slate-100 text-slate-600 border-slate-200'"
                  >
                    <span class="w-1.5 h-1.5 rounded-full mr-1.5" :class="round.is_active ? 'bg-teal-500' : 'bg-slate-400'"></span>
                    {{ round.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex flex-col gap-1">
                    <span 
                      class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border w-fit"
                      :class="round.is_locked ? 'bg-slate-100 text-slate-600 border-slate-200' : 'bg-teal-50 text-teal-700 border-teal-200'"
                    >
                      <component :is="round.is_locked ? Lock : CheckCircle" class="w-3 h-3 mr-1.5" />
                      {{ round.is_locked ? 'Locked' : 'Open' }}
                    </span>
                    <div v-if="round.locked_by" class="text-[10px] text-slate-400 pl-1">
                      by {{ round.locked_by.name }}
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div v-if="pageant && !pageant.is_completed" class="flex items-center gap-2">
                    <button
                      v-if="pageant.current_round_id !== round.id"
                      @click="setCurrentRoundDirect(round.id)"
                      :disabled="actionLoading || !isChannelReady"
                      class="px-3 py-1.5 text-xs font-medium text-teal-700 bg-teal-50 hover:bg-teal-100 border border-teal-200 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      {{ actionLoading ? 'Setting...' : 'Set Current' }}
                    </button>
                    
                    <button
                      v-if="!round.is_locked"
                      @click="lockRound(round.id)"
                      :disabled="actionLoading || !isChannelReady"
                      class="px-3 py-1.5 text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      {{ actionLoading ? 'Locking...' : 'Lock' }}
                    </button>
                    <button
                      v-else
                      @click="unlockRound(round.id)"
                      :disabled="actionLoading || !isChannelReady"
                      class="px-3 py-1.5 text-xs font-medium text-teal-700 bg-teal-50 hover:bg-teal-100 border border-teal-200 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      {{ actionLoading ? 'Unlocking...' : 'Unlock' }}
                    </button>
                  </div>
                  <span v-else class="text-xs text-slate-400 italic">View only</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Round Statistics -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 animate-fade-in">
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-4">
          <div class="p-3 bg-teal-50 text-teal-600 rounded-xl">
            <Circle class="h-6 w-6" />
          </div>
          <div>
            <p class="text-sm font-medium text-slate-500">Total Rounds</p>
            <p class="text-2xl font-bold text-slate-900">{{ rounds.length }}</p>
          </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-4">
          <div class="p-3 bg-teal-50 text-teal-600 rounded-xl">
            <CheckCircle class="h-6 w-6" />
          </div>
          <div>
            <p class="text-sm font-medium text-slate-500">Active Rounds</p>
            <p class="text-2xl font-bold text-slate-900">{{ activeRoundsCount }}</p>
          </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-4">
          <div class="p-3 bg-slate-100 text-slate-600 rounded-xl">
            <Lock class="h-6 w-6" />
          </div>
          <div>
            <p class="text-sm font-medium text-slate-500">Locked Rounds</p>
            <p class="text-2xl font-bold text-slate-900">{{ lockedRoundsCount }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Notify Judges Modal -->
    <div 
      v-if="showNotifyModal" 
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
      @click.self="showNotifyModal = false"
    >
      <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden">
        <!-- Modal Header -->
        <div class="bg-gradient-to-br from-purple-50 via-purple-50/50 to-white border-b border-purple-100 p-6">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                <Bell class="w-6 h-6 text-purple-600" />
                Notify Judges
              </h2>
              <p class="text-sm text-slate-600 mt-1">Send real-time notifications to selected judges</p>
            </div>
            <button 
              @click="showNotifyModal = false"
              class="p-2 hover:bg-slate-100 rounded-lg transition-colors"
            >
              <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
        </div>

        <!-- Modal Body -->
        <form @submit.prevent="sendNotifications" class="p-6 overflow-y-auto max-h-[calc(90vh-180px)]">
          <div class="space-y-6">
            <!-- Round Selection -->
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-2">
                Related Round (Optional)
              </label>
              <CustomSelect
                v-model="notifyForm.round_id"
                :options="[{ value: '', label: 'General Notification' }, ...roundOptions]"
                placeholder="Select Round"
              />
              <p class="mt-1 text-xs text-slate-500">If selected, judges will be notified to score this specific round</p>
            </div>

            <!-- Judge Selection -->
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-3">
                Select Judges <span class="text-red-500">*</span>
              </label>
              
              <!-- Select All / Deselect All -->
              <div class="flex items-center gap-3 mb-3">
                <button
                  type="button"
                  @click="selectAllJudges"
                  class="text-xs font-medium text-purple-600 hover:text-purple-700 underline"
                >
                  Select All
                </button>
                <span class="text-slate-300">|</span>
                <button
                  type="button"
                  @click="deselectAllJudges"
                  class="text-xs font-medium text-slate-600 hover:text-slate-700 underline"
                >
                  Deselect All
                </button>
                <span class="ml-auto text-xs text-slate-500">
                  {{ notifyForm.judge_ids.length }} selected
                </span>
              </div>

              <!-- Judges List -->
              <div class="space-y-2 max-h-60 overflow-y-auto border border-slate-200 rounded-xl p-3 bg-slate-50/50">
                <label
                  v-for="judge in judges"
                  :key="judge.id"
                  class="flex items-center gap-3 p-3 bg-white rounded-lg border border-slate-100 hover:border-purple-200 hover:bg-purple-50/30 transition-all cursor-pointer"
                >
                  <input
                    type="checkbox"
                    :value="judge.id"
                    v-model="notifyForm.judge_ids"
                    class="w-4 h-4 text-purple-600 border-slate-300 rounded focus:ring-purple-500"
                  />
                  <div class="flex-1 min-w-0">
                    <div class="font-medium text-slate-900">{{ judge.name }}</div>
                    <div class="text-xs text-slate-500">{{ judge.email }}</div>
                  </div>
                </label>
                
                <div v-if="judges.length === 0" class="text-center py-8 text-slate-500">
                  No judges assigned to this pageant
                </div>
              </div>
              <p v-if="notifyFormErrors.judge_ids" class="mt-2 text-sm text-red-600">{{ notifyFormErrors.judge_ids }}</p>
            </div>

            <!-- Message -->
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-2">
                Notification Message <span class="text-red-500">*</span>
              </label>
              <textarea
                v-model="notifyForm.message"
                rows="4"
                class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all resize-none"
                placeholder="Enter your message to the judges..."
                maxlength="500"
              ></textarea>
              <div class="flex justify-between items-center mt-1">
                <p v-if="notifyFormErrors.message" class="text-sm text-red-600">{{ notifyFormErrors.message }}</p>
                <p class="text-xs text-slate-500 ml-auto">{{ notifyForm.message.length }}/500</p>
              </div>
            </div>

            <!-- Preview -->
            <div v-if="notifyForm.message || notifyForm.round_id" class="bg-purple-50 border border-purple-200 rounded-xl p-4">
              <div class="flex gap-3">
                <div class="flex-shrink-0">
                  <Bell class="w-5 h-5 text-purple-600" />
                </div>
                <div class="flex-1">
                  <p class="text-xs font-bold text-purple-900 mb-1">PREVIEW</p>
                  <p class="text-sm font-semibold text-slate-900 mb-1">
                    {{ notifyForm.round_id ? `Time to Score: ${getRoundName(notifyForm.round_id)}` : 'Scoring Notification' }}
                  </p>
                  <p class="text-sm text-slate-700">{{ notifyForm.message || 'Your message will appear here...' }}</p>
                </div>
              </div>
            </div>
          </div>
        </form>

        <!-- Modal Footer -->
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex justify-end gap-3">
          <button 
            type="button"
            @click="showNotifyModal = false"
            :disabled="notifyFormProcessing"
            class="px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-lg text-sm font-medium transition-all disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Cancel
          </button>
          <button 
            type="submit"
            @click="sendNotifications"
            :disabled="notifyFormProcessing || notifyForm.judge_ids.length === 0 || !notifyForm.message"
            class="px-6 py-2 bg-purple-600 hover:bg-purple-700 disabled:bg-slate-300 text-white rounded-lg text-sm font-medium transition-all shadow-sm hover:shadow-md disabled:cursor-not-allowed flex items-center gap-2"
          >
            <Bell class="w-4 h-4" />
            <span v-if="notifyFormProcessing">Sending...</span>
            <span v-else>Send Notification ({{ notifyForm.judge_ids.length }})</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Progress Modal -->
    <div 
      v-if="showProgressModal" 
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
      @click.self="showProgressModal = false"
    >
      <div class="bg-white rounded-3xl shadow-2xl max-w-4xl w-full max-h-[80vh] overflow-hidden">
        <!-- Modal Header -->
        <div class="bg-gradient-to-br from-teal-50 via-teal-50/50 to-white border-b border-teal-100 p-6">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-2xl font-bold text-slate-900">Judge Scoring Progress</h2>
              <p class="text-sm text-slate-500 mt-1">Real-time completion status per judge</p>
            </div>
            <button 
              @click="showProgressModal = false"
              class="p-2 hover:bg-slate-100 rounded-lg transition-colors"
            >
              <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
        </div>

        <!-- Modal Body -->
        <div class="p-6 overflow-y-auto max-h-[calc(80vh-100px)]">
          <div v-if="judges.length === 0" class="text-center py-12">
            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
              </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-900 mb-2">No Judges Assigned</h3>
            <p class="text-slate-500">There are no judges assigned to this pageant.</p>
          </div>

          <!-- Judges List with Progress -->
          <div v-else class="space-y-6">
            <div 
              v-for="judge in judges" 
              :key="judge.id"
              class="bg-slate-50/50 rounded-2xl p-5 border border-slate-100"
            >
              <!-- Judge Info -->
              <div class="flex items-center gap-3 mb-4">
                <div class="h-12 w-12 bg-gradient-to-br from-teal-100 to-teal-200 rounded-xl flex items-center justify-center text-teal-700 font-bold text-lg shadow-inner">
                  {{ judge.name.charAt(0) }}
                </div>
                <div>
                  <h4 class="font-bold text-slate-900">{{ judge.name }}</h4>
                  <p class="text-sm text-slate-500">{{ judge.email }}</p>
                </div>
              </div>

              <!-- Progress per Round -->
              <div class="space-y-3">
                <div 
                  v-for="round in rounds" 
                  :key="round.id"
                  class="bg-white rounded-xl p-4 border border-slate-100"
                >
                  <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                      <span class="text-sm font-medium text-slate-900">{{ round.name }}</span>
                      <span 
                        v-if="pageant.current_round_id === round.id" 
                        class="inline-flex items-center px-2 py-0.5 bg-teal-100 text-teal-700 text-xs font-bold uppercase tracking-wide rounded-full border border-teal-200"
                      >
                        Current
                      </span>
                    </div>
                    <span class="text-sm font-bold" :class="getProgressColor(judge.rounds_progress[round.id]?.percentage || 0)">
                      {{ judge.rounds_progress[round.id]?.percentage || 0 }}%
                    </span>
                  </div>

                  <!-- Progress Bar -->
                  <div class="w-full bg-slate-200 rounded-full h-3 overflow-hidden">
                    <div 
                      class="h-full transition-all duration-500 ease-out rounded-full"
                      :class="getProgressBarColor(judge.rounds_progress[round.id]?.percentage || 0)"
                      :style="{ width: `${judge.rounds_progress[round.id]?.percentage || 0}%` }"
                    ></div>
                  </div>

                  <!-- Score Details -->
                  <div class="mt-2 text-xs text-slate-500">
                    {{ judge.rounds_progress[round.id]?.submitted || 0 }} / {{ judge.rounds_progress[round.id]?.total || 0 }} scores submitted
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Notification System -->
    <NotificationSystem ref="notificationSystem" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { Circle, CheckCircle, Lock, Bell, Activity } from 'lucide-vue-next'
import CustomSelect from '../../Components/CustomSelect.vue'
import TabulatorLayout from '../../Layouts/TabulatorLayout.vue'
import NotificationSystem from '../../Components/NotificationSystem.vue'

defineOptions({
  layout: TabulatorLayout
})

const props = defineProps({
  pageant: {
    type: Object,
    required: true
  },
  rounds: {
    type: Array,
    default: () => []
  },
  judges: {
    type: Array,
    default: () => []
  }
})

const selectedRoundId = ref(props.pageant.current_round_id?.toString() || '')
const notificationSystem = ref(null)
const actionLoading = ref(false)
const isChannelReady = ref(false)
const showProgressModal = ref(false)
const showNotifyModal = ref(false)
const notifyForm = ref({
  judge_ids: [],
  message: '',
  round_id: ''
})
const notifyFormErrors = ref({})
const notifyFormProcessing = ref(false)
let pageantChannel = null

const roundOptions = computed(() => {
  return props.rounds.map(round => ({
    value: round.id.toString(),
    label: round.name + (round.is_locked ? ' 🔒' : '')
  }))
})

const activeRoundsCount = computed(() => {
  return props.rounds.filter(round => round.is_active).length
})

const lockedRoundsCount = computed(() => {
  return props.rounds.filter(round => round.is_locked).length
})

const getRoundTypeDisplay = (round) => {
  if (!round || !round.type) return 'Standard'
  if (round.top_n_proceed) {
    return `${round.type} (Top ${round.top_n_proceed})`
  }
  return round.type
}

const getProgressColor = (percentage) => {
  if (percentage === 100) return 'text-green-600'
  if (percentage >= 50) return 'text-teal-600'
  return 'text-slate-600'
}

const getProgressBarColor = (percentage) => {
  if (percentage === 100) return 'bg-green-500'
  if (percentage >= 50) return 'bg-teal-500'
  return 'bg-slate-400'
}

const selectAllJudges = () => {
  notifyForm.value.judge_ids = props.judges.map(j => j.id)
}

const deselectAllJudges = () => {
  notifyForm.value.judge_ids = []
}

const getRoundName = (roundId) => {
  if (!roundId) return ''
  const round = props.rounds.find(r => r.id.toString() === roundId.toString())
  return round ? round.name : ''
}

const sendNotifications = () => {
  notifyFormErrors.value = {}
  
  if (notifyForm.value.judge_ids.length === 0) {
    notifyFormErrors.value.judge_ids = 'Please select at least one judge'
    return
  }
  
  if (!notifyForm.value.message.trim()) {
    notifyFormErrors.value.message = 'Please enter a message'
    return
  }
  
  notifyFormProcessing.value = true
  
  router.post(route('tabulator.notify-judges', props.pageant.id), {
    judge_ids: notifyForm.value.judge_ids,
    message: notifyForm.value.message,
    round_id: notifyForm.value.round_id || null
  }, {
    preserveScroll: true,
    onSuccess: () => {
      showNotifyModal.value = false
      notifyForm.value = {
        judge_ids: [],
        message: '',
        round_id: ''
      }
      if (notificationSystem.value) {
        notificationSystem.value.success('Notifications sent successfully', {
          title: 'Judges Notified',
          timeout: 4000
        })
      }
    },
    onError: (errors) => {
      notifyFormErrors.value = errors
      console.error('Error sending notifications:', errors)
    },
    onFinish: () => {
      notifyFormProcessing.value = false
    }
  })
}

const setCurrentRound = (option) => {
  // Handle both direct roundId and option object from CustomSelect
  const roundId = typeof option === 'object' ? option.value : option
  if (!roundId) return
  
  actionLoading.value = true
  router.post(route('tabulator.set-current-round', props.pageant.id), {
    round_id: parseInt(roundId)
  }, {
    preserveScroll: true,
    onSuccess: () => {
      // Success message will be shown via flash message and real-time event
    },
    onError: (errors) => {
      console.error('Error setting current round:', errors)
    },
    onFinish: () => {
      actionLoading.value = false
    }
  })
}

const setCurrentRoundDirect = (roundId) => {
  selectedRoundId.value = roundId.toString()
  setCurrentRound(roundId)
}

const lockRound = (roundId) => {
  actionLoading.value = true
  router.post(route('tabulator.rounds.lock', [props.pageant.id, roundId]), {}, {
    preserveScroll: true,
    onSuccess: () => {
      // Success message will be shown via flash message and real-time event
    },
    onError: (errors) => {
      console.error('Error locking round:', errors)
    },
    onFinish: () => {
      actionLoading.value = false
    }
  })
}

const unlockRound = (roundId) => {
  actionLoading.value = true
  router.post(route('tabulator.rounds.unlock', [props.pageant.id, roundId]), {}, {
    preserveScroll: true,
    onSuccess: () => {
      // Success message will be shown via flash message and real-time event
    },
    onError: (errors) => {
      console.error('Error unlocking round:', errors)
    },
    onFinish: () => {
      actionLoading.value = false
    }
  })
}

// Real-time event listeners
onMounted(() => {
  if (props.pageant) {
    console.log('Subscribing to pageant channel:', `pageant.${props.pageant.id}`)
    // Listen for round updates to show confirmation notifications
    pageantChannel = window.Echo.private(`pageant.${props.pageant.id}`)
      .subscribed(() => {
        isChannelReady.value = true
      })
      .listen('RoundUpdated', (e) => {
        console.log('RoundUpdated event received:', e)
        handleRoundUpdate(e)
      })
      .listen('ScoreUpdated', (e) => {
        console.log('ScoreUpdated event received:', e)
        // Refresh judges progress when scores are updated
        router.reload({ only: ['judges'] })
      })
  }
})

onUnmounted(() => {
  if (props.pageant) {
    window.Echo.leave(`pageant.${props.pageant.id}`)
  }
})

const handleRoundUpdate = (event) => {
  const { action, round_name, message } = event

  // Show confirmation notifications for tabulator actions
  if (notificationSystem.value) {
    switch (action) {
      case 'set_current':
        notificationSystem.value.success(`Current round set to: ${round_name}`, {
          title: 'Round Changed',
          timeout: 4000
        })
        // Refresh the page data to update UI
        setTimeout(() => {
          router.reload({ only: ['pageant', 'rounds'] })
        }, 1000)
        break
      
      case 'locked':
        notificationSystem.value.warning(`Round "${round_name}" has been locked`, {
          title: 'Round Locked',
          timeout: 4000
        })
        // Refresh the page data to update lock status
        setTimeout(() => {
          router.reload({ only: ['rounds'] })
        }, 1000)
        break
      
      case 'unlocked':
        notificationSystem.value.success(`Round "${round_name}" has been unlocked`, {
          title: 'Round Unlocked',
          timeout: 4000
        })
        // Refresh the page data to update lock status
        setTimeout(() => {
          router.reload({ only: ['rounds'] })
        }, 1000)
        break
    }
  }

  // Refresh judges progress data when scores are updated
  if (action === 'score_updated' || action === 'score_created') {
    router.reload({ only: ['judges', 'rounds'] })
  }
}
</script>

<style scoped>
.animate-blob {
  animation: blob 7s infinite;
}
.animation-delay-2000 {
  animation-delay: 2s;
}
@keyframes blob {
  0% {
    transform: translate(0px, 0px) scale(1);
  }
  33% {
    transform: translate(30px, -50px) scale(1.1);
  }
  66% {
    transform: translate(-20px, 20px) scale(0.9);
  }
  100% {
    transform: translate(0px, 0px) scale(1);
  }
}
</style>
