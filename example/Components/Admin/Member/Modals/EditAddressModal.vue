<template>
  <Modal :show="show" :title="editingAddress ? 'Edit Address' : 'Add Address'" max-width="2xl" @close="$emit('close')">
    <div v-if="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
      {{ errorMessage }}
    </div>

    <form @submit.prevent="submitForm" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Address Type *</label>
        <select
          v-model="formData.address_type"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          required
        >
          <option value="">Select Type</option>
          <option value="physical">Physical</option>
          <option value="postal">Postal</option>
          <option value="billing">Billing</option>
        </select>
        <p v-if="errors.address_type" class="mt-1 text-sm text-red-600">{{ errors.address_type }}</p>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Street Number</label>
          <input
            v-model="formData.street_nr"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Street Name</label>
          <input
            v-model="formData.street_name"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Suburb *</label>
          <input
            v-model="formData.suburb"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
            required
          />
          <p v-if="errors.suburb" class="mt-1 text-sm text-red-600">{{ errors.suburb }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Postal Code *</label>
          <input
            v-model="formData.postal_code"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
            required
          />
          <p v-if="errors.postal_code" class="mt-1 text-sm text-red-600">{{ errors.postal_code }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
          <select
            v-model="formData.country_id"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
            @change="loadStates"
          >
            <option value="">Select Country</option>
            <option v-for="country in countries" :key="country.id" :value="country.id">
              {{ country.name }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">State/Province</label>
          <select
            v-model="formData.state_id"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
            @change="loadCities"
            :disabled="!formData.country_id"
          >
            <option value="">Select State</option>
            <option v-for="state in states" :key="state.id" :value="state.id">
              {{ state.name }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
          <select
            v-model="formData.city_id"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
            :disabled="!formData.state_id"
          >
            <option value="">Select City</option>
            <option v-for="city in cities" :key="city.id" :value="city.id">
              {{ city.name }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">PO Box Number</label>
          <input
            v-model="formData.po_box_nr"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Private Bag Number</label>
          <input
            v-model="formData.private_bag_nr"
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
          <span v-if="isSubmitting">{{ editingAddress ? 'Updating...' : 'Adding...' }}</span>
          <span v-else>{{ editingAddress ? 'Update Address' : 'Add Address' }}</span>
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
  address: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'success'])

const isSubmitting = ref(false)
const errorMessage = ref(null)
const errors = ref({})
const editingAddress = ref(!!props.address)
const countries = ref([])
const states = ref([])
const cities = ref([])

const formData = reactive({
  address_type: props.address?.address_type || 'physical',
  street_nr: props.address?.street_nr || '',
  street_name: props.address?.street_name || '',
  suburb: props.address?.suburb || '',
  postal_code: props.address?.postal_code || '',
  country_id: props.address?.country_id || null,
  state_id: props.address?.state_id || null,
  city_id: props.address?.city_id || null,
  po_box_nr: props.address?.po_box_nr || '',
  private_bag_nr: props.address?.private_bag_nr || ''
})

onMounted(async () => {
  try {
    const response = await axios.get(route('vue.countries.index'), { params: { per_page: 1000 } })
    countries.value = response.data?.data || []
    
    if (formData.country_id) {
      await loadStates()
    }
    if (formData.state_id) {
      await loadCities()
    }
  } catch (error) {
    console.error('Failed to load countries:', error)
  }
})

watch(() => props.address, (newAddress) => {
  if (newAddress) {
    editingAddress.value = true
    Object.assign(formData, {
      address_type: newAddress.address_type || 'physical',
      street_nr: newAddress.street_nr || '',
      street_name: newAddress.street_name || '',
      suburb: newAddress.suburb || '',
      postal_code: newAddress.postal_code || '',
      country_id: newAddress.country_id || null,
      state_id: newAddress.state_id || null,
      city_id: newAddress.city_id || null,
      po_box_nr: newAddress.po_box_nr || '',
      private_bag_nr: newAddress.private_bag_nr || ''
    })
  } else {
    editingAddress.value = false
    Object.assign(formData, {
      address_type: 'physical',
      street_nr: '',
      street_name: '',
      suburb: '',
      postal_code: '',
      country_id: null,
      state_id: null,
      city_id: null,
      po_box_nr: '',
      private_bag_nr: ''
    })
  }
}, { deep: true })

const loadStates = async () => {
  if (!formData.country_id) {
    states.value = []
    cities.value = []
    formData.state_id = null
    formData.city_id = null
    return
  }

  try {
    const response = await axios.get(route('vue.states.index'), { params: { country_id: formData.country_id, per_page: 1000 } })
    states.value = response.data?.data || []
    cities.value = []
    formData.city_id = null
  } catch (error) {
    console.error('Failed to load states:', error)
  }
}

const loadCities = async () => {
  if (!formData.state_id) {
    cities.value = []
    formData.city_id = null
    return
  }

  try {
    const response = await axios.get(route('vue.cities.index'), { params: { state_id: formData.state_id, per_page: 1000 } })
    cities.value = response.data?.data || []
  } catch (error) {
    console.error('Failed to load cities:', error)
  }
}

const submitForm = async () => {
  if (isSubmitting.value) return

  isSubmitting.value = true
  errorMessage.value = null
  errors.value = {}

  try {
    const url = editingAddress.value
      ? route('vue.members.addresses.update', { id: props.memberId, addressId: props.address.id })
      : route('vue.members.addresses.store', props.memberId)

    const method = editingAddress.value ? 'put' : 'post'
    const response = await axios[method](url, formData)

    if (response.data.success) {
      emit('success')
      emit('close')
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errorMessage.value = error.response?.data?.message || `Failed to ${editingAddress.value ? 'update' : 'create'} address`
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

