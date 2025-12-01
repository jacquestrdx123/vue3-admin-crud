<template>
  <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-200 dark:border-gray-700">
    <div class="px-4 py-5 sm:p-6">
      <div class="flex items-center">
        <div class="flex-shrink-0">
          <div :class="[
            'p-3 rounded-md',
            colorClasses[color] || colorClasses.blue
          ]">
            <slot name="icon">
              <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
              </svg>
            </slot>
          </div>
        </div>
        <div class="ml-5 w-0 flex-1">
          <dl>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
              {{ label }}
            </dt>
            <dd class="flex items-baseline">
              <div class="text-2xl font-semibold text-gray-900 dark:text-white">
                <template v-if="format === 'currency'">
                  {{ currency }}{{ formatNumber(value) }}
                </template>
                <template v-else-if="format === 'percentage'">
                  {{ value }}%
                </template>
                <template v-else>
                  {{ formatNumber(value) }}
                </template>
              </div>
              <div
                v-if="change !== null && change !== undefined"
                :class="[
                  'ml-2 flex items-baseline text-sm font-semibold',
                  change >= 0 ? 'text-green-600' : 'text-red-600'
                ]"
              >
                <svg
                  v-if="change >= 0"
                  class="self-center flex-shrink-0 h-5 w-5 text-green-500"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path
                    fill-rule="evenodd"
                    d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z"
                    clip-rule="evenodd"
                  />
                </svg>
                <svg
                  v-else
                  class="self-center flex-shrink-0 h-5 w-5 text-red-500"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path
                    fill-rule="evenodd"
                    d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z"
                    clip-rule="evenodd"
                  />
                </svg>
                <span class="ml-1">{{ Math.abs(change) }}%</span>
              </div>
            </dd>
            <dd v-if="subtext" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              {{ subtext }}
            </dd>
          </dl>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  label: {
    type: String,
    required: true
  },
  value: {
    type: [String, Number],
    required: true
  },
  format: {
    type: String,
    default: 'number', // number, currency, percentage
    validator: (value) => ['number', 'currency', 'percentage'].includes(value)
  },
  currency: {
    type: String,
    default: 'R'
  },
  color: {
    type: String,
    default: 'blue',
    validator: (value) => ['blue', 'green', 'red', 'yellow', 'indigo', 'purple', 'pink'].includes(value)
  },
  change: {
    type: Number,
    default: null
  },
  subtext: {
    type: String,
    default: null
  }
})

const colorClasses = {
  blue: 'bg-blue-500',
  green: 'bg-green-500',
  red: 'bg-red-500',
  yellow: 'bg-yellow-500',
  indigo: 'bg-indigo-500',
  purple: 'bg-purple-500',
  pink: 'bg-pink-500'
}

const formatNumber = (num) => {
  if (typeof num === 'string') return num
  return num.toLocaleString('en-US')
}
</script>

