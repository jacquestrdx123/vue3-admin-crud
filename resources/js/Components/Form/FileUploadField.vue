<template>
  <div v-show="isVisible" :class="columnClass">
    <label v-if="label" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>

    <!-- File Upload Area -->
    <div
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="handleDrop"
      :class="[
        'border-2 border-dashed rounded-lg p-6 text-center transition-colors',
        isDragging ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-gray-300 dark:border-gray-600',
        hasError ? 'border-red-300' : ''
      ]"
    >
      <input
        ref="fileInput"
        type="file"
        :multiple="multiple"
        :accept="acceptedFileTypes.join(',')"
        @change="handleFileSelect"
        class="hidden"
      />

      <!-- Upload Icon -->
      <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
      </svg>

      <div class="mt-4">
        <button
          type="button"
          @click="$refs.fileInput.click()"
          class="px-4 py-2 text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"
        >
          Select {{ multiple ? 'files' : 'a file' }}
        </button>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
          or drag and drop here
        </p>
        <p v-if="maxSize" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
          Max size: {{ formatFileSize(maxSize * 1024) }}
        </p>
      </div>
    </div>

    <!-- File Preview -->
    <div v-if="files.length > 0" class="mt-4 space-y-2">
      <div
        v-for="(file, index) in files"
        :key="index"
        class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900 rounded-lg"
      >
        <div class="flex items-center flex-1 min-w-0">
          <!-- Preview thumbnail for images -->
          <img
            v-if="file.preview && imagePreview"
            :src="file.preview"
            class="h-10 w-10 rounded object-cover flex-shrink-0"
            :alt="file.name"
          />
          <div v-else class="h-10 w-10 rounded bg-gray-200 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
            <svg class="h-6 w-6 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
            </svg>
          </div>
          
          <div class="ml-3 flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
              {{ file.name }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400">
              {{ formatFileSize(file.size) }}
            </p>
          </div>
        </div>

        <!-- Remove button -->
        <button
          type="button"
          @click="removeFile(index)"
          class="ml-4 text-red-600 hover:text-red-800 dark:text-red-400"
        >
          <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Hint -->
    <p v-if="hint && !hasError" class="mt-2 text-sm text-gray-500 dark:text-gray-400">
      {{ hint }}
    </p>

    <!-- Error Messages -->
    <p v-if="hasError" class="mt-2 text-sm text-red-600 dark:text-red-400">
      {{ errorMessage }}
    </p>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useFieldVisibility } from '@/Composables/useFieldVisibility'

const props = defineProps({
  modelValue: {
    type: [Array, File, null],
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
  required: {
    type: Boolean,
    default: false
  },
  multiple: {
    type: Boolean,
    default: false
  },
  maxSize: {
    type: Number,
    default: 10240 // 10MB in KB
  },
  maxFiles: {
    type: Number,
    default: null
  },
  acceptedFileTypes: {
    type: Array,
    default: () => []
  },
  imagePreview: {
    type: Boolean,
    default: true
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

const files = ref([])
const isDragging = ref(false)
const fileInput = ref(null)

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

const handleFileSelect = (event) => {
  const selectedFiles = Array.from(event.target.files)
  processFiles(selectedFiles)
}

const handleDrop = (event) => {
  isDragging.value = false
  const droppedFiles = Array.from(event.dataTransfer.files)
  processFiles(droppedFiles)
}

const processFiles = async (newFiles) => {
  for (const file of newFiles) {
    // Validate file size
    if (props.maxSize && file.size > props.maxSize * 1024) {
      alert(`File ${file.name} exceeds maximum size of ${formatFileSize(props.maxSize * 1024)}`)
      continue
    }

    // Validate file count
    if (props.maxFiles && files.value.length >= props.maxFiles) {
      alert(`Maximum ${props.maxFiles} files allowed`)
      break
    }

    // Create preview for images
    let preview = null
    if (file.type.startsWith('image/') && props.imagePreview) {
      preview = await readFileAsDataURL(file)
    }

    if (props.multiple) {
      files.value.push({
        file,
        name: file.name,
        size: file.size,
        type: file.type,
        preview
      })
    } else {
      files.value = [{
        file,
        name: file.name,
        size: file.size,
        type: file.type,
        preview
      }]
    }
  }

  emitFiles()
}

const removeFile = (index) => {
  files.value.splice(index, 1)
  emitFiles()
}

const emitFiles = () => {
  if (props.multiple) {
    emit('update:modelValue', files.value.map(f => f.file))
  } else {
    emit('update:modelValue', files.value.length > 0 ? files.value[0].file : null)
  }
}

const readFileAsDataURL = (file) => {
  return new Promise((resolve) => {
    const reader = new FileReader()
    reader.onload = (e) => resolve(e.target.result)
    reader.readAsDataURL(file)
  })
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}
</script>

