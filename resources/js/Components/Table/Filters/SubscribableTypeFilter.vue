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
  internalValue.subscribable_type = event.target.value
  internalValue.subscribable_id = '' // Reset ID when type changes
  emit('update:modelValue', { ...internalValue })
}

const handleIdChange = (event) => {
  internalValue.subscribable_id = event.target.value
  emit('update:modelValue', { ...internalValue })
}

// Watch for type changes to load options
watch(() => internalValue.subscribable_type, async (newType) => {
  if (!newType) {
    subscribableOptions.value = {}
    return
  }

  loading.value = true
  try {
    const ajaxUrl = props.fields.subscribable_id?.ajax_url || '/api/subscribables'
    const response = await axios.get(ajaxUrl, {
      params: { type: newType }
    })
    
    subscribableOptions.value = response.data || {}
  } catch (error) {
    console.error('Error fetching subscribable options:', error)
    subscribableOptions.value = {}
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

