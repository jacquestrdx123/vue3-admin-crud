<template>
  <Modal :show="show" :title="editingDocument ? 'Edit Required Document' : 'Add Required Document'" max-width="md" @close="$emit('close')">
    <div v-if="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
      {{ errorMessage }}
    </div>

    <form @submit.prevent="submitForm" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Document Type *</label>
        <select
          v-model="formData.member_document_type_id"
          class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
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
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Minimum Age (Days)</label>
        <input
          v-model.number="formData.minimum_age"
          type="number"
          min="0"
          class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
        />
        <p v-if="errors.minimum_age" class="mt-1 text-sm text-red-600">{{ errors.minimum_age }}</p>
      </div>
    </form>

    <template #footer>
      <div class="flex justify-end gap-3">
        <button
          type="button"
          @click="$emit('close')"
          class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700"
          :disabled="isSubmitting"
        >
          Cancel
        </button>
        <button
          type="button"
          @click="submitForm"
          class="px-4 py-2 text-sm font-semibold text-black rounded-lg bg-ciba-green hover:bg-ciba-green/90"
          :disabled="isSubmitting"
        >
          <span v-if="isSubmitting">Saving...</span>
          <span v-else>{{ editingDocument ? 'Update' : 'Add' }} Document</span>
        </button>
      </div>
    </template>
  </Modal>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import axios from 'axios'
import Modal from '@/Components/UI/Modal.vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  licenceId: {
    type: Number,
    required: true
  },
  document: {
    type: Object,
    default: null
  },
  documentTypes: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['close', 'success'])

const isSubmitting = ref(false)
const errorMessage = ref(null)
const errors = ref({})
const editingDocument = ref(!!props.document)

const formData = reactive({
  member_document_type_id: props.document?.member_document_type_id || '',
  minimum_age: props.document?.minimum_age || 0
})

watch(() => props.document, (newDoc) => {
  if (newDoc) {
    editingDocument.value = true
    formData.member_document_type_id = newDoc.member_document_type_id || ''
    formData.minimum_age = newDoc.minimum_age || 0
  } else {
    editingDocument.value = false
    formData.member_document_type_id = ''
    formData.minimum_age = 0
  }
}, { deep: true })

watch(() => props.show, (isShowing) => {
  if (!isShowing) {
    editingDocument.value = false
    formData.member_document_type_id = ''
    formData.minimum_age = 0
    errorMessage.value = null
    errors.value = {}
  }
})

const submitForm = async () => {
  if (isSubmitting.value) return

  isSubmitting.value = true
  errorMessage.value = null
  errors.value = {}

  try {
    const routeName = editingDocument.value
      ? 'vue.licences.required-documents.update'
      : 'vue.licences.required-documents.store'
    
    const routeParams = editingDocument.value
      ? { id: props.licenceId, docId: props.document.id }
      : { id: props.licenceId }

    const method = editingDocument.value ? 'put' : 'post'
    const response = await axios[method](route(routeName, routeParams), formData)

    if (response.data.success) {
      emit('success')
      emit('close')
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errorMessage.value = error.response?.data?.message || 'Failed to save required document'
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

