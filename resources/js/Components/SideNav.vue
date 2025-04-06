<template>
  <div class="flex h-screen">
    <!-- Mobile menu button with improved touch target -->
    <button
      @click="toggleSidebar"
      class="lg:hidden fixed top-4 left-4 z-50 p-2 rounded-md shadow-md bg-white"
      :class="[roleColor, 'hover:bg-gray-100']"
      aria-label="Toggle menu"
    >
      <Menu v-if="!isOpen" class="h-6 w-6" />
      <X v-else class="h-6 w-6" />
    </button>

    <!-- Mobile overlay backdrop -->
    <div 
      v-if="isOpen && isMobile" 
      @click="toggleSidebar"
      class="fixed inset-0 bg-gray-900 bg-opacity-50 z-30 transition-opacity duration-300 ease-in-out"
      aria-hidden="true"
    ></div>

    <!-- Sidebar -->
    <div
      class="fixed inset-y-0 left-0 z-40 bg-white border-r transform transition-all duration-300 ease-in-out"
      :class="[
        isOpen ? 'w-64' : 'w-20', 
        isMobile ? (isOpen ? 'translate-x-0' : '-translate-x-full') : 'translate-x-0',
        isMobile && isOpen ? 'shadow-xl' : ''
      ]"
    >
      <!-- Logo -->
      <div class="flex items-start p-4 border-b overflow-hidden">
        <Crown class="h-9 w-9 flex-shrink-0 mt-1" :class="roleColor" />
        <div 
          class="ml-3 font-bold text-gray-900 transition-all duration-300 overflow-hidden"
          :class="[isOpen ? 'opacity-100 max-w-full' : 'opacity-0 w-0 hidden']"
        >
          <div class="text-lg leading-tight">Pageant</div>
          <div class="text-lg leading-tight">Tabulation System</div>
        </div>
      </div>

      <!-- User info -->
      <div class="p-4 border-b overflow-hidden">
        <div class="flex items-center">
          <div class="h-12 w-12 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
            <User2 class="h-7 w-7" :class="roleColor" />
          </div>
          <div 
            class="ml-3 transition-all duration-300 overflow-hidden whitespace-nowrap"
            :class="[isOpen ? 'opacity-100 max-w-full' : 'opacity-0 max-w-0']"
          >
            <p class="text-sm font-medium text-gray-900">{{ user?.name }}</p>
            <p class="text-xs text-gray-500 capitalize">{{ user?.role }}</p>
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <nav 
        class="p-3 space-y-1 scrollbar-style" 
        :class="[isMobile ? 'overflow-y-auto' : 'overflow-visible']" 
        :style="isMobile ? 'max-height: calc(100vh - 200px)' : ''"
      >
        <template v-for="item in navigation" :key="item.name">
          <!-- Regular menu item without children -->
          <Link
            v-if="!item.children"
            :href="item.href"
            @click="openSubmenus = {}"
            class="flex items-center px-3 py-3 rounded-md text-sm font-medium transition-all duration-300 group relative overflow-visible"
            :class="[
              isActiveLink(item) ? activeClass + ' text-white' : 'text-gray-600 hover:bg-gray-100'
            ]"
          >
            <div class="flex items-center justify-center flex-shrink-0">
              <component :is="item.icon" class="h-6 w-6" :class="[!isActiveLink(item) ? 'mx-auto' : '']" />
            </div>
            
            <!-- Item name (visible when sidebar is open) -->
            <span 
              class="ml-3 transition-all duration-300 whitespace-nowrap overflow-hidden"
              :class="[isOpen ? 'opacity-100 max-w-full' : 'opacity-0 max-w-0 hidden']"
            >
              {{ item.name }}
            </span>
            
            <!-- Expandable hover popup for collapsed state (desktop only) -->
            <div 
              v-if="!isOpen && !isMobile" 
              class="absolute left-full top-0 ml-2 px-4 py-3 bg-white shadow-lg rounded-md opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition-all duration-200 z-50 whitespace-nowrap border border-gray-200 min-w-[150px] transform -translate-x-2 group-hover:translate-x-0 flex items-center"
              :class="[isActiveLink(item) ? 'border-l-4 ' + hoverBorderColor : '']"
            >
              <component :is="item.icon" class="h-5 w-5 mr-3" :class="[isActiveLink(item) ? roleColor : 'text-gray-500']" />
              <span :class="[isActiveLink(item) ? 'font-semibold ' + roleColor : 'text-gray-700']">{{ item.name }}</span>
            </div>
          </Link>
          
          <!-- Expandable menu item with children -->
          <div v-else class="mb-1">
            <!-- Parent item -->
            <button
              @click="toggleSubmenu(item.name)"
              class="w-full flex items-center px-3 py-3 rounded-md text-sm font-medium transition-all duration-300 group relative overflow-visible"
              :class="[
                isParentActive(item) ? activeClass + ' text-white' : 'text-gray-600 hover:bg-gray-100'
              ]"
            >
              <div class="flex items-center justify-center flex-shrink-0">
                <component :is="item.icon" class="h-6 w-6" :class="[!isParentActive(item) ? 'mx-auto' : '']" />
              </div>
              
              <!-- Item name and dropdown arrow (visible when sidebar is open) -->
              <div 
                class="ml-3 flex items-center justify-between w-full transition-all duration-300 whitespace-nowrap overflow-hidden"
                :class="[isOpen ? 'opacity-100 max-w-full' : 'opacity-0 max-w-0 hidden']"
              >
                <span>{{ item.name }}</span>
                <ChevronDown 
                  class="h-4 w-4 ml-2 transition-transform duration-200"
                  :class="{ 'transform rotate-180': openSubmenus[item.name] }"
                />
              </div>
              
              <!-- Expandable hover popup for collapsed state (desktop only) -->
              <div 
                v-if="!isOpen && !isMobile" 
                class="absolute left-full top-0 ml-2 px-4 py-3 bg-white shadow-lg rounded-md opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition-all duration-200 z-50 whitespace-nowrap border border-gray-200 min-w-[180px] transform -translate-x-2 group-hover:translate-x-0"
                :class="[isParentActive(item) ? 'border-l-4 ' + hoverBorderColor : '']"
              >
                <div class="flex items-center justify-between w-full">
                  <div class="flex items-center">
                    <component :is="item.icon" class="h-5 w-5 mr-3" :class="[isParentActive(item) ? roleColor : 'text-gray-500']" />
                    <span :class="[isParentActive(item) ? 'font-semibold ' + roleColor : 'text-gray-700']">{{ item.name }}</span>
                  </div>
                  <ChevronRight class="h-4 w-4 text-gray-400" />
                </div>
                
                <!-- Submenu items in hover popup for collapsed sidebar -->
                <div class="mt-2 ml-8 space-y-1">
                  <a 
                    v-for="child in item.children" 
                    :key="child.name"
                    :href="child.href"
                    @click.prevent="navigateToSubMenuItem(child.href, item.name)"
                    class="block py-2 px-3 text-sm rounded-md hover:bg-gray-100 transition-colors"
                    :class="[isActiveLink(child) ? 'font-semibold ' + roleColor : 'text-gray-700']"
                  >
                    {{ child.name }}
                  </a>
                </div>
              </div>
            </button>
            
            <!-- Submenu items (visible only when parent is expanded and sidebar is open) -->
            <div 
              v-if="isOpen && openSubmenus[item.name]"
              class="mt-1 ml-8 space-y-1 overflow-hidden transition-all duration-300"
            >
              <Link 
                v-for="child in item.children" 
                :key="child.name"
                :href="child.href"
                @click.prevent="navigateToSubMenuItem(child.href, item.name)"
                class="flex items-center px-3 py-2 text-sm rounded-md transition-colors"
                :class="[
                  isActiveLink(child) ? activeClass + ' text-white' : 'text-gray-600 hover:bg-gray-100'
                ]"
              >
                <component :is="child.icon" class="h-4 w-4 mr-2" />
                <span>{{ child.name }}</span>
              </Link>
            </div>
          </div>
        </template>
      </nav>

      <!-- Sidebar toggle button (hidden on mobile) -->
      <div class="absolute bottom-20 w-full flex justify-center p-2 lg:block hidden">
        <button 
          @click="toggleCollapse" 
          class="p-2 rounded-full bg-gray-100 hover:bg-gray-200 transition-all duration-200"
          :title="isOpen ? 'Collapse sidebar' : 'Expand sidebar'"
        >
          <ChevronLeft v-if="isOpen" class="h-6 w-6 text-gray-600" />
          <ChevronRight v-else class="h-6 w-6 text-gray-600" />
        </button>
      </div>

      <!-- Logout button -->
      <div class="absolute bottom-0 w-full p-4 border-t">
        <button
          @click="logout"
          class="flex items-center w-full px-3 py-3 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-md transition-colors group relative"
        >
          <LogOut class="h-6 w-6 flex-shrink-0" />
          <span 
            class="ml-3 transition-all duration-300 whitespace-nowrap"
            :class="[isOpen ? 'opacity-100 max-w-full' : 'opacity-0 max-w-0 hidden']"
          >
            Logout
          </span>
          
          <!-- Expandable hover popup for collapsed state (desktop only) -->
          <div 
            v-if="!isOpen && !isMobile" 
            class="absolute left-full bottom-0 ml-2 px-4 py-3 bg-white shadow-lg rounded-md opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition-all duration-200 z-50 whitespace-nowrap border border-gray-200 min-w-[150px] transform -translate-x-2 group-hover:translate-x-0 flex items-center"
          >
            <LogOut class="h-5 w-5 mr-3 text-gray-500" />
            <span class="text-gray-700">Logout</span>
          </div>
        </button>
      </div>
    </div>

    <!-- Main content -->
    <div
      class="flex-1 transition-all duration-300"
      :class="{ 
        'lg:ml-64': isOpen, 
        'lg:ml-20': !isOpen,
        'ml-0': !isOpen && isMobile || isOpen && isMobile
      }"
    >
      <!-- Wrap the slot in a div to ensure it has a proper root element -->
      <div class="h-full pt-16 lg:pt-0">
        <slot></slot>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import {
  Crown,
  LayoutDashboard,
  Award,
  Calendar,
  Archive,
  PieChart,
  Users,
  Settings,
  Calculator,
  ClipboardList,
  Printer,
  LogOut,
  Menu,
  X,
  User2,
  Star,
  ChevronLeft,
  ChevronRight,
  Plus,
  FileText,
  LayoutList,
  ChevronDown,
  UserCog,
  UserPlus,
  Shield,
  Gavel,
  UsersRound,
  ClipboardSignature
} from 'lucide-vue-next'

