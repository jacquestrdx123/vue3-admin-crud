<template>
  <button
    @click="handleExport"
    :disabled="loading || disabled"
    :class="[
      'inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white',
      loading || disabled
        ? 'bg-gray-400 cursor-not-allowed'
        : 'bg-ciba-green hover:bg-ciba-green/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-ciba-green'
    ]"
  >
    <svg
      v-if="!loading"
      class="h-5 w-5 mr-2"
      fill="none"
      stroke="currentColor"
      viewBox="0 0 24 24"
    >
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
      />
    </svg>
    <svg
      v-else
      class="animate-spin h-5 w-5 mr-2"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle
        class="opacity-25"
        cx="12"
        cy="12"
        r="10"
        stroke="currentColor"
        stroke-width="4"
      ></circle>
      <path
        class="opacity-75"
        fill="currentColor"
        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
      ></path>
    </svg>
    <span>{{ loading ? loadingText : buttonText }}</span>
  </button>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({
  exportUrl: {
    type: String,
    required: true
  },
  exportData: {
    type: Object,
    default: () => ({})
  },
  buttonText: {
    type: String,
    default: 'Export to Excel'
  },
  loadingText: {
    type: String,
    default: 'Exporting...'
  },
  disabled: {
    type: Boolean,
    default: false
  },
  method: {
    type: String,
    default: 'post',
    validator: (value) => ['post', 'get'].includes(value)
  }
})

const emit = defineEmits(['export-started', 'export-complete', 'export-error'])

const loading = ref(false)

const handleExport = async () => {
  if (loading.value || props.disabled) return

  loading.value = true
  emit('export-started')

  try {
    const response = await axios({
      method: props.method,
      url: props.exportUrl,
      data: props.method === 'post' ? props.exportData : undefined,
      params: props.method === 'get' ? props.exportData : undefined
    })

    // Show success notification
    if (window.$toast) {
      window.$toast.success('Export started successfully. You will be notified when ready.')
    }

    emit('export-complete', response.data)
  } catch (error) {
    console.error('Export error:', error)
    
    // Show error notification
    if (window.$toast) {
      window.$toast.error(error.response?.data?.message || 'Export failed. Please try again.')
    }
    
    emit('export-error', error)
  } finally {
    loading.value = false
  }
}
</script>

