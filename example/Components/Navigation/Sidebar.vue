<template>
  <div class="flex h-screen bg-gray-100 dark:bg-gray-900">
    <!-- Sidebar -->
    <div :class="[
      'fixed inset-y-0 left-0 z-50 flex flex-col bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transition-all duration-300',
      sidebarOpen ? 'w-64' : 'w-20'
    ]">
      <!-- Logo/Brand -->
      <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200 dark:border-gray-700">
        <h1 v-if="sidebarOpen" class="text-xl font-bold text-gray-900 dark:text-white">
          {{ appName }}
        </h1>
        <button
          @click="toggleSidebar"
          class="p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700"
        >
          <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 overflow-y-auto py-4">
        <NavigationGroup
          v-for="(group, index) in filteredNavigation"
          :key="index"
          :group="group"
          :sidebar-open="sidebarOpen"
        />
      </nav>

      <!-- User Menu -->
      <div class="border-t border-gray-200 dark:border-gray-700 p-4">
        <div v-if="user" class="flex items-center gap-3">
          <div class="flex-shrink-0">
            <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-white font-semibold">
              {{ userInitials }}
            </div>
          </div>
          <div v-if="sidebarOpen" class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
              {{ user.name }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
              {{ user.email }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div :class="[
      'flex-1 transition-all duration-300',
      sidebarOpen ? 'ml-64' : 'ml-20'
    ]">
      <slot />
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import NavigationGroup from './NavigationGroup.vue'

const props = defineProps({
  navigation: {
    type: Array,
    required: true
  }
})

const page = usePage()
const sidebarOpen = ref(true)

const user = computed(() => page.props.auth.user)
const permissions = computed(() => page.props.auth.user?.permissions || [])

const appName = computed(() => {
  return import.meta.env.VITE_APP_NAME || 'Laravel'
})

const userInitials = computed(() => {
  if (!user.value) return ''
  const names = user.value.name.split(' ')
  return names.map(n => n[0]).join('').substring(0, 2).toUpperCase()
})

const filteredNavigation = computed(() => {
  return props.navigation.filter(group => {
    if (!group.permission) return true
    return hasPermission(group.permission)
  }).map(group => ({
    ...group,
    items: group.items.filter(item => {
      if (!item.permission) return true
      return hasPermission(item.permission)
    })
  })).filter(group => group.items.length > 0)
})

const hasPermission = (permission) => {
  if (!permission) return true
  if (Array.isArray(permission)) {
    return permission.some(p => permissions.value.includes(p))
  }
  return permissions.value.includes(permission)
}

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}
</script>

