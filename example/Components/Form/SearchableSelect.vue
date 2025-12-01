<template>
  <div :class="[columnClass, 'relative', 'searchable-select-' + fieldId]">
    <label v-if="label" :for="fieldId" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    
    <div class="relative">
      <!-- Dropdown Button -->
      <button
        type="button"
        @click="toggleDropdown"
        :disabled="disabled"
        :class="[
          'relative w-full cursor-default rounded-lg bg-white dark:bg-gray-800 py-2 pl-3 pr-10 text-left',
          'border border-gray-300 dark:border-gray-600',
          'focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500',
          'disabled:bg-gray-100 dark:disabled:bg-gray-900 disabled:cursor-not-allowed',
          hasError ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''
        ]"
      >
        <span class="flex items-center">
          <span v-if="selectedOption" class="block truncate text-gray-900 dark:text-gray-100">
            {{ selectedOption.label }}
          </span>
          <span v-else class="block truncate text-gray-500 dark:text-gray-400">
            {{ placeholder || 'Select...' }}
          </span>
        </span>
        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
          <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 3a.75.75 0 01.55.24l3.25 3.5a.75.75 0 11-1.1 1.02L10 4.852 7.3 7.76a.75.75 0 01-1.1-1.02l3.25-3.5A.75.75 0 0110 3zm-3.76 9.2a.75.75 0 011.06.04l2.7 2.908 2.7-2.908a.75.75 0 111.1 1.02l-3.25 3.5a.75.75 0 01-1.1 0l-3.25-3.5a.75.75 0 01.04-1.06z" clip-rule="evenodd" />
          </svg>
        </span>
      </button>

      <!-- Dropdown Panel -->
      <transition
        enter-active-class="transition ease-out duration-100"
        enter-from-class="transform opacity-0 scale-95"
        enter-to-class="transform opacity-100 scale-100"
        leave-active-class="transition ease-in duration-75"
        leave-from-class="transform opacity-100 scale-100"
        leave-to-class="transform opacity-0 scale-95"
      >
      <div
        v-if="isOpen"
        class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white dark:bg-gray-800 py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm"
      >
          <!-- Search Input -->
          <div v-if="searchable" class="sticky top-0 z-10 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 p-2">
            <div class="relative">
              <input
                ref="searchInput"
                type="text"
                v-model="searchQuery"
                @input="onSearch"
                class="w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 pr-3 py-2 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500"
                style="padding-left: 2.75rem !important;"
                :placeholder="searchPlaceholder"
              />
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
              <div v-if="loading" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <svg class="animate-spin h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </div>
            </div>
          </div>

          <!-- Options List -->
          <div class="py-1">
            <template v-if="loading && filteredOptions.length === 0">
              <div class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400 text-center">
                Loading...
              </div>
            </template>
            
            <template v-else-if="filteredOptions.length === 0">
              <div class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400 text-center">
                {{ noResultsText }}
              </div>
            </template>
            
            <template v-else>
              <button
                v-for="option in filteredOptions"
                :key="option.value"
                type="button"
                @click="selectOption(option)"
                :class="[
                  'relative cursor-pointer select-none w-full text-left px-4 py-2',
                  'hover:bg-gray-100 dark:hover:bg-gray-700',
                  isSelected(option) ? 'bg-indigo-50 dark:bg-indigo-900/20' : ''
                ]"
              >
                <div class="flex items-center justify-between">
                  <span :class="[
                    'block truncate',
                    isSelected(option) ? 'font-semibold text-indigo-600 dark:text-indigo-400' : 'font-normal text-gray-900 dark:text-gray-100'
                  ]">
                    {{ option.label }}
                  </span>
                  <svg
                    v-if="isSelected(option)"
                    class="h-5 w-5 text-indigo-600 dark:text-indigo-400"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                  >
                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                  </svg>
                </div>
              </button>
            </template>
          </div>
        </div>
      </transition>
    </div>
    
    <!-- Hint -->
    <p v-if="hint && !hasError" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
      {{ hint }}
    </p>
    
    <!-- Error Messages -->
    <p v-if="hasError" class="mt-1 text-sm text-red-600 dark:text-red-400">
      {{ errorMessage }}
    </p>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onUnmounted, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  modelValue: {
    type: [String, Number, Boolean],
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
    default: 'Select...'
  },
  searchPlaceholder: {
    type: String,
    default: 'Search...'
  },
  options: {
    type: Array,
    default: () => []
  },
  // For AJAX search
  searchUrl: {
    type: String,
    default: null
  },
  searchParams: {
    type: Object,
    default: () => ({})
  },
  searchable: {
    type: Boolean,
    default: true
  },
  debounceMs: {
    type: Number,
    default: 300
  },
  required: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  errorMessages: {
    type: [String, Array],
    default: () => []
  },
  hint: {
    type: String,
    default: ''
  },
  columnSpan: {
    type: Number,
    default: 12
  },
  noResultsText: {
    type: String,
    default: 'No results found'
  }
})

