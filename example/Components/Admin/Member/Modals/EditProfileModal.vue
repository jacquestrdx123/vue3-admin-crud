<template>
  <Modal :show="show" title="Edit Profile Details" max-width="2xl" @close="$emit('close')">
    <div v-if="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
      {{ errorMessage }}
    </div>

    <form @submit.prevent="submitForm" class="space-y-4">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Primary PPC</label>
          <select
            v-model="formData.primary_ppc_id"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          >
            <option value="">Select PPC</option>
            <option v-for="user in users" :key="user.id" :value="user.id">
              {{ user.name }} ({{ user.email }})
            </option>
          </select>
          <p v-if="errors.primary_ppc_id" class="mt-1 text-sm text-red-600">{{ errors.primary_ppc_id }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Secondary PPC</label>
          <select
            v-model="formData.secondary_ppc_id"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          >
            <option value="">Select PPC</option>
            <option v-for="user in users" :key="user.id" :value="user.id">
              {{ user.name }} ({{ user.email }})
            </option>
          </select>
          <p v-if="errors.secondary_ppc_id" class="mt-1 text-sm text-red-600">{{ errors.secondary_ppc_id }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Corporate Account</label>
          <select
            v-model="formData.corporate_account_id"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          >
            <option value="">Select Corporate Account</option>
            <option v-for="account in corporateAccounts" :key="account.id" :value="account.id">
              {{ account.name }}
            </option>
          </select>
          <p v-if="errors.corporate_account_id" class="mt-1 text-sm text-red-600">{{ errors.corporate_account_id }}</p>
        </div>

        <div>
          <label class="flex items-center">
            <input
              v-model="formData.is_good_standing"
              type="checkbox"
              class="rounded border-gray-300 text-ciba-green focus:ring-ciba-green"
            />
            <span class="ml-2 text-sm font-medium text-gray-700">Good Standing</span>
          </label>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Date Joined</label>
          <input
            v-model="formData.date_joined"
            type="date"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
          <p v-if="errors.date_joined" class="mt-1 text-sm text-red-600">{{ errors.date_joined }}</p>
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
import { ref, reactive, watch, onMounted } from 'vue'
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
const users = ref([])
const corporateAccounts = ref([])

const formData = reactive({
  primary_ppc_id: props.member.primary_ppc_id || props.member.primary_p_p_c_id || null,
  secondary_ppc_id: props.member.secondary_ppc_id || props.member.secondary_p_p_c_id || null,
  corporate_account_id: props.member.corporate_account_id || null,
  is_good_standing: props.member.is_good_standing || false,
  date_joined: props.member.date_joined || ''
})

onMounted(async () => {
  try {
    // Load users using the dedicated endpoint
    const [usersResponse, accountsResponse] = await Promise.all([
      axios.get(route('vue.members.options.users')),
      axios.get(route('vue.members.options.corporate-accounts'))
    ])
    users.value = usersResponse.data || []
    corporateAccounts.value = accountsResponse.data || []
  } catch (error) {
    console.error('Failed to load options:', error)
  }
})

watch(() => props.member, (newMember) => {
  if (newMember) {
    Object.assign(formData, {
      primary_ppc_id: newMember.primary_ppc_id || newMember.primary_p_p_c_id || null,
      secondary_ppc_id: newMember.secondary_ppc_id || newMember.secondary_p_p_c_id || null,
      corporate_account_id: newMember.corporate_account_id || null,
      is_good_standing: newMember.is_good_standing || false,
      date_joined: newMember.date_joined || ''
    })
  }
}, { deep: true })

const submitForm = async () => {
  if (isSubmitting.value) return

  isSubmitting.value = true
  errorMessage.value = null
  errors.value = {}

  try {
    const response = await axios.put(route('vue.members.update-profile', props.member.id), formData)

    if (response.data.success) {
      emit('success')
      emit('close')
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errorMessage.value = error.response?.data?.message || 'Failed to update profile'
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

