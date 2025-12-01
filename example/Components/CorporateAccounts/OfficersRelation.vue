<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Officers</h2>
      <button
        @click="openCreateModal"
        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700"
      >
        Add Officer
      </button>
    </div>

    <!-- Filters -->
    <div class="mb-4 flex gap-4">
      <div class="flex-1">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search officers..."
          class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
        />
      </div>
      <select
        v-model="filters.status"
        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
      >
        <option value="">All Statuses</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
        <option value="suspended">Suspended</option>
      </select>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-900">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Member
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Highest Qualification
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Contact Number
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Email
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              ID/Passport
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Nationality
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Appointment Date
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Status
            </th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
          <tr v-if="filteredOfficers.length === 0">
            <td colspan="9" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
              No officers found
            </td>
          </tr>
          <tr v-for="officer in filteredOfficers" :key="officer.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
              {{ officer.member?.name || '-' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
              {{ officer.highest_qualification }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
              {{ officer.contact_number }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
              {{ officer.email }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
              {{ officer.id_or_passport }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
              {{ officer.nationality }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
              {{ formatDate(officer.appointment_date) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="getStatusBadgeClass(officer.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                {{ formatStatus(officer.status) }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <button
                @click="openEditModal(officer)"
                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 mr-3"
              >
                Edit
              </button>
              <button
                @click="deleteOfficer(officer)"
                class="text-red-600 hover:text-red-900 dark:text-red-400"
              >
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <RelationModal
      v-if="showModal"
      :show="showModal"
      :title="modalTitle"
      :fields="officerFields"
      :initial-data="editingOfficer"
      :loading="loading"
      @save="saveOfficer"
      @close="closeModal"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import RelationModal from './RelationModal.vue'

const props = defineProps({
  corporateAccountId: {
    type: Number,
    required: true
  },
  officers: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['refresh'])

const showModal = ref(false)
const editingOfficer = ref(null)
const loading = ref(false)
const searchQuery = ref('')
const filters = ref({
  status: ''
})

const officerFields = [
  { name: 'member_id', label: 'Member ID', type: 'number', required: true },
  { name: 'highest_qualification', label: 'Highest Qualification', type: 'text', required: true },
  { name: 'contact_number', label: 'Contact Number', type: 'text', required: true },
  { name: 'email', label: 'Email', type: 'email', required: true },
  { name: 'id_or_passport', label: 'ID or Passport', type: 'text', required: true },
  { name: 'nationality', label: 'Nationality', type: 'text', required: true },
  { name: 'work_permit_path', label: 'Work Permit Path', type: 'text', required: false },
  { name: 'cv_path', label: 'CV Path', type: 'text', required: false },
  { name: 'qualifications_path', label: 'Qualifications Path', type: 'text', required: false },
  { name: 'appointment_date', label: 'Appointment Date', type: 'date', required: true },
  { 
    name: 'status', 
    label: 'Status', 
    type: 'select', 
    required: true,
    options: [
      { value: 'active', label: 'Active' },
      { value: 'inactive', label: 'Inactive' },
      { value: 'suspended', label: 'Suspended' }
    ]
  }
]

const modalTitle = computed(() => {
  return editingOfficer.value ? 'Edit Officer' : 'Add Officer'
})

const filteredOfficers = computed(() => {
  let result = props.officers || []
  
  // Search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(officer => 
      officer.member?.name?.toLowerCase().includes(query) ||
      officer.email?.toLowerCase().includes(query) ||
      officer.highest_qualification?.toLowerCase().includes(query) ||
      officer.nationality?.toLowerCase().includes(query)
    )
  }
  
  // Status filter
  if (filters.value.status) {
    result = result.filter(officer => officer.status === filters.value.status)
  }
  
  return result
})

const openCreateModal = () => {
  editingOfficer.value = null
  showModal.value = true
}

const openEditModal = (officer) => {
  editingOfficer.value = { ...officer }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingOfficer.value = null
}

const saveOfficer = async (formData) => {
  loading.value = true
  
  try {
    if (editingOfficer.value) {
      // Update existing officer
      await axios.put(
        route('corporate-accounts.officers.update', {
          corporateAccountId: props.corporateAccountId,
          officerId: editingOfficer.value.id
        }),
        formData
      )
    } else {
      // Create new officer
      await axios.post(
        route('corporate-accounts.officers.store', props.corporateAccountId),
        formData
      )
    }
    
    closeModal()
    emit('refresh')
  } catch (error) {
    console.error('Error saving officer:', error)
    alert(error.response?.data?.message || 'An error occurred while saving the officer')
  } finally {
    loading.value = false
  }
}

const deleteOfficer = async (officer) => {
  if (!confirm(`Are you sure you want to delete this officer?`)) {
    return
  }
  
  try {
    await axios.delete(
      route('corporate-accounts.officers.destroy', {
        corporateAccountId: props.corporateAccountId,
        officerId: officer.id
      })
    )
    
    emit('refresh')
  } catch (error) {
    console.error('Error deleting officer:', error)
    alert(error.response?.data?.message || 'An error occurred while deleting the officer')
  }
}

const formatStatus = (status) => {
  if (!status) return 'N/A'
  return status.charAt(0).toUpperCase() + status.slice(1)
}

const getStatusBadgeClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
    inactive: 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400',
    suspended: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
  }
  return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}
</script>

