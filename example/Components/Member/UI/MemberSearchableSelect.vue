<template>
  <div class="space-y-1" v-click-outside="closeDropdown">
    <label v-if="label" class="block text-sm font-medium text-gray-700">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    
    <div class="relative">
      <input
        ref="searchInput"
        v-model="searchQuery"
        type="text"
        :placeholder="placeholder"
        :required="required"
        @focus="openDropdown"
        @input="handleInput"
        @keydown.down.prevent="navigateDown"
        @keydown.up.prevent="navigateUp"
        @keydown.enter.prevent="selectHighlighted"
        @keydown.escape="closeDropdown"
        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
        :class="{ 'border-red-300': error }"
      />
      
      <button
        type="button"
        @click="toggleDropdown"
        class="absolute inset-y-0 right-0 px-3 flex items-center"
      >
        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
      </button>

      <!-- Dropdown -->
      <transition
        enter-active-class="transition ease-out duration-100"
        enter-from-class="transform opacity-0 scale-95"
        enter-to-class="transform opacity-100 scale-100"
        leave-active-class="transition ease-in duration-75"
        leave-from-class="transform opacity-100 scale-100"
        leave-to-class="transform opacity-0 scale-95"
      >
        <div
          v-show="isOpen && filteredOptions.length > 0"
          class="absolute z-50 mt-1 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto"
        >
          <ul class="py-1">
            <li
              v-for="(option, index) in filteredOptions"
              :key="option"
              @click="selectOption(option)"
              @mouseenter="highlightedIndex = index"
              :class="[
                'px-3 py-2 cursor-pointer',
                highlightedIndex === index
                  ? 'bg-blue-100 text-blue-900'
                  : 'text-gray-900 hover:bg-gray-100'
              ]"
            >
              {{ option }}
            </li>
          </ul>
        </div>
      </transition>

      <!-- No results message -->
      <div
        v-show="isOpen && searchQuery && filteredOptions.length === 0"
        class="absolute z-50 mt-1 w-full bg-white border border-gray-300 rounded-md shadow-lg"
      >
        <div class="px-3 py-2 text-sm text-gray-500">
          No matches found. You can still use "{{ searchQuery }}" as a custom value.
        </div>
      </div>
    </div>
    
    <p v-if="error" class="text-red-500 text-xs mt-1">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  options: {
    type: Array,
    default: () => []
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Search or type...'
  },
  required: {
    type: Boolean,
    default: false
  },
  error: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:modelValue'])

const searchQuery = ref(props.modelValue || '')
const isOpen = ref(false)
const highlightedIndex = ref(0)
const searchInput = ref(null)

// Watch modelValue changes from parent
watch(() => props.modelValue, (newValue) => {
  searchQuery.value = newValue || ''
})

const filteredOptions = computed(() => {
  if (!searchQuery.value) {
    return props.options
  }
  
  const query = searchQuery.value.toLowerCase()
  return props.options.filter(option => 
    option.toLowerCase().includes(query)
  )
})

function handleInput() {
  emit('update:modelValue', searchQuery.value)
  if (!isOpen.value) {
    openDropdown()
  }
  highlightedIndex.value = 0
}

function openDropdown() {
  isOpen.value = true
  highlightedIndex.value = 0
}

function closeDropdown() {
  isOpen.value = false
}

function toggleDropdown() {
  if (isOpen.value) {
    closeDropdown()
  } else {
    openDropdown()
    searchInput.value?.focus()
  }
}

function selectOption(option) {
  searchQuery.value = option
  emit('update:modelValue', option)
  closeDropdown()
}

function navigateDown() {
  if (highlightedIndex.value < filteredOptions.value.length - 1) {
    highlightedIndex.value++
  }
}

function navigateUp() {
  if (highlightedIndex.value > 0) {
    highlightedIndex.value--
  }
}

function selectHighlighted() {
  if (filteredOptions.value.length > 0 && isOpen.value) {
    selectOption(filteredOptions.value[highlightedIndex.value])
  } else {
    closeDropdown()
  }
}

// Custom directive for click outside
const vClickOutside = {
  mounted(el, binding) {
    el.clickOutsideEvent = function(event) {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value()
      }
    }
    document.addEventListener('click', el.clickOutsideEvent)
  },
  unmounted(el) {
    document.removeEventListener('click', el.clickOutsideEvent)
  }
}
</script>

