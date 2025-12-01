import { ref, computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import axios from 'axios'
import { useToast } from './useToast.js'

axios.defaults.withCredentials = true

const activeFavorite = ref(null)
const isLoading = ref(false)

export function useFavoriteMenus() {
  const page = usePage()
  const toast = useToast()
  const csrfToken = computed(() => page?.props?.csrf_token ?? '')

  const withCsrf = (config = {}) => ({
    withCredentials: true,
    ...config,
    headers: {
      'X-CSRF-TOKEN': csrfToken.value,
      'X-Requested-With': 'XMLHttpRequest',
      ...(config.headers ?? {}),
    },
  })

  // Get favorites from Inertia shared props
  const initialFavorites = computed(() => page?.props?.favoriteMenus || [])

  const isFavorited = (menuKey) => {
    // Always check against the live initialFavorites to ensure reactivity
    return initialFavorites.value.some(fav => fav.menu_key === menuKey)
  }

  const addFavorite = async (menuKey) => {
    if (isFavorited(menuKey)) {
      toast.info('Menu item is already in favorites')
      return false
    }

    isLoading.value = true

    try {
      await axios.post('/api/user/favorite-menus', { menu_key: menuKey }, withCsrf())
      
      toast.success('Added to favorites')
      
      // Reload the page to refresh the menu
      router.reload({ only: ['favoriteMenus', 'menu'] })
      
      return true
    } catch (error) {
      console.error('Failed to add favorite:', error)
      toast.danger('Failed to add favorite')
      return false
    } finally {
      isLoading.value = false
    }
  }

  const removeFavorite = async (menuKey) => {
    isLoading.value = true

    try {
      await axios.delete(`/api/user/favorite-menus/${menuKey}`, withCsrf())
      
      toast.success('Removed from favorites')
      
      // Clear active favorite if it's the one being removed
      if (activeFavorite.value?.menu_key === menuKey) {
        activeFavorite.value = null
      }
      
      // Reload the page to refresh the menu
      router.reload({ only: ['favoriteMenus', 'menu'] })
      
      return true
    } catch (error) {
      console.error('Failed to remove favorite:', error)
      toast.danger('Failed to remove favorite')
      return false
    } finally {
      isLoading.value = false
    }
  }

  const toggleFavorite = async (menuKey) => {
    if (isFavorited(menuKey)) {
      return await removeFavorite(menuKey)
    } else {
      return await addFavorite(menuKey)
    }
  }

  const setActiveFavorite = (favorite) => {
    // If clicking the same favorite, toggle it off
    if (activeFavorite.value?.menu_key === favorite?.menu_key) {
      activeFavorite.value = null
    } else {
      activeFavorite.value = favorite
    }
  }

  const clearActiveFavorite = () => {
    activeFavorite.value = null
  }

  const reorderFavorites = async (menuKeys) => {
    isLoading.value = true

    try {
      await axios.put('/api/user/favorite-menus/reorder', { menu_keys: menuKeys }, withCsrf())
      
      // Reload the page to refresh the menu
      router.reload({ only: ['favoriteMenus'] })
      
      return true
    } catch (error) {
      console.error('Failed to reorder favorites:', error)
      toast.danger('Failed to reorder favorites')
      return false
    } finally {
      isLoading.value = false
    }
  }

  const clearAllFavorites = async () => {
    isLoading.value = true

    try {
      await axios.delete('/api/user/favorite-menus/all', withCsrf())
      
      toast.success('All favorites cleared')
      
      // Clear active favorite
      activeFavorite.value = null
      
      // Reload the page to refresh the menu
      router.reload({ only: ['favoriteMenus', 'menu'] })
      
      return true
    } catch (error) {
      console.error('Failed to clear favorites:', error)
      toast.danger('Failed to clear favorites')
      return false
    } finally {
      isLoading.value = false
    }
  }

  return {
    favoriteMenus: computed(() => initialFavorites.value),
    activeFavorite,
    isLoading,
    isFavorited,
    addFavorite,
    removeFavorite,
    toggleFavorite,
    setActiveFavorite,
    clearActiveFavorite,
    reorderFavorites,
    clearAllFavorites,
  }
}

