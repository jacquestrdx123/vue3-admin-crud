<template>
  <Modal :show="show" title="Edit Tax Information" max-width="2xl" @close="$emit('close')">
    <div v-if="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
      {{ errorMessage }}
    </div>

    <form @submit.prevent="submitForm" class="space-y-4">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tax Years Experience</label>
          <input
            v-model.number="formData.tax_years_experience"
            type="number"
            min="0"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
          <p v-if="errors.tax_years_experience" class="mt-1 text-sm text-red-600">{{ errors.tax_years_experience }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">PR Number</label>
          <input
            v-model="formData.pr_number"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
          <p v-if="errors.pr_number" class="mt-1 text-sm text-red-600">{{ errors.pr_number }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Income Tax Number</label>
          <input
            v-model="formData.income_tax_number"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
          <p v-if="errors.income_tax_number" class="mt-1 text-sm text-red-600">{{ errors.income_tax_number }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Citizenship Status Code</label>
          <input
            v-model="formData.citizen_resident_status_code"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
            placeholder="e.g. ZA"
          />
          <p v-if="errors.citizen_resident_status_code" class="mt-1 text-sm text-red-600">{{ errors.citizen_resident_status_code }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">SARS Status Date</label>
          <input
            v-model="formData.sars_status_date"
            type="date"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">SARS Registration Date</label>
          <input
            v-model="formData.sars_registration_date"
            type="date"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">SARS Deregistration Date</label>
          <input
            v-model="formData.sars_deregistration_date"
            type="date"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="flex items-center">
            <input
              v-model="formData.sars_status"
              type="checkbox"
              class="rounded border-gray-300 text-ciba-green focus:ring-ciba-green"
            />
            <span class="ml-2 text-sm font-medium text-gray-700">SARS Status (Compliant)</span>
          </label>
        </div>
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
  member: {
    type: Object,
    required: true
  },
  taxInfo: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['close', 'success'])

const isSubmitting = ref(false)
const errorMessage = ref(null)
const errors = ref({})

const formData = reactive({
  tax_years_experience: props.taxInfo?.tax_years_experience || props.member.tax_years_experience || null,
  pr_number: props.taxInfo?.pr_number || props.member.pr_number || '',
  income_tax_number: props.taxInfo?.income_tax_number || props.member.income_tax_number || '',
  sars_status_date: props.taxInfo?.sars_status_date || props.member.sars_status_date || '',
  sars_registration_date: props.taxInfo?.sars_registration_date || props.member.sars_registration_date || '',
  sars_deregistration_date: props.taxInfo?.sars_deregistration_date || props.member.sars_deregistration_date || '',
  sars_status: props.taxInfo?.sars_status || props.member.sars_status || false,
  citizen_resident_status_code: props.taxInfo?.citizen_resident_status_code || props.member.citizen_resident_status_code || ''
})

watch(() => props.taxInfo, (newTaxInfo) => {
  if (newTaxInfo) {
    Object.assign(formData, {
      tax_years_experience: newTaxInfo.tax_years_experience || null,
      pr_number: newTaxInfo.pr_number || '',
      income_tax_number: newTaxInfo.income_tax_number || '',
      sars_status_date: newTaxInfo.sars_status_date || '',
      sars_registration_date: newTaxInfo.sars_registration_date || '',
      sars_deregistration_date: newTaxInfo.sars_deregistration_date || '',
      sars_status: newTaxInfo.sars_status || false,
      citizen_resident_status_code: newTaxInfo.citizen_resident_status_code || ''
    })
  }
}, { deep: true })

const submitForm = async () => {
  if (isSubmitting.value) return

  isSubmitting.value = true
  errorMessage.value = null
  errors.value = {}

  try {
    const response = await axios.put(route('vue.members.update-tax-info', props.member.id), formData)

    if (response.data.success) {
      emit('success')
      emit('close')
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errorMessage.value = error.response?.data?.message || 'Failed to update tax information'
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

