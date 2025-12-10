<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useAuth } from '@/composables/useAuth';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import {
    ArrowUpRightFromSquare,
    Building,
    ChevronDown,
    Key,
    LayoutGrid,
    Settings,
    Shield,
    Users,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { route } from 'ziggy-js';
import AppLogo from './AppLogo.vue';
const { hasRole, hasPermission, can } = useAuth();

// Track expanded state for the Accounts group - closed by default
const isAccountsExpanded = ref(false);

// Check if current route is one of the account routes
const isAccountRouteActive = computed(() => {
    const currentRoute = route().current();
    return (
        currentRoute?.includes('admin.roles') ||
        currentRoute?.includes('admin.permissions') ||
        currentRoute?.includes('admin.users')
    );
});

// Watch for route changes and expand accounts if on account routes
watch(
    () => route().current(),
    (newRoute) => {
        if (
            newRoute?.includes('admin.roles') ||
            newRoute?.includes('admin.permissions') ||
            newRoute?.includes('admin.users')
        ) {
            isAccountsExpanded.value = true;
        }
    },
    { immediate: true },
);

// Check if accounts section should be shown
const showAccountsSection = computed(() => {
    return can({
        roles: ['super_admin', 'admin'],
        permissions: ['role', 'permission', 'user'],
    });
});

// Main navigation items with access control
const mainNavItems = computed(() => {
    const items: Array<NavItem & { routeName: string | string[] }> = [];

    // Dashboard - available to all authenticated users
    items.push({
        title: 'Dashboard',
        href: route('dashboard'),
        icon: LayoutGrid,
        routeName: 'dashboard',
    });

    // Notes - check role or permission
    if (
        can({
            roles: ['super_admin', 'admin', 'faculty', 'student'],
            permissions: ['notes_view', 'classes_view'],
        })
    ) {
        items.push({
            title: 'Notes',
            href: route('admin.notes.index'),
            icon: Users,
            routeName: 'admin.notes.index',
        });
        items.push({
            title: 'Class Rooms',
            href: route('admin.classes.index'),
            icon: Building,
            routeName: [
                'admin.classes.index',
                'admin.classes',
                'admin.assignments',
                'admin.submissions',
            ],
        });
    }

    // Routines - check role or permission
    if (
        can({
            roles: ['super_admin', 'admin', 'faculty', 'student'],
            permissions: ['routines_view', 'routines_manage'],
        })
    ) {
        items.push({
            title: 'My Routines',
            href: route('admin.myRoutines'),
            icon: LayoutGrid,
            routeName: ['admin.myRoutines'],
        });
    }

    // Routines - check role or permission
    if (
        can({
            roles: ['super_admin', 'admin'],
        })
    ) {
        items.push({
            title: 'Routines Management',
            href: route('admin.routines.index'),
            icon: LayoutGrid,
            routeName: ['admin.routines.index', 'admin.routines'],
        });
    }

    // API Documentation - for specific roles only
    if (hasRole(['super_admin', 'admin'])) {
        items.push({
            title: 'API Documentation',
            href: route('admin.api.index'),
            icon: ArrowUpRightFromSquare,
            routeName: 'admin.api.index',
        });
    }

    // Site Settings - for super_admin only or specific permission
    if (hasRole('super_admin') || hasPermission('site_settings')) {
        items.push({
            title: 'Site Settings',
            href: route('admin.settings.index'),
            icon: Settings,
            routeName: 'admin.settings.index',
        });
    }

    return items;
});

// Accounts navigation items with granular permission checks
const accountsNavItems = computed(() => {
    const items: Array<NavItem & { routeName: string | string[] }> = [];

    // Roles - check for role permission
    if (hasPermission('role')) {
        items.push({
            title: 'Roles',
            href: route('admin.roles.index'),
            icon: Shield,
            routeName: ['admin.roles.index', 'admin.roles'],
        });
    }

    // Permissions - check for permission permission
    if (hasPermission('permission')) {
        items.push({
            title: 'Permissions',
            href: route('admin.permissions.index'),
            icon: Key,
            routeName: ['admin.permissions.index', 'admin.permissions'],
        });
    }

    // Users - check for user permission
    if (hasPermission('user')) {
        items.push({
            title: 'Users',
            href: route('admin.users.index'),
            icon: Users,
            routeName: ['admin.users.index', 'admin.users'],
        });
    }

    return items;
});

const footerNavItems: NavItem[] = [
    {
        title: 'Main Site',
        href: '/',
        icon: ArrowUpRightFromSquare,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />

            <!-- Accounts Group with expand/collapse functionality -->
            <!-- Only show if user has access to any account item -->
            <div
                v-if="showAccountsSection && accountsNavItems.length > 0"
                class="px-2 py-1"
            >
                <button
                    @click="isAccountsExpanded = !isAccountsExpanded"
                    class="flex w-full items-center justify-between rounded-md p-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground"
                    :class="{
                        'bg-accent text-accent-foreground':
                            isAccountRouteActive,
                    }"
                >
                    <div class="flex items-center gap-2">
                        <Building class="h-4 w-4" />
                        <span>Accounts</span>
                    </div>
                    <ChevronDown
                        class="h-4 w-4 transition-transform duration-200"
                        :class="{ 'rotate-180': isAccountsExpanded }"
                    />
                </button>

                <!-- Child items - only show if expanded -->
                <div v-if="isAccountsExpanded" class="mt-1 ml-4 space-y-1">
                    <NavMain :items="accountsNavItems" />
                </div>
            </div>
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
