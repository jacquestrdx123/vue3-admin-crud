import { usePage } from '@inertiajs/vue3'

/**
 * Composable to help preserve query parameters (especially presets) when making Inertia requests
 * @returns {Function} Function to get current URL params as an object
 */
export function usePreserveQueryParams() {
  const page = usePage()

  /**
   * Get current URL query parameters as an object
   * @returns {Object} Current query parameters
   */
  const getCurrentParams = () => {
    // Use window.location.search as it's more reliable than page.url for query params
    const queryString = window.location.search
    if (!queryString) {
      return {}
    }

    try {
      const params = new URLSearchParams(queryString)
      const result = {}

      // Convert URLSearchParams to object, handling arrays for presets
      for (const [key, value] of params.entries()) {
        if (key === 'presets' || key.startsWith('presets[')) {
          // Presets might be in the URL multiple times (presets[]=x&presets[]=y)
          // or as a single value (presets=x)
          if (!result.presets) {
            result.presets = []
          }
          if (Array.isArray(result.presets)) {
            result.presets.push(value)
          }
        } else {
          result[key] = value
        }
      }

      // Deduplicate presets array
      if (result.presets && Array.isArray(result.presets)) {
        result.presets = [...new Set(result.presets)]
      }

      return result
    } catch (error) {
      console.error('Failed to parse URL params:', error)
      return {}
    }
  }

  return {
    getCurrentParams
  }
}

