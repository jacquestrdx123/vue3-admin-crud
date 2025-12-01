<template>
  <div class="space-y-4">
    <div v-if="successMessage" class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
      {{ successMessage }}
    </div>

    <div v-if="errorMessage" class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
      {{ errorMessage }}
    </div>

    <form @submit.prevent="submitForm" class="space-y-6">
      <!-- Registered with Another Professional Body -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Are you currently registered with another professional body? *
        </label>
        <div class="flex gap-4">
          <label class="inline-flex items-center">
            <input
              v-model="formData.registered_another_professional_body"
              type="radio"
              value="yes"
              class="rounded border-gray-300 text-ciba-green focus:ring-ciba-green"
            />
            <span class="ml-2 text-sm">Yes</span>
          </label>
          <label class="inline-flex items-center">
            <input
              v-model="formData.registered_another_professional_body"
              type="radio"
              value="no"
              class="rounded border-gray-300 text-ciba-green focus:ring-ciba-green"
            />
            <span class="ml-2 text-sm">No</span>
          </label>
        </div>
        <p v-if="errors.registered_another_professional_body" class="mt-1 text-sm text-red-600">{{ getErrorMessage('registered_another_professional_body') }}</p>
      </div>

      <div v-if="formData.registered_another_professional_body === 'yes'" class="mt-3">
        <label class="fi-form-field-label">
          Please select all professional bodies you are a member of:
        </label>
        
        <!-- Custom Multi-Select Dropdown with Checkboxes -->
        <div ref="dropdownRef" class="relative mt-2">
          <!-- Selected Items Display / Trigger -->
          <div
            @click="isDropdownOpen = !isDropdownOpen"
            class="min-h-[42px] block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm cursor-pointer fi-input hover:border-gray-400 transition-colors"
            :class="errors.other_professional_body_ids ? 'border-red-300' : (isDropdownOpen ? 'border-ciba-green ring-2 ring-ciba-green ring-opacity-20' : '')"
          >
            <div v-if="selectedProfessionalBodies.length > 0" class="flex flex-wrap gap-1.5">
              <span
                v-for="body in selectedProfessionalBodies"
                :key="body.value"
                class="inline-flex items-center gap-1 px-2 py-1 text-xs bg-ciba-green bg-opacity-20 text-gray-800 rounded-md font-medium"
              >
                {{ body.label }}
                <button
                  type="button"
                  @click.stop="removeProfessionalBody(body.value)"
                  class="hover:text-red-600 transition-colors"
                >
                  <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                </button>
              </span>
            </div>
            <span v-else class="text-gray-500">
              Select professional bodies...
            </span>
            <svg
              class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none"
              :class="{ 'rotate-180': isDropdownOpen }"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </div>

          <!-- Dropdown with Checkboxes -->
          <transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
          >
            <div
              v-show="isDropdownOpen"
              class="absolute z-50 mt-1 w-full bg-white shadow-lg rounded-lg border border-gray-200 max-h-60 overflow-auto"
            >
              <div
            v-for="body in other_professional_bodies"
            :key="body.id"
                @click="toggleProfessionalBody(body.id)"
                class="px-3 py-2 cursor-pointer hover:bg-gray-50 flex items-center gap-2 transition-colors"
          >
            <input
              type="checkbox"
                  :checked="formData.other_professional_body_ids.includes(body.id)"
              class="rounded border-gray-300 text-ciba-green focus:ring-ciba-green"
                  @click.stop="toggleProfessionalBody(body.id)"
                  readonly
            />
                <span class="text-sm text-gray-900">{{ body.name }}</span>
              </div>
              <div
                @click="toggleProfessionalBody('other')"
                class="px-3 py-2 cursor-pointer hover:bg-gray-50 flex items-center gap-2 transition-colors border-t border-gray-200"
              >
            <input
              type="checkbox"
                  :checked="formData.other_professional_body_ids.includes('other')"
              class="rounded border-gray-300 text-ciba-green focus:ring-ciba-green"
                  @click.stop="toggleProfessionalBody('other')"
                  readonly
            />
                <span class="text-sm font-medium text-gray-900">Other</span>
              </div>
            </div>
          </transition>
        </div>
        
        <div v-if="formData.other_professional_body_ids.includes('other')" class="mt-3">
          <label class="fi-form-field-label">
            Please specify the professional body:
          </label>
          <input
            type="text"
            v-model="formData.registered_another_professional_body_specify"
            class="fi-input"
            :class="errors.registered_another_professional_body_specify ? 'border-red-300' : ''"
            placeholder="Please specify the professional body"
          />
        </div>
        <span v-if="errors.other_professional_body_ids" class="fi-error">{{ getErrorMessage('other_professional_body_ids') }}</span>
        <span v-if="errors.registered_another_professional_body_specify" class="fi-error">{{ getErrorMessage('registered_another_professional_body_specify') }}</span>
      </div>

      <!-- Removed from Professional Body -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Have you ever been removed as a member from a professional body? *
        </label>
        <div class="flex gap-4">
          <label class="inline-flex items-center">
            <input
              v-model="formData.removed_from_professional_body"
              type="radio"
              value="yes"
              class="rounded border-gray-300 text-ciba-green focus:ring-ciba-green"
            />
            <span class="ml-2 text-sm">Yes</span>
          </label>
          <label class="inline-flex items-center">
            <input
              v-model="formData.removed_from_professional_body"
              type="radio"
              value="no"
              class="rounded border-gray-300 text-ciba-green focus:ring-ciba-green"
            />
            <span class="ml-2 text-sm">No</span>
          </label>
        </div>
        <p v-if="errors.removed_from_professional_body" class="mt-1 text-sm text-red-600">{{ getErrorMessage('removed_from_professional_body') }}</p>
      </div>

      <div v-if="formData.removed_from_professional_body === 'yes'">
        <label class="block text-sm font-medium text-gray-700 mb-1">Please specify</label>
        <textarea
          v-model="formData.removed_from_professional_body_specify"
          rows="3"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
        ></textarea>
        <p v-if="errors.removed_from_professional_body_specify" class="mt-1 text-sm text-red-600">{{ getErrorMessage('removed_from_professional_body_specify') }}</p>
      </div>

      <!-- Theft/Forgery -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Have you been convicted of theft, forgery, or issuing a forged document? *
        </label>
        <div class="flex gap-4">
          <label class="inline-flex items-center">
            <input
              v-model="formData.theft_forgery"
              type="radio"
              value="yes"
              class="rounded border-gray-300 text-ciba-green focus:ring-ciba-green"
            />
            <span class="ml-2 text-sm">Yes</span>
          </label>
          <label class="inline-flex items-center">
            <input
              v-model="formData.theft_forgery"
              type="radio"
              value="no"
              class="rounded border-gray-300 text-ciba-green focus:ring-ciba-green"
            />
            <span class="ml-2 text-sm">No</span>
          </label>
        </div>
        <p v-if="errors.theft_forgery" class="mt-1 text-sm text-red-600">{{ getErrorMessage('theft_forgery') }}</p>
      </div>

      <div v-if="formData.theft_forgery === 'yes'">
        <label class="block text-sm font-medium text-gray-700 mb-1">Please specify</label>
        <textarea
          v-model="formData.theft_forgery_specify"
          rows="3"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
        ></textarea>
        <p v-if="errors.theft_forgery_specify" class="mt-1 text-sm text-red-600">{{ getErrorMessage('theft_forgery_specify') }}</p>
      </div>

      <!-- Unrehabilitated/Insolvent -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Are you declared un-rehabilitated or insolvent? *
        </label>
        <div class="flex gap-4">
          <label class="inline-flex items-center">
            <input
              v-model="formData.unrehabilitated_insolvent"
              type="radio"
              value="yes"
              class="rounded border-gray-300 text-ciba-green focus:ring-ciba-green"
            />
            <span class="ml-2 text-sm">Yes</span>
          </label>
          <label class="inline-flex items-center">
            <input
              v-model="formData.unrehabilitated_insolvent"
              type="radio"
              value="no"
              class="rounded border-gray-300 text-ciba-green focus:ring-ciba-green"
            />
            <span class="ml-2 text-sm">No</span>
          </label>
        </div>
        <p v-if="errors.unrehabilitated_insolvent" class="mt-1 text-sm text-red-600">{{ getErrorMessage('unrehabilitated_insolvent') }}</p>
      </div>

      <div v-if="formData.unrehabilitated_insolvent === 'yes'">
        <label class="block text-sm font-medium text-gray-700 mb-1">Please specify</label>
        <textarea
          v-model="formData.unrehabilitated_insolvent_specify"
          rows="3"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
        ></textarea>
        <p v-if="errors.unrehabilitated_insolvent_specify" class="mt-1 text-sm text-red-600">{{ getErrorMessage('unrehabilitated_insolvent_specify') }}</p>
      </div>

      <!-- Office of Trust Misconduct -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Have you been removed from an office of trust on account of misconduct? *
        </label>
        <div class="flex gap-4">
          <label class="inline-flex items-center">
            <input
              v-model="formData.office_trust_misconduct"
              type="radio"
              value="yes"
              class="rounded border-gray-300 text-ciba-green focus:ring-ciba-green"
            />
            <span class="ml-2 text-sm">Yes</span>
          </label>
          <label class="inline-flex items-center">
            <input
              v-model="formData.office_trust_misconduct"
              type="radio"
              value="no"
              class="rounded border-gray-300 text-ciba-green focus:ring-ciba-green"
            />
            <span class="ml-2 text-sm">No</span>
          </label>
        </div>
        <p v-if="errors.office_trust_misconduct" class="mt-1 text-sm text-red-600">{{ getErrorMessage('office_trust_misconduct') }}</p>
      </div>

      <div v-if="formData.office_trust_misconduct === 'yes'">
        <label class="block text-sm font-medium text-gray-700 mb-1">Please specify</label>
        <textarea
          v-model="formData.office_trust_misconduct_specify"
          rows="3"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
        ></textarea>
        <p v-if="errors.office_trust_misconduct_specify" class="mt-1 text-sm text-red-600">{{ getErrorMessage('office_trust_misconduct_specify') }}</p>
      </div>

      <!-- Professional Conduct CIBA -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Is there any information relating to your professional conduct of which CIBA should be aware? *
        </label>
        <div class="flex gap-4">
          <label class="inline-flex items-center">
            <input
              v-model="formData.professional_conduct_ciba"
              type="radio"
              value="yes"
              class="rounded border-gray-300 text-ciba-green focus:ring-ciba-green"
            />
            <span class="ml-2 text-sm">Yes</span>
          </label>
          <label class="inline-flex items-center">
            <input
              v-model="formData.professional_conduct_ciba"
              type="radio"
              value="no"
              class="rounded border-gray-300 text-ciba-green focus:ring-ciba-green"
            />
            <span class="ml-2 text-sm">No</span>
          </label>
        </div>
        <p v-if="errors.professional_conduct_ciba" class="mt-1 text-sm text-red-600">{{ getErrorMessage('professional_conduct_ciba') }}</p>
      </div>

      <div v-if="formData.professional_conduct_ciba === 'yes'">
        <label class="block text-sm font-medium text-gray-700 mb-1">Please specify</label>
        <textarea
          v-model="formData.professional_conduct_ciba_specify"
          rows="3"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
        ></textarea>
        <p v-if="errors.professional_conduct_ciba_specify" class="mt-1 text-sm text-red-600">{{ getErrorMessage('professional_conduct_ciba_specify') }}</p>
      </div>

      <!-- Paying Application Fees -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Do you commit to paying the applicable annual fees due? *
        </label>
        <div class="flex gap-4">
          <label class="inline-flex items-center">
            <input
              v-model="formData.paying_application_fees"
              type="radio"
              value="yes"
              class="rounded border-gray-300 text-ciba-green focus:ring-ciba-green"
            />
            <span class="ml-2 text-sm">Yes</span>
          </label>
          <label class="inline-flex items-center">
            <input
              v-model="formData.paying_application_fees"
              type="radio"
              value="no"
              class="rounded border-gray-300 text-ciba-green focus:ring-ciba-green"
            />
            <span class="ml-2 text-sm">No</span>
          </label>
        </div>
        <p v-if="errors.paying_application_fees" class="mt-1 text-sm text-red-600">{{ getErrorMessage('paying_application_fees') }}</p>
      </div>

      <div v-if="formData.paying_application_fees === 'no'">
        <label class="block text-sm font-medium text-gray-700 mb-1">Please specify</label>
        <textarea
          v-model="formData.paying_application_fees_specify"
          rows="3"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
        ></textarea>
        <p v-if="errors.paying_application_fees_specify" class="mt-1 text-sm text-red-600">{{ getErrorMessage('paying_application_fees_specify') }}</p>
      </div>

      <!-- Currently Under Investigation -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Are you currently under investigation of any kind? *
        </label>
        <div class="flex gap-4">
          <label class="inline-flex items-center">
            <input
              v-model="formData.currently_under_investigation"
              type="radio"
              value="yes"
              class="rounded border-gray-300 text-ciba-green focus:ring-ciba-green"
            />
            <span class="ml-2 text-sm">Yes</span>
          </label>
          <label class="inline-flex items-center">
            <input
              v-model="formData.currently_under_investigation"
              type="radio"
              value="no"
              class="rounded border-gray-300 text-ciba-green focus:ring-ciba-green"
            />
            <span class="ml-2 text-sm">No</span>
          </label>
        </div>
        <p v-if="errors.currently_under_investigation" class="mt-1 text-sm text-red-600">{{ getErrorMessage('currently_under_investigation') }}</p>
      </div>

      <div v-if="formData.currently_under_investigation === 'yes'">
        <label class="block text-sm font-medium text-gray-700 mb-1">Please specify</label>
        <textarea
          v-model="formData.currently_under_investigation_specify"
          rows="3"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
        ></textarea>
        <p v-if="errors.currently_under_investigation_specify" class="mt-1 text-sm text-red-600">{{ getErrorMessage('currently_under_investigation_specify') }}</p>
      </div>

      <!-- Support CIBA -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Will you support and stand behind CIBA's efforts to advance our profession? *
        </label>
        <div class="flex gap-4">
          <label class="inline-flex items-center">
            <input
              v-model="formData.support_share_bind_ciba"
              type="radio"
              value="yes"
              class="rounded border-gray-300 text-ciba-green focus:ring-ciba-green"
            />
            <span class="ml-2 text-sm">Yes</span>
          </label>
          <label class="inline-flex items-center">
            <input
              v-model="formData.support_share_bind_ciba"
              type="radio"
              value="no"
              class="rounded border-gray-300 text-ciba-green focus:ring-ciba-green"
            />
            <span class="ml-2 text-sm">No</span>
          </label>
        </div>
        <p v-if="errors.support_share_bind_ciba" class="mt-1 text-sm text-red-600">{{ getErrorMessage('support_share_bind_ciba') }}</p>
      </div>

      <div v-if="formData.support_share_bind_ciba === 'no'">
        <label class="block text-sm font-medium text-gray-700 mb-1">Please specify</label>
        <textarea
          v-model="formData.support_share_bind_ciba_specify"
          rows="3"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-ciba-green focus:ring-ciba-green"
        ></textarea>
        <p v-if="errors.support_share_bind_ciba_specify" class="mt-1 text-sm text-red-600">{{ getErrorMessage('support_share_bind_ciba_specify') }}</p>
      </div>

      <div class="flex justify-end gap-3 pt-4">
        <button
          type="submit"
          :disabled="submitting"
          class="px-6 py-2 bg-ciba-green hover:bg-[#A9E204] text-black font-semibold rounded-full transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
        >
          <span v-if="submitting">Saving...</span>
          <span v-else>Save Changes</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted, onUnmounted, computed } from 'vue'
