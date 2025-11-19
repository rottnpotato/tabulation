<template>
    <Head :title="pageant.name || 'Pageant Details'" />
    <div class="space-y-4 sm:space-y-6">
      <!-- Back button and page title -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <Link 
          :href="previousPage"
          class="inline-flex items-center px-2.5 py-1.5 sm:px-3 sm:py-2 text-xs sm:text-sm text-gray-700 z-50 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 hover:text-teal-600 active:bg-gray-100 transition-colors duration-150 ease-in-out cursor-pointer btn-transition w-max"
        >
          <ChevronLeft class="h-3 w-3 sm:h-4 sm:w-4 mr-1" />
          Back to {{ fromPage === 'index' ? 'All' : fromPage === 'previous' ? 'Previous' : 'Previous' }} Pageants
        </Link>
        <div class="flex flex-wrap gap-2 sm:space-x-3">
          <button
            @click="exportPageantData"
            class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-medium text-gray-700 z-50 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 transition-colors duration-150 ease-in-out cursor-pointer btn-transition"
          >
            <FileText class="h-3 w-3 sm:h-4 sm:w-4 mr-1 sm:mr-2" />
            Export Data
          </button>
          <button
            @click="editPageant"
            class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-medium z-50 text-white bg-teal-600 border border-teal-700 rounded-md shadow-sm hover:bg-teal-700 transition-colors duration-150 ease-in-out cursor-pointer btn-transition"
          >
            <Edit class="h-3 w-3 sm:h-4 sm:w-4 mr-1 sm:mr-2" />
            Edit Pageant
          </button>
        </div>
      </div>
  
      <!-- Hero section with pageant cover -->
      <div class="relative rounded-lg overflow-hidden">
        <div class="h-48 sm:h-64 md:h-80 w-full">
          <img
            :src="pageant.coverImage || '/images/placeholders/pageant-cover.jpg'"
            :alt="pageant.name"
            class="w-full h-full object-cover"
          />
          <div
            class="absolute inset-0 bg-gradient-to-r from-teal-900/70 via-teal-800/50 to-transparent"
          ></div>
        </div>
        <div class="absolute bottom-0 left-0 right-0 p-4 sm:p-6 md:p-8">
          <span
            class="inline-flex items-center px-2 py-0.5 sm:px-2.5 sm:py-0.5 rounded-full text-2xs sm:text-xs font-medium bg-teal-100 text-teal-800 mb-2 sm:mb-3"
          >
            {{ pageant.status }}
          </span>
          <h1 class="text-xl sm:text-3xl md:text-4xl font-bold text-white mb-1 sm:mb-2">
            {{ pageant.name }}
          </h1>
          <p class="text-sm sm:text-base text-teal-50 md:max-w-2xl">{{ pageant.description || 'No description provided. The organizer will add details soon.' }}</p>
  
          <div class="mt-3 sm:mt-4 flex flex-wrap gap-2 sm:gap-4">
            <div class="flex items-center text-white/90 text-xs sm:text-sm">
              <Calendar class="h-4 w-4 sm:h-5 sm:w-5 mr-1 sm:mr-2 text-teal-300" />
              {{ FormatDate(pageant.start_date) || 'Date not set' }}
            </div>
            <div class="flex items-center text-white/90 text-xs sm:text-sm">
              <MapPin class="h-4 w-4 sm:h-5 sm:w-5 mr-1 sm:mr-2 text-teal-300" />
              {{ pageant.venue || pageant.location || 'Venue not specified' }}
            </div>
            <div class="flex items-center text-white/90 text-xs sm:text-sm">
              <Users class="h-4 w-4 sm:h-5 sm:w-5 mr-1 sm:mr-2 text-teal-300" />
              {{ pageant.contestants_count ? `${pageant.contestants_count} Contestants` : 'No contestants added yet' }}
            </div>
          </div>
        </div>
      </div>
  
      <!-- Pageant Progress Section -->
      <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="border-b border-gray-200">
          <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
              Pageant Progress
            </h2>
  
            <div class="mb-6">
              <div
                class="flex items-center justify-between text-sm text-gray-500 mb-2"
              >
                <span>Overall Completion</span>
                <span class="font-medium">{{ pageant.progress || 0 }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div
                  class="bg-teal-600 h-2.5 rounded-full"
                  :style="{ width: `${pageant.progress || 0}%` }"
                ></div>
              </div>
            </div>
  
            <!-- Timeline -->
            <div v-if="pageant.phases && pageant.phases.length > 0" class="relative">
              <div
                class="absolute top-0 bottom-0 left-[19px] border-l-2 border-dashed border-gray-200"
              ></div>
  
              <div
                v-for="(phase, index) in pageant.phases"
                :key="index"
                class="relative pl-12 pb-8"
              >
                <div
                  :class="[
                    'absolute left-0 top-0 h-10 w-10 rounded-full flex items-center justify-center',
                    phase.completed
                      ? 'bg-teal-100 text-teal-600'
                      : phase.current
                        ? 'bg-teal-600 text-white'
                        : 'bg-gray-100 text-gray-400',
                  ]"
                >
                  <component :is="phase.icon" class="h-5 w-5" />
                </div>
  
                <div>
                  <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    {{ phase.name }}
                    <span
                      v-if="phase.current"
                      class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-100 text-teal-800"
                    >
                      Current
                    </span>
                    <CheckCircle
                      v-if="phase.completed"
                      class="ml-2 h-5 w-5 text-teal-600"
                    />
                  </h3>
                  <p class="text-sm text-gray-500 mt-1">
                    {{ phase.description }}
                  </p>
  
                  <div
                    v-if="phase.milestones && phase.milestones.length > 0"
                    class="mt-3 space-y-2"
                  >
                    <div
                      v-for="(milestone, mIndex) in phase.milestones"
                      :key="mIndex"
                      class="flex items-start space-x-3"
                    >
                      <div
                        :class="[
                          'h-5 w-5 rounded-full flex-shrink-0 mt-0.5',
                          milestone.completed
                            ? 'bg-teal-600 text-white'
                            : 'bg-gray-200 text-gray-400',
                        ]"
                        class="flex items-center justify-center"
                      >
                        <Check v-if="milestone.completed" class="h-3 w-3" />
                      </div>
                      <div>
                        <p class="text-sm font-medium text-gray-700">
                          {{ milestone.name }}
                        </p>
                        <p v-if="milestone.date" class="text-xs text-gray-500">
                          {{ FormatDate(milestone.date) }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div v-else class="bg-gray-50 rounded-lg p-8 text-center">
              <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-gray-100 mb-4">
                <Calendar class="h-8 w-8 text-gray-400" />
              </div>
              <h3 class="text-lg font-medium text-gray-700 mb-1">No Events Scheduled</h3>
              <p class="text-gray-500 max-w-md mx-auto">
                No events have been added to this pageant yet. Events will appear here once they are scheduled.
              </p>
            </div>
          </div>
        </div>
      </div>
  
      <!-- Contestants Section -->
      <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="p-6">
          <div class="flex items-center justify-between mb-2">
            <h2 class="text-xl font-semibold text-gray-900">Contestants</h2>
            <Link
              href="#"
              class="inline-flex items-center px-3 py-2 text-sm font-medium text-teal-600 hover:text-teal-700 hover:underline cursor-pointer"
              @click.prevent="viewAllContestants"
            >
              View All Contestants
              <ChevronRight class="h-4 w-4 ml-1" />
            </Link>
          </div>
          
          <div class="flex items-center mb-6">
            <span class="text-sm text-gray-500">
              <span class="font-medium">Scoring System:</span> {{ pageant.scoringSystem?.description || 'Scoring system not configured yet' }}
            </span>
            <div class="ml-auto" v-if="pageant.scoringSystem?.type">
              <span 
                class="inline-flex items-center px-2 py-1 text-xs rounded-md bg-teal-100 text-teal-800 font-medium"
              >
                {{ 
                  pageant.scoringSystem.type === 'percentage' ? '0-100%' : 
                  pageant.scoringSystem.type === '1-10' ? 'Scale: 1-10' :
                  pageant.scoringSystem.type === '1-5' ? 'Scale: 1-5' :
                  'Points: 0-' + pageant.scoringSystem.maxScore
                }}
              </span>
            </div>
          </div>
  
          <div v-if="contestants && contestants.length > 0"
            class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6"
          >
            <div
              v-for="contestant in contestants"
              :key="contestant.id"
              class="bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-xl overflow-hidden hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 group"
            >
              <div class="relative">
                <div class="w-full h-56 overflow-hidden">
                  <img
                    :src="contestant.photo || '/images/placeholders/contestant-placeholder.jpg'"
                    :alt="contestant.name"
                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                  />
                </div>
                <div class="absolute top-3 right-3">
                  <span
                    class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-teal-600 text-white text-sm font-bold shadow-md"
                  >
                    #{{ contestant.number || '?' }}
                  </span>
                </div>
                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-4 text-white">
                  <h3 class="text-lg font-bold truncate">
                    {{ contestant.name }}
                  </h3>
                  <p class="text-sm text-teal-100">
                    {{ contestant.age || '?' }} years • {{ contestant.origin || 'Unknown' }}
                  </p>
                </div>
              </div>
              
              <div class="p-4">
                <div class="mb-3">
                  <div class="flex items-center justify-between mb-1">
                    <span class="text-sm font-medium text-gray-700">Current Score</span>
                    <div class="flex items-center">
                      <Star 
                        v-if="contestant.score !== null && contestant.score !== undefined"
                        :class="normalizeScoreToPercentage(contestant.score) >= 90 ? 'text-amber-500' : 'text-gray-400'" 
                        class="h-4 w-4 mr-1" 
                        :fill="normalizeScoreToPercentage(contestant.score) >= 90 ? 'currentColor' : 'none'"
                      />
                      <span 
                        v-if="contestant.score !== null && contestant.score !== undefined"
                        :class="{
                          'font-bold text-teal-600': normalizeScoreToPercentage(contestant.score) >= 90,
                          'font-medium text-gray-700': normalizeScoreToPercentage(contestant.score) < 90
                        }"
                      >
                        {{ formatScore(contestant.score) }}
                      </span>
                      <span 
                        v-else
                        class="text-sm italic text-gray-500"
                      >
                        Pending
                      </span>
                    </div>
                  </div>
                  <div 
                    v-if="contestant.score !== null && contestant.score !== undefined" 
                    class="w-full h-2 bg-gray-200 rounded-full overflow-hidden"
                  >
                    <div
                      class="h-full rounded-full transition-all duration-500"
                      :class="getScoreColorClass(contestant.score)"
                      :style="{ width: `${normalizeScoreToPercentage(contestant.score)}%` }"
                    ></div>
                  </div>
                  <div 
                    v-else 
                    class="w-full h-2 bg-gray-200 rounded-full overflow-hidden flex items-center justify-center"
                  >
                    <div class="text-[8px] text-gray-400 uppercase tracking-wider">Not scored yet</div>
                  </div>
                </div>
                
                <div class="mt-3 flex justify-between items-center">
                  <span 
                    v-if="contestant.score !== null && contestant.score !== undefined"
                    :class="{
                      'bg-teal-100 text-teal-800': normalizeScoreToPercentage(contestant.score) >= 90,
                      'bg-amber-100 text-amber-800': normalizeScoreToPercentage(contestant.score) >= 80 && normalizeScoreToPercentage(contestant.score) < 90,
                      'bg-blue-100 text-blue-800': normalizeScoreToPercentage(contestant.score) >= 70 && normalizeScoreToPercentage(contestant.score) < 80,
                      'bg-gray-100 text-gray-800': normalizeScoreToPercentage(contestant.score) < 70
                    }"
                    class="inline-flex items-center px-2 py-1 rounded text-xs font-medium"
                  >
                    {{ getPerformanceLabel(contestant.score) }}
                  </span>
                  <span 
                    v-else
                    class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-600"
                  >
                    <Clock class="h-3 w-3 mr-1" />
                    Awaiting scores
                  </span>
                  <button class="inline-flex items-center text-xs font-medium text-teal-600 hover:text-teal-800 transition-colors">
                    View Details
                    <ChevronRight class="h-3 w-3 ml-1" />
                  </button>
                </div>
              </div>
            </div>
          </div>
          
          <div v-else class="bg-gray-50 rounded-xl p-10 text-center border border-gray-200">
            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-teal-100 mb-4">
              <Users class="h-8 w-8 text-teal-600" />
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-1">No Contestants Added Yet</h3>
            <p class="text-gray-500 max-w-md mx-auto mb-6">
              The organizer will add contestants to this pageant during the setup phase.
            </p>
            <p class="text-sm text-gray-500">
              Check back soon or notify the organizer to begin adding contestants.
            </p>
          </div>
        </div>
      </div>
  
      <!-- Scoring Stats Section -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
          <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
              Top Categories
            </h3>
            <div v-if="pageant.topCategories && pageant.topCategories.length > 0" class="space-y-4">
              <div v-for="category in pageant.topCategories" :key="category.name">
                <div class="flex items-center justify-between text-sm mb-1">
                  <span class="font-medium text-gray-700">{{
                    category.name
                  }}</span>
                  <span class="text-gray-500">{{
                    category.avgScore.toFixed(1)
                  }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div
                    class="bg-teal-600 h-2 rounded-full"
                    :style="{ width: `${(category.avgScore / 100) * 100}%` }"
                  ></div>
                </div>
              </div>
            </div>
            <div v-else class="flex flex-col items-center justify-center text-center py-8">
              <Star class="h-8 w-8 text-gray-300 mb-2" />
              <p class="text-gray-500">No categories have been set up for this pageant yet.</p>
            </div>
          </div>
        </div>
  
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
          <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Judges</h3>
            <div v-if="pageant.judges && pageant.judges.length > 0" class="space-y-3">
              <div
                v-for="judge in pageant.judges"
                :key="judge.id"
                class="flex items-center"
              >
                <div
                  class="h-10 w-10 rounded-full bg-gray-200 flex-shrink-0 overflow-hidden"
                >
                  <img
                    v-if="judge.photo"
                    :src="judge.photo"
                    :alt="judge.name"
                    class="h-full w-full object-cover"
                  />
                  <User2 v-else class="h-6 w-6 m-2 text-gray-500" />
                </div>
                <div class="ml-3">
                  <p class="text-sm font-medium text-gray-900">
                    {{ judge.name }}
                  </p>
                  <p class="text-xs text-gray-500">{{ judge.role }}</p>
                </div>
                <div class="ml-auto flex items-center">
                  <span
                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                    :class="
                      judge.active
                        ? 'bg-green-100 text-green-800'
                        : 'bg-gray-100 text-gray-800'
                    "
                  >
                    {{ judge.active ? 'Active' : 'Standby' }}
                  </span>
                </div>
              </div>
            </div>
            <div v-else class="flex flex-col items-center justify-center text-center py-8">
              <User2 class="h-8 w-8 text-gray-300 mb-2" />
              <p class="text-gray-500">No judges have been assigned to this pageant yet.</p>
            </div>
          </div>
        </div>
  
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
          <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
              Activity Log
            </h3>
            <div v-if="pageant.recentActivities && pageant.recentActivities.length > 0" class="space-y-4">
              <div
                v-for="(activity, index) in pageant.recentActivities"
                :key="index"
                class="flex items-start"
              >
                <div
                  class="h-8 w-8 rounded-full bg-teal-100 flex items-center justify-center flex-shrink-0 mt-0.5"
                >
                  <component :is="activity.icon" class="h-4 w-4 text-teal-600" />
                </div>
                <div class="ml-3">
                  <p
                    class="text-sm text-gray-900"
                    v-html="activity.description"
                  ></p>
                  <p class="text-xs text-gray-500">{{ activity.time }}</p>
                </div>
              </div>
            </div>
            <div v-else class="flex flex-col items-center justify-center text-center py-8">
              <ClipboardList class="h-8 w-8 text-gray-300 mb-2" />
              <p class="text-gray-500">No recent activity has been recorded for this pageant.</p>
            </div>
          </div>
        </div>
      </div>
  
      <!-- Action Buttons -->
      <div class="flex justify-end space-x-4 mt-8">
        <button
          class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 transition-colors duration-150 ease-in-out cursor-pointer"
        >
          <PauseCircle class="h-5 w-5 mr-2 text-amber-500" />
          Pause Pageant
        </button>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
  import { Head, Link, router, usePage } from '@inertiajs/vue3';
  import { useNotification } from '@/Composables/useNotification';
  import {
    Calendar,
    MapPin,
    File,
    CheckCircle,
    Check,
    Star,
    ChevronLeft,
    ChevronRight,
    Users,
    FileText,
    Edit,
    Clock,
    Timer,
    Eye,
    PauseCircle,
    ClipboardList,
  } from 'lucide-vue-next';
  import AdminLayout from '@/Layouts/AdminLayout.vue';
  
  // Page setup
  defineOptions({
    layout: AdminLayout,
  });
  
  // Define props with the pageant data from the server
  const props = defineProps({
    pageant: {
      type: Object,
      required: true
    },
    contestants: {
      type: Array,
      default: () => []
    },
  });
  
  // Determine the referring page to control back button behavior
  const fromPage = computed(() => {
    const referrer = document.referrer;
    if (referrer.includes('/admin/pageants/ongoing')) return 'index';
    if (referrer.includes('/admin/pageants/previous')) return 'previous';
    if (referrer.includes('/admin/pageants')) return 'all';
    return 'index'; // Default
  });
  
  // Dynamic back button URL
  const previousPage = computed(() => {
    if (fromPage.value === 'index') return route('admin.pageants.index');
    if (fromPage.value === 'previous') return route('admin.pageants.previous');
    return route('admin.pageants.index');
  });
  
  // Helper function to format date in a more readable format
  const FormatDate = (dateString) => {
    if (!dateString) return null;
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
  };
  
  // Function to format expiry date with time
  const formatExpiryDate = (dateString) => {
    if (!dateString) return null;
    const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleDateString(undefined, options);
  };
  
  // Add notification service
  const notify = useNotification();
  
  // Update the exportPageantData function
  const exportPageantData = () => {
    notify.info('Preparing pageant data for export...', { timeout: 2000 });
    
    // Simulate export delay for demo
    setTimeout(() => {
      notify.success('Pageant data has been exported successfully!');
    }, 2000);
    
    // In a real implementation, this would trigger a download
    console.log('Exporting pageant data for:', props.pageant.name);
  };
  
  // Update the editPageant function
  const editPageant = () => {
    // Check if pageant is in an editable state
    if (['Draft', 'Setup'].includes(props.pageant.status)) {
      router.visit(`/admin/pageants/edit/${props.pageant.id}`);
    } else {
      notify.warning(`Pageant cannot be edited in '${props.pageant.status}' status. Only Draft and Setup pageants can be edited directly.`);
    }
  };
  
  // View all contestants
  const viewAllContestants = () => {
    // Use route helper when we have a contestants route
    // router.visit(route('admin.pageants.contestants', { id: props.pageant.id }));
    alert('Navigate to all contestants page for this pageant');
  };
  
  // Normalize score to percentage for visualization
  const normalizeScoreToPercentage = (score) => {
    if (score === null || score === undefined) return 0;
    
    const scoringSystem = props.pageant?.scoringSystem?.type || 'percentage';
    
    if (scoringSystem === 'percentage') return score;
    if (scoringSystem === '1-10') return (score / 10) * 100;
    if (scoringSystem === '1-5') return (score / 5) * 100;
    
    // Custom point system
    const maxScore = props.pageant?.scoringSystem?.maxScore || 100;
    return (score / maxScore) * 100;
  };
  
  // Reactive pageant data combined from props and local enhancements
  const pageant = ref({
    ...props.pageant,
  });
  
  // Contestants from props
  const contestants = computed(() => {
    return props.contestants || [];
  });
  
  // Helper to get color class based on score
  const getScoreColorClass = (score) => {
    const percentage = normalizeScoreToPercentage(score);
    
    if (percentage >= 90) return 'bg-teal-600';
    if (percentage >= 80) return 'bg-teal-500';
    if (percentage >= 70) return 'bg-teal-400';
    if (percentage >= 60) return 'bg-amber-500';
    return 'bg-red-500';
  };
  
  // Helper to format score display based on scoring system
  const formatScore = (score) => {
    if (score === null || score === undefined) return 'Pending';
    
    const scoringSystem = props.pageant?.scoringSystem?.type || 'percentage';
    
    switch (scoringSystem) {
      case 'percentage':
        return score.toFixed(1) + '%';
      case '1-10':
        return score.toFixed(1) + '/10';
      case '1-5':
        return score.toFixed(1) + '/5';
      case 'points':
        return score.toFixed(0) + ' pts';
      default:
        return score.toFixed(1);
    }
  };
  
  // Helper to get performance label based on score
  const getPerformanceLabel = (score) => {
    if (score === null || score === undefined) return 'Not Rated';
    
    const percentage = normalizeScoreToPercentage(score);
    
    if (percentage >= 90) return 'Outstanding';
    if (percentage >= 80) return 'Excellent';
    if (percentage >= 70) return 'Very Good';
    if (percentage >= 60) return 'Good';
    return 'Average';
  };
  
  // Real-time event update listener
  let pageantEventListener = null;
  
  onMounted(() => {
    // Listen for pageant event updates
    pageantEventListener = window.addEventListener('pageant-event-updated', (e) => {
      // Only refresh data if the update is for the current pageant
      if (e.detail.pageant_id === pageant.id) {
        // Show notification about real-time update
        notify.info('Pageant data updated in real-time', {
          title: 'Live Update',
          timeout: 3000
        });
        
        // Refresh the pageant data from the server
        router.reload({ only: ['pageant', 'contestants'] });
      }
    });
  });
  
  onBeforeUnmount(() => {
    // Clean up event listener
    if (pageantEventListener) {
      window.removeEventListener('pageant-event-updated', pageantEventListener);
    }
  });
  </script>
  
  <style scoped>
  /* Fixed aspect ratio styles for proper image display */
  .w-full.h-48 {
    position: relative;
    height: 12rem; /* 48px * 4 = 192px = 12rem */
  }
  
  .w-full.h-48 img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  </style>
  