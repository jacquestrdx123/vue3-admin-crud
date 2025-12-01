<template>
  <Modal :show="show" title="Add Tag" max-width="md" @close="$emit('close')">
    <div v-if="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
      {{ errorMessage }}
    </div>

    <form @submit.prevent="submitForm" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tag *</label>
        <select
          v-model="formData.tag_id"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          required
        >
          <option value="">Select a tag</option>
          <option v-for="tag in availableTags" :key="tag.id" :value="tag.id">
            {{ tag.name }}
          </option>
        </select>
        <p v-if="errors.tag_id" class="mt-1 text-sm text-red-600">{{ errors.tag_id }}</p>
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
          :disabled="isSubmitting || !formData.tag_id"
        >
          <span v-if="isSubmitting">Adding...</span>
          <span v-else>Add Tag</span>
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
  memberId: {
    type: [Number, String],
    required: true
  }
})

const emit = defineEmits(['close', 'success'])

const isSubmitting = ref(false)
const errorMessage = ref(null)
const errors = ref({})
const availableTags = ref([])

const formData = reactive({
  tag_id: ''
})

const loadTags = async () => {
  try {
    // Use the API endpoint that returns tags as {id: name}
    const response = await axios.get(route('api.tags.index'))
    // Convert object {id: name} to array [{id, name}]
    if (response.data && typeof response.data === 'object') {
      availableTags.value = Object.entries(response.data).map(([id, name]) => ({
        id: parseInt(id),
        name: name
      }))
    } else {
      availableTags.value = []
    }
  } catch (error) {
    console.error('Failed to load tags:', error)
    availableTags.value = []
  }
}

// Load tags when modal opens
watch(() => props.show, (isOpen) => {
  if (isOpen && availableTags.value.length === 0) {
    loadTags()
  }
}, { immediate: true })

const submitForm = async () => {
  if (isSubmitting.value || !formData.tag_id) return

  isSubmitting.value = true
  errorMessage.value = null
  errors.value = {}

  try {
    const response = await axios.post(route('vue.members.tags.store', props.memberId), formData)

    if (response.data.success) {
      emit('success')
      emit('close')
      formData.tag_id = ''
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errorMessage.value = error.response?.data?.message || 'Failed to add tag'
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