const page = usePage()

// --- State ---
const isOpen = ref(window.innerWidth >= 1024) // Sidebar open/closed
const isMobile = ref(window.innerWidth < 1024) // Mobile view active
const openSubmenus = ref({}) // Tracks which parent submenus are open { [itemName]: boolean }

// --- Computed Properties ---
const componentPath = computed(() => {
  const path = page.component.value || '';
  return path;
})

const user = computed(() => {
  const authUser = page.props.auth?.user || null
  if (authUser) {
    return {
      name: authUser.name || 'User',
      role: authUser.role || 'admin',
    }
  }
  // Fallback logic
  const path = componentPath.value || ''
  let role = 'admin'
  if (path && path.startsWith('Admin/')) role = 'admin'
  else if (path && path.startsWith('Organizer/')) role = 'organizer'
  else if (path && path.startsWith('Tabulator/')) role = 'tabulator'
  else if (path && path.startsWith('Judge/')) role = 'judge'
  return {
    name: `${role.charAt(0).toUpperCase() + role.slice(1)} User`,
    role: role,
  }
})

// Role-based styling
const roleColor = computed(() => {
  switch (user.value?.role) {
    case 'admin': return 'text-teal-600'
    case 'organizer': return 'text-orange-600'
    case 'tabulator': return 'text-blue-600'
    case 'judge': return 'text-amber-600'
    default: return 'text-gray-600'
  }
})

