<template>
  <MemberCard :hoverable="hoverable" :className="className">
    <div class="flex items-center">
      <div :class="['flex-shrink-0 rounded-md p-3', iconBgClass]">
        <component :is="icon" :class="['h-6 w-6', iconColorClass]" />
      </div>
      <div class="ml-5 w-0 flex-1">
        <dl>
          <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
            {{ title }}
          </dt>
          <dd class="flex items-baseline">
            <div class="text-2xl font-semibold text-gray-900 dark:text-white">
              {{ value }}
            </div>
            <div v-if="change" :class="['ml-2 flex items-baseline text-sm font-semibold', changeColorClass]">
              <svg v-if="changeType === 'increase'" class="self-center flex-shrink-0 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
              </svg>
              <svg v-else-if="changeType === 'decrease'" class="self-center flex-shrink-0 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
              <span class="sr-only">{{ changeType === 'increase' ? 'Increased' : 'Decreased' }} by</span>
              {{ change }}
            </div>
          </dd>
        </dl>
      </div>
    </div>
  </MemberCard>
</template>

<script setup>
import { computed } from 'vue'
import MemberCard from '@/Components/Member/UI/MemberCard.vue'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  value: {
    type: [String, Number],
    required: true
  },
  icon: {
    type: Object,
    required: true
  },
  color: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'success', 'warning', 'danger', 'info'].includes(value)
  },
  change: {
    type: String,
    default: null
  },
  changeType: {
    type: String,
    default: 'increase',
    validator: (value) => ['increase', 'decrease'].includes(value)
  },
  hoverable: {
    type: Boolean,
    default: true
  },
  className: {
    type: String,
    default: ''
  }
})

const iconBgClass = computed(() => {
  const colors = {
    primary: 'bg-member-primary-light',
    success: 'bg-green-100 dark:bg-green-900',
    warning: 'bg-yellow-100 dark:bg-yellow-900',
    danger: 'bg-red-100 dark:bg-red-900',
    info: 'bg-blue-100 dark:bg-blue-900'
  }
  return colors[props.color]
})

const iconColorClass = computed(() => {
  const colors = {
    primary: 'text-member-primary-dark',
    success: 'text-green-600 dark:text-green-400',
    warning: 'text-yellow-600 dark:text-yellow-400',
    danger: 'text-red-600 dark:text-red-400',
    info: 'text-blue-600 dark:text-blue-400'
  }
  return colors[props.color]
})

const changeColorClass = computed(() => {
  return props.changeType === 'increase' ? 'text-green-600' : 'text-red-600'
})
</script>

<style scoped>
:root {
  --member-primary-light: #F0FFB3;
  --member-primary-dark: #5A7302;
}

.bg-member-primary-light {
  background-color: var(--member-primary-light);
}

.text-member-primary-dark {
  color: var(--member-primary-dark);
}
</style>

