<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Members</h2>
      <button
        @click="openCreateModal"
        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700"
      >
        Add Member
      </button>
    </div>

    <!-- Filters -->
    <div class="mb-4 flex gap-4">
      <div class="flex-1">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search members..."
          class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
        />
      </div>
      <select
        v-model="filters.status"
        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
      >
        <option value="">All Statuses</option>
        <option value="active">Active</option>
        <option value="suspended">Suspended</option>
        <option value="cancelled">Cancelled</option>
      </select>
      <select
        v-model="filters.is_good_standing"
        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
      >
        <option value="">All Standing</option>
        <option value="1">Good Standing</option>
        <option value="0">Not Good Standing</option>
      </select>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-900">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Member Code
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Name
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Email
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Mobile
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Status
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Good Standing
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Daily Balance
            </th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
          <tr v-if="filteredMembers.length === 0">
            <td colspan="8" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
              No members found
            </td>
          </tr>
          <tr v-for="member in filteredMembers" :key="member.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
              {{ member.m_code }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
              {{ member.first_name }} {{ member.last_name }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
              {{ member.email }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
              {{ member.mobile_number || '-' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="getStatusBadgeClass(member.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                {{ formatStatus(member.status) }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <span v-if="member.is_good_standing" class="text-ciba-green">✓</span>
              <span v-else class="text-red-600">✗</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
              {{ formatCurrency(member.daily_balance) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <button
                @click="openEditModal(member)"
                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 mr-3"
              >
                Edit
              </button>
              <button
                @click="deleteMember(member)"
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
      :fields="memberFields"
      :initial-data="editingMember"
      :loading="loading"
      @save="saveMember"
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
  members: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['refresh'])

const showModal = ref(false)
const editingMember = ref(null)
const loading = ref(false)
const searchQuery = ref('')
const filters = ref({
  status: '',
  is_good_standing: ''
})

const memberFields = [
  { name: 'm_code', label: 'Member Code', type: 'text', required: true },
  { name: 'first_name', label: 'First Name', type: 'text', required: true },
  { name: 'last_name', label: 'Last Name', type: 'text', required: true },
  { name: 'email', label: 'Email', type: 'email', required: true },
  { name: 'mobile_number', label: 'Mobile Number', type: 'text', required: false },
  { 
    name: 'status', 
    label: 'Status', 
    type: 'select', 
    required: true,
    options: [
      { value: 'active', label: 'Active' },
      { value: 'suspended', label: 'Suspended' },
      { value: 'cancelled', label: 'Cancelled' }
    ]
  },
  { name: 'is_good_standing', label: 'Good Standing', type: 'checkbox', required: false }
]

const modalTitle = computed(() => {
  return editingMember.value ? 'Edit Member' : 'Add Member'
})

const filteredMembers = computed(() => {
  let result = props.members || []
  
  // Search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(member => 
      member.m_code?.toLowerCase().includes(query) ||
      member.first_name?.toLowerCase().includes(query) ||
      member.last_name?.toLowerCase().includes(query) ||
      member.email?.toLowerCase().includes(query)
    )
  }
  
  // Status filter
  if (filters.value.status) {
    result = result.filter(member => member.status === filters.value.status)
  }
  
  // Good standing filter
  if (filters.value.is_good_standing !== '') {
    const standing = filters.value.is_good_standing === '1'
    result = result.filter(member => member.is_good_standing === standing)
  }
  
  return result
})

const openCreateModal = () => {
  editingMember.value = null
  showModal.value = true
}

const openEditModal = (member) => {
  editingMember.value = { ...member }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingMember.value = null
}

const saveMember = async (formData) => {
  loading.value = true
  
  try {
    if (editingMember.value) {
      // Update existing member
      await axios.put(
        route('corporate-accounts.members.update', {
          corporateAccountId: props.corporateAccountId,
          memberId: editingMember.value.id
        }),
        formData
      )
    } else {
      // Create new member
      await axios.post(
        route('corporate-accounts.members.store', props.corporateAccountId),
        formData
      )
    }
    
    closeModal()
    emit('refresh')
  } catch (error) {
    console.error('Error saving member:', error)
    alert(error.response?.data?.message || 'An error occurred while saving the member')
  } finally {
    loading.value = false
  }
}

const deleteMember = async (member) => {
  if (!confirm(`Are you sure you want to delete ${member.first_name} ${member.last_name}?`)) {
    return
  }
  
  try {
    await axios.delete(
      route('corporate-accounts.members.destroy', {
        corporateAccountId: props.corporateAccountId,
        memberId: member.id
      })
    )
    
    emit('refresh')
  } catch (error) {
    console.error('Error deleting member:', error)
    alert(error.response?.data?.message || 'An error occurred while deleting the member')
  }
}

const formatStatus = (status) => {
  if (!status) return 'N/A'
  return status.charAt(0).toUpperCase() + status.slice(1)
}

const getStatusBadgeClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
    suspended: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
    cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
  }
  return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
}

const formatCurrency = (value) => {
  if (value === null || value === undefined) return 'R0.00'
  const num = parseFloat(value)
  if (isNaN(num)) return 'R0.00'
  return 'R' + num.toLocaleString('en-ZA', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}
</script>

