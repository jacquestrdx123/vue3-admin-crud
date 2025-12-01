<template>
  <div class="flex flex-col gap-1 relative">
    <label v-if="label" class="text-sm font-medium text-gray-700 dark:text-gray-300">
      {{ label }}
    </label>
    
    <!-- Multi-select Dropdown -->
    <div class="relative">
      <button
        type="button"
        @click="toggleDropdown"
        class="relative w-full cursor-default rounded-md bg-white dark:bg-gray-700 py-2 pl-3 pr-10 text-left border border-gray-300 dark:border-gray-600 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
      >
        <span class="flex items-center flex-wrap gap-1">
          <span v-if="selectedTags.length === 0" class="block truncate text-gray-500 dark:text-gray-400">
            Select tags...
          </span>
          <template v-else>
            <span
              v-for="tagId in selectedTags"
              :key="tagId"
              class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-200 text-xs"
            >
              {{ getTagName(tagId) }}
              <button
                type="button"
                @click.stop="removeTag(tagId)"
                class="hover:text-indigo-600 dark:hover:text-indigo-400"
              >
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
            </span>
          </template>
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
                placeholder="Search tags..."
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
            <template v-if="loading">
              <div class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400 text-center">
                Loading tags...
              </div>
            </template>
            <template v-else-if="filteredOptions.length === 0">
              <div class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400 text-center">
                No tags found
              </div>
            </template>
            <template v-else>
              <button
                v-for="option in filteredOptions"
                :key="option.id"
                type="button"
                @click="toggleTag(option.id)"
                :class="[
                  'relative cursor-pointer select-none w-full text-left px-4 py-2',
                  'hover:bg-gray-100 dark:hover:bg-gray-700',
                  isTagSelected(option.id) ? 'bg-indigo-50 dark:bg-indigo-900/20' : ''
                ]"
              >
                <div class="flex items-center justify-between">
                  <span :class="[
                    'block truncate',
                    isTagSelected(option.id) ? 'font-semibold text-indigo-600 dark:text-indigo-400' : 'font-normal text-gray-900 dark:text-gray-100'
                  ]">
                    {{ option.name }}
                  </span>
                  <svg
                    v-if="isTagSelected(option.id)"
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
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({})
  },
  label: {
    type: String,
    default: ''
  },
  name: {
    type: String,
    required: true
  },
  fields: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['update:modelValue'])

const isOpen = ref(false)
const searchQuery = ref('')
const searchInput = ref(null)
const loading = ref(false)
const tags = ref([])

// Get selected tag IDs from modelValue
const selectedTags = computed(() => {
  const fieldName = Object.keys(props.fields)[0] || 'tag_id'
  const value = props.modelValue?.[fieldName]
  if (!value) return []
  return Array.isArray(value) ? value : [value]
})

// Fetch tags on mount
onMounted(async () => {
  await fetchTags()
})

const fetchTags = async () => {
  loading.value = true
  try {
    const response = await axios.get(route('api.tags.index'))
    // Convert object {id: name} to array [{id, name}]
    tags.value = Object.entries(response.data).map(([id, name]) => ({
      id: parseInt(id),
      name: name
    }))
  } catch (error) {
    console.error('Error fetching tags:', error)
    tags.value = []
  } finally {
    loading.value = false
  }
}

const filteredOptions = computed(() => {
  if (!searchQuery.value) {
    return tags.value
  }
  
  const query = searchQuery.value.toLowerCase()
  return tags.value.filter(tag => 
    tag.name.toLowerCase().includes(query)
  )
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

const isTagSelected = (tagId) => {
  return selectedTags.value.includes(tagId)
}

const toggleTag = (tagId) => {
  const fieldName = Object.keys(props.fields)[0] || 'tag_id'
  const currentValue = [...selectedTags.value]
  const index = currentValue.indexOf(tagId)
  
  if (index > -1) {
    currentValue.splice(index, 1)
  } else {
    currentValue.push(tagId)
  }
  
  emit('update:modelValue', {
    ...props.modelValue,
    [fieldName]: currentValue.length > 0 ? currentValue : null
  })
}

const removeTag = (tagId) => {
  toggleTag(tagId)
}

const getTagName = (tagId) => {
  const tag = tags.value.find(t => t.id === tagId)
  return tag?.name || `Tag #${tagId}`
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

