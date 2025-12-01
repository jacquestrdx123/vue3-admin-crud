<template>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
        Date Range
      </label>
      <select
        :value="modelValue.dateRange"
        @change="updateDateRange($event.target.value)"
        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
      >
        <option value="this_year">This Year</option>
        <option value="last_year">Last Year</option>
        <option value="this_month">This Month</option>
        <option value="last_month">Last Month</option>
        <option value="this_quarter">This Quarter</option>
        <option value="last_quarter">Last Quarter</option>
        <option value="this_week">This Week</option>
        <option value="last_week">Last Week</option>
        <option value="custom">Custom Range</option>
      </select>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
        Start Date
      </label>
      <input
        :value="modelValue.startDate"
        @input="updateField('startDate', $event.target.value)"
        type="date"
        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
      />
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
        End Date
      </label>
      <input
        :value="modelValue.endDate"
        @input="updateField('endDate', $event.target.value)"
        type="date"
        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
      />
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: {
    type: Object,
    required: true,
    validator: (value) => {
      return value.hasOwnProperty('dateRange') &&
             value.hasOwnProperty('startDate') &&
             value.hasOwnProperty('endDate')
    }
  }
})

const emit = defineEmits(['update:modelValue'])

const calculateDates = (range) => {
  const now = new Date()
  const year = now.getFullYear()
  let start, end

  switch (range) {
    case 'this_year':
      start = new Date(year, 0, 1)
      end = now
      break
    case 'last_year':
      start = new Date(year - 1, 0, 1)
      end = new Date(year - 1, 11, 31)
      break
    case 'this_month':
      start = new Date(year, now.getMonth(), 1)
      end = now
      break
    case 'last_month':
      start = new Date(year, now.getMonth() - 1, 1)
      end = new Date(year, now.getMonth(), 0)
      break
    case 'this_quarter':
      const quarter = Math.floor(now.getMonth() / 3)
      start = new Date(year, quarter * 3, 1)
      end = now
      break
    case 'last_quarter':
      const lastQuarter = Math.floor(now.getMonth() / 3) - 1
      const lastQuarterYear = lastQuarter < 0 ? year - 1 : year
      const lastQuarterMonth = lastQuarter < 0 ? 9 : lastQuarter * 3
      start = new Date(lastQuarterYear, lastQuarterMonth, 1)
      end = new Date(lastQuarterYear, lastQuarterMonth + 3, 0)
      break
    case 'this_week':
      const dayOfWeek = now.getDay()
      const diff = now.getDate() - dayOfWeek + (dayOfWeek === 0 ? -6 : 1)
      start = new Date(now.setDate(diff))
      end = new Date()
      break
    case 'last_week':
      const lastWeekEnd = new Date()
      lastWeekEnd.setDate(lastWeekEnd.getDate() - lastWeekEnd.getDay())
      const lastWeekStart = new Date(lastWeekEnd)
      lastWeekStart.setDate(lastWeekEnd.getDate() - 6)
      start = lastWeekStart
      end = lastWeekEnd
      break
    case 'custom':
      return null
    default:
      start = new Date(year, 0, 1)
      end = now
  }

  return {
    startDate: start.toISOString().split('T')[0],
    endDate: end.toISOString().split('T')[0]
  }
}

const updateDateRange = (range) => {
  const dates = calculateDates(range)
  
  if (dates) {
    emit('update:modelValue', {
      dateRange: range,
      ...dates
    })
  } else {
    emit('update:modelValue', {
      ...props.modelValue,
      dateRange: range
    })
  }
}

const updateField = (field, value) => {
  emit('update:modelValue', {
    ...props.modelValue,
    dateRange: 'custom',
    [field]: value
  })
}
</script>

