<template>
  <Modal :show="show" :title="editingCpd ? 'Edit CPD Requirement' : 'Add CPD Requirement'" max-width="md" @close="$emit('close')">
    <div v-if="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
      {{ errorMessage }}
    </div>

    <form @submit.prevent="submitForm" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">CPD Category *</label>
        <select
          v-model="formData.cpd_category_id"
          class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          required
        >
          <option value="">Select CPD Category</option>
          <option v-for="category in cpdCategories" :key="category.id" :value="category.id">
            {{ category.name }}
          </option>
        </select>
        <p v-if="errors.cpd_category_id" class="mt-1 text-sm text-red-600">{{ errors.cpd_category_id }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Units *</label>
        <input
          v-model.number="formData.units"
          type="number"
          min="0"
          class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          required
        />
        <p v-if="errors.units" class="mt-1 text-sm text-red-600">{{ errors.units }}</p>
      </div>

      <div>
        <label class="flex items-center">
          <input
            v-model="formData.verifiable"
            type="checkbox"
            class="rounded border-gray-300 text-ciba-green focus:ring-ciba-green"
          />
          <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Verifiable</span>
        </label>
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
          <span v-else>{{ editingCpd ? 'Update' : 'Add' }} Requirement</span>
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
  cpd: {
    type: Object,
    default: null
  },
  cpdCategories: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['close', 'success'])

const isSubmitting = ref(false)
const errorMessage = ref(null)
const errors = ref({})
const editingCpd = ref(!!props.cpd)

const formData = reactive({
  cpd_category_id: props.cpd?.cpd_category_id || '',
  units: props.cpd?.units || 0,
  verifiable: props.cpd?.verifiable || false
})

watch(() => props.cpd, (newCpd) => {
  if (newCpd) {
    editingCpd.value = true
    formData.cpd_category_id = newCpd.cpd_category_id || ''
    formData.units = newCpd.units || 0
    formData.verifiable = newCpd.verifiable || false
  } else {
    editingCpd.value = false
    formData.cpd_category_id = ''
    formData.units = 0
    formData.verifiable = false
  }
}, { deep: true })

watch(() => props.show, (isShowing) => {
  if (!isShowing) {
    editingCpd.value = false
    formData.cpd_category_id = ''
    formData.units = 0
    formData.verifiable = false
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
    const routeName = editingCpd.value
      ? 'vue.designations.cpd-requirements.update'
      : 'vue.designations.cpd-requirements.store'
    
    const routeParams = editingCpd.value
      ? { id: props.designationId, cpdId: props.cpd.id }
      : { id: props.designationId }

    const method = editingCpd.value ? 'put' : 'post'
    const response = await axios[method](route(routeName, routeParams), formData)

    if (response.data.success) {
      emit('success')
      emit('close')
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errorMessage.value = error.response?.data?.message || 'Failed to save CPD requirement'
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

