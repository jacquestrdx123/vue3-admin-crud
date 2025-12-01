<template>
  <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
      <h3 v-if="title" class="text-lg font-medium text-gray-900 dark:text-white mb-4">
        {{ title }}
      </h3>
      <apexchart
        :type="type"
        :height="height"
        :options="chartOptions"
        :series="series"
      />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import VueApexCharts from 'vue3-apexcharts'

const apexchart = VueApexCharts

const props = defineProps({
  title: {
    type: String,
    default: null
  },
  type: {
    type: String,
    default: 'line' // line, area, bar, pie, donut, radialBar
  },
  series: {
    type: Array,
    required: true
  },
  categories: {
    type: Array,
    default: () => []
  },
  height: {
    type: [Number, String],
    default: 350
  },
  colors: {
    type: Array,
    default: () => ['#4F46E5', '#10B981', '#F59E0B', '#EF4444']
  },
  customOptions: {
    type: Object,
    default: () => ({})
  }
})

const chartOptions = computed(() => {
  return {
    chart: {
      type: props.type,
      toolbar: {
        show: true
      },
      background: 'transparent'
    },
    colors: props.colors,
    xaxis: {
      categories: props.categories,
      labels: {
        style: {
          colors: '#9CA3AF'
        }
      }
    },
    yaxis: {
      labels: {
        style: {
          colors: '#9CA3AF'
        }
      }
    },
    dataLabels: {
      enabled: false
    },
    stroke: {
      curve: 'smooth',
      width: 2
    },
    grid: {
      borderColor: '#374151',
      strokeDashArray: 4
    },
    legend: {
      labels: {
        colors: '#9CA3AF'
      }
    },
    tooltip: {
      theme: 'dark'
    },
    ...props.customOptions
  }
})
</script>

