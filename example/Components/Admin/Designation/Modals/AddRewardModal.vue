<template>
  <Modal :show="show" :title="editingReward ? 'Edit Reward' : 'Add Reward'" max-width="lg" @close="$emit('close')">
    <div v-if="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
      {{ errorMessage }}
    </div>

    <form @submit.prevent="submitForm" class="space-y-4">
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
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">URL</label>
        <input
          v-model="formData.url"
          type="url"
          class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
        />
        <p v-if="errors.url" class="mt-1 text-sm text-red-600">{{ errors.url }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Link</label>
        <input
          v-model="formData.link"
          type="text"
          class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
        />
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Discount Percentage (%)</label>
          <input
            v-model.number="formData.discount_percentage"
            type="number"
            min="0"
            max="100"
            step="0.01"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Discount Flat Amount</label>
          <input
            v-model.number="formData.discount_flat_amount"
            type="number"
            min="0"
            step="0.01"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">External Company</label>
        <select
          v-model="formData.external_company_id"
          class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
        >
          <option value="">Select External Company</option>
          <option v-for="company in externalCompanies" :key="company.id" :value="company.id">
            {{ company.name }}
          </option>
        </select>
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
          <span v-else>{{ editingReward ? 'Update' : 'Add' }} Reward</span>
        </button>
      </div>
    </template>
  </Modal>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
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
  reward: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'success'])

const isSubmitting = ref(false)
const errorMessage = ref(null)
const errors = ref({})
const externalCompanies = ref([])
const editingReward = ref(!!props.reward)

const formData = reactive({
  name: props.reward?.name || '',
  url: props.reward?.url || '',
  link: props.reward?.link || '',
  discount_percentage: props.reward?.discount_percentage || null,
  discount_flat_amount: props.reward?.discount_flat_amount || null,
  external_company_id: props.reward?.external_company_id || ''
})

watch(() => props.reward, (newReward) => {
  if (newReward) {
    editingReward.value = true
    formData.name = newReward.name || ''
    formData.url = newReward.url || ''
    formData.link = newReward.link || ''
    formData.discount_percentage = newReward.discount_percentage || null
    formData.discount_flat_amount = newReward.discount_flat_amount || null
    formData.external_company_id = newReward.external_company_id || ''
  } else {
    editingReward.value = false
    formData.name = ''
    formData.url = ''
    formData.link = ''
    formData.discount_percentage = null
    formData.discount_flat_amount = null
    formData.external_company_id = ''
  }
}, { deep: true })

watch(() => props.show, (isShowing) => {
  if (!isShowing) {
    editingReward.value = false
    formData.name = ''
    formData.url = ''
    formData.link = ''
    formData.discount_percentage = null
    formData.discount_flat_amount = null
    formData.external_company_id = ''
    errorMessage.value = null
    errors.value = {}
  }
})

onMounted(async () => {
  try {
    const response = await axios.get(route('vue.designations.options', { type: 'external-companies' }))
    externalCompanies.value = response.data || []
  } catch (error) {
    console.error('Failed to load external companies:', error)
  }
})

const submitForm = async () => {
  if (isSubmitting.value) return

  isSubmitting.value = true
  errorMessage.value = null
  errors.value = {}

  try {
    const routeName = editingReward.value
      ? 'vue.designations.rewards.update'
      : 'vue.designations.rewards.store'
    
    const routeParams = editingReward.value
      ? { id: props.designationId, rewardId: props.reward.id }
      : { id: props.designationId }

    const method = editingReward.value ? 'put' : 'post'
    const response = await axios[method](route(routeName, routeParams), formData)

    if (response.data.success) {
      emit('success')
      emit('close')
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errorMessage.value = error.response?.data?.message || 'Failed to save reward'
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

