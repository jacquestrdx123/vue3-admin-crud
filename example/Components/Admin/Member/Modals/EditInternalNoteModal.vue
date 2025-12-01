<template>
  <Modal :show="show" :title="editingNote ? 'Edit Internal Note' : 'Add Internal Note'" max-width="2xl" @close="$emit('close')">
    <div v-if="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
      {{ errorMessage }}
    </div>

    <form @submit.prevent="submitForm" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Content *</label>
        <textarea
          v-model="formData.content"
          rows="5"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          required
        ></textarea>
        <p v-if="errors.content" class="mt-1 text-sm text-red-600">{{ errors.content }}</p>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
          <select
            v-model="formData.priority"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          >
            <option value="">Select Priority</option>
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select
            v-model="formData.status"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          >
            <option value="">Select Status</option>
            <option value="open">Open</option>
            <option value="resolved">Resolved</option>
            <option value="archived">Archived</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Notify Staff</label>
          <select
            v-model="formData.notify_staff_id"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          >
            <option value="">Select Staff Member</option>
            <option v-for="user in users" :key="user.id" :value="user.id">
              {{ user.name }}
            </option>
          </select>
        </div>

        <div>
          <label class="flex items-center">
            <input
              v-model="formData.is_private"
              type="checkbox"
              class="rounded border-gray-300 text-ciba-green focus:ring-ciba-green"
            />
            <span class="ml-2 text-sm font-medium text-gray-700">Private</span>
          </label>
        </div>

        <div>
          <label class="flex items-center">
            <input
              v-model="formData.remind_me"
              type="checkbox"
              class="rounded border-gray-300 text-ciba-green focus:ring-ciba-green"
            />
            <span class="ml-2 text-sm font-medium text-gray-700">Remind Me</span>
          </label>
        </div>

        <div v-if="formData.remind_me">
          <label class="block text-sm font-medium text-gray-700 mb-1">Remind At</label>
          <input
            v-model="formData.remind_at"
            type="datetime-local"
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
          <span v-if="isSubmitting">{{ editingNote ? 'Updating...' : 'Adding...' }}</span>
          <span v-else>{{ editingNote ? 'Update Note' : 'Add Note' }}</span>
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
  note: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'success'])

const isSubmitting = ref(false)
const errorMessage = ref(null)
const errors = ref({})
const editingNote = ref(!!props.note)
const users = ref([])

const formData = reactive({
  content: props.note?.content || '',
  is_private: props.note?.is_private || false,
  priority: props.note?.priority || '',
  status: props.note?.status || '',
  notify_staff_id: props.note?.notify_staff_id || null,
  remind_me: props.note?.remind_me || false,
  remind_at: props.note?.remind_at || ''
})

onMounted(async () => {
  try {
    const response = await axios.get(route('vue.members.options.users'))
    users.value = response.data || []
  } catch (error) {
    console.error('Failed to load users:', error)
  }
})

watch(() => props.note, (newNote) => {
  if (newNote) {
    editingNote.value = true
    Object.assign(formData, {
      content: newNote.content || '',
      is_private: newNote.is_private || false,
      priority: newNote.priority || '',
      status: newNote.status || '',
      notify_staff_id: newNote.notify_staff_id || null,
      remind_me: newNote.remind_me || false,
      remind_at: newNote.remind_at || ''
    })
  } else {
    editingNote.value = false
    Object.assign(formData, {
      content: '',
      is_private: false,
      priority: '',
      status: '',
      notify_staff_id: null,
      remind_me: false,
      remind_at: ''
    })
  }
}, { deep: true })

const submitForm = async () => {
  if (isSubmitting.value) return

  isSubmitting.value = true
  errorMessage.value = null
  errors.value = {}

  try {
    const url = editingNote.value
      ? route('vue.members.internal-notes.update', { id: props.memberId, noteId: props.note.id })
      : route('vue.members.internal-notes.store', props.memberId)

    const method = editingNote.value ? 'put' : 'post'
    const response = await axios[method](url, formData)

    if (response.data.success) {
      emit('success')
      emit('close')
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errorMessage.value = error.response?.data?.message || `Failed to ${editingNote.value ? 'update' : 'create'} internal note`
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

