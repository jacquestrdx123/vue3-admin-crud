<template>
  <div class="flex flex-col gap-1">
    <label v-if="label" class="text-sm font-medium text-gray-700 dark:text-gray-300">
      {{ label }}
    </label>
    <select
      :value="modelValue"
      @change="$emit('update:modelValue', $event.target.value)"
      :disabled="loading || !dependentValue"
      class="block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
    >
      <option value="">
        {{ loading ? 'Loading...' : (dependentValue ? (Object.keys(dynamicOptions).length > 0 ? 'All' : 'No options available') : 'Select a ' + dependsOnLabel + ' first') }}
      </option>
      <option
        v-for="(label, value) in dynamicOptions"
        :key="value"
        :value="value"
      >
        {{ label }}
      </option>
    </select>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import axios from 'axios'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  label: {
    type: String,
    default: ''
  },
  name: {
    type: String,
    required: true
  },
  dependsOn: {
    type: String,
    default: ''
  },
  dependsOnLabel: {
    type: String,
    default: 'filter'
  },
  dependentValue: {
    type: [String, Number],
    default: null
  },
  filterOptionsRoute: {
    type: String,
    default: ''
  },
  options: {
    type: Object,
    default: () => ({})
  }
})

// Debug log to see what props are received
console.log('DependentSelectFilter props:', {
  name: props.name,
  dependsOn: props.dependsOn,
  filterOptionsRoute: props.filterOptionsRoute,
  dependentValue: props.dependentValue
})

const emit = defineEmits(['update:modelValue'])

const loading = ref(false)
const dynamicOptions = ref({})

// Watch for changes in the dependent filter
watch(() => props.dependentValue, async (newValue) => {
  console.log('Dependent filter changed:', {
    filter: props.name,
    dependsOn: props.dependsOn,
    newValue: newValue
  })

  if (!newValue) {
    dynamicOptions.value = {}
    emit('update:modelValue', '')
    return
  }

  // Fetch new options based on the dependent value
  loading.value = true
  try {
    const params = {
      filter: props.name,
      depends_on: props.dependsOn,
      [props.dependsOn]: newValue
    }

    console.log('Fetching filter options with params:', params)
    console.log('Route:', route(props.filterOptionsRoute))

    const response = await axios.get(route(props.filterOptionsRoute), { params })
    
    console.log('Filter options response:', response.data)
    
    dynamicOptions.value = response.data.options || {}

    console.log('Dynamic options set to:', dynamicOptions.value)

    // Clear the current value if it's not in the new options
    if (props.modelValue && !dynamicOptions.value[props.modelValue]) {
      emit('update:modelValue', '')
    }
  } catch (error) {
    console.error('Error fetching filter options:', error)
    console.error('Error response:', error.response)
    dynamicOptions.value = {}
  } finally {
    loading.value = false
  }
}, { immediate: true })
</script>

