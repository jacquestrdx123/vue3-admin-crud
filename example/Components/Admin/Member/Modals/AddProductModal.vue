<template>
  <Modal :show="show" :title="editingProduct ? 'Edit Product' : 'Add Product'" max-width="2xl" @close="$emit('close')">
    <div v-if="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
      {{ errorMessage }}
    </div>

    <form @submit.prevent="submitForm" class="space-y-4">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subscribable Type *</label>
          <select
            v-model="formData.subscribable_type"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
            required
          >
            <option value="">Select type</option>
            <option value="App\Models\BillingProducts\Membership">Membership</option>
            <option value="App\Models\BillingProducts\Licence">Licence</option>
            <option value="App\Models\BillingProducts\Designation">Designation</option>
            <option value="App\Models\BillingProducts\PlusMembership">Plus Product</option>
          </select>
          <p v-if="errors.subscribable_type" class="mt-1 text-sm text-red-600">{{ errors.subscribable_type }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subscribable *</label>
          <select
            v-model="formData.subscribable_id"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
            required
            :disabled="!formData.subscribable_type || isLoadingSubscribables || availableSubscribables.length === 0"
          >
            <option value="">{{ isLoadingSubscribables ? 'Loading...' : `Select ${getSubscribableTypeLabel()}` }}</option>
            <option v-for="subscribable in availableSubscribables" :key="subscribable.id" :value="subscribable.id">
              {{ subscribable.name }}
            </option>
          </select>
          <p v-if="errors.subscribable_id" class="mt-1 text-sm text-red-600">{{ errors.subscribable_id }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Product Type *</label>
          <select
            v-model="formData.product_type"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
            required
          >
            <option value="monthly">Monthly</option>
            <option value="quarterly">Quarterly</option>
            <option value="yearly">Yearly</option>
          </select>
          <p v-if="errors.product_type" class="mt-1 text-sm text-red-600">{{ errors.product_type }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Discount (%)</label>
          <input
            v-model.number="formData.discount"
            type="number"
            min="0"
            max="100"
            step="0.01"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
          <p v-if="errors.discount" class="mt-1 text-sm text-red-600">{{ errors.discount }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date *</label>
          <input
            v-model="formData.start_date"
            type="date"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
            required
          />
          <p v-if="errors.start_date" class="mt-1 text-sm text-red-600">{{ errors.start_date }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date *</label>
          <input
            v-model="formData.end_date"
            type="date"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
            required
          />
          <p v-if="errors.end_date" class="mt-1 text-sm text-red-600">{{ errors.end_date }}</p>
        </div>

        <div class="col-span-2">
          <label class="flex items-center gap-2">
            <input
              v-model="formData.is_active"
              type="checkbox"
              class="rounded border-gray-300 text-ciba-green focus:ring-ciba-green"
            />
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Active</span>
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
          :disabled="isSubmitting || !isFormValid"
        >
          <span v-if="isSubmitting">{{ editingProduct ? 'Updating...' : 'Adding...' }}</span>
          <span v-else>{{ editingProduct ? 'Update Product' : 'Add Product' }}</span>
        </button>
      </div>
    </template>
  </Modal>
</template>

<script setup>
import { ref, reactive, watch, computed } from 'vue'
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
  },
  product: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'success'])

const isSubmitting = ref(false)
const isLoadingSubscribables = ref(false)
const errorMessage = ref(null)
const errors = ref({})
const availableSubscribables = ref([])

const editingProduct = computed(() => !!props.product)

const formData = reactive({
  subscribable_type: '',
  subscribable_id: '',
  product_type: 'yearly',
  discount: 0,
  start_date: '',
  end_date: '',
  is_active: true
})

const isFormValid = computed(() => {
  return formData.subscribable_type && formData.subscribable_id && formData.start_date && formData.end_date
})

const getSubscribableTypeLabel = () => {
  if (formData.subscribable_type.includes('PlusMembership')) return 'Plus Product'
  if (formData.subscribable_type.includes('Membership')) return 'Membership'
  if (formData.subscribable_type.includes('Licence')) return 'Licence'
  if (formData.subscribable_type.includes('Designation')) return 'Designation'
  return 'Product'
}

const loadSubscribables = async () => {
  // Reset subscribable_id when type changes
  formData.subscribable_id = ''
  
  if (!formData.subscribable_type) {
    availableSubscribables.value = []
    isLoadingSubscribables.value = false
    return
  }

  isLoadingSubscribables.value = true
  errorMessage.value = null

  try {
    const response = await axios.get(route('api.subscribables.index'), {
      params: { type: formData.subscribable_type }
    })
    
    // Handle different response formats
    if (Array.isArray(response.data)) {
      // API returns array with {value, label} or {id, name} format
      availableSubscribables.value = response.data.map(item => ({
        id: item.value !== undefined ? item.value : item.id,
        name: item.label !== undefined ? item.label : item.name
      }))
    } else if (response.data && typeof response.data === 'object') {
      // API returns object {id: name} format
      availableSubscribables.value = Object.entries(response.data).map(([id, name]) => ({
        id: parseInt(id),
        name: name
      }))
    } else {
      availableSubscribables.value = []
    }
  } catch (error) {
    console.error('Failed to load subscribables:', error)
    console.error('Error details:', error.response?.data)
    console.error('Request URL:', route('api.subscribables.index'))
    console.error('Request params:', { type: formData.subscribable_type })
    availableSubscribables.value = []
    errorMessage.value = 'Failed to load ' + getSubscribableTypeLabel() + ' options: ' + (error.response?.data?.message || error.message)
  } finally {
    isLoadingSubscribables.value = false
  }
}

// Watch for subscribable_type changes and reload options
watch(() => formData.subscribable_type, (newType, oldType) => {
  if (newType !== oldType && newType) {
    loadSubscribables()
  }
})

// Load product data when editing
watch(() => props.product, (product) => {
  if (product) {
    formData.subscribable_type = product.subscribable_type || ''
    formData.subscribable_id = product.subscribable_id || ''
    formData.product_type = product.product_type || 'yearly'
    formData.discount = product.discount ? (product.discount * 100) : 0
    formData.start_date = product.start_date || ''
    formData.end_date = product.end_date || ''
    formData.is_active = product.is_active !== undefined ? product.is_active : true
    
    if (formData.subscribable_type) {
      loadSubscribables()
    }
  }
}, { immediate: true })

// Reset form when modal closes
watch(() => props.show, (isOpen) => {
  if (!isOpen) {
    formData.subscribable_type = ''
    formData.subscribable_id = ''
    formData.product_type = 'yearly'
    formData.discount = 0
    formData.start_date = ''
    formData.end_date = ''
    formData.is_active = true
    availableSubscribables.value = []
    errors.value = {}
    errorMessage.value = null
    isLoadingSubscribables.value = false
  } else {
    // When modal opens, if editing and subscribable_type is set, load options
    if (props.product && formData.subscribable_type) {
      // Small delay to ensure formData is set
      setTimeout(() => {
        loadSubscribables()
      }, 100)
    }
  }
})

const submitForm = async () => {
  if (isSubmitting.value || !isFormValid.value) return

  isSubmitting.value = true
  errorMessage.value = null
  errors.value = {}

  const url = editingProduct.value
    ? route('vue.members.products.update', { id: props.memberId, productId: props.product.id })
    : route('vue.members.products.store', { id: props.memberId })
  
  const method = editingProduct.value ? 'put' : 'post'
  
  // Prepare data with proper types
  const submitData = {
    subscribable_type: formData.subscribable_type,
    subscribable_id: parseInt(formData.subscribable_id),
    product_type: formData.product_type,
    discount: formData.discount ? formData.discount / 100 : 0, // Convert percentage to decimal (0-1 range)
    start_date: formData.start_date,
    end_date: formData.end_date,
    is_active: Boolean(formData.is_active)
  }
  
  console.log('Submitting product data:', submitData)
  console.log('Request URL:', url)
  
  try {
    const response = await axios[method](url, submitData)

    if (response.data.success) {
      emit('success')
      emit('close')
    }
  } catch (error) {
    console.error('Product submission error:', error)
    console.error('Error response:', error.response)
    console.error('Error data:', error.response?.data)
    console.error('Request URL:', url)
    console.error('Request data:', submitData)
    
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
      errorMessage.value = 'Please fix the validation errors below'
    } else {
      const errorMsg = error.response?.data?.message || error.message || `Failed to ${editingProduct.value ? 'update' : 'add'} product`
      errorMessage.value = errorMsg
      console.error('Error message:', errorMsg)
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>


