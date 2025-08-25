<template>
  <Head title="Admin Dashboard" />
  <div class="space-y-4 sm:space-y-6">
    <!-- Page Header and Quick Links -->
    <div class="bg-gradient-to-r from-teal-500 to-teal-600 rounded-xl shadow-md overflow-hidden">
      <div class="p-4 sm:p-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
          <div class="text-white">
            <h1 class="text-2xl sm:text-3xl font-bold">Admin Dashboard</h1>
            <p class="mt-1 text-sm sm:text-base opacity-90">Welcome back, {{ user?.name }}</p>
          </div>
          <div class="flex flex-wrap gap-2 sm:space-x-3">
            <button class="bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-medium text-white hover:bg-white/30 flex items-center shadow-sm transition-all">
              <CalendarDays class="h-3 w-3 sm:h-4 sm:w-4 mr-1 sm:mr-2 text-white" />
              <span>{{ formattedDate }}</span>
            </button>
            <button 
              @click="refreshDashboard" 
              class="bg-white text-teal-700 rounded-lg px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-medium hover:bg-teal-50 flex items-center shadow-sm transition-all"
              :disabled="isLoading"
            >
              <RefreshCw :class="['h-3 w-3 sm:h-4 sm:w-4 mr-1 sm:mr-2 text-teal-600', {'animate-spin': isLoading}]" />
              <span>{{ isLoading ? 'Refreshing...' : 'Refresh' }}</span>
            </button>
          </div>
        </div>
      </div>
      
      <!-- Quick Links Bar -->
      <div class="bg-teal-700/30 backdrop-blur-sm px-3 sm:px-6 py-3 sm:py-4">
        <div class="flex flex-wrap gap-2 sm:gap-3">
          <Link :href="route('admin.pageants.create')" class="flex items-center px-2 py-1.5 sm:px-4 sm:py-2 rounded-lg bg-white text-teal-700 hover:bg-teal-50 transition-all shadow-sm text-xs sm:text-sm">
            <Plus class="h-3 w-3 sm:h-4 sm:w-4 mr-1 sm:mr-2 text-teal-600" />
            <span class="font-medium">Create Pageant</span>
          </Link>
          
          <Link 
            v-if="pageantCounts.pending_approval > 0"
            :href="route('admin.pageants.pending-approvals')" 
            class="flex items-center px-2 py-1.5 sm:px-4 sm:py-2 rounded-lg bg-orange-100 text-orange-800 hover:bg-orange-200 transition-all shadow-sm text-xs sm:text-sm border border-orange-200"
          >
            <ClockIcon class="h-3 w-3 sm:h-4 sm:w-4 mr-1 sm:mr-2 text-orange-600" />
            <span class="font-medium">{{ pageantCounts.pending_approval }} Pending Approval</span>
          </Link>
          
          <Link :href="route('admin.pageants.index')" class="flex items-center px-2 py-1.5 sm:px-4 sm:py-2 rounded-lg bg-white/20 text-white hover:bg-white/30 transition-all shadow-sm text-xs sm:text-sm">
            <ActivityIcon class="h-3 w-3 sm:h-4 sm:w-4 mr-1 sm:mr-2" />
            <span class="font-medium">Manage Pageants</span>
          </Link>
          
          <Link href="/admin/audit-log" class="flex items-center px-2 py-1.5 sm:px-4 sm:py-2 rounded-lg bg-white/20 text-white hover:bg-white/30 transition-all shadow-sm text-xs sm:text-sm">
            <FileText class="h-3 w-3 sm:h-4 sm:w-4 mr-1 sm:mr-2" />
            <span class="font-medium">View Audit Log</span>
          </Link>
        </div>
      </div>
    </div>

    <!-- Pageant Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
      <!-- Pageant Status Summary Card -->
      <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden hover:shadow-lg transition-all">
        <div class="px-4 sm:px-6 pt-4 sm:pt-6 pb-3 sm:pb-4 border-b border-gray-100">
          <div class="flex items-center justify-between">
            <h2 class="text-lg sm:text-xl font-semibold text-gray-900">Pageant Status</h2>
            <div class="p-1.5 sm:p-2 bg-teal-100 rounded-full">
              <Crown class="h-4 w-4 sm:h-5 sm:w-5 text-teal-600" />
            </div>
          </div>
          <p class="text-xs sm:text-sm text-gray-500 mt-1">Overview of all pageant events</p>
        </div>
        
        <div class="grid grid-cols-2 gap-2 sm:gap-4 p-3 sm:p-6">
          <template v-if="isLoading">
            <SkeletonCard v-for="i in 4" :key="i" />
          </template>
          <template v-else>
            <!-- Active Pageants -->
            <div class="bg-gradient-to-br from-teal-50 to-teal-100 rounded-lg p-3 sm:p-4 border border-teal-200">
              <div class="flex items-center space-x-2">
                <div class="p-1.5 sm:p-2 bg-teal-100 rounded-full border border-teal-200">
                  <ActivityIcon class="h-3 w-3 sm:h-4 sm:w-4 text-teal-600" />
                </div>
                <span class="text-xs sm:text-sm font-medium text-teal-800">Active</span>
              </div>
              <p class="text-xl sm:text-2xl font-bold text-gray-900 mt-1 sm:mt-2">{{ pageantCounts.active }}</p>
              <p class="text-2xs sm:text-xs text-teal-700 mt-0.5 sm:mt-1">Currently running pageants</p>
            </div>
            
            <!-- Pending Approval Pageants -->
            <div v-if="pageantCounts.pending_approval > 0" class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-3 sm:p-4 border border-orange-200">
              <div class="flex items-center space-x-2">
                <div class="p-1.5 sm:p-2 bg-orange-100 rounded-full border border-orange-200">
                  <ClockIcon class="h-3 w-3 sm:h-4 sm:w-4 text-orange-600" />
                </div>
                <span class="text-xs sm:text-sm font-medium text-orange-800">Pending Approval</span>
              </div>
              <p class="text-xl sm:text-2xl font-bold text-gray-900 mt-1 sm:mt-2">{{ pageantCounts.pending_approval }}</p>
              <p class="text-2xs sm:text-xs text-orange-700 mt-0.5 sm:mt-1">Awaiting admin approval</p>
            </div>
            
            <!-- Draft/Setup Pageants -->
            <div class="bg-gradient-to-br from-teal-50 to-teal-100 rounded-lg p-3 sm:p-4 border border-teal-200 opacity-90">
              <div class="flex items-center space-x-2">
                <div class="p-1.5 sm:p-2 bg-teal-100 rounded-full border border-teal-200">
                  <FileEdit class="h-3 w-3 sm:h-4 sm:w-4 text-teal-600" />
                </div>
                <span class="text-xs sm:text-sm font-medium text-teal-800">Draft/Setup</span>
              </div>
              <p class="text-xl sm:text-2xl font-bold text-gray-900 mt-1 sm:mt-2">{{ pageantCounts.draft + pageantCounts.setup }}</p>
              <p class="text-2xs sm:text-xs text-teal-700 mt-0.5 sm:mt-1">Pageants in preparation</p>
            </div>
            
            <!-- Completed Pageants -->
            <div class="bg-gradient-to-br from-teal-50 to-teal-100 rounded-lg p-3 sm:p-4 border border-teal-200 opacity-85">
              <div class="flex items-center space-x-2">
                <div class="p-1.5 sm:p-2 bg-teal-100 rounded-full border border-teal-200">
                  <CheckCircle class="h-3 w-3 sm:h-4 sm:w-4 text-teal-600" />
                </div>
                <span class="text-xs sm:text-sm font-medium text-teal-800">Completed</span>
              </div>
              <p class="text-xl sm:text-2xl font-bold text-gray-900 mt-1 sm:mt-2">{{ pageantCounts.completed }}</p>
              <p class="text-2xs sm:text-xs text-teal-700 mt-0.5 sm:mt-1">Finished pageant events</p>
            </div>
            
            <!-- Archived Pageants -->
            <div class="bg-gradient-to-br from-teal-50 to-teal-100 rounded-lg p-3 sm:p-4 border border-teal-200 opacity-80">
              <div class="flex items-center space-x-2">
                <div class="p-1.5 sm:p-2 bg-teal-100 rounded-full border border-teal-200">
                  <Archive class="h-3 w-3 sm:h-4 sm:w-4 text-teal-600" />
                </div>
                <span class="text-xs sm:text-sm font-medium text-teal-800">Archived</span>
              </div>
              <p class="text-xl sm:text-2xl font-bold text-gray-900 mt-1 sm:mt-2">{{ pageantCounts.archived }}</p>
              <p class="text-2xs sm:text-xs text-teal-700 mt-0.5 sm:mt-1">Archived pageant records</p>
            </div>
          </template>
        </div>
        
        <div class="flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 bg-teal-50 border-t border-teal-100">
          <p class="text-sm font-medium text-gray-900">Total Pageants</p>
          <p class="text-xl sm:text-2xl font-bold text-teal-600">{{ pageantCounts.total }}</p>
        </div>
      </div>
      
      <!-- User Overview Card -->
      <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden hover:shadow-lg transition-all">
        <div class="px-4 sm:px-6 pt-4 sm:pt-6 pb-3 sm:pb-4 border-b border-gray-100">
          <div class="flex items-center justify-between">
            <h2 class="text-lg sm:text-xl font-semibold text-gray-900">System Users</h2>
            <div class="p-1.5 sm:p-2 bg-teal-100 rounded-full">
              <Users class="h-4 w-4 sm:h-5 sm:w-5 text-teal-600" />
            </div>
          </div>
          <p class="text-xs sm:text-sm text-gray-500 mt-1">Overview of user accounts</p>
        </div>
        
        <div class="p-3 sm:p-6 grid grid-cols-1 gap-2 sm:gap-4">
          <template v-if="isLoading">
            <SkeletonCard v-for="i in 3" :key="i" />
          </template>
          <template v-else>
            <!-- Organizers -->
            <div class="flex items-center justify-between p-3 sm:p-4 bg-gradient-to-r from-teal-50 to-teal-100 rounded-lg border border-teal-200">
              <div class="flex items-center space-x-2 sm:space-x-3">
                <div class="p-1.5 sm:p-2 bg-teal-100 rounded-full border border-teal-200">
                  <Users class="h-4 w-4 sm:h-5 sm:w-5 text-teal-600" />
                </div>
                <div>
                  <p class="text-sm font-medium text-teal-800">Organizers</p>
                  <p class="text-2xs sm:text-xs text-teal-700">Event managers</p>
                </div>
              </div>
              <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ userCounts.organizers }}</p>
            </div>
            
            <!-- Tabulators -->
            <div class="flex items-center justify-between p-3 sm:p-4 bg-gradient-to-r from-teal-50 to-teal-100 rounded-lg border border-teal-200 opacity-90">
              <div class="flex items-center space-x-2 sm:space-x-3">
                <div class="p-1.5 sm:p-2 bg-teal-100 rounded-full border border-teal-200">
                  <Calculator class="h-4 w-4 sm:h-5 sm:w-5 text-teal-600" />
                </div>
                <div>
                  <p class="text-sm font-medium text-teal-800">Tabulators</p>
                  <p class="text-2xs sm:text-xs text-teal-700">Score managers</p>
                </div>
              </div>
              <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ userCounts.tabulators }}</p>
            </div>
            
            <!-- Judges -->
            <div class="flex items-center justify-between p-3 sm:p-4 bg-gradient-to-r from-teal-50 to-teal-100 rounded-lg border border-teal-200 opacity-85">
              <div class="flex items-center space-x-2 sm:space-x-3">
                <div class="p-1.5 sm:p-2 bg-teal-100 rounded-full border border-teal-200">
                  <GavelIcon class="h-4 w-4 sm:h-5 sm:w-5 text-teal-600" />
                </div>
                <div>
                  <p class="text-sm font-medium text-teal-800">Judges</p>
                  <p class="text-2xs sm:text-xs text-teal-700">Event judges</p>
                </div>
              </div>
              <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ userCounts.judges }}</p>
            </div>
          </template>
        </div>
      </div>
    </div>

    <!-- Recent Pageants -->
    <div class="bg-white shadow-md rounded-xl border border-gray-100 overflow-hidden">
      <div class="p-4 sm:p-6">
        <div class="flex items-center justify-between mb-4 sm:mb-6">
          <h2 class="text-lg sm:text-xl font-semibold text-gray-900">Recent Pageants</h2>
          <Link :href="route('admin.pageants.index')" class="text-xs sm:text-sm text-teal-600 hover:text-teal-800 flex items-center">
            View All <ChevronRight class="h-3 w-3 sm:h-4 sm:w-4 ml-1" />
          </Link>
        </div>
        
        <div class="overflow-x-auto -mx-4 sm:-mx-0">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-teal-50">
              <tr>
                <th scope="col" class="px-3 sm:px-6 py-2 sm:py-3 text-left text-2xs sm:text-xs font-medium text-teal-600 uppercase tracking-wider">Name</th>
                <th scope="col" class="px-3 sm:px-6 py-2 sm:py-3 text-left text-2xs sm:text-xs font-medium text-teal-600 uppercase tracking-wider">Status</th>
                <th scope="col" class="px-3 sm:px-6 py-2 sm:py-3 text-left text-2xs sm:text-xs font-medium text-teal-600 uppercase tracking-wider hidden sm:table-cell">Date</th>
                <th scope="col" class="px-3 sm:px-6 py-2 sm:py-3 text-left text-2xs sm:text-xs font-medium text-teal-600 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <template v-if="isLoading">
                <tr v-for="i in 5" :key="i" class="hover:bg-gray-50">
                  <td colspan="4" class="px-3 sm:px-6 py-3 sm:py-4">
                    <SkeletonRow />
                  </td>
                </tr>
              </template>
              <template v-else-if="recentPageants.length > 0">
                <tr v-for="pageant in recentPageants" :key="pageant.id" class="hover:bg-teal-50 transition-colors">
                  <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-6 w-6 sm:h-8 sm:w-8 rounded-full bg-gradient-to-r from-teal-500 to-teal-600 flex items-center justify-center text-white font-bold text-xs sm:text-sm">
                        {{ pageant.name.charAt(0) }}
                      </div>
                      <div class="ml-2 sm:ml-4">
                        <div class="text-xs sm:text-sm font-medium text-gray-900 truncate max-w-[100px] sm:max-w-none">{{ pageant.name }}</div>
                        <div class="text-2xs sm:text-xs text-gray-500 truncate max-w-[100px] sm:max-w-none">{{ pageant.location || 'No location' }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                    <span class="px-1.5 sm:px-3 py-0.5 sm:py-1 inline-flex text-2xs sm:text-xs leading-5 font-semibold rounded-full" :class="getStatusClass(pageant.status)">
                      {{ pageant.status }}
                    </span>
                  </td>
                  <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-2xs sm:text-sm text-gray-500 hidden sm:table-cell">
                    {{ pageant.start_date || 'Not set' }}
                  </td>
                  <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-2xs sm:text-sm font-medium">
                    <Link 
                      :href="getPageantDetailRoute(pageant)"
                      class="text-teal-600 hover:text-teal-900 inline-flex items-center space-x-1"
                    >
                      <ExternalLink class="h-3 w-3 sm:h-4 sm:w-4" />
                      <span>View</span>
                    </Link>
                  </td>
                </tr>
              </template>
              <tr v-else>
                <td colspan="4" class="px-3 sm:px-6 py-4 text-center text-xs sm:text-sm text-gray-500">
                  No pageants found
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white shadow-md rounded-xl border border-gray-100 overflow-hidden">
      <div class="p-4 sm:p-6">
        <div class="flex items-center justify-between mb-4 sm:mb-6">
          <h2 class="text-lg sm:text-xl font-semibold text-gray-900">Recent Activity</h2>
          <Link href="/admin/audit-log" class="text-xs sm:text-sm text-teal-600 hover:text-teal-800 flex items-center">
            View All <ChevronRight class="h-3 w-3 sm:h-4 sm:w-4 ml-1" />
          </Link>
        </div>
        
        <div class="space-y-2 sm:space-y-3">
          <template v-if="isLoading">
            <div v-for="i in 5" :key="i" class="bg-gray-50 rounded-lg p-3 sm:p-4 animate-pulse">
              <div class="h-4 sm:h-5 bg-gray-200 rounded w-3/4 mb-2 sm:mb-3"></div>
              <div class="h-3 sm:h-4 bg-gray-200 rounded w-1/2"></div>
            </div>
          </template>
          <template v-else-if="recentActivities.length > 0">
            <div 
              v-for="activity in recentActivities" 
              :key="activity.id" 
              class="bg-gradient-to-r from-gray-50 to-white hover:from-teal-50 hover:to-white rounded-lg p-3 sm:p-4 border border-gray-100 hover:border-teal-100 transition-all group"
            >
              <div class="flex items-start">
                <div class="flex-shrink-0 h-7 w-7 sm:h-8 sm:w-8 rounded-full flex items-center justify-center text-white font-bold text-xs" :class="getActivityIconClass(activity.action_type)">
                  <component :is="getActivityIcon(activity.action_type)" class="h-3.5 w-3.5 sm:h-4 sm:w-4" />
                </div>
                <div class="ml-3 flex-1 min-w-0">
                  <p class="text-xs sm:text-sm text-gray-900 font-medium truncate">
                    {{ formatActivityDetails(activity) }}
                  </p>
                  <div class="flex items-center mt-1 text-2xs sm:text-xs text-gray-500">
                    <Clock class="h-3 w-3 mr-1 flex-shrink-0" />
                    <span>{{ formatDate(activity.created_at) }}</span>
                    <span class="mx-1">•</span>
                    <User class="h-3 w-3 mr-1 flex-shrink-0" />
                    <span class="truncate">{{ activity.user?.name || 'System' }}</span>
                  </div>
                </div>
              </div>
              <div class="mt-2 pl-10 opacity-0 group-hover:opacity-100 transition-opacity">
                <Link 
                  v-if="activity.target_entity === 'Pageant' && activity.target_id" 
                  :href="getPageantDetailRouteById(activity.target_id)"
                  class="inline-flex items-center px-2 py-1 text-2xs sm:text-xs font-medium text-teal-600 bg-teal-50 rounded-md hover:bg-teal-100 transition-colors"
                >
                  <ExternalLink class="h-2.5 w-2.5 sm:h-3 sm:w-3 mr-1" />
                  View Pageant
                </Link>
              </div>
            </div>
          </template>
          <div v-else class="bg-gray-50 rounded-lg p-3 sm:p-4 text-xs sm:text-sm text-gray-500 text-center">
            No recent activity found
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
  Calendar, CalendarDays, Crown, RefreshCw, ChevronRight, 
  Users, Calculator, TrendingUp, TrendingDown, Activity as ActivityIcon,
  Eye, Edit, Plus, CalendarClock, CheckCircle, FileEdit, Archive,
  ExternalLink, FileText, Gavel as GavelIcon, User as UserIcon,
  Clock as ClockIcon, Server as ServerIcon, Shield as ShieldIcon,
  Settings as SettingsIcon, Trash as TrashIcon, Key as KeyIcon,
  Unlock as UnlockIcon, Lock as LockIcon
} from 'lucide-vue-next';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SkeletonCard from '@/Components/Skeletons/SkeletonCard.vue';
import SkeletonRow from '@/Components/Skeletons/SkeletonRow.vue';
import SkeletonActivity from '@/Components/Skeletons/SkeletonActivity.vue';

