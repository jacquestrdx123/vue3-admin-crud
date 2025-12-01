import { ref, computed, onMounted, onUnmounted } from 'vue'
import axios from 'axios'

const notifications = ref([])
const unreadCount = ref(0)
const isLoading = ref(false)
const error = ref(null)
const pollInterval = ref(null)

/**
 * Composable for managing database notifications
 */
export function useNotifications() {
  /**
   * Fetch unread notifications
   */
  const fetchUnreadNotifications = async () => {
    try {
      isLoading.value = true
      error.value = null
      const response = await axios.get('/api/notifications/unread')
      notifications.value = response.data.data
      return response.data.data
    } catch (err) {
      error.value = err.message
      console.error('Error fetching unread notifications:', err)
      return []
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Fetch all notifications with pagination
   */
  const fetchAllNotifications = async (page = 1) => {
    try {
      isLoading.value = true
      error.value = null
      const response = await axios.get(`/api/notifications?page=${page}`)
      return response.data
    } catch (err) {
      error.value = err.message
      console.error('Error fetching notifications:', err)
      return { data: [], meta: {} }
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Fetch unread notification count
   */
  const fetchUnreadCount = async () => {
    try {
      const response = await axios.get('/api/notifications/unread-count')
      unreadCount.value = response.data.count
      return response.data.count
    } catch (err) {
      console.error('Error fetching unread count:', err)
      return 0
    }
  }

  /**
   * Mark a notification as read
   */
  const markAsRead = async (notificationId) => {
    try {
      await axios.patch(`/api/notifications/${notificationId}/mark-read`)
      
      // Update local state
      const notification = notifications.value.find(n => n.id === notificationId)
      if (notification && !notification.read_at) {
        notification.read_at = new Date().toISOString()
        unreadCount.value = Math.max(0, unreadCount.value - 1)
      }
      
      return true
    } catch (err) {
      console.error('Error marking notification as read:', err)
      return false
    }
  }

  /**
   * Mark all notifications as read
   */
  const markAllAsRead = async () => {
    try {
      await axios.patch('/api/notifications/mark-all-read')
      
      // Update local state
      notifications.value.forEach(notification => {
        if (!notification.read_at) {
          notification.read_at = new Date().toISOString()
        }
      })
      unreadCount.value = 0
      
      return true
    } catch (err) {
      console.error('Error marking all notifications as read:', err)
      return false
    }
  }

  /**
   * Delete a notification
   */
  const deleteNotification = async (notificationId) => {
    try {
      await axios.delete(`/api/notifications/${notificationId}`)
      
      // Update local state
      const index = notifications.value.findIndex(n => n.id === notificationId)
      if (index !== -1) {
        const notification = notifications.value[index]
        if (!notification.read_at) {
          unreadCount.value = Math.max(0, unreadCount.value - 1)
        }
        notifications.value.splice(index, 1)
      }
      
      return true
    } catch (err) {
      console.error('Error deleting notification:', err)
      return false
    }
  }

  /**
   * Delete all read notifications
   */
  const deleteAllRead = async () => {
    try {
      await axios.delete('/api/notifications/delete-all-read')
      
      // Update local state
      notifications.value = notifications.value.filter(n => !n.read_at)
      
      return true
    } catch (err) {
      console.error('Error deleting all read notifications:', err)
      return false
    }
  }

  /**
   * Start polling for new notifications
   */
  const startPolling = (intervalMs = 30000) => {
    stopPolling()
    
    // Initial fetch
    fetchUnreadCount()
    fetchUnreadNotifications()
    
    // Poll for updates
    pollInterval.value = setInterval(() => {
      fetchUnreadCount()
      fetchUnreadNotifications()
    }, intervalMs)
  }

  /**
   * Stop polling for notifications
   */
  const stopPolling = () => {
    if (pollInterval.value) {
      clearInterval(pollInterval.value)
      pollInterval.value = null
    }
  }

  /**
   * Get notification icon based on type
   */
  const getNotificationIcon = (notification) => {
    const data = notification.data || {}
    
    if (data.icon) {
      return data.icon
    }
    
    // Default icons based on common notification types
    const type = notification.type.toLowerCase()
    if (type.includes('ticket')) return 'mdi-ticket'
    if (type.includes('payment')) return 'mdi-currency-usd'
    if (type.includes('document')) return 'mdi-file-document'
    if (type.includes('member')) return 'mdi-account'
    if (type.includes('message') || type.includes('mail')) return 'mdi-email'
    
    return 'mdi-bell'
  }

  /**
   * Get notification title
   */
  const getNotificationTitle = (notification) => {
    return notification.data?.title || 'Notification'
  }

  /**
   * Get notification body/message
   */
  const getNotificationBody = (notification) => {
    return notification.data?.body || notification.data?.message || ''
  }

  /**
   * Get notification action URL
   */
  const getNotificationUrl = (notification) => {
    return notification.data?.url || notification.data?.action_url || null
  }

  /**
   * Format time ago
   */
  const formatTimeAgo = (dateString) => {
    const date = new Date(dateString)
    const now = new Date()
    const seconds = Math.floor((now - date) / 1000)

    if (seconds < 60) return 'just now'
    if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`
    if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`
    if (seconds < 604800) return `${Math.floor(seconds / 86400)}d ago`
    
    return date.toLocaleDateString()
  }

  return {
    notifications,
    unreadCount,
    isLoading,
    error,
    fetchUnreadNotifications,
    fetchAllNotifications,
    fetchUnreadCount,
    markAsRead,
    markAllAsRead,
    deleteNotification,
    deleteAllRead,
    startPolling,
    stopPolling,
    getNotificationIcon,
    getNotificationTitle,
    getNotificationBody,
    getNotificationUrl,
    formatTimeAgo,
  }
}

