<template>
  <div>
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
              Date Filter - New Applications
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
            Filter membership application data by date range
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

    <!-- Charts Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
      <!-- All Members Chart -->
      <div class="bg-gradient-to-r from-ciba-green/10 to-ciba-turquoise/10 border-l-4 border-ciba-green rounded-lg p-6">
        <h3 class="flex gap-2 items-center text-lg font-bold text-gray-900 dark:text-white mb-4">
          <span class="material-icons text-ciba-green">bar_chart</span>
          Approved Associate Applications
        </h3>
        <div v-if="!data.allChartData" class="text-center py-12 text-gray-500">
          Loading chart data...
        </div>
        <div v-else ref="allChartRef" style="min-height: 300px;"></div>
      </div>

      <!-- Namibia Chart -->
      <div class="bg-gradient-to-r from-ciba-green/10 to-ciba-turquoise/10 border-l-4 border-ciba-green rounded-lg p-6">
        <h3 class="flex gap-2 items-center text-lg font-bold text-gray-900 dark:text-white mb-4">
          <span class="material-icons text-ciba-green">bar_chart</span>
          Approved Associate Applications (Namibia)
        </h3>
        <div v-if="!data.namibiaChartData" class="text-center py-12 text-gray-500">
          Loading chart data...
        </div>
        <div v-else ref="namibiaChartRef" style="min-height: 300px;"></div>
      </div>
    </div>

    <!-- Conversion Info Lists Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- All Members Conversion -->
      <div class="bg-gradient-to-r from-ciba-green/10 to-ciba-turquoise/10 border-l-4 border-ciba-green rounded-lg p-6">
        <h3 class="flex gap-2 items-center text-lg font-bold text-gray-900 dark:text-white mb-2">
          <span class="material-icons text-ciba-green">bar_chart</span>
          Membership Conversion Analytics
        </h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
          Track application conversions and designation progress
        </p>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
          <div class="mb-6">
            <h4 class="mb-3 font-semibold text-gray-800 dark:text-gray-200">
              Membership Applications
            </h4>
            <div class="text-2xl font-bold text-ciba-green">
              {{ data.allConversionData?.applications_count || 0 }}
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400">
              Applications received
            </p>
          </div>
          
          <div>
            <h4 class="mb-3 font-semibold text-gray-800 dark:text-gray-200">
              Designation Conversions
            </h4>
            <div class="space-y-2">
              <div
                v-for="designation in data.allConversionData?.designations || []"
                :key="designation.id"
                class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700 last:border-b-0"
              >
                <span class="text-sm text-gray-700 dark:text-gray-300">
                  {{ designation.name }}
                </span>
                <span class="text-sm font-semibold" :class="designation.count > 0 ? 'text-ciba-green' : 'text-gray-400'">
                  {{ designation.count }}
                </span>
              </div>
              <p v-if="!data.allConversionData?.designations?.length" class="text-sm text-gray-500 dark:text-gray-400">
                No designation data available
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Namibia Conversion -->
      <div class="bg-gradient-to-r from-ciba-green/10 to-ciba-turquoise/10 border-l-4 border-ciba-green rounded-lg p-6">
        <h3 class="flex gap-2 items-center text-lg font-bold text-gray-900 dark:text-white mb-2">
          <span class="material-icons text-ciba-green">bar_chart</span>
          Namibia Membership Conversion Analytics
        </h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
          Track application conversions and designation progress
        </p>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
          <div class="mb-6">
            <h4 class="mb-3 font-semibold text-gray-800 dark:text-gray-200">
              Membership Applications
            </h4>
            <div class="text-2xl font-bold text-ciba-green">
              {{ data.namibiaConversionData?.applications_count || 0 }}
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400">
              Applications received
            </p>
          </div>
          
          <div>
            <h4 class="mb-3 font-semibold text-gray-800 dark:text-gray-200">
              Designation Conversions
            </h4>
            <div class="space-y-2">
              <div
                v-for="designation in data.namibiaConversionData?.designations || []"
                :key="designation.id"
                class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700 last:border-b-0"
              >
                <span class="text-sm text-gray-700 dark:text-gray-300">
                  {{ designation.name }}
                </span>
                <span class="text-sm font-semibold" :class="designation.count > 0 ? 'text-ciba-green' : 'text-gray-400'">
                  {{ designation.count }}
                </span>
              </div>
              <p v-if="!data.namibiaConversionData?.designations?.length" class="text-sm text-gray-500 dark:text-gray-400">
                No designation data available
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue'
import { router } from '@inertiajs/vue3'
import ApexCharts from 'apexcharts'
import 'apexcharts/dist/apexcharts.css'

const props = defineProps({
  data: {
    type: Object,
    default: () => ({})
  }
})

