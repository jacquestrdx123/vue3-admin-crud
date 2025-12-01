<template>
  <Modal :show="show" :title="editingInterview ? 'Edit Required Interview' : 'Add Required Interview'" max-width="md" @close="$emit('close')">
    <div v-if="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
      {{ errorMessage }}
    </div>

    <form @submit.prevent="submitForm" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Interview *</label>
        <select
          v-model="formData.interview_id"
          class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          required
        >
          <option value="">Select Interview</option>
          <option v-for="interview in interviews" :key="interview.id" :value="interview.id">
            {{ interview.name }}
          </option>
        </select>
        <p v-if="errors.interview_id" class="mt-1 text-sm text-red-600">{{ errors.interview_id }}</p>
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
          <span v-else>{{ editingInterview ? 'Update' : 'Add' }} Interview</span>
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
  designationId: {
    type: Number,
    required: true
  },
  interview: {
    type: Object,
    default: null
  },
  interviews: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['close', 'success'])

const isSubmitting = ref(false)
const errorMessage = ref(null)
const errors = ref({})
const editingInterview = ref(!!props.interview)

const formData = reactive({
  interview_id: props.interview?.interview_id || ''
})

watch(() => props.interview, (newInterview) => {
  if (newInterview) {
    editingInterview.value = true
    formData.interview_id = newInterview.interview_id || ''
  } else {
    editingInterview.value = false
    formData.interview_id = ''
  }
}, { deep: true })

watch(() => props.show, (isShowing) => {
  if (!isShowing) {
    editingInterview.value = false
    formData.interview_id = ''
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
    const routeName = editingInterview.value
      ? 'vue.designations.required-interviews.update'
      : 'vue.designations.required-interviews.store'
    
    const routeParams = editingInterview.value
      ? { id: props.designationId, interviewId: props.interview.id }
      : { id: props.designationId }

    const method = editingInterview.value ? 'put' : 'post'
    const response = await axios[method](route(routeName, routeParams), formData)

    if (response.data.success) {
      emit('success')
      emit('close')
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errorMessage.value = error.response?.data?.message || 'Failed to save required interview'
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

