import { reactive, ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

export function useRegistrationForm() {
  // OTP State
  const otpModal = reactive({
    show: false,
    otp: '',
    verified: false,
    loading: false,
    error: null,
    expiryTime: null
  })

  // Validation State
  const validation = reactive({
    emailValid: null,
    mobileValid: null,
    idNumberValid: null,
    emailChecking: false,
    mobileChecking: false,
    idNumberChecking: false,
    emailMessage: '',
    mobileMessage: '',
    idNumberMessage: ''
  })

  // Form processing state
  const processing = ref(false)
  const errors = ref({})

  /**
   * Normalize Laravel validation errors
   * Converts arrays to strings and handles translation keys
   */
  function normalizeErrors(responseErrors) {
    if (!responseErrors || typeof responseErrors !== 'object') {
      return {}
    }

    const normalized = {}

    // Translation key mappings for common Laravel validation messages
    const translationMap = {
      'validation.required': 'This field is required.',
      'validation.unique': 'This value has already been taken.',
      'validation.email': 'Please enter a valid email address.',
      'validation.min.string': (field, min) => `This field must be at least ${min} characters.`,
      'validation.confirmed': 'The confirmation does not match.',
      'validation.accepted': 'You must accept this field.',
      'validation.numeric': 'This field must be a number.',
      'validation.digits': (field, digits) => `This field must be ${digits} digits.`,
      'validation.date': 'Please enter a valid date.',
      'validation.url': 'Please enter a valid URL.',
      'validation.array': 'This field must be an array.',
      'validation.file': 'Please upload a valid file.',
      'validation.max.file': (field, max) => `File size must not exceed ${max}KB.`,
    }

    Object.keys(responseErrors).forEach(key => {
      const errorValue = responseErrors[key]

      // If it's an array, get the first message
      if (Array.isArray(errorValue) && errorValue.length > 0) {
        let message = errorValue[0]

        // Check if it's a translation key
        if (typeof message === 'string' && message.startsWith('validation.')) {
          // Try to find a translation mapping
          if (translationMap[message]) {
            if (typeof translationMap[message] === 'function') {
              // Handle parameterized translations (like min.string)
              message = translationMap[message](key)
            } else {
              message = translationMap[message]
            }
          } else {
            // If no mapping found, try to extract a readable message from the array
            // Look for a non-translation-key message
            const readableMessage = errorValue.find(msg => 
              typeof msg === 'string' && !msg.startsWith('validation.')
            )
            if (readableMessage) {
              message = readableMessage
            } else {
              // Fallback: convert translation key to readable text
              message = message.replace('validation.', '').replace(/\./g, ' ').replace(/\b\w/g, l => l.toUpperCase())
            }
          }
        }

        normalized[key] = message
      } else if (typeof errorValue === 'string') {
        // Already a string, use it directly
        normalized[key] = errorValue
      } else if (errorValue) {
        // Other types, convert to string
        normalized[key] = String(errorValue)
      }
    })

    return normalized
  }

  /**
   * Validate SA ID number and extract date of birth
   */
  function validateSAID(idNumber) {
    if (!idNumber || idNumber.length !== 13) {
      return { valid: false, error: 'ID number must be 13 digits', dateOfBirth: null }
    }

    // Extract date components
    const dateString = idNumber.substring(0, 6)
    const year = dateString.substring(0, 2)
    const month = dateString.substring(2, 4)
    const day = dateString.substring(4, 6)

    // Determine century
    const fullYear = (parseInt(year) <= 29 ? '20' : '19') + year

    // Validate date
    const dateOfBirth = `${fullYear}-${month}-${day}`
    const date = new Date(dateOfBirth)
    
    if (isNaN(date.getTime())) {
      return { valid: false, error: 'Invalid date in ID number', dateOfBirth: null }
    }

    // Validate month and day
    if (parseInt(month) < 1 || parseInt(month) > 12) {
      return { valid: false, error: 'Invalid month in ID number', dateOfBirth: null }
    }
    if (parseInt(day) < 1 || parseInt(day) > 31) {
      return { valid: false, error: 'Invalid day in ID number', dateOfBirth: null }
    }

    // Luhn algorithm checksum validation
    const digits = idNumber.split('').map(Number)
    let sum = 0
    
    for (let i = 0; i < 12; i++) {
      if (i % 2 === 0) {
        sum += digits[i]
      } else {
        const doubled = digits[i] * 2
        sum += doubled > 9 ? doubled - 9 : doubled
      }
    }
    
    const checkDigit = (10 - (sum % 10)) % 10
    
    if (checkDigit !== digits[12]) {
      return { valid: false, error: 'Invalid ID number checksum', dateOfBirth: null }
    }

    return { valid: true, error: null, dateOfBirth }
  }

  /**
   * Check if email is unique
   */
  async function checkEmailUniqueness(email) {
    const trimmed = email?.trim() ?? ''

    validation.emailMessage = ''
    validation.emailValid = null

    if (!trimmed) {
      validation.emailValid = false
      validation.emailMessage = 'Email address is required.'
      return { valid: false, message: validation.emailMessage }
    }

    validation.emailChecking = true
    
    try {
      const response = await axios.post('/api/member/validate-email', { email: trimmed })
      validation.emailValid = response.data.valid
      validation.emailMessage = validation.emailValid ? '' : (response.data.message ?? '')

      return {
        valid: validation.emailValid,
        message: validation.emailMessage
      }
    } catch (error) {
      console.error('Email check failed:', error)

      let message = 'Unable to validate email address. Please try again.'

      if (error.response?.status === 422) {
        message = error.response?.data?.errors?.email?.[0] ?? message
      } else if (error.response?.data?.message) {
        message = error.response.data.message
      }

      validation.emailValid = false
      validation.emailMessage = message

      return {
        valid: false,
        message
      }
    } finally {
      validation.emailChecking = false
    }
  }

  /**
   * Check if mobile number is unique
   */
  async function checkMobileUniqueness(mobile) {
    console.log('📞 checkMobileUniqueness() called with:', mobile)
    
    const normalized = mobile?.trim() ?? ''

    validation.mobileMessage = ''
    validation.mobileValid = null

    if (!normalized) {
      console.log('  ❌ Mobile number empty')
      validation.mobileValid = false
      validation.mobileMessage = 'Mobile number is required.'
      return { valid: false, message: validation.mobileMessage }
    }

    if (normalized.length < 10) {
      console.log('  ❌ Mobile number too short')
      validation.mobileValid = false
      validation.mobileMessage = 'Please enter at least 10 digits.'
      return { valid: false, message: validation.mobileMessage }
    }

    console.log('  📡 Making API request to /api/member/validate-mobile')
    validation.mobileChecking = true
    
    try {
      const response = await axios.post('/api/member/validate-mobile', { mobile_number: normalized })
      console.log('  ✅ API response:', response.data)
      
      validation.mobileValid = response.data.valid
      validation.mobileMessage = validation.mobileValid ? '' : (response.data.message ?? '')
      
      return {
        valid: validation.mobileValid,
        message: validation.mobileMessage
      }
    } catch (error) {
      console.error('  ❌ Mobile check failed:', error)
      console.error('  - Error response:', error.response?.data)
      console.error('  - Error status:', error.response?.status)
      
      let message = 'Unable to validate mobile number. Please try again.'

      if (error.response?.status === 422) {
        message = error.response?.data?.errors?.mobile_number?.[0] ?? message
      } else if (error.response?.data?.message) {
        message = error.response.data.message
      }

      validation.mobileValid = false
      validation.mobileMessage = message
      
      return {
        valid: false,
        message
      }
    } finally {
      validation.mobileChecking = false
    }
  }

  /**
   * Validate ID number against API
   */
  async function validateIdentityNumber(identityNumber) {
    const trimmed = identityNumber?.trim() ?? ''

    validation.idNumberMessage = ''
    validation.idNumberValid = null

    if (!trimmed) {
      validation.idNumberValid = false
      validation.idNumberMessage = 'ID number is required.'
      return { valid: false, message: validation.idNumberMessage, dateOfBirth: null }
    }

    validation.idNumberChecking = true

    try {
      const response = await axios.post('/api/member/validate-id-number', {
        identity_number: trimmed
      })

      const { valid, message, date_of_birth: dateOfBirth } = response.data

      validation.idNumberValid = valid
      validation.idNumberMessage = valid ? '' : (message ?? '')

      return {
        valid,
        message: validation.idNumberMessage,
        dateOfBirth: dateOfBirth ?? null
      }
    } catch (error) {
      let message = 'Unable to validate ID number. Please try again.'

      if (error.response?.status === 422) {
        message = error.response?.data?.errors?.identity_number?.[0] ?? message
      } else if (error.response?.data?.message) {
        message = error.response.data.message
      }

      validation.idNumberValid = false
      validation.idNumberMessage = message

      return {
        valid: false,
        message,
        dateOfBirth: null
      }
    } finally {
      validation.idNumberChecking = false
    }
  }

  /**
   * Send OTP to mobile number
   */
  async function sendOTP(mobileNumber) {
    // Check if SA or Namibia number
    const isSAOrNamibia = mobileNumber.includes('+27') || mobileNumber.includes('+264')
    
    if (!isSAOrNamibia) {
      // Auto-verify for non-SA/Namibia numbers
      otpModal.verified = true
      return { success: true, message: 'Mobile number accepted (OTP not required for international numbers)' }
    }

    // Test number bypass
    if (mobileNumber === '+2779490103') {
      otpModal.show = true
      otpModal.otp = '1234'
      // Set expiry time to 10 minutes from now for test number
      const expiryDate = new Date()
      expiryDate.setMinutes(expiryDate.getMinutes() + 10)
      otpModal.expiryTime = expiryDate
      return { success: true, message: 'Test number - use OTP: 1234' }
    }

    otpModal.loading = true
    otpModal.error = null

    try {
      const response = await axios.post('/api/member/send-otp', { 
        mobile_number: mobileNumber 
      })
      
      otpModal.loading = false
      
      if (response.data.success) {
        otpModal.show = true
        // Use expiry_date from response if available, otherwise calculate 10 minutes from now
        if (response.data.expiry_date) {
          otpModal.expiryTime = new Date(response.data.expiry_date)
        } else {
          const expiryDate = new Date()
          expiryDate.setMinutes(expiryDate.getMinutes() + 10)
          otpModal.expiryTime = expiryDate
        }
        return { success: true, message: 'OTP sent successfully' }
      } else {
        otpModal.error = response.data.message || 'Failed to send OTP'
        return { success: false, message: otpModal.error }
      }
    } catch (error) {
      otpModal.loading = false
      
      if (error.response?.status === 429) {
        otpModal.error = 'Too many requests. Please wait before requesting another OTP.'
      } else if (error.response?.data?.message) {
        otpModal.error = error.response.data.message
      } else {
        otpModal.error = 'Failed to send OTP. Please try again.'
      }
      
      return { success: false, message: otpModal.error }
    }
  }

  /**
   * Auto-validate and send OTP (debounced)
   * This function is called automatically after user stops typing for 400ms
   */
  async function autoValidateAndSendOTP(mobileNumber) {
    if (!mobileNumber || mobileNumber.length < 12) {
      return
    }

    // Check if already verified
    if (otpModal.verified) {
      return
    }

    // Check if already showing modal (don't re-send)
    if (otpModal.show) {
      return
    }

    // Validate mobile number uniqueness first
    const { valid, message } = await checkMobileUniqueness(mobileNumber)

    if (!valid) {
      // Don't send OTP if validation fails
      return
    }

    // Check if requires OTP (SA/Namibia)
    if (!requiresOTP(mobileNumber)) {
      // Auto-verify for non-SA/Namibia numbers
      otpModal.verified = true
      return
    }

    // Auto-send OTP
    await sendOTP(mobileNumber)
  }

  /**
   * Verify OTP code
   */
  async function verifyOTP(mobileNumber, otpCode) {
    otpModal.loading = true
    otpModal.error = null

    try {
      const response = await axios.post('/api/member/verify-otp', {
        mobile_number: mobileNumber,
        otp: otpCode
      })

      otpModal.loading = false

      if (response.data.valid) {
        otpModal.verified = true
        otpModal.show = false
        otpModal.otp = ''
        otpModal.error = null
        otpModal.expiryTime = null
        return { success: true, message: response.data.message || 'Mobile number verified successfully' }
      } else {
        otpModal.error = response.data.message || 'Invalid OTP. Please try again.'
        return { success: false, message: otpModal.error }
      }
    } catch (error) {
      otpModal.loading = false
      otpModal.error = error.response?.data?.message || 'OTP verification failed'
      return { success: false, message: otpModal.error }
    }
  }

  /**
   * Close OTP modal
   */
  function closeOTPModal() {
    otpModal.show = false
    otpModal.otp = ''
    otpModal.error = null
    otpModal.expiryTime = null
  }

  /**
   * Reset OTP state
   */
  function resetOTP() {
    otpModal.show = false
    otpModal.otp = ''
    otpModal.verified = false
    otpModal.loading = false
    otpModal.error = null
    otpModal.expiryTime = null
  }

  /**
   * Check OTP status for a mobile number
   */
  async function checkOTPStatus(mobileNumber) {
    if (!mobileNumber || mobileNumber.length < 12) {
      return
    }

    // Check if SA or Namibia number
    const isSAOrNamibia = mobileNumber.includes('+27') || mobileNumber.includes('+264')
    if (!isSAOrNamibia) {
      return
    }

    try {
      const response = await axios.post('/api/member/check-otp-status', {
        mobile_number: mobileNumber
      })

      if (response.data.has_otp && !response.data.expired) {
        // OTP exists and hasn't expired, show modal with countdown
        otpModal.show = true
        if (response.data.expiry_date) {
          otpModal.expiryTime = new Date(response.data.expiry_date)
        } else if (response.data.expires_in) {
          // Fallback: calculate from expires_in
          const expiryDate = new Date()
          expiryDate.setSeconds(expiryDate.getSeconds() + response.data.expires_in)
          otpModal.expiryTime = expiryDate
        } else {
          // Default: 10 minutes from now
          const expiryDate = new Date()
          expiryDate.setMinutes(expiryDate.getMinutes() + 10)
          otpModal.expiryTime = expiryDate
        }
      }
    } catch (error) {
      // Silently fail - don't show error to user
      console.error('Error checking OTP status:', error)
    }
  }

  /**
   * Submit form with FormData for file uploads
   */
  function submitForm(routeName, formData, options = {}) {
    // Convert reactive object to FormData
    const data = new FormData()
    
    Object.keys(formData).forEach(key => {
      const value = formData[key]
      
      // Skip null/undefined values unless it's a required field
      if (value === null || value === undefined) {
        return
      }
      
      // Handle files
      if (value instanceof File) {
        data.append(key, value)
      }
      // Handle arrays
      else if (Array.isArray(value)) {
        if (value.length === 0) {
          // Empty array - skip it (Laravel will treat as nullable)
          return
        } else if (value.length > 0 && value[0] instanceof File) {
          // Array of files
          value.forEach(file => data.append(`${key}[]`, file))
        } else if (value.length > 0 && typeof value[0] === 'object' && value[0] !== null) {
          // Array of objects - send in FormData format that Laravel can parse
          value.forEach((item, index) => {
            Object.keys(item).forEach(subKey => {
              data.append(`${key}[${index}][${subKey}]`, item[subKey] || '')
            })
          })
        } else {
          // Array of primitive values
          value.forEach((item, index) => {
            data.append(`${key}[${index}]`, item)
          })
        }
      }
      // Handle booleans
      else if (typeof value === 'boolean') {
        data.append(key, value ? '1' : '0')
      }
      // Handle other values
      else {
        data.append(key, value)
      }
    })

    processing.value = true
    errors.value = {}

    router.post(routeName, data, {
      preserveScroll: true,
      onSuccess: () => {
        processing.value = false
        if (options.onSuccess) {
          options.onSuccess()
        }
      },
      onError: (responseErrors) => {
        processing.value = false
        // Normalize errors before assigning
        errors.value = normalizeErrors(responseErrors)
        
        if (options.onError) {
          options.onError(errors.value)
        }
        
        // Scroll to first error
        setTimeout(() => {
          const firstError = document.querySelector('.text-red-600, .text-red-500')
          if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' })
          }
        }, 100)
      },
      onFinish: () => {
        processing.value = false
        if (options.onFinish) {
          options.onFinish()
        }
      }
    })
  }

  /**
   * Check if mobile number requires OTP
   */
  function requiresOTP(mobileNumber) {
    if (!mobileNumber) return false
    return mobileNumber.includes('+27') || mobileNumber.includes('+264')
  }

  /**
   * Format file size for display
   */
  function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes'
    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
  }

  return {
    // State
    otpModal,
    validation,
    processing,
    errors,
    
    // Methods
    validateSAID,
    checkEmailUniqueness,
    checkMobileUniqueness,
    validateIdentityNumber,
    sendOTP,
    verifyOTP,
    autoValidateAndSendOTP,
    closeOTPModal,
    resetOTP,
    checkOTPStatus,
    submitForm,
    requiresOTP,
    formatFileSize,
    normalizeErrors
  }
}

