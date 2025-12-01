<template>
  <Modal :show="show" :title="editingQualification ? 'Edit Qualification' : 'Add Qualification'" max-width="2xl" @close="$emit('close')">
    <div v-if="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
      {{ errorMessage }}
    </div>

    <form @submit.prevent="submitForm" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Qualification *</label>
        <select
          v-model="formData.qualification_id"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          required
        >
          <option value="">Select Qualification</option>
          <option v-for="qual in qualifications" :key="qual.id" :value="qual.id">
            {{ qual.name }} {{ qual.nqf_level ? `(NQF ${qual.nqf_level})` : '' }}
          </option>
        </select>
        <p v-if="errors.qualification_id" class="mt-1 text-sm text-red-600">{{ errors.qualification_id }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Institution *</label>
        <input
          v-model="formData.institution"
          type="text"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          placeholder="e.g. University of Cape Town"
          required
        />
        <p v-if="errors.institution" class="mt-1 text-sm text-red-600">{{ errors.institution }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Institution Type</label>
        <input
          v-model="formData.institution_type"
          type="text"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          placeholder="e.g. University, College, etc."
        />
        <p v-if="errors.institution_type" class="mt-1 text-sm text-red-600">{{ errors.institution_type }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Completion Date *</label>
        <input
          v-model="formData.completion_date"
          type="date"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          required
        />
        <p v-if="errors.completion_date" class="mt-1 text-sm text-red-600">{{ errors.completion_date }}</p>
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
          <span v-if="isSubmitting">{{ editingQualification ? 'Updating...' : 'Adding...' }}</span>
          <span v-else>{{ editingQualification ? 'Update Qualification' : 'Add Qualification' }}</span>
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
  qualification: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'success'])

const isSubmitting = ref(false)
const errorMessage = ref(null)
const errors = ref({})
const editingQualification = ref(!!props.qualification)
const qualifications = ref([])

const formData = reactive({
  qualification_id: props.qualification?.qualification_id || '',
  institution: props.qualification?.institution || '',
  institution_type: props.qualification?.institution_type || '',
  completion_date: props.qualification?.completion_date || ''
})

onMounted(async () => {
  try {
    const response = await axios.get(route('vue.member-qualifications.index'), { params: { per_page: 1000 } })
    qualifications.value = response.data?.data || []
  } catch (error) {
    // Try alternative route
    try {
      const response = await axios.get('/api/qualifications')
      qualifications.value = response.data || []
    } catch (err) {
      console.error('Failed to load qualifications:', err)
    }
  }
})

watch(() => props.qualification, (newQualification) => {
  if (newQualification) {
    editingQualification.value = true
    Object.assign(formData, {
      qualification_id: newQualification.qualification_id || '',
      institution: newQualification.institution || '',
      institution_type: newQualification.institution_type || '',
      completion_date: newQualification.completion_date || ''
    })
  } else {
    editingQualification.value = false
    Object.assign(formData, {
      qualification_id: '',
      institution: '',
      institution_type: '',
      completion_date: ''
    })
  }
}, { deep: true })

const submitForm = async () => {
  if (isSubmitting.value) return

  isSubmitting.value = true
  errorMessage.value = null
  errors.value = {}

  try {
    const url = editingQualification.value
      ? route('vue.members.qualifications.update', { id: props.memberId, qualificationId: props.qualification.id })
      : route('vue.members.qualifications.store', props.memberId)

    const method = editingQualification.value ? 'put' : 'post'
    const response = await axios[method](url, formData)

    if (response.data.success) {
      emit('success')
      emit('close')
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errorMessage.value = error.response?.data?.message || `Failed to ${editingQualification.value ? 'update' : 'create'} qualification`
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

