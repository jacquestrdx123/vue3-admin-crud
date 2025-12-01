<template>
  <div class="space-y-4">
    <div v-if="successMessage" class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
      {{ successMessage }}
    </div>

    <div v-if="errorMessage" class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
      {{ errorMessage }}
    </div>

    <form @submit.prevent="submitForm" class="space-y-4">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
          <select
            v-model="formData.title"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
            required
          >
            <option value="">Select Title</option>
            <option value="Mr">Mr</option>
            <option value="Ms">Ms</option>
            <option value="Mrs">Mrs</option>
            <option value="Dr">Dr</option>
            <option value="Prof">Prof</option>
          </select>
          <p v-if="errors.title" class="mt-1 text-sm text-red-600">{{ getErrorMessage('title') }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
          <input
            v-model="formData.first_name"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
            required
          />
          <p v-if="errors.first_name" class="mt-1 text-sm text-red-600">{{ getErrorMessage('first_name') }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
          <input
            v-model="formData.middle_name"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
          <p v-if="errors.middle_name" class="mt-1 text-sm text-red-600">{{ getErrorMessage('middle_name') }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
          <input
            v-model="formData.last_name"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
            required
          />
          <p v-if="errors.last_name" class="mt-1 text-sm text-red-600">{{ getErrorMessage('last_name') }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Gender *</label>
          <select
            v-model="formData.gender"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
            required
          >
            <option value="">Select Gender</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
          </select>
          <p v-if="errors.gender" class="mt-1 text-sm text-red-600">{{ getErrorMessage('gender') }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth *</label>
          <input
            v-model="formData.date_of_birth"
            type="date"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
            required
          />
          <p v-if="errors.date_of_birth" class="mt-1 text-sm text-red-600">{{ getErrorMessage('date_of_birth') }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Equity Code *</label>
          <select
            v-model="formData.equity_code"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
            required
          >
            <option value="">Select Equity Code</option>
            <option value="BA">Black: African</option>
            <option value="BC">Coloured</option>
            <option value="BI">Indian/Asian</option>
            <option value="Wh">White</option>
            <option value="Oth">Other</option>
          </select>
          <p v-if="errors.equity_code" class="mt-1 text-sm text-red-600">{{ getErrorMessage('equity_code') }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nationality *</label>
          <select
            v-model="formData.nationality_code"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
            required
          >
            <option value="">Select Nationality</option>
            <option v-for="country in countries" :key="country.code" :value="country.code">
              {{ country.name }}
            </option>
          </select>
          <p v-if="errors.nationality_code" class="mt-1 text-sm text-red-600">{{ getErrorMessage('nationality_code') }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Home Language *</label>
          <select
            v-model.number="formData.home_language"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
            required
          >
            <option value="">Select Home Language</option>
            <option v-for="language in homeLanguages" :key="language.id" :value="language.id">
              {{ language.name }}
            </option>
          </select>
          <p v-if="errors.home_language" class="mt-1 text-sm text-red-600">{{ getErrorMessage('home_language') }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Preferred Language *</label>
          <select
            v-model.number="formData.preferred_language"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
            required
          >
            <option value="">Select Preferred Language</option>
            <option v-for="language in homeLanguages" :key="language.id" :value="language.id">
              {{ language.name }}
            </option>
          </select>
          <p v-if="errors.preferred_language" class="mt-1 text-sm text-red-600">{{ getErrorMessage('preferred_language') }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Has Other Citizenship *</label>
          <select
            v-model="formData.has_other_citizenship"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
            required
          >
            <option value="">Select Option</option>
            <option value="yes">Yes</option>
            <option value="no">No</option>
            <option value="na">Not Applicable</option>
          </select>
          <p v-if="errors.has_other_citizenship" class="mt-1 text-sm text-red-600">{{ getErrorMessage('has_other_citizenship') }}</p>
        </div>
      </div>

      <div class="flex justify-end gap-3 pt-4">
        <button
          type="submit"
          :disabled="submitting"
          class="px-6 py-2 bg-ciba-green hover:bg-[#A9E204] text-black font-semibold rounded-full transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
        >
          <span v-if="submitting">Saving...</span>
          <span v-else>Save Changes</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  member: { type: Object, required: true },
  applicationId: { type: [Number, String], required: true },
  countries: { type: Array, default: () => [] },
  homeLanguages: { type: Array, default: () => [] },
  maritalStatuses: { type: Array, default: () => [] }
})

const emit = defineEmits(['completion-updated'])

const submitting = ref(false)
const successMessage = ref(null)
const errorMessage = ref(null)
const errors = ref({})

const formData = reactive({
  title: props.member.title || '',
  first_name: props.member.first_name || '',
  middle_name: props.member.middle_name || '',
  last_name: props.member.last_name || '',
  gender: props.member.gender || '',
  date_of_birth: props.member.date_of_birth || '',
  equity_code: props.member.equity_code || '',
  nationality_code: props.member.nationality_code || '',
  has_other_citizenship: props.member.has_other_citizenship || '',
  identity_number: props.member.identity_number || '',
  passport_number: props.member.passport_number || '',
  home_language: props.member.home_language || '',
  preferred_language: props.member.preferred_language || ''
})

watch(() => props.member, (newMember) => {
  Object.assign(formData, {
    title: newMember.title || '',
    first_name: newMember.first_name || '',
    middle_name: newMember.middle_name || '',
    last_name: newMember.last_name || '',
    gender: newMember.gender || '',
    date_of_birth: newMember.date_of_birth || '',
    equity_code: newMember.equity_code || '',
    nationality_code: newMember.nationality_code || '',
    has_other_citizenship: newMember.has_other_citizenship || '',
    identity_number: newMember.identity_number || '',
    passport_number: newMember.passport_number || '',
    home_language: newMember.home_language || '',
    preferred_language: newMember.preferred_language || ''
  })
}, { deep: true })

function getErrorMessage(field) {
  const error = errors.value[field]
  if (Array.isArray(error) && error.length > 0) {
    return error[0]
  }
  if (typeof error === 'string') {
    return error
  }
  return null
}

async function submitForm() {
  if (submitting.value) return

  submitting.value = true
  successMessage.value = null
  errorMessage.value = null
  errors.value = {}

  try {
    const response = await axios.post(
      route('vue.member.application-wizard.update-personal-details', props.applicationId),
      formData
    )

    successMessage.value = response.data.message || 'Personal details updated successfully'
    emit('completion-updated', {
      field: 'personal_details',
      percentage: response.data.completion_percentage
    })

    setTimeout(() => {
      successMessage.value = null
    }, 3000)
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    }
    errorMessage.value = error.response?.data?.message || 'Failed to update personal details'
  } finally {
    submitting.value = false
  }
}
</script>

<style scoped>
.bg-ciba-green {
  background-color: #BAF504;
}
</style>

