import { defineStore } from 'pinia'
import axios from 'axios'

export const useNotificationStore = defineStore('notifications', {
  state: () => ({
    notifications: [],
    unreadCount: 0,
    isLoading: false,
    error: null,
    hasNewNotifications: false,
    seenNotificationIds: [],
    pollInterval: null,
  }),

  getters: {
    /**
     * Get display count for badge (99+ for large numbers)
     */
    displayCount: (state) => {
      return state.unreadCount > 99 ? '99+' : state.unreadCount
    },

    /**
     * Check if there are unseen notifications
     */
    hasUnseen: (state) => {
      return state.notifications.some(n => !state.seenNotificationIds.includes(n.id))
    },
  },

  actions: {
    /**
     * Initialize store - load seen IDs from localStorage
     */
    initialize() {
      this.loadSeenIds()
    },

    /**
     * Load seen notification IDs from localStorage
     */
    loadSeenIds() {
      try {
        const stored = localStorage.getItem('seen_notification_ids')
        if (stored) {
          this.seenNotificationIds = JSON.parse(stored)
        }
      } catch (error) {
        console.error('Error loading seen notification IDs:', error)
        this.seenNotificationIds = []
      }
    },

    /**
     * Save seen notification IDs to localStorage
     */
    saveSeenIds() {
      try {
        localStorage.setItem('seen_notification_ids', JSON.stringify(this.seenNotificationIds))
      } catch (error) {
        console.error('Error saving seen notification IDs:', error)
      }
    },

    /**
     * Mark notification IDs as seen
     */
    markAsSeen(notificationIds) {
      const idsArray = Array.isArray(notificationIds) ? notificationIds : [notificationIds]
      
      idsArray.forEach(id => {
        if (!this.seenNotificationIds.includes(id)) {
          this.seenNotificationIds.push(id)
        }
      })
      
      this.saveSeenIds()
      this.checkForNewNotifications()
    },

    /**
     * Check if there are new (unseen) notifications
     */
    checkForNewNotifications() {
      const unseenNotifications = this.notifications.filter(
        n => !this.seenNotificationIds.includes(n.id)
      )
      
      if (unseenNotifications.length > 0) {
        this.hasNewNotifications = true
      } else {
        this.hasNewNotifications = false
      }
    },

    /**
     * Fetch unread notifications
     */
    async fetchUnreadNotifications() {
      try {
        this.isLoading = true
        this.error = null
        const response = await axios.get('/api/notifications/unread')
        
        const previousIds = this.notifications.map(n => n.id)
        const newNotifications = response.data.data
        
        // Check if there are truly new notifications (not in previous list)
        const hasNewOnes = newNotifications.some(
          n => !previousIds.includes(n.id) && !this.seenNotificationIds.includes(n.id)
        )
        
        this.notifications = newNotifications
        
        if (hasNewOnes) {
          this.hasNewNotifications = true
        }
        
        return newNotifications
      } catch (err) {
        this.error = err.message
        console.error('Error fetching unread notifications:', err)
        return []
      } finally {
        this.isLoading = false
      }
    },

    /**
     * Fetch unread notification count
     */
    async fetchUnreadCount() {
      try {
        const response = await axios.get('/api/notifications/unread-count')
        this.unreadCount = response.data.count
        return response.data.count
      } catch (err) {
        console.error('Error fetching unread count:', err)
        return 0
      }
    },

    /**
     * Mark a notification as read
     */
    async markAsRead(notificationId) {
      try {
        await axios.patch(`/api/notifications/${notificationId}/mark-read`)
        
        // Update local state
        const notification = this.notifications.find(n => n.id === notificationId)
        if (notification && !notification.read_at) {
          notification.read_at = new Date().toISOString()
          this.unreadCount = Math.max(0, this.unreadCount - 1)
        }
        
        return true
      } catch (err) {
        console.error('Error marking notification as read:', err)
        return false
      }
    },

    /**
     * Mark all notifications as read
     */
    async markAllAsRead() {
      try {
        await axios.patch('/api/notifications/mark-all-read')
        
        // Update local state
        this.notifications.forEach(notification => {
          if (!notification.read_at) {
            notification.read_at = new Date().toISOString()
          }
        })
        this.unreadCount = 0
        
        return true
      } catch (err) {
        console.error('Error marking all notifications as read:', err)
        return false
      }
    },

    /**
     * Delete a notification
     */
    async deleteNotification(notificationId) {
      try {
        await axios.delete(`/api/notifications/${notificationId}`)
        
        // Update local state
        const index = this.notifications.findIndex(n => n.id === notificationId)
        if (index !== -1) {
          const notification = this.notifications[index]
          if (!notification.read_at) {
            this.unreadCount = Math.max(0, this.unreadCount - 1)
          }
          this.notifications.splice(index, 1)
        }
        
        return true
      } catch (err) {
        console.error('Error deleting notification:', err)
        return false
      }
    },

    /**
     * Delete all read notifications
     */
    async deleteAllRead() {
      try {
        await axios.delete('/api/notifications/delete-all-read')
        
        // Update local state
        this.notifications = this.notifications.filter(n => !n.read_at)
        
        return true
      } catch (err) {
        console.error('Error deleting all read notifications:', err)
        return false
      }
    },

    /**
     * Start polling for new notifications
     */
    startPolling(intervalMs = 30000) {
      this.stopPolling()
      
      // Initial fetch
      this.fetchUnreadCount()
      this.fetchUnreadNotifications()
      
      // Poll for updates
      this.pollInterval = setInterval(() => {
        this.fetchUnreadCount()
        this.fetchUnreadNotifications()
      }, intervalMs)
    },

    /**
     * Stop polling for notifications
     */
    stopPolling() {
      if (this.pollInterval) {
        clearInterval(this.pollInterval)
        this.pollInterval = null
      }
    },

    /**
     * Dismiss new notification alert
     */
    dismissNewAlert() {
      // Mark all current notifications as seen
      const currentIds = this.notifications.map(n => n.id)
      this.markAsSeen(currentIds)
      this.hasNewNotifications = false
    },
  },
})

