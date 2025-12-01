<template>
  <Modal :show="show" title="Add Certificate" max-width="2xl" @close="$emit('close')">
    <div v-if="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
      {{ errorMessage }}
    </div>

    <form @submit.prevent="submitForm" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Product *</label>
        <select
          v-model="formData.product_id"
          class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          required
          :disabled="isLoadingProducts || availableProducts.length === 0"
        >
          <option value="">Select Product</option>
          <option v-for="product in availableProducts" :key="product.id" :value="String(product.id)">
            {{ product.subscribable?.name || 'Unknown' }} - {{ formatDate(product.start_date) }} - {{ formatDate(product.end_date) }}
          </option>
        </select>
        <p v-if="errors.product_id" class="mt-1 text-sm text-red-600">{{ errors.product_id }}</p>
        <p v-if="availableProducts.length === 0 && !isLoadingProducts" class="mt-1 text-sm text-gray-500">
          No products found for this member
        </p>
        <!-- Debug info (remove in production) -->
        <div v-if="true" class="mt-2 text-xs text-gray-400">
          Debug: product_id={{ formData.product_id }}, type={{ typeof formData.product_id }}, valid={{ isFormValid }}, products={{ availableProducts.length }}
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
          class="px-4 py-2 text-sm font-semibold text-black rounded-lg bg-ciba-green hover:bg-ciba-green/90 disabled:opacity-50 disabled:cursor-not-allowed"
          :disabled="isSubmitting || !isFormValid || isLoadingProducts"
          :title="!isFormValid && !isLoadingProducts ? (formData.product_id ? 'Please wait for products to load' : 'Please select a product') : ''"
        >
          <span v-if="isSubmitting">Generating Certificate...</span>
          <span v-else-if="isLoadingProducts">Loading Products...</span>
          <span v-else>Generate Certificate</span>
        </button>
      </div>
    </template>
  </Modal>
</template>

<script setup>
import { ref, reactive, watch, computed, onMounted } from 'vue'
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
  }
})

const emit = defineEmits(['close', 'success'])

const isSubmitting = ref(false)
const isLoadingProducts = ref(false)
const errorMessage = ref(null)
const errors = ref({})
const availableProducts = ref([])

const formData = reactive({
  product_id: ''
})

const isFormValid = computed(() => {
  const hasProductId = formData.product_id && formData.product_id !== '' && formData.product_id !== null
  const valid = hasProductId
  console.log('isFormValid check:', { 
    product_id: formData.product_id, 
    product_id_type: typeof formData.product_id,
    hasProductId,
    productsCount: availableProducts.length, 
    valid 
  })
  return valid
})

const formatDate = (date) => {
  if (!date) return ''
  const d = new Date(date)
  return d.toLocaleDateString('en-ZA', { year: 'numeric', month: '2-digit', day: '2-digit' })
}

const loadProducts = async () => {
  isLoadingProducts.value = true
  try {
    const url = route('vue.members.products.select', { id: props.memberId })
    console.log('Loading products from:', url)
    const response = await axios.get(url)
    console.log('Products loaded:', response.data)
    availableProducts.value = response.data || []
  } catch (error) {
    console.error('Failed to load products:', error)
    availableProducts.value = []
    errorMessage.value = 'Failed to load products. Please try again.'
  } finally {
    isLoadingProducts.value = false
  }
}

// Load products when modal opens
watch(() => props.show, (isOpen) => {
  if (isOpen) {
    loadProducts()
    formData.product_id = ''
    errors.value = {}
    errorMessage.value = null
  }
})

onMounted(() => {
  if (props.show) {
    loadProducts()
  }
})

const submitForm = async () => {
  console.log('submitForm called', { isSubmitting: isSubmitting.value, isFormValid: isFormValid.value, product_id: formData.product_id })
  
  if (isSubmitting.value || !isFormValid.value) {
    console.log('Form validation failed or already submitting')
    return
  }

  isSubmitting.value = true
  errorMessage.value = null
  errors.value = {}

  try {
    const url = route('vue.members.certificates.store', { id: props.memberId })
    console.log('Submitting to:', url, { product_id: formData.product_id })
    
    const response = await axios.post(url, {
      product_id: parseInt(formData.product_id, 10)
    })

    console.log('Response:', response.data)

    if (response.data.success) {
      emit('success')
      emit('close')
    } else {
      errorMessage.value = response.data.message || 'Failed to generate certificate'
    }
  } catch (error) {
    console.error('Error submitting form:', error)
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errorMessage.value = error.response?.data?.message || 'Failed to generate certificate'
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

