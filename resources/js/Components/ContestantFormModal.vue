<template>
  <TransitionRoot appear :show="show" as="template">
    <Dialog as="div" @close="closeModal" class="relative z-30">
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
              <div class="relative bg-gradient-to-r from-purple-600 to-pink-500 p-6 text-white">
                <DialogTitle as="h3" class="text-2xl font-bold leading-6">
                  {{ contestant ? 'Edit Contestant' : 'Add New Contestant' }}
                </DialogTitle>
                <p class="mt-2 text-purple-100 max-w-2xl">
                  {{ contestant ? 'Update contestant details and photos' : 'Enter contestant information and upload photos' }}
                </p>
                <button 
                  @click="closeModal" 
                  class="absolute top-4 right-4 text-white hover:text-purple-200 transition-colors"
                >
                  <XCircle class="h-6 w-6" />
                </button>
              </div>

              <form @submit.prevent="handleSubmit" enctype="multipart/form-data" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Left Column -->
                  <div class="space-y-6">
                    <div>
                      <label for="contestantNumber" class="block text-sm font-medium text-gray-700 mb-1">
                        Contestant Number <span class="text-red-500">*</span>
                      </label>
                      <input
                        id="contestantNumber"
                        v-model="form.number"
                        type="number"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200 focus:ring-opacity-50 transition-colors"
                        :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-200': errors.number }"
                        placeholder="e.g. 1"
                        required
                      />
                      <p v-if="errors.number" class="mt-1 text-sm text-red-500">{{ errors.number }}</p>
                      <p v-else class="mt-1 text-xs text-gray-500">Enter the contestant's competition number</p>
                    </div>

                    <div>
                      <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Full Name <span class="text-red-500">*</span>
                      </label>
                      <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200 focus:ring-opacity-50 transition-colors"
                        :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-200': errors.name }"
                        placeholder="e.g. Jane Smith"
                        required
                      />
                      <p v-if="errors.name" class="mt-1 text-sm text-red-500">{{ errors.name }}</p>
                      <p v-else class="mt-1 text-xs text-gray-500">Enter the contestant's full name</p>
                    </div>

                    <div>
                      <label for="age" class="block text-sm font-medium text-gray-700 mb-1">
                        Age
                      </label>
                      <input
                        id="age"
                        v-model="form.age"
                        type="number"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200 focus:ring-opacity-50 transition-colors"
                        :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-200': errors.age }"
                        placeholder="e.g. 24"
                      />
                      <p v-if="errors.age" class="mt-1 text-sm text-red-500">{{ errors.age }}</p>
                      <p v-else class="mt-1 text-xs text-gray-500">Optional. Enter the contestant's age</p>
                    </div>

                    <div>
                      <label for="origin" class="block text-sm font-medium text-gray-700 mb-1">
                        Origin/Location
                      </label>
                      <input
                        id="origin"
                        v-model="form.origin"
                        type="text"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200 focus:ring-opacity-50 transition-colors"
                        :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-200': errors.origin }"
                        placeholder="e.g. New York, USA"
                      />
                      <p v-if="errors.origin" class="mt-1 text-sm text-red-500">{{ errors.origin }}</p>
                      <p v-else class="mt-1 text-xs text-gray-500">Optional. Enter the contestant's hometown or representation</p>
                    </div>
                  </div>

                  <!-- Right Column -->
                  <div class="space-y-6">
                    <div>
                      <label for="bio" class="block text-sm font-medium text-gray-700 mb-1">
                        Biography
                      </label>
                      <textarea
                        id="bio"
                        v-model="form.bio"
                        rows="4"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200 focus:ring-opacity-50 transition-colors"
                        :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-200': errors.bio }"
                        placeholder="Share the contestant's background, achievements, interests..."
                      ></textarea>
                      <p v-if="errors.bio" class="mt-1 text-sm text-red-500">{{ errors.bio }}</p>
                      <p v-else class="mt-1 text-xs text-gray-500">Optional. Add a brief biography for the contestant</p>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">
                        Contestant Photos
                      </label>
                      <div class="flex flex-col w-full">
                        <label
                          class="flex flex-col w-full h-32 border-2 border-dashed rounded-lg border-gray-300 hover:border-purple-400 hover:bg-purple-50 transition-colors cursor-pointer"
                        >
                          <div class="flex flex-col items-center justify-center pt-7">
                            <Camera class="w-8 h-8 text-purple-400 group-hover:text-purple-600" />
                            <p class="pt-1 text-sm tracking-wider text-gray-600 group-hover:text-gray-600">
                              {{ imageFiles.length > 0 ? `${imageFiles.length} file(s) selected` : 'Add photos' }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                              Drag & drop files here or click to browse
                            </p>
                          </div>
                          <input
                            type="file"
                            class="opacity-0 absolute"
                            multiple
                            accept="image/*"
                            @change="handleImagesChange"
                          />
                        </label>
                      </div>
                      <p v-if="errors.images" class="mt-1 text-sm text-red-500">{{ errors.images }}</p>
                      <p v-else class="mt-1 text-xs text-gray-500">Upload contestant photos. First photo will be the primary image</p>
                    </div>

                    <!-- Image Previews -->
                    <div v-if="imageFiles.length > 0 || imagePreviewUrls.length > 0">
                      <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-medium text-gray-700">Image Previews:</p>
                        <button
                          v-if="imagePreviewUrls.length > 0"
                          type="button"
                          @click="clearAllImages"
                          class="text-xs text-red-500 hover:text-red-700 transition-colors"
                        >
                          Clear all
                        </button>
                      </div>
                      <div class="grid grid-cols-3 gap-3">
                        <div 
                          v-for="(url, index) in imagePreviewUrls" 
                          :key="`preview-${index}`" 
                          class="relative group h-24 rounded-lg overflow-hidden border border-gray-200 shadow-sm"
                        >
                          <img :src="url" class="w-full h-full object-cover" />
                          <div 
                            class="absolute inset-0 bg-black/70 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center"
                          >
                            <button 
                              type="button" 
                              @click="removeImagePreview(index)" 
                              class="text-white hover:text-red-400 transition-colors"
                            >
                              <XCircle class="h-6 w-6" />
                            </button>
                          </div>
                          <!-- Primary indicator -->
                          <div v-if="index === 0" class="absolute top-1 right-1 bg-purple-600 rounded-full px-1.5 py-0.5 text-white text-2xs">
                            Primary
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="mt-8 pt-5 border-t border-gray-200 flex justify-end space-x-3">
                  <button
                    type="button"
                    @click="closeModal"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 rounded-lg shadow-sm hover:shadow transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
                    :disabled="isLoading"
                  >
                    <span v-if="isLoading" class="flex items-center">
                      <Loader2 class="h-4 w-4 mr-2 animate-spin" />
                      Processing...
                    </span>
                    <span v-else>
                      {{ contestant ? 'Save Changes' : 'Add Contestant' }}
                    </span>
                  </button>
                </div>
              </form>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, reactive, watch, computed, onMounted } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue'
