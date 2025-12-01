<template>
  <form @submit.prevent="handleSubmit" class="space-y-6">
    <div class="grid grid-cols-12 gap-4">
      <slot :form="formData" :errors="errors" />
    </div>

    <div v-if="!hideActions" class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
      <button
        v-if="!hideCancel"
        type="button"
        @click="handleCancel"
        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700"
      >
        {{ cancelLabel }}
      </button>

      <button
        type="submit"
        :disabled="processing"
        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <span v-if="processing" class="flex items-center gap-2">
          <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ processingLabel }}
        </span>
        <span v-else>{{ submitLabel }}</span>
      </button>
    </div>
  </form>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  initialData: {
    type: Object,
    default: () => ({})
  },
  submitUrl: {
    type: String,
    default: null
  },
  method: {
    type: String,
    default: 'post'
  },
  submitLabel: {
    type: String,
    default: 'Save'
  },
  cancelLabel: {
    type: String,
    default: 'Cancel'
  },
  processingLabel: {
    type: String,
    default: 'Saving...'
  },
  hideActions: {
    type: Boolean,
    default: false
  },
  hideCancel: {
    type: Boolean,
    default: false
  },
  preserveScroll: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['submit', 'cancel', 'success', 'error'])

const formData = reactive({ ...props.initialData })
const errors = ref({})
const processing = ref(false)

const handleSubmit = () => {
  if (props.submitUrl) {
    processing.value = true
    
    router[props.method](props.submitUrl, formData, {
      preserveScroll: props.preserveScroll,
      onSuccess: (page) => {
        processing.value = false
        errors.value = {}
        emit('success', page)
      },
      onError: (pageErrors) => {
        processing.value = false
        errors.value = pageErrors
        emit('error', pageErrors)
      },
      onFinish: () => {
        processing.value = false
      }
    })
  } else {
    emit('submit', formData)
  }
}

const handleCancel = () => {
  emit('cancel')
}

defineExpose({
  formData,
  errors,
  processing,
  submit: handleSubmit,
  setErrors: (newErrors) => {
    errors.value = newErrors
  },
  clearErrors: () => {
    errors.value = {}
  }
})
</script>

