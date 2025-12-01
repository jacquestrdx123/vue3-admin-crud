<template>
  <BaseForm
    :initial-data="formData"
    :submit-url="submitUrl"
    :method="method"
    @success="$emit('success', $event)"
    @error="$emit('error', $event)"
    @cancel="$emit('cancel')"
  >
    <template #default="{ form, errors }">
      <component
        v-for="field in fields"
        :key="field.name"
        :is="getFieldComponent(field.type)"
        v-model="form[field.name]"
        v-bind="field"
        :error-messages="errors[field.name]"
        :form-data="form"
      />
    </template>
  </BaseForm>
</template>

<script setup>
import { computed } from 'vue'
import BaseForm from './BaseForm.vue'
import TextField from './TextField.vue'
import SelectField from './SelectField.vue'
import CheckboxField from './CheckboxField.vue'
import ToggleField from './ToggleField.vue'
import TextareaField from './TextareaField.vue'
import NumberField from './NumberField.vue'
import DateField from './DateField.vue'

const props = defineProps({
  fields: {
    type: Array,
    required: true
  },
  initialData: {
    type: Object,
    default: () => ({})
  },
  submitUrl: {
    type: String,
    default: null
  },
  method: {
    type: String,
    default: 'post'
  }
})

defineEmits(['success', 'error', 'cancel'])

const formData = computed(() => {
  const data = { ...props.initialData }
  
  props.fields.forEach(field => {
    if (!(field.name in data)) {
      data[field.name] = field.default || field.value || null
    }
  })
  
  return data
})

const getFieldComponent = (type) => {
  const components = {
    text: TextField,
    email: TextField,
    password: TextField,
    number: NumberField,
    select: SelectField,
    checkbox: CheckboxField,
    toggle: ToggleField,
    textarea: TextareaField,
    date: DateField,
    datetime: DateField,
    time: DateField
  }
  
  return components[type] || TextField
}
</script>

