import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

export function useCPD(year = null) {
  const currentYear = year || new Date().getFullYear()
  const entries = ref([])
  const loading = ref(false)
  
  const totalHours = computed(() => {
    return entries.value.reduce((sum, entry) => sum + (entry.hours || 0), 0)
  })
  
  const hoursByCategory = computed(() => {
    const grouped = {}
    entries.value.forEach(entry => {
      const category = entry.category?.name || 'Uncategorized'
      if (!grouped[category]) {
        grouped[category] = 0
      }
      grouped[category] += entry.hours || 0
    })
    return Object.entries(grouped).map(([category, hours]) => ({
      category,
      hours
    }))
  })
  
  const progress = computed(() => {
    // Assuming required hours from member data
    const required = 40 // This should come from member data
    return totalHours.value > 0 ? (totalHours.value / required) * 100 : 0
  })
  
  const compliance = computed(() => {
    return progress.value >= 100
  })
  
  const fetchEntries = async (filters = {}) => {
    loading.value = true
    try {
      router.get(route('vue.member.cpd.entries'), { ...filters, year: currentYear }, {
        preserveState: true,
        onSuccess: (page) => {
          entries.value = page.props.cpds.data
        },
        onFinish: () => {
          loading.value = false
        }
      })
    } catch (error) {
      loading.value = false
      console.error('Error fetching CPD entries:', error)
    }
  }
  
  const createEntry = (data) => {
    router.post(route('vue.member.cpd.entries.store'), data)
  }
  
  const updateEntry = (id, data) => {
    router.put(route('vue.member.cpd.entries.update', id), data)
  }
  
  const deleteEntry = (id) => {
    if (confirm('Are you sure you want to delete this CPD entry?')) {
      router.delete(route('vue.member.cpd.entries.destroy', id))
    }
  }
  
  return {
    entries,
    loading,
    totalHours,
    hoursByCategory,
    progress,
    compliance,
    fetchEntries,
    createEntry,
    updateEntry,
    deleteEntry
  }
}

