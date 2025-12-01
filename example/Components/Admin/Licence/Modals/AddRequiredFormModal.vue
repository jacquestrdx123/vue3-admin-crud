<template>
  <Modal :show="show" :title="editingForm ? 'Edit Required Form' : 'Add Required Form'" max-width="md" @close="$emit('close')">
    <div v-if="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
      {{ errorMessage }}
    </div>

    <form @submit.prevent="submitForm" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Form *</label>
        <select
          v-model="formData.form_id"
          class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          required
        >
          <option value="">Select Form</option>
          <option v-for="form in forms" :key="form.id" :value="form.id">
            {{ form.name }}
          </option>
        </select>
        <p v-if="errors.form_id" class="mt-1 text-sm text-red-600">{{ errors.form_id }}</p>
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
          <span v-else>{{ editingForm ? 'Update' : 'Add' }} Form</span>
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
  form: {
    type: Object,
    default: null
  },
  forms: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['close', 'success'])

const isSubmitting = ref(false)
const errorMessage = ref(null)
const errors = ref({})
const editingForm = ref(!!props.form)

const formData = reactive({
  form_id: props.form?.form_id || ''
})

watch(() => props.form, (newForm) => {
  if (newForm) {
    editingForm.value = true
    formData.form_id = newForm.form_id || ''
  } else {
    editingForm.value = false
    formData.form_id = ''
  }
}, { deep: true })

watch(() => props.show, (isShowing) => {
  if (!isShowing) {
    editingForm.value = false
    formData.form_id = ''
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
    const routeName = editingForm.value
      ? 'vue.licences.required-forms.update'
      : 'vue.licences.required-forms.store'
    
    const routeParams = editingForm.value
      ? { id: props.licenceId, formId: props.form.id }
      : { id: props.licenceId }

    const method = editingForm.value ? 'put' : 'post'
    const response = await axios[method](route(routeName, routeParams), formData)

    if (response.data.success) {
      emit('success')
      emit('close')
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errorMessage.value = error.response?.data?.message || 'Failed to save required form'
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

