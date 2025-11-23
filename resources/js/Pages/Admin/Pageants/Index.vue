<template>
  <AdminLayout>
    <div class="container mx-auto pb-4 sm:pb-8">
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-4 sm:mb-6">
        <div class="px-4 sm:px-6 py-4 sm:py-5 border-b border-gray-200">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h1 class="text-xl sm:text-2xl font-semibold text-gray-800">Pageant Management</h1>
            <Link
              href="/admin/pageants/create"
              class="bg-teal-600 hover:bg-teal-700 text-white px-3 py-1.5 sm:px-4 sm:py-2 rounded-md text-xs sm:text-sm font-medium inline-flex items-center transition-colors duration-150"
            >
              <span class="mr-1 sm:mr-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="12" y1="5" x2="12" y2="19"></line>
                  <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
              </span>
              Create New Pageant
            </Link>
          </div>
        </div>

        <!-- Status filter tabs - scrollable on mobile -->
        <div class="px-4 sm:px-6 pt-3 sm:pt-4 border-b border-gray-200 overflow-x-auto">
          <div class="flex items-center -mb-px space-x-4 sm:space-x-6 min-w-max">
            <button 
              v-for="filter in statusFilters" 
              :key="filter.value"
              @click="currentStatusFilter = filter.value"
              class="pb-3 sm:pb-4 px-1 inline-flex items-center border-b-2 font-medium text-xs sm:text-sm transition-colors duration-200"
              :class="[
                currentStatusFilter === filter.value 
                  ? 'border-teal-500 text-teal-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              <component 
                :is="filter.icon" 
                class="h-4 w-4 sm:h-5 sm:w-5 mr-1 sm:mr-2"
                :class="currentStatusFilter === filter.value ? 'text-teal-500' : 'text-gray-400'"
              />
              {{ filter.label }}
              <span 
                class="ml-1 sm:ml-2 px-1.5 sm:px-2 py-0.5 text-2xs sm:text-xs rounded-full"
                :class="currentStatusFilter === filter.value ? 'bg-teal-100 text-teal-800' : 'bg-gray-100 text-gray-700'"
              >
                {{ getPageantCount(filter.value) }}
              </span>
            </button>
          </div>
        </div>

        <!-- Additional filters -->
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 bg-gray-50">
          <div class="flex flex-col sm:flex-row sm:items-center space-y-3 sm:space-y-0">
            <div class="sm:hidden w-full">
              <button 
                @click="showFilters = !showFilters" 
                class="w-full flex items-center justify-between px-3 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50"
              >
                <span class="flex items-center">
                  <component :is="filterIcon" class="h-4 w-4 mr-2 text-gray-500" />
                  Filters & Sorting
                </span>
                <component :is="chevronIcon" class="h-4 w-4 text-gray-500 transition-transform" :class="{'transform rotate-180': showFilters}" />
              </button>
            </div>

            <div :class="['space-y-3 sm:space-y-0 sm:flex sm:flex-wrap sm:items-center gap-2 sm:gap-4', showFilters ? 'block mt-3' : 'hidden sm:flex']">
              <!-- Date filter -->
              <div class="flex items-center space-x-2">
                <label class="text-xs sm:text-sm font-medium text-gray-700">Date:</label>
                <div class="min-w-[120px]">
                  <CustomSelect
                    v-model="dateFilter"
                    :options="dateFilterOptions"
                    variant="teal"
                    placeholder="Select Date"
                  />
                </div>
              </div>

              <!-- Organizer filter -->
              <div class="flex items-center space-x-2">
                <label class="text-xs sm:text-sm font-medium text-gray-700">Organizer:</label>
                <div class="min-w-[140px]">
                  <CustomSelect
                    v-model="organizerFilter"
                    :options="organizerFilterOptions"
                    variant="teal"
                    placeholder="Select Organizer"
                  />
                </div>
              </div>

              <!-- Sort by -->
              <div class="flex items-center space-x-2">
                <label class="text-xs sm:text-sm font-medium text-gray-700">Sort By:</label>
                <div class="min-w-[130px]">
                  <CustomSelect
                    v-model="sortBy"
                    :options="sortByOptions"
                    variant="teal"
                    placeholder="Sort By"
                  />
                </div>
              </div>

              <!-- Sort direction -->
              <div class="flex items-center space-x-2">
                <label class="text-xs sm:text-sm font-medium text-gray-700">Order:</label>
                <div class="min-w-[120px]">
                  <CustomSelect
                    v-model="sortDirection"
                    :options="sortDirectionOptions"
                    variant="teal"
                    placeholder="Select Order"
                  />
                </div>
              </div>
            </div>

            <!-- Search -->
            <div class="flex items-center space-x-2 sm:ml-auto">
              <div class="relative flex-grow">
                <input 
                  v-model="searchQuery"
                  type="text" 
                  placeholder="Search pageants..." 
                  class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 text-xs sm:text-sm pl-4 pr-10"
                />
                <component :is="searchIcon" class="absolute right-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" />
              </div>
              <button 
                @click="resetFilters"
                class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500"
              >
                Reset
              </button>
            </div>
          </div>
        </div>

        <!-- Mobile view: Card listing -->
        <div class="sm:hidden">
          <div class="px-4 py-3 space-y-3">
            <div v-if="paginatedPageants.length === 0" class="text-center py-8">
              <div class="flex flex-col items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10"></circle>
                  <path d="M16 16s-1.5-2-4-2-4 2-4 2"></path>
                  <line x1="9" y1="9" x2="9.01" y2="9"></line>
                  <line x1="15" y1="9" x2="15.01" y2="9"></line>
                </svg>
                <p class="text-base font-medium">No pageants found</p>
                <p class="text-sm">Try adjusting your search or filter criteria</p>
              </div>
            </div>

            <div 
              v-for="pageant in paginatedPageants" 
              :key="pageant.id"
              @click="router.visit(route('admin.pageants.detail', pageant.id))"
              class="bg-white border border-gray-200 rounded-lg p-3 shadow-sm hover:border-teal-300 hover:shadow-md transition-all cursor-pointer relative"
            >
              <div class="flex items-start space-x-3">
                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gradient-to-r from-teal-500 to-teal-600 flex items-center justify-center text-white font-bold">
                  {{ pageant.name.charAt(0) }}
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ pageant.name }}</p>
                    <span class="px-2 py-0.5 inline-flex text-2xs leading-5 font-semibold rounded-full" :class="getStatusClass(pageant.status)">
                      {{ pageant.status }}
                    </span>
                  </div>
                  <p v-if="pageant.location" class="text-xs text-gray-500 truncate mt-0.5">{{ pageant.location }}</p>
                  
                  <div class="mt-2 space-y-1.5">
                    <div class="flex items-center text-xs text-gray-500">
                      <Calendar class="h-3 w-3 mr-1.5 text-gray-400 flex-shrink-0" />
                      <span>{{ pageant.start_date || 'Not scheduled yet' }}</span>
                    </div>
                    
                    <div class="flex items-center text-xs text-gray-500">
                      <Users class="h-3 w-3 mr-1.5 text-gray-400 flex-shrink-0" />
                      <span v-if="pageant.organizers && pageant.organizers.length > 0" class="truncate">
                        {{ pageant.organizers.map(o => o.name).join(', ') }}
                      </span>
                      <span v-else class="text-gray-400">No organizers assigned</span>
                    </div>
                    
                    <div class="flex items-center text-xs text-gray-500">
                      <Clock class="h-3 w-3 mr-1.5 text-gray-400 flex-shrink-0" />
                      <span>Created: {{ formatDate(pageant.created_at) }}</span>
                    </div>
                  </div>
                  
                  <div class="mt-3 flex items-center justify-end space-x-2">
                    <!-- Approve/Reject buttons for pending pageants on mobile -->
                    <template v-if="pageant.status === 'Pending_Approval'">
                      <button
                        @click.stop="approvePageant(pageant)"
                        :disabled="processingPageants.includes(pageant.id)"
                        class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                      >
                        <template v-if="processingPageants.includes(pageant.id) && lastAction === 'approve' && lastActionPageant === pageant.id">
                          <div class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin mr-1"></div>
                          Approving
                        </template>
                        <template v-else>
                          <CheckCircle class="h-3 w-3 mr-1" />
                          Approve
                        </template>
                      </button>
                      
                      <button
                        @click.stop="rejectPageant(pageant)"
                        :disabled="processingPageants.includes(pageant.id)"
                        class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                      >
                        <template v-if="processingPageants.includes(pageant.id) && lastAction === 'reject' && lastActionPageant === pageant.id">
                          <div class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin mr-1"></div>
                          Rejecting
                        </template>
                        <template v-else>
                          <XCircle class="h-3 w-3 mr-1" />
                          Reject
                        </template>
                      </button>
                    </template>
                    
                    <Link 
                      :href="route('admin.pageants.detail', pageant.id)"
                      class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded text-teal-700 bg-teal-100 hover:bg-teal-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition-colors"
                      @click.stop
                    >
                      <Eye class="h-3 w-3 mr-1" />
                      View Details
                    </Link>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Desktop view: Table -->
        <div class="hidden sm:block overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 table-fixed">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/5">
                  Pageant Name
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[10%]">
                  Status
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[10%]">
                  Start Date
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[10%]">
                  End Date
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[15%]">
                  Organizer(s)
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[10%]">
                  Created
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[15%]">
                  Edit Permission
                </th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-[10%]">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="paginatedPageants.length === 0">
                <td colspan="8" class="px-6 py-10 text-center text-gray-500">
                  <div class="flex flex-col items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                      <circle cx="12" cy="12" r="10"></circle>
                      <path d="M16 16s-1.5-2-4-2-4 2-4 2"></path>
                      <line x1="9" y1="9" x2="9.01" y2="9"></line>
                      <line x1="15" y1="9" x2="15.01" y2="9"></line>
                    </svg>
                    <p class="text-lg font-medium">No pageants found</p>
                    <p class="text-sm">Try adjusting your search or filter criteria</p>
                  </div>
                </td>
              </tr>
              <tr 
                v-for="pageant in paginatedPageants" 
                :key="pageant.id"
                @click="router.visit(route('admin.pageants.detail', pageant.id))"
                class="hover:bg-gray-50 transition-colors duration-150 cursor-pointer relative group border-l-4 border-transparent hover:border-l-4 hover:border-teal-500"
                title="Click to view pageant details"
              >
                <td class="px-6 py-4 relative">
                  <!-- Clickable indicator -->
                  <div class="absolute -left-3 top-1/2 transform -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                    <div class="bg-teal-500 text-white rounded-full p-1 shadow-md">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                      </svg>
                    </div>
                  </div>
                  <div class="text-sm font-medium text-gray-800 group-hover:text-teal-700 transition-colors duration-150 truncate">{{ pageant.name }}</div>
                  <div v-if="pageant.location" class="text-xs text-gray-500 truncate">{{ pageant.location }}</div>
                </td>
                <td class="px-4 py-4">
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusClass(pageant.status)">
                    {{ pageant.status }}
                  </span>
                </td>
                <td class="px-4 py-4 text-sm text-gray-500 truncate">
                  {{ pageant.start_date || 'Not set' }}
                </td>
                <td class="px-4 py-4 text-sm text-gray-500 truncate">
                  {{ pageant.end_date || 'Not set' }}
                </td>
                <td class="px-4 py-4 text-sm text-gray-500">
                  <div v-if="pageant.organizers && pageant.organizers.length > 0" class="flex flex-wrap">
                    <span 
                      v-for="(organizer, index) in pageant.organizers" 
                      :key="organizer.id"
                      class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 mr-1 mb-1 truncate max-w-[120px]"
                      :title="organizer.name"
                    >
                      {{ organizer.name }}
                    </span>
                  </div>
                  <span v-else class="text-gray-400">No organizers assigned</span>
                </td>
                <td class="px-4 py-4 text-sm text-gray-500 truncate">
                  {{ formatDate(pageant.created_at) }}
                </td>
                <td class="px-4 py-4 text-sm">
                  <div v-if="pageant.status === 'Completed'" class="text-gray-600">
                    <span class="inline-flex items-center">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-gray-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                      </svg>
                      <span class="truncate">Locked</span>
                    </span>
                  </div>
                  <div v-else-if="pageant.status === 'Unlocked_For_Edit'" class="text-green-600">
                    <span class="inline-flex items-center">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-green-500 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                      </svg>
                      <div class="flex flex-col min-w-0">
                        <div class="flex items-center truncate">
                          <span class="truncate">Granted to </span>
                          <span class="font-medium ml-1 truncate">{{ pageant.edit_permission_granted_to?.name || 'Editor' }}</span>
                        </div>
                        <div v-if="pageant.edit_permission_expires_at" class="flex items-center text-xs text-gray-500 truncate">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                          </svg>
                          <span class="truncate">Expires: {{ formatExpiryDate(pageant.edit_permission_expires_at) }}</span>
                        </div>
                      </div>
                    </span>
                  </div>
                  <div v-else class="text-gray-400 text-xs italic">
                    Not applicable
                  </div>
                </td>
                <td class="px-4 py-4 text-sm text-right whitespace-nowrap">
                  <div class="inline-flex items-center space-x-2">
                    <!-- Approve/Reject buttons for pending pageants -->
                    <template v-if="pageant.status === 'Pending_Approval'">
                      <button
                        @click.stop="approvePageant(pageant)"
                        :disabled="processingPageants.includes(pageant.id)"
                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                        title="Approve Pageant"
                      >
                        <template v-if="processingPageants.includes(pageant.id) && lastAction === 'approve' && lastActionPageant === pageant.id">
                          <div class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin mr-1"></div>
                          Approving
                        </template>
                        <template v-else>
                          <CheckCircle class="h-3 w-3 mr-1" />
                          Approve
                        </template>
                      </button>
                      
                      <button
                        @click.stop="rejectPageant(pageant)"
                        :disabled="processingPageants.includes(pageant.id)"
                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                        title="Reject Pageant"
                      >
                        <template v-if="processingPageants.includes(pageant.id) && lastAction === 'reject' && lastActionPageant === pageant.id">
                          <div class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin mr-1"></div>
                          Rejecting
                        </template>
                        <template v-else>
                          <XCircle class="h-3 w-3 mr-1" />
                          Reject
                        </template>
                      </button>
                    </template>
                    
                    <Link 
                      :href="route('admin.pageants.detail', pageant.id)"
                      class="text-teal-600 hover:text-teal-900"
                      @click.stop
                    >
                      <Eye class="h-4 w-4" />
                    </Link>

                    <button
                      @click.stop="confirmArchive(pageant)"
                      class="text-gray-500 hover:text-gray-700"
                      title="Archive Pageant"
                    >
                      <Archive class="h-4 w-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination controls -->
      <div class="mt-5 flex justify-between items-center">
        <div class="text-xs sm:text-sm text-gray-700">
          Showing 
          <span class="font-medium">{{ paginationStart }}</span> 
          to 
          <span class="font-medium">{{ paginationEnd }}</span> 
          of 
          <span class="font-medium">{{ filteredPageants.length }}</span> 
          pageants
        </div>
        
        <div class="flex justify-end space-x-1 sm:space-x-2">
          <button 
            @click="currentPage--" 
            :disabled="currentPage <= 1"
            class="inline-flex items-center px-2 sm:px-3 py-1 sm:py-2 border border-gray-300 text-xs sm:text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <ChevronLeft class="h-3 w-3 sm:h-4 sm:w-4 mr-1" />
            Previous
          </button>
          
          <div class="hidden sm:flex space-x-2">
            <button 
              v-for="page in visiblePageNumbers" 
              :key="page"
              @click="currentPage = page"
              :class="[
                'inline-flex items-center px-3 py-2 border text-sm font-medium rounded-md',
                currentPage === page 
                  ? 'bg-teal-50 border-teal-500 text-teal-600 z-10' 
                  : 'border-gray-300 text-gray-700 hover:bg-gray-50'
              ]"
            >
              {{ page }}
            </button>
          </div>
          
          <div class="sm:hidden flex items-center">
            <span class="text-xs font-medium text-gray-700">{{ currentPage }} / {{ totalPages }}</span>
          </div>
          
          <button 
            @click="currentPage++"
            :disabled="currentPage >= totalPages"
            class="inline-flex items-center px-2 sm:px-3 py-1 sm:py-2 border border-gray-300 text-xs sm:text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Next
            <ChevronRight class="h-3 w-3 sm:h-4 sm:w-4 ml-1" />
          </button>
        </div>
      </div>
    </div>

    <!-- Confirmation Modal -->
    <Modal :show="showConfirmModal" @close="closeConfirmModal">
      <div class="p-6 bg-white rounded-lg shadow-md">
        <div class="flex items-center space-x-3 mb-4">
          <div :class="[
            'w-12 h-12 rounded-full flex items-center justify-center',
            confirmAction === 'approve' ? 'bg-green-100' : (confirmAction === 'archive' ? 'bg-gray-100' : 'bg-red-100')
          ]">
            <CheckCircle v-if="confirmAction === 'approve'" class="h-6 w-6 text-green-600" />
            <Archive v-else-if="confirmAction === 'archive'" class="h-6 w-6 text-gray-600" />
            <XCircle v-else class="h-6 w-6 text-red-600" />
          </div>
          <div>
            <h3 class="text-lg font-medium text-gray-900">
              {{ confirmAction === 'approve' ? 'Approve Pageant' : (confirmAction === 'archive' ? 'Archive Pageant' : 'Reject Pageant') }}
            </h3>
            <p class="text-sm text-gray-500">{{ selectedPageant?.name }}</p>
          </div>
        </div>
        
        <p class="text-gray-700 mb-6">
          <template v-if="confirmAction === 'approve'">
            Are you sure you want to approve this pageant? The organizer will be able to start managing contestants, criteria, and other pageant details.
          </template>
          <template v-else-if="confirmAction === 'reject'">
            Are you sure you want to reject this pageant? This action cannot be undone and the organizer will need to resubmit.
          </template>
          <template v-else-if="confirmAction === 'archive'">
            Are you sure you want to archive this pageant? It will be moved to the archived list.
            <div class="mt-4">
              <label for="admin-archive-reason" class="block text-sm font-medium text-gray-700">
                Reason (optional)
              </label>
              <div class="mt-1">
                <textarea
                  id="admin-archive-reason"
                  v-model="archiveReason"
                  rows="3"
                  class="shadow-sm focus:ring-teal-500 focus:border-teal-500 block w-full sm:text-sm border-gray-300 rounded-md"
                  placeholder="Enter a reason..."
                ></textarea>
              </div>
            </div>
          </template>
        </p>
        
        <div class="flex justify-end space-x-3">
          <button
            @click="closeConfirmModal"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
          >
            Cancel
          </button>
          <button
            @click="confirmActionHandler"
            :disabled="isProcessing"
            :class="[
              'px-4 py-2 text-sm font-medium text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed',
              confirmAction === 'approve' 
                ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500' 
                : (confirmAction === 'archive' 
                    ? 'bg-gray-600 hover:bg-gray-700 focus:ring-gray-500'
                    : 'bg-red-600 hover:bg-red-700 focus:ring-red-500')
            ]"
          >
            <template v-if="isProcessing">
              <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2 inline-block"></div>
              Processing...
            </template>
            <template v-else>
              {{ confirmAction === 'approve' ? 'Approve' : (confirmAction === 'archive' ? 'Archive' : 'Reject') }}
            </template>
          </button>
        </div>
      </div>
    </Modal>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
  Activity, 
  Archive, 
  Award, 
  Calendar, 
  Clock, 
  Edit3, 
  FileCheck,
  Users,
  Lock,
  Unlock,
  Ban,
  Search,
  Filter,
  ChevronDown,
  ChevronLeft, 
  ChevronRight,
  Eye,
  CheckCircle,
  XCircle
} from 'lucide-vue-next';
import { useNotification } from '@/Composables/useNotification';
import CustomSelect from '@/Components/CustomSelect.vue';
import Modal from '@/Components/Modal.vue';

