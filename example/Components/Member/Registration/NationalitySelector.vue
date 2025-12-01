<template>
  <div class="space-y-2">
    <label :for="id" class="block text-sm font-medium text-gray-700">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>

    <select
      :id="id"
      v-model="localValue"
      class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
      :class="{
        'border-gray-300': !error,
        'border-red-500': error
      }"
      @change="onChange"
    >
      <option value="">Select nationality</option>
      <option v-for="country in countries" :key="country.code" :value="country.code">
        {{ country.name }}
      </option>
    </select>

    <p v-if="hint && !error" class="text-sm text-gray-500">{{ hint }}</p>
    <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  id: {
    type: String,
    required: true
  },
  label: {
    type: String,
    default: 'Nationality'
  },
  modelValue: {
    type: String,
    default: 'ZA'
  },
  countries: {
    type: Array,
    required: true
  },
  required: {
    type: Boolean,
    default: false
  },
  hint: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:modelValue'])

const localValue = ref(props.modelValue)

watch(() => props.modelValue, (newVal) => {
  localValue.value = newVal
})

const onChange = () => {
  emit('update:modelValue', localValue.value)
}
</script>