const activeClass = computed(() => {
  switch (user.value?.role) {
    case 'admin': return 'bg-teal-600'
    case 'organizer': return 'bg-orange-600'
    case 'tabulator': return 'bg-blue-600'
    case 'judge': return 'bg-amber-600'
    default: return 'bg-gray-600'
  }
})

const hoverBorderColor = computed(() => {
  switch (user.value?.role) {
    case 'admin': return 'border-teal-600'
    case 'organizer': return 'border-orange-600'
    case 'tabulator': return 'border-blue-600'
    case 'judge': return 'border-amber-600'
    default: return 'border-gray-600'
  }
})

// Navigation items
const navigation = computed(() => {
  // Clear debugging output that might be polluting the console
  console.log('User role in navigation:', user.value?.role);
  
  // Helper function to create route URLs based on role
  const rolePrefix = user.value?.role || 'admin';
  
  // Generate proper URLs for each section
  switch (user.value?.role) {
    case 'admin':
      return [
        { name: 'Dashboard', href: route('admin.dashboard'), route: 'admin.dashboard', icon: LayoutDashboard },
        { 
          name: 'Pageant Management', 
          icon: Crown,
          children: [
            { name: 'All Pageants', href: route('admin.pageants.index'), route: 'admin.pageants.index', icon: LayoutList },
            { name: 'Create Pageant', href: route('admin.pageants.create'), route: 'admin.pageants.create', icon: Plus },
            { name: 'Previous Pageants', href: route('admin.pageants.previous'), route: 'admin.pageants.previous', icon: Award },
            { name: 'Archived Pageants', href: route('admin.pageants.archived'), route: 'admin.pageants.archived', icon: Archive }
          ]
        },
        { 
          name: 'User Management', 
          icon: UserCog,
          children: [
            { name: 'Organizers', href: route('admin.users.organizers'), route: 'admin.users.organizers', icon: UsersRound },
            { name: 'Create Organizer', href: route('admin.users.organizers.create'), route: 'admin.users.organizers.create', icon: UserPlus },
            { name: 'Administrators', href: route('admin.users.admins'), route: 'admin.users.admins', icon: Shield },
            { name: 'Tabulators', href: route('admin.users.tabulators'), route: 'admin.users.tabulators', icon: Calculator },
            { name: 'Judges', href: route('admin.users.judges'), route: 'admin.users.judges', icon: Gavel },
            { name: 'User Permissions', href: route('admin.users.permissions'), route: 'admin.users.permissions', icon: ClipboardSignature }
          ]
        },
        { name: 'Reports', href: route('admin.reports'), route: 'admin.reports', icon: ClipboardList },
        { name: 'Audit Log', href: '/admin/audit-log', route: 'Admin/AuditLog', icon: FileText }
      ]
    case 'organizer':
      return [
        { name: 'Dashboard', href: route('organizer.dashboard'), route: 'organizer.dashboard', icon: LayoutDashboard },
        { name: 'My Pageants', href: route('organizer.my-pageants'), route: 'organizer.my-pageants', icon: Award },
        { name: 'Contestants', href: route('organizer.contestants'), route: 'organizer.contestants', icon: Users },
        { name: 'Timeline', href: route('organizer.timeline'), route: 'organizer.timeline', icon: Calendar }
      ]
    case 'tabulator':
      return [
        { name: 'Dashboard', href: route('tabulator.dashboard'), route: 'tabulator.dashboard', icon: LayoutDashboard },
        { name: 'Judges', href: route('tabulator.judges'), route: 'tabulator.judges', icon: Users },
        { name: 'Scores', href: route('tabulator.scores'), route: 'tabulator.scores', icon: ClipboardList },
        { name: 'Results', href: route('tabulator.results'), route: 'tabulator.results', icon: Award },
        { name: 'Print', href: route('tabulator.print'), route: 'tabulator.print', icon: Printer }
      ]
    case 'judge':
      return [
        { name: 'Scoring', href: route('judge.scoring'), route: 'judge.scoring', icon: Calculator }
      ]
    default:
      return []
  }
})

