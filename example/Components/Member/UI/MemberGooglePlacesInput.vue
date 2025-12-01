<template>
  <div class="space-y-1">
    <label v-if="label" class="block text-sm font-medium text-gray-700">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    <div class="relative">
      <input
        ref="autocompleteInput"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        type="text"
        :placeholder="placeholder"
        :required="required"
        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
        :class="{ 'border-yellow-400': apiError }"
      />
      <div v-if="apiError" class="absolute right-2 top-2">
        <svg class="h-5 w-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
        </svg>
      </div>
    </div>
    <p v-if="apiError" class="text-yellow-600 text-xs mt-1">
      ⚠️ Google Places API not enabled. Please enable it in Google Cloud Console or enter address manually.
    </p>
    <p v-if="error" class="text-red-500 text-xs mt-1">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Start typing an address...'
  },
  required: {
    type: Boolean,
    default: false
  },
  error: {
    type: String,
    default: ''
  },
  addressFields: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['update:modelValue', 'place-selected'])

const page = usePage()
const autocompleteInput = ref(null)
const apiError = ref(false)
let autocomplete = null

onMounted(() => {
  // Set Google API key from Inertia props to window for backward compatibility
  if (page.props.googleApiKey) {
    window.GOOGLE_API_KEY = page.props.googleApiKey
  }
  
  loadGooglePlaces()
})

function loadGooglePlaces() {
  // Get API key from Inertia props or window (fallback)
  const apiKey = page.props.googleApiKey || window.GOOGLE_API_KEY
  
  if (!apiKey) {
    console.error('Google Maps API key is not configured. Please set GOOGLE_API_KEY in your .env file.')
    apiError.value = true
    return
  }

  // Check if Google Places API is already loaded
  if (window.google && window.google.maps && window.google.maps.places) {
    initAutocomplete()
    return
  }

  // If script is loading, wait for it
  if (window.googleMapsLoading) {
    window.googleMapsLoading.then(() => {
      initAutocomplete()
    }).catch(() => {
      apiError.value = true
    })
    return
  }

  // Load the Google Places API script
  if (!document.getElementById('google-places-script')) {
    window.googleMapsLoading = new Promise((resolve, reject) => {
      const script = document.createElement('script')
      script.id = 'google-places-script'
      script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places&callback=initGoogleMaps`
      script.async = true
      script.defer = true
      
      window.initGoogleMaps = () => {
        delete window.initGoogleMaps
        resolve()
      }
      
      script.onerror = () => {
        console.error('Failed to load Google Places API. Please check your API key and ensure Places API is enabled.')
        apiError.value = true
        reject()
      }
      
      document.head.appendChild(script)
    })

    window.googleMapsLoading.then(() => {
      initAutocomplete()
    }).catch(() => {
      apiError.value = true
    })
  }
}

function initAutocomplete() {
  if (!autocompleteInput.value) return

  try {
    autocomplete = new google.maps.places.Autocomplete(autocompleteInput.value, {
      types: ['address'],
      componentRestrictions: { country: 'za' } // Restrict to South Africa
    })

    autocomplete.addListener('place_changed', handlePlaceSelect)
    apiError.value = false
  } catch (error) {
    console.error('Google Places Autocomplete initialization failed:', error)
    apiError.value = true
    // Still allow manual input even if autocomplete fails
  }
}

function handlePlaceSelect() {
  const place = autocomplete.getPlace()
  
  if (!place.address_components) {
    return
  }

  // Parse address components
  const addressData = {
    formatted_address: place.formatted_address || '',
    street_number: '',
    route: '',
    suburb: '',
    city: '',
    state: '',
    postal_code: '',
    country: ''
  }

  place.address_components.forEach(component => {
    const types = component.types
    
    if (types.includes('street_number')) {
      addressData.street_number = component.long_name
    }
    if (types.includes('route')) {
      addressData.route = component.long_name
    }
    if (types.includes('sublocality') || types.includes('sublocality_level_1')) {
      addressData.suburb = component.long_name
    }
    if (types.includes('locality')) {
      addressData.city = component.long_name
    }
    if (types.includes('administrative_area_level_1')) {
      addressData.state = component.long_name
    }
    if (types.includes('postal_code')) {
      addressData.postal_code = component.long_name
    }
    if (types.includes('country')) {
      addressData.country = component.long_name
    }
  })

  // Combine street number and route for full address
  addressData.street_address = [addressData.street_number, addressData.route]
    .filter(Boolean)
    .join(' ')

  // Update the input value
  emit('update:modelValue', addressData.street_address)
  
  // Emit the parsed address data
  emit('place-selected', addressData)
}
</script>