const emit = defineEmits(['update:modelValue', 'change', 'search'])

const isOpen = ref(false)
const searchQuery = ref('')
const searchInput = ref(null)
const loading = ref(false)
const internalOptions = ref([...props.options])
let debounceTimeout = null

const fieldId = computed(() => `field-${props.name}`)

const selectedOption = computed(() => {
  return internalOptions.value.find(opt => opt.value == props.modelValue)
})

const filteredOptions = computed(() => {
  if (!searchQuery.value) {
    return internalOptions.value
  }
  
  const query = searchQuery.value.toLowerCase()
  return internalOptions.value.filter(option => 
    option.label.toLowerCase().includes(query)
  )
})

const columnClass = computed(() => {
  const span = props.columnSpan
  if (span === 12) return 'col-span-12'
  if (span === 6) return 'col-span-12 md:col-span-6'
  if (span === 4) return 'col-span-12 md:col-span-4'
  if (span === 3) return 'col-span-12 md:col-span-3'
  return `col-span-${span}`
})

const hasError = computed(() => {
  if (Array.isArray(props.errorMessages)) {
    return props.errorMessages.length > 0
  }
  return !!props.errorMessages
})

const errorMessage = computed(() => {
  if (Array.isArray(props.errorMessages)) {
    return props.errorMessages[0]
  }
  return props.errorMessages
})

const toggleDropdown = () => {
  if (props.disabled) return
  
  isOpen.value = !isOpen.value
  
  if (isOpen.value && props.searchable) {
    nextTick(() => {
      searchInput.value?.focus()
    })
  }
}

const closeDropdown = () => {
  isOpen.value = false
  searchQuery.value = ''
}

const selectOption = (option) => {
  emit('update:modelValue', option.value)
  emit('change', option)
  closeDropdown()
}

const isSelected = (option) => {
  return option.value == props.modelValue
}

const onSearch = () => {
  if (!props.searchUrl) return
  
  // Clear existing timeout
  if (debounceTimeout) {
    clearTimeout(debounceTimeout)
  }
  
  // Set new timeout for debounced search
  debounceTimeout = setTimeout(() => {
    performSearch()
  }, props.debounceMs)
}

const performSearch = async () => {
  if (!props.searchUrl) return
  
  loading.value = true
  
  try {
    const params = {
      ...props.searchParams,
      search: searchQuery.value
    }
    
    const response = await axios.get(props.searchUrl, { params })
    internalOptions.value = response.data
    
    emit('search', response.data)
  } catch (error) {
    console.error('Search error:', error)
  } finally {
    loading.value = false
  }
}

// Watch for external options changes
watch(() => props.options, (newOptions) => {
  internalOptions.value = [...newOptions]
}, { deep: true })

// Fetch initial value if modelValue exists but no matching option is found
const fetchInitialValue = async () => {
  if (!props.searchUrl || !props.modelValue) {
    return
  }

  // Check if we already have the option
  const hasOption = internalOptions.value.some(opt => opt.value == props.modelValue)
  if (hasOption) {
    return
  }

  // Fetch the specific item by ID
  loading.value = true
  try {
    const params = {
      ...props.searchParams,
      id: props.modelValue
    }
    
    const response = await axios.get(props.searchUrl, { params })
    if (response.data && response.data.length > 0) {
      // Add the fetched option to internalOptions
      internalOptions.value = [...response.data, ...internalOptions.value]
    }
  } catch (error) {
    console.error('Error fetching initial value:', error)
  } finally {
    loading.value = false
  }
}

// Fetch initial value on mount
onMounted(() => {
  fetchInitialValue()
})

// Watch modelValue changes to fetch if needed
watch(() => props.modelValue, (newValue) => {
  if (newValue && props.searchUrl) {
    const hasOption = internalOptions.value.some(opt => opt.value == newValue)
    if (!hasOption) {
      fetchInitialValue()
    }
  }
})

// Click outside handler
const handleClickOutside = (event) => {
  const dropdown = document.querySelector('.searchable-select-' + fieldId.value)
  if (dropdown && !dropdown.contains(event.target)) {
    closeDropdown()
  }
}

watch(isOpen, (newValue) => {
  if (newValue) {
    setTimeout(() => {
      document.addEventListener('click', handleClickOutside)
    }, 100)
  } else {
    document.removeEventListener('click', handleClickOutside)
  }
})

// Cleanup on unmount
onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

