<template>
  <div class="flex flex-col gap-2">
    <!-- Type Select -->
    <div class="flex flex-col gap-1">
      <label v-if="fields.subscribable_type?.label" class="text-sm font-medium text-gray-700 dark:text-gray-300">
        {{ fields.subscribable_type.label }}
      </label>
      <select
        :value="internalValue.subscribable_type"
        @change="handleTypeChange"
        class="block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
      >
        <option value="">All</option>
        <option
          v-for="(optionLabel, value) in fields.subscribable_type?.options"
          :key="value"
          :value="value"
        >
          {{ optionLabel }}
        </option>
      </select>
    </div>

    <!-- ID Select (AJAX loaded) -->
    <div class="flex flex-col gap-1">
      <label v-if="fields.subscribable_id?.label" class="text-sm font-medium text-gray-700 dark:text-gray-300">
        {{ fields.subscribable_id.label }}
      </label>
      <select
        :value="internalValue.subscribable_id"
        @change="handleIdChange"
        :disabled="loading || !internalValue.subscribable_type"
        class="block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <option value="">
          {{ loading ? 'Loading...' : (internalValue.subscribable_type ? (Object.keys(subscribableOptions).length > 0 ? 'All' : 'No options available') : 'Select type first') }}
        </option>
        <option
          v-for="(optionLabel, value) in subscribableOptions"
          :key="value"
          :value="value"
        >
          {{ optionLabel }}
        </option>
      </select>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, reactive } from 'vue'
import axios from 'axios'

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({ subscribable_type: '', subscribable_id: '' })
  },
  name: {
    type: String,
    required: true
  },
  label: {
    type: String,
    default: ''
  },
  fields: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['update:modelValue'])

const loading = ref(false)
const subscribableOptions = ref({})
const internalValue = reactive({
  subscribable_type: props.modelValue?.subscribable_type || '',
  subscribable_id: props.modelValue?.subscribable_id || ''
})

const handleTypeChange = (event) => {
  const newType = event.target.value || ''
  internalValue.subscribable_type = newType
  internalValue.subscribable_id = '' // Reset ID when type changes
  subscribableOptions.value = {} // Clear options when type is cleared
  emit('update:modelValue', { 
    subscribable_type: newType, 
    subscribable_id: '' 
  })
}

const handleIdChange = (event) => {
  const newId = event.target.value || ''
  internalValue.subscribable_id = newId
  emit('update:modelValue', { 
    subscribable_type: internalValue.subscribable_type, 
    subscribable_id: newId 
  })
}

// Watch for type changes to load options
watch(() => internalValue.subscribable_type, async (newType) => {
  if (!newType || newType === '') {
    subscribableOptions.value = {}
    internalValue.subscribable_id = '' // Ensure ID is cleared when type is cleared
    return
  }

  loading.value = true
  try {
    const ajaxUrl = props.fields.subscribable_id?.ajax_url || route('api.subscribables.index')
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
    if (internalValue.subscribable_id && !subscribableOptions.value[internalValue.subscribable_id]) {
      internalValue.subscribable_id = ''
      emit('update:modelValue', { 
        subscribable_type: internalValue.subscribable_type, 
        subscribable_id: '' 
      })
    }
  } catch (error) {
    console.error('Error fetching subscribable options:', error)
    subscribableOptions.value = {}
    internalValue.subscribable_id = ''
    emit('update:modelValue', { 
      subscribable_type: internalValue.subscribable_type, 
      subscribable_id: '' 
    })
  } finally {
    loading.value = false
  }
}, { immediate: true })

// Watch for external changes to modelValue
watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    internalValue.subscribable_type = newValue.subscribable_type || ''
    internalValue.subscribable_id = newValue.subscribable_id || ''
  }
}, { deep: true })
</script>

