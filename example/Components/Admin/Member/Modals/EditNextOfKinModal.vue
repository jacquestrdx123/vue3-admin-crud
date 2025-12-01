<template>
  <Modal :show="show" title="Edit Next of Kin Information" max-width="2xl" @close="$emit('close')">
    <div v-if="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
      {{ errorMessage }}
    </div>

    <form @submit.prevent="submitForm" class="space-y-4">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
          <input
            v-model="formData.next_of_kin_first_name"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
          <p v-if="errors.next_of_kin_first_name" class="mt-1 text-sm text-red-600">{{ errors.next_of_kin_first_name }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
          <input
            v-model="formData.next_of_kin_last_name"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
          <p v-if="errors.next_of_kin_last_name" class="mt-1 text-sm text-red-600">{{ errors.next_of_kin_last_name }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Relationship</label>
          <input
            v-model="formData.next_of_kin_relationship"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
          <p v-if="errors.next_of_kin_relationship" class="mt-1 text-sm text-red-600">{{ errors.next_of_kin_relationship }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
          <input
            v-model="formData.next_of_kin_number"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
          <p v-if="errors.next_of_kin_number" class="mt-1 text-sm text-red-600">{{ errors.next_of_kin_number }}</p>
        </div>

        <div class="col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input
            v-model="formData.next_of_kin_email"
            type="email"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
          <p v-if="errors.next_of_kin_email" class="mt-1 text-sm text-red-600">{{ errors.next_of_kin_email }}</p>
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
  nextOfKin: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['close', 'success'])

const isSubmitting = ref(false)
const errorMessage = ref(null)
const errors = ref({})

const formData = reactive({
  next_of_kin_first_name: props.nextOfKin?.first_name || props.member.next_of_kin_first_name || '',
  next_of_kin_last_name: props.nextOfKin?.last_name || props.member.next_of_kin_last_name || '',
  next_of_kin_relationship: props.nextOfKin?.relationship || props.member.next_of_kin_relationship || '',
  next_of_kin_number: props.nextOfKin?.number || props.member.next_of_kin_number || '',
  next_of_kin_email: props.nextOfKin?.email || props.member.next_of_kin_email || ''
})

watch(() => props.nextOfKin, (newNextOfKin) => {
  if (newNextOfKin) {
    Object.assign(formData, {
      next_of_kin_first_name: newNextOfKin.first_name || '',
      next_of_kin_last_name: newNextOfKin.last_name || '',
      next_of_kin_relationship: newNextOfKin.relationship || '',
      next_of_kin_number: newNextOfKin.number || '',
      next_of_kin_email: newNextOfKin.email || ''
    })
  }
}, { deep: true })

const submitForm = async () => {
  if (isSubmitting.value) return

  isSubmitting.value = true
  errorMessage.value = null
  errors.value = {}

  try {
    const response = await axios.put(route('vue.members.update-next-of-kin', props.member.id), formData)

    if (response.data.success) {
      emit('success')
      emit('close')
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errorMessage.value = error.response?.data?.message || 'Failed to update next of kin information'
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

