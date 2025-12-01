<template>
  <div class="space-y-2">
    <label v-if="label" class="block text-sm font-medium text-gray-700">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    
    <div
      class="relative border-2 border-dashed rounded-lg p-6 transition-colors"
      :class="{
        'border-gray-300 hover:border-gray-400': !isDragging && !error,
        'border-teal-500 bg-teal-50': isDragging,
        'border-red-300 bg-red-50': error
      }"
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="onDrop"
    >
      <input
        :id="id"
        ref="fileInput"
        type="file"
        :accept="accept"
        class="hidden"
        @change="onFileChange"
      />

      <div v-if="!selectedFile" class="text-center">
        <svg
          class="mx-auto h-12 w-12 text-gray-400"
          stroke="currentColor"
          fill="none"
          viewBox="0 0 48 48"
          aria-hidden="true"
        >
          <path
            d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
        </svg>
        <div class="mt-4 flex text-sm text-gray-600 justify-center">
          <label
            :for="id"
            class="relative cursor-pointer rounded-md font-medium text-teal-600 hover:text-teal-500 focus-within:outline-none"
          >
            <span>Upload a file</span>
          </label>
          <p class="pl-1">or drag and drop</p>
        </div>
        <p class="text-xs text-gray-500 mt-2">
          {{ acceptDescription }} up to {{ maxSizeMB }}MB
        </p>
      </div>

      <div v-else class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <svg
            class="h-8 w-8 text-gray-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
            />
          </svg>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 truncate">
              {{ selectedFile.name }}
            </p>
            <p class="text-xs text-gray-500">
              {{ formatFileSize(selectedFile.size) }}
            </p>
          </div>
        </div>
        <button
          type="button"
          @click="removeFile"
          class="ml-4 text-red-600 hover:text-red-800"
        >
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

    <p v-if="hint" class="text-sm text-gray-500">{{ hint }}</p>
    <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  id: {
    type: String,
    required: true
  },
  label: {
    type: String,
    default: ''
  },
  modelValue: {
    type: File,
    default: null
  },
  accept: {
    type: String,
    default: '*'
  },
  acceptDescription: {
    type: String,
    default: 'Any file type'
  },
  maxSize: {
    type: Number,
    default: 10240 // 10MB in KB
  },
  required: {
    type: Boolean,
    default: false
  },
  error: {
    type: String,
    default: ''
  },
  hint: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:modelValue'])

const fileInput = ref(null)
const selectedFile = ref(props.modelValue)
const isDragging = ref(false)

const maxSizeMB = computed(() => (props.maxSize / 1024).toFixed(0))

const validateFile = (file) => {
  if (file.size > props.maxSize * 1024) {
    return `File size must be less than ${maxSizeMB.value}MB`
  }
  return null
}

const onFileChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    const validationError = validateFile(file)
    if (validationError) {
      alert(validationError)
      return
    }
    selectedFile.value = file
    emit('update:modelValue', file)
  }
}

const onDrop = (event) => {
  isDragging.value = false
  const file = event.dataTransfer.files[0]
  if (file) {
    const validationError = validateFile(file)
    if (validationError) {
      alert(validationError)
      return
    }
    selectedFile.value = file
    emit('update:modelValue', file)
  }
}

const removeFile = () => {
  selectedFile.value = null
  emit('update:modelValue', null)
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}
</script>

