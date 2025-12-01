import { ref, computed } from 'vue'

/**
 * Composable for client-side form validation
 * @param {Object} rules - Validation rules object
 * @returns {Object} - Validation methods and state
 */
export function useFormValidation(rules = {}) {
    const errors = ref({})
    const touched = ref({})

    const hasErrors = computed(() => {
        return Object.keys(errors.value).length > 0
    })

    const validateField = (field, value) => {
        const fieldRules = rules[field]
        if (!fieldRules) return true

        const fieldErrors = []

        fieldRules.forEach(rule => {
            if (typeof rule === 'string') {
                const error = validateRule(rule, value, field)
                if (error) fieldErrors.push(error)
            } else if (typeof rule === 'function') {
                const result = rule(value)
                if (result !== true) fieldErrors.push(result)
            }
        })

        if (fieldErrors.length > 0) {
            errors.value[field] = fieldErrors
            return false
        } else {
            delete errors.value[field]
            return true
        }
    }

    const validateRule = (rule, value, field) => {
        // Required
        if (rule === 'required') {
            if (value === null || value === undefined || value === '') {
                return `${formatFieldName(field)} is required`
            }
        }

        // Email
        if (rule === 'email') {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
            if (value && !emailRegex.test(value)) {
                return `${formatFieldName(field)} must be a valid email`
            }
        }

        // Min length
        if (rule.startsWith('min:')) {
            const min = parseInt(rule.split(':')[1])
            if (value && value.length < min) {
                return `${formatFieldName(field)} must be at least ${min} characters`
            }
        }

        // Max length
        if (rule.startsWith('max:')) {
            const max = parseInt(rule.split(':')[1])
            if (value && value.length > max) {
                return `${formatFieldName(field)} must be less than ${max} characters`
            }
        }

        // Numeric
        if (rule === 'numeric') {
            if (value && isNaN(value)) {
                return `${formatFieldName(field)} must be a number`
            }
        }

        // Min value
        if (rule.startsWith('min_value:')) {
            const min = parseFloat(rule.split(':')[1])
            if (value && parseFloat(value) < min) {
                return `${formatFieldName(field)} must be at least ${min}`
            }
        }

        // Max value
        if (rule.startsWith('max_value:')) {
            const max = parseFloat(rule.split(':')[1])
            if (value && parseFloat(value) > max) {
                return `${formatFieldName(field)} must be at most ${max}`
            }
        }

        // URL
        if (rule === 'url') {
            const urlRegex = /^https?:\/\/.+/
            if (value && !urlRegex.test(value)) {
                return `${formatFieldName(field)} must be a valid URL`
            }
        }

        // Confirmed (for password confirmation)
        if (rule.startsWith('confirmed:')) {
            const confirmField = rule.split(':')[1]
            // This would need access to other form values
        }

        return null
    }

    const validateAll = (formData) => {
        let isValid = true

        Object.keys(rules).forEach(field => {
            const fieldValid = validateField(field, formData[field])
            if (!fieldValid) isValid = false
        })

        return isValid
    }

    const touchField = (field) => {
        touched.value[field] = true
    }

    const clearErrors = (field = null) => {
        if (field) {
            delete errors.value[field]
        } else {
            errors.value = {}
        }
    }

    const setErrors = (newErrors) => {
        errors.value = { ...newErrors }
    }

    const getFieldError = (field) => {
        return errors.value[field]?.[0] || null
    }

    const formatFieldName = (field) => {
        return field
            .split('_')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ')
    }

    return {
        errors,
        touched,
        hasErrors,
        validateField,
        validateAll,
        touchField,
        clearErrors,
        setErrors,
        getFieldError
    }
}

