<template>
  <div class="space-y-6">
    <!-- Header Section with Gradient Background -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
      <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-pink-50">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-semibold text-gray-800">Contestants</h1>
            <p class="mt-1 text-sm text-gray-600">Manage contestants across all your pageants</p>
          </div>
          <button
            @click="ShowAddModal = true"
            class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 rounded-lg shadow-sm hover:shadow transition-all transform hover:-translate-y-0.5 flex items-center"
          >
            <Plus class="h-4 w-4 mr-2" />
            Add Contestant
          </button>
        </div>
      </div>
    </div>

    <!-- Contestant List -->
    <div class="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-200">
      <div class="px-6 py-5 border-b border-gray-200">
        <div class="flex justify-between items-center">
          <h2 class="text-xl font-semibold text-gray-800 flex items-center">
            <Users class="h-5 w-5 mr-2 text-purple-500" />
            All Contestants 
            <span class="ml-2 text-gray-500 text-sm font-normal">({{ Contestants.length }})</span>
          </h2>
          
          <!-- Search and filter controls - Add later if needed -->
        </div>
      </div>

      <div class="p-6">
        <!-- No contestants message -->
        <div v-if="Contestants.length === 0" class="text-center py-12">
          <div class="mx-auto w-24 h-24 flex items-center justify-center rounded-full bg-gradient-to-r from-purple-100 to-pink-100 mb-4">
            <Users class="h-12 w-12 text-purple-500" />
          </div>
          <h3 class="text-lg font-medium text-gray-900 mb-1">No contestants yet</h3>
          <p class="text-gray-600 mb-6 max-w-md mx-auto">Add your first contestant to get started</p>
          <button
            @click="ShowAddModal = true"
            class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 rounded-lg shadow-sm hover:shadow transition-all transform hover:-translate-y-0.5"
          >
            <Plus class="h-4 w-4 inline mr-1" />
            Add Contestant
          </button>
        </div>

        <!-- Contestants Grid -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <div 
            v-for="contestant in Contestants" 
            :key="contestant.id"
            class="bg-white border border-gray-200 rounded-xl overflow-hidden cursor-pointer hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 group"
            @click="ViewContestantDetails(contestant)"
          >
            <div class="relative h-64">
              <img 
                :src="contestant.photo" 
                :alt="contestant.name" 
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
              />
              <!-- Gradient overlay -->
              <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent opacity-70"></div>
              
              <!-- Contestant number badge -->
              <div class="absolute top-3 right-3">
                <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-purple-600 text-white text-sm font-bold shadow-md">
                  #{{ contestant.contestNumber }}
                </span>
              </div>
              
              <!-- Action buttons overlay - visible on hover -->
              <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                <button 
                  @click.stop="ViewContestantDetails(contestant)" 
                  class="bg-purple-500/20 backdrop-blur-sm text-white px-3 py-1.5 rounded-full text-sm border border-purple-500/30 hover:bg-purple-500/30 transition-colors"
                >
                  <Eye class="h-4 w-4 inline-block mr-1" />
                  View
                </button>
                <button 
                  @click.stop="EditContestant(contestant)" 
                  class="bg-white/20 backdrop-blur-sm text-white px-3 py-1.5 rounded-full text-sm border border-white/30 hover:bg-white/30 transition-colors"
                >
                  <Edit2 class="h-4 w-4 inline-block mr-1" />
                  Edit
                </button>
                <button 
                  @click.stop="DeleteContestant(contestant.id)" 
                  class="bg-red-500/20 backdrop-blur-sm text-white px-3 py-1.5 rounded-full text-sm border border-red-500/30 hover:bg-red-500/30 transition-colors"
                >
                  <Trash2 class="h-4 w-4 inline-block mr-1" />
                  Delete
                </button>
              </div>
              
              <!-- Contestant info at bottom -->
              <div class="absolute bottom-0 left-0 right-0 p-4 text-white">
                <h3 class="text-lg font-bold truncate">{{ contestant.name }}</h3>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1">
                  <div class="flex items-center text-sm text-purple-100">
                    <MapPin class="h-3.5 w-3.5 mr-1 text-purple-300" />
                    <span class="truncate">{{ contestant.city }}</span>
                  </div>
                  <div class="flex items-center text-sm text-purple-100">
                    <Calendar class="h-3.5 w-3.5 mr-1 text-purple-300" />
                    <span>{{ contestant.age }} years</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <TransitionRoot appear :show="ShowAddModal" as="template">
      <Dialog as="div" @close="ShowAddModal = false" class="relative z-30">
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
                    {{ EditingContestant ? 'Edit Contestant' : 'Add New Contestant' }}
                  </DialogTitle>
                  <p class="mt-2 text-gray-600 max-w-2xl">
                    {{ EditingContestant ? 'Update contestant information' : 'Add a new contestant to your pageant' }}
                  </p>
                  <button 
                    @click="ShowAddModal = false" 
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors"
                  >
                    <XCircle class="h-6 w-6" />
                  </button>
                </div>

                <form @submit.prevent="HandleSubmit" class="p-6">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div class="space-y-6">
                      <div>
                        <label for="contestNumber" class="block text-sm font-medium text-gray-700 mb-1">
                          Contestant Number <span class="text-red-500">*</span>
                        </label>
                        <input
                          id="contestNumber"
                          v-model="Form.contestNumber"
                          type="text"
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200 focus:ring-opacity-50 transition-colors"
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
                          v-model="Form.name"
                          type="text"
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200 focus:ring-opacity-50 transition-colors"
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
                          v-model="Form.age"
                          type="number"
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200 focus:ring-opacity-50 transition-colors"
                          placeholder="e.g. 24"
                          required
                        />
                        <p class="mt-1 text-xs text-gray-500">Enter the contestant's age</p>
                      </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6">
                      <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-1">
                          Location <span class="text-red-500">*</span>
                        </label>
                        <input
                          id="city"
                          v-model="Form.city"
                          type="text"
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200 focus:ring-opacity-50 transition-colors"
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
                          v-model="Form.bio"
                          rows="4"
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200 focus:ring-opacity-50 transition-colors"
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
                    <div v-if="Form.photos && Form.photos.length > 0" class="mb-4">
                      <div class="flex flex-wrap gap-3">
                        <div 
                          v-for="(photo, index) in Form.photos" 
                          :key="index" 
                          class="relative w-24 h-24 rounded-lg overflow-hidden group"
                        >
                          <img :src="photo" class="w-full h-full object-cover" />
                          <button 
                            @click.prevent="removePhoto(index)"
                            class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white"
                          >
                            <Trash2 class="h-5 w-5" />
                          </button>
                        </div>
                      </div>
                    </div>
                    
                    <!-- Upload New Photos -->
                    <div class="flex flex-col w-full">
                      <label
                        class="flex flex-col w-full h-32 border-2 border-dashed rounded-lg border-gray-300 hover:border-purple-400 hover:bg-purple-50 transition-colors cursor-pointer"
                      >
                        <div class="flex flex-col items-center justify-center pt-7">
                          <Camera class="w-8 h-8 text-purple-400 group-hover:text-purple-600" />
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
                      @click="ShowAddModal = false"
                      class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300"
                    >
                      Cancel
                    </button>
                    <button
                      type="submit"
                      class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 rounded-lg shadow-sm hover:shadow transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
                    >
                      {{ EditingContestant ? 'Save Changes' : 'Add Contestant' }}
                    </button>
                  </div>
                </form>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- View Contestant Details Modal -->
    <TransitionRoot appear :show="ShowDetailsModal" as="template">
      <Dialog as="div" @close="ShowDetailsModal = false" class="relative z-30">
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
              <DialogPanel class="w-full max-w-7xl transform overflow-hidden rounded-2xl bg-white shadow-xl transition-all">
                <div v-if="SelectedContestant" class="flex flex-col md:flex-row w-full">
                  <!-- Photo Carousel Section - Left side on larger screens, top on mobile -->
                  <div class="md:w-[60%] relative">
                    <div class="relative h-full md:h-[600px]">
                      <!-- Main Photo with Animation -->
                      <motion.div
                        :initial="{ opacity: 0, x: -20 }"
                        :animate="{ opacity: 1, x: 0 }"
                        :transition="{ duration: 0.3 }"
                        class="w-full h-full absolute inset-0"
                        :key="currentPhotoIndex"
                      >
                        <img 
                          :src="activePhoto || SelectedContestant.photo" 
                          :alt="SelectedContestant.name" 
                          class="w-full h-full object-cover"
                        />
                      </motion.div>
                      
                      <!-- Gradient overlay -->
                      <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent opacity-70"></div>
                      
                      <!-- Contestant number badge -->
                      <div class="absolute top-4 right-4">
                        <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-purple-600 text-white text-lg font-bold shadow-md">
                          #{{ SelectedContestant.contestNumber }}
                        </span>
                      </div>
                      
                      <!-- Photo Navigation Controls -->
                      <div class="absolute inset-x-0 top-1/2 -translate-y-1/2 flex justify-between px-4">
                        <button 
                          @click="prevPhoto" 
                          class="bg-black/20 backdrop-blur-sm text-white rounded-full p-2 hover:bg-black/40 transition-colors"
                          :class="{'opacity-50 cursor-not-allowed': currentPhotoIndex <= 0}"
                          :disabled="currentPhotoIndex <= 0"
                        >
                          <ChevronLeft class="h-6 w-6" />
                        </button>
                        <button 
                          @click="nextPhoto" 
                          class="bg-black/20 backdrop-blur-sm text-white rounded-full p-2 hover:bg-black/40 transition-colors"
                          :class="{'opacity-50 cursor-not-allowed': currentPhotoIndex >= contestantPhotos.length - 1}"
                          :disabled="currentPhotoIndex >= contestantPhotos.length - 1"
                        >
                          <ChevronRight class="h-6 w-6" />
                        </button>
                      </div>
                      
                      <!-- Thumbnails row at bottom -->
                      <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-black/80 to-transparent">
                        <div class="flex gap-2 overflow-x-auto pb-2 snap-x">
                          <motion.div
                            v-for="(photo, index) in contestantPhotos" 
                            :key="index"
                            @click="setActivePhoto(photo, index)"
                            class="h-14 w-14 flex-shrink-0 rounded-md overflow-hidden cursor-pointer snap-start transition-all"
                            :class="{ 'ring-2 ring-white': currentPhotoIndex === index }"
                            :initial="{ opacity: 0, y: 20 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ duration: 0.3, delay: index * 0.1 }"
                          >
                            <img :src="photo" class="h-full w-full object-cover" />
                          </motion.div>
                        </div>
                      </div>
                      
                      <!-- Name overlay at bottom of image on mobile only -->
                      <div class="absolute bottom-16 left-0 right-0 p-6 text-white md:hidden">
                        <h2 class="text-2xl font-bold">{{ SelectedContestant.name }}</h2>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Details Section - Right side on larger screens, bottom on mobile -->
                  <div class="md:w-[40%] p-6 relative">
                    <!-- Close button -->
                    <button 
                      @click="ShowDetailsModal = false" 
                      class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 transition-colors"
                    >
                      <XCircle class="h-6 w-6" />
                    </button>
                    
                    <!-- Details content -->
                    <div class="space-y-6">
                      <!-- Name - hidden on mobile (shown over image) -->
                      <div class="hidden md:block">
                        <h2 class="text-3xl font-bold text-gray-900">{{ SelectedContestant.name }}</h2>
                      </div>
                      
                      <!-- Info cards -->
                      <div class="grid grid-cols-2 gap-4">
                        <div class="bg-purple-50 rounded-lg p-4">
                          <div class="flex items-center">
                            <Calendar class="h-5 w-5 text-purple-600 mr-2" />
                            <span class="text-sm font-medium text-gray-600">Age</span>
                          </div>
                          <p class="mt-1 text-lg font-semibold text-gray-900">{{ SelectedContestant.age }} years</p>
                        </div>
                        
                        <div class="bg-purple-50 rounded-lg p-4">
                          <div class="flex items-center">
                            <MapPin class="h-5 w-5 text-purple-600 mr-2" />
                            <span class="text-sm font-medium text-gray-600">Location</span>
                          </div>
                          <p class="mt-1 text-lg font-semibold text-gray-900">{{ SelectedContestant.city }}</p>
                        </div>
                      </div>
                      
                      <!-- Biography -->
                      <div class="bg-white border border-gray-200 rounded-lg p-5">
                        <h3 class="text-xl font-semibold text-gray-800 mb-3 flex items-center">
                          <FileText class="h-5 w-5 text-purple-500 mr-2" />
                          Biography
                        </h3>
                        <p class="text-gray-700 leading-relaxed">{{ SelectedContestant.bio }}</p>
                      </div>
                      
                      <!-- Actions -->
                      <div class="flex justify-end space-x-3 pt-4">
                        <button
                          @click="EditContestant(SelectedContestant); ShowDetailsModal = false"
                          class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 rounded-lg shadow-sm hover:shadow transition-all flex items-center"
                        >
                          <Edit2 class="h-4 w-4 mr-2" />
                          Edit
                        </button>
                        <button
                          @click="DeleteContestant(SelectedContestant.id); ShowDetailsModal = false"
                          class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 rounded-lg shadow-sm hover:shadow transition-all flex items-center"
                        >
                          <Trash2 class="h-4 w-4 mr-2" />
                          Delete
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue'
import { 
  Plus, 
  MapPin, 
  Calendar, 
  Edit2, 
  Trash2, 
  XCircle, 
  Camera, 
  Users, 
  Eye, 
  FileText, 
  ChevronLeft, 
  ChevronRight 
} from 'lucide-vue-next'
import OrganizerLayout from '@/Layouts/OrganizerLayout.vue'
import { motion } from 'motion-v'

