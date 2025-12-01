<template>
  <Modal :show="show" :title="title" max-width="md" @close="handleCancel">
    <div class="text-sm text-gray-600 dark:text-gray-400">
      {{ message }}
    </div>

    <template #footer>
      <div class="flex justify-end gap-3">
        <Button variant="outline" @click="handleCancel">
          {{ cancelText }}
        </Button>
        <Button :variant="dangerMode ? 'danger' : 'primary'" @click="handleConfirm">
          {{ confirmText }}
        </Button>
      </div>
    </template>
  </Modal>
</template>

<script setup>
import Modal from './Modal.vue'
import Button from './Button.vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: 'Confirm Action'
  },
  message: {
    type: String,
    required: true
  },
  confirmText: {
    type: String,
    default: 'Confirm'
  },
  cancelText: {
    type: String,
    default: 'Cancel'
  },
  dangerMode: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['confirm', 'cancel', 'update:show'])

const handleConfirm = () => {
  emit('confirm')
  emit('update:show', false)
}

const handleCancel = () => {
  emit('cancel')
  emit('update:show', false)
}
</script>

