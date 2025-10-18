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
              <div class="relative bg-gradient-to-r from-orange-600 to-orange-500 p-6 text-white">
                <DialogTitle as="h3" class="text-2xl font-bold leading-6">
                  {{ contestant ? 'Edit Contestant' : (mode === 'pair' ? 'Add New Pair' : 'Add New Contestant') }}
                </DialogTitle>
                <p class="mt-2 text-purple-100 max-w-2xl">
                  {{ contestant ? 'Update contestant details and photos' : (mode === 'pair' ? 'Enter pair information and upload photos for both members' : 'Enter contestant information and upload photos') }}
                </p>
                <!-- Pageant Type Context -->
                <div v-if="pageant && pageant.contestant_type && !contestant" class="mt-3 p-3 bg-orange-500/30 rounded-lg border border-orange-400/30">
                  <p class="text-sm text-orange-100 font-medium mb-1">{{ getPageantTypeTitle() }}</p>
                  <p class="text-xs text-orange-200">{{ getPageantTypeDescription() }}</p>
                </div>
                <button 
                  @click="closeModal" 
                  class="absolute top-4 right-4 text-white hover:text-purple-200 transition-colors"
                >
                  <XCircle class="h-6 w-6" />
                </button>
              </div>

              <form @submit.prevent="handleSubmit" enctype="multipart/form-data" class="p-6">
                <!-- Individual/Edit Mode: Two-column layout -->
                <div v-if="mode === 'individual' || contestant" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Left Column -->
                  <div class="space-y-6">
                    <div>
                      <div class="flex items-center mb-1">
                        <label for="contestantNumber" class="block text-sm font-medium text-gray-700">
                          Contestant Number <span class="text-red-500">*</span>
                        </label>
                        <Tooltip text="This number will be used for identification during the pageant. Should be unique and easy to remember." position="top">
                          <HelpCircle class="h-4 w-4 text-gray-400 ml-1 hover:text-gray-600" />
                        </Tooltip>
                      </div>
                      <input
                        id="contestantNumber"
                        v-model="form.number"
                        type="number"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200 focus:ring-opacity-50 transition-colors hover:border-gray-400"
                        :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-200': errors.number }"
                        placeholder="e.g. 1"
                        required
                      />
                      <p v-if="errors.number" class="mt-1 text-sm text-red-500">{{ errors.number }}</p>
                      <p v-else class="mt-1 text-xs text-gray-500">Enter the contestant's competition number</p>
                    </div>

                    <div>
                      <div class="flex items-center mb-1">
                        <label for="name" class="block text-sm font-medium text-gray-700">
                          Full Name <span class="text-red-500">*</span>
                        </label>
                        <Tooltip text="Enter the contestant's complete legal name as it should appear in official documents and announcements." position="top">
                          <HelpCircle class="h-4 w-4 text-gray-400 ml-1 hover:text-gray-600" />
                        </Tooltip>
                      </div>
                      <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200 focus:ring-opacity-50 transition-colors hover:border-gray-400"
                        :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-200': errors.name }"
                        placeholder="e.g. Jane Smith"
                        required
                      />
                      <p v-if="errors.name" class="mt-1 text-sm text-red-500">{{ errors.name }}</p>
                      <p v-else class="mt-1 text-xs text-gray-500">Enter the contestant's full name</p>
                    </div>

                    <div>
                      <div class="flex items-center mb-1">
                        <label for="age" class="block text-sm font-medium text-gray-700">
                          Age
                        </label>
                        <Tooltip text="Age may be required for certain pageant categories and eligibility verification. Leave blank if not applicable." position="top">
                          <HelpCircle class="h-4 w-4 text-gray-400 ml-1 hover:text-gray-600" />
                        </Tooltip>
                      </div>
                      <input
                        id="age"
                        v-model="form.age"
                        type="number"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200 focus:ring-opacity-50 transition-colors hover:border-gray-400"
                        :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-200': errors.age }"
                        placeholder="e.g. 24"
                      />
                      <p v-if="errors.age" class="mt-1 text-sm text-red-500">{{ errors.age }}</p>
                      <p v-else class="mt-1 text-xs text-gray-500">Optional. Enter the contestant's age</p>
                    </div>

                    <div>
                      <div class="flex items-center mb-1">
                        <label for="origin" class="block text-sm font-medium text-gray-700">
                          Origin/Location
                        </label>
                        <Tooltip text="The city, state, or country the contestant represents. This will be displayed during introductions and announcements." position="top">
                          <HelpCircle class="h-4 w-4 text-gray-400 ml-1 hover:text-gray-600" />
                        </Tooltip>
                      </div>
                      <input
                        id="origin"
                        v-model="form.origin"
                        type="text"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200 focus:ring-opacity-50 transition-colors hover:border-gray-400"
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
                      <div class="flex items-center mb-1">
                        <label for="gender" class="block text-sm font-medium text-gray-700">
                          Gender <span class="text-red-500">*</span>
                        </label>
                        <Tooltip text="Select the contestant's gender. Used to enforce pairing rules (one male and one female)." position="top">
                          <HelpCircle class="h-4 w-4 text-gray-400 ml-1 hover:text-gray-600" />
                        </Tooltip>
                      </div>
                      <select
                        id="gender"
                        v-model="form.gender"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200 focus:ring-opacity-50 transition-colors hover:border-gray-400"
                        :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-200': errors.gender }"
                        required
                      >
                        <option value="" disabled>Select gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                      </select>
                      <p v-if="errors.gender" class="mt-1 text-sm text-red-500">{{ errors.gender }}</p>
                      <p v-else class="mt-1 text-xs text-gray-500">Required. Determines valid pair combinations.</p>
                    </div>

                    <div>
                      <div class="flex items-center mb-1">
                        <label for="bio" class="block text-sm font-medium text-gray-700">
                          Biography
                        </label>
                        <Tooltip text="A brief description of the contestant's background, achievements, interests, and what makes them unique. This may be read during the pageant." position="top">
                          <HelpCircle class="h-4 w-4 text-gray-400 ml-1 hover:text-gray-600" />
                        </Tooltip>
                      </div>
                      <textarea
                        id="bio"
                        v-model="form.bio"
                        rows="4"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200 focus:ring-opacity-50 transition-colors hover:border-gray-400"
                        :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-200': errors.bio }"
                        placeholder="Share the contestant's background, achievements, interests..."
                      ></textarea>
                      <p v-if="errors.bio" class="mt-1 text-sm text-red-500">{{ errors.bio }}</p>
                      <p v-else class="mt-1 text-xs text-gray-500">Optional. Add a brief biography for the contestant</p>
                    </div>

                    <div>
                      <div class="flex items-center mb-1">
                        <label class="block text-sm font-medium text-gray-700">
                          Contestant Photos
                        </label>
                        <Tooltip text="Upload high-quality photos of the contestant. The first photo will be used as the main profile image. Multiple photos will be displayed in a gallery view." position="top">
                          <HelpCircle class="h-4 w-4 text-gray-400 ml-1 hover:text-gray-600" />
                        </Tooltip>
                      </div>
                      <div class="flex flex-col w-full">
                        <Tooltip text="Click to browse or drag and drop multiple image files. Supported formats: JPG, PNG, GIF" position="bottom">
                          <label
                            class="flex flex-col w-full h-32 border-2 border-dashed rounded-lg border-gray-300 hover:border-purple-400 hover:bg-purple-50 transition-all cursor-pointer transform hover:scale-[1.02]"
                          >
                            <div class="flex flex-col items-center justify-center pt-7">
                              <Camera class="w-8 h-8 text-purple-400 group-hover:text-purple-600 transition-colors" />
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
                        </Tooltip>
                      </div>
                      <p v-if="errors.images" class="mt-1 text-sm text-red-500">{{ errors.images }}</p>
                      <p v-else class="mt-1 text-xs text-gray-500">Upload contestant photos. First photo will be the primary image</p>
                    </div>

                    <!-- Image Previews -->
                    <div v-if="imageFiles.length > 0 || imagePreviewUrls.length > 0">
                      <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-medium text-gray-700">Image Previews:</p>
                        <Tooltip text="Remove all uploaded photos" position="left">
                          <button
                            v-if="imagePreviewUrls.length > 0"
                            type="button"
                            @click="clearAllImages"
                            class="text-xs text-red-500 hover:text-red-700 transition-colors hover:underline"
                          >
                            Clear all
                          </button>
                        </Tooltip>
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
                            <Tooltip text="Remove this photo" position="top">
                              <button 
                                type="button" 
                                @click="removeImagePreview(index)" 
                                class="text-white hover:text-red-400 transition-all transform hover:scale-110"
                              >
                                <XCircle class="h-6 w-6" />
                              </button>
                            </Tooltip>
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

                <!-- Pair Creation Mode: Compact single-column layout -->
                <div v-if="mode === 'pair' && !contestant" class="space-y-5">
                  <!-- Pair Number -->
                  <div>
                    <div class="flex items-center mb-2">
                      <label for="pairNumber" class="block text-sm font-semibold text-gray-800">
                        Pair Number <span class="text-red-500">*</span>
                      </label>
                      <Tooltip text="This number will be shared by both members of the pair for identification during the pageant." position="top">
                        <HelpCircle class="h-4 w-4 text-gray-400 ml-1 hover:text-gray-600" />
                      </Tooltip>
                    </div>
                    <input
                      id="pairNumber"
                      v-model="form.number"
                      type="number"
                      class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 bg-white shadow-sm hover:border-orange-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-100 transition-all duration-200 outline-none text-gray-800 font-medium placeholder:text-gray-400 placeholder:font-normal"
                      :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-100': errors.number }"
                      placeholder="Enter pair number"
                      required
                    />
                    <p v-if="errors.number" class="mt-1.5 text-xs text-red-600 font-medium">{{ errors.number }}</p>
                    <p v-else class="mt-1.5 text-xs text-gray-500 font-medium">Both members will share this number</p>
                  </div>                  <!-- Member 1 - Compact -->
                  <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-5 border-2 border-blue-100 shadow-sm">
                    <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center pb-3 border-b border-blue-200">
                      <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center mr-3">
                        <Users class="h-4 w-4 text-white" />
                      </div>
                      <span>Member 1</span>
                      <span class="ml-auto text-xs font-medium text-blue-600 bg-blue-100 px-2 py-1 rounded-full">Primary</span>
                    </h4>
                    <div class="grid grid-cols-2 gap-4">
                      <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-2">
                          Full Name <span class="text-red-500">*</span>
                        </label>
                        <input
                          v-model="form.member1.name"
                          type="text"
                          class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 bg-white shadow-sm hover:border-orange-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-100 transition-all duration-200 outline-none text-gray-800 font-medium placeholder:text-gray-400 placeholder:font-normal"
                          :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-100': errors['member1.name'] }"
                          placeholder="Enter full name"
                          required
                        />
                        <p v-if="errors['member1.name']" class="mt-1.5 text-xs text-red-600 font-medium">{{ errors['member1.name'] }}</p>
                      </div>
                      
                      <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-2">
                          Gender <span class="text-red-500">*</span>
                        </label>
                        <select
                          v-model="form.member1.gender"
                          class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 bg-white shadow-sm hover:border-orange-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-100 transition-all duration-200 outline-none text-gray-800 font-medium cursor-pointer"
                          :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-100': errors['member1.gender'] }"
                          required
                        >
                          <option value="" disabled>Select gender</option>
                          <option value="male">♂ Male</option>
                          <option value="female">♀ Female</option>
                        </select>
                        <p v-if="errors['member1.gender']" class="mt-1.5 text-xs text-red-600 font-medium">{{ errors['member1.gender'] }}</p>
                      </div>

                      <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-2">
                          Age <span class="text-red-500">*</span>
                        </label>
                        <input
                          v-model="form.member1.age"
                          type="number"
                          class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 bg-white shadow-sm hover:border-orange-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-100 transition-all duration-200 outline-none text-gray-800 font-medium placeholder:text-gray-400 placeholder:font-normal"
                          :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-100': errors['member1.age'] }"
                          placeholder="Age"
                          min="16"
                          max="35"
                          required
                        />
                        <p v-if="errors['member1.age']" class="mt-1.5 text-xs text-red-600 font-medium">{{ errors['member1.age'] }}</p>
                      </div>

                      <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-2">
                          Origin/Location <span class="text-red-500">*</span>
                        </label>
                        <input
                          v-model="form.member1.origin"
                          type="text"
                          class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 bg-white shadow-sm hover:border-orange-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-100 transition-all duration-200 outline-none text-gray-800 font-medium placeholder:text-gray-400 placeholder:font-normal"
                          :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-100': errors['member1.origin'] }"
                          placeholder="City, Country"
                          required
                        />
                        <p v-if="errors['member1.origin']" class="mt-1.5 text-xs text-red-600 font-medium">{{ errors['member1.origin'] }}</p>
                      </div>

                      <div class="col-span-2">
                        <label class="block text-sm font-semibold text-gray-800 mb-2">Biography <span class="text-gray-400 font-normal text-xs">(Optional)</span></label>
                        <textarea
                          v-model="form.member1.bio"
                          rows="3"
                          class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 bg-white shadow-sm hover:border-orange-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-100 transition-all duration-200 outline-none text-gray-800 font-medium placeholder:text-gray-400 placeholder:font-normal resize-none"
                          placeholder="Share background, achievements, and interests..."
                        ></textarea>
                      </div>
                    </div>
                  </div>

                  <!-- Member 2 - Compact -->
                  <div class="bg-gradient-to-br from-pink-50 to-rose-50 rounded-2xl p-5 border-2 border-pink-100 shadow-sm">
                    <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center pb-3 border-b border-pink-200">
                      <div class="w-8 h-8 rounded-full bg-pink-500 flex items-center justify-center mr-3">
                        <Users class="h-4 w-4 text-white" />
                      </div>
                      <span>Member 2</span>
                      <span class="ml-auto text-xs font-medium text-pink-600 bg-pink-100 px-2 py-1 rounded-full">Secondary</span>
                    </h4>
                    <div class="grid grid-cols-2 gap-4">
                      <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-2">
                          Full Name <span class="text-red-500">*</span>
                        </label>
                        <input
                          v-model="form.member2.name"
                          type="text"
                          class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 bg-white shadow-sm hover:border-orange-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-100 transition-all duration-200 outline-none text-gray-800 font-medium placeholder:text-gray-400 placeholder:font-normal"
                          :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-100': errors['member2.name'] }"
                          placeholder="Enter full name"
                          required
                        />
                        <p v-if="errors['member2.name']" class="mt-1.5 text-xs text-red-600 font-medium">{{ errors['member2.name'] }}</p>
                      </div>
                      
                      <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-2">
                          Gender <span class="text-red-500">*</span>
                        </label>
                        <select
                          v-model="form.member2.gender"
                          class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 bg-white shadow-sm hover:border-orange-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-100 transition-all duration-200 outline-none text-gray-800 font-medium cursor-pointer"
                          :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-100': errors['member2.gender'] }"
                          required
                        >
                          <option value="" disabled>Select gender</option>
                          <option value="male">♂ Male</option>
                          <option value="female">♀ Female</option>
                        </select>
                        <p v-if="errors['member2.gender']" class="mt-1.5 text-xs text-red-600 font-medium">{{ errors['member2.gender'] }}</p>
                      </div>

                      <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-2">
                          Age <span class="text-red-500">*</span>
                        </label>
                        <input
                          v-model="form.member2.age"
                          type="number"
                          class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 bg-white shadow-sm hover:border-orange-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-100 transition-all duration-200 outline-none text-gray-800 font-medium placeholder:text-gray-400 placeholder:font-normal"
                          :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-100': errors['member2.age'] }"
                          placeholder="Age"
                          min="16"
                          max="35"
                          required
                        />
                        <p v-if="errors['member2.age']" class="mt-1.5 text-xs text-red-600 font-medium">{{ errors['member2.age'] }}</p>
                      </div>

                      <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-2">
                          Origin/Location <span class="text-red-500">*</span>
                        </label>
                        <input
                          v-model="form.member2.origin"
                          type="text"
                          class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 bg-white shadow-sm hover:border-orange-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-100 transition-all duration-200 outline-none text-gray-800 font-medium placeholder:text-gray-400 placeholder:font-normal"
                          :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-100': errors['member2.origin'] }"
                          placeholder="City, Country"
                          required
                        />
                        <p v-if="errors['member2.origin']" class="mt-1.5 text-xs text-red-600 font-medium">{{ errors['member2.origin'] }}</p>
                      </div>

                      <div class="col-span-2">
                        <label class="block text-sm font-semibold text-gray-800 mb-2">Biography <span class="text-gray-400 font-normal text-xs">(Optional)</span></label>
                        <textarea
                          v-model="form.member2.bio"
                          rows="3"
                          class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 bg-white shadow-sm hover:border-orange-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-100 transition-all duration-200 outline-none text-gray-800 font-medium placeholder:text-gray-400 placeholder:font-normal resize-none"
                          placeholder="Share background, achievements, and interests..."
                        ></textarea>
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
                    class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-orange-600 to-orange-500 hover:from-orange-700 hover:to-orange-600 rounded-lg shadow-sm hover:shadow transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
                    :disabled="isLoading"
                  >
                    <span v-if="isLoading" class="flex items-center">
                      <Loader2 class="h-4 w-4 mr-2 animate-spin" />
                      Processing...
                    </span>
                    <span v-else>
                      {{ contestant ? 'Save Changes' : (mode === 'pair' ? 'Create Pair' : 'Add Contestant') }}
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
import { XCircle, Camera, Loader2, HelpCircle, Users } from 'lucide-vue-next'
import Tooltip from '@/Components/Tooltip.vue'
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
  pageant: {
    type: Object,
    default: null
  },
  contestant: {
    type: Object,
    default: null
  },
  mode: {
    type: String,
    default: 'individual', // 'individual' or 'pair'
    validator: value => ['individual', 'pair'].includes(value)
  }
})

