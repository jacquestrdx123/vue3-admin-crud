<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Corporate Members</h2>
      <button
        @click="openCreateModal"
        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700"
      >
        Add Corporate Member
      </button>
    </div>

    <!-- Filters -->
    <div class="mb-4 flex gap-4">
      <div class="flex-1">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search corporate members..."
          class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
        />
      </div>
      <select
        v-model="filters.training_stage"
        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
      >
        <option value="">All Stages</option>
        <option value="stage1">Stage 1</option>
        <option value="stage2">Stage 2</option>
        <option value="stage3">Stage 3</option>
      </select>
      <select
        v-model="filters.logbook_status"
        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
      >
        <option value="">All Logbook Status</option>
        <option value="pending">Pending</option>
        <option value="in_progress">In Progress</option>
        <option value="completed">Completed</option>
        <option value="approved">Approved</option>
      </select>
      <select
        v-model="filters.is_active"
        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
      >
        <option value="">All Status</option>
        <option value="1">Active</option>
        <option value="0">Inactive</option>
      </select>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-900">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Name
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Training Stage
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Logbook Status
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Training Officer
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Last Declaration
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Active
            </th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
          <tr v-if="filteredCorporateMembers.length === 0">
            <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
              No corporate members found
            </td>
          </tr>
          <tr v-for="corporateMember in filteredCorporateMembers" :key="corporateMember.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
              {{ corporateMember.name }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="getTrainingStageBadgeClass(corporateMember.training_stage)" class="px-2 py-1 text-xs font-semibold rounded-full">
                {{ formatTrainingStage(corporateMember.training_stage) }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="getLogbookStatusBadgeClass(corporateMember.logbook_status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                {{ formatLogbookStatus(corporateMember.logbook_status) }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
              {{ corporateMember.training_officer?.name || '-' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
              {{ formatDate(corporateMember.last_annual_declaration_at) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <span v-if="corporateMember.is_active" class="text-ciba-green">✓</span>
              <span v-else class="text-red-600">✗</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <button
                @click="openEditModal(corporateMember)"
                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 mr-3"
              >
                Edit
              </button>
              <button
                @click="deleteCorporateMember(corporateMember)"
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
      :fields="corporateMemberFields"
      :initial-data="editingCorporateMember"
      :loading="loading"
      @save="saveCorporateMember"
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
  corporateMembers: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['refresh'])

const showModal = ref(false)
const editingCorporateMember = ref(null)
const loading = ref(false)
const searchQuery = ref('')
const filters = ref({
  training_stage: '',
  logbook_status: '',
  is_active: ''
})

const corporateMemberFields = [
  { name: 'name', label: 'Name', type: 'text', required: true },
  { 
    name: 'training_stage', 
    label: 'Training Stage', 
    type: 'select', 
    required: true,
    options: [
      { value: 'stage1', label: 'Stage 1' },
      { value: 'stage2', label: 'Stage 2' },
      { value: 'stage3', label: 'Stage 3' }
    ]
  },
  { 
    name: 'logbook_status', 
    label: 'Logbook Status', 
    type: 'select', 
    required: true,
    options: [
      { value: 'pending', label: 'Pending' },
      { value: 'in_progress', label: 'In Progress' },
      { value: 'completed', label: 'Completed' },
      { value: 'approved', label: 'Approved' }
    ]
  },
  { name: 'trainee_member_id', label: 'Member ID', type: 'number', required: true },
  { name: 'training_officer_id', label: 'Training Officer ID', type: 'number', required: false },
  { name: 'last_annual_declaration_at', label: 'Last Annual Declaration', type: 'date', required: false },
  { name: 'is_active', label: 'Active', type: 'checkbox', required: false }
]

const modalTitle = computed(() => {
  return editingCorporateMember.value ? 'Edit Corporate Member' : 'Add Corporate Member'
})

const filteredCorporateMembers = computed(() => {
  let result = props.corporateMembers || []
  
  // Search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(member => 
      member.first_name?.toLowerCase().includes(query) ||
      member.training_officer?.name?.toLowerCase().includes(query)
    )
  }
  
  // Training stage filter
  if (filters.value.training_stage) {
    result = result.filter(member => member.training_stage === filters.value.training_stage)
  }
  
  // Logbook status filter
  if (filters.value.logbook_status) {
    result = result.filter(member => member.logbook_status === filters.value.logbook_status)
  }
  
  // Active filter
  if (filters.value.is_active !== '') {
    const active = filters.value.is_active === '1'
    result = result.filter(member => member.is_active === active)
  }
  
  return result
})

const openCreateModal = () => {
  editingCorporateMember.value = null
  showModal.value = true
}

const openEditModal = (corporateMember) => {
  editingCorporateMember.value = { ...corporateMember }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingCorporateMember.value = null
}

const saveCorporateMember = async (formData) => {
  loading.value = true
  
  try {
    if (editingCorporateMember.value) {
      // Update existing corporate member
      await axios.put(
        route('corporate-accounts.corporate-members.update', {
          corporateAccountId: props.corporateAccountId,
          corporateMemberId: editingCorporateMember.value.id
        }),
        formData
      )
    } else {
      // Create new corporate member
      await axios.post(
        route('corporate-accounts.corporate-members.store', props.corporateAccountId),
        formData
      )
    }
    
    closeModal()
    emit('refresh')
  } catch (error) {
    console.error('Error saving corporate member:', error)
    alert(error.response?.data?.message || 'An error occurred while saving the corporate member')
  } finally {
    loading.value = false
  }
}

const deleteCorporateMember = async (corporateMember) => {
  if (!confirm(`Are you sure you want to delete ${corporateMember.name}?`)) {
    return
  }
  
  try {
    await axios.delete(
      route('corporate-accounts.corporate-members.destroy', {
        corporateAccountId: props.corporateAccountId,
        corporateMemberId: corporateMember.id
      })
    )
    
    emit('refresh')
  } catch (error) {
    console.error('Error deleting corporate member:', error)
    alert(error.response?.data?.message || 'An error occurred while deleting the corporate member')
  }
}

const formatTrainingStage = (stage) => {
  const stages = {
    stage1: 'Stage 1',
    stage2: 'Stage 2',
    stage3: 'Stage 3',
  }
  return stages[stage] || stage
}

const formatLogbookStatus = (status) => {
  if (!status) return 'N/A'
  return status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const getTrainingStageBadgeClass = (stage) => {
  const classes = {
    stage1: 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
    stage2: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
    stage3: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
  }
  return classes[stage] || 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
}

const getLogbookStatusBadgeClass = (status) => {
  const classes = {
    pending: 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400',
    in_progress: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
    completed: 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
    approved: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
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

