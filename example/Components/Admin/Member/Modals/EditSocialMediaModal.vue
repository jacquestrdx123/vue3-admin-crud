<template>
  <Modal :show="show" title="Edit Social Media Links" max-width="2xl" @close="$emit('close')">
    <div v-if="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
      {{ errorMessage }}
    </div>

    <form @submit.prevent="submitForm" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">LinkedIn</label>
        <input
          v-model="formData.social_link_linkedin"
          type="url"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          placeholder="https://linkedin.com/in/..."
        />
        <p v-if="errors.social_link_linkedin" class="mt-1 text-sm text-red-600">{{ errors.social_link_linkedin }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Facebook</label>
        <input
          v-model="formData.social_link_facebook"
          type="url"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          placeholder="https://facebook.com/..."
        />
        <p v-if="errors.social_link_facebook" class="mt-1 text-sm text-red-600">{{ errors.social_link_facebook }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Instagram</label>
        <input
          v-model="formData.social_link_instagram"
          type="url"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          placeholder="https://instagram.com/..."
        />
        <p v-if="errors.social_link_instagram" class="mt-1 text-sm text-red-600">{{ errors.social_link_instagram }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">X (Twitter)</label>
        <input
          v-model="formData.social_link_x"
          type="url"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          placeholder="https://x.com/..."
        />
        <p v-if="errors.social_link_x" class="mt-1 text-sm text-red-600">{{ errors.social_link_x }}</p>
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
  socialMedia: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['close', 'success'])

const isSubmitting = ref(false)
const errorMessage = ref(null)
const errors = ref({})

const formData = reactive({
  social_link_linkedin: props.socialMedia?.linkedin || props.member.social_link_linkedin || '',
  social_link_facebook: props.socialMedia?.facebook || props.member.social_link_facebook || '',
  social_link_instagram: props.socialMedia?.instagram || props.member.social_link_instagram || '',
  social_link_x: props.socialMedia?.x || props.member.social_link_x || ''
})

watch(() => props.socialMedia, (newSocialMedia) => {
  if (newSocialMedia) {
    Object.assign(formData, {
      social_link_linkedin: newSocialMedia.linkedin || '',
      social_link_facebook: newSocialMedia.facebook || '',
      social_link_instagram: newSocialMedia.instagram || '',
      social_link_x: newSocialMedia.x || ''
    })
  }
}, { deep: true })

const submitForm = async () => {
  if (isSubmitting.value) return

  isSubmitting.value = true
  errorMessage.value = null
  errors.value = {}

  try {
    const response = await axios.put(route('vue.members.update-social-media', props.member.id), formData)

    if (response.data.success) {
      emit('success')
      emit('close')
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errorMessage.value = error.response?.data?.message || 'Failed to update social media links'
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

