<template>
  <div :class="columnClass" class="flex flex-col gap-3">
    <!-- Type Select -->
    <div class="flex flex-col gap-1 w-full">
      <label v-if="typeField?.label" class="text-sm font-medium text-gray-700 dark:text-gray-300">
        {{ typeField.label }}
        <span v-if="typeField.required" class="text-red-500">*</span>
      </label>
      <select
        :value="formData?.subscribable_type || ''"
        @change="handleTypeChange"
        :required="typeField?.required"
        :disabled="disabled"
        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
        :class="{
          'border-red-300': hasTypeError,
          'dark:border-red-600': hasTypeError
        }"
      >
        <option value="">{{ typeField?.placeholder || 'Select a product type...' }}</option>
        <option
          v-for="(optionLabel, value) in typeField?.options"
          :key="value"
          :value="value"
        >
          {{ optionLabel }}
        </option>
      </select>
      <div v-if="hasTypeError" class="text-sm text-red-600 dark:text-red-400">
        {{ typeError }}
      </div>
    </div>

    <!-- ID Select (AJAX loaded) -->
    <div class="flex flex-col gap-1 w-full">
      <label v-if="idField?.label" class="text-sm font-medium text-gray-700 dark:text-gray-300">
        {{ idField.label }}
        <span v-if="idField.required" class="text-red-500">*</span>
      </label>
      <select
        :value="formData?.subscribable_id || ''"
        @change="handleIdChange"
        :required="idField?.required"
        :disabled="loading || !formData?.subscribable_type || disabled"
        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
        :class="{
          'border-red-300': hasIdError,
          'dark:border-red-600': hasIdError
        }"
      >
        <option value="">
          {{ loading ? 'Loading...' : (formData?.subscribable_type ? (Object.keys(subscribableOptions).length > 0 ? (idField?.placeholder || 'Select a product...') : 'No options available') : 'Select type first') }}
        </option>
        <option
          v-for="(optionLabel, value) in subscribableOptions"
          :key="value"
          :value="value"
        >
          {{ optionLabel }}
        </option>
      </select>
      <div v-if="hasIdError" class="text-sm text-red-600 dark:text-red-400">
        {{ idError }}
      </div>
      <div v-if="hint && !hasIdError" class="text-sm text-gray-500 dark:text-gray-400">
        {{ hint }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  modelValue: {
    type: [String, Number, Boolean, Object],
    default: null
  },
  name: {
    type: String,
    required: true
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: ''
  },
  required: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  columnSpan: {
    type: Number,
    default: 12
  },
  errorMessages: {
    type: [String, Array],
    default: () => []
  },
  hint: {
    type: String,
    default: ''
  },
  formData: {
    type: Object,
    default: () => ({})
  },
  fields: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['update:modelValue'])

const loading = ref(false)
const subscribableOptions = ref({})

const typeField = computed(() => props.fields?.subscribable_type)
const idField = computed(() => props.fields?.subscribable_id)

const typeError = computed(() => {
  if (Array.isArray(props.errorMessages)) {
    return props.errorMessages.find(msg => typeof msg === 'string' && msg.includes('subscribable_type')) || ''
  }
  return typeof props.errorMessages === 'string' && props.errorMessages.includes('subscribable_type') ? props.errorMessages : ''
})

const idError = computed(() => {
  if (Array.isArray(props.errorMessages)) {
    return props.errorMessages.find(msg => typeof msg === 'string' && msg.includes('subscribable_id')) || ''
  }
  return typeof props.errorMessages === 'string' && props.errorMessages.includes('subscribable_id') ? props.errorMessages : ''
})

const hasTypeError = computed(() => !!typeError.value)
const hasIdError = computed(() => !!idError.value)

const columnClass = computed(() => {
  const span = props.columnSpan
  if (span === 12) return 'col-span-12'
  if (span === 6) return 'col-span-12 md:col-span-6'
  if (span === 4) return 'col-span-12 md:col-span-4'
  if (span === 3) return 'col-span-12 md:col-span-3'
  return `col-span-${span}`
})

// Initialize formData fields if they don't exist
onMounted(() => {
  if (props.formData) {
    if (props.formData.subscribable_type === undefined) {
      props.formData.subscribable_type = ''
    }
    if (props.formData.subscribable_id === undefined) {
      props.formData.subscribable_id = ''
    }
  }
})

const handleTypeChange = (event) => {
  const newType = event.target.value || ''
  
  // Update formData directly (it's reactive)
  if (props.formData) {
    props.formData.subscribable_type = newType
    props.formData.subscribable_id = '' // Reset ID when type changes
  }
  
  // Also emit modelValue update for compatibility
  emit('update:modelValue', {
    subscribable_type: newType,
    subscribable_id: ''
  })
}

const handleIdChange = (event) => {
  const newId = event.target.value || ''
  
  // Update formData directly (it's reactive)
  if (props.formData) {
    props.formData.subscribable_id = newId
  }
  
  // Also emit modelValue update for compatibility
  emit('update:modelValue', {
    subscribable_type: props.formData?.subscribable_type || '',
    subscribable_id: newId
  })
}

// Watch for type changes to load options
watch(() => props.formData?.subscribable_type, async (newType) => {
  if (!newType || newType === '') {
    subscribableOptions.value = {}
    return
  }

  loading.value = true
  try {
    const ajaxUrl = idField.value?.ajax_url || route('api.subscribables.index')
    const response = await axios.get(ajaxUrl, {
      params: { type: newType }
    })
    
    // Transform array response [{value, label}] to object {id: "name"} format
    if (Array.isArray(response.data)) {
      subscribableOptions.value = response.data.reduce((acc, item) => {
        const id = item.value !== undefined ? item.value : item.id
        const name = item.label !== undefined ? item.label : item.name
        if (id !== undefined && name !== undefined) {
          acc[id] = name
        }
        return acc
      }, {})
    } else if (response.data && typeof response.data === 'object') {
      // Already in object format
      subscribableOptions.value = response.data
    } else {
      subscribableOptions.value = {}
    }
    
    // Reset ID if current ID is not in new options
    if (props.formData?.subscribable_id && !subscribableOptions.value[props.formData.subscribable_id]) {
      if (props.formData) {
        props.formData.subscribable_id = ''
      }
      
      emit('update:modelValue', {
        subscribable_type: props.formData?.subscribable_type || '',
        subscribable_id: ''
      })
    }
  } catch (error) {
    console.error('Error fetching subscribable options:', error)
    subscribableOptions.value = {}
  } finally {
    loading.value = false
  }
}, { immediate: true })
</script>

