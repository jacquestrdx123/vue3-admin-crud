<template>
  <MemberDashboardLayout :title="title" :breadcrumbs="breadcrumbs">
    <!-- Stepper -->
    <WizardStepper 
      :steps="steps"
      :current-step="currentStep"
      class="mb-8"
    />
    
    <!-- Content -->
    <MemberCard>
      <slot />
      
      <!-- Navigation -->
      <template #footer>
        <div class="flex justify-between items-center">
          <MemberButton
            v-if="currentStep > 1"
            @click="emit('previous')"
            variant="secondary"
            :disabled="processing"
          >
            ← Previous
          </MemberButton>
          <div v-else></div>
          
          <div class="flex gap-3">
            <MemberButton
              @click="emit('save-draft')"
              variant="ghost"
              :disabled="processing"
            >
              Save Draft
            </MemberButton>
            <MemberButton
              v-if="currentStep < totalSteps"
              @click="emit('next')"
              :disabled="!canProceed || processing"
              variant="primary"
            >
              Next →
            </MemberButton>
            <MemberButton
              v-else
              @click="emit('submit')"
              :loading="isSubmitting"
              :disabled="!canProceed || isSubmitting"
              variant="primary"
            >
              Submit Application
            </MemberButton>
          </div>
        </div>
      </template>
    </MemberCard>
  </MemberDashboardLayout>
</template>

<script setup>
import MemberDashboardLayout from '@/Layouts/MemberDashboardLayout.vue'
import MemberCard from '@/Components/Member/UI/MemberCard.vue'
import MemberButton from '@/Components/Member/UI/MemberButton.vue'
import WizardStepper from '@/Components/Member/Applications/WizardStepper.vue'

defineProps({
  title: {
    type: String,
    required: true
  },
  breadcrumbs: {
    type: Array,
    default: () => []
  },
  steps: {
    type: Array,
    required: true
  },
  currentStep: {
    type: Number,
    required: true
  },
  totalSteps: {
    type: Number,
    required: true
  },
  canProceed: {
    type: Boolean,
    default: true
  },
  processing: {
    type: Boolean,
    default: false
  },
  isSubmitting: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['next', 'previous', 'save-draft', 'submit'])
</script>

