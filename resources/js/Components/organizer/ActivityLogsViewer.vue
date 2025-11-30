<template>
  <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden animate-fade-in h-fit">
    <!-- Header -->
    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
      <div>
        <h2 class="text-lg font-bold text-slate-900">Recent Activity</h2>
        <p class="text-sm text-slate-500">Latest updates</p>
      </div>
    </div>

    <!-- Logs List -->
    <div class="divide-y divide-slate-100">
      <div v-if="loading" class="p-6 space-y-4">
        <div v-for="i in 3" :key="i" class="flex items-center gap-4">
          <div class="h-10 w-10 bg-slate-100 rounded-full animate-pulse"></div>
          <div class="flex-1 space-y-2">
            <div class="h-4 w-3/4 bg-slate-100 rounded animate-pulse"></div>
            <div class="h-3 w-1/2 bg-slate-100 rounded animate-pulse"></div>
          </div>
        </div>
      </div>
      
      <div v-else-if="!logs.length" class="p-8 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-50 mb-4">
          <Activity class="h-8 w-8 text-slate-400" />
        </div>
        <h3 class="text-lg font-bold text-slate-900">No recent activity</h3>
        <p class="text-sm text-slate-500 mt-1">Activities will appear here as they happen.</p>
      </div>
      
      <div v-else v-for="log in logs" :key="log.id"
           class="group p-5 hover:bg-slate-50/80 transition-all">
        <div class="flex items-start gap-4">
          <div class="flex-shrink-0">
            <div class="p-2 rounded-xl bg-gradient-to-br shadow-sm group-hover:scale-110 transition-transform"
                 :class="getActivityIconClass(log.action_type)">
              <component :is="getActivityIcon(log.action_type)" class="h-4 w-4 text-white" />
            </div>
          </div>
          
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-slate-900">
              {{ log.description }}
            </p>
            <div class="flex flex-wrap items-center gap-2 mt-1 text-xs text-slate-500">
              <span class="font-medium text-teal-600">{{ log.pageant_name }}</span>
              <span class="text-slate-300">•</span>
              <span>{{ log.formatted_time }}</span>
              <span class="text-slate-300">•</span>
              <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 font-medium">
                {{ log.user_name }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { 
  Activity, Star, Edit, UserPlus, Users, Scale, 
  BarChart2, Timer, CheckCircle, ListChecks, Crown, Sparkles 
} from 'lucide-vue-next'
import { route } from 'ziggy-js'

interface ActivityLog {
  id: number
  pageant_id?: number
  pageant_name?: string
  user_name: string
  user_role: string
  action_type: string
  description: string
  entity_type?: string
  entity_id?: number
  metadata?: any
  created_at: string
  formatted_time: string
}

interface Props {
  pageantIds?: number[]
  initialLimit?: number
}

const props = withDefaults(defineProps<Props>(), {
  initialLimit: 15
})

const logs = ref<ActivityLog[]>([])
const loading = ref(false)

const fetchLogs = async () => {
  loading.value = true

  try {
    const params = new URLSearchParams()
    params.append('per_page', props.initialLimit.toString())

    const url = route('organizer.activities') + '?' + params.toString()

    const response = await fetch(url, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    })

    if (!response.ok) {
      throw new Error('Failed to fetch activities')
    }

    const data = await response.json()
    logs.value = data.activities || []
  } catch (err) {
    console.error('Error fetching activities:', err)
  } finally {
    loading.value = false
  }
}

// Helper functions for activity display
const getActivityIcon = (actionType: string) => {
  const iconMap: Record<string, any> = {
    'SCORE_SUBMITTED': Star,
    'SCORE_UPDATED': Edit,
    'CONTESTANT_ADDED': UserPlus,
    'CONTESTANT_UPDATED': Users,
    'CONTESTANT_REMOVED': Users,
    'JUDGE_ASSIGNED': Scale,
    'JUDGE_REMOVED': Users,
    'TABULATOR_ASSIGNED': BarChart2,
    'TABULATOR_REMOVED': BarChart2,
    'ROUND_STARTED': Timer,
    'ROUND_COMPLETED': CheckCircle,
    'CRITERIA_CREATED': ListChecks,
    'CRITERIA_UPDATED': ListChecks,
    'PAGEANT_UPDATED': Crown,
    'STATUS_CHANGED': Sparkles,
  }
  return iconMap[actionType] || Activity
}

const getActivityIconClass = (actionType: string) => {
  const classMap: Record<string, string> = {
    'SCORE_SUBMITTED': 'from-amber-400 to-amber-600',
    'SCORE_UPDATED': 'from-amber-400 to-amber-600',
    'CONTESTANT_ADDED': 'from-blue-400 to-blue-600',
    'CONTESTANT_UPDATED': 'from-blue-400 to-blue-600',
    'CONTESTANT_REMOVED': 'from-red-400 to-red-600',
    'JUDGE_ASSIGNED': 'from-indigo-400 to-indigo-600',
    'JUDGE_REMOVED': 'from-red-400 to-red-600',
    'TABULATOR_ASSIGNED': 'from-purple-400 to-purple-600',
    'TABULATOR_REMOVED': 'from-red-400 to-red-600',
    'ROUND_STARTED': 'from-teal-400 to-teal-600',
    'ROUND_COMPLETED': 'from-teal-400 to-teal-600',
    'CRITERIA_CREATED': 'from-cyan-400 to-cyan-600',
    'CRITERIA_UPDATED': 'from-cyan-400 to-cyan-600',
    'PAGEANT_UPDATED': 'from-emerald-400 to-emerald-600',
    'STATUS_CHANGED': 'from-pink-400 to-pink-600',
  }
  return classMap[actionType] || 'from-slate-400 to-slate-600'
}

onMounted(() => {
  fetchLogs()

  // Subscribe to real-time activity updates
  if (window.Echo && props.pageantIds && props.pageantIds.length > 0) {
    props.pageantIds.forEach(pageantId => {
      window.Echo.private(`organizer.pageant.${pageantId}`)
        .listen('.activity.created', (event: any) => {
          logs.value.unshift({
            id: event.id,
            pageant_id: event.pageant_id,
            pageant_name: event.pageant_name || 'Unknown Pageant',
            user_name: event.user_name,
            user_role: event.user_role,
            action_type: event.action_type,
            description: event.description,
            entity_type: event.entity_type,
            entity_id: event.entity_id,
            metadata: event.metadata,
            created_at: event.created_at,
            formatted_time: event.formatted_time,
          })
          
          // Keep list to a reasonable size
          if (logs.value.length > props.initialLimit) {
            logs.value = logs.value.slice(0, props.initialLimit)
          }
        })
    })
  }
})

onUnmounted(() => {
  if (window.Echo && props.pageantIds && props.pageantIds.length > 0) {
    props.pageantIds.forEach(pageantId => {
      window.Echo.leave(`organizer.pageant.${pageantId}`)
    })
  }
})
</script>
