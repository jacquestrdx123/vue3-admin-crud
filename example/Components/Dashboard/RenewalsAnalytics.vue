<template>
  <div>
    <!-- Billing Run Filter Section -->
    <div class="mb-6">
      <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <button
          @click="isFilterExpanded = !isFilterExpanded"
          class="w-full flex items-center justify-between mb-4"
        >
          <div class="flex gap-2 items-center">
            <span class="material-icons text-ciba-green">filter_list</span>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">
              Billing Run Filter - Renewals
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
            Filter renewal data by billing run and year
          </p>
          
          <form @submit.prevent="applyFilter" class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Billing Run</label>
            <select
              v-model="filters.billingRun"
              class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            >
              <option value="all">All Billing Runs</option>
              <option value="january">January Billing Run</option>
              <option value="july">July Billing Run</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Year</label>
            <select
              v-model="filters.year"
              class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            >
              <option v-for="year in yearOptions" :key="year" :value="year">{{ year }}</option>
            </select>
          </div>
          <div class="md:col-span-2 flex justify-end">
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

    <!-- Renewal Info Lists Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- All Members Renewals -->
      <div class="bg-gradient-to-r from-ciba-green/10 to-ciba-turquoise/10 border-l-4 border-ciba-green rounded-lg p-6">
        <h3 class="flex gap-2 items-center text-lg font-bold text-gray-900 dark:text-white mb-2">
          <span class="material-icons text-ciba-green">autorenew</span>
          Membership Renewal Analytics
        </h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
          Track membership and designation renewal statistics
          <span v-if="billingRunDescription" class="inline-flex items-center px-2 py-1 ml-2 text-xs font-medium bg-blue-100 text-blue-800 rounded-full dark:bg-blue-900/30 dark:text-blue-300">
            {{ billingRunDescription }}
          </span>
        </p>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
          <!-- Membership Renewals Summary -->
          <div class="p-4 mb-6 bg-gradient-to-r from-ciba-green/5 to-ciba-turquoise/5 border border-ciba-green/20 rounded-lg">
            <h4 class="mb-2 font-semibold text-gray-800 dark:text-gray-200">
              Total Membership Renewals
            </h4>
            <div class="text-3xl font-bold text-ciba-green">
              {{ formatNumber(data.allRenewalData?.membership_renewals || 0) }}
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400">
              Active membership renewals processed
            </p>
          </div>
          
          <!-- Designation Renewals -->
          <div>
            <h4 class="mb-4 font-semibold text-gray-800 dark:text-gray-200">
              Designation Renewals by Category
            </h4>
            
            <!-- Summary Stats -->
            <div v-if="allDesignationStats" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
              <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-center">
                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                  {{ formatNumber(allDesignationStats.total) }}
                </div>
                <div class="text-xs text-gray-600 dark:text-gray-400">Total Designation Renewals</div>
              </div>
              <div class="col-span-2 p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg text-center">
                <div class="text-xs font-bold text-purple-600 dark:text-purple-400 truncate">
                  {{ allDesignationStats.topDesignation }}
                </div>
                <div class="text-xs text-gray-600 dark:text-gray-400">Top Designation</div>
              </div>
            </div>
            
            <!-- Designation List -->
            <div class="space-y-2 max-h-96 overflow-y-auto">
              <div
                v-for="(count, name) in data.allRenewalData?.designation_renewals || {}"
                :key="name"
                class="flex justify-between items-center px-4 py-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
              >
                <div class="flex-1">
                  <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ name }}
                  </span>
                </div>
                <div class="flex gap-2 items-center">
                  <span class="text-lg font-bold" :class="count > 0 ? 'text-ciba-green' : 'text-gray-400'">
                    {{ formatNumber(count) }}
                  </span>
                  <div v-if="count > 0" class="w-2 h-2 rounded-full bg-ciba-green"></div>
                </div>
              </div>
            </div>
            
            <!-- Legend -->
            <div class="pt-4 mt-4 border-t border-gray-200 dark:border-gray-700">
              <div class="flex gap-2 items-center text-xs text-gray-500 dark:text-gray-400">
                <div class="w-2 h-2 rounded-full bg-ciba-green"></div>
                <span>Active renewals</span>
                <div class="ml-4 w-2 h-2 bg-gray-400 rounded-full"></div>
                <span>No renewals</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Namibia Renewals -->
      <div class="bg-gradient-to-r from-ciba-green/10 to-ciba-turquoise/10 border-l-4 border-ciba-green rounded-lg p-6">
        <h3 class="flex gap-2 items-center text-lg font-bold text-gray-900 dark:text-white mb-2">
          <span class="material-icons text-ciba-green">autorenew</span>
          Namibia Membership Renewal Analytics
        </h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
          Track membership and designation renewal statistics
          <span v-if="billingRunDescription" class="inline-flex items-center px-2 py-1 ml-2 text-xs font-medium bg-blue-100 text-blue-800 rounded-full dark:bg-blue-900/30 dark:text-blue-300">
            {{ billingRunDescription }}
          </span>
        </p>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
          <!-- Membership Renewals Summary -->
          <div class="p-4 mb-6 bg-gradient-to-r from-ciba-green/5 to-ciba-turquoise/5 border border-ciba-green/20 rounded-lg">
            <h4 class="mb-2 font-semibold text-gray-800 dark:text-gray-200">
              Total Membership Renewals
            </h4>
            <div class="text-3xl font-bold text-ciba-green">
              {{ formatNumber(data.namibiaRenewalData?.membership_renewals || 0) }}
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400">
              Active membership renewals processed
            </p>
          </div>
          
          <!-- Designation Renewals -->
          <div>
            <h4 class="mb-4 font-semibold text-gray-800 dark:text-gray-200">
              Designation Renewals by Category
            </h4>
            
            <!-- Summary Stats -->
            <div v-if="namibiaDesignationStats" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
              <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-center">
                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                  {{ formatNumber(namibiaDesignationStats.total) }}
                </div>
                <div class="text-xs text-gray-600 dark:text-gray-400">Total Designation Renewals</div>
              </div>
              <div class="col-span-2 p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg text-center">
                <div class="text-xs font-bold text-purple-600 dark:text-purple-400 truncate">
                  {{ namibiaDesignationStats.topDesignation }}
                </div>
                <div class="text-xs text-gray-600 dark:text-gray-400">Top Designation</div>
              </div>
            </div>
            
            <!-- Designation List -->
            <div class="space-y-2 max-h-96 overflow-y-auto">
              <div
                v-for="(count, name) in data.namibiaRenewalData?.designation_renewals || {}"
                :key="name"
                class="flex justify-between items-center px-4 py-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
              >
                <div class="flex-1">
                  <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ name }}
                  </span>
                </div>
                <div class="flex gap-2 items-center">
                  <span class="text-lg font-bold" :class="count > 0 ? 'text-ciba-green' : 'text-gray-400'">
                    {{ formatNumber(count) }}
                  </span>
                  <div v-if="count > 0" class="w-2 h-2 rounded-full bg-ciba-green"></div>
                </div>
              </div>
            </div>
            
            <!-- Legend -->
            <div class="pt-4 mt-4 border-t border-gray-200 dark:border-gray-700">
              <div class="flex gap-2 items-center text-xs text-gray-500 dark:text-gray-400">
                <div class="w-2 h-2 rounded-full bg-ciba-green"></div>
                <span>Active renewals</span>
                <div class="ml-4 w-2 h-2 bg-gray-400 rounded-full"></div>
                <span>No renewals</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  data: {
    type: Object,
    default: () => ({})
  }
})

