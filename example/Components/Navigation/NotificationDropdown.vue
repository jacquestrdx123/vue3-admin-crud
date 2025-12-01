<template>
  <div class="relative" ref="dropdownRef">
    <!-- Notification Bell Button -->
    <button
      @click="toggleDropdown"
      class="relative p-2 text-white hover:bg-white/10 rounded-lg transition-colors"
      :class="{ 
        'bg-white/10': isOpen,
        'notification-bell-new': store.hasNewNotifications
      }"
    >
      <MaterialIcon icon="mdi-bell-outline" size="lg" />
      <span
        v-if="store.unreadCount > 0"
        class="absolute -top-1 -right-1 bg-red-600 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center"
        :class="{ 'notification-pulse': store.hasNewNotifications }"
      >
        {{ store.displayCount }}
      </span>
      <!-- New notification indicator dot -->
      <span
        v-if="store.hasNewNotifications"
        class="absolute top-0 right-0 w-3 h-3 bg-ciba-green rounded-full border-2 border-gray-900 notification-ping"
      ></span>
    </button>

    <!-- Dropdown Panel -->
    <Transition name="dropdown">
      <div
        v-if="isOpen"
        class="absolute right-0 mt-2 w-96 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 z-50 max-h-[600px] flex flex-col"
      >
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Notifications</h3>
          <div class="flex items-center gap-2">
            <button
              v-if="store.unreadCount > 0"
              @click="handleMarkAllAsRead"
              class="text-sm text-ciba-green hover:text-ciba-green/80 transition-colors"
              title="Mark all as read"
            >
              <MaterialIcon icon="mdi-check-all" size="sm" />
            </button>
            <button
              @click="handleDeleteAllRead"
              class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition-colors"
              title="Delete all read"
            >
              <MaterialIcon icon="mdi-delete-sweep" size="sm" />
            </button>
          </div>
        </div>

        <!-- Notifications List -->
        <div class="flex-1 overflow-y-auto">
          <div v-if="store.isLoading && store.notifications.length === 0" class="p-8 text-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-ciba-green mx-auto"></div>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Loading notifications...</p>
          </div>

          <div v-else-if="store.notifications.length === 0" class="p-8 text-center">
            <MaterialIcon icon="mdi-bell-off-outline" size="xl" class="text-gray-400 dark:text-gray-600 mb-2" />
            <p class="text-sm text-gray-500 dark:text-gray-400">No notifications</p>
          </div>

          <div v-else>
            <div
              v-for="notification in store.notifications"
              :key="notification.id"
              class="border-b border-gray-100 dark:border-gray-700 last:border-b-0 transition-colors"
              :class="{ 
                'bg-blue-50/50 dark:bg-blue-900/10': !notification.read_at,
                'notification-item-new': isNewNotification(notification)
              }"
            >
              <div 
                class="p-4 flex gap-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50"
                @click="toggleExpand(notification.id)"
              >
                <!-- Icon -->
                <div class="flex-shrink-0">
                  <div 
                    class="w-10 h-10 rounded-full flex items-center justify-center"
                    :class="notification.read_at ? 'bg-gray-200 dark:bg-gray-700' : 'bg-ciba-green/20'"
                  >
                    <MaterialIcon 
                      :icon="getNotificationIcon(notification)" 
                      size="sm" 
                      :class="notification.read_at ? 'text-gray-600 dark:text-gray-400' : 'text-ciba-green'"
                    />
                  </div>
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                  <div class="flex items-start justify-between gap-2 mb-1">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-white line-clamp-1">
                      {{ getNotificationTitle(notification) }}
                    </h4>
                    <button
                      @click.stop="handleDelete(notification.id)"
                      class="flex-shrink-0 text-gray-400 hover:text-red-500 dark:text-gray-500 dark:hover:text-red-400 transition-colors"
                    >
                      <MaterialIcon icon="mdi-close" size="xs" />
                    </button>
                  </div>
                  
                  <div class="mb-2">
                    <p 
                      class="text-sm text-gray-600 dark:text-gray-400 transition-all duration-300"
                      :class="{ 'line-clamp-2': !isExpanded(notification.id) }"
                      v-html="getNotificationBody(notification)"
                    ></p>
                    <button
                      v-if="getNotificationBody(notification).length > 100"
                      @click.stop="toggleExpand(notification.id)"
                      class="text-xs text-ciba-green hover:text-ciba-green/80 mt-1 transition-colors"
                    >
                      {{ isExpanded(notification.id) ? 'Show less' : 'Show more' }}
                    </button>
                  </div>
                  
                  <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-500 dark:text-gray-500">
                      {{ formatTimeAgo(notification.created_at) }}
                    </span>
                    
                    <div class="flex items-center gap-3">
                      <button
                        v-if="!notification.read_at"
                        @click.stop="handleMarkAsRead(notification.id)"
                        class="text-xs text-ciba-green hover:text-ciba-green/80 transition-colors flex items-center gap-1"
                      >
                        <MaterialIcon icon="mdi-check" size="xs" />
                        <span>Mark as read</span>
                      </button>
                      
                      <button
                        v-if="getNotificationUrl(notification)"
                        @click.stop="handleNotificationClick(notification)"
                        class="text-xs text-ciba-green hover:text-ciba-green/80 font-medium transition-colors flex items-center gap-1"
                      >
                        <MaterialIcon icon="mdi-open-in-new" size="xs" />
                        <span>View</span>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div v-if="viewAllRoute" class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
          <button
            @click="viewAllNotifications"
            class="w-full text-center text-sm text-ciba-green hover:text-ciba-green/80 font-medium transition-colors"
          >
            View all notifications
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { router } from '@inertiajs/vue3'
import { useNotificationStore } from '@/Stores/notificationStore.js'
import { useMemberNotificationStore } from '@/Stores/memberNotificationStore.js'
import { useNotifications } from '@/Composables/useNotifications.js'
import MaterialIcon from './MaterialIcon.vue'