const props = defineProps({
  pageantCounts: Object,
  userCounts: Object,
  recentPageantActivity: Array,
  recentUserActivity: Array,
  recentPageants: Array,
  user: Object
});

const isLoading = ref(false);

// Combine all recent activities into one array
const recentActivities = computed(() => {
  // Use empty arrays if props are undefined
  const pageantActivity = props.recentPageantActivity || [];
  const userActivity = props.recentUserActivity || [];
  
  // Combine and sort activities by created_at (most recent first)
  return [...pageantActivity, ...userActivity]
    .sort((a, b) => {
      return new Date(b.created_at) - new Date(a.created_at);
    })
    .slice(0, 5); // Show only 5 most recent activities
});

const formattedDate = computed(() => {
  return new Date().toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
});

// Format date to readable format
const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    hour12: true
  }).format(date);
};

// Get CSS class for status badge - Revised with different colors
const getStatusClass = (status) => {
  if (!status) return 'bg-gray-100 text-gray-800';
  
  const classes = {
    'Draft': 'bg-gray-100 text-gray-800',
    'Setup': 'bg-blue-100 text-blue-800',
    'Active': 'bg-green-100 text-green-800',
    'Completed': 'bg-purple-100 text-purple-800',
    'Unlocked_For_Edit': 'bg-amber-100 text-amber-800',
    'Archived': 'bg-slate-100 text-slate-800',
    'Cancelled': 'bg-red-100 text-red-800'
  };
  
  return classes[status] || 'bg-teal-100 text-teal-800';
};

