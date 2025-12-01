<template>
  <Modal :show="show" title="Edit Billing Details" max-width="2xl" @close="$emit('close')">
    <div v-if="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
      {{ errorMessage }}
    </div>

    <form @submit.prevent="submitForm" class="space-y-4">
      <div>
        <label class="flex items-center">
          <input
            v-model="formData.use_company_details_for_invoice"
            type="checkbox"
            class="rounded border-gray-300 text-ciba-green focus:ring-ciba-green"
          />
          <span class="ml-2 text-sm font-medium text-gray-700">Use Company Details for Invoice</span>
        </label>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
        <input
          v-model="formData.invoice_company_name"
          type="text"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
        />
        <p v-if="errors.invoice_company_name" class="mt-1 text-sm text-red-600">{{ errors.invoice_company_name }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">VAT Number</label>
        <input
          v-model="formData.invoice_company_vat"
          type="text"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
        />
        <p v-if="errors.invoice_company_vat" class="mt-1 text-sm text-red-600">{{ errors.invoice_company_vat }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Company Address</label>
        <textarea
          v-model="formData.invoice_company_address"
          rows="3"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
        ></textarea>
        <p v-if="errors.invoice_company_address" class="mt-1 text-sm text-red-600">{{ errors.invoice_company_address }}</p>
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
  billingDetails: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['close', 'success'])

const isSubmitting = ref(false)
const errorMessage = ref(null)
const errors = ref({})

const formData = reactive({
  use_company_details_for_invoice: props.billingDetails?.use_company_details_for_invoice || props.member.use_company_details_for_invoice || false,
  invoice_company_name: props.billingDetails?.invoice_company_name || props.member.invoice_company_name || '',
  invoice_company_vat: props.billingDetails?.invoice_company_vat || props.member.invoice_company_vat || '',
  invoice_company_address: props.billingDetails?.invoice_company_address || props.member.invoice_company_address || ''
})

watch(() => props.billingDetails, (newBillingDetails) => {
  if (newBillingDetails) {
    Object.assign(formData, {
      use_company_details_for_invoice: newBillingDetails.use_company_details_for_invoice || false,
      invoice_company_name: newBillingDetails.invoice_company_name || '',
      invoice_company_vat: newBillingDetails.invoice_company_vat || '',
      invoice_company_address: newBillingDetails.invoice_company_address || ''
    })
  }
}, { deep: true })

const submitForm = async () => {
  if (isSubmitting.value) return

  isSubmitting.value = true
  errorMessage.value = null
  errors.value = {}

  try {
    const response = await axios.put(route('vue.members.update-billing-details', props.member.id), formData)

    if (response.data.success) {
      emit('success')
      emit('close')
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errorMessage.value = error.response?.data?.message || 'Failed to update billing details'
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

