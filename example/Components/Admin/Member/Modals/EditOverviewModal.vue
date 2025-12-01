<template>
  <Modal :show="show" title="Edit Member Overview" max-width="2xl" @close="$emit('close')">
    <div v-if="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
      {{ errorMessage }}
    </div>

    <form @submit.prevent="submitForm" class="space-y-4">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Member Code *</label>
          <input
            v-model="formData.m_code"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
            required
          />
          <p v-if="errors.m_code" class="mt-1 text-sm text-red-600">{{ errors.m_code }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Legacy Member Code</label>
          <input
            v-model="formData.legacy_m_code"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
          <input
            v-model="formData.first_name"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
            required
          />
          <p v-if="errors.first_name" class="mt-1 text-sm text-red-600">{{ errors.first_name }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
          <input
            v-model="formData.last_name"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
            required
          />
          <p v-if="errors.last_name" class="mt-1 text-sm text-red-600">{{ errors.last_name }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
          <input
            v-model="formData.email"
            type="email"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
            required
          />
          <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
          <input
            v-model="formData.phone"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Mobile</label>
          <input
            v-model="formData.mobile"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <input
            v-model="formData.status"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="flex items-center">
            <input
              v-model="formData.is_active"
              type="checkbox"
              class="rounded border-gray-300 text-ciba-green focus:ring-ciba-green"
            />
            <span class="ml-2 text-sm font-medium text-gray-700">Active</span>
          </label>
        </div>

        <div>
          <label class="flex items-center">
            <input
              v-model="formData.is_suspended"
              type="checkbox"
              class="rounded border-gray-300 text-ciba-green focus:ring-ciba-green"
            />
            <span class="ml-2 text-sm font-medium text-gray-700">Suspended</span>
          </label>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Balance</label>
          <input
            v-model.number="formData.balance"
            type="number"
            step="0.01"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Future Balance</label>
          <input
            v-model.number="formData.future_balance"
            type="number"
            step="0.01"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Billing Cycle Month</label>
          <input
            v-model.number="formData.billing_cycle_month"
            type="number"
            min="1"
            max="12"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Billing Cycle Type</label>
          <input
            v-model="formData.billing_cycle_type"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
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
  }
})

const emit = defineEmits(['close', 'success'])

const isSubmitting = ref(false)
const errorMessage = ref(null)
const errors = ref({})

const formData = reactive({
  m_code: props.member.m_code || '',
  legacy_m_code: props.member.legacy_m_code || props.member.m_code || '',
  first_name: props.member.first_name || '',
  last_name: props.member.last_name || '',
  email: props.member.email || '',
  phone: props.member.phone || '',
  mobile: props.member.mobile || '',
  status: props.member.status || '',
  is_active: props.member.is_active || false,
  is_suspended: props.member.is_suspended || false,
  balance: props.member.balance || 0,
  future_balance: props.member.future_balance || 0,
  billing_cycle_month: props.member.billing_cycle_month || null,
  billing_cycle_type: props.member.billing_cycle_type || ''
})

watch(() => props.member, (newMember) => {
  if (newMember) {
    Object.assign(formData, {
      m_code: newMember.m_code || '',
      legacy_m_code: newMember.legacy_m_code || newMember.m_code || '',
      first_name: newMember.first_name || '',
      last_name: newMember.last_name || '',
      email: newMember.email || '',
      phone: newMember.phone || '',
      mobile: newMember.mobile || '',
      status: newMember.status || '',
      is_active: newMember.is_active || false,
      is_suspended: newMember.is_suspended || false,
      balance: newMember.balance || 0,
      future_balance: newMember.future_balance || 0,
      billing_cycle_month: newMember.billing_cycle_month || null,
      billing_cycle_type: newMember.billing_cycle_type || ''
    })
  }
}, { deep: true })

const submitForm = async () => {
  if (isSubmitting.value) return

  isSubmitting.value = true
  errorMessage.value = null
  errors.value = {}

  try {
    const response = await axios.put(route('vue.members.update-overview', props.member.id), formData)

    if (response.data.success) {
      emit('success')
      emit('close')
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errorMessage.value = error.response?.data?.message || 'Failed to update member overview'
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

