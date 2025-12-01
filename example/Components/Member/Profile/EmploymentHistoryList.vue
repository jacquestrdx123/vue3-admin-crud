<template>
  <div class="space-y-4">
    <div v-if="successMessage" class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
      {{ successMessage }}
    </div>

    <div class="mb-4 text-sm text-gray-600">
      <p>Please add at least 3 previous employers to complete this section.</p>
      <p class="font-medium">Current count: {{ employmentHistory.length }} employer{{ employmentHistory.length !== 1 ? 's' : '' }}</p>
    </div>

    <div v-if="employmentHistory.length === 0" class="text-center py-8 text-gray-500">
      <p class="mb-4">No employment history added yet.</p>
      <button
        type="button"
        @click="$emit('open-modal')"
        class="inline-block px-6 py-2 bg-ciba-green hover:bg-[#A9E204] text-black font-semibold rounded-full transition-colors"
      >
        Add Employment History
      </button>
    </div>

    <div v-else>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Company</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Job Title</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Period</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Status</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="employment in employmentHistory" :key="employment.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ employment.company_name || 'N/A' }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ employment.job_title || 'N/A' }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ formatDateRange(employment.start_date, employment.end_date) }}
              </td>
              <td class="px-6 py-4 text-sm">
                <span
                  v-if="employment.is_current"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                >
                  Current
                </span>
                <span
                  v-else
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
                >
                  Past
                </span>
              </td>
              <td class="px-6 py-4 text-sm">
                <div class="flex items-center gap-3">
                  <button
                    type="button"
                    @click="$emit('edit-history', employment)"
                    class="text-ciba-green hover:text-[#A9E204] font-medium"
                  >
                    Edit
                  </button>
                  <button
                    type="button"
                    @click="$emit('delete-history', employment.id)"
                    class="text-red-600 hover:text-red-800 font-medium"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="mt-4 flex justify-center">
        <button
          type="button"
          @click="$emit('open-modal')"
          class="inline-block px-6 py-2 bg-ciba-green hover:bg-[#A9E204] text-black font-semibold rounded-full transition-colors"
        >
          Add Another Employment Record
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  employmentHistory: { type: Array, default: () => [] },
  applicationId: { type: [Number, String], required: true }
})

const emit = defineEmits(['open-modal', 'edit-history', 'delete-history'])

const successMessage = ref(null)

function formatDate(date) {
  if (!date) return null
  
  try {
    return new Date(date).toLocaleDateString('en-ZA', {
      year: 'numeric',
      month: 'short'
    })
  } catch (error) {
    return null
  }
}

function formatDateRange(startDate, endDate) {
  const start = formatDate(startDate) || 'N/A'
  const end = endDate ? formatDate(endDate) : 'Present'
  
  return `${start} - ${end}`
}
</script>

<style scoped>
.bg-ciba-green {
  background-color: #BAF504;
}

.text-ciba-green {
  color: #BAF504;
}
</style>

