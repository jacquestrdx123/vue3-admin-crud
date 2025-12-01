<template>
  <Modal :show="show" title="Edit Designation Overview" max-width="2xl" @close="$emit('close')">
    <div v-if="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
      {{ errorMessage }}
    </div>

    <form @submit.prevent="submitForm" class="space-y-4">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Designation Name *</label>
          <input
            v-model="formData.name"
            type="text"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
            required
          />
          <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Abbreviation *</label>
          <input
            v-model="formData.abbreviation"
            type="text"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
            required
          />
          <p v-if="errors.abbreviation" class="mt-1 text-sm text-red-600">{{ errors.abbreviation }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Monthly Price *</label>
          <input
            v-model.number="formData.monthly_price"
            type="number"
            step="0.01"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
            required
          />
          <p v-if="errors.monthly_price" class="mt-1 text-sm text-red-600">{{ errors.monthly_price }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quarterly Price *</label>
          <input
            v-model.number="formData.quarterly_price"
            type="number"
            step="0.01"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
            required
          />
          <p v-if="errors.quarterly_price" class="mt-1 text-sm text-red-600">{{ errors.quarterly_price }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Yearly Price *</label>
          <input
            v-model.number="formData.yearly_price"
            type="number"
            step="0.01"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
            required
          />
          <p v-if="errors.yearly_price" class="mt-1 text-sm text-red-600">{{ errors.yearly_price }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Initial Fee</label>
          <input
            v-model.number="formData.initial_fee"
            type="number"
            step="0.01"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Rank</label>
          <input
            v-model.number="formData.rank"
            type="number"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="flex items-center">
            <input
              v-model="formData.is_chartered_designation"
              type="checkbox"
              class="rounded border-gray-300 text-ciba-green focus:ring-ciba-green"
            />
            <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Chartered Designation</span>
          </label>
        </div>

        <div>
          <label class="flex items-center">
            <input
              v-model="formData.show_on_application_screen"
              type="checkbox"
              class="rounded border-gray-300 text-ciba-green focus:ring-ciba-green"
            />
            <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Show on Application Screen</span>
          </label>
        </div>

        <div class="col-span-2">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
          <textarea
            v-model="formData.description"
            rows="4"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          ></textarea>
        </div>
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
          <span v-else>Save Changes</span>
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
  designation: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close', 'success'])

const isSubmitting = ref(false)
const errorMessage = ref(null)
const errors = ref({})

const formData = reactive({
  name: props.designation.name || '',
  abbreviation: props.designation.abbreviation || '',
  monthly_price: props.designation.monthly_price || 0,
  quarterly_price: props.designation.quarterly_price || 0,
  yearly_price: props.designation.yearly_price || 0,
  initial_fee: props.designation.initial_fee || 0,
  rank: props.designation.rank || 0,
  is_chartered_designation: props.designation.is_chartered_designation || false,
  show_on_application_screen: props.designation.show_on_application_screen ?? true,
  description: props.designation.description || ''
})

watch(() => props.designation, (newDesignation) => {
  if (newDesignation) {
    Object.assign(formData, {
      name: newDesignation.name || '',
      abbreviation: newDesignation.abbreviation || '',
      monthly_price: newDesignation.monthly_price || 0,
      quarterly_price: newDesignation.quarterly_price || 0,
      yearly_price: newDesignation.yearly_price || 0,
      initial_fee: newDesignation.initial_fee || 0,
      rank: newDesignation.rank || 0,
      is_chartered_designation: newDesignation.is_chartered_designation || false,
      show_on_application_screen: newDesignation.show_on_application_screen ?? true,
      description: newDesignation.description || ''
    })
  }
}, { deep: true })

const submitForm = async () => {
  if (isSubmitting.value) return

  isSubmitting.value = true
  errorMessage.value = null
  errors.value = {}

  try {
    const response = await axios.put(route('vue.designations.update-overview', props.designation.id), formData)

    if (response.data.success) {
      emit('success')
      emit('close')
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errorMessage.value = error.response?.data?.message || 'Failed to update designation overview'
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

