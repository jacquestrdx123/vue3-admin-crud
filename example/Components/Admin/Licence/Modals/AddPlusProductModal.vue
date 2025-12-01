<template>
  <Modal :show="show" :title="editingPlusProduct ? 'Edit Plus Product' : 'Add Plus Product'" max-width="2xl" @close="$emit('close')">
    <div v-if="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
      {{ errorMessage }}
    </div>

    <form @submit.prevent="submitForm" class="space-y-4">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name *</label>
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
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Slug</label>
          <input
            v-model="formData.slug"
            type="text"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Rank</label>
          <input
            v-model.number="formData.rank"
            type="number"
            min="0"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Monthly Price</label>
          <input
            v-model.number="formData.monthly_price"
            type="number"
            step="0.01"
            min="0"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quarterly Price</label>
          <input
            v-model.number="formData.quarterly_price"
            type="number"
            step="0.01"
            min="0"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Yearly Price *</label>
          <input
            v-model.number="formData.yearly_price"
            type="number"
            step="0.01"
            min="0"
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
            min="0"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Verifiable CPD Units Required</label>
          <input
            v-model.number="formData.verifiable_cpd_units_required"
            type="number"
            min="0"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Non-Verifiable CPD Units Required</label>
          <input
            v-model.number="formData.non_verifiable_cpd_units_required"
            type="number"
            min="0"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
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
          <span v-else>{{ editingPlusProduct ? 'Update' : 'Add' }} Plus Product</span>
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
  plusProduct: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'success'])

const isSubmitting = ref(false)
const errorMessage = ref(null)
const errors = ref({})
const editingPlusProduct = ref(!!props.plusProduct)

const formData = reactive({
  name: props.plusProduct?.name || '',
  abbreviation: props.plusProduct?.abbreviation || '',
  slug: props.plusProduct?.slug || '',
  monthly_price: props.plusProduct?.monthly_price ? props.plusProduct.monthly_price / 100 : 0,
  quarterly_price: props.plusProduct?.quarterly_price ? props.plusProduct.quarterly_price / 100 : 0,
  yearly_price: props.plusProduct?.yearly_price ? props.plusProduct.yearly_price / 100 : 0,
  initial_fee: props.plusProduct?.initial_fee ? props.plusProduct.initial_fee / 100 : 0,
  rank: props.plusProduct?.rank || 0,
  verifiable_cpd_units_required: props.plusProduct?.verifiable_cpd_units_required || 0,
  non_verifiable_cpd_units_required: props.plusProduct?.non_verifiable_cpd_units_required || 0
})

watch(() => props.plusProduct, (newPlusProduct) => {
  if (newPlusProduct) {
    editingPlusProduct.value = true
    formData.name = newPlusProduct.name || ''
    formData.abbreviation = newPlusProduct.abbreviation || ''
    formData.slug = newPlusProduct.slug || ''
    formData.monthly_price = newPlusProduct.monthly_price ? newPlusProduct.monthly_price / 100 : 0
    formData.quarterly_price = newPlusProduct.quarterly_price ? newPlusProduct.quarterly_price / 100 : 0
    formData.yearly_price = newPlusProduct.yearly_price ? newPlusProduct.yearly_price / 100 : 0
    formData.initial_fee = newPlusProduct.initial_fee ? newPlusProduct.initial_fee / 100 : 0
    formData.rank = newPlusProduct.rank || 0
    formData.verifiable_cpd_units_required = newPlusProduct.verifiable_cpd_units_required || 0
    formData.non_verifiable_cpd_units_required = newPlusProduct.non_verifiable_cpd_units_required || 0
  } else {
    editingPlusProduct.value = false
    formData.name = ''
    formData.abbreviation = ''
    formData.slug = ''
    formData.monthly_price = 0
    formData.quarterly_price = 0
    formData.yearly_price = 0
    formData.initial_fee = 0
    formData.rank = 0
    formData.verifiable_cpd_units_required = 0
    formData.non_verifiable_cpd_units_required = 0
  }
}, { deep: true })

watch(() => props.show, (isShowing) => {
  if (!isShowing) {
    editingPlusProduct.value = false
    formData.name = ''
    formData.abbreviation = ''
    formData.slug = ''
    formData.monthly_price = 0
    formData.quarterly_price = 0
    formData.yearly_price = 0
    formData.initial_fee = 0
    formData.rank = 0
    formData.verifiable_cpd_units_required = 0
    formData.non_verifiable_cpd_units_required = 0
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
    const routeName = editingPlusProduct.value
      ? 'vue.licences.plus-products.update'
      : 'vue.licences.plus-products.store'
    
    const routeParams = editingPlusProduct.value
      ? { id: props.licenceId, plusId: props.plusProduct.id }
      : { id: props.licenceId }

    const method = editingPlusProduct.value ? 'put' : 'post'
    const response = await axios[method](route(routeName, routeParams), formData)

    if (response.data.success) {
      emit('success')
      emit('close')
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errorMessage.value = error.response?.data?.message || 'Failed to save plus product'
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