// --- Methods ---

// Active link detection
const isActiveLink = (item) => {
  // Check if we're working with a route() generated URL
  const itemUrl = typeof item.href === 'string' ? item.href : '';
  
  // Exact current URL from the page
  const currentUrl = page.url || '';
  
  // For direct top-level menu items like Dashboard, Reports, Audit Log
  if (!item.children) {
    // Special cases for non-pageant section direct links
    if (item.name === 'Dashboard' || item.name === 'Reports' || item.name === 'Audit Log') {
      // Check for Reports specifically
      if (item.name === 'Reports') {
        return route().current('admin.reports');
      }
      
      // Check for Audit Log specifically
      if (item.name === 'Audit Log') {
        return currentUrl.includes('admin/audit-log');
      }
      
      // Direct route checking for these items
      if (item.route && route().current(item.route)) {
        return true;
      }
      
      // URL-based matching as fallback
      if (currentUrl === itemUrl) {
        return true;
      }
      
      // For section root dashboards
      if (item.name === 'Dashboard') {
        const role = user.value?.role || '';
        
        if (role === 'admin' && route().current('admin.dashboard')) {
          return true;
        } else if (role === 'organizer' && route().current('organizer.dashboard')) {
          return true;
        } else if (role === 'tabulator' && route().current('tabulator.dashboard')) {
          return true;
        } else if (role === 'judge' && route().current('judge.scoring')) {
          return true;
        }
      }
    }
  }
  
  // Direct checks for specific pageant navigation items
  if (item.name === 'Create Pageant') {
    return route().current('admin.pageants.create');
  }
  
  if (item.name === 'All Pageants' && route().current('admin.pageants.create')) {
    return false;
  }
  
  if (item.name === 'Previous Pageants') {
    return route().current('admin.pageants.previous') || route().current('admin.pageants.previous.detail');
  }
  
  if (item.name === 'Archived Pageants') {
    return route().current('admin.pageants.archived') || route().current('admin.pageants.archived.detail');
  }
  
  // Special case for All Pageants with sub-routes
  if (item.name === 'All Pageants') {
    // All Pageants is active when on the index page or a pageant detail page through the main route
    return route().current('admin.pageants.index') || route().current('admin.pageants.detail');
  }
  
  // First try route-based matching
  if (item.route && route().current(item.route)) {
    return true;
  }
  
  // If we don't have route information or route matching failed, fall back to URL matching
  if (page.url && itemUrl && (page.url === itemUrl || page.url.startsWith(itemUrl + '/'))) {
    // Special case for admin/pageants to prevent highlighting "All Pageants" 
    // when on other pageant-related pages
    if (itemUrl.includes('admin/pageants') && !route().current('admin.pageants.index')) {
      return false;
    }
    return true;
  }

  return false;
}

