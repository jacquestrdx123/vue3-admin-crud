<template>
  <div class="flex items-center gap-2">
    <input
      :id="name"
      type="checkbox"
      :checked="modelValue"
      @change="$emit('update:modelValue', $event.target.checked)"
      class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600"
    />
    <label
      :for="name"
      class="text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer"
    >
      {{ label }}
    </label>
  </div>
</template>

<script setup>
defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  name: {
    type: String,
    required: true
  },
  label: {
    type: String,
    required: true
  }
})

defineEmits(['update:modelValue'])
</script>
