<template>
  <div class="flex items-center gap-2">
    <!-- Notifications Dropdown -->
    <NotificationDropdown view-all-route="vue.notifications.index" />

    <!-- User Account Menu -->
    <div class="relative" ref="menuRef">
      <button
        @click="toggleMenu"
        class="flex items-center gap-2 px-3 py-2 text-white hover:bg-white/10 rounded-lg transition-colors"
      >
        <MaterialIcon icon="mdi-account-circle" size="lg" />
        <span class="font-medium">{{ currentUser?.name || 'Account' }}</span>
      </button>

      <!-- Dropdown Menu -->
      <Transition name="dropdown">
        <div
          v-if="showMenu"
          class="absolute right-0 mt-2 w-64 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 py-2 z-50"
        >
          <!-- User Info -->
          <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
            <div class="font-medium text-gray-900 dark:text-white">{{ currentUser?.name || 'Account' }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">{{ currentUser?.email || '' }}</div>
          </div>

          <!-- Menu Items -->
          <div class="py-1">
            <a
              @click.prevent="goToDashboard"
              href="#"
              class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer"
            >
              <MaterialIcon icon="mdi-view-dashboard" />
              <span>Dashboard</span>
            </a>
            <a
              @click.prevent="goToCustomers"
              href="#"
              class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer"
            >
              <MaterialIcon icon="mdi-account-group" />
              <span>Members</span>
            </a>
            <a
              @click.prevent="goToMenuItems"
              href="#"
              class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer"
            >
              <MaterialIcon icon="mdi-menu" />
              <span>Menu Items</span>
            </a>
            <a
              @click.prevent="openMenuBuilder"
              href="#"
              class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer"
            >
              <MaterialIcon icon="mdi-drag-horizontal" />
              <span>Menu Builder</span>
            </a>
          </div>

          <!-- Debug: Keepalive Test Button -->
          <div v-if="isDebugMode" class="border-t border-gray-200 dark:border-gray-700 py-1">
            <a
              @click.prevent="testKeepalive"
              href="#"
              class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer whitespace-nowrap"
              :class="{ 'opacity-50 cursor-not-allowed': isPingingKeepalive }"
            >
              <span>{{ isPingingKeepalive ? 'Pinging...' : 'Keepalive' }}</span>
            </a>
          </div>

          <!-- Logout -->
          <div class="border-t border-gray-200 dark:border-gray-700 py-1">
            <a
              @click.prevent="logout"
              href="#"
              class="flex items-center gap-3 px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer"
            >
              <MaterialIcon icon="mdi-logout" />
              <span>Logout</span>
            </a>
          </div>
        </div>
      </Transition>
    </div>

    <!-- Menu Builder Modal -->
    <MenuBuilder
      :show="showMenuBuilder"
      :menu-groups="menuGroups"
      @close="showMenuBuilder = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import MaterialIcon from './MaterialIcon.vue'
import NotificationDropdown from './NotificationDropdown.vue'
import MenuBuilder from './MenuBuilder.vue'
import { useSessionKeepalive } from '@/Composables/useSessionKeepalive.js'

const page = usePage()
const showMenu = ref(false)
const menuRef = ref(null)
const isPingingKeepalive = ref(false)
const showMenuBuilder = ref(false)

const { pingKeepalive } = useSessionKeepalive()

const currentUser = computed(() => {
  return page?.props?.auth?.user || null
})

const isDebugMode = computed(() => {
  const env = page?.props?.app_env
  return env === 'local' || env === 'debug' || env === 'development'
})

const menuGroups = computed(() => {
  return page?.props?.menuGroups || []
})

const toggleMenu = () => {
  showMenu.value = !showMenu.value
}

const goToDashboard = () => {
  showMenu.value = false
  const url = route('vue.dashboard')
  if (url) router.visit(url)
}

const goToMenuItems = () => {
  showMenu.value = false
  const url = route('vue.menu-groups.index')
  if (url) router.visit(url)
}

const openMenuBuilder = () => {
  showMenu.value = false
  showMenuBuilder.value = true
}

const goToCustomers = () => {
  showMenu.value = false
  const url = route('vue.members.index')
  if (url) router.visit(url)
}

const logout = () => {
  showMenu.value = false
    
  const url = route('vue.logout')
  if (url) router.post(url)
}

const testKeepalive = async () => {
  if (isPingingKeepalive.value) {
    return
  }

  showMenu.value = false
  isPingingKeepalive.value = true

  try {
    await pingKeepalive()
    console.log('Keepalive ping successful')
  } catch (error) {
    console.error('Keepalive ping failed:', error)
  } finally {
    isPingingKeepalive.value = false
  }
}

// Close menu when clicking outside
const handleClickOutside = (event) => {
  if (menuRef.value && !menuRef.value.contains(event.target)) {
    showMenu.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

.dropdown-enter-to,
.dropdown-leave-from {
  opacity: 1;
  transform: translateY(0);
}
</style>