defineOptions({
  layout: OrganizerLayout
})

const ShowAddModal = ref(false)
const ShowDetailsModal = ref(false)
const EditingContestant = ref(null)
const SelectedContestant = ref(null)
const currentPhotoIndex = ref(0)
const activePhoto = ref(null)

const Form = ref({
  name: '',
  age: '',
  city: '',
  bio: '',
  photos: [], // Array to store multiple photos
  contestNumber: ''
})

// Mock data with multiple photos for each contestant
const Contestants = ref([
  {
    id: 1,
    name: 'Jane Smith',
    age: 23,
    city: 'New York',
    bio: 'Fashion model and entrepreneur with a passion for environmental activism',
    photo: 'https://randomuser.me/api/portraits/women/44.jpg',
    photos: [
      'https://randomuser.me/api/portraits/women/44.jpg',
      'https://images.unsplash.com/photo-1534528741775-53994a69daeb?ixlib=rb-4.0.3&auto=format&fit=crop&w=700&q=80',
      'https://images.unsplash.com/photo-1517841905240-472988babdf9?ixlib=rb-4.0.3&auto=format&fit=crop&w=700&q=80'
    ],
    contestNumber: '001'
  },
  {
    id: 2,
    name: 'Maria Rodriguez',
    age: 25,
    city: 'Miami',
    bio: 'Classical pianist and medical student working on healthcare initiatives',
    photo: 'https://randomuser.me/api/portraits/women/65.jpg',
    photos: [
      'https://randomuser.me/api/portraits/women/65.jpg',
      'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?ixlib=rb-4.0.3&auto=format&fit=crop&w=700&q=80',
      'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?ixlib=rb-4.0.3&auto=format&fit=crop&w=700&q=80'
    ],
    contestNumber: '002'
  },
  {
    id: 3,
    name: 'Sarah Johnson',
    age: 22,
    city: 'Los Angeles',
    bio: 'Actress and volunteer teaching arts to underprivileged children',
    photo: 'https://randomuser.me/api/portraits/women/58.jpg',
    photos: [
      'https://randomuser.me/api/portraits/women/58.jpg',
      'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?ixlib=rb-4.0.3&auto=format&fit=crop&w=700&q=80',
      'https://images.unsplash.com/photo-1488426862026-3ee34a7d66df?ixlib=rb-4.0.3&auto=format&fit=crop&w=700&q=80'
    ],
    contestNumber: '003'
  }
])