// Define props from controller
const props = defineProps({
  pageants: {
    type: Array,
    required: true
  },
  organizers: {
    type: Array,
    default: () => []
  },
  pageantCounts: {
    type: Object,
    default: () => ({
      draft: 0,
      setup: 0,
      active: 0,
      completed: 0,
      unlocked_for_edit: 0,
      archived: 0,
      cancelled: 0,
      total: 0
    })
  }
});

// Navigation icons
import { 
  LayoutList, 
  ClipboardCheck, 
  AlertTriangle
} from 'lucide-vue-next';

// Filters state
const searchQuery = ref('');
const currentStatusFilter = ref('all');
const dateFilter = ref('all');
const organizerFilter = ref('');
const sortBy = ref('created_at');
const sortDirection = ref('desc');
const currentPage = ref(1);
const itemsPerPage = ref(10);

// Approve/Reject state management
const showConfirmModal = ref(false);
const confirmAction = ref('');
const selectedPageant = ref(null);
const isProcessing = ref(false);
const processingPageants = ref([]);
const lastAction = ref('');
const lastActionPageant = ref(null);
const archiveReason = ref('');

const confirmArchive = (pageant) => {
  selectedPageant.value = pageant;
  confirmAction.value = 'archive';
  archiveReason.value = ''; // Reset reason
  showConfirmModal.value = true;
};

