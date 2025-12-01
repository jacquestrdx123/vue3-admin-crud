<template>
  <div class="flex gap-2">
    <input
      type="date"
      :value="modelValue?.from || ''"
      @change="updateFrom"
      placeholder="From"
      class="block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
    />
    <input
      type="date"
      :value="modelValue?.to || ''"
      @change="updateTo"
      placeholder="To"
      class="block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
    />
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({ from: null, to: null })
  },
  label: {
    type: String,
    required: true
  }
})

const emit = defineEmits(['update:modelValue'])

const updateFrom = (event) => {
  emit('update:modelValue', {
    from: event.target.value || null,
    to: props.modelValue?.to || null
  })
}

const updateTo = (event) => {
  emit('update:modelValue', {
    from: props.modelValue?.from || null,
    to: event.target.value || null
  })
}
</script>

