<template>
  <Modal :show="show" title="Upload Document" max-width="2xl" @close="$emit('close')">
    <div v-if="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
      {{ errorMessage }}
    </div>

    <form @submit.prevent="submitForm" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Document Type *</label>
        <select
          v-model="formData.member_document_type_id"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          required
        >
          <option value="">Select Document Type</option>
          <option v-for="type in documentTypes" :key="type.id" :value="type.id">
            {{ type.name }}
          </option>
        </select>
        <p v-if="errors.member_document_type_id" class="mt-1 text-sm text-red-600">{{ errors.member_document_type_id }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Document Name (Optional)</label>
        <input
          v-model="formData.name"
          type="text"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          placeholder="e.g. Proof of Qualification"
        />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Files *</label>
        <input
          ref="fileInput"
          type="file"
          multiple
          accept=".pdf,.jpg,.jpeg,.png"
          @change="handleFileChange"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          required
        />
        <p class="mt-1 text-xs text-gray-500">Supported formats: PDF, JPG, JPEG, PNG. Maximum size 10 MB per file.</p>
        <p v-if="errors.documents" class="mt-1 text-sm text-red-600">{{ errors.documents }}</p>
      </div>

      <div v-if="selectedFiles.length > 0" class="mt-2">
        <p class="text-sm font-medium text-gray-700 mb-2">Selected Files:</p>
        <ul class="list-disc list-inside text-sm text-gray-600">
          <li v-for="(file, index) in selectedFiles" :key="index">{{ file.name }}</li>
        </ul>
      </div>
    </form>

    <template #footer>
      <div class="flex justify-end gap-3">
        <button
          type="button"
          @click="$emit('close')"
          class="px-4 py-2 text-sm font-medium text-gray-700 rounded-lg border border-gray-300 hover:bg-gray-100"
          :disabled="isSubmitting"
        >
          Cancel
        </button>
        <button
          type="button"
          @click="submitForm"
          class="px-4 py-2 text-sm font-semibold text-black rounded-lg bg-ciba-green hover:bg-ciba-green/90"
          :disabled="isSubmitting || !formData.member_document_type_id || selectedFiles.length === 0"
        >
          <span v-if="isSubmitting">Uploading...</span>
          <span v-else>Upload Document</span>
        </button>
      </div>
    </template>
  </Modal>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import axios from 'axios'
import Modal from '@/Components/UI/Modal.vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  memberId: {
    type: [Number, String],
    required: true
  },
  preselectedDocumentTypeId: {
    type: [Number, String],
    default: null
  }
})

const emit = defineEmits(['close', 'success'])

const isSubmitting = ref(false)
const errorMessage = ref(null)
const errors = ref({})
const documentTypes = ref([])
const selectedFiles = ref([])
const fileInput = ref(null)

const formData = reactive({
  member_document_type_id: '',
  name: '',
  documents: []
})

onMounted(async () => {
  try {
    const response = await axios.get(route('vue.members.options.document-types'))
    documentTypes.value = response.data || []
    
    // Set preselected document type if provided
    if (props.preselectedDocumentTypeId) {
      formData.member_document_type_id = String(props.preselectedDocumentTypeId)
    }
  } catch (error) {
    console.error('Failed to load document types:', error)
  }
})

// Watch for preselected document type changes
watch(() => props.preselectedDocumentTypeId, (newValue) => {
  if (newValue && documentTypes.value.length > 0) {
    formData.member_document_type_id = String(newValue)
  }
})

// Watch for modal show state to reset form when opened
watch(() => props.show, (isOpen) => {
  if (isOpen) {
    // Reset form
    formData.name = ''
    selectedFiles.value = []
    if (fileInput.value) {
      fileInput.value.value = ''
    }
    errorMessage.value = null
    errors.value = {}
    
    // Set preselected document type if provided
    if (props.preselectedDocumentTypeId) {
      formData.member_document_type_id = String(props.preselectedDocumentTypeId)
    } else {
      formData.member_document_type_id = ''
    }
  }
})

const handleFileChange = (event) => {
  selectedFiles.value = Array.from(event.target.files || [])
  formData.documents = selectedFiles.value
}

const submitForm = async () => {
  if (isSubmitting.value || !formData.member_document_type_id || selectedFiles.value.length === 0) return

  isSubmitting.value = true
  errorMessage.value = null
  errors.value = {}

  try {
    const formDataToSend = new FormData()
    formDataToSend.append('member_document_type_id', formData.member_document_type_id)
    
    if (formData.name) {
      formDataToSend.append('name', formData.name)
    }

    selectedFiles.value.forEach((file) => {
      formDataToSend.append('documents[]', file)
    })

    const response = await axios.post(route('vue.members.documents.store', props.memberId), formDataToSend, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    if (response.data.success) {
      emit('success')
      emit('close')
      // Reset form
      formData.member_document_type_id = ''
      formData.name = ''
      selectedFiles.value = []
      if (fileInput.value) {
        fileInput.value.value = ''
      }
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errorMessage.value = error.response?.data?.message || 'Failed to upload document'
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