import axios from 'axios'

const props = defineProps({
  member: { type: Object, required: true },
  applicationId: { type: [Number, String], required: false, default: null },
  otherProfessionalBodies: { type: Array, default: () => [] }
})

const emit = defineEmits(['completion-updated'])

const submitting = ref(false)
const successMessage = ref(null)
const errorMessage = ref(null)
const errors = ref({})
const other_professional_bodies = ref(props.otherProfessionalBodies || [])
const isDropdownOpen = ref(false)
const dropdownRef = ref(null)

// Load other professional bodies if not provided as prop
onMounted(async () => {
  if (other_professional_bodies.value.length === 0) {
    try {
      const response = await axios.get(route('api.other-professional-bodies.index'))
      other_professional_bodies.value = response.data.data || response.data || []
    } catch (error) {
      console.error('Failed to load professional bodies:', error)
    }
  }
})

function normalizeBooleanValue(value) {
  if (value === null || value === undefined || value === '') {
    return ''
  }
  if (value === 'yes' || value === 1 || value === '1' || value === true) {
    return 'yes'
  }
  if (value === 'no' || value === 0 || value === '0' || value === false) {
    return 'no'
  }
  return ''
}

// Initialize other_professional_body_ids from member's relationships
const initializeProfessionalBodies = () => {
  const ids = []
  if (props.member.other_professional_bodies) {
    props.member.other_professional_bodies.forEach(body => {
      if (body.pivot?.other_professional_body_id) {
        ids.push(body.pivot.other_professional_body_id)
      } else if (body.pivot?.custom_name) {
        ids.push('other')
      }
    })
  }
  // Fallback: if member has old other_professional_body_id, use it
  if (ids.length === 0 && props.member.other_professional_body_id) {
    ids.push(props.member.other_professional_body_id)
  }
  return ids
}

