<template>
  <div class="member-file-upload">
    <label v-if="label" class="block text-sm font-medium text-gray-700 mb-2">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>

    <div
      class="border-2 border-dashed rounded-lg p-6 text-center transition-colors"
      :class="[
        isDragging ? 'border-ciba-green bg-green-50' : 'border-gray-300',
        errorMessage ? 'border-red-300 bg-red-50' : '',
        disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
      ]"
      @dragover.prevent="handleDragOver"
      @dragleave.prevent="handleDragLeave"
      @drop.prevent="handleDrop"
      @click="!disabled && triggerFileInput()"
    >
      <input
        ref="fileInput"
        type="file"
        :accept="accept"
        :multiple="multiple"
        :disabled="disabled"
        class="hidden"
        @change="handleFileSelect"
      />

      <div v-if="!uploadedFiles.length" class="space-y-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
        </svg>
        <div class="text-sm text-gray-600">
          <span class="font-medium text-ciba-green">Click to upload</span>
          or drag and drop
        </div>
        <p v-if="hint" class="text-xs text-gray-500">{{ hint }}</p>
        <p v-if="acceptText" class="text-xs text-gray-500">{{ acceptText }}</p>
        <p v-if="maxSize" class="text-xs text-gray-500">Max size: {{ formatFileSize(maxSize) }}</p>
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="(file, index) in uploadedFiles"
          :key="index"
          class="flex items-center justify-between p-3 bg-white rounded border border-gray-200"
        >
          <div class="flex items-center gap-3 flex-1 min-w-0">
            <div class="flex-shrink-0">
              <svg v-if="isImage(file)" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
              </svg>
              <svg v-else-if="isPDF(file)" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
              </svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 truncate">{{ file.name }}</p>
              <p class="text-xs text-gray-500">{{ formatFileSize(file.size) }}</p>
            </div>
          </div>
          
          <button
            v-if="!disabled"
            type="button"
            @click.stop="removeFile(index)"
            class="ml-4 text-red-500 hover:text-red-700 flex-shrink-0"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </button>
        </div>
        
        <button
          v-if="multiple && !disabled"
          type="button"
          @click.stop="triggerFileInput"
          class="text-sm text-ciba-green hover:text-ciba-green/80 font-medium"
        >
          + Add another file
        </button>
      </div>
    </div>

    <p v-if="errorMessage" class="mt-2 text-sm text-red-600">{{ errorMessage }}</p>
    <p v-else-if="description" class="mt-2 text-sm text-gray-500">{{ description }}</p>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  label: {
    type: String,
    default: null
  },
  description: {
    type: String,
    default: null
  },
  hint: {
    type: String,
    default: null
  },
  accept: {
    type: String,
    default: '*'
  },
  acceptText: {
    type: String,
    default: null
  },
  multiple: {
    type: Boolean,
    default: false
  },
  maxSize: {
    type: Number,
    default: 10485760 // 10MB
  },
  required: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  modelValue: {
    type: [File, Array],
    default: null
  },
  error: {
    type: String,
    default: null
  }
})

const emit = defineEmits(['update:modelValue', 'error'])

const fileInput = ref(null)
const uploadedFiles = ref([])
const isDragging = ref(false)
const errorMessage = ref(props.error)

watch(() => props.error, (newError) => {
  errorMessage.value = newError
})

watch(() => props.modelValue, (newValue) => {
  if (!newValue) {
    uploadedFiles.value = []
  } else if (Array.isArray(newValue)) {
    uploadedFiles.value = newValue
  } else {
    uploadedFiles.value = [newValue]
  }
}, { immediate: true })

function triggerFileInput() {
  fileInput.value?.click()
}

function handleFileSelect(event) {
  const files = Array.from(event.target.files || [])
  processFiles(files)
}

function handleDragOver() {
  if (!props.disabled) {
    isDragging.value = true
  }
}

function handleDragLeave() {
  isDragging.value = false
}

function handleDrop(event) {
  isDragging.value = false
  if (props.disabled) return

  const files = Array.from(event.dataTransfer.files || [])
  processFiles(files)
}

function processFiles(files) {
  errorMessage.value = null

  // Validate files
  for (const file of files) {
    if (props.maxSize && file.size > props.maxSize) {
      errorMessage.value = `File "${file.name}" exceeds maximum size of ${formatFileSize(props.maxSize)}`
      emit('error', errorMessage.value)
      return
    }

    if (props.accept !== '*') {
      const acceptedTypes = props.accept.split(',').map(t => t.trim())
      const fileExtension = '.' + file.name.split('.').pop()
      const mimeType = file.type

      const isAccepted = acceptedTypes.some(type => {
        if (type.startsWith('.')) {
          return fileExtension.toLowerCase() === type.toLowerCase()
        }
        return mimeType.match(new RegExp(type.replace('*', '.*')))
      })

      if (!isAccepted) {
        errorMessage.value = `File type not accepted. Please upload: ${props.acceptText || props.accept}`
        emit('error', errorMessage.value)
        return
      }
    }
  }

  // Update files
  if (props.multiple) {
    uploadedFiles.value = [...uploadedFiles.value, ...files]
    emit('update:modelValue', uploadedFiles.value)
  } else {
    uploadedFiles.value = [files[0]]
    emit('update:modelValue', files[0])
  }

  // Reset input
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

function removeFile(index) {
  uploadedFiles.value.splice(index, 1)
  
  if (props.multiple) {
    emit('update:modelValue', uploadedFiles.value.length ? uploadedFiles.value : null)
  } else {
    emit('update:modelValue', null)
  }
}

function formatFileSize(bytes) {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}

function isImage(file) {
  return file.type.startsWith('image/')
}

function isPDF(file) {
  return file.type === 'application/pdf'
}

defineExpose({
  reset: () => {
    uploadedFiles.value = []
    errorMessage.value = null
    if (fileInput.value) {
      fileInput.value.value = ''
    }
  }
})
</script>

<style scoped>
.text-ciba-green {
  color: #BAF504;
}

.border-ciba-green {
  border-color: #BAF504;
}
</style>

