<template>
  <div>
    <h2 v-if="title" class="text-lg font-medium text-gray-900 dark:text-white mb-4">
      {{ title }}
    </h2>
    
    <!-- Date Filter Section -->
    <div class="mb-6">
      <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <button
          @click="isFilterExpanded = !isFilterExpanded"
          class="w-full flex items-center justify-between mb-4"
        >
          <div class="flex gap-2 items-center">
            <span class="material-icons text-ciba-green">calendar_today</span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">
              Date Filter - Stats Overview
            </h3>
          </div>
          <svg
            :class="[
              'w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200',
              isFilterExpanded ? 'rotate-180' : ''
            ]"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>
        
        <div
          v-show="isFilterExpanded"
          class="transition-all duration-200"
        >
          <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            Filter stat widgets by date range
          </p>
          
          <form @submit.prevent="applyFilter" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date Range</label>
              <select
                v-model="filters.dateRange"
                @change="updateDateRange"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              >
                <option value="this_year">This Year</option>
                <option value="last_year">Last Year</option>
                <option value="this_month">This Month</option>
                <option value="last_month">Last Month</option>
                <option value="this_quarter">This Quarter</option>
                <option value="last_quarter">Last Quarter</option>
                <option value="custom">Custom Range</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
              <input
                v-model="filters.startDate"
                type="date"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date</label>
              <input
                v-model="filters.endDate"
                type="date"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              />
            </div>
            <div class="md:col-span-3 flex justify-end">
              <button
                type="submit"
                :disabled="loading"
                class="px-6 py-2 text-sm font-semibold text-black bg-ciba-green rounded-lg hover:bg-ciba-green/90 disabled:opacity-50"
              >
                <span v-if="!loading">Apply Filter</span>
                <span v-else class="flex items-center gap-2">
                  <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Applying...
                </span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
      <FunkyStatCard
        v-for="(stat, index) in stats"
        :key="stat.label"
        v-bind="stat"
        :is-active="selectedStatIndex === index"
        @click="selectStat(index)"
      />
    </div>
    
    <!-- Shared Detail Panel -->
    <transition name="slide-down">
      <div v-if="selectedStat" class="detail-panel mt-6">
        <div class="detail-panel-inner">
          <!-- Header -->
          <div class="detail-panel-header">
            <div class="flex items-center gap-3">
              <div class="detail-panel-icon" :class="`detail-panel-icon--${selectedStat.color}`">
                {{ selectedStat.label.charAt(0) }}
              </div>
              <div>
                <h3 class="text-xl font-bold text-gray-900">{{ selectedStat.label }}</h3>
                <p class="text-sm text-gray-600">{{ selectedStat.description }}</p>
              </div>
            </div>
            <button @click="selectedStatIndex = null" class="detail-panel-close">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          
          <!-- Details Grid -->
          <div class="detail-panel-grid">
            <div v-for="(detail, index) in selectedStat.details" :key="index" class="detail-panel-item">
              <div class="detail-panel-item-label">{{ detail.label }}</div>
              <div class="detail-panel-item-value">{{ detail.value }}</div>
            </div>
          </div>
          
          <!-- Status Badge -->
          <div class="detail-panel-status" :class="`detail-panel-status--${selectedStat.color}`">
            {{ selectedStat.statusText }}
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import FunkyStatCard from '@/Components/UI/FunkyStatCard.vue'

const props = defineProps({
  title: {
    type: String,
    default: null
  },
  stats: {
    type: Array,
    required: true
  }
})

const isFilterExpanded = ref(true)
const loading = ref(false)
const selectedStatIndex = ref(null)

const selectedStat = computed(() => {
  if (selectedStatIndex.value === null) return null
  return props.stats[selectedStatIndex.value]
})

const selectStat = (index) => {
  if (selectedStatIndex.value === index) {
    selectedStatIndex.value = null
  } else {
    selectedStatIndex.value = index
  }
}
const currentYear = new Date().getFullYear()

const filters = ref({
  dateRange: 'this_month',
  startDate: `${currentYear}-01-01`,
  endDate: new Date().toISOString().split('T')[0]
})

// Watch for manual date changes and switch to custom mode
watch(() => filters.value.startDate, () => {
  if (filters.value.dateRange !== 'custom') {
    filters.value.dateRange = 'custom'
  }
})

watch(() => filters.value.endDate, () => {
  if (filters.value.dateRange !== 'custom') {
    filters.value.dateRange = 'custom'
  }
})