const formData = reactive({
  registered_another_professional_body: normalizeBooleanValue(props.member.registered_another_professional_body),
  other_professional_body_ids: initializeProfessionalBodies(),
  registered_another_professional_body_specify: props.member.registered_another_professional_body_specify || '',
  removed_from_professional_body: normalizeBooleanValue(props.member.removed_from_professional_body),
  removed_from_professional_body_specify: props.member.removed_from_professional_body_specify || '',
  theft_forgery: normalizeBooleanValue(props.member.theft_forgery),
  theft_forgery_specify: props.member.theft_forgery_specify || '',
  unrehabilitated_insolvent: normalizeBooleanValue(props.member.unrehabilitated_insolvent),
  unrehabilitated_insolvent_specify: props.member.unrehabilitated_insolvent_specify || '',
  office_trust_misconduct: normalizeBooleanValue(props.member.office_trust_misconduct),
  office_trust_misconduct_specify: props.member.office_trust_misconduct_specify || '',
  professional_conduct_ciba: normalizeBooleanValue(props.member.professional_conduct_ciba),
  professional_conduct_ciba_specify: props.member.professional_conduct_ciba_specify || '',
  paying_application_fees: normalizeBooleanValue(props.member.paying_application_fees),
  paying_application_fees_specify: props.member.paying_application_fees_specify || '',
  currently_under_investigation: normalizeBooleanValue(props.member.currently_under_investigation),
  currently_under_investigation_specify: props.member.currently_under_investigation_specify || '',
  support_share_bind_ciba: normalizeBooleanValue(props.member.support_share_bind_ciba),
  support_share_bind_ciba_specify: props.member.support_share_bind_ciba_specify || ''
})

