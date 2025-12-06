// composables/useAuth.ts
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export const useAuth = () => {
    const page = usePage();
    
    const user = computed(() => page.props.auth?.user);
    const roles = computed(() => user.value?.roles || []);
    const permissions = computed(() => user.value?.permissions || []);
    
    const hasRole = (roleName: string | string[]) => {
        if (!user.value) return false;
        
        if (Array.isArray(roleName)) {
            return roleName.some(role => roles.value.includes(role));
        }
        return roles.value.includes(roleName);
    };
    
    const hasPermission = (permissionName: string | string[]) => {
        if (!user.value) return false;
        
        if (Array.isArray(permissionName)) {
            return permissionName.some(permission => permissions.value.includes(permission));
        }
        return permissions.value.includes(permissionName);
    };
    
    const can = (options: {
        roles?: string | string[];
        permissions?: string | string[];
    }) => {
        if (!user.value) return false;
        
        // Check roles first
        if (options.roles && hasRole(options.roles)) {
            return true;
        }
        
        // Check permissions
        if (options.permissions && hasPermission(options.permissions)) {
            return true;
        }
        
        return false;
    };
    
    return {
        user,
        roles,
        permissions,
        hasRole,
        hasPermission,
        can,
    };
};