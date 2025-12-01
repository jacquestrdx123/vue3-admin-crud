<template>
  <Modal :show="show" :title="editingMembership ? 'Edit Required Membership' : 'Add Required Membership'" max-width="md" @close="$emit('close')">
    <div v-if="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
      {{ errorMessage }}
    </div>

    <form @submit.prevent="submitForm" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Membership *</label>
        <select
          v-model="formData.membership_id"
          class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          required
        >
          <option value="">Select Membership</option>
          <option v-for="membership in memberships" :key="membership.id" :value="membership.id">
            {{ membership.name }}
          </option>
        </select>
        <p v-if="errors.membership_id" class="mt-1 text-sm text-red-600">{{ errors.membership_id }}</p>
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
          <span v-else>{{ editingMembership ? 'Update' : 'Add' }} Membership</span>
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
  membership: {
    type: Object,
    default: null
  },
  memberships: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['close', 'success'])

const isSubmitting = ref(false)
const errorMessage = ref(null)
const errors = ref({})
const editingMembership = ref(!!props.membership)

const formData = reactive({
  membership_id: props.membership?.membership_id || ''
})

watch(() => props.membership, (newMembership) => {
  if (newMembership) {
    editingMembership.value = true
    formData.membership_id = newMembership.membership_id || ''
  } else {
    editingMembership.value = false
    formData.membership_id = ''
  }
}, { deep: true })

watch(() => props.show, (isShowing) => {
  if (!isShowing) {
    editingMembership.value = false
    formData.membership_id = ''
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
    const routeName = editingMembership.value
      ? 'vue.designations.required-memberships.update'
      : 'vue.designations.required-memberships.store'
    
    const routeParams = editingMembership.value
      ? { id: props.designationId, membershipId: props.membership.id }
      : { id: props.designationId }

    const method = editingMembership.value ? 'put' : 'post'
    const response = await axios[method](route(routeName, routeParams), formData)

    if (response.data.success) {
      emit('success')
      emit('close')
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errorMessage.value = error.response?.data?.message || 'Failed to save required membership'
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