// Filter options for CustomSelect components
const dateFilterOptions = [
  { value: 'all', label: 'All Time' },
  { value: 'today', label: 'Today' },
  { value: 'week', label: 'This Week' },
  { value: 'month', label: 'This Month' },
  { value: 'year', label: 'This Year' }
];

const organizerFilterOptions = computed(() => [
  { value: '', label: 'All Organizers' },
  ...props.organizers.map(organizer => ({
    value: organizer.id,
    label: organizer.name
  }))
]);

const sortByOptions = [
  { value: 'name', label: 'Name' },
  { value: 'created_at', label: 'Date Created' },
  { value: 'start_date', label: 'Start Date' },
  { value: 'end_date', label: 'End Date' }
];

const sortDirectionOptions = [
  { value: 'asc', label: 'Ascending' },
  { value: 'desc', label: 'Descending' }
];

// Status filters
const statusFilters = [
  { label: 'All Pageants', value: 'all', icon: LayoutList },
  { label: 'Pending Approval', value: 'pending_approval', icon: Clock },
  { label: 'Draft & Setup', value: 'setup', icon: ClipboardCheck },
  { label: 'Active', value: 'active', icon: Calendar },
  { label: 'Completed', value: 'completed', icon: Award },
  { label: 'Archived', value: 'archived', icon: Archive },
  { label: 'Cancelled', value: 'cancelled', icon: AlertTriangle }
];

