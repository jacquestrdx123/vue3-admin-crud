import { computed } from 'vue'

/**
 * Composable for handling conditional field visibility (showWhen/hideWhen)
 * @param {Object} formData - The reactive form data object
 * @param {Object} field - The field configuration object
 * @returns {Object} - Computed visibility state
 */
export function useFieldVisibility(formData, field) {
    const isVisible = computed(() => {
        // Handle show_when condition
        if (field.show_when) {
            const { field: targetField, operator, value } = field.show_when
            return evaluateCondition(formData[targetField], operator, value)
        }

        // Handle hide_when condition
        if (field.hide_when) {
            const { field: targetField, operator, value } = field.hide_when
            return !evaluateCondition(formData[targetField], operator, value)
        }

        // Handle show_when_conditions (AND/OR logic)
        if (field.show_when_conditions && field.show_when_conditions.conditions) {
            const { type, conditions } = field.show_when_conditions
            const results = conditions.map(cond => 
                evaluateCondition(formData[cond.field], cond.operator, cond.value)
            )
            return type === 'AND' ? results.every(r => r) : results.some(r => r)
        }

        // Handle hide_when_conditions (AND/OR logic)
        if (field.hide_when_conditions && field.hide_when_conditions.conditions) {
            const { type, conditions } = field.hide_when_conditions
            const results = conditions.map(cond => 
                evaluateCondition(formData[cond.field], cond.operator, cond.value)
            )
            const shouldHide = type === 'AND' ? results.every(r => r) : results.some(r => r)
            return !shouldHide
        }

        // Default: visible
        return true
    })

    return { isVisible }
}

/**
 * Evaluate a single condition
 * @param {*} fieldValue - The current value of the field
 * @param {string} operator - The comparison operator
 * @param {*} targetValue - The value to compare against
 * @returns {boolean}
 */
function evaluateCondition(fieldValue, operator, targetValue) {
    switch (operator) {
        case 'equals':
        case '==':
        case '=':
            return fieldValue == targetValue
        case 'not_equals':
        case '!=':
        case '<>':
            return fieldValue != targetValue
        case 'greater_than':
        case '>':
            return fieldValue > targetValue
        case 'less_than':
        case '<':
            return fieldValue < targetValue
        case 'greater_than_or_equal':
        case '>=':
            return fieldValue >= targetValue
        case 'less_than_or_equal':
        case '<=':
            return fieldValue <= targetValue
        default:
            return true
    }
}

