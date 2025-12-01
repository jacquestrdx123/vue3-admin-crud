<template>
  <Modal
    :show="show"
    title="Sync Roles & Permissions from Production"
    max-width="2xl"
    :closable="!syncing"
    :close-on-backdrop="!syncing"
    @close="handleClose"
  >
    <!-- Configuration Form -->
    <div v-if="!syncing && !syncResult" class="space-y-4">
      <div>
        <label for="endpoint" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Endpoint URL <span class="text-red-500">*</span>
        </label>
        <input
          id="endpoint"
          v-model="config.endpoint"
          type="text"
          placeholder="https://accounts.myciba.org"
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
          :disabled="syncing"
        />
        <p v-if="errors.endpoint" class="mt-1 text-sm text-red-600 dark:text-red-400">
          {{ errors.endpoint }}
        </p>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
          Enter the base URL of the remote system (e.g., https://accounts.myciba.org)
        </p>
      </div>

      <div>
        <label for="secret" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Secret (Optional)
        </label>
        <input
          id="secret"
          v-model="config.secret"
          type="password"
          placeholder="Enter secret for export endpoint"
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
          :disabled="syncing"
        />
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
          Optional secret for accessing the export endpoint
        </p>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="syncing" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
      <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
        {{ syncStatus }}
      </p>
    </div>

    <!-- Error State -->
    <div v-if="syncResult && !syncResult.success" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-4">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
            Sync Failed
          </h3>
          <div class="mt-2 text-sm text-red-700 dark:text-red-300">
            <p>{{ syncResult.error }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Success State -->
    <div v-if="syncResult && syncResult.success" class="space-y-4">
      <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-green-800 dark:text-green-200">
              Sync completed successfully!
            </h3>
          </div>
        </div>
      </div>

      <div v-if="syncResult.stats" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Metric
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Count
              </th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <tr>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                Permissions Created
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                {{ syncResult.stats.permissions_created || 0 }}
              </td>
            </tr>
            <tr>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                Permissions Updated
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                {{ syncResult.stats.permissions_updated || 0 }}
              </td>
            </tr>
            <tr>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                Roles Created
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                {{ syncResult.stats.roles_created || 0 }}
              </td>
            </tr>
            <tr>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                Roles Updated
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                {{ syncResult.stats.roles_updated || 0 }}
              </td>
            </tr>
            <tr>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                User Roles Synced
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                {{ syncResult.stats.user_roles_synced || 0 }}
              </td>
            </tr>
            <tr v-if="syncResult.stats.menu_groups_created !== undefined || syncResult.stats.menu_groups_updated !== undefined">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                Menu Groups Created
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                {{ syncResult.stats.menu_groups_created || 0 }}
              </td>
            </tr>
            <tr v-if="syncResult.stats.menu_groups_created !== undefined || syncResult.stats.menu_groups_updated !== undefined">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                Menu Groups Updated
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                {{ syncResult.stats.menu_groups_updated || 0 }}
              </td>
            </tr>
            <tr v-if="syncResult.stats.menu_items_created !== undefined || syncResult.stats.menu_items_updated !== undefined">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                Menu Items Created
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                {{ syncResult.stats.menu_items_created || 0 }}
              </td>
            </tr>
            <tr v-if="syncResult.stats.menu_items_created !== undefined || syncResult.stats.menu_items_updated !== undefined">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                Menu Items Updated
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                {{ syncResult.stats.menu_items_updated || 0 }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <template #footer>
      <div class="flex justify-end gap-2">
        <Button
          v-if="!syncing && !syncResult"
          variant="outline"
          @click="handleClose"
        >
          Cancel
        </Button>
        <Button
          v-if="!syncing && !syncResult"
          variant="primary"
          :disabled="!canProceed"
          @click="handleSync"
        >
          Sync
        </Button>
        <Button
          v-if="syncResult && (syncResult.success || !syncing)"
          variant="primary"
          @click="handleComplete"
        >
          {{ syncResult?.success ? 'Done' : 'Close' }}
        </Button>
      </div>
    </template>
  </Modal>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import axios from 'axios'
import Modal from '@/Components/UI/Modal.vue'
import Button from '@/Components/UI/Button.vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'success'])

const config = ref({
  endpoint: 'https://accounts.myciba.org',
  secret: ''
})

const errors = ref({})
const syncing = ref(false)
const syncStatus = ref('')
const syncResult = ref(null)

const canProceed = computed(() => {
  return config.value.endpoint && config.value.endpoint.trim() !== '' && !errors.value.endpoint
})

const validateEndpoint = () => {
  errors.value = {}
  if (!config.value.endpoint || config.value.endpoint.trim() === '') {
    errors.value.endpoint = 'Endpoint URL is required'
    return false
  }
  
  try {
    const url = new URL(config.value.endpoint)
    if (!['http:', 'https:'].includes(url.protocol)) {
      errors.value.endpoint = 'Endpoint must use HTTP or HTTPS protocol'
      return false
    }
  } catch (e) {
    errors.value.endpoint = 'Please enter a valid URL'
    return false
  }
  
  return true
}

const handleSync = async () => {
  if (!validateEndpoint()) {
    return
  }

  syncing.value = true
  syncStatus.value = 'Fetching data from remote system...'
  syncResult.value = null

  try {
    // Use proxy endpoint to fetch data from remote system (avoids CORS issues)
    const fetchResponse = await axios.post(route('api.fetch.roles-permissions'), {
      endpoint: config.value.endpoint.trim(),
      secret: config.value.secret?.trim() || null
    })

    if (!fetchResponse.data.success) {
      throw new Error(fetchResponse.data.message || fetchResponse.data.error || 'Failed to fetch data from remote system')
    }

    const remoteData = fetchResponse.data.data

    if (!remoteData) {
      throw new Error('Invalid response from remote system')
    }

    // Validate the response structure
    if (!remoteData.roles || !Array.isArray(remoteData.roles)) {
      throw new Error('Invalid data structure: roles array is missing')
    }
    if (!remoteData.permissions || !Array.isArray(remoteData.permissions)) {
      throw new Error('Invalid data structure: permissions array is missing')
    }
    if (!remoteData.user_roles || !Array.isArray(remoteData.user_roles)) {
      throw new Error('Invalid data structure: user_roles array is missing')
    }

    syncStatus.value = 'Importing data to local system...'

    // Import the data locally
    const importResponse = await axios.post(route('api.import.roles-permissions'), {
      roles: remoteData.roles,
      permissions: remoteData.permissions,
      user_roles: remoteData.user_roles,
      menu_groups: remoteData.menu_groups || [],
      menu_items: remoteData.menu_items || []
    })

    if (importResponse.data.success) {
      syncResult.value = {
        success: true,
        stats: importResponse.data.stats || {}
      }
    } else {
      throw new Error(importResponse.data.message || 'Import failed')
    }
  } catch (error) {
    console.error('Sync error:', error)
    console.error('Error details:', {
      response: error.response?.data,
      status: error.response?.status,
      message: error.message
    })
    
    let errorMessage = 'Failed to sync from production'
    
    if (error.response) {
      // Handle validation errors
      if (error.response.status === 422 && error.response.data?.errors) {
        const errors = Object.values(error.response.data.errors).flat()
        errorMessage = errors.join(', ') || error.response.data.message
      }
      // Handle authentication errors
      else if (error.response.status === 401 || error.response.status === 403) {
        errorMessage = 'Authentication failed. Please refresh the page and try again.'
      }
      // Handle fetch endpoint errors
      else if (error.response.data?.success === false) {
        errorMessage = error.response.data.message || error.response.data.error || 'Failed to fetch data from remote system'
      }
      // Generic response error
      else {
        errorMessage = error.response.data?.message || `Status ${error.response.status}: ${error.response.statusText || 'Failed to fetch/import data'}`
      }
    } else if (error.request) {
      errorMessage = 'No response from server. Please check the endpoint URL and your connection, then try again.'
    } else if (error.message) {
      errorMessage = error.message
    }
    
    syncResult.value = {
      success: false,
      error: errorMessage
    }
  } finally {
    syncing.value = false
    syncStatus.value = ''
  }
}

const handleComplete = () => {
  if (syncResult.value?.success) {
    emit('success')
    handleClose()
    router.reload()
  } else {
    handleClose()
  }
}

const handleClose = () => {
  if (syncing.value) {
    return
  }
  resetModal()
  emit('close')
}

const resetModal = () => {
  config.value = {
    endpoint: 'https://accounts.myciba.org',
    secret: ''
  }
  errors.value = {}
  syncing.value = false
  syncStatus.value = ''
  syncResult.value = null
}

// Reset when modal is closed
watch(() => props.show, (newValue) => {
  if (!newValue) {
    resetModal()
  }
})
</script>