// Get appropriate icon for activity type
const getActivityIcon = (actionType) => {
  if (!actionType) return ActivityIcon;
  
  const icons = {
    'CREATE': Plus,
    'UPDATE': Edit,
    'DELETE': TrashIcon,
    'GRANT_EDIT_PERMISSION': UnlockIcon,
    'REVOKE_EDIT_PERMISSION': LockIcon,
    'CHANGE_STATUS': SettingsIcon,
    'ASSIGN_ORGANIZER': UserIcon,
    'LOGIN': KeyIcon,
    'LOGOUT': KeyIcon
  };
  
  return icons[actionType] || ActivityIcon;
};

// Get appropriate icon background class for activity type
const getActivityIconClass = (actionType) => {
  if (!actionType) return 'bg-teal-100 text-teal-600';
  
  const classes = {
    'CREATE': 'bg-green-100 text-green-600',
    'UPDATE': 'bg-blue-100 text-blue-600',
    'DELETE': 'bg-red-100 text-red-600',
    'GRANT_EDIT_PERMISSION': 'bg-amber-100 text-amber-600',
    'REVOKE_EDIT_PERMISSION': 'bg-purple-100 text-purple-600',
    'CHANGE_STATUS': 'bg-teal-100 text-teal-600',
    'ASSIGN_ORGANIZER': 'bg-indigo-100 text-indigo-600',
    'LOGIN': 'bg-emerald-100 text-emerald-600',
    'LOGOUT': 'bg-slate-100 text-slate-600'
  };
  
  return classes[actionType] || 'bg-teal-100 text-teal-600';
};