// Helper to check if a parent item should be highlighted (active child)
const isParentActive = (item) => {
  return item.children && item.children.some(child => isActiveLink(child));
}

// Determine and set the open submenu based on the active link
const updateActiveSubmenu = () => {
  if (!navigation.value) return;

  const newOpenState = {};
  let activeParentFound = false;

  for (const item of navigation.value) {
    if (!activeParentFound && item.children && isParentActive(item)) {
      newOpenState[item.name] = true;
      activeParentFound = true;
      // If only one submenu can be active at a time, uncomment break
      // break;
    }
  }
  // Replace the old state entirely on navigation/load
  openSubmenus.value = newOpenState;
}

// Sidebar visibility toggles
const toggleSidebar = () => {
  isOpen.value = !isOpen.value;
  if (isMobile.value) {
    // Toggle body scroll lock for mobile overlay
    document.body.style.overflow = isOpen.value ? 'hidden' : '';
  }
}

const toggleCollapse = () => {
  isOpen.value = !isOpen.value;
  if (!isMobile.value) {
    // Persist desktop collapsed state
    localStorage.setItem('sidebarCollapsed', (!isOpen.value).toString());
  }
}

// Toggle a specific submenu, closing others
const toggleSubmenu = (name) => {
  const currentlyOpen = !!openSubmenus.value[name]; // Ensure boolean
  const newOpenState = {}; // Start fresh for accordion behavior

  // If the clicked menu wasn't open, mark it to be opened
  if (!currentlyOpen) {
    newOpenState[name] = true;
  }
  // Otherwise, clicking an open menu closes it (and all others remain closed)

  openSubmenus.value = newOpenState; // Update the state
}