const allChartRef = ref(null)
const namibiaChartRef = ref(null)
let allChart = null
let namibiaChart = null

const isFilterExpanded = ref(true)
const loading = ref(false)
const filters = ref({
  dateRange: 'this_year',
  startDate: new Date().getFullYear() + '-01-01',
  endDate: new Date().toISOString().split('T')[0]
})

// Watch for manual date changes and switch to custom mode
watch(() => filters.value.startDate, () => {
  if (filters.value.dateRange !== 'custom') {
    console.log('Start date manually changed, switching to custom mode')
    filters.value.dateRange = 'custom'
  }
})

watch(() => filters.value.endDate, () => {
  if (filters.value.dateRange !== 'custom') {
    console.log('End date manually changed, switching to custom mode')
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
  console.log('=== APPLYING NEW APPLICATIONS FILTER ===')
  console.log('Filter values:', {
    dateRange: filters.value.dateRange,
    startDate: filters.value.startDate,
    endDate: filters.value.endDate
  })
  
  loading.value = true
  
  const params = {
    date_range: filters.value.dateRange,
    start_date: filters.value.startDate,
    end_date: filters.value.endDate
  }
  
  console.log('Sending params to backend:', params)
  
  router.visit(route('vue.dashboard'), {
    method: 'get',
    data: params,
    only: ['newApplicationsData'],
    preserveScroll: true,
    preserveState: false,
    onSuccess: (page) => {
      console.log('Filter applied successfully, new data:', page.props.newApplicationsData)
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

const renderCharts = async () => {
  await nextTick()
  
  if (allChart) {
    allChart.destroy()
    allChart = null
  }
  if (namibiaChart) {
    namibiaChart.destroy()
    namibiaChart = null
  }

  if (allChartRef.value && props.data.allChartData) {
    const allOptions = {
      chart: {
        type: 'area',
        height: 300,
        toolbar: {
          show: false
        },
        animations: {
          enabled: true,
          easing: 'easeinout',
          speed: 800,
        }
      },
      series: [{
        name: 'Memberships Per Month',
        data: props.data.allChartData.data || [],
      }],
      xaxis: {
        categories: props.data.allChartData.labels || [],
        labels: {
          style: {
            fontFamily: 'inherit',
            fontWeight: 600,
          },
        },
      },
      yaxis: {
        labels: {
          style: {
            fontFamily: 'inherit',
          },
        },
      },
      colors: ['#f59e0b'],
      stroke: {
        curve: 'smooth',
        width: 2,
      },
      fill: {
        type: 'gradient',
        gradient: {
          shadeIntensity: 1,
          opacityFrom: 0.7,
          opacityTo: 0.2,
        },
      },
      dataLabels: {
        enabled: false
      },
      tooltip: {
        theme: 'light',
        x: {
          show: true
        }
      }
    }
    
    try {
      allChart = new ApexCharts(allChartRef.value, allOptions)
      await allChart.render()
    } catch (error) {
      console.error('Error rendering all members chart:', error)
    }
  }

  if (namibiaChartRef.value && props.data.namibiaChartData) {
    const namibiaOptions = {
      chart: {
        type: 'area',
        height: 300,
        toolbar: {
          show: false
        },
        animations: {
          enabled: true,
          easing: 'easeinout',
          speed: 800,
        }
      },
      series: [{
        name: 'Memberships Per Month',
        data: props.data.namibiaChartData.data || [],
      }],
      xaxis: {
        categories: props.data.namibiaChartData.labels || [],
        labels: {
          style: {
            fontFamily: 'inherit',
            fontWeight: 600,
          },
        },
      },
      yaxis: {
        labels: {
          style: {
            fontFamily: 'inherit',
          },
        },
      },
      colors: ['#f59e0b'],
      stroke: {
        curve: 'smooth',
        width: 2,
      },
      fill: {
        type: 'gradient',
        gradient: {
          shadeIntensity: 1,
          opacityFrom: 0.7,
          opacityTo: 0.2,
        },
      },
      dataLabels: {
        enabled: false
      },
      tooltip: {
        theme: 'light',
        x: {
          show: true
        }
      }
    }
    
    try {
      namibiaChart = new ApexCharts(namibiaChartRef.value, namibiaOptions)
      await namibiaChart.render()
    } catch (error) {
      console.error('Error rendering Namibia chart:', error)
    }
  }
}

onMounted(() => {
  console.log('NewApplicationsAnalytics mounted, data:', props.data)
  setTimeout(() => {
    renderCharts()
  }, 100)
})

watch(() => props.data, (newData) => {
  console.log('NewApplicationsAnalytics data updated:', newData)
  setTimeout(() => {
    renderCharts()
  }, 100)
}, { deep: true })
</script>

