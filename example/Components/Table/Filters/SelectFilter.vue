<template>
  <div class="flex flex-col gap-1 relative">
    <label v-if="label" class="text-sm font-medium text-gray-700 dark:text-gray-300">
      {{ label }}
    </label>
    
    <!-- Regular Select (non-searchable) -->
    <select
      v-if="!searchable"
      :value="modelValue"
      @change="$emit('update:modelValue', $event.target.value)"
      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
    >
      <option value="">All</option>
      <option
        v-for="(label, value) in options"
        :key="value"
        :value="value"
      >
        {{ label }}
      </option>
    </select>

    <!-- Searchable Select Dropdown -->
    <div v-else class="relative">
      <button
        type="button"
        @click="toggleDropdown"
        class="relative w-full cursor-default rounded-md bg-white dark:bg-gray-700 py-2 pl-3 pr-10 text-left border border-gray-300 dark:border-gray-600 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
      >
        <span class="flex items-center">
          <span v-if="selectedLabel" class="block truncate text-gray-900 dark:text-gray-100">
            {{ selectedLabel }}
          </span>
          <span v-else class="block truncate text-gray-500 dark:text-gray-400">
            All
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
          <div class="sticky top-0 z-10 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 p-2">
            <div class="relative">
              <input
                ref="searchInput"
                type="text"
                v-model="searchQuery"
                class="w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 pr-3 py-2 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500"
                style="padding-left: 2.75rem !important;"
                placeholder="Search..."
              />
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Options List -->
          <div class="py-1">
            <template v-if="filteredOptions.length === 0">
              <div class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400 text-center">
                No results found
              </div>
            </template>
            
            <template v-else>
              <button
                type="button"
                @click="selectOption('')"
                :class="[
                  'relative cursor-pointer select-none w-full text-left px-4 py-2',
                  'hover:bg-gray-100 dark:hover:bg-gray-700',
                  !modelValue ? 'bg-indigo-50 dark:bg-indigo-900/20' : ''
                ]"
              >
                <div class="flex items-center justify-between">
                  <span :class="[
                    'block truncate',
                    !modelValue ? 'font-semibold text-indigo-600 dark:text-indigo-400' : 'font-normal text-gray-900 dark:text-gray-100'
                  ]">
                    All
                  </span>
                  <svg
                    v-if="!modelValue"
                    class="h-5 w-5 text-indigo-600 dark:text-indigo-400"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                  >
                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                  </svg>
                </div>
              </button>
              <button
                v-for="(label, value) in filteredOptions"
                :key="value"
                type="button"
                @click="selectOption(value)"
                :class="[
                  'relative cursor-pointer select-none w-full text-left px-4 py-2',
                  'hover:bg-gray-100 dark:hover:bg-gray-700',
                  isSelected(value) ? 'bg-indigo-50 dark:bg-indigo-900/20' : ''
                ]"
              >
                <div class="flex items-center justify-between">
                  <span :class="[
                    'block truncate',
                    isSelected(value) ? 'font-semibold text-indigo-600 dark:text-indigo-400' : 'font-normal text-gray-900 dark:text-gray-100'
                  ]">
                    {{ label }}
                  </span>
                  <svg
                    v-if="isSelected(value)"
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
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onUnmounted } from 'vue'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  label: {
    type: String,
    default: ''
  },
  options: {
    type: Object,
    required: true
  },
  searchable: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['update:modelValue'])

const isOpen = ref(false)
const searchQuery = ref('')
const searchInput = ref(null)

const selectedLabel = computed(() => {
  if (!props.modelValue) return ''
  return props.options[props.modelValue] || ''
})

const filteredOptions = computed(() => {
  if (!searchQuery.value) {
    return props.options
  }
  
  const query = searchQuery.value.toLowerCase()
  const filtered = {}
  
  for (const [value, label] of Object.entries(props.options)) {
    if (String(label).toLowerCase().includes(query)) {
      filtered[value] = label
    }
  }
  
  return filtered
})

const toggleDropdown = () => {
  isOpen.value = !isOpen.value
  
  if (isOpen.value) {
    nextTick(() => {
      searchInput.value?.focus()
    })
  }
}

const closeDropdown = () => {
  isOpen.value = false
  searchQuery.value = ''
}

const selectOption = (value) => {
  emit('update:modelValue', value)
  closeDropdown()
}

const isSelected = (value) => {
  return String(props.modelValue) === String(value)
}

// Click outside handler
const handleClickOutside = (event) => {
  const dropdown = event.target.closest('.relative')
  if (!dropdown || !dropdown.contains(event.target)) {
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