import { XCircle, Camera, Loader2 } from 'lucide-vue-next'
import axios from 'axios'

const props = defineProps({
  show: {
    type: Boolean,
    required: true
  },
  pageantId: {
    type: Number,
    required: true
  },
  contestant: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'saved'])

const form = reactive({
  name: '',
  number: '',
  origin: '',
  age: '',
  bio: '',
})

const errors = reactive({})
const isLoading = ref(false)
const imageFiles = ref([])
const existingImages = ref([])
const imagePreviewUrls = ref([])

const resetForm = () => {
  form.name = ''
  form.number = ''
  form.origin = ''
  form.age = ''
  form.bio = ''
  Object.keys(errors).forEach(key => delete errors[key])
  imageFiles.value = []
  existingImages.value = []
  imagePreviewUrls.value = []
  isLoading.value = false
}

// Initialize form values when contestant prop changes
watch(() => props.contestant, (newValue) => {
  if (newValue) {
    form.name = newValue.name || ''
    form.number = newValue.number || ''
    form.origin = newValue.origin || ''
    form.age = newValue.age || ''
    form.bio = newValue.bio || ''
    
    // Reset image files but keep track of existing images
    imageFiles.value = []
    existingImages.value = newValue.images || []
    
    // Create preview URLs for existing images
    imagePreviewUrls.value = existingImages.value.map(img => img.path)
  } else {
    // Reset the form for new contestant
    resetForm()
  }
}, { immediate: true })

