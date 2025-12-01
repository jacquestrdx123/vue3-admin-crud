<template>
  <div class="space-y-4">
    <div v-if="qualifications.length === 0" class="text-center py-8 text-gray-500">
      <p class="mb-4">No qualifications added yet.</p>
      <button
        type="button"
        @click="$emit('open-modal')"
        class="inline-block px-6 py-2 bg-ciba-green hover:bg-[#A9E204] text-black font-semibold rounded-full transition-colors"
      >
        Add Qualification
      </button>
    </div>

    <div v-else>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Qualification</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Institution</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Completion Date</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Status</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="qualification in qualifications" :key="qualification.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ qualification.qualification_name || qualification.qualification?.name || 'N/A' }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ qualification.institution || 'N/A' }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ formatDate(qualification.completion_date) }}
              </td>
              <td class="px-6 py-4 text-sm">
                <span
                  v-if="qualification.staff_verified"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                >
                  Verified
                </span>
                <span
                  v-else
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"
                >
                  Pending
                </span>
              </td>
              <td class="px-6 py-4 text-sm">
                <button
                  type="button"
                  @click="$emit('edit-qualification', qualification)"
                  class="text-ciba-green hover:text-[#A9E204] font-medium mr-4"
                >
                  Edit
                </button>
                <button
                  type="button"
                  @click="$emit('delete-qualification', qualification.id)"
                  class="text-red-600 hover:text-red-800 font-medium"
                >
                  Delete
                </button>
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
          Add Another Qualification
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  qualifications: { type: Array, default: () => [] },
  applicationId: { type: [Number, String], required: true }
})

const emit = defineEmits(['open-modal', 'edit-qualification', 'delete-qualification'])

function formatDate(date) {
  if (!date) return 'N/A'
  
  try {
    return new Date(date).toLocaleDateString('en-ZA', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    })
  } catch (error) {
    return 'N/A'
  }
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