watch(() => props.member, (newMember) => {
  const professionalBodyIds = []
  if (newMember.other_professional_bodies) {
    newMember.other_professional_bodies.forEach(body => {
      if (body.pivot?.other_professional_body_id) {
        professionalBodyIds.push(body.pivot.other_professional_body_id)
      } else if (body.pivot?.custom_name) {
        professionalBodyIds.push('other')
      }
    })
  }
  if (professionalBodyIds.length === 0 && newMember.other_professional_body_id) {
    professionalBodyIds.push(newMember.other_professional_body_id)
  }
  
  Object.assign(formData, {
    registered_another_professional_body: normalizeBooleanValue(newMember.registered_another_professional_body),
    other_professional_body_ids: professionalBodyIds,
    registered_another_professional_body_specify: newMember.registered_another_professional_body_specify || '',
    removed_from_professional_body: normalizeBooleanValue(newMember.removed_from_professional_body),
    removed_from_professional_body_specify: newMember.removed_from_professional_body_specify || '',
    theft_forgery: normalizeBooleanValue(newMember.theft_forgery),
    theft_forgery_specify: newMember.theft_forgery_specify || '',
    unrehabilitated_insolvent: normalizeBooleanValue(newMember.unrehabilitated_insolvent),
    unrehabilitated_insolvent_specify: newMember.unrehabilitated_insolvent_specify || '',
    office_trust_misconduct: normalizeBooleanValue(newMember.office_trust_misconduct),
    office_trust_misconduct_specify: newMember.office_trust_misconduct_specify || '',
    professional_conduct_ciba: normalizeBooleanValue(newMember.professional_conduct_ciba),
    professional_conduct_ciba_specify: newMember.professional_conduct_ciba_specify || '',
    paying_application_fees: normalizeBooleanValue(newMember.paying_application_fees),
    paying_application_fees_specify: newMember.paying_application_fees_specify || '',
    currently_under_investigation: normalizeBooleanValue(newMember.currently_under_investigation),
    currently_under_investigation_specify: newMember.currently_under_investigation_specify || '',
    support_share_bind_ciba: normalizeBooleanValue(newMember.support_share_bind_ciba),
    support_share_bind_ciba_specify: newMember.support_share_bind_ciba_specify || ''
  })
}, { deep: true })