const props = defineProps({
  store: {
    type: Object,
    default: null,
  },
  viewAllRoute: {
    type: String,
    default: null,
  },
})

// Use provided store or default to admin notification store
const store = props.store || useNotificationStore()
const dropdownRef = ref(null)
const isOpen = ref(false)
const expandedNotificationId = ref(null)

// Still use the composable for helper functions
const {
  getNotificationIcon,
  getNotificationTitle,
  getNotificationBody,
  getNotificationUrl,
  formatTimeAgo,
} = useNotifications()

const isNewNotification = (notification) => {
  return !store.seenNotificationIds.includes(notification.id)
}

const isExpanded = (notificationId) => {
  return expandedNotificationId.value === notificationId
}

const toggleExpand = (notificationId) => {
  if (expandedNotificationId.value === notificationId) {
    expandedNotificationId.value = null
  } else {
    expandedNotificationId.value = notificationId
  }
}

const toggleDropdown = async () => {
  isOpen.value = !isOpen.value
  
  if (isOpen.value) {
    await store.fetchUnreadNotifications()
    // Mark current notifications as seen when opening dropdown
    store.dismissNewAlert()
  }
}

const handleMarkAsRead = async (notificationId) => {
  await store.markAsRead(notificationId)
}

const handleMarkAllAsRead = async () => {
  await store.markAllAsRead()
}

const handleDelete = async (notificationId) => {
  await store.deleteNotification(notificationId)
}

const handleDeleteAllRead = async () => {
  if (confirm('Are you sure you want to delete all read notifications?')) {
    await store.deleteAllRead()
  }
}

const handleNotificationClick = async (notification) => {
  // Mark as read if unread
  if (!notification.read_at) {
    await store.markAsRead(notification.id)
  }
  
  // Navigate to URL if available
  const url = getNotificationUrl(notification)
  if (url) {
    isOpen.value = false
    
    // Always open in a new tab
    // Convert relative URLs to absolute URLs
    const absoluteUrl = url.startsWith('/') 
      ? `${window.location.origin}${url}`
      : url
    
    window.open(absoluteUrl, '_blank')
  }
}

const viewAllNotifications = () => {
  isOpen.value = false
  if (props.viewAllRoute) {
    router.visit(route(props.viewAllRoute))
  }
}

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    isOpen.value = false
  }
}

// Start polling when mounted
onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  store.initialize()
  store.startPolling(30000) // Poll every 30 seconds
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
  store.stopPolling()
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

.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Styling for HTML content in notifications */
.line-clamp-2 :deep(a) {
  color: #BAF504;
  text-decoration: underline;
}

.line-clamp-2 :deep(a:hover) {
  opacity: 0.8;
}

.line-clamp-2 :deep(strong),
.line-clamp-2 :deep(b) {
  font-weight: 600;
}

.line-clamp-2 :deep(em),
.line-clamp-2 :deep(i) {
  font-style: italic;
}

/* New notification animations */
.notification-bell-new {
  animation: bellShake 0.5s ease-in-out;
  animation-iteration-count: 3;
}

@keyframes bellShake {
  0%, 100% { transform: rotate(0deg); }
  10%, 30%, 50%, 70%, 90% { transform: rotate(-10deg); }
  20%, 40%, 60%, 80% { transform: rotate(10deg); }
}

.notification-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.7;
  }
}

.notification-ping {
  animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;
}

@keyframes ping {
  75%, 100% {
    transform: scale(2);
    opacity: 0;
  }
}

.notification-item-new {
  animation: highlightNew 0.5s ease-in-out;
}

@keyframes highlightNew {
  0% {
    background-color: rgba(186, 245, 4, 0.2);
  }
  100% {
    background-color: transparent;
  }
}
</style>
