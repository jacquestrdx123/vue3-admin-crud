<template>
  <div class="admin-layout">
    <!-- Sidebar -->
    <SidebarMenu ref="sidebarRef" />

    <!-- Main Content Area -->
    <div class="main-content">
      <!-- Top Bar -->
      <header class="top-bar sticky top-0 z-30">
        <div class="bg-gray-900 dark:bg-black border-b border-gray-700 dark:border-gray-800 px-6 py-3 flex items-center justify-between gap-4">
          <div class="flex-1">
            <FavoriteMenuBar />
          </div>
          <button
            @click="openSearchModal"
            class="flex items-center gap-2 px-4 py-2 bg-gray-800 dark:bg-gray-700 hover:bg-gray-700 dark:hover:bg-gray-600 border border-gray-700 dark:border-gray-600 rounded-lg transition-colors group"
          >
            <svg class="h-5 w-5 text-gray-400 group-hover:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <span class="text-sm text-gray-400 group-hover:text-gray-300">Search</span>
            <kbd class="hidden sm:inline-flex items-center px-2 py-0.5 text-xs font-medium text-gray-400 bg-gray-900 dark:bg-gray-800 border border-gray-700 rounded">
              ⌘K
            </kbd>
          </button>
          <TopRightMenu />
        </div>
        <div class="bg-gray-100 dark:bg-gray-800 px-6 py-2">
          <Breadcrumbs />
        </div>
      </header>

      <!-- Page Content -->
      <main class="page-content p-6">
        <slot />
      </main>
    </div>

    <!-- Toast Notifications -->
    <ToastContainer />

    <!-- Global Search Modal -->
    <GlobalSearch />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import SidebarMenu from '@/Components/Navigation/SidebarMenu.vue'
import TopRightMenu from '@/Components/Navigation/TopRightMenu.vue'
import Breadcrumbs from '@/Components/Navigation/Breadcrumbs.vue'
import FavoriteMenuBar from '@/Components/Navigation/FavoriteMenuBar.vue'
import GlobalSearch from '@/Components/Navigation/GlobalSearch.vue'
import ToastContainer from '@/Components/UI/ToastContainer.vue'
import { useGlobalSearch } from '@/Composables/useGlobalSearch.js'
import { useSessionKeepalive } from '@/Composables/useSessionKeepalive.js'

const sidebarRef = ref(null)
const { openModal } = useGlobalSearch()

// Initialize session keepalive to prevent 419 errors
useSessionKeepalive()

const openSearchModal = () => {
  openModal()
}

// Keyboard shortcut (Ctrl+K or Cmd+K)
const handleKeyboardShortcut = (event) => {
  if ((event.ctrlKey || event.metaKey) && event.key === 'k') {
    event.preventDefault()
    openModal()
  }
}

onMounted(() => {
  document.addEventListener('keydown', handleKeyboardShortcut)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeyboardShortcut)
})
</script>

<style scoped>
.admin-layout {
  min-height: 100vh;
  background-color: rgb(249 250 251);
}

.dark .admin-layout {
  background-color: rgb(17 24 39);
}

.main-content {
  min-height: 100vh;
  margin-left: 250px;
  transition: margin-left 0.3s ease;
}

.top-bar {
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}

.page-content {
  max-width: 100%;
}

/* Responsive */
@media (max-width: 1023px) {
  .main-content {
    margin-left: 0 !important;
  }
}
</style>

<style>
/* Global style to handle sidebar collapse state */
body.sidebar-collapsed .main-content {
  margin-left: 64px !important;
}

body.sidebar-expanded .main-content {
  margin-left: 250px !important;
}

@media (max-width: 1023px) {
  body .main-content {
    margin-left: 0 !important;
  }
}
</style>
