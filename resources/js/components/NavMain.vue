<!-- components/NavMain.vue -->
<script setup lang="ts">
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

interface NavItemWithRoute extends NavItem {
    routeName?: string | string[];
}

defineProps<{
    items: NavItemWithRoute[];
}>();

// Helper to check if route is active
const isRouteActive = (routeName?: string | string[]) => {
    if (!routeName) return false;

    const currentRoute = route().current();
    if (!currentRoute) return false;

    if (Array.isArray(routeName)) {
        return routeName.some(
            (name) =>
                currentRoute === name || currentRoute.startsWith(name + '.'),
        );
    }

    return (
        currentRoute === routeName || currentRoute.startsWith(routeName + '.')
    );
};
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton
                    as-child
                    :is-active="isRouteActive(item.routeName)"
                    :tooltip="item.title"
                >
                    <Link :href="item.href">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
