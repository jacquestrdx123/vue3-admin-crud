<template>
  <Modal :show="show" title="Edit Conduct & Declaration History" max-width="2xl" @close="$emit('close')">
    <div v-if="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
      {{ errorMessage }}
    </div>

    <form @submit.prevent="submitForm" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Registered with another professional body?</label>
        <select
          v-model="formData.registered_another_professional_body"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
        >
          <option value="">Select</option>
          <option value="yes">Yes</option>
          <option value="no">No</option>
        </select>
        <p v-if="errors.registered_another_professional_body" class="mt-1 text-sm text-red-600">{{ errors.registered_another_professional_body }}</p>
      </div>

      <div v-if="formData.registered_another_professional_body === 'yes'">
        <label class="block text-sm font-medium text-gray-700 mb-1">Specify</label>
        <input
          v-model="formData.registered_another_professional_body_specify"
          type="text"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
        />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Removed from professional body?</label>
        <select
          v-model="formData.removed_from_professional_body"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
        >
          <option value="">Select</option>
          <option value="yes">Yes</option>
          <option value="no">No</option>
        </select>
        <p v-if="errors.removed_from_professional_body" class="mt-1 text-sm text-red-600">{{ errors.removed_from_professional_body }}</p>
      </div>

      <div v-if="formData.removed_from_professional_body === 'yes'">
        <label class="block text-sm font-medium text-gray-700 mb-1">Specify</label>
        <input
          v-model="formData.removed_from_professional_body_specify"
          type="text"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
        />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Convicted of theft/forgery?</label>
        <select
          v-model="formData.theft_forgery"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
        >
          <option value="">Select</option>
          <option value="yes">Yes</option>
          <option value="no">No</option>
        </select>
        <p v-if="errors.theft_forgery" class="mt-1 text-sm text-red-600">{{ errors.theft_forgery }}</p>
      </div>

      <div v-if="formData.theft_forgery === 'yes'">
        <label class="block text-sm font-medium text-gray-700 mb-1">Specify</label>
        <input
          v-model="formData.theft_forgery_specify"
          type="text"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
        />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Un-rehabilitated/insolvent?</label>
        <select
          v-model="formData.unrehabilitated_insolvent"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
        >
          <option value="">Select</option>
          <option value="yes">Yes</option>
          <option value="no">No</option>
        </select>
        <p v-if="errors.unrehabilitated_insolvent" class="mt-1 text-sm text-red-600">{{ errors.unrehabilitated_insolvent }}</p>
      </div>

      <div v-if="formData.unrehabilitated_insolvent === 'yes'">
        <label class="block text-sm font-medium text-gray-700 mb-1">Specify</label>
        <input
          v-model="formData.unrehabilitated_insolvent_specify"
          type="text"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
        />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Removed from office of trust?</label>
        <select
          v-model="formData.office_trust_misconduct"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
        >
          <option value="">Select</option>
          <option value="yes">Yes</option>
          <option value="no">No</option>
        </select>
        <p v-if="errors.office_trust_misconduct" class="mt-1 text-sm text-red-600">{{ errors.office_trust_misconduct }}</p>
      </div>

      <div v-if="formData.office_trust_misconduct === 'yes'">
        <label class="block text-sm font-medium text-gray-700 mb-1">Specify</label>
        <input
          v-model="formData.office_trust_misconduct_specify"
          type="text"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
        />
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
  conduct: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['close', 'success'])

const isSubmitting = ref(false)
const errorMessage = ref(null)
const errors = ref({})

const formData = reactive({
  registered_another_professional_body: props.conduct?.registered_another_professional_body || props.member.registered_another_professional_body || '',
  registered_another_professional_body_specify: props.conduct?.registered_another_professional_body_specify || props.member.registered_another_professional_body_specify || '',
  removed_from_professional_body: props.conduct?.removed_from_professional_body || props.member.removed_from_professional_body || '',
  removed_from_professional_body_specify: props.conduct?.removed_from_professional_body_specify || props.member.removed_from_professional_body_specify || '',
  theft_forgery: props.conduct?.theft_forgery || props.member.theft_forgery || '',
  theft_forgery_specify: props.conduct?.theft_forgery_specify || props.member.theft_forgery_specify || '',
  unrehabilitated_insolvent: props.conduct?.unrehabilitated_insolvent || props.member.unrehabilitated_insolvent || '',
  unrehabilitated_insolvent_specify: props.conduct?.unrehabilitated_insolvent_specify || props.member.unrehabilitated_insolvent_specify || '',
  office_trust_misconduct: props.conduct?.office_trust_misconduct || props.member.office_trust_misconduct || '',
  office_trust_misconduct_specify: props.conduct?.office_trust_misconduct_specify || props.member.office_trust_misconduct_specify || ''
})

watch(() => props.conduct, (newConduct) => {
  if (newConduct) {
    Object.assign(formData, {
      registered_another_professional_body: newConduct.registered_another_professional_body || '',
      registered_another_professional_body_specify: newConduct.registered_another_professional_body_specify || '',
      removed_from_professional_body: newConduct.removed_from_professional_body || '',
      removed_from_professional_body_specify: newConduct.removed_from_professional_body_specify || '',
      theft_forgery: newConduct.theft_forgery || '',
      theft_forgery_specify: newConduct.theft_forgery_specify || '',
      unrehabilitated_insolvent: newConduct.unrehabilitated_insolvent || '',
      unrehabilitated_insolvent_specify: newConduct.unrehabilitated_insolvent_specify || '',
      office_trust_misconduct: newConduct.office_trust_misconduct || '',
      office_trust_misconduct_specify: newConduct.office_trust_misconduct_specify || ''
    })
  }
}, { deep: true })

const submitForm = async () => {
  if (isSubmitting.value) return

  isSubmitting.value = true
  errorMessage.value = null
  errors.value = {}

  try {
    const response = await axios.put(route('vue.members.update-conduct-history', props.member.id), formData)

    if (response.data.success) {
      emit('success')
      emit('close')
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errorMessage.value = error.response?.data?.message || 'Failed to update conduct history'
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

