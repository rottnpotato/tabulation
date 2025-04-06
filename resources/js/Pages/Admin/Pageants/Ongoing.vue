<template>
  <Head title="Ongoing Pageants" />
  <div class="space-y-6">
    <div class="bg-white shadow-sm rounded-lg p-6">
      <h2 class="text-2xl font-semibold text-gray-900 mb-6">Ongoing Pageants</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Link 
          v-for="pageant in pageants" 
          :key="pageant.id" 
          :href="route('admin.pageants.detail', { id: pageant.id })"
          class="bg-white border rounded-lg shadow-sm hover:shadow-md transition-all duration-300 flex flex-col h-full relative overflow-hidden group cursor-pointer"
        >
          <!-- Cover image with gradient overlay -->
          <div class="h-48 w-full overflow-hidden relative">
            <img 
              :src="pageant.coverImage || '/images/placeholders/pageant-cover.jpg'" 
              :alt="pageant.name || 'Pageant'" 
              class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" 
            />
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
            <div class="absolute bottom-3 left-4 right-4 flex justify-between items-center">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-100 text-teal-800">
                <Clock class="h-3 w-3 mr-1" />
                {{ pageant.status || 'Draft' }}
              </span>
              <span class="text-white text-xs bg-teal-600/70 px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                View Details
              </span>
            </div>
          </div>
          
          <div class="p-5 flex-grow flex flex-col">
            <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">{{ pageant.name || 'Untitled Pageant' }}</h3>
            <p class="text-gray-600 mb-4 text-sm line-clamp-3">{{ pageant.description || 'No description provided. Click to view and update pageant details.' }}</p>
            
            <div class="space-y-3 mt-auto">
              <div class="flex items-center text-sm text-gray-500">
                <Calendar class="h-4 w-4 mr-2 text-teal-600" />
                {{ pageant.start_date ? pageant.start_date : 'Date not set' }}
              </div>
              <div class="flex items-center text-sm text-gray-500">
                <MapPin class="h-4 w-4 mr-2 text-teal-600" />
                {{ pageant.location || pageant.venue || 'Venue not specified' }}
              </div>
              <div class="flex items-center text-sm text-gray-500">
                <Users class="h-4 w-4 mr-2 text-teal-600" />
                {{ pageant.contestants || pageant.organizers ? (pageant.organizers.length + ' Organizers') : '0 Contestants' }}
              </div>
              <div class="flex items-center text-sm text-gray-500">
                <Timer class="h-4 w-4 mr-2 text-teal-600" />
                {{ pageant.currentRound || pageant.status || 'Setup Phase' }}
              </div>
            </div>

            <!-- Progress indicator -->
            <div class="mt-5">
              <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                <span>Progress</span>
                <span>{{ pageant.progress || 0 }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-teal-600 h-2 rounded-full" :style="{ width: `${pageant.progress || getProgressFromStatus(pageant.status) || 0}%` }"></div>
              </div>
            </div>
          </div>
          
          <!-- Arrow indicator for clickable card -->
          <div class="absolute top-1/2 right-3 transform -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            <div class="bg-teal-600 text-white rounded-full p-1.5 shadow-md">
              <ChevronRight class="h-4 w-4" />
            </div>
          </div>
        </Link>
      </div>
      
      <!-- Empty state -->
      <div v-if="pageants.length === 0" class="text-center py-12">
        <div class="mx-auto h-16 w-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
          <Calendar class="h-8 w-8 text-gray-400" />
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-1">No ongoing pageants</h3>
        <p class="text-sm text-gray-500 max-w-md mx-auto">
          There are currently no ongoing pageants. Create a new pageant to get started.
        </p>
        <div class="mt-6">
          <Link 
            :href="route('admin.pageants.create')" 
            class="inline-flex items-center bg-teal-600 hover:bg-teal-700 text-white py-2 px-4 rounded-md font-medium transition-colors duration-300"
          >
            <Plus class="h-4 w-4 mr-2" />
            Create New Pageant
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Calendar, MapPin, Clock, Users, Timer, Eye, Plus, ChevronRight } from 'lucide-vue-next';
import AdminLayout from '@/Layouts/AdminLayout.vue';