const emit = defineEmits(['close', 'saved'])

const form = reactive({
  name: '',
  number: '',
  gender: '',
  origin: '',
  age: '',
  bio: '',
  // Pair-specific fields
  member1: {
    name: '',
    gender: '',
    age: '',
    origin: '',
    bio: '',
  },
  member2: {
    name: '',
    gender: '',
    age: '',
    origin: '',
    bio: '',
  }
})

const errors = reactive({})
const isLoading = ref(false)
const imageFiles = ref([])
const existingImages = ref([])
const imagePreviewUrls = ref([])

const resetForm = () => {
  form.name = ''
  form.number = ''
  form.gender = ''
  form.origin = ''
  form.age = ''
  form.bio = ''
  form.member1 = {
    name: '',
    gender: '',
    age: '',
    origin: '',
    bio: '',
  }
  form.member2 = {
    name: '',
    gender: '',
    age: '',
    origin: '',
    bio: '',
  }
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
    form.gender = newValue.gender || ''
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

const handleSubmit = async () => {
  // Clear previous errors
  Object.keys(errors).forEach(key => delete errors[key])
  isLoading.value = true
  
  try {
    const formData = new FormData()
    
    if (props.mode === 'pair' && !props.contestant) {
      // Client-side validation for pairs
      if (form.member1.gender === form.member2.gender) {
        errors['member2.gender'] = 'Pair members must have different genders'
        isLoading.value = false
        return
      }
      
      // Validate required fields for both members
      if (!form.member1.name || !form.member1.gender) {
        errors['member1.name'] = !form.member1.name ? 'Member 1 name is required' : ''
        errors['member1.gender'] = !form.member1.gender ? 'Member 1 gender is required' : ''
        isLoading.value = false
        return
      }
      
      if (!form.member2.name || !form.member2.gender) {
        errors['member2.name'] = !form.member2.name ? 'Member 2 name is required' : ''
        errors['member2.gender'] = !form.member2.gender ? 'Member 2 gender is required' : ''
        isLoading.value = false
        return
      }
      
      // For pair creation, we need to create individual contestants first, then create the pair
      // This is a two-step process to work with the existing backend
      
      try {
        // Step 1: Create member 1
        const member1FormData = new FormData()
        member1FormData.append('name', form.member1.name)
        member1FormData.append('number', form.number)
        member1FormData.append('gender', form.member1.gender)
        member1FormData.append('age', form.member1.age || '18')
        member1FormData.append('origin', form.member1.origin || 'Not specified')
        if (form.member1.bio) member1FormData.append('bio', form.member1.bio)
        
        const member1Response = await axios.post(
          `/organizer/pageant/${props.pageantId}/contestants`, 
          member1FormData,
          { headers: { 'Content-Type': 'multipart/form-data' } }
        )
        
        if (!member1Response.data.success) {
          throw new Error('Failed to create first member')
        }
        
        // Step 2: Create member 2
        const member2FormData = new FormData()
        member2FormData.append('name', form.member2.name)
        member2FormData.append('number', form.number)
        member2FormData.append('gender', form.member2.gender)
        member2FormData.append('age', form.member2.age || '18')
        member2FormData.append('origin', form.member2.origin || 'Not specified')
        if (form.member2.bio) member2FormData.append('bio', form.member2.bio)
        
        const member2Response = await axios.post(
          `/organizer/pageant/${props.pageantId}/contestants`, 
          member2FormData,
          { headers: { 'Content-Type': 'multipart/form-data' } }
        )
        
        if (!member2Response.data.success) {
          throw new Error('Failed to create second member')
        }
        
        // Step 3: Link the pair using the new pair endpoint
        const pairData = {
          member_ids: [member1Response.data.contestant.id, member2Response.data.contestant.id]
        }
        
        const pairResponse = await axios.post(
          `/organizer/pageant/${props.pageantId}/pairs`, 
          pairData
        )
        
        if (pairResponse.data.success) {
          // Emit both members as saved
          emit('saved', pairResponse.data.members[0])
          emit('saved', pairResponse.data.members[1])
          closeModal()
        } else {
          errors.general = 'Failed to link pair. Please try again.'
        }
        
      } catch (error) {
        console.error('Error creating pair:', error)
        if (error.response && error.response.data && error.response.data.errors) {
          Object.assign(errors, error.response.data.errors)
        } else if (error.response && error.response.data && error.response.data.message) {
          errors.general = error.response.data.message
        } else {
          errors.general = 'An error occurred while creating the pair. Please try again.'
        }
      } finally {
        isLoading.value = false
      }
      return
      
    } else {
      // Individual contestant submission
      formData.append('name', form.name)
      formData.append('number', form.number)
      formData.append('gender', form.gender)
      
      if (form.origin) formData.append('origin', form.origin)
      if (form.age) formData.append('age', form.age)
      if (form.bio) formData.append('bio', form.bio)
    }
    
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
      // Create new individual contestant
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
    
    if (response && response.data.success) {
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

const closeModal = () => {
  resetForm()
  emit('close')
}

// Helper functions for pageant type context
const getPageantTypeTitle = () => {
  if (!props.pageant || !props.pageant.contestant_type) return ''
  
  switch (props.pageant.contestant_type) {
    case 'solo':
      return 'Solo Competition'
    case 'pairs':
      return 'Pairs Competition - Individual Entry'
    case 'both':
      return 'Mixed Competition'
    default:
      return ''
  }
}

const getPageantTypeDescription = () => {
  if (!props.pageant || !props.pageant.contestant_type) return ''
  
  switch (props.pageant.contestant_type) {
    case 'solo':
      return 'This contestant will compete individually in this solo-only pageant.'
    case 'pairs':
      return 'This individual will be available to create pairs. You\'ll need to create pairs from individuals to compete in this pairs-only pageant.'
    case 'both':
      return 'This contestant can compete individually or be part of a pair in this mixed pageant.'
    default:
      return ''
  }
}
</script>

<style scoped>
.text-2xs {
  font-size: 0.625rem;
  line-height: 0.75rem;
}
</style> 