// Reset all filters
const resetFilters = () => {
  searchQuery.value = '';
  currentStatusFilter.value = 'all';
  dateFilter.value = 'all';
  organizerFilter.value = '';
  sortBy.value = 'created_at';
  sortDirection.value = 'desc';
  currentPage.value = 1;
};

// Utility for status class
const getStatusClass = (status) => {
  switch (status) {
    case 'Draft':
      return 'bg-purple-100 text-purple-800';
    case 'Setup':
      return 'bg-blue-100 text-blue-800';
    case 'Active':
      return 'bg-green-100 text-green-800';
    case 'Completed':
      return 'bg-teal-100 text-teal-800';
    case 'Unlocked_For_Edit':
      return 'bg-yellow-100 text-yellow-800';
    case 'Archived':
      return 'bg-gray-100 text-gray-800';
    case 'Cancelled':
      return 'bg-red-100 text-red-800';
    case 'Pending_Approval':
      return 'bg-orange-100 text-orange-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
};

// Pageant count helper
const getPageantCount = (statusFilter) => {
  if (statusFilter === 'all') {
    return props.pageantCounts.total;
  }
  
  if (statusFilter === 'setup') {
    return props.pageantCounts.draft + props.pageantCounts.setup;
  }
  
  if (statusFilter === 'pending_approval') {
    return props.pageants.filter(p => p.status === 'Pending_Approval').length;
  }
  
  return props.pageantCounts[statusFilter] || 0;
};

// Get the right detail URL based on pageant status
const getPageantDetailUrl = (pageant) => {
  if (['Draft', 'Setup', 'Active'].includes(pageant.status)) {
    return `/admin/pageants/${pageant.id}`;
  } else if (['Completed', 'Unlocked_For_Edit'].includes(pageant.status)) {
    return `/admin/pageants/previous/${pageant.id}`;
  } else {
    return `/admin/pageants/archived/${pageant.id}`;
  }
};

// Format date helper
const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

// Add this helper function to the script section
const formatExpiryDate = (dateString) => {
  if (!dateString) return '';
  
  // Parse the date string
  const date = new Date(dateString);
  
  // Format as MM/DD/YYYY
  return `${(date.getMonth() + 1).toString().padStart(2, '0')}/${date.getDate().toString().padStart(2, '0')}/${date.getFullYear()}`;
};

// Filter pageants
const filteredPageants = computed(() => {
  let result = [...props.pageants];
  
  // Filter by status
  if (currentStatusFilter.value !== 'all') {
    if (currentStatusFilter.value === 'setup') {
      result = result.filter(p => p.status === 'Draft' || p.status === 'Setup');
    } else if (currentStatusFilter.value === 'pending_approval') {
      result = result.filter(p => p.status === 'Pending_Approval');
    } else {
      result = result.filter(p => p.status.toLowerCase() === currentStatusFilter.value);
    }
  }
  
  // Filter by organizer
  if (organizerFilter.value) {
    result = result.filter(p => 
      p.organizers && p.organizers.some(o => o.id === parseInt(organizerFilter.value))
    );
  }
  
  // Filter by date
  if (dateFilter.value !== 'all') {
    const now = new Date();
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const startOfWeek = new Date(today);
    startOfWeek.setDate(today.getDate() - today.getDay());
    const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);
    const startOfYear = new Date(now.getFullYear(), 0, 1);
    
    result = result.filter(p => {
      const createdDate = new Date(p.created_at);
      
      if (dateFilter.value === 'today') {
        return createdDate >= today;
      } else if (dateFilter.value === 'week') {
        return createdDate >= startOfWeek;
      } else if (dateFilter.value === 'month') {
        return createdDate >= startOfMonth;
      } else if (dateFilter.value === 'year') {
        return createdDate >= startOfYear;
      }
      
      return true;
    });
  }
  
  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(p => 
      p.name.toLowerCase().includes(query) || 
      p.description?.toLowerCase().includes(query) ||
      p.location?.toLowerCase().includes(query) ||
      p.organizers?.some(o => o.name.toLowerCase().includes(query))
    );
  }
  
  // Sort results
  result.sort((a, b) => {
    let aValue = a[sortBy.value];
    let bValue = b[sortBy.value];
    
    // Handle date comparison
    if (['created_at', 'start_date', 'end_date'].includes(sortBy.value)) {
      aValue = aValue ? new Date(aValue).getTime() : 0;
      bValue = bValue ? new Date(bValue).getTime() : 0;
    }
    
    // Handle string comparison
    if (typeof aValue === 'string') {
      aValue = aValue.toLowerCase();
      bValue = bValue.toLowerCase();
    }
    
    // Handle undefined/null values
    if (aValue === undefined || aValue === null) aValue = '';
    if (bValue === undefined || bValue === null) bValue = '';
    
    // Sort direction
    if (sortDirection.value === 'asc') {
      return aValue > bValue ? 1 : -1;
    } else {
      return aValue < bValue ? 1 : -1;
    }
  });
  
  return result;
});

