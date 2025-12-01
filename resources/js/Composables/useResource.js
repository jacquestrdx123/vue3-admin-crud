import { router } from '@inertiajs/vue3'
import { ref } from 'vue'

/**
 * Composable for common resource operations
 * @param {string} resourceName - The resource name for routing
 * @returns {Object} - Resource operation methods
 */
export function useResource(resourceName) {
    const loading = ref(false)

    const visitIndex = () => {
        router.visit(route(`vue.${resourceName}.index`))
    }

    const visitCreate = () => {
        router.visit(route(`vue.${resourceName}.create`))
    }

    const visitShow = (id) => {
        router.visit(route(`vue.${resourceName}.show`, id))
    }

    const visitEdit = (id) => {
        router.visit(route(`vue.${resourceName}.edit`, id))
    }

    const destroy = (id, confirmMessage = 'Are you sure?') => {
        if (confirm(confirmMessage)) {
            router.delete(route(`vue.${resourceName}.destroy`, id), {
                preserveScroll: true,
                onStart: () => { loading.value = true },
                onFinish: () => { loading.value = false }
            })
        }
    }

    const bulkAction = (action, ids, confirmMessage = null) => {
        if (confirmMessage && !confirm(confirmMessage)) {
            return
        }

        router.post(route(`vue.${resourceName}.bulk-action`), {
            action,
            ids
        }, {
            preserveScroll: true,
            onStart: () => { loading.value = true },
            onFinish: () => { loading.value = false }
        })
    }

    const applySort = (column, direction) => {
        router.get(route(`vue.${resourceName}.index`), {
            sort_column: column,
            sort_direction: direction
        }, {
            preserveState: true,
            preserveScroll: true
        })
    }

    const applyFilters = (filters) => {
        router.get(route(`vue.${resourceName}.index`), filters, {
            preserveState: true,
            preserveScroll: true
        })
    }

    return {
        loading,
        visitIndex,
        visitCreate,
        visitShow,
        visitEdit,
        destroy,
        bulkAction,
        applySort,
        applyFilters
    }
}

