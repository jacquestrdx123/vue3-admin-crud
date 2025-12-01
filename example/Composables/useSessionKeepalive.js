import { computed, onMounted, onUnmounted } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import axios from 'axios'

axios.defaults.withCredentials = true

/**
 * Composable for session keepalive to prevent 419 CSRF token expiration errors.
 * Pings the backend every 5 minutes to refresh the session.
 */
export function useSessionKeepalive() {
  const page = usePage()
  const csrfToken = computed(() => page?.props?.csrf_token ?? '')

  let keepaliveInterval = null
  let isLoggingOut = false

  const withCsrf = (config = {}) => ({
    withCredentials: true,
    ...config,
    headers: {
      'X-CSRF-TOKEN': csrfToken.value,
      'X-Requested-With': 'XMLHttpRequest',
      ...(config.headers ?? {}),
    },
  })

  /**
   * Force logout by calling the logout endpoint and redirecting to login.
   */
  const forceLogout = async () => {
    // Prevent multiple logout calls
    if (isLoggingOut) {
      return
    }

    isLoggingOut = true

    // Stop the keepalive interval
    stopKeepalive()

    try {
      // Try to call logout endpoint to clear server-side session
      // This will work even if session is expired (controller handles it gracefully)
      if (typeof route !== 'undefined') {
        const logoutUrl = route('vue.logout')
        if (logoutUrl) {
          // Use Inertia router to handle logout properly
          router.post(logoutUrl, {}, {
            onFinish: () => {
              // After logout, redirect to login page
              // The logout endpoint already redirects, but this ensures we get there
              const loginUrl = route('vue.login')
              if (loginUrl) {
                router.visit(loginUrl)
              }
            },
            onError: () => {
              // Even if logout fails, redirect to login
              const loginUrl = route('vue.login')
              if (loginUrl) {
                router.visit(loginUrl)
              }
            }
          })
          return
        }
      }
    } catch (error) {
      console.warn('Logout route not available:', error)
    }

    // Fallback: redirect to login page directly
    window.location.href = '/vue-admin/login'
  }

  /**
   * Check if the error indicates the user is no longer authenticated.
   */
  const isUnauthenticatedError = (error) => {
    if (!error.response) {
      // Network errors or other issues - don't treat as auth failure
      return false
    }

    const status = error.response.status

    // Check for 401 (Unauthorized) or 403 (Forbidden)
    if (status === 401 || status === 403) {
      return true
    }

    // Check for 302 redirect (common with web middleware when not authenticated)
    // Note: axios may not follow redirects for POST requests, so we check the status
    if (status === 302) {
      return true
    }

    return false
  }

  /**
   * Ping the backend to refresh the session and get a new CSRF token.
   */
  const pingKeepalive = async () => {
    try {
      const response = await axios.post('/api/session/keepalive', {}, withCsrf())
      
      // Update CSRF token in meta tag if returned
      if (response.data?.csrf_token) {
        const metaTag = document.querySelector('meta[name="csrf-token"]')
        if (metaTag) {
          metaTag.setAttribute('content', response.data.csrf_token)
        }
      }
    } catch (error) {
      // Check if this is an authentication error
      if (isUnauthenticatedError(error)) {
        console.warn('Session expired - forcing logout')
        await forceLogout()
        return
      }

      // Log other errors but don't show toasts - this is a silent background operation
      console.warn('Session keepalive failed:', error)
    }
  }

  /**
   * Start the keepalive interval.
   */
  const startKeepalive = () => {
    // Clear any existing interval
    if (keepaliveInterval) {
      clearInterval(keepaliveInterval)
    }

    // Ping immediately on start
    pingKeepalive()

    // Then ping every 5 minutes (300,000ms)
    keepaliveInterval = setInterval(() => {
      pingKeepalive()
    }, 300000)
  }

  /**
   * Stop the keepalive interval.
   */
  const stopKeepalive = () => {
    if (keepaliveInterval) {
      clearInterval(keepaliveInterval)
      keepaliveInterval = null
    }
  }

  onMounted(() => {
    startKeepalive()
  })

  onUnmounted(() => {
    stopKeepalive()
  })

  return {
    startKeepalive,
    stopKeepalive,
    pingKeepalive,
  }
}