function getErrorMessage(field) {
  const error = errors.value[field]
  if (Array.isArray(error) && error.length > 0) {
    return error[0]
  }
  if (typeof error === 'string') {
    return error
  }
  return null
}

// Computed property for selected professional bodies display
const selectedProfessionalBodies = computed(() => {
  const selected = []
  formData.other_professional_body_ids.forEach(id => {
    if (id === 'other') {
      selected.push({ value: 'other', label: 'Other' })
    } else {
      const body = other_professional_bodies.value.find(b => b.id == id)
      if (body) {
        selected.push({ value: body.id, label: body.name })
      }
    }
  })
  return selected
})

// Toggle professional body selection
const toggleProfessionalBody = (value) => {
  const index = formData.other_professional_body_ids.indexOf(value)
  if (index > -1) {
    formData.other_professional_body_ids.splice(index, 1)
  } else {
    formData.other_professional_body_ids.push(value)
  }
}

// Remove professional body from selection
const removeProfessionalBody = (value) => {
  const index = formData.other_professional_body_ids.indexOf(value)
  if (index > -1) {
    formData.other_professional_body_ids.splice(index, 1)
  }
}

// Handle click outside to close dropdown
const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    isDropdownOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

async function submitForm() {
  if (submitting.value) return

  submitting.value = true
  successMessage.value = null
  errorMessage.value = null
  errors.value = {}

  // Ensure other_professional_body_ids is always an array with string values
  // Only include if registered_another_professional_body is 'yes'
  const professionalBodyIds = formData.registered_another_professional_body === 'yes'
    ? (Array.isArray(formData.other_professional_body_ids) 
        ? formData.other_professional_body_ids.map(id => String(id))
        : [])
    : []

  const submitData = {
    ...formData,
    registered_another_professional_body: formData.registered_another_professional_body || null,
    other_professional_body_ids: professionalBodyIds,
    removed_from_professional_body: formData.removed_from_professional_body || null,
    theft_forgery: formData.theft_forgery || null,
    unrehabilitated_insolvent: formData.unrehabilitated_insolvent || null,
    office_trust_misconduct: formData.office_trust_misconduct || null,
    professional_conduct_ciba: formData.professional_conduct_ciba || null,
    paying_application_fees: formData.paying_application_fees || null,
    currently_under_investigation: formData.currently_under_investigation || null,
    support_share_bind_ciba: formData.support_share_bind_ciba || null
  }

  try {
    // Use profile route if no applicationId, otherwise use application wizard route
    const routeName = props.applicationId 
      ? 'vue.member.application-wizard.update-conduct-history'
      : 'vue.member.profile.update-conduct-history'
    
    const routeParams = props.applicationId ? [props.applicationId] : []
    
    const response = await axios.put(
      route(routeName, ...routeParams),
      submitData
    )

    successMessage.value = response.data.message || 'Conduct history updated successfully'
    
    // Only emit completion-updated if we have an applicationId (for wizard context)
    if (props.applicationId && response.data.completion_percentage !== undefined) {
      emit('completion-updated', {
        field: 'conduct_history',
        percentage: response.data.completion_percentage
      })
    } else {
      // For standalone use, just emit success
    emit('completion-updated', {
      field: 'conduct_history',
      percentage: response.data.completion_percentage
    })
    }

    setTimeout(() => {
      successMessage.value = null
    }, 3000)
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    }
    errorMessage.value = error.response?.data?.message || 'Failed to update conduct history'
  } finally {
    submitting.value = false
  }
}
</script>

<style scoped>
.bg-ciba-green {
  background-color: #BAF504;
}
</style>

