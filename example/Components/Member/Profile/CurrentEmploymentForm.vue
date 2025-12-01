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
          <label class="block text-sm font-medium text-gray-700 mb-1">Tax Years Experience</label>
          <input
            v-model.number="formData.tax_years_experience"
            type="number"
            min="0"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
          <p v-if="errors.tax_years_experience" class="mt-1 text-sm text-red-600">{{ errors.tax_years_experience }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">PR Number</label>
          <input
            v-model="formData.pr_number"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
            placeholder="e.g., PR-12345"
          />
          <p v-if="errors.pr_number" class="mt-1 text-sm text-red-600">{{ errors.pr_number }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Own Practice</label>
          <div class="flex gap-4">
            <label class="inline-flex items-center">
              <input
                v-model="formData.own_practice"
                type="radio"
                :value="true"
                class="rounded border-gray-300 text-ciba-green focus:ring-ciba-green"
              />
              <span class="ml-2 text-sm">Yes</span>
            </label>
            <label class="inline-flex items-center">
              <input
                v-model="formData.own_practice"
                type="radio"
                :value="false"
                class="rounded border-gray-300 text-ciba-green focus:ring-ciba-green"
              />
              <span class="ml-2 text-sm">No</span>
            </label>
          </div>
          <p v-if="errors.own_practice" class="mt-1 text-sm text-red-600">{{ errors.own_practice }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Registered on eFiling</label>
          <div class="flex gap-4">
            <label class="inline-flex items-center">
              <input
                v-model="formData.registered_on_e_filling"
                type="radio"
                :value="true"
                class="rounded border-gray-300 text-ciba-green focus:ring-ciba-green"
              />
              <span class="ml-2 text-sm">Yes</span>
            </label>
            <label class="inline-flex items-center">
              <input
                v-model="formData.registered_on_e_filling"
                type="radio"
                :value="false"
                class="rounded border-gray-300 text-ciba-green focus:ring-ciba-green"
              />
              <span class="ml-2 text-sm">No</span>
            </label>
          </div>
          <p v-if="errors.registered_on_e_filling" class="mt-1 text-sm text-red-600">{{ errors.registered_on_e_filling }}</p>
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
  applicationId: { type: [Number, String], required: true }
})

const emit = defineEmits(['completion-updated'])

const submitting = ref(false)
const successMessage = ref(null)
const errorMessage = ref(null)
const errors = ref({})

const formData = reactive({
  tax_years_experience: props.member.tax_years_experience || null,
  pr_number: props.member.pr_number || '',
  own_practice: props.member.own_practice ?? null,
  registered_on_e_filling: props.member.registered_on_e_filling ?? null
})

watch(() => props.member, (newMember) => {
  Object.assign(formData, {
    tax_years_experience: newMember.tax_years_experience || null,
    pr_number: newMember.pr_number || '',
    own_practice: newMember.own_practice ?? null,
    registered_on_e_filling: newMember.registered_on_e_filling ?? null
  })
}, { deep: true })

async function submitForm() {
  if (submitting.value) return

  submitting.value = true
  successMessage.value = null
  errorMessage.value = null
  errors.value = {}

  try {
    const response = await axios.post(
      route('vue.member.application-wizard.update-current-employment', props.applicationId),
      formData
    )

    successMessage.value = response.data.message || 'Employment information updated successfully'
    emit('completion-updated', {
      field: 'employment',
      percentage: response.data.completion_percentage
    })

    setTimeout(() => {
      successMessage.value = null
    }, 3000)
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    }
    errorMessage.value = error.response?.data?.message || 'Failed to update employment information'
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

