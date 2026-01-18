<template>
  <div class="print-container min-h-screen bg-slate-50/50">
    <!-- Screen View -->
    <div class="screen-only min-h-screen pb-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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
                <div class="inline-flex items-center rounded-full bg-teal-50 px-3 py-1 text-xs font-medium text-teal-700 uppercase tracking-wide border border-teal-100 mb-2">
                  <Printer class="mr-1.5 h-3.5 w-3.5" />
                  <span>Print Center</span>
                </div>
                <h1 class="text-3xl font-bold tracking-tight font-display text-slate-900">
                  Print Final Results
                </h1>
                <p class="text-slate-500 text-lg max-w-2xl font-light flex items-center gap-2">
                  {{ pageant ? pageant.name : 'Select a pageant' }}
                  <span v-if="reportTitle" class="text-slate-400 mx-2">|</span>
                  <span v-if="reportTitle" class="text-teal-600 font-medium">{{ reportTitle }}</span>
                </p>
                
                <div v-if="pageant" class="flex flex-wrap gap-2 mt-4">
                  <span v-if="pageant.date" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-teal-400 mr-1.5"></span>
                    {{ pageant.date }}
                  </span>
                  <span v-if="pageant.venue" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-teal-400 mr-1.5"></span>
                    {{ pageant.venue }}
                  </span>
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-teal-400 mr-1.5"></span>
                    {{ judges.length }} Judges
                  </span>
                </div>
              </div>
              
              <div v-if="pageant" class="flex items-center">
                <button
                  @click="printResults"
                  :disabled="!canPrint"
                  class="inline-flex items-center justify-center px-6 py-3 rounded-xl font-medium transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 group"
                  :class="canPrint 
                    ? 'bg-teal-600 text-white hover:bg-teal-700' 
                    : 'bg-slate-300 text-slate-500 cursor-not-allowed hover:shadow-lg hover:translate-y-0'"
                >
                  <Printer class="mr-2 h-5 w-5 group-hover:scale-110 transition-transform" />
                  <span>Print Report</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Warning: Not All Rounds Locked -->
        <div v-if="pageant && !canPrint" class="bg-amber-50 border border-amber-200 rounded-2xl p-6 mb-8">
          <div class="flex items-start gap-4">
            <div class="flex-shrink-0">
              <AlertCircle class="h-6 w-6 text-amber-500" />
            </div>
            <div class="flex-1">
              <h3 class="text-lg font-bold text-amber-800 mb-2">Cannot Print - Rounds Not Locked</h3>
              <p class="text-amber-700 mb-4">
                All rounds must be locked before you can print the final results. Please lock the following rounds:
              </p>
              <div class="flex flex-wrap gap-2">
                <span 
                  v-for="round in unlockedRounds" 
                  :key="round.id"
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-100 text-amber-800 text-sm font-medium rounded-lg border border-amber-200"
                >
                  <Lock class="h-3.5 w-3.5" />
                  {{ round.name }}
                  <span class="text-amber-600 text-xs">({{ round.type }})</span>
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- No Pageant Assigned -->
        <div v-if="!pageant" class="text-center py-20 animate-fade-in">
          <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-12 max-w-2xl mx-auto">
            <div class="mx-auto w-24 h-24 flex items-center justify-center rounded-full bg-slate-50 mb-6">
              <Printer class="h-12 w-12 text-slate-400" />
            </div>
            <h3 class="text-2xl font-bold text-slate-900 mb-4">No Pageant Selected</h3>
            <p class="text-slate-500 mb-8 text-lg">
              Once an organizer assigns you to a pageant, you will be able to generate and print final results here.
            </p>
            <Link 
              :href="route('tabulator.dashboard')"
              class="inline-flex items-center px-6 py-3 bg-slate-900 hover:bg-slate-800 text-white font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
            >
              <LayoutDashboard class="w-5 h-5 mr-2" />
              Return to Dashboard
            </Link>
          </div>
        </div>

        <div v-else class="space-y-8 animate-fade-in">
          <!-- Print Settings Card -->
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 relative">
            <div class="absolute inset-0 overflow-hidden rounded-2xl pointer-events-none">
              <div class="absolute top-0 right-0 w-32 h-32 bg-teal-50 rounded-bl-full -mr-8 -mt-8 opacity-50"></div>
            </div>
            <div class="relative z-10">
              <h3 class="text-lg font-bold text-slate-900 mb-4">Print Settings</h3>
              <p class="text-sm text-slate-600 mb-6">
                <span v-if="selectedStage === 'overall'" class="font-medium text-teal-700">
                  Overall Tally shows all contestants with their scores across all rounds, ranked by final round score
                </span>
                <span v-else-if="selectedStage === 'final-result'" class="font-medium text-amber-700">
                  Final Result shows only the Top {{ pageant?.number_of_winners || 3 }} contestants who competed in the final round
                </span>
                <span v-else>
                  Configure the stage and format for printing results
                </span>
              </p>
              
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Stage Selector -->
                <div class="space-y-1.5">
                  <label class="text-sm font-medium text-slate-700">Result Stage</label>
                  <div class="relative">
                    <button
                      @click="showStageDropdown = !showStageDropdown; showPaperSizeDropdown = false; showMinorAwardDropdown = false"
                      class="w-full flex items-center justify-between px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 hover:border-teal-300 hover:ring-2 hover:ring-teal-50 transition-all focus:outline-none"
                    >
                      <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                        <span>{{ stageLabels[selectedStage] }}</span>
                      </div>
                      <ChevronDown class="h-4 w-4 text-slate-400" />
                    </button>
                    
                    <div
                      v-if="showStageDropdown"
                      class="absolute left-0 right-0 z-20 mt-2 overflow-hidden rounded-xl border border-slate-100 bg-white shadow-xl ring-1 ring-black/5 max-h-64 overflow-y-auto"
                    >
                      <div class="p-1">
                        <button
                          v-for="[key, label] in Object.entries(stageLabels)"
                          :key="key"
                          @click="selectedStage = key; showStageDropdown = false; selectedMinorAward = ''"
                          class="w-full flex items-center gap-2 px-3 py-2 text-sm rounded-lg transition-all cursor-pointer"
                          :class="selectedStage === key ? 'bg-teal-50 text-teal-700' : 'text-slate-600 hover:bg-slate-50'"
                        >
                          <span class="w-1.5 h-1.5 rounded-full" :class="selectedStage === key ? 'bg-teal-500' : 'bg-slate-300'"></span>
                          <span>{{ label }}</span>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Minor Awards Selector -->
                <div class="space-y-1.5">
                  <label class="text-sm font-medium text-slate-700">Minor Awards</label>
                  <div class="relative">
                    <button
                      @click="showMinorAwardDropdown = !showMinorAwardDropdown; showStageDropdown = false; showPaperSizeDropdown = false"
                      class="w-full flex items-center justify-between px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 hover:border-amber-300 hover:ring-2 hover:ring-amber-50 transition-all focus:outline-none"
                      :class="{ 'border-amber-400 ring-2 ring-amber-100': selectedMinorAward }"
                    >
                      <div class="flex items-center gap-2">
                        <Award class="h-4 w-4" :class="selectedMinorAward ? 'text-amber-500' : 'text-slate-400'" />
                        <span :class="selectedMinorAward ? 'text-amber-700' : 'text-slate-500'">
                          {{ selectedMinorAward === '__ALL__' ? 'All Minor Awards' : (selectedMinorAward ? `Best in ${selectedMinorAward}` : 'Select Minor Award') }}
                        </span>
                      </div>
                      <ChevronDown class="h-4 w-4 text-slate-400" />
                    </button>
                    
                    <div
                      v-if="showMinorAwardDropdown"
                      class="absolute left-0 right-0 z-20 mt-2 overflow-hidden rounded-xl border border-slate-100 bg-white shadow-xl ring-1 ring-black/5 max-h-64 overflow-y-auto"
                    >
                      <div class="p-1">
                        <button
                          @click="selectedMinorAward = ''; showMinorAwardDropdown = false"
                          class="w-full flex items-center gap-2 px-3 py-2 text-sm rounded-lg transition-all cursor-pointer"
                          :class="!selectedMinorAward ? 'bg-slate-50 text-slate-700' : 'text-slate-600 hover:bg-slate-50'"
                        >
                          <span class="w-1.5 h-1.5 rounded-full" :class="!selectedMinorAward ? 'bg-slate-500' : 'bg-slate-300'"></span>
                          <span>None (Show Results)</span>
                        </button>
                        <div v-if="minorAwardOptions.length === 0" class="px-3 py-2 text-sm text-slate-400 italic">
                          No minor awards available
                        </div>
                        <!-- Print All Minor Awards Option -->
                        <button
                          v-if="minorAwardOptions.length > 0"
                          @click="selectedMinorAward = '__ALL__'; showMinorAwardDropdown = false"
                          class="w-full flex items-center gap-2 px-3 py-2 text-sm rounded-lg transition-all cursor-pointer border-b border-slate-100 mb-1"
                          :class="selectedMinorAward === '__ALL__' ? 'bg-amber-100 text-amber-800' : 'text-slate-600 hover:bg-amber-50'"
                        >
                          <Award class="h-3.5 w-3.5" :class="selectedMinorAward === '__ALL__' ? 'text-amber-600' : 'text-slate-400'" />
                          <span class="font-medium">📋 Print All Minor Awards</span>
                        </button>
                        <button
                          v-for="award in minorAwardOptions"
                          :key="award.key"
                          @click="selectedMinorAward = award.key; showMinorAwardDropdown = false"
                          class="w-full flex items-center gap-2 px-3 py-2 text-sm rounded-lg transition-all cursor-pointer"
                          :class="selectedMinorAward === award.key ? 'bg-amber-50 text-amber-700' : 'text-slate-600 hover:bg-slate-50'"
                        >
                          <Award class="h-3.5 w-3.5" :class="selectedMinorAward === award.key ? 'text-amber-500' : 'text-slate-400'" />
                          <span>{{ award.label }}</span>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Paper Size Selector -->
                <div class="space-y-1.5">
                  <label class="text-sm font-medium text-slate-700">Paper Size</label>
                  <div class="relative">
                    <button
                      @click="showPaperSizeDropdown = !showPaperSizeDropdown; showStageDropdown = false; showMinorAwardDropdown = false"
                      class="w-full flex items-center justify-between px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 hover:border-teal-300 hover:ring-2 hover:ring-teal-50 transition-all focus:outline-none"
                    >
                      <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                        <span>{{ paperSizes[selectedPaperSize].name }}</span>
                      </div>
                      <ChevronDown class="h-4 w-4 text-slate-400" />
                    </button>
                    
                    <div
                      v-if="showPaperSizeDropdown"
                      class="absolute left-0 right-0 z-20 mt-2 overflow-hidden rounded-xl border border-slate-100 bg-white shadow-xl ring-1 ring-black/5"
                    >
                      <div class="p-1">
                        <button
                          v-for="(paper, key) in paperSizes"
                          :key="key"
                          @click="selectedPaperSize = key; showPaperSizeDropdown = false"
                          class="flex w-full items-center justify-between px-3 py-2 text-left text-sm rounded-lg transition-colors"
                          :class="selectedPaperSize === key ? 'bg-teal-50 text-teal-700' : 'text-slate-600 hover:bg-slate-50'"
                        >
                          <span>{{ paper.name }}</span>
                          <span class="w-1.5 h-1.5 rounded-full" :class="selectedPaperSize === key ? 'bg-teal-500' : 'bg-slate-300'"></span>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Preview Card -->
          <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
              <div>
                <h2 class="text-lg font-bold text-slate-900">Print Preview</h2>
                <p class="text-sm text-slate-500">Preview how the document will look when printed</p>
              </div>
              <div class="text-right hidden sm:block">
                <div class="text-sm font-medium text-slate-900">{{ pageant.name }}</div>
                <div class="text-xs text-slate-500">{{ selectedMinorAward === '__ALL__' ? 'All Minor Awards' : (selectedMinorAward ? `Best in ${selectedMinorAward}` : reportTitle) }}</div>
              </div>
            </div>
            
            <div class="p-8 bg-slate-100/50 overflow-x-auto">
              <div class="bg-white shadow-lg mx-auto transition-all duration-300" :style="{ width: getPreviewWidth(), minHeight: '11in' }">
                <div class="p-8" ref="printArea">
                  <!-- All Minor Awards Display (Condensed) -->
                  <template v-if="selectedMinorAward === '__ALL__'">
                    <div class="flex items-center gap-4 mb-6 pb-3 border-b-2 border-amber-400">
                      <!-- Logo -->
                      <div v-if="getLogoUrl" class="flex-shrink-0">
                        <img :src="getLogoUrl" alt="Pageant Logo" class="w-16 h-16 object-contain rounded-lg border border-amber-200" />
                      </div>
                      <!-- Header Content -->
                      <div class="flex-1 text-center" :class="{ 'pr-16': getLogoUrl }">
                        <h1 class="text-xl font-bold uppercase tracking-wide mb-1">{{ pageant.name }}</h1>
                        <div class="text-xs uppercase tracking-widest text-gray-600 mb-3">Minor Awards Summary</div>
                        
                        <div class="flex justify-center items-center gap-6 text-[10px] text-gray-600">
                          <span v-if="pageant.date">DATE: {{ pageant.date }}</span>
                          <span v-if="pageant.venue">VENUE: {{ pageant.venue }}</span>
                        </div>
                      </div>
                    </div>
                    
                    <!-- Condensed Minor Awards Grid -->
                    <div class="flex flex-col items-center gap-3">
                      <div v-for="award in minorAwardOptions" :key="award.key" class="border border-amber-200 rounded-lg p-3 bg-amber-50/30 w-full max-w-2xl">
                        <div class="text-center mb-2 pb-1 border-b border-amber-200">
                          <h3 class="text-sm font-bold text-amber-800">🏆 Best in {{ award.label }}</h3>
                        </div>
                        
                        <!-- Pair pageant: Show male & female side by side -->
                        <template v-if="isPairPageant && getMinorAwardDataByKey(award.key)">
                          <div class="grid grid-cols-2 gap-2 text-[10px]">
                            <!-- Male Winner -->
                            <div v-if="getMinorAwardDataByKey(award.key)?.male_winners?.length > 0" class="text-center border-r border-amber-100 pr-2">
                              <div class="text-[9px] font-semibold text-blue-700 mb-1">♂ Male</div>
                              <div v-for="winner in getMinorAwardDataByKey(award.key)?.male_winners" :key="winner.id">
                                <div class="font-bold text-gray-900">#{{ winner.number }}</div>
                                <div class="text-gray-700">{{ capitalizeName(winner.name) }}</div>
                                <div class="text-gray-500 text-[9px]">{{ winner.score?.toFixed(2) }} pts</div>
                              </div>
                            </div>
                            <div v-else class="text-center text-gray-400 italic border-r border-amber-100 pr-2">
                              <div class="text-[9px] font-semibold text-blue-300 mb-1">♂ Male</div>
                              No winner
                            </div>
                            <!-- Female Winner -->
                            <div v-if="getMinorAwardDataByKey(award.key)?.female_winners?.length > 0" class="text-center pl-2">
                              <div class="text-[9px] font-semibold text-pink-700 mb-1">♀ Female</div>
                              <div v-for="winner in getMinorAwardDataByKey(award.key)?.female_winners" :key="winner.id">
                                <div class="font-bold text-gray-900">#{{ winner.number }}</div>
                                <div class="text-gray-700">{{ capitalizeName(winner.name) }}</div>
                                <div class="text-gray-500 text-[9px]">{{ winner.score?.toFixed(2) }} pts</div>
                              </div>
                            </div>
                            <div v-else class="text-center text-gray-400 italic pl-2">
                              <div class="text-[9px] font-semibold text-pink-300 mb-1">♀ Female</div>
                              No winner
                            </div>
                          </div>
                        </template>
                        
                        <!-- Solo pageant: Show single winner -->
                        <template v-else-if="getMinorAwardDataByKey(award.key)?.winners?.length > 0">
                          <div v-for="winner in getMinorAwardDataByKey(award.key)?.winners" :key="winner.id" class="text-center text-[10px]">
                            <div class="font-bold text-gray-900">#{{ winner.number }} - {{ capitalizeName(winner.name) }}</div>
                            <div class="text-gray-500">{{ winner.score?.toFixed(2) }} pts</div>
                          </div>
                        </template>
                        
                        <!-- No winner -->
                        <div v-else class="text-center text-[10px] text-gray-400 italic py-2">
                          No winner determined
                        </div>
                      </div>
                    </div>
                    
                    <!-- Signatures Section for All Minor Awards -->
                    <div class="mt-8 pt-4 border-t border-gray-200">
                      <div class="grid grid-cols-2 gap-6">
                        <!-- Panel of Judges -->
                        <div>
                          <h3 class="text-[10px] font-bold uppercase border-b border-black mb-3 pb-1">Panel of Judges</h3>
                          <div class="grid grid-cols-2 gap-x-4 gap-y-6">
                            <div v-for="judge in judges" :key="judge.id" class="text-center">
                              <div class="border-b border-gray-400 h-6"></div>
                              <div class="text-[9px] font-bold mt-1">{{ capitalizeName(judge.name) }}</div>
                              <div class="text-[8px] uppercase text-gray-500">{{ judge.role }}</div>
                            </div>
                          </div>
                        </div>
                        
                        <!-- Tabulator Certification -->
                        <div class="flex flex-col justify-end">
                          <div class="text-right">
                            <div class="text-[9px] uppercase text-gray-500 mb-4">Certified Correct:</div>
                            <div class="border-b border-black h-6 w-48 ml-auto"></div>
                            <div class="text-[10px] font-bold mt-1">{{ tabulatorName }}</div>
                            <div class="text-[8px] uppercase text-gray-500">Head Tabulator</div>
                            <div class="text-[8px] text-gray-500">{{ new Date().toLocaleDateString() }}</div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </template>
                  
                  <!-- Single Minor Award Display -->
                  <template v-else-if="selectedMinorAward && selectedMinorAwardData">
                    <div class="flex items-center gap-4 mb-8 pb-4 border-b-2 border-amber-400">
                      <!-- Logo -->
                      <div v-if="getLogoUrl" class="flex-shrink-0">
                        <img :src="getLogoUrl" alt="Pageant Logo" class="w-20 h-20 object-contain rounded-lg border border-amber-200" />
                      </div>
                      <!-- Header Content -->
                      <div class="flex-1 text-center" :class="{ 'pr-20': getLogoUrl }">
                        <h1 class="text-2xl font-bold uppercase tracking-wide mb-1">{{ pageant.name }}</h1>
                        <div class="text-sm uppercase tracking-widest text-gray-600 mb-4">Minor Awards Report</div>
                        
                        <div class="flex justify-center items-center gap-8 text-xs text-gray-600">
                          <span v-if="pageant.date">DATE: {{ pageant.date }}</span>
                          <span v-if="pageant.venue">VENUE: {{ pageant.venue }}</span>
                        </div>
                        
                        <div class="mt-6">
                          <h2 class="text-xl font-bold text-amber-700 flex items-center justify-center gap-2">
                            <Award class="h-6 w-6" />
                            Best in {{ selectedMinorAward }}
                          </h2>
                        </div>
                      </div>
                    </div>
                    
                    <!-- Minor Award Winners -->
                    <div class="space-y-6">
                      <!-- For pair pageants with gender-specific winners -->
                      <template v-if="isPairPageant && ((selectedMinorAwardData.male_winners && selectedMinorAwardData.male_winners.length > 0) || (selectedMinorAwardData.female_winners && selectedMinorAwardData.female_winners.length > 0))">
                        <div class="grid grid-cols-2 gap-8">
                          <!-- Male Winners -->
                          <div v-if="selectedMinorAwardData.male_winners && selectedMinorAwardData.male_winners.length > 0" class="border-r border-slate-200 pr-4">
                            <div class="mb-4 pb-2 border-b border-blue-200">
                              <div class="flex items-center justify-center gap-2 text-sm font-semibold text-blue-900">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs">♂</span>
                                Male Winner
                              </div>
                            </div>
                            <div v-for="winner in selectedMinorAwardData.male_winners" :key="winner.id" class="text-center py-4">
                              <div class="text-3xl font-bold text-amber-500 mb-2">🏆</div>
                              <div class="text-lg font-bold text-gray-900">#{{ winner.number }} - {{ winner.name }}</div>
                              <div class="text-sm text-gray-600 mt-1">Score: {{ winner.score?.toFixed(2) }}</div>
                            </div>
                          </div>
                          
                          <!-- Female Winners -->
                          <div v-if="selectedMinorAwardData.female_winners && selectedMinorAwardData.female_winners.length > 0" class="pl-4">
                            <div class="mb-4 pb-2 border-b border-pink-200">
                              <div class="flex items-center justify-center gap-2 text-sm font-semibold text-pink-900">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-pink-100 text-pink-700 text-xs">♀</span>
                                Female Winner
                              </div>
                            </div>
                            <div v-for="winner in selectedMinorAwardData.female_winners" :key="winner.id" class="text-center py-4">
                              <div class="text-3xl font-bold text-amber-500 mb-2">🏆</div>
                              <div class="text-lg font-bold text-gray-900">#{{ winner.number }} - {{ winner.name }}</div>
                              <div class="text-sm text-gray-600 mt-1">Score: {{ winner.score?.toFixed(2) }}</div>
                            </div>
                          </div>
                        </div>
                      </template>
                      
                      <!-- Standard winners (non-pair pageant) -->
                      <template v-else-if="selectedMinorAwardData.winners && selectedMinorAwardData.winners.length > 0">
                        <div class="text-center py-6">
                          <div v-for="winner in selectedMinorAwardData.winners" :key="winner.id" class="py-4">
                            <div class="text-4xl font-bold text-amber-500 mb-3">🏆</div>
                            <div class="text-xl font-bold text-gray-900">#{{ winner.number }} - {{ winner.name }}</div>
                            <div class="text-sm text-gray-600 mt-2">Score: {{ winner.score?.toFixed(2) }}</div>
                          </div>
                        </div>
                      </template>
                      
                      <!-- No winners message -->
                      <div v-else class="text-center py-12 text-gray-500">
                        <Award class="h-12 w-12 mx-auto mb-4 text-gray-300" />
                        <p>No winners found for this award</p>
                      </div>
                    </div>
                    
                    <!-- Signatures for Minor Award -->
                    <div class="mt-12 page-break-inside-avoid">
                      <div class="grid grid-cols-3 gap-8">
                        <div class="col-span-3 mb-8">
                          <h3 class="text-xs font-bold uppercase border-b border-black mb-4 pb-1">Panel of Judges</h3>
                          <div class="grid grid-cols-3 gap-x-8 gap-y-12">
                            <div v-for="judge in judges" :key="judge.id" class="text-center">
                              <div class="border-b border-gray-400 h-8"></div>
                              <div class="text-xs font-bold mt-1">{{ capitalizeName(judge.name) }}</div>
                              <div class="text-[10px] uppercase text-gray-500">{{ judge.role }}</div>
                            </div>
                          </div>
                        </div>
                        <div class="col-span-3">
                          <div class="flex justify-end">
                            <div class="w-64 text-center">
                              <div class="text-[10px] uppercase text-gray-500 mb-8 text-left">Certified Correct:</div>
                              <div class="border-b border-black h-8"></div>
                              <div class="text-xs font-bold mt-1">{{ tabulatorName }}</div>
                              <div class="text-[10px] uppercase text-gray-500">Head Tabulator</div>
                              <div class="text-[10px] text-gray-500">{{ new Date().toLocaleDateString() }}</div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </template>
                  
                  <!-- Normal Results Display (when no minor award selected) -->
                  <template v-else>
                  <!-- For pair pageants, show side by side in single view -->
                  <template v-if="isPairPageant && maleResults.length > 0 && femaleResults.length > 0">
                    <!-- Unified Header for Pair Pageants -->
                    <div class="flex items-center gap-4 mb-8 pb-4 border-b-2 border-black">
                      <!-- Logo -->
                      <div v-if="getLogoUrl" class="flex-shrink-0">
                        <img :src="getLogoUrl" alt="Pageant Logo" class="w-20 h-20 object-contain rounded-lg border border-gray-200" />
                      </div>
                      <!-- Header Content -->
                      <div class="flex-1 text-center" :class="{ 'pr-20': getLogoUrl }">
                        <h1 class="text-2xl font-bold uppercase tracking-wide mb-1">{{ pageant.name }}</h1>
                        <div class="text-sm uppercase tracking-widest text-gray-600 mb-4">Official Tabulation Report</div>
                        
                        <div class="flex justify-center items-center gap-8 text-xs text-gray-600">
                          <span v-if="pageant.date">DATE: {{ pageant.date }}</span>
                          <span v-if="pageant.venue">VENUE: {{ pageant.venue }}</span>
                        </div>
                        
                        <div class="mt-6">
                          <h2 class="text-xl font-bold text-black">{{ reportTitle || 'Final Results' }}</h2>
                        </div>
                      </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-8">
                      <!-- Male Column -->
                      <div class="border-r-2 border-slate-200 pr-4">
                        <div class="mb-4 pb-2 border-b border-blue-200">
                          <div class="flex items-center justify-center gap-2 text-sm font-semibold text-blue-900">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs">♂</span>
                            Male
                          </div>
                        </div>
                        <PrintableResults
                          :pageant="pageant"
                          :results="maleResults"
                          :rank-reference-results="rankReferenceResults"
                          :judges="judges"
                          :rounds="rounds"
                          :report-title="reportTitle"
                          :is-male-category="true"
                          :is-last-final-round="isLastFinalRound"
                          :number-of-winners="pageant?.number_of_winners || 3"
                          :hide-rank-column="selectedStage === 'overall'"
                          :show-all-rounds="selectedStage === 'overall'"
                          :selected-stage="selectedStage"
                          :ranking-method="pageant?.ranking_method || 'score_average'"
                          :final-score-mode="pageant?.final_score_mode || 'fresh'"
                        />
                      </div>
                      
                      <!-- Female Column -->
                      <div class="pl-4">
                        <div class="mb-4 pb-2 border-b border-pink-200">
                          <div class="flex items-center justify-center gap-2 text-sm font-semibold text-pink-900">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-pink-100 text-pink-700 text-xs">♀</span>
                            Female
                          </div>
                        </div>
                        <PrintableResults
                          :pageant="pageant"
                          :results="femaleResults"
                          :rank-reference-results="rankReferenceResults"
                          :judges="judges"
                          :rounds="rounds"
                          :report-title="reportTitle"
                          :is-female-category="true"
                          :is-last-final-round="isLastFinalRound"
                          :number-of-winners="pageant?.number_of_winners || 3"
                          :hide-rank-column="selectedStage === 'overall'"
                          :show-all-rounds="selectedStage === 'overall'"
                          :selected-stage="selectedStage"
                          :ranking-method="pageant?.ranking_method || 'score_average'"
                          :final-score-mode="pageant?.final_score_mode || 'fresh'"
                        />
                      </div>
                    </div>

                    <!-- Single Signatures Section for both genders -->
                    <div class="mt-12 page-break-inside-avoid">
                      <div class="grid grid-cols-3 gap-8">
                        <!-- Judges Signatures -->
                        <div class="col-span-3 mb-8">
                          <h3 class="text-xs font-bold uppercase border-b border-black mb-4 pb-1">Panel of Judges</h3>
                          <div class="grid grid-cols-3 gap-x-8 gap-y-12">
                            <div v-for="judge in judges" :key="judge.id" class="text-center">
                              <div class="border-b border-gray-400 h-8"></div>
                              <div class="text-xs font-bold mt-1">{{ capitalizeName(judge.name) }}</div>
                              <div class="text-[10px] uppercase text-gray-500">{{ judge.role }}</div>
                            </div>
                          </div>
                        </div>

                        <!-- Certification -->
                        <div class="col-span-3">
                          <div class="flex justify-end">
                            <div class="w-64 text-center">
                              <div class="text-[10px] uppercase text-gray-500 mb-8 text-left">Certified Correct:</div>
                              <div class="border-b border-black h-8"></div>
                              <div class="text-xs font-bold mt-1">{{ tabulatorName }}</div>
                              <div class="text-[10px] uppercase text-gray-500">Head Tabulator</div>
                              <div class="text-[10px] text-gray-500">{{ new Date().toLocaleDateString() }}</div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </template>
                  
                  <!-- Single gender or standard pageant -->
                  <template v-else-if="isPairPageant">
                    <div v-if="maleResults.length > 0">
                      <div class="text-center mb-6 pb-3 border-b-2 border-blue-300">
                        <h3 class="text-xl font-bold text-blue-900 flex items-center justify-center gap-2">
                          <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-700">♂</span>
                          Male Category
                        </h3>
                      </div>
                      <PrintableResults
                        :pageant="pageant"
                        :results="maleResults"
                        :rank-reference-results="rankReferenceResults"
                        :judges="judges"
                        :rounds="rounds"
                        :report-title="`${reportTitle} - Male`"
                        :is-male-category="true"
                        :is-last-final-round="isLastFinalRound"
                        :number-of-winners="pageant?.number_of_winners || 3"
                        :show-all-rounds="selectedStage === 'overall'"
                        :selected-stage="selectedStage"
                        :ranking-method="pageant?.ranking_method || 'score_average'"
                        :final-score-mode="pageant?.final_score_mode || 'fresh'"
                      />
                    </div>
                    
                    <div v-if="femaleResults.length > 0" :class="{ 'page-break-before': maleResults.length > 0 }">
                      <div class="text-center mb-6 pb-3 border-b-2 border-pink-300">
                        <h3 class="text-xl font-bold text-pink-900 flex items-center justify-center gap-2">
                          <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-pink-100 text-pink-700">♀</span>
                          Female Category
                        </h3>
                      </div>
                      <PrintableResults
                        :pageant="pageant"
                        :results="femaleResults"
                        :rank-reference-results="rankReferenceResults"
                        :judges="judges"
                        :rounds="rounds"
                        :report-title="`${reportTitle} - Female`"
                        :is-female-category="true"
                        :is-last-final-round="isLastFinalRound"
                        :number-of-winners="pageant?.number_of_winners || 3"
                        :show-all-rounds="selectedStage === 'overall'"
                        :selected-stage="selectedStage"
                        :ranking-method="pageant?.ranking_method || 'score_average'"
                        :final-score-mode="pageant?.final_score_mode || 'fresh'"
                      />
                    </div>
                  </template>
                  
                  <!-- Standard single ranking -->
                  <PrintableResults
                    v-else
                    :pageant="pageant"
                    :results="resultsToShow"
                    :rank-reference-results="rankReferenceResults"
                    :judges="judges"
                    :rounds="rounds"
                    :report-title="reportTitle"
                    :is-last-final-round="isLastFinalRound"
                    :number-of-winners="pageant?.number_of_winners || 3"
                    :hide-rank-column="selectedStage === 'overall'"
                    :show-all-rounds="selectedStage === 'overall'"
                    :selected-stage="selectedStage"
                    :ranking-method="pageant?.ranking_method || 'score_average'"
                    :final-score-mode="pageant?.final_score_mode || 'fresh'"
                  />
                  </template>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Print-Only Content -->
    <div v-if="pageant" class="print-only">
      <!-- All Minor Awards Print (Condensed on One Page) -->
      <template v-if="selectedMinorAward === '__ALL__'">
        <div class="flex items-center gap-3 mb-4 pb-2 border-b-2 border-amber-400">
          <!-- Logo -->
          <div v-if="getLogoUrl" class="flex-shrink-0">
            <img :src="getLogoUrl" alt="Pageant Logo" class="w-14 h-14 object-contain rounded border border-amber-200" />
          </div>
          <!-- Header Content -->
          <div class="flex-1 text-center" :class="{ 'pr-14': getLogoUrl }">
            <h1 class="text-xl font-bold uppercase tracking-wide mb-1">{{ pageant.name }}</h1>
            <div class="text-xs uppercase tracking-widest text-gray-600 mb-2">Minor Awards Summary</div>
            
            <div class="flex justify-center items-center gap-6 text-[10px] text-gray-600">
              <span v-if="pageant.date">DATE: {{ pageant.date }}</span>
              <span v-if="pageant.venue">VENUE: {{ pageant.venue }}</span>
            </div>
          </div>
        </div>
        
        <!-- Condensed Minor Awards Grid for Print -->
        <div class="flex flex-col items-center gap-2">
          <div v-for="award in minorAwardOptions" :key="award.key" class="border border-amber-200 rounded p-2 bg-amber-50/30 w-full max-w-2xl">
            <div class="text-center mb-1 pb-1 border-b border-amber-200">
              <h3 class="text-xs font-bold text-amber-800">🏆 {{ award.label }}</h3>
            </div>
            
            <!-- Pair pageant: Show male & female side by side -->
            <template v-if="isPairPageant && getMinorAwardDataByKey(award.key)">
              <div class="grid grid-cols-2 gap-2 text-[9px]">
                <!-- Male Winner -->
                <div v-if="getMinorAwardDataByKey(award.key)?.male_winners?.length > 0" class="text-center border-r border-amber-100 pr-1">
                  <div class="text-[8px] font-semibold text-blue-700 mb-0.5">♂ Male</div>
                  <div v-for="winner in getMinorAwardDataByKey(award.key)?.male_winners" :key="winner.id">
                    <div class="font-bold text-gray-900">#{{ winner.number }}</div>
                    <div class="text-gray-700 leading-tight">{{ capitalizeName(winner.name) }}</div>
                    <div class="text-gray-500 text-[8px]">{{ winner.score?.toFixed(2) }} pts</div>
                  </div>
                </div>
                <div v-else class="text-center text-gray-400 italic border-r border-amber-100 pr-1">
                  <div class="text-[8px] font-semibold text-blue-300 mb-0.5">♂ Male</div>
                  —
                </div>
                <!-- Female Winner -->
                <div v-if="getMinorAwardDataByKey(award.key)?.female_winners?.length > 0" class="text-center pl-1">
                  <div class="text-[8px] font-semibold text-pink-700 mb-0.5">♀ Female</div>
                  <div v-for="winner in getMinorAwardDataByKey(award.key)?.female_winners" :key="winner.id">
                    <div class="font-bold text-gray-900">#{{ winner.number }}</div>
                    <div class="text-gray-700 leading-tight">{{ capitalizeName(winner.name) }}</div>
                    <div class="text-gray-500 text-[8px]">{{ winner.score?.toFixed(2) }} pts</div>
                  </div>
                </div>
                <div v-else class="text-center text-gray-400 italic pl-1">
                  <div class="text-[8px] font-semibold text-pink-300 mb-0.5">♀ Female</div>
                  —
                </div>
              </div>
            </template>
            
            <!-- Solo pageant: Show single winner -->
            <template v-else-if="getMinorAwardDataByKey(award.key)?.winners?.length > 0">
              <div v-for="winner in getMinorAwardDataByKey(award.key)?.winners" :key="winner.id" class="text-center text-[9px]">
                <div class="font-bold text-gray-900">#{{ winner.number }} - {{ capitalizeName(winner.name) }}</div>
                <div class="text-gray-500 text-[8px]">{{ winner.score?.toFixed(2) }} pts</div>
              </div>
            </template>
            
            <!-- No winner -->
            <div v-else class="text-center text-[9px] text-gray-400 italic py-1">
              No winner determined
            </div>
          </div>
        </div>
        
        <!-- Signatures Section for All Minor Awards Print -->
        <div class="mt-6 pt-3 border-t border-gray-200">
          <div class="grid grid-cols-2 gap-4">
            <!-- Panel of Judges -->
            <div>
              <h3 class="text-[9px] font-bold uppercase border-b border-black mb-2 pb-0.5">Panel of Judges</h3>
              <div class="grid grid-cols-2 gap-x-3 gap-y-4">
                <div v-for="judge in judges" :key="judge.id" class="text-center">
                  <div class="border-b border-gray-400 h-5"></div>
                  <div class="text-[8px] font-bold mt-0.5">{{ capitalizeName(judge.name) }}</div>
                  <div class="text-[7px] uppercase text-gray-500">{{ judge.role }}</div>
                </div>
              </div>
            </div>
            
            <!-- Tabulator Certification -->
            <div class="flex flex-col justify-end">
              <div class="text-right">
                <div class="text-[8px] uppercase text-gray-500 mb-3">Certified Correct:</div>
                <div class="border-b border-black h-5 w-40 ml-auto"></div>
                <div class="text-[9px] font-bold mt-0.5">{{ tabulatorName }}</div>
                <div class="text-[7px] uppercase text-gray-500">Head Tabulator</div>
                <div class="text-[7px] text-gray-500">{{ new Date().toLocaleDateString() }}</div>
              </div>
            </div>
          </div>
        </div>
      </template>
      
      <!-- Single Minor Award Print -->
      <template v-else-if="selectedMinorAward && selectedMinorAwardData">
        <div class="flex items-center gap-4 mb-8 pb-4 border-b-2 border-amber-400">
          <!-- Logo -->
          <div v-if="getLogoUrl" class="flex-shrink-0">
            <img :src="getLogoUrl" alt="Pageant Logo" class="w-20 h-20 object-contain rounded-lg border border-amber-200" />
          </div>
          <!-- Header Content -->
          <div class="flex-1 text-center" :class="{ 'pr-20': getLogoUrl }">
            <h1 class="text-2xl font-bold uppercase tracking-wide mb-1">{{ pageant.name }}</h1>
            <div class="text-sm uppercase tracking-widest text-gray-600 mb-4">Minor Awards Report</div>
            
            <div class="flex justify-center items-center gap-8 text-xs text-gray-600">
              <span v-if="pageant.date">DATE: {{ pageant.date }}</span>
              <span v-if="pageant.venue">VENUE: {{ pageant.venue }}</span>
            </div>
            
            <div class="mt-6">
              <h2 class="text-xl font-bold text-amber-700">Best in {{ selectedMinorAward }}</h2>
            </div>
          </div>
        </div>
        
        <!-- Minor Award Winners for Print -->
        <div class="space-y-6">
          <!-- For pair pageants with gender-specific winners -->
          <template v-if="isPairPageant && ((selectedMinorAwardData.male_winners && selectedMinorAwardData.male_winners.length > 0) || (selectedMinorAwardData.female_winners && selectedMinorAwardData.female_winners.length > 0))">
            <div class="grid grid-cols-2 gap-8">
              <!-- Male Winners -->
              <div v-if="selectedMinorAwardData.male_winners && selectedMinorAwardData.male_winners.length > 0" class="border-r border-slate-200 pr-4">
                <div class="mb-4 pb-2 border-b border-blue-200">
                  <div class="flex items-center justify-center gap-2 text-sm font-semibold text-blue-900">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs">♂</span>
                    Male Winner
                  </div>
                </div>
                <div v-for="winner in selectedMinorAwardData.male_winners" :key="winner.id" class="text-center py-4">
                  <div class="text-3xl font-bold text-amber-500 mb-2">🏆</div>
                  <div class="text-lg font-bold text-gray-900">#{{ winner.number }} - {{ winner.name }}</div>
                  <div class="text-sm text-gray-600 mt-1">Score: {{ winner.score?.toFixed(2) }}</div>
                </div>
              </div>
              
              <!-- Female Winners -->
              <div v-if="selectedMinorAwardData.female_winners && selectedMinorAwardData.female_winners.length > 0" class="pl-4">
                <div class="mb-4 pb-2 border-b border-pink-200">
                  <div class="flex items-center justify-center gap-2 text-sm font-semibold text-pink-900">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-pink-100 text-pink-700 text-xs">♀</span>
                    Female Winner
                  </div>
                </div>
                <div v-for="winner in selectedMinorAwardData.female_winners" :key="winner.id" class="text-center py-4">
                  <div class="text-3xl font-bold text-amber-500 mb-2">🏆</div>
                  <div class="text-lg font-bold text-gray-900">#{{ winner.number }} - {{ winner.name }}</div>
                  <div class="text-sm text-gray-600 mt-1">Score: {{ winner.score?.toFixed(2) }}</div>
                </div>
              </div>
            </div>
          </template>
          
          <!-- Standard winners (non-pair pageant) -->
          <template v-else-if="selectedMinorAwardData.winners && selectedMinorAwardData.winners.length > 0">
            <div class="text-center py-6">
              <div v-for="winner in selectedMinorAwardData.winners" :key="winner.id" class="py-4">
                <div class="text-4xl font-bold text-amber-500 mb-3">🏆</div>
                <div class="text-xl font-bold text-gray-900">#{{ winner.number }} - {{ winner.name }}</div>
                <div class="text-sm text-gray-600 mt-2">Score: {{ winner.score?.toFixed(2) }}</div>
              </div>
            </div>
          </template>
          
          <!-- No winners message -->
          <div v-else class="text-center py-12 text-gray-500">
            <p>No winners found for this award</p>
          </div>
        </div>
        
        <!-- Signatures for Minor Award Print -->
        <div class="mt-12 page-break-inside-avoid">
          <div class="grid grid-cols-3 gap-8">
            <div class="col-span-3 mb-8">
              <h3 class="text-xs font-bold uppercase border-b border-black mb-4 pb-1">Panel of Judges</h3>
              <div class="grid grid-cols-3 gap-x-8 gap-y-12">
                <div v-for="judge in judges" :key="judge.id" class="text-center">
                  <div class="border-b border-gray-400 h-8"></div>
                  <div class="text-xs font-bold mt-1">{{ capitalizeName(judge.name) }}</div>
                  <div class="text-[10px] uppercase text-gray-500">{{ judge.role }}</div>
                </div>
              </div>
            </div>
            <div class="col-span-3">
              <div class="flex justify-end">
                <div class="w-64 text-center">
                  <div class="text-[10px] uppercase text-gray-500 mb-8 text-left">Certified Correct:</div>
                  <div class="border-b border-black h-8"></div>
                  <div class="text-xs font-bold mt-1">{{ tabulatorName }}</div>
                  <div class="text-[10px] uppercase text-gray-500">Head Tabulator</div>
                  <div class="text-[10px] text-gray-500">{{ new Date().toLocaleDateString() }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>
      
      <!-- Normal Results Print (when no minor award selected) -->
      <template v-else>
      <!-- For pair pageants, show side by side in single view -->
      <template v-if="isPairPageant && maleResults.length > 0 && femaleResults.length > 0">
        <!-- Unified Header for Pair Pageants -->
        <div class="flex items-center gap-4 mb-8 pb-4 border-b-2 border-black">
          <!-- Logo -->
          <div v-if="getLogoUrl" class="flex-shrink-0">
            <img :src="getLogoUrl" alt="Pageant Logo" class="w-20 h-20 object-contain rounded-lg border border-gray-200" />
          </div>
          <!-- Header Content -->
          <div class="flex-1 text-center" :class="{ 'pr-20': getLogoUrl }">
            <h1 class="text-2xl font-bold uppercase tracking-wide mb-1">{{ pageant.name }}</h1>
            <div class="text-sm uppercase tracking-widest text-gray-600 mb-4">Official Tabulation Report</div>
            
            <div class="flex justify-center items-center gap-8 text-xs text-gray-600">
              <span v-if="pageant.date">DATE: {{ pageant.date }}</span>
              <span v-if="pageant.venue">VENUE: {{ pageant.venue }}</span>
            </div>
            
            <div class="mt-6">
              <h2 class="text-xl font-bold text-black">{{ reportTitle || 'Final Results' }}</h2>
            </div>
          </div>
        </div>
        
        <div class="grid grid-cols-2 gap-6">
          <!-- Male Column -->
          <div class="border-r-2 border-slate-200 pr-3">
            <div class="mb-3 pb-1 border-b border-blue-200">
              <div class="flex items-center justify-center gap-2 text-xs font-semibold text-blue-900">
                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-100 text-blue-700 text-[10px]">♂</span>
                Male
              </div>
            </div>
            <PrintableResults
              :pageant="pageant"
              :results="maleResults"
              :rank-reference-results="rankReferenceResults"
              :judges="judges"
              :rounds="rounds"
              :report-title="reportTitle"
              :is-male-category="true"
              :is-last-final-round="isLastFinalRound"
              :number-of-winners="pageant?.number_of_winners || 3"
              :hide-rank-column="selectedStage === 'overall'"
              :show-all-rounds="selectedStage === 'overall'"
              :selected-stage="selectedStage"
              :ranking-method="pageant?.ranking_method || 'score_average'"
              :final-score-mode="pageant?.final_score_mode || 'fresh'"
            />
          </div>
          
          <!-- Female Column -->
          <div class="pl-3">
            <div class="mb-3 pb-1 border-b border-pink-200">
              <div class="flex items-center justify-center gap-2 text-xs font-semibold text-pink-900">
                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-pink-100 text-pink-700 text-[10px]">♀</span>
                Female
              </div>
            </div>
            <PrintableResults
              :pageant="pageant"
              :results="femaleResults"
              :rank-reference-results="rankReferenceResults"
              :judges="judges"
              :rounds="rounds"
              :report-title="reportTitle"
              :is-female-category="true"
              :is-last-final-round="isLastFinalRound"
              :number-of-winners="pageant?.number_of_winners || 3"
              :hide-rank-column="selectedStage === 'overall'"
              :show-all-rounds="selectedStage === 'overall'"
              :selected-stage="selectedStage"
              :ranking-method="pageant?.ranking_method || 'score_average'"
              :final-score-mode="pageant?.final_score_mode || 'fresh'"
            />
          </div>
        </div>

        <!-- Single Signatures Section for both genders -->
        <div class="mt-12 page-break-inside-avoid">
          <div class="grid grid-cols-3 gap-8">
            <!-- Judges Signatures -->
            <div class="col-span-3 mb-8">
              <h3 class="text-xs font-bold uppercase border-b border-black mb-4 pb-1">Panel of Judges</h3>
              <div class="grid grid-cols-3 gap-x-8 gap-y-12">
                <div v-for="judge in judges" :key="judge.id" class="text-center">
                  <div class="border-b border-gray-400 h-8"></div>
                  <div class="text-xs font-bold mt-1">{{ capitalizeName(judge.name) }}</div>
                  <div class="text-[10px] uppercase text-gray-500">{{ judge.role }}</div>
                </div>
              </div>
            </div>

            <!-- Certification -->
            <div class="col-span-3">
              <div class="flex justify-end">
                <div class="w-64 text-center">
                  <div class="text-[10px] uppercase text-gray-500 mb-8 text-left">Certified Correct:</div>
                  <div class="border-b border-black h-8"></div>
                  <div class="text-xs font-bold mt-1">{{ tabulatorName }}</div>
                  <div class="text-[10px] uppercase text-gray-500">Head Tabulator</div>
                  <div class="text-[10px] text-gray-500">{{ new Date().toLocaleDateString() }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>
      
      <!-- Single gender or standard pageant -->
      <template v-else-if="isPairPageant">
        <div v-if="maleResults.length > 0">
          <div class="text-center mb-6 pb-3 border-b-2 border-blue-300">
            <h3 class="text-xl font-bold text-blue-900">Male Category</h3>
          </div>
          <PrintableResults
            :pageant="pageant"
            :results="maleResults"
            :rank-reference-results="rankReferenceResults"
            :judges="judges"
            :rounds="rounds"
            :report-title="`${reportTitle} - Male`"
            :is-male-category="true"
            :is-last-final-round="isLastFinalRound"
            :number-of-winners="pageant?.number_of_winners || 3"
            :show-all-rounds="selectedStage === 'overall'"
            :selected-stage="selectedStage"
            :ranking-method="pageant?.ranking_method || 'score_average'"
            :final-score-mode="pageant?.final_score_mode || 'fresh'"
          />
        </div>
        
        <div v-if="femaleResults.length > 0" :class="{ 'page-break-before': maleResults.length > 0 }">
          <div class="text-center mb-6 pb-3 border-b-2 border-pink-300">
            <h3 class="text-xl font-bold text-pink-900">Female Category</h3>
          </div>
          <PrintableResults
            :pageant="pageant"
            :results="femaleResults"
            :rank-reference-results="rankReferenceResults"
            :judges="judges"
            :rounds="rounds"
            :report-title="`${reportTitle} - Female`"
            :is-female-category="true"
            :is-last-final-round="isLastFinalRound"
            :number-of-winners="pageant?.number_of_winners || 3"
            :show-all-rounds="selectedStage === 'overall'"
            :selected-stage="selectedStage"
            :ranking-method="pageant?.ranking_method || 'score_average'"
            :final-score-mode="pageant?.final_score_mode || 'fresh'"
          />
        </div>
      </template>
      
      <!-- Standard single ranking -->
      <PrintableResults
        v-else
        :pageant="pageant"
        :results="resultsToShow"
        :rank-reference-results="rankReferenceResults"
        :judges="judges"
        :rounds="rounds"
        :report-title="reportTitle"
        :is-last-final-round="isLastFinalRound"
        :number-of-winners="pageant?.number_of_winners || 3"
        :hide-rank-column="selectedStage === 'overall'"
        :show-all-rounds="selectedStage === 'overall'"
        :selected-stage="selectedStage"
        :ranking-method="pageant?.ranking_method || 'score_average'"
        :final-score-mode="pageant?.final_score_mode || 'fresh'"
      />
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { Printer, LayoutDashboard, ChevronDown, FileText, Layers, Maximize, Award, AlertCircle, Lock } from 'lucide-vue-next'
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import PrintableResults from '../../Components/tabulator/PrintableResults.vue'
import TabulatorLayout from '../../Layouts/TabulatorLayout.vue'

defineOptions({
  layout: TabulatorLayout
})

interface JudgeRankData {
  ranks: number[]
  scores?: number[]
  details?: Array<{ judge_id: number; judge_name?: string; score: number; rank?: number }>
}

interface Result {
  id: number
  number: number
  name: string
  gender?: string
  image: string
  scores: Record<string, number>
  displayScores?: Record<string, number>
  displayTotal?: number
  totalScore?: number
  final_score: number
  totalRankSum?: number
  weightedRawTotal?: number
  judgeRanks?: Record<string, JudgeRankData>
  rank?: number
  qualified?: boolean
  qualification_cutoff?: number | null
  hasQualifiedForFinal?: boolean
  is_pair?: boolean
  member_names?: string[]
  member_genders?: string[]
  inheritanceBreakdown?: Record<string, { stageType: string; percentage: number; stageAverage: number; contribution: number }>
}

interface Pageant {
  id: number
  name: string
  contestant_type?: string
  date?: string
  venue?: string
  location?: string
  number_of_winners?: number
  logo?: string
  ranking_method?: 'score_average' | 'rank_sum' | 'ordinal'
  final_score_mode?: 'fresh' | 'inherit'
  final_score_inheritance?: Record<string, number>
}

interface Judge {
  id: number
  name: string
  role: string
}

interface Round {
  id: number
  name: string
  type: string
  display_order: number
}

interface RoundType {
  key: string
  label: string
  display_order: number
}

interface MinorAwardWinner {
  id: number
  number: number
  name: string
  gender?: string
  score: number
  is_pair?: boolean
  member_names?: string[]
  member_genders?: string[]
  image?: string
}

interface MinorAwardRound {
  round: {
    id: number
    name: string
    type: string
  }
  male_winners?: MinorAwardWinner[]
  female_winners?: MinorAwardWinner[]
  winners: MinorAwardWinner[]
}

interface Tabulator {
  id: number
  name: string
}

interface UnlockedRound {
  id: number
  name: string
  type: string
}

interface RoundResult {
  contestants: Result[]
  top_n_proceed?: number
}

interface Props {
  pageant?: Pageant
  rounds?: Round[]
  roundTypes: RoundType[]
  roundResults?: Record<string, RoundResult>
  resultsByRoundType?: Record<string, Result[]>
  resultsOverall: Result[]
  overallTally?: Result[]
  finalTopN?: Result[]
  resultsSemiFinal: Result[]
  resultsFinal: Result[]
  minorAwards?: Record<string, MinorAwardRound>
  judges: Judge[]
  tabulator?: Tabulator
  allRoundsLocked?: boolean
  unlockedRounds?: UnlockedRound[]
}

const props = defineProps<Props>()

const isRankSumMethod = computed(() => {
  return props.pageant?.ranking_method === 'rank_sum'
})

const isOrdinalMethod = computed(() => {
  return props.pageant?.ranking_method === 'ordinal'
})

const printArea = ref<HTMLElement | null>(null)
const showStageDropdown = ref(false)
const showPaperSizeDropdown = ref(false)
const showMinorAwardDropdown = ref(false)
const selectedPaperSize = ref<keyof typeof paperSizes>('letter')
const selectedStage = ref<string>('overall')
const selectedMinorAward = ref<string>('')

const paperSizes = {
  letter: { name: 'Letter (8.5" × 11")', size: 'letter', margin: '0.5in', width: '8.5in' },
  a4: { name: 'A4 (210mm × 297mm)', size: 'A4', margin: '12mm', width: '210mm' },
  legal: { name: 'Legal (8.5" × 14")', size: 'legal', margin: '0.5in', width: '8.5in' },
  oficio: { name: 'Oficio (8.5" × 13")', size: '8.5in 13in', margin: '0.5in', width: '8.5in' }
} as const

// Get pageant logo URL
const getLogoUrl = computed(() => {
  const logo = props.pageant?.logo
  if (!logo || typeof logo !== 'string') return null
  if (logo.startsWith('http') || logo.startsWith('//') || logo.startsWith('/')) {
    return logo
  }
  return `/storage/${logo}`
})

// Get available minor awards from props
const minorAwardOptions = computed(() => {
  if (!props.minorAwards) return []
  return Object.entries(props.minorAwards)
    .filter(([_, data]) => {
      // Only include awards that have winners
      return (data.winners && data.winners.length > 0) ||
             (data.male_winners && data.male_winners.length > 0) ||
             (data.female_winners && data.female_winners.length > 0)
    })
    .map(([roundName, data]) => ({
      key: roundName,
      label: `Best in ${roundName}`,
      roundName: data.round?.name || roundName
    }))
})

// Get selected minor award data
const selectedMinorAwardData = computed(() => {
  if (!selectedMinorAward.value || !props.minorAwards) return null
  return props.minorAwards[selectedMinorAward.value]
})

// Get minor award data by key (used for "Print All" view)
const getMinorAwardDataByKey = (key: string) => {
  if (!props.minorAwards!) return null
  return props.minorAwards[key!]
}

// Build dynamic stage labels from round types
const stageLabels = computed<Record<string, string>>(() => {
  const labels: Record<string, string> = {
    // Overall Tally - same as Results page (all contestants, all rounds, ranked by final score)
    overall: 'Overall Tally',
  }
  
  // Add labels for each round type from the pageant, but rename 'Final' to 'Final Results'
  props.roundTypes.forEach((roundType) => {
    if (roundType.key.toLowerCase() === 'final') {
      labels[roundType.key] = 'Final Results'
    } else {
      labels[roundType.key] = roundType.label
    }
  })
  
  return labels
})

const getFinalRoundName = (): string | null => {
  if (!props.rounds || props.rounds.length === 0) return null
  const finalRounds = props.rounds.filter(round => round.type?.toLowerCase() === 'final')
  if (finalRounds.length === 0) return null
  return finalRounds[finalRounds.length - 1].name
}

const normalizeStageKey = (value: string): string => {
  return value.toLowerCase().replace(/[\s-]/g, '')
}

const stageKey = computed(() => {
  return normalizeStageKey(selectedStage.value)
})

const isNumericStage = computed(() => /^\d+$/.test(selectedStage.value))

const isStageTypeSelection = computed(() => {
  return stageKey.value !== '' && stageKey.value !== 'overall' && !isNumericStage.value
})

const overallResults = computed<Result[]>(() => {
  return Array.isArray(props.overallTally)
    ? props.overallTally
    : (Array.isArray(props.resultsOverall) ? props.resultsOverall : [])
})

const overallResultsById = computed(() => {
  return new Map(overallResults.value.map(result => [result.id, result]))
})

const rankReferenceResults = computed<Result[] | undefined>(() => {
  if (isStageTypeSelection.value) {
    return overallResults.value
  }

  return undefined
})

const mergeJudgeRanksFromOverall = (list: Result[]): Result[] => {
  if (overallResultsById.value.size === 0) return list

  return list.map(result => {
    const overall = overallResultsById.value.get(result.id)
    if (!overall) return result

    const hasJudgeRanks = result.judgeRanks && Object.keys(result.judgeRanks).length > 0
    if (hasJudgeRanks) return result

    return {
      ...result,
      judgeRanks: overall.judgeRanks
    }
  })
}

const getRoundAverageRankFromRanks = (result: Result, roundName: string): number | null => {
  const ranks = result.judgeRanks?.[roundName]?.ranks
  if (!ranks || ranks.length === 0) return null

  const total = ranks.reduce((sum, rank) => sum + rank, 0)
  return Number((total / ranks.length).toFixed(2))
}

const overallRoundAverageRankMap = computed(() => {
  const map = new Map<string, Map<number, number>>()

  if (!props.rounds || props.rounds.length === 0 || overallResults.value.length === 0) {
    return map
  }

  props.rounds.forEach(round => {
    const roundName = round.name
    const judgeScores = new Map<number, Array<{ contestantId: number; score: number }>>()

    overallResults.value.forEach(result => {
      const details = result.judgeRanks?.[roundName]?.details
      if (!details || details.length === 0) return

      details.forEach(detail => {
        const score = typeof detail.score === 'number' ? detail.score : Number(detail.score)
        if (!Number.isFinite(score)) return

        const entries = judgeScores.get(detail.judge_id) ?? []
        entries.push({ contestantId: result.id, score })
        judgeScores.set(detail.judge_id, entries)
      })
    })

    const roundRanks = new Map<number, { sum: number; count: number }>()

    judgeScores.forEach(entries => {
      const sorted = [...entries].sort((a, b) => b.score - a.score)
      let index = 0
      let betterCount = 0

      while (index < sorted.length) {
        const currentScore = sorted[index].score
        const sameScoreGroup: typeof sorted = []

        while (index < sorted.length && sorted[index].score === currentScore) {
          sameScoreGroup.push(sorted[index])
          index += 1
        }

        const rank = betterCount + 1
        sameScoreGroup.forEach(entry => {
          const current = roundRanks.get(entry.contestantId) ?? { sum: 0, count: 0 }
          roundRanks.set(entry.contestantId, {
            sum: current.sum + rank,
            count: current.count + 1
          })
        })

        betterCount += sameScoreGroup.length
      }
    })

    const roundMap = new Map<number, number>()
    roundRanks.forEach((value, contestantId) => {
      if (value.count > 0) {
        roundMap.set(contestantId, Number((value.sum / value.count).toFixed(2)))
      }
    })

    if (roundMap.size > 0) {
      map.set(roundName, roundMap)
    }
  })

  return map
})

const overallRankRoundNames = computed(() => {
  if (!props.rounds || props.rounds.length === 0) return []
  const finalScoreMode = props.pageant?.final_score_mode || 'fresh'

  if (finalScoreMode === 'fresh') {
    const finalRoundName = getFinalRoundName()
    if (finalRoundName) return [finalRoundName]
  }

  return props.rounds.map(round => round.name)
})

const getOverallRoundAverageRank = (result: Result, roundName: string): number | null => {
  const fromMap = overallRoundAverageRankMap.value.get(roundName)?.get(result.id)
  if (fromMap !== null && fromMap !== undefined) {
    return fromMap
  }

  return getRoundAverageRankFromRanks(result, roundName)
}

const getOverallAverageRank = (result: Result): number | null => {
  if (overallRankRoundNames.value.length === 0) return null

  let sum = 0
  let count = 0

  overallRankRoundNames.value.forEach(roundName => {
    const average = getOverallRoundAverageRank(result, roundName)
    if (average !== null) {
      sum += average
      count += 1
    }
  })

  if (count === 0) return null
  return Number((sum / count).toFixed(2))
}

const getScoreAverageTotal = (result: Result): number => {
  const finalScoreMode = props.pageant?.final_score_mode || 'fresh'
  const scores = result.scores ?? {}

  if (finalScoreMode === 'inherit') {
    const sum = Object.values(scores).reduce((total, score) => {
      const numeric = typeof score === 'number' ? score : Number(score)
      return Number.isFinite(numeric) ? total + numeric : total
    }, 0)

    if (sum > 0 || Object.keys(scores).length > 0) {
      return sum
    }
  }

  const finalRoundName = getFinalRoundName()
  if (finalRoundName && scores[finalRoundName] !== undefined) {
    const finalRoundScore = scores[finalRoundName]
    const numeric = typeof finalRoundScore === 'number' ? finalRoundScore : Number(finalRoundScore)
    return Number.isFinite(numeric) ? numeric : 0
  }

  return result.totalScore ?? result.final_score ?? 0
}

const resultsToShow = computed<Result[]>(() => {
  let baseList: Result[] = []
  let topNProceed: number | null = null
  
  // Handle 'overall' - Same as Overall Tally in Results page
  if (selectedStage.value === 'overall') {
    baseList = Array.isArray(props.overallTally) ? props.overallTally : (Array.isArray(props.resultsOverall) ? props.resultsOverall : [])
    topNProceed = props.pageant?.number_of_winners || null
  } else if (props.roundResults && /^\d+$/.test(selectedStage.value)) {
    // Try to get results from individual round data FIRST (same as Results.vue)
    const roundKey = `round_${selectedStage.value}`
    const roundResult = props.roundResults[roundKey]
    if (roundResult && roundResult.contestants) {
      baseList = Array.isArray(roundResult.contestants) ? roundResult.contestants : []
      topNProceed = roundResult.top_n_proceed || null
    }
  } else if (props.resultsByRoundType && selectedStage.value in props.resultsByRoundType) {
    // Try to get results from the dynamic resultsByRoundType
    const results = props.resultsByRoundType[selectedStage.value]
    baseList = Array.isArray(results) ? results : []
  } else if (selectedStage.value.toLowerCase() === 'final') {
    // Handle 'final' - Use finalTopN (only contestants who competed in final round)
    baseList = Array.isArray(props.finalTopN) ? props.finalTopN : (Array.isArray(props.resultsFinal) ? props.resultsFinal : [])
  } else {
    // Fallback to legacy props for backward compatibility
    switch (selectedStage.value) {
      case 'semi-final':
        baseList = Array.isArray(props.resultsSemiFinal) ? props.resultsSemiFinal : []
        break
      default:
        baseList = Array.isArray(props.overallTally) ? props.overallTally : (Array.isArray(props.resultsOverall) ? props.resultsOverall : [])
    }
  }

  if (isStageTypeSelection.value) {
    baseList = mergeJudgeRanksFromOverall(baseList)
  }

  // Deduplicate by contestant ID (keep first occurrence)
  const seenIds = new Set<number>()
  const deduplicated = baseList.filter(contestant => {
    if (seenIds.has(contestant.id)) {
      return false
    }
    seenIds.add(contestant.id)
    return true
  })

  const rankingMethod = props.pageant?.ranking_method || 'score_average'
  const finalScoreMode = props.pageant?.final_score_mode || 'fresh'

  // Sort contestants: for inherit mode, trust backend order; for fresh mode, compute locally
  const sorted = [...deduplicated].sort((a, b) => {
    if (stageKey.value === 'final' && (rankingMethod === 'rank_sum' || rankingMethod === 'ordinal')) {
      const avgA = getOverallAverageRank(a) ?? Infinity
      const avgB = getOverallAverageRank(b) ?? Infinity
      if (avgA !== avgB) return avgA - avgB
      return (a.number ?? 0) - (b.number ?? 0)
    }

    if (selectedStage.value === 'overall') {
      if (rankingMethod === 'score_average') {
        const scoreA = getScoreAverageTotal(a)
        const scoreB = getScoreAverageTotal(b)
        if (scoreB !== scoreA) return scoreB - scoreA
        return (a.number ?? 0) - (b.number ?? 0)
      }

      // For inherit mode, trust the backend-computed rank completely
      // Backend already computes correct ranking including weighted inherit calculations
      if (finalScoreMode === 'inherit') {
        if ((a.rank ?? 0) > 0 && (b.rank ?? 0) > 0) {
          return (a.rank ?? 0) - (b.rank ?? 0)
        }
        if ((a.rank ?? 0) > 0 && (b.rank ?? 0) === 0) return -1
        if ((a.rank ?? 0) === 0 && (b.rank ?? 0) > 0) return 1
        return (a.number ?? 0) - (b.number ?? 0)
      }
      
      // For fresh mode, sort by hasQualifiedForFinal and then by ranking metric
      const aHasFinal = a.hasQualifiedForFinal === true
      const bHasFinal = b.hasQualifiedForFinal === true
      
      if (aHasFinal && !bHasFinal) return -1
      if (!aHasFinal && bHasFinal) return 1
      
      if (aHasFinal && bHasFinal) {
        if (rankingMethod === 'rank_sum' || rankingMethod === 'ordinal') {
          const rankSumA = a.totalRankSum ?? 999999
          const rankSumB = b.totalRankSum ?? 999999
          return rankSumA - rankSumB
        }

        const scoreA = getScoreAverageTotal(a)
        const scoreB = getScoreAverageTotal(b)
        if (scoreB !== scoreA) return scoreB - scoreA
      }

      return (a.number ?? 0) - (b.number ?? 0)
    }
    
    // For round-specific views, check ranking method
    if (rankingMethod === 'rank_sum' || rankingMethod === 'ordinal') {
      if ((a.rank ?? 0) > 0 && (b.rank ?? 0) > 0) {
        return (a.rank ?? 0) - (b.rank ?? 0)
      }
      if ((a.rank ?? 0) > 0 && (b.rank ?? 0) === 0) return -1
      if ((a.rank ?? 0) === 0 && (b.rank ?? 0) > 0) return 1
      const weightedA = (a as any).weightedRankAvg ?? a.totalRankSum ?? 999999
      const weightedB = (b as any).weightedRankAvg ?? b.totalRankSum ?? 999999
      return weightedA - weightedB
    }

    const scoreA = getScoreAverageTotal(a)
    const scoreB = getScoreAverageTotal(b)
    return scoreB - scoreA
  })

  // Update qualified status based on position
  return sorted.map((contestant, index) => {
    const currentRank = typeof contestant.rank === 'number' && contestant.rank > 0 ? contestant.rank : index + 1
    const qualified = topNProceed === null || currentRank <= topNProceed
    
    return {
      ...contestant,
      rank: currentRank,
      qualified,
      qualification_cutoff: topNProceed
    }
  })
})

const isPairPageant = computed(() => {
  return props.pageant?.contestant_type === 'pairs' || props.pageant?.contestant_type === 'both'
})

// Get the current qualification cutoff for the active stage
const currentQualificationCutoff = computed(() => {
  if (selectedStage.value === 'overall') {
    return props.pageant?.number_of_winners || null
  }
  if (props.roundResults && /^\d+$/.test(selectedStage.value)) {
    const roundKey = `round_${selectedStage.value}`
    const roundResult = props.roundResults[roundKey]
    return roundResult?.top_n_proceed || null
  }
  return null
})

const maleResults = computed(() => {
  if (!isPairPageant.value) return []
  
  const males = resultsToShow.value.filter(r => r.gender === 'male')
  const rankingMethod = props.pageant?.ranking_method || 'score_average'
  const finalScoreMode = props.pageant?.final_score_mode || 'fresh'
  
  // Sort: trust backend rank for inherit mode, compute locally for fresh mode
  const sorted = [...males].sort((a, b) => {
    if (stageKey.value === 'final' && (rankingMethod === 'rank_sum' || rankingMethod === 'ordinal')) {
      const avgA = getOverallAverageRank(a) ?? Infinity
      const avgB = getOverallAverageRank(b) ?? Infinity
      if (avgA !== avgB) return avgA - avgB
      return (a.number ?? 0) - (b.number ?? 0)
    }

    if (selectedStage.value === 'overall') {
      if (rankingMethod === 'score_average') {
        const scoreA = getScoreAverageTotal(a)
        const scoreB = getScoreAverageTotal(b)
        if (scoreB !== scoreA) return scoreB - scoreA
        return (a.number ?? 0) - (b.number ?? 0)
      }

      // For inherit mode, trust backend-computed rank
      if (finalScoreMode === 'inherit') {
        if ((a.rank ?? 0) > 0 && (b.rank ?? 0) > 0) {
          return (a.rank ?? 0) - (b.rank ?? 0)
        }
        if ((a.rank ?? 0) > 0 && (b.rank ?? 0) === 0) return -1
        if ((a.rank ?? 0) === 0 && (b.rank ?? 0) > 0) return 1
        return (a.number ?? 0) - (b.number ?? 0)
      }
      
      // For fresh mode
      const aHasFinal = a.hasQualifiedForFinal === true
      const bHasFinal = b.hasQualifiedForFinal === true
      
      if (aHasFinal && !bHasFinal) return -1
      if (!aHasFinal && bHasFinal) return 1
      
      if (aHasFinal && bHasFinal) {
        if (rankingMethod === 'rank_sum' || rankingMethod === 'ordinal') {
          const rankSumA = a.totalRankSum ?? 999999
          const rankSumB = b.totalRankSum ?? 999999
          return rankSumA - rankSumB
        }

        const scoreA = getScoreAverageTotal(a)
        const scoreB = getScoreAverageTotal(b)
        if (scoreB !== scoreA) return scoreB - scoreA
      }
      return (a.number ?? 0) - (b.number ?? 0)
    }
    
    if (rankingMethod === 'rank_sum' || rankingMethod === 'ordinal') {
      if ((a.rank ?? 0) > 0 && (b.rank ?? 0) > 0) return (a.rank ?? 0) - (b.rank ?? 0)
      if ((a.rank ?? 0) > 0 && (b.rank ?? 0) === 0) return -1
      if ((a.rank ?? 0) === 0 && (b.rank ?? 0) > 0) return 1
      const weightedA = (a as any).weightedRankAvg ?? a.totalRankSum ?? 999999
      const weightedB = (b as any).weightedRankAvg ?? b.totalRankSum ?? 999999
      return weightedA - weightedB
    }

    const scoreA = getScoreAverageTotal(a)
    const scoreB = getScoreAverageTotal(b)
    return scoreB - scoreA
  })
  
  // Recompute rank and qualified status within male group
  const topN = currentQualificationCutoff.value
  return sorted.map((contestant, index) => {
    const genderRank = index + 1
    return {
      ...contestant,
      rank: genderRank,
      qualified: topN === null || genderRank <= topN,
      qualification_cutoff: topN
    }
  })
})

const femaleResults = computed(() => {
  if (!isPairPageant.value) return []
  
  const females = resultsToShow.value.filter(r => r.gender === 'female')
  const rankingMethod = props.pageant?.ranking_method || 'score_average'
  const finalScoreMode = props.pageant?.final_score_mode || 'fresh'
  
  // Sort: trust backend rank for inherit mode, compute locally for fresh mode
  const sorted = [...females].sort((a, b) => {
    if (stageKey.value === 'final' && (rankingMethod === 'rank_sum' || rankingMethod === 'ordinal')) {
      const avgA = getOverallAverageRank(a) ?? Infinity
      const avgB = getOverallAverageRank(b) ?? Infinity
      if (avgA !== avgB) return avgA - avgB
      return (a.number ?? 0) - (b.number ?? 0)
    }

    if (selectedStage.value === 'overall') {
      if (rankingMethod === 'score_average') {
        const scoreA = getScoreAverageTotal(a)
        const scoreB = getScoreAverageTotal(b)
        if (scoreB !== scoreA) return scoreB - scoreA
        return (a.number ?? 0) - (b.number ?? 0)
      }

      // For inherit mode, trust backend-computed rank
      if (finalScoreMode === 'inherit') {
        if ((a.rank ?? 0) > 0 && (b.rank ?? 0) > 0) {
          return (a.rank ?? 0) - (b.rank ?? 0)
        }
        if ((a.rank ?? 0) > 0 && (b.rank ?? 0) === 0) return -1
        if ((a.rank ?? 0) === 0 && (b.rank ?? 0) > 0) return 1
        return (a.number ?? 0) - (b.number ?? 0)
      }
      
      // For fresh mode
      const aHasFinal = a.hasQualifiedForFinal === true
      const bHasFinal = b.hasQualifiedForFinal === true
      
      if (aHasFinal && !bHasFinal) return -1
      if (!aHasFinal && bHasFinal) return 1
      
      if (aHasFinal && bHasFinal) {
        if (rankingMethod === 'rank_sum' || rankingMethod === 'ordinal') {
          const rankSumA = a.totalRankSum ?? 999999
          const rankSumB = b.totalRankSum ?? 999999
          return rankSumA - rankSumB
        }

        const scoreA = getScoreAverageTotal(a)
        const scoreB = getScoreAverageTotal(b)
        if (scoreB !== scoreA) return scoreB - scoreA
      }
      return (a.number ?? 0) - (b.number ?? 0)
    }
    
    if (rankingMethod === 'rank_sum' || rankingMethod === 'ordinal') {
      if ((a.rank ?? 0) > 0 && (b.rank ?? 0) > 0) return (a.rank ?? 0) - (b.rank ?? 0)
      if ((a.rank ?? 0) > 0 && (b.rank ?? 0) === 0) return -1
      if ((a.rank ?? 0) === 0 && (b.rank ?? 0) > 0) return 1
      const weightedA = (a as any).weightedRankAvg ?? a.totalRankSum ?? 999999
      const weightedB = (b as any).weightedRankAvg ?? b.totalRankSum ?? 999999
      return weightedA - weightedB
    }

    const scoreA = getScoreAverageTotal(a)
    const scoreB = getScoreAverageTotal(b)
    return scoreB - scoreA
  })
  
  // Recompute rank and qualified status within female group
  const topN = currentQualificationCutoff.value
  return sorted.map((contestant, index) => {
    const genderRank = index + 1
    return {
      ...contestant,
      rank: genderRank,
      qualified: topN === null || genderRank <= topN,
      qualification_cutoff: topN
    }
  })
})

const reportTitle = computed(() => stageLabels.value[selectedStage.value])

const isLastFinalRound = computed(() => {
  return selectedStage.value.toLowerCase() === 'final'
})

// Tabulator name for signatures
const tabulatorName = computed(() => {
  return props.tabulator?.name ? capitalizeName(props.tabulator.name) : 'Head Tabulator'
})

// Check if printing is allowed (all rounds must be locked)
const canPrint = computed(() => {
  return props.allRoundsLocked === true
})

const getPreviewWidth = () => {
  return paperSizes[selectedPaperSize.value].width
}

const capitalizeName = (name: string): string => {
  if (!name) return ''
  return name
    .split(' ')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
    .join(' ')
}

const printResults = () => {
  // Get the printable content
  const printContent = document.querySelector('.print-only');
  
  if (!printContent) {
    console.error('Print content not found');
    return;
  }
  
  // Get selected paper size configuration
  const paperConfig = paperSizes[selectedPaperSize.value];
  const pageantInfo = props.pageant;
  const judgeCount = props.judges?.length ?? 0;
  const stageLabel = reportTitle.value;
  const paperName = paperConfig.name;
  const generatedAt = new Date().toLocaleString();
  
  // Get all stylesheets from the current page
  const stylesheets = Array.from(document.styleSheets)
    .map(sheet => {
      try {
        // For external stylesheets, get the href
        if (sheet.href) {
          return `<link rel="stylesheet" href="${sheet.href}">`;
        }
        // For inline styles, get the CSS rules
        else if (sheet.cssRules) {
          const rules = Array.from(sheet.cssRules)
            .map(rule => rule.cssText)
            .join('\n');
          return `<style>${rules}</style>`;
        }
      } catch (e) {
        // Handle CORS issues with external stylesheets
        console.warn('Could not access stylesheet:', e);
        return '';
      }
      return '';
    })
    .filter(Boolean)
    .join('\n');
  
  // Create a new window for printing
  const printWindow = window.open('', '_blank');
  
  if (!printWindow) {
    console.error('Could not open print window');
    return;
  }
  
  // Copy the content to the new window with all styles
  printWindow.document.write(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>Print Results - ${paperConfig.name}</title>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      ${stylesheets}
      <style>
        body {
          margin: 0;
          padding: 0;
          font-family: system-ui, -apple-system, sans-serif;
          background-color: #f3f4f6;
        }

        .print-shell {
          min-height: 100vh;
          box-sizing: border-box;
          padding: 24px;
        }

        .print-header {
          display: flex;
          justify-content: space-between;
          align-items: flex-start;
          gap: 24px;
          padding-bottom: 12px;
          margin-bottom: 16px;
          border-bottom: 1px solid #e5e7eb;
        }

        .print-title {
          margin: 0;
          font-size: 20px;
          font-weight: 600;
          letter-spacing: -0.02em;
          color: #111827;
        }

        .print-subtitle {
          margin: 4px 0 0 0;
          font-size: 12px;
          color: #4b5563;
        }

        .print-meta {
          font-size: 11px;
          color: #4b5563;
        }

        .print-meta-row {
          display: flex;
          justify-content: flex-end;
          gap: 4px;
          margin-bottom: 2px;
        }

        .print-meta-label {
          font-weight: 500;
          color: #374151;
        }

        .print-main {
          margin-top: 12px;
        }

        .print-content-card {
          background-color: #ffffff;
          border-radius: 12px;
          padding: 16px 20px;
          box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
          border: 1px solid #e5e7eb;
        }

        @media print {
          @page {
            size: ${paperConfig.size};
            margin: ${paperConfig.margin};
          }
          
          body {
            margin: 0;
            padding: 0;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            background: white;
          }
          
          /* Ensure only printable content shows */
          .screen-only {
            display: none !important;
          }
          
          .print-only {
            display: block !important;
            width: 100%;
            max-width: none;
          }
          
          /* Optimize text sizes for selected paper */
          h1 { font-size: 18px; margin: 0 0 8px 0; }
          h2 { font-size: 16px; margin: 0 0 6px 0; }
          h3 { font-size: 14px; margin: 0 0 4px 0; }
          h4, h5, h6 { font-size: 12px; margin: 0 0 4px 0; }
          
          p, div, span {
            font-size: 11px;
            line-height: 1.3;
          }
          
          /* Table optimizations */
          table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            page-break-inside: auto;
          }
          
          th, td {
            padding: 4px 6px;
            border: 1px solid #ccc;
            font-size: 10px;
            line-height: 1.2;
          }
          
          th {
            background-color: #f5f5f5 !important;
            font-weight: bold;
          }
          
          /* Winner row highlighting for print */
          tr.winner-row-1 td {
            background-color: #fef3c7 !important;
            border-left: 4px solid #f59e0b !important;
            font-weight: 700 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
          }
          
          tr.winner-row-2 td {
            background-color: #e0e7ff !important;
            border-left: 4px solid #6366f1 !important;
            font-weight: 700 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
          }
          
          tr.winner-row-3 td {
            background-color: #fed7aa !important;
            border-left: 4px solid #ea580c !important;
            font-weight: 700 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
          }
          
          /* Ensure images print */
          img {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
          }
          
          /* Page break controls */
          .page-break-before {
            page-break-before: always;
          }
          
          .page-break-after {
            page-break-after: always;
          }
          
          .no-page-break {
            page-break-inside: avoid;
          }
          
          /* Reduce excessive spacing */
          .space-y-4 > * + * {
            margin-top: 8px !important;
          }
          
          .space-y-6 > * + * {
            margin-top: 12px !important;
          }
          
          .mb-4, .my-4 {
            margin-bottom: 8px !important;
          }
          
          .mb-6, .my-6 {
            margin-bottom: 12px !important;
          }
          
          .p-4 {
            padding: 8px !important;
          }
          
          .p-6 {
            padding: 12px !important;
            padding-bottom: 0px !important;
          }
          
          /* Image sizing - ensure logo displays */
          img {
            max-width: 80px !important;
            max-height: 80px !important;
            object-fit: contain !important;
            display: block !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
          }
          
          /* Remove shadows and rounded corners for print */
          .shadow, .shadow-lg, .shadow-xl {
            box-shadow: none !important;
          }
          
          .rounded, .rounded-lg, .rounded-xl, .rounded-3xl {
            border-radius: 0 !important;
          }

          .print-shell {
            padding: 0;
          }

          .print-content-card {
            box-shadow: none;
            border-radius: 0;
            border: none;
            padding: 0;
          }
        }
      </style>
    </head>
    <body>
      <div class="print-shell">
        <header class="print-header">
          <div>
            <h1 class="print-title">${pageantInfo?.name ?? 'Final Results Report'}</h1>
            <p class="print-subtitle">
              ${stageLabel ? stageLabel : ''}
            </p>
          </div>
          <div class="print-meta">
            ${pageantInfo?.date ? `<div class="print-meta-row"><span class="print-meta-label">Date:</span><span>${pageantInfo.date}</span></div>` : ''}
            ${pageantInfo?.venue ? `<div class="print-meta-row"><span class="print-meta-label">Venue:</span><span>${pageantInfo.venue}</span></div>` : ''}
            ${pageantInfo?.location ? `<div class="print-meta-row"><span class="print-meta-label">Location:</span><span>${pageantInfo.location}</span></div>` : ''}
            <div class="print-meta-row"><span class="print-meta-label">Stage:</span><span>${stageLabel}</span></div>
            <div class="print-meta-row"><span class="print-meta-label">Paper:</span><span>${paperName}</span></div>
            <div class="print-meta-row"><span class="print-meta-label">Judges:</span><span>${judgeCount}</span></div>
            <div class="print-meta-row"><span class="print-meta-label">Generated:</span><span>${generatedAt}</span></div>
          </div>
        </header>
        <main class="print-main">
          <div class="print-content-card">
            ${printContent.innerHTML}
          </div>
        </main>
      </div>
    </body>
    </html>
  `);
  
  printWindow.document.close();
  
  // Wait for content to load, then print
  printWindow.onload = () => {
    printWindow.focus();
    printWindow.print();
    printWindow.close();
  };
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

@media print {
  .screen-only {
    display: none !important;
  }
  
  .print-only {
    display: block !important;
  }
  
  .print-container {
    margin: 0;
    padding: 0;
    background: white !important;
  }
}

@media screen {
  .print-only {
    display: none !important;
  }
}

/* Winner highlighting */
:deep(.winner-row-1) {
  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%) !important;
  border-left: 4px solid #f59e0b !important;
  font-weight: 600 !important;
}

:deep(.winner-row-2) {
  background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%) !important;
  border-left: 4px solid #6366f1 !important;
  font-weight: 600 !important;
}

:deep(.winner-row-3) {
  background: linear-gradient(135deg, #fed7aa 0%, #fdba74 100%) !important;
  border-left: 4px solid #ea580c !important;
  font-weight: 600 !important;
}

:deep(.winner-row-1 td),
:deep(.winner-row-2 td),
:deep(.winner-row-3 td) {
  padding-top: 12px !important;
  padding-bottom: 12px !important;
}
</style>

<style>
/* Global print styles to hide layout elements */
@media print {
  /* Hide sidebar and navigation */
  .sidebar,
  .side-nav,
  nav,
  .navigation,
  .nav-container,
  [class*="nav"],
  [class*="sidebar"] {
    display: none !important;
  }
  
  /* Hide header elements */
  header,
  .header,
  [class*="header"] {
    display: none !important;
  }
  
  /* Hide buttons and interactive elements */
  button,
  .btn,
  [class*="button"] {
    display: none !important;
  }
  
  /* Ensure full width for print content */
  body,
  #app,
  .min-h-screen,
  .bg-gray-50 {
    margin: 0 !important;
    padding: 0 !important;
    background: white !important;
  }
  
  /* Override layout padding */
  .p-6 {
    padding: 0 !important;
  }
  
  /* Show only print content */
  .print-only {
    display: block !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
  }
  
  /* Winner highlighting for print */
  .winner-row-1 {
    background-color: #fef3c7 !important;
    border-left: 4px solid #f59e0b !important;
    font-weight: 700 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  
  .winner-row-2 {
    background-color: #e0e7ff !important;
    border-left: 4px solid #6366f1 !important;
    font-weight: 700 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  
  .winner-row-3 {
    background-color: #fed7aa !important;
    border-left: 4px solid #ea580c !important;
    font-weight: 700 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  
  .winner-row-1 td,
  .winner-row-2 td,
  .winner-row-3 td {
    padding-top: 8px !important;
    padding-bottom: 8px !important;
  }
}
</style>