// Pagination computed properties
const totalPages = computed(() => {
  return Math.ceil(filteredPageants.value.length / itemsPerPage.value) || 1;
});

const paginatedPageants = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return filteredPageants.value.slice(start, end);
});

const paginationStart = computed(() => {
  if (filteredPageants.value.length === 0) return 0;
  return ((currentPage.value - 1) * itemsPerPage.value) + 1;
});

const paginationEnd = computed(() => {
  if (filteredPageants.value.length === 0) return 0;
  return Math.min(currentPage.value * itemsPerPage.value, filteredPageants.value.length);
});

// Pagination computed properties for visible page numbers
const visiblePageNumbers = computed(() => {
  const total = totalPages.value;
  const current = currentPage.value;
  const visible = [];
  
  if (total <= 7) {
    // Show all pages if 7 or fewer
    for (let i = 1; i <= total; i++) {
      visible.push(i);
    }
  } else {
    // Always show first page
    visible.push(1);
    
    if (current <= 4) {
      // Near the beginning
      for (let i = 2; i <= 5; i++) {
        visible.push(i);
      }
      visible.push('...');
      visible.push(total);
    } else if (current >= total - 3) {
      // Near the end
      visible.push('...');
      for (let i = total - 4; i <= total; i++) {
        visible.push(i);
      }
    } else {
      // In the middle
      visible.push('...');
      for (let i = current - 1; i <= current + 1; i++) {
        visible.push(i);
      }
      visible.push('...');
      visible.push(total);
    }
  }
  
  return visible;
});

