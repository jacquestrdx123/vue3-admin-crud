<template>
  <Modal :show="show" :title="editingSubject ? 'Edit Required Subject' : 'Add Required Subject'" max-width="md" @close="$emit('close')">
    <div v-if="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
      {{ errorMessage }}
    </div>

    <form @submit.prevent="submitForm" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subject *</label>
        <select
          v-model="formData.subject_id"
          class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          required
        >
          <option value="">Select Subject</option>
          <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
            {{ subject.name }} {{ subject.code ? `(${subject.code})` : '' }}
          </option>
        </select>
        <p v-if="errors.subject_id" class="mt-1 text-sm text-red-600">{{ errors.subject_id }}</p>
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
          <span v-else>{{ editingSubject ? 'Update' : 'Add' }} Subject</span>
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
  membershipId: {
    type: Number,
    required: true
  },
  subject: {
    type: Object,
    default: null
  },
  subjects: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['close', 'success'])

const isSubmitting = ref(false)
const errorMessage = ref(null)
const errors = ref({})
const editingSubject = ref(!!props.subject)

const formData = reactive({
  subject_id: props.subject?.subject_id || ''
})

watch(() => props.subject, (newSubject) => {
  if (newSubject) {
    editingSubject.value = true
    formData.subject_id = newSubject.subject_id || ''
  } else {
    editingSubject.value = false
    formData.subject_id = ''
  }
}, { deep: true })

watch(() => props.show, (isShowing) => {
  if (!isShowing) {
    editingSubject.value = false
    formData.subject_id = ''
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
    const routeName = editingSubject.value
      ? 'vue.memberships.required-subjects.update'
      : 'vue.memberships.required-subjects.store'
    
    const routeParams = editingSubject.value
      ? { id: props.membershipId, subjectId: props.subject.id }
      : { id: props.membershipId }

    const method = editingSubject.value ? 'put' : 'post'
    const response = await axios[method](route(routeName, routeParams), formData)

    if (response.data.success) {
      emit('success')
      emit('close')
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errorMessage.value = error.response?.data?.message || 'Failed to save required subject'
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