// Page setup
defineOptions({
  layout: AdminLayout,
});

// Props
const props = defineProps({
  pageants: {
    type: Array,
    default: () => [],
  }
});

// Helper function to format date in a more readable format
const FormatDate = (dateString) => {
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(dateString).toLocaleDateString(undefined, options);
};

// Helper function to estimate progress based on pageant status
const getProgressFromStatus = (status) => {
  if (!status) return 0;
  
  const statusMap = {
    'Draft': 10,
    'Setup': 25,
    'Active': 60,
    'Completed': 100,
    'Unlocked_For_Edit': 100,
    'Archived': 100,
    'Cancelled': 100
  };
  
  return statusMap[status] || 0;
};

// If we're using mock data, display this. Otherwise, use props.pageants
const pageants = ref(props.pageants.length > 0 ? props.pageants : [
  {
    id: 1,
    name: 'Miss Universe 2025',
    description: 'The most prestigious beauty pageant showcasing grace, talent, and intelligence on the global stage. Contestants from over 90 countries compete for the coveted crown.',
    start_date: '2025-05-15',
    venue: 'Grand Plaza Hotel, Crystal Ballroom',
    status: 'Preliminary Round',
    contestants: 92,
    currentRound: 'Evening Gown',
    progress: 45,
    coverImage: 'https://images.unsplash.com/photo-1569937756447-1d44f657ee0b?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=2070&q=80'
  },
  {
    id: 2,
    name: 'Mr. World 2025',
    description: 'Celebrating male excellence in fashion, fitness, and personality. A global competition showcasing the finest talents from around the world.',
    start_date: '2025-06-20',
    venue: 'Metropolitan Convention Center',
    status: 'Semi-Finals',
    contestants: 78,
    currentRound: 'Talent Showcase',
    progress: 65,
    coverImage: 'https://images.unsplash.com/photo-1521119989659-a83eee488004?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=2023&q=80'
  },
  {
    id: 3,
    name: 'Teen Pageant Excellence 2025',
    description: 'A platform for young talents aged 13-19 to showcase their abilities and build confidence. Focuses on academic achievement, community service, and personal growth.',
    start_date: '2025-07-10',
    venue: 'Central City Auditorium',
    status: 'Registration Closing',
    contestants: 52,
    currentRound: 'Contestant Registration',
    progress: 15,
    coverImage: 'https://images.unsplash.com/photo-1566492031773-4f4e44671857?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=2070&q=80'
  },
  {
    id: 4,
    name: 'Ms. Elegance International',
    description: 'A celebration of mature beauty and accomplishment for women over 40. This pageant highlights professional achievements, community impact, and personal style.',
    start_date: '2025-08-05',
    venue: 'Regal Seasons Hotel',
    status: 'Interview Phase',
    contestants: 45,
    currentRound: 'Panel Interviews',
    progress: 30,
    coverImage: 'https://images.unsplash.com/photo-1502635385003-ee1e6a1a742d?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=2070&q=80'
  },
  {
    id: 5,
    name: 'Cultural Heritage Showcase 2025',
    description: 'A unique pageant celebrating cultural diversity and heritage through traditional costumes, performances, and storytelling from various regions worldwide.',
    start_date: '2025-09-12',
    venue: 'International Cultural Center',
    status: 'Preliminary Judging',
    contestants: 63,
    currentRound: 'National Costume',
    progress: 40,
    coverImage: 'https://images.unsplash.com/photo-1571380401583-82b0dafba9f4?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=2070&q=80'
  },
  {
    id: 6,
    name: 'Eco-Friendly Fashion Gala',
    description: 'A sustainable pageant focusing on eco-conscious fashion and environmental advocacy. Contestants showcase outfits made from recycled materials and present environmental projects.',
    start_date: '2025-10-18',
    venue: 'Green Living Exhibition Hall',
    status: 'Project Presentation',
    contestants: 38,
    currentRound: 'Eco Projects',
    progress: 55,
    coverImage: 'https://images.unsplash.com/photo-1523983254932-c7e6571c9d60?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=2072&q=80'
  }
]);
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style> 