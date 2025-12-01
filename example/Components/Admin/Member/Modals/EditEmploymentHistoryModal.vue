<template>
  <Modal :show="show" :title="editingHistory ? 'Edit Employment History' : 'Add Employment History'" max-width="4xl" @close="$emit('close')">
    <div v-if="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
      {{ errorMessage }}
    </div>

    <form @submit.prevent="submitForm" class="space-y-6">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Company Name *</label>
          <input
            v-model="formData.company_name"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
            required
          />
          <p v-if="errors.company_name" class="mt-1 text-sm text-red-600">{{ errors.company_name }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Job Title *</label>
          <input
            v-model="formData.job_title"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
            required
          />
          <p v-if="errors.job_title" class="mt-1 text-sm text-red-600">{{ errors.job_title }}</p>
        </div>

        <div>
          <label class="flex items-center">
            <input
              v-model="formData.is_current"
              type="checkbox"
              class="rounded border-gray-300 text-ciba-green focus:ring-ciba-green"
            />
            <span class="ml-2 text-sm font-medium text-gray-700">Current Employment</span>
          </label>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label>
          <input
            v-model="formData.start_date"
            type="date"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
            required
          />
          <p v-if="errors.start_date" class="mt-1 text-sm text-red-600">{{ errors.start_date }}</p>
        </div>

        <div v-if="!formData.is_current">
          <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
          <input
            v-model="formData.end_date"
            type="date"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
          <p v-if="errors.end_date" class="mt-1 text-sm text-red-600">{{ errors.end_date }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Employer Contact Number</label>
          <input
            v-model="formData.employer_contact_number"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Employment Sector</label>
          <select
            v-model="formData.employment_sector_id"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          >
            <option value="">Select Sector</option>
            <option v-for="sector in employmentSectors" :key="sector.id" :value="sector.id">
              {{ sector.name }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Employment Industry</label>
          <select
            v-model="formData.employment_industry_id"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          >
            <option value="">Select Industry</option>
            <option v-for="industry in employmentIndustries" :key="industry.id" :value="industry.id">
              {{ industry.name }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Type of Employment</label>
          <select
            v-model="formData.type_of_employment_id"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          >
            <option value="">Select Type</option>
            <option v-for="type in typesOfEmployment" :key="type.id" :value="type.id">
              {{ type.name }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Employment Status</label>
          <select
            v-model="formData.employment_status_id"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          >
            <option value="">Select Status</option>
            <option v-for="status in employmentStatuses" :key="status.id" :value="status.id">
              {{ status.name }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Employer Address</label>
          <input
            v-model="formData.employer_address"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Employer City</label>
          <input
            v-model="formData.employer_city"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Employer State</label>
          <input
            v-model="formData.employer_state"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Employer Postal Code</label>
          <input
            v-model="formData.employer_postal_code"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div class="col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">Other Details</label>
          <textarea
            v-model="formData.other_details"
            rows="3"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          ></textarea>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Reference Name</label>
          <input
            v-model="formData.reference_name"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Reference Position</label>
          <input
            v-model="formData.reference_position"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Reference Contact Number</label>
          <input
            v-model="formData.reference_contact_number"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Reference Email</label>
          <input
            v-model="formData.reference_email"
            type="email"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div class="col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">Reference Relationship</label>
          <input
            v-model="formData.reference_relationship"
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
          <span v-if="isSubmitting">{{ editingHistory ? 'Updating...' : 'Adding...' }}</span>
          <span v-else>{{ editingHistory ? 'Update Employment' : 'Add Employment' }}</span>
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
  memberId: {
    type: [Number, String],
    required: true
  },
  history: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'success'])

const isSubmitting = ref(false)
const errorMessage = ref(null)
const errors = ref({})
const editingHistory = ref(!!props.history)
const employmentSectors = ref([])
const employmentIndustries = ref([])
const typesOfEmployment = ref([])
const employmentStatuses = ref([])

const formData = reactive({
  company_name: props.history?.company_name || '',
  job_title: props.history?.job_title || '',
  is_current: props.history?.is_current || false,
  start_date: props.history?.start_date || '',
  end_date: props.history?.end_date || '',
  employer_contact_number: props.history?.employer_contact_number || '',
  employment_sector_id: props.history?.employment_sector_id || '',
  employment_industry_id: props.history?.employment_industry_id || '',
  type_of_employment_id: props.history?.type_of_employment_id || '',
  employment_status_id: props.history?.employment_status_id || '',
  employer_address: props.history?.employer_address || '',
  employer_city: props.history?.employer_city || '',
  employer_state: props.history?.employer_state || '',
  employer_postal_code: props.history?.employer_postal_code || '',
  other_details: props.history?.other_details || '',
  reference_name: props.history?.reference_name || '',
  reference_position: props.history?.reference_position || '',
  reference_contact_number: props.history?.reference_contact_number || '',
  reference_email: props.history?.reference_email || '',
  reference_relationship: props.history?.reference_relationship || ''
})

onMounted(async () => {
  // Load options - these might need API endpoints or be passed as props
  // For now, we'll leave them empty and they can be populated later
})

watch(() => props.history, (newHistory) => {
  if (newHistory) {
    editingHistory.value = true
    Object.assign(formData, {
      company_name: newHistory.company_name || '',
      job_title: newHistory.job_title || '',
      is_current: newHistory.is_current || false,
      start_date: newHistory.start_date || '',
      end_date: newHistory.end_date || '',
      employer_contact_number: newHistory.employer_contact_number || '',
      employment_sector_id: newHistory.employment_sector_id || '',
      employment_industry_id: newHistory.employment_industry_id || '',
      type_of_employment_id: newHistory.type_of_employment_id || '',
      employment_status_id: newHistory.employment_status_id || '',
      employer_address: newHistory.employer_address || '',
      employer_city: newHistory.employer_city || '',
      employer_state: newHistory.employer_state || '',
      employer_postal_code: newHistory.employer_postal_code || '',
      other_details: newHistory.other_details || '',
      reference_name: newHistory.reference_name || '',
      reference_position: newHistory.reference_position || '',
      reference_contact_number: newHistory.reference_contact_number || '',
      reference_email: newHistory.reference_email || '',
      reference_relationship: newHistory.reference_relationship || ''
    })
  } else {
    editingHistory.value = false
    Object.assign(formData, {
      company_name: '',
      job_title: '',
      is_current: false,
      start_date: '',
      end_date: '',
      employer_contact_number: '',
      employment_sector_id: '',
      employment_industry_id: '',
      type_of_employment_id: '',
      employment_status_id: '',
      employer_address: '',
      employer_city: '',
      employer_state: '',
      employer_postal_code: '',
      other_details: '',
      reference_name: '',
      reference_position: '',
      reference_contact_number: '',
      reference_email: '',
      reference_relationship: ''
    })
  }
}, { deep: true })

const submitForm = async () => {
  if (isSubmitting.value) return

  isSubmitting.value = true
  errorMessage.value = null
  errors.value = {}

  try {
    const url = editingHistory.value
      ? route('vue.members.employment-history.update', { id: props.memberId, historyId: props.history.id })
      : route('vue.members.employment-history.store', props.memberId)

    const method = editingHistory.value ? 'put' : 'post'
    const response = await axios[method](url, formData)

    if (response.data.success) {
      emit('success')
      emit('close')
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errorMessage.value = error.response?.data?.message || `Failed to ${editingHistory.value ? 'update' : 'create'} employment history`
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

