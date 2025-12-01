import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

/**
 * Composable for checking user permissions
 * @returns {Object} - Permission checking methods
 */
export function usePermissions() {
    const page = usePage()

    const permissions = computed(() => {
        return page.props.auth.user?.permissions || []
    })

    const roles = computed(() => {
        return page.props.auth.user?.roles || []
    })

    const hasPermission = (permission) => {
        if (!permission) return true
        
        if (Array.isArray(permission)) {
            return permission.some(p => permissions.value.includes(p))
        }
        
        return permissions.value.includes(permission)
    }

    const hasAnyPermission = (permissionList) => {
        return permissionList.some(p => hasPermission(p))
    }

    const hasAllPermissions = (permissionList) => {
        return permissionList.every(p => hasPermission(p))
    }

    const hasRole = (role) => {
        if (!role) return true
        
        if (Array.isArray(role)) {
            return role.some(r => roles.value.includes(r))
        }
        
        return roles.value.includes(role)
    }

    const hasAnyRole = (roleList) => {
        return roleList.some(r => hasRole(r))
    }

    const hasAllRoles = (roleList) => {
        return roleList.every(r => hasRole(r))
    }

    const can = (permission) => hasPermission(permission)
    const cannot = (permission) => !hasPermission(permission)

    return {
        permissions,
        roles,
        hasPermission,
        hasAnyPermission,
        hasAllPermissions,
        hasRole,
        hasAnyRole,
        hasAllRoles,
        can,
        cannot
    }
}