// Format activity details for display
const formatActivityDetails = (activity) => {
  if (!activity) return '';
  
  return activity.details || `${activity.action_type} on ${activity.target_entity || 'system'}`;
};

// Get route for pageant detail by ID
const getPageantDetailRouteById = (pageantId) => {
  if (!pageantId) return '#';
  
  // Default to pageants detail route
  return route('admin.pageants.detail', { id: pageantId });
};

// Get link to view target entity
const getActivityTargetLink = (activity) => {
  if (activity.target_entity === 'Pageant' && activity.target_id) {
    return `/admin/pageants/${activity.target_id}`;
  }
  return '#';
};

// Get appropriate route for pageant detail page based on status
const getPageantDetailRoute = (pageant) => {
  const status = pageant.status;
  
  if (['Draft', 'Setup', 'Active'].includes(status)) {
    return route('admin.pageants.detail', { id: pageant.id });
  } else if (['Completed', 'Unlocked_For_Edit'].includes(status)) {
    return route('admin.pageants.previous.detail', { id: pageant.id });
  } else if (['Archived', 'Cancelled'].includes(status)) {
    return route('admin.pageants.archived.detail', { id: pageant.id });
  }
  
  return route('admin.pageants.detail', { id: pageant.id });
};

// Refresh dashboard data
const refreshDashboard = () => {
  isLoading.value = true;
  
  // Simulate API request with a timeout
  setTimeout(() => {
    window.location.reload();
  }, 500);
};
</script>

<script>
export default {
  layout: AdminLayout,
  components: {
    SkeletonCard,
    SkeletonRow,
    SkeletonActivity
  }
}
</script>