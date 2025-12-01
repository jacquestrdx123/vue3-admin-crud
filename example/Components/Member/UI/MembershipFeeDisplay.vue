<template>
  <div>
    <div class="flex items-center justify-between mb-6 mt-4">
      <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
          {{  membershipType }}
        </h3>
        <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">
          {{ description }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  fee: {
    type: [Number, String],
    required: true,
  },
  membershipType: {
    type: String,
    default: 'CIBA Member: Associate Application',
  },
  period: {
    type: String,
    default: 'p.a.',
  },
  description: {
    type: String,
    default: 'Annual membership fee',
  },
})

const formattedFee = computed(() => {
  if (typeof props.fee === 'string') {
    return props.fee
  }
  return `R${props.fee.toLocaleString('en-ZA')}`
})
</script>

