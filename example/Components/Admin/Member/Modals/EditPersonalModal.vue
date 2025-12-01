<template>
  <Modal :show="show" title="Edit Personal Information" max-width="4xl" @close="$emit('close')">
    <div v-if="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
      {{ errorMessage }}
    </div>

    <form @submit.prevent="submitForm" class="space-y-6">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
          <select
            v-model="formData.title"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          >
            <option value="">Select Title</option>
            <option value="Mr">Mr</option>
            <option value="Ms">Ms</option>
            <option value="Mrs">Mrs</option>
            <option value="Dr">Dr</option>
            <option value="Prof">Prof</option>
          </select>
          <p v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
          <select
            v-model="formData.gender"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          >
            <option value="">Select Gender</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
          </select>
          <p v-if="errors.gender" class="mt-1 text-sm text-red-600">{{ errors.gender }}</p>
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
          <label class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
          <input
            v-model="formData.middle_name"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
          <p v-if="errors.middle_name" class="mt-1 text-sm text-red-600">{{ errors.middle_name }}</p>
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
          <label class="block text-sm font-medium text-gray-700 mb-1">Maiden Surname</label>
          <input
            v-model="formData.maiden_surname"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Identity Number</label>
          <input
            v-model="formData.identity_number"
            type="text"
            maxlength="13"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
          <p v-if="errors.identity_number" class="mt-1 text-sm text-red-600">{{ errors.identity_number }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Passport Number</label>
          <input
            v-model="formData.passport_number"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
          <input
            v-model="formData.date_of_birth"
            type="date"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
          <p v-if="errors.date_of_birth" class="mt-1 text-sm text-red-600">{{ errors.date_of_birth }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nationality</label>
          <input
            v-model="formData.nationality_code"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
            placeholder="e.g. ZA"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Marital Status</label>
          <input
            v-model="formData.marital_status"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Home Language</label>
          <input
            v-model="formData.home_language"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Mobile Number</label>
          <input
            v-model="formData.mobile_number"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Alternative Mobile</label>
          <input
            v-model="formData.alternative_mobile_number"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Work Number</label>
          <input
            v-model="formData.work_number"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Alternative Email</label>
          <input
            v-model="formData.alternative_email"
            type="email"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Current Status</label>
          <input
            v-model="formData.current_status"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Current Job Role</label>
          <input
            v-model="formData.current_job_role"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Employer Name</label>
          <input
            v-model="formData.employer_name"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Years of Experience</label>
          <input
            v-model.number="formData.years_of_work_experience"
            type="number"
            min="0"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Highest Education</label>
          <input
            v-model="formData.highest_level_of_education"
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
  title: props.member.title || '',
  first_name: props.member.first_name || '',
  middle_name: props.member.middle_name || '',
  last_name: props.member.last_name || '',
  maiden_surname: props.member.maiden_surname || '',
  identity_number: props.member.identity_number || '',
  passport_number: props.member.passport_number || '',
  date_of_birth: props.member.date_of_birth || '',
  gender: props.member.gender || '',
  nationality_code: props.member.nationality_code || '',
  marital_status: props.member.marital_status || '',
  home_language: props.member.home_language || '',
  mobile_number: props.member.mobile_number || '',
  alternative_mobile_number: props.member.alternative_mobile_number || '',
  work_number: props.member.work_number || '',
  alternative_email: props.member.alternative_email || '',
  current_status: props.member.current_status || '',
  current_job_role: props.member.current_job_role || '',
  employer_name: props.member.employer_name || '',
  years_of_work_experience: props.member.years_of_work_experience || null,
  highest_level_of_education: props.member.highest_level_of_education || ''
})

watch(() => props.member, (newMember) => {
  if (newMember) {
    Object.assign(formData, {
      title: newMember.title || '',
      first_name: newMember.first_name || '',
      middle_name: newMember.middle_name || '',
      last_name: newMember.last_name || '',
      maiden_surname: newMember.maiden_surname || '',
      identity_number: newMember.identity_number || '',
      passport_number: newMember.passport_number || '',
      date_of_birth: newMember.date_of_birth || '',
      gender: newMember.gender || '',
      nationality_code: newMember.nationality_code || '',
      marital_status: newMember.marital_status || '',
      home_language: newMember.home_language || '',
      mobile_number: newMember.mobile_number || '',
      alternative_mobile_number: newMember.alternative_mobile_number || '',
      work_number: newMember.work_number || '',
      alternative_email: newMember.alternative_email || '',
      current_status: newMember.current_status || '',
      current_job_role: newMember.current_job_role || '',
      employer_name: newMember.employer_name || '',
      years_of_work_experience: newMember.years_of_work_experience || null,
      highest_level_of_education: newMember.highest_level_of_education || ''
    })
  }
}, { deep: true })

const submitForm = async () => {
  if (isSubmitting.value) return

  isSubmitting.value = true
  errorMessage.value = null
  errors.value = {}

  try {
    const response = await axios.put(route('vue.members.update-personal', props.member.id), formData)

    if (response.data.success) {
      emit('success')
      emit('close')
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errorMessage.value = error.response?.data?.message || 'Failed to update personal information'
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