// Watch for filter changes
watch([currentStatusFilter, dateFilter, organizerFilter, searchQuery, sortBy, sortDirection], () => {
  currentPage.value = 1;
});

// Mobile filter toggle state
const showFilters = ref(false);

// Initialize notification system
const notify = useNotification();

// Approve/Reject methods
const approvePageant = (pageant) => {
  selectedPageant.value = pageant;
  confirmAction.value = 'approve';
  showConfirmModal.value = true;
};

const rejectPageant = (pageant) => {
  selectedPageant.value = pageant;
  confirmAction.value = 'reject';
  showConfirmModal.value = true;
};

const closeConfirmModal = () => {
  showConfirmModal.value = false;
  selectedPageant.value = null;
  confirmAction.value = '';
  isProcessing.value = false;
};

const confirmActionHandler = () => {
  if (!selectedPageant.value) return;
  
  isProcessing.value = true;
  processingPageants.value.push(selectedPageant.value.id);
  lastAction.value = confirmAction.value;
  lastActionPageant.value = selectedPageant.value.id;
  
  if (confirmAction.value === 'archive') {
    router.patch(route('admin.pageants.update_status', selectedPageant.value.id), {
      status: 'Archived',
      reason: archiveReason.value
    }, {
      onSuccess: () => {
        notify.success(`Pageant "${selectedPageant.value.name}" has been archived successfully!`);
        closeConfirmModal();
        refreshData();
      },
      onError: (errors) => {
        console.error('Error archiving pageant:', errors);
        notify.error('Failed to archive pageant. Please try again.');
        closeConfirmModal();
      },
      onFinish: () => {
        isProcessing.value = false;
        const index = processingPageants.value.indexOf(selectedPageant.value.id);
        if (index > -1) {
          processingPageants.value.splice(index, 1);
        }
        lastAction.value = '';
        lastActionPageant.value = null;
      }
    });
    return;
  }
  
  const action = confirmAction.value === 'approve' ? 'approve' : 'reject';
  const url = route(`admin.pageants.${action}`, selectedPageant.value.id);
  
  router.post(url, {}, {
    onSuccess: (page) => {
      notify.success(
        `Pageant "${selectedPageant.value.name}" has been ${confirmAction.value}d successfully!`
      );
      closeConfirmModal();
      
      // Refresh the data to show updated list
      refreshData();
    },
    onError: (errors) => {
      console.error('Error processing pageant:', errors);
      
      // Check for specific error types
      if (errors && errors.message) {
        notify.error(errors.message);
      } else if (typeof errors === 'string') {
        notify.error(errors);
      } else if (errors && Object.keys(errors).length > 0) {
        const firstError = Object.values(errors)[0];
        notify.error(Array.isArray(firstError) ? firstError[0] : firstError);
      } else {
        notify.error(`Failed to ${confirmAction.value} pageant. Please try again.`);
      }
      
      closeConfirmModal();
    },
    onFinish: () => {
      isProcessing.value = false;
      const index = processingPageants.value.indexOf(selectedPageant.value.id);
      if (index > -1) {
        processingPageants.value.splice(index, 1);
      }
      lastAction.value = '';
      lastActionPageant.value = null;
    }
  });
};

const refreshData = () => {
  router.reload({ 
    only: ['pageants', 'organizers', 'pageantCounts'],
  });
};

// Icon references for dynamic component usage
const filterIcon = Filter;
const chevronIcon = ChevronDown;
const searchIcon = Search;

// // Add this right after the props definition
// const page = usePage();
// const notify = useNotification();

// // Add this right after defining the reactive data
// onMounted(() => {
//   // Check if there are any flash messages (from redirects after creating a pageant)
//   const flash = page.props.flash;
  
//   if (flash?.success) {
//     notify.success(flash.success);
//   }
  
//   if (flash?.error) {
//     notify.error(flash.error);
//   }
// });
</script> 