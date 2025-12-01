<template>
  <Modal :show="show" :title="editingNqf ? 'Edit Required NQF' : 'Add Required NQF'" max-width="md" @close="$emit('close')">
    <div v-if="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
      {{ errorMessage }}
    </div>

    <form @submit.prevent="submitForm" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">NQF Level *</label>
        <select
          v-model.number="formData.nqf_level"
          class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          required
        >
          <option value="">Select NQF Level</option>
          <option v-for="level in [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]" :key="level" :value="level">
            Level {{ level }}
          </option>
        </select>
        <p v-if="errors.nqf_level" class="mt-1 text-sm text-red-600">{{ errors.nqf_level }}</p>
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
          <span v-else>{{ editingNqf ? 'Update' : 'Add' }} NQF</span>
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
  nqf: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'success'])

const isSubmitting = ref(false)
const errorMessage = ref(null)
const errors = ref({})
const editingNqf = ref(!!props.nqf)

const formData = reactive({
  nqf_level: props.nqf?.nqf_level || ''
})

watch(() => props.nqf, (newNqf) => {
  if (newNqf) {
    editingNqf.value = true
    formData.nqf_level = newNqf.nqf_level || ''
  } else {
    editingNqf.value = false
    formData.nqf_level = ''
  }
}, { deep: true })

watch(() => props.show, (isShowing) => {
  if (!isShowing) {
    editingNqf.value = false
    formData.nqf_level = ''
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
    const routeName = editingNqf.value
      ? 'vue.designations.required-nqfs.update'
      : 'vue.designations.required-nqfs.store'
    
    const routeParams = editingNqf.value
      ? { id: props.designationId, nqfId: props.nqf.id }
      : { id: props.designationId }

    const method = editingNqf.value ? 'put' : 'post'
    const response = await axios[method](route(routeName, routeParams), formData)

    if (response.data.success) {
      emit('success')
      emit('close')
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errorMessage.value = error.response?.data?.message || 'Failed to save required NQF'
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

