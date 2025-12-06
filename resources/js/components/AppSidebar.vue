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
import { dashboard } from '@/routes';
import { index as apiIndex } from '@/routes/admin/api';
import { index as notesIndex } from '@/routes/admin/notes';
import permissions from '@/routes/admin/permissions';
import roles from '@/routes/admin/roles';
import { index as routineIndex } from '@/routes/admin/routines';
import { index as settingsIndex } from '@/routes/admin/settings';
import users from '@/routes/admin/users';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
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
import AppLogo from './AppLogo.vue';

// Track expanded state for the Accounts group - closed by default
const isAccountsExpanded = ref(false);

const page = usePage();

// Check if current route is one of the account routes
const isAccountRouteActive = computed(() => {
    const currentUrl = page.url;
    return (
        currentUrl.includes('/admin-dashboard/roles') ||
        currentUrl.includes('/admin-dashboard/permissions') ||
        currentUrl.includes('/admin-dashboard/users')
    );
});

// Watch for route changes and expand accounts if on account routes
watch(
    () => page.url,
    (newUrl) => {
        if (
            newUrl.includes('/admin-dashboard/roles') ||
            newUrl.includes('/admin-dashboard/permissions') ||
            newUrl.includes('/admin-dashboard/users')
        ) {
            isAccountsExpanded.value = true;
        }
    },
    { immediate: true },
);

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Notes',
        href: notesIndex(),
        icon: Users,
    },
    {
        title: 'Routines',
        href: routineIndex(),
        icon: LayoutGrid,
    },
    {
        title: 'API Documentation',
        href: apiIndex(),
        icon: ArrowUpRightFromSquare,
    },
    {
        title: 'Site Settings',
        href: settingsIndex(),
        icon: Settings,
    },
];

const accountsNavItems: NavItem[] = [
    {
        title: 'Roles',
        href: roles.index(),
        icon: Shield,
    },
    {
        title: 'Permissions',
        href: permissions.index(),
        icon: Key,
    },
    {
        title: 'Users',
        href: users.index(),
        icon: Users,
    },
];

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
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />

            <!-- Accounts Group with expand/collapse functionality -->
            <div class="px-2 py-1">
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

                <!-- Child items -->
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