// Computed property to get all photos for current selected contestant
const contestantPhotos = computed(() => {
  if (!SelectedContestant.value) return []
  return SelectedContestant.value.photos || [SelectedContestant.value.photo]
})

const HandleSubmit = () => {
  if (EditingContestant.value) {
    const index = Contestants.value.findIndex(c => c.id === EditingContestant.value?.id)
    if (index !== -1) {
      // Ensure the main photo is updated from the first photo in array
      const updatedContestant = { 
        ...EditingContestant.value, 
        ...Form.value,
        photo: Form.value.photos[0] || EditingContestant.value.photo 
      }
      Contestants.value[index] = updatedContestant
    }
  } else {
    // Create new contestant with main photo from first upload
    Contestants.value.push({
      id: Contestants.value.length + 1,
      ...Form.value,
      photo: Form.value.photos[0] || 'https://randomuser.me/api/portraits/women/32.jpg' // Default photo for demo
    })
  }
  
  ShowAddModal.value = false
  EditingContestant.value = null
  resetForm()
}

const ViewContestantDetails = (contestant) => {
  SelectedContestant.value = contestant
  currentPhotoIndex.value = 0
  activePhoto.value = contestant.photos?.[0] || contestant.photo
  ShowDetailsModal.value = true
}

const EditContestant = (contestant) => {
  EditingContestant.value = contestant
  Form.value = { 
    ...contestant,
    photos: contestant.photos || [contestant.photo]
  }
  ShowAddModal.value = true
}