const updateDateRange = () => {
  const now = new Date()
  const year = now.getFullYear()
  
  switch (filters.value.dateRange) {
    case 'this_year':
      filters.value.startDate = `${year}-01-01`
      filters.value.endDate = now.toISOString().split('T')[0]
      break
    case 'last_year':
      filters.value.startDate = `${year - 1}-01-01`
      filters.value.endDate = `${year - 1}-12-31`
      break
    case 'this_month':
      filters.value.startDate = new Date(year, now.getMonth(), 1).toISOString().split('T')[0]
      filters.value.endDate = now.toISOString().split('T')[0]
      break
    case 'last_month':
      const lastMonth = new Date(year, now.getMonth() - 1, 1)
      const lastMonthEnd = new Date(year, now.getMonth(), 0)
      filters.value.startDate = lastMonth.toISOString().split('T')[0]
      filters.value.endDate = lastMonthEnd.toISOString().split('T')[0]
      break
    case 'this_quarter':
      const quarter = Math.floor(now.getMonth() / 3)
      filters.value.startDate = new Date(year, quarter * 3, 1).toISOString().split('T')[0]
      filters.value.endDate = now.toISOString().split('T')[0]
      break
    case 'last_quarter':
      const lastQuarter = Math.floor(now.getMonth() / 3) - 1
      const lastQuarterYear = lastQuarter < 0 ? year - 1 : year
      const lastQuarterMonth = lastQuarter < 0 ? 9 : lastQuarter * 3
      filters.value.startDate = new Date(lastQuarterYear, lastQuarterMonth, 1).toISOString().split('T')[0]
      filters.value.endDate = new Date(lastQuarterYear, lastQuarterMonth + 3, 0).toISOString().split('T')[0]
      break
  }
}

const applyFilter = () => {
  loading.value = true
  
  const params = {
    stats_date_range: filters.value.dateRange,
    stats_start_date: filters.value.startDate,
    stats_end_date: filters.value.endDate
  }
  
  router.visit(route('vue.dashboard'), {
    method: 'get',
    data: params,
    only: ['stats'],
    preserveScroll: true,
    preserveState: false,
    onFinish: () => {
      loading.value = false
    }
  })
}
</script>

<style scoped>
.detail-panel {
  background: white;
  border-radius: 20px;
  box-shadow: 0 10px 25px -5px rgb(0 0 0 / 0.1);
  overflow: hidden;
}

.detail-panel-inner {
  padding: 2rem;
}

.detail-panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 2px solid #f3f4f6;
}

.detail-panel-icon {
  width: 3.5rem;
  height: 3.5rem;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  font-weight: 700;
}

.detail-panel-icon--indigo { background: #eef2ff; color: #6366f1; }
.detail-panel-icon--green { background: #f0fdf4; color: #22c55e; }
.detail-panel-icon--blue { background: #eff6ff; color: #3b82f6; }
.detail-panel-icon--red { background: #fef2f2; color: #ef4444; }
.detail-panel-icon--purple { background: #faf5ff; color: #a855f7; }
.detail-panel-icon--yellow { background: #fefce8; color: #eab308; }
.detail-panel-icon--pink { background: #fdf2f8; color: #ec4899; }

.detail-panel-close {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 12px;
  color: #6b7280;
  transition: all 0.2s;
}

.detail-panel-close:hover {
  background: #f3f4f6;
  color: #1f2937;
}

.detail-panel-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 2rem;
  margin-bottom: 2rem;
}

.detail-panel-item {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.detail-panel-item-label {
  font-size: 0.75rem;
  font-weight: 600;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.detail-panel-item-value {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2937;
}

.detail-panel-status {
  text-align: center;
  padding: 1rem;
  border-radius: 12px;
  font-size: 0.875rem;
  font-weight: 600;
}

.detail-panel-status--indigo { background: #eef2ff; color: #6366f1; }
.detail-panel-status--green { background: #f0fdf4; color: #22c55e; }
.detail-panel-status--blue { background: #eff6ff; color: #3b82f6; }
.detail-panel-status--red { background: #fef2f2; color: #ef4444; }
.detail-panel-status--purple { background: #faf5ff; color: #a855f7; }
.detail-panel-status--yellow { background: #fefce8; color: #eab308; }
.detail-panel-status--pink { background: #fdf2f8; color: #ec4899; }

/* Slide down animation */
.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.slide-down-enter-from {
  opacity: 0;
  transform: translateY(-20px);
}

.slide-down-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}

</style>