const isFilterExpanded = ref(true)
const loading = ref(false)
const currentYear = new Date().getFullYear()

const filters = ref({
  billingRun: props.data.billingRun || 'all',
  year: props.data.year || currentYear
})

const yearOptions = computed(() => {
  const years = []
  for (let i = 0; i <= 5; i++) {
    years.push(currentYear + 1 - i)
  }
  return years
})

const billingRunDescription = computed(() => {
  if (filters.value.billingRun === 'all') {
    return `All Runs - ${filters.value.year}`
  }
  const run = filters.value.billingRun.charAt(0).toUpperCase() + filters.value.billingRun.slice(1)
  return `${run} ${filters.value.year}`
})

const allDesignationStats = computed(() => {
  const renewals = props.data.allRenewalData?.designation_renewals || {}
  const counts = Object.values(renewals)
  if (counts.length === 0) return null
  
  const total = counts.reduce((sum, count) => sum + count, 0)
  const topDesignation = Object.entries(renewals).reduce((a, b) => a[1] > b[1] ? a : b)[0]
  
  return { total, topDesignation }
})

const namibiaDesignationStats = computed(() => {
  const renewals = props.data.namibiaRenewalData?.designation_renewals || {}
  const counts = Object.values(renewals)
  if (counts.length === 0) return null
  
  const total = counts.reduce((sum, count) => sum + count, 0)
  const topDesignation = Object.entries(renewals).reduce((a, b) => a[1] > b[1] ? a : b)[0]
  
  return { total, topDesignation }
})

const applyFilter = () => {
  console.log('=== APPLYING RENEWALS FILTER ===')
  console.log('Filter values:', {
    billingRun: filters.value.billingRun,
    year: filters.value.year
  })
  
  loading.value = true
  
  const params = {
    billing_run: filters.value.billingRun,
    year: filters.value.year
  }
  
  console.log('Sending params to backend:', params)
  
  router.visit(route('vue.dashboard'), {
    method: 'get',
    data: params,
    only: ['renewalsData'],
    preserveScroll: true,
    preserveState: false,
    onSuccess: (page) => {
      console.log('Filter applied successfully, new data:', page.props.renewalsData)
    },
    onError: (errors) => {
      console.error('Filter application error:', errors)
    },
    onFinish: () => {
      loading.value = false
      console.log('Filter request finished')
    }
  })
}

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-US').format(num)
}
</script>

