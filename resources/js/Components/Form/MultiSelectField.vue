<template>
  <div v-show="isVisible" :class="columnClass">
    <label v-if="label" :for="fieldId" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>

    <div class="relative">
      <!-- Selected Items Display -->
      <div
        @click="toggleDropdown"
        class="min-h-[38px] block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 disabled:cursor-not-allowed sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white cursor-pointer p-2"
        :class="{'border-red-300': hasError}"
      >
        <div v-if="selectedItems.length > 0" class="flex flex-wrap gap-1">
          <span
            v-for="item in selectedItems"
            :key="item.value"
            class="inline-flex items-center gap-1 px-2 py-1 text-xs bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200 rounded"
          >
            {{ item.label }}
            <button
              type="button"
              @click.stop="removeItem(item.value)"
              class="hover:text-indigo-600"
            >
              <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </span>
        </div>
        <span v-else class="text-gray-500 dark:text-gray-400">
          {{ placeholder || 'Select options...' }}
        </span>
      </div>

      <!-- Dropdown -->
      <div
        v-show="isOpen"
        class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg rounded-md border border-gray-200 dark:border-gray-700 max-h-60 overflow-auto"
      >
        <div
          v-for="option in normalizedOptions"
          :key="option.value"
          @click="toggleOption(option)"
          class="px-3 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2"
        >
          <input
            type="checkbox"
            :checked="isSelected(option.value)"
            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
            @click.stop
          />
          <span class="text-sm text-gray-900 dark:text-white">{{ option.label }}</span>
        </div>
      </div>
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
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useFieldVisibility } from '@/Composables/useFieldVisibility'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
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
  options: {
    type: [Array, Object],
    required: true
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
  showWhen: {
    type: Object,
    default: null
  },
  hideWhen: {
    type: Object,
    default: null
  },
  formData: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['update:modelValue'])

const isOpen = ref(false)
const fieldId = computed(() => `field-${props.name}`)

const normalizedOptions = computed(() => {
  if (Array.isArray(props.options)) {
    return props.options.map(opt => {
      if (typeof opt === 'object' && opt !== null) {
        return opt
      }
      return { value: opt, label: opt }
    })
  }
  
  return Object.entries(props.options).map(([value, label]) => ({
    value,
    label
  }))
})

const selectedItems = computed(() => {
  return normalizedOptions.value.filter(opt => 
    props.modelValue.includes(opt.value)
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

const fieldConfig = computed(() => ({
  show_when: props.showWhen,
  hide_when: props.hideWhen
}))

const { isVisible } = useFieldVisibility(props.formData, fieldConfig.value)

const toggleDropdown = () => {
  if (!props.disabled) {
    isOpen.value = !isOpen.value
  }
}

const isSelected = (value) => {
  return props.modelValue.includes(value)
}

const toggleOption = (option) => {
  const newValue = [...props.modelValue]
  const index = newValue.indexOf(option.value)
  
  if (index > -1) {
    newValue.splice(index, 1)
  } else {
    newValue.push(option.value)
  }
  
  emit('update:modelValue', newValue)
}

const removeItem = (value) => {
  const newValue = props.modelValue.filter(v => v !== value)
  emit('update:modelValue', newValue)
}

// Close dropdown on outside click
const handleClickOutside = (event) => {
  if (!event.target.closest(`#${fieldId.value}`)) {
    isOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