// Navigate to a child item, ensuring its parent is open and others closed
const navigateToSubMenuItem = (childHref, parentName) => {
  const newOpenState = {};
  newOpenState[parentName] = true; // Ensure the parent is open
  openSubmenus.value = newOpenState; // Update state, closing others

  router.visit(childHref);
}

const logout = () => {
  openSubmenus.value = {}; // Clear open menus on logout
  router.post(route('logout'));
}

// Window resize handling
const updateSidebarState = () => {
  const mobile = window.innerWidth < 1024;
  if (mobile !== isMobile.value) {
    isMobile.value = mobile;
    if (mobile) {
      // If switching to mobile, always close sidebar initially
      isOpen.value = false;
      document.body.style.overflow = ''; // Ensure overflow is reset
    } else {
      // If switching to desktop, restore state or default to open
      const savedState = localStorage.getItem('sidebarCollapsed');
      isOpen.value = savedState === 'true' ? false : true;
      document.body.style.overflow = ''; // Ensure overflow is reset
    }
  }
  // Keep desktop state consistent even without switching mode
  else if (!isMobile.value) {
    const savedState = localStorage.getItem('sidebarCollapsed');
    // Only update if not currently mobile - prevents flicker on resize near breakpoint
    if (!isMobile.value) {
      isOpen.value = savedState === 'true' ? false : true;
    }
  }
}

// --- Lifecycle Hooks ---
onMounted(() => {
  // Initial setup based on screen size and saved state
  updateSidebarState();

  // Determine active submenu based on initial URL
  updateActiveSubmenu();

  window.addEventListener('resize', updateSidebarState);
})

onUnmounted(() => {
  window.removeEventListener('resize', updateSidebarState);
  document.body.style.overflow = ''; // Cleanup body style on component unmount
})

// Watch for page navigation to update the active submenu and handle mobile sidebar
watch(() => page.url, () => {
  updateActiveSubmenu();
  // If on mobile and sidebar is open, close it on navigation
  if (isMobile.value && isOpen.value) {
    isOpen.value = false;
    document.body.style.overflow = ''; // Release scroll lock
  }
});
</script>

<style scoped>
.scrollbar-style {
  scrollbar-width: thin;
  scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
}

.scrollbar-style::-webkit-scrollbar {
  width: 5px;
}

.scrollbar-style::-webkit-scrollbar-track {
  background: transparent;
}

.scrollbar-style::-webkit-scrollbar-thumb {
  background-color: rgba(156, 163, 175, 0.5);
  border-radius: 20px;
}

/* Ensure content area has padding when mobile menu button is visible */
@media (max-width: 1023px) {
  .flex-1 > div {
    padding-top: 4rem; /* Adjust as needed based on button size/position */
  }
}
</style>
