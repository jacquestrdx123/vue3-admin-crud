<template>
  <div class="space-y-4 bg-gray-50 p-6 rounded-lg">
    <h3 class="text-lg font-medium text-gray-900">Disclosure</h3>
    
    <div class="space-y-2">
      <label class="block text-sm font-medium text-gray-700">
        Have you ever been convicted of any crime or had any legal proceedings against you?
        <span class="text-red-500">*</span>
      </label>

      <div class="space-y-2">
        <div class="flex items-center">
          <input
            id="disclosure-no"
            v-model="localStatus"
            type="radio"
            value="no"
            class="h-4 w-4 border-gray-300 text-teal-600 focus:ring-teal-500"
            @change="updateStatus"
          />
          <label for="disclosure-no" class="ml-2 text-sm text-gray-700">
            No
          </label>
        </div>

        <div class="flex items-center">
          <input
            id="disclosure-yes"
            v-model="localStatus"
            type="radio"
            value="yes"
            class="h-4 w-4 border-gray-300 text-teal-600 focus:ring-teal-500"
            @change="updateStatus"
          />
          <label for="disclosure-yes" class="ml-2 text-sm text-gray-700">
            Yes
          </label>
        </div>
      </div>

      <p v-if="statusError" class="text-sm text-red-600">{{ statusError }}</p>
    </div>

    <div v-if="localStatus === 'yes'" class="space-y-2 mt-4">
      <label for="disclosure-details" class="block text-sm font-medium text-gray-700">
        Please provide details
        <span class="text-red-500">*</span>
      </label>

      <textarea
        id="disclosure-details"
        v-model="localDetails"
        rows="4"
        maxlength="1000"
        placeholder="Provide full details of the conviction or legal proceedings..."
        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
        :class="{
          'border-gray-300': !detailsError,
          'border-red-500': detailsError
        }"
        @input="updateDetails"
      />

      <div class="flex justify-between items-center">
        <p v-if="detailsError" class="text-sm text-red-600">{{ detailsError }}</p>
        <p class="text-xs text-gray-500 ml-auto">
          {{ localDetails.length }} / 1000 characters
        </p>
      </div>
    </div>

    <p class="text-xs text-gray-500 italic">
      Note: This information will be handled confidentially and is required for membership compliance.
    </p>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  status: {
    type: String,
    default: ''
  },
  details: {
    type: String,
    default: ''
  },
  statusError: {
    type: String,
    default: ''
  },
  detailsError: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:status', 'update:details'])

const localStatus = ref(props.status)
const localDetails = ref(props.details)

watch(() => props.status, (newVal) => {
  localStatus.value = newVal
})

watch(() => props.details, (newVal) => {
  localDetails.value = newVal
})

const updateStatus = () => {
  emit('update:status', localStatus.value)
  if (localStatus.value === 'no') {
    localDetails.value = ''
    emit('update:details', '')
  }
}

const updateDetails = () => {
  emit('update:details', localDetails.value)
}
</script>