const handleImagesChange = (event) => {
  const files = Array.from(event.target.files)
  if (files.length === 0) return
  
  // Add to image files array
  imageFiles.value.push(...files)
  
  // Generate preview URLs for new files
  files.forEach(file => {
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreviewUrls.value.push(e.target.result)
    }
    reader.readAsDataURL(file)
  })
}

const removeImagePreview = (index) => {
  // If it's an existing image
  if (index < existingImages.value.length) {
    existingImages.value[index].toRemove = true
  }
  
  // Remove from preview URLs
  imagePreviewUrls.value.splice(index, 1)
  
  // If it's a new image file
  const newFilesIndex = index - existingImages.value.length
  if (newFilesIndex >= 0) {
    imageFiles.value.splice(newFilesIndex, 1)
  }
}

const clearAllImages = () => {
  // Mark all existing images for removal
  existingImages.value.forEach(img => {
    img.toRemove = true
  })
  
  // Clear preview URLs and new image files
  imagePreviewUrls.value = []
  imageFiles.value = []
}

const closeModal = () => {
  emit('close')
}

const handleSubmit = async () => {
  // Clear previous errors
  Object.keys(errors).forEach(key => delete errors[key])
  isLoading.value = true
  
  try {
    const formData = new FormData()
    formData.append('name', form.name)
    formData.append('number', form.number)
    
    if (form.origin) formData.append('origin', form.origin)
    if (form.age) formData.append('age', form.age)
    if (form.bio) formData.append('bio', form.bio)
    
    // Add images
    imageFiles.value.forEach(file => {
      formData.append('images[]', file)
    })
    
    // Add existing images marked for removal
    const removeImageIds = existingImages.value
      .filter(img => img.toRemove)
      .map(img => img.id)
    
    if (removeImageIds.length > 0) {
      removeImageIds.forEach((id, index) => {
        formData.append(`remove_image_ids[${index}]`, id)
      })
    }
    
    // Set primary image if available
    const remainingExistingImages = existingImages.value.filter(img => !img.toRemove)
    if (remainingExistingImages.length > 0) {
      formData.append('primary_image_id', remainingExistingImages[0].id)
    }
    
    let response
    
    if (props.contestant) {
      // Update existing contestant
      response = await axios.post(
        `/organizer/pageant/${props.pageantId}/contestants/${props.contestant.id}`, 
        formData,
        {
          headers: {
            'Content-Type': 'multipart/form-data',
            'X-HTTP-Method-Override': 'PUT'
          }
        }
      )
    } else {
      // Create new contestant
      response = await axios.post(
        `/organizer/pageant/${props.pageantId}/contestants`, 
        formData,
        {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        }
      )
    }
    
    if (response.data.success) {
      emit('saved', response.data.contestant)
      closeModal()
    }
  } catch (error) {
    console.error('Error submitting contestant:', error)
    
    if (error.response && error.response.data && error.response.data.errors) {
      // Set validation errors
      Object.assign(errors, error.response.data.errors)
    } else {
      // Set generic error
      errors.general = 'An error occurred while saving the contestant. Please try again.'
    }
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
.text-2xs {
  font-size: 0.625rem;
  line-height: 0.75rem;
}
</style> 