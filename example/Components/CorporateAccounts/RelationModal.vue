<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="show"
        class="fixed inset-0 z-50 overflow-y-auto"
        @click.self="close"
      >
        <div class="flex min-h-screen items-center justify-center p-4">
          <!-- Backdrop -->
          <div
            class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-75 transition-opacity"
            @click="close"
          ></div>

          <!-- Modal -->
          <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to-class="opacity-100 translate-y-0 sm:scale-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 translate-y-0 sm:scale-100"
            leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <div
              v-if="show"
              class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl transform transition-all w-full max-w-2xl"
            >
              <!-- Header -->
              <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                  {{ title }}
                </h3>
                <button
                  @click="close"
                  class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
                >
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>

              <!-- Body -->
              <form @submit.prevent="save">
                <div class="px-6 py-4 max-h-[calc(100vh-200px)] overflow-y-auto">
                  <div class="space-y-4">
                    <div v-for="field in fields" :key="field.name">
                      <!-- Text/Email/Number Input -->
                      <div v-if="['text', 'email', 'number'].includes(field.type)">
                        <label :for="field.name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                          {{ field.label }}
                          <span v-if="field.required" class="text-red-500">*</span>
                        </label>
                        <input
                          :id="field.name"
                          v-model="formData[field.name]"
                          :type="field.type"
                          :required="field.required"
                          class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        />
                        <p v-if="errors[field.name]" class="mt-1 text-sm text-red-600 dark:text-red-400">
                          {{ errors[field.name] }}
                        </p>
                      </div>

                      <!-- Date Input -->
                      <div v-else-if="field.type === 'date'">
                        <label :for="field.name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                          {{ field.label }}
                          <span v-if="field.required" class="text-red-500">*</span>
                        </label>
                        <input
                          :id="field.name"
                          v-model="formData[field.name]"
                          type="date"
                          :required="field.required"
                          class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        />
                        <p v-if="errors[field.name]" class="mt-1 text-sm text-red-600 dark:text-red-400">
                          {{ errors[field.name] }}
                        </p>
                      </div>

                      <!-- Select Input -->
                      <div v-else-if="field.type === 'select'">
                        <label :for="field.name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                          {{ field.label }}
                          <span v-if="field.required" class="text-red-500">*</span>
                        </label>
                        <select
                          :id="field.name"
                          v-model="formData[field.name]"
                          :required="field.required"
                          class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        >
                          <option value="">Select {{ field.label }}</option>
                          <option
                            v-for="option in field.options"
                            :key="option.value"
                            :value="option.value"
                          >
                            {{ option.label }}
                          </option>
                        </select>
                        <p v-if="errors[field.name]" class="mt-1 text-sm text-red-600 dark:text-red-400">
                          {{ errors[field.name] }}
                        </p>
                      </div>

                      <!-- Checkbox Input -->
                      <div v-else-if="field.type === 'checkbox'" class="flex items-center">
                        <input
                          :id="field.name"
                          v-model="formData[field.name]"
                          type="checkbox"
                          class="w-4 h-4 text-indigo-600 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500"
                        />
                        <label :for="field.name" class="ml-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                          {{ field.label }}
                        </label>
                      </div>

                      <!-- Textarea Input -->
                      <div v-else-if="field.type === 'textarea'">
                        <label :for="field.name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                          {{ field.label }}
                          <span v-if="field.required" class="text-red-500">*</span>
                        </label>
                        <textarea
                          :id="field.name"
                          v-model="formData[field.name]"
                          :required="field.required"
                          :rows="field.rows || 3"
                          class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        ></textarea>
                        <p v-if="errors[field.name]" class="mt-1 text-sm text-red-600 dark:text-red-400">
                          {{ errors[field.name] }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                  <button
                    type="button"
                    @click="close"
                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600"
                    :disabled="loading"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="loading"
                  >
                    <span v-if="loading">Saving...</span>
                    <span v-else>Save</span>
                  </button>
                </div>
              </form>
            </div>
          </Transition>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    required: true
  },
  fields: {
    type: Array,
    required: true
  },
  initialData: {
    type: Object,
    default: null
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['save', 'close'])

const formData = ref({})
const errors = ref({})

const initializeForm = () => {
  const data = {}
  props.fields.forEach(field => {
    if (props.initialData && props.initialData[field.name] !== undefined) {
      data[field.name] = props.initialData[field.name]
    } else if (field.type === 'checkbox') {
      data[field.name] = false
    } else {
      data[field.name] = ''
    }
  })
  formData.value = data
  errors.value = {}
}

const save = () => {
  // Clear previous errors
  errors.value = {}
  
  // Basic validation
  let hasErrors = false
  props.fields.forEach(field => {
    if (field.required && !formData.value[field.name]) {
      errors.value[field.name] = `${field.label} is required`
      hasErrors = true
    }
  })
  
  if (hasErrors) {
    return
  }
  
  emit('save', { ...formData.value })
}

const close = () => {
  if (!props.loading) {
    emit('close')
  }
}

// Initialize form when modal is shown or initial data changes
watch(() => props.show, (newVal) => {
  if (newVal) {
    initializeForm()
  }
})

watch(() => props.initialData, () => {
  if (props.show) {
    initializeForm()
  }
}, { deep: true })

onMounted(() => {
  if (props.show) {
    initializeForm()
  }
})
</script>

