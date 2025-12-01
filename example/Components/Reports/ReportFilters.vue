<template>
  <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg border border-gray-200 dark:border-gray-700 p-6">
    <div class="mb-4">
      <h3 class="flex gap-2 items-center text-lg font-bold text-gray-900 dark:text-white">
        <span class="material-icons text-ciba-green">filter_list</span>
        {{ title }}
      </h3>
      <p v-if="description" class="text-sm text-gray-600 dark:text-gray-400">
        {{ description }}
      </p>
    </div>
    
    <form @submit.prevent="handleSubmit">
      <slot />
      
      <div class="flex justify-end gap-3 pt-4 mt-4 border-t border-gray-200 dark:border-gray-700">
        <button
          v-if="showReset"
          type="button"
          @click="handleReset"
          class="px-6 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600"
        >
          Reset
        </button>
        <button
          type="submit"
          :disabled="loading"
          class="px-6 py-2 text-sm font-semibold text-black bg-ciba-green rounded-lg hover:bg-ciba-green/90 disabled:opacity-50"
        >
          <span v-if="!loading">{{ submitText }}</span>
          <span v-else class="flex items-center gap-2">
            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ loadingText }}
          </span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
const props = defineProps({
  title: {
    type: String,
    default: 'Report Filters'
  },
  description: {
    type: String,
    default: null
  },
  loading: {
    type: Boolean,
    default: false
  },
  submitText: {
    type: String,
    default: 'Apply Filters'
  },
  loadingText: {
    type: String,
    default: 'Applying...'
  },
  showReset: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['submit', 'reset'])

const handleSubmit = () => {
  emit('submit')
}

const handleReset = () => {
  emit('reset')
}
</script>