const DeleteContestant = (id) => {
  if (confirm('Are you sure you want to delete this contestant?')) {
    Contestants.value = Contestants.value.filter(c => c.id !== id)
  }
}

const handlePhotoChange = (event) => {
  const files = event.target.files
  if (!files || files.length === 0) return
  
  // Process multiple files
  for (let i = 0; i < files.length; i++) {
    const file = files[i]
    const reader = new FileReader()
    
    reader.onloadend = () => {
      Form.value.photos.push(reader.result)
    }
    
    reader.readAsDataURL(file)
  }
}

const removePhoto = (index) => {
  Form.value.photos.splice(index, 1)
}

const nextPhoto = () => {
  if (currentPhotoIndex.value < contestantPhotos.value.length - 1) {
    currentPhotoIndex.value++
    activePhoto.value = contestantPhotos.value[currentPhotoIndex.value]
  }
}

const prevPhoto = () => {
  if (currentPhotoIndex.value > 0) {
    currentPhotoIndex.value--
    activePhoto.value = contestantPhotos.value[currentPhotoIndex.value]
  }
}

const setActivePhoto = (photo, index) => {
  activePhoto.value = photo
  currentPhotoIndex.value = index
}

const resetForm = () => {
  Form.value = { 
    name: '', 
    age: '', 
    city: '', 
    bio: '', 
    photos: [], 
    contestNumber: '' 
  }
}
</script>

<style scoped>
/* Custom styling for the dialog panel to significantly increase width */
:deep(.max-w-7xl) {
  max-width: 60vw !important; /* Use almost full viewport width */
  width: 1920px !important; /* Set a very large base width */
}

/* Adjust image section height for the larger modal */
:deep(.md\:h-\[600px\]) {
  height: 80vh !important; /* Make image height responsive to viewport */
  max-height: 800px !important; /* But cap it at a reasonable max */
}
</style>