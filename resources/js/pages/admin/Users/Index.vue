<script setup lang="ts">
import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { create, destroy, edit, index, show } from '@/routes/admin/users';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Edit, Eye, Trash, Circle, Clock, Wifi, WifiOff, User, UserCheck, UserX } from 'lucide-vue-next';

// ✅ Receive users from Laravel (Inertia props)
const props = defineProps<{
    users: {
        id: number;
        name: string;
        email: string;
        status: 'active' | 'blocked';
        is_online: boolean;
        online_status: string;
        last_seen_text: string;
        last_seen_detailed: {
            text: string;
            icon: string;
            color: string;
        };
    }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Users', href: index().url },
];

// ✅ Access users via props
const users = props.users;

// ✅ Calculate stats
const totalUsers = users.length;
const onlineUsers = users.filter(u => u.is_online).length;
const activeUsers = users.filter(u => u.status === 'active').length;
const blockedUsers = users.filter(u => u.status === 'blocked').length;

// ✅ Status badge classes
function getStatusClasses(status: string) {
    return {
        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': status === 'active',
        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': status === 'blocked',
    };
}

// ✅ Color classes for online status
function getColorClasses(color: string) {
    const colorMap = {
        green: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        amber: 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200',
        gray: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
        red: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    };
    return colorMap[color as keyof typeof colorMap] || colorMap.gray;
}

// ✅ Get appropriate icon for status
function getStatusIcon(icon: string) {
    const icons = {
        online: Circle,
        recent: Circle,
        away: Clock,
        offline: WifiOff,
        never: WifiOff,
    };
    return icons[icon as keyof typeof icons] || Circle;
}
</script>

<template>
    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-4 rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">
                    User Management
                </h2>

                <Link
                    :href="create()"
                    class="rounded-md bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600 focus:ring-2 focus:ring-amber-400 focus:outline-none"
                >
                    + Add User
                </Link>
            </div>

            <!-- Stats Summary -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Users</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ totalUsers }}</p>
                        </div>
                        <div class="p-2 bg-amber-100 dark:bg-amber-900 rounded-lg">
                            <User class="h-6 w-6 text-amber-600 dark:text-amber-400" />
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Online Now</p>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                                {{ onlineUsers }}
                            </p>
                        </div>
                        <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                            <Wifi class="h-6 w-6 text-green-600 dark:text-green-400" />
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Users</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                {{ activeUsers }}
                            </p>
                        </div>
                        <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                            <UserCheck class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Blocked</p>
                            <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                                {{ blockedUsers }}
                            </p>
                        </div>
                        <div class="p-2 bg-red-100 dark:bg-red-900 rounded-lg">
                            <UserX class="h-6 w-6 text-red-600 dark:text-red-400" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="relative overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <Table>
                    <TableCaption>List of all registered users with online status</TableCaption>

                    <TableHeader>
                        <TableRow>
                            <TableHead>ID</TableHead>
                            <TableHead>Name</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Account Status</TableHead>
                            <TableHead>Online Status</TableHead>
                            <TableHead>Last Activity</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <template v-if="users.length">
                            <TableRow 
                                v-for="user in users" 
                                :key="user.id" 
                                class="hover:bg-gray-50 dark:hover:bg-gray-800"
                            >
                                <TableCell class="font-medium">
                                    {{ user.id }}
                                </TableCell>
                                <TableCell class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ user.name }}
                                </TableCell>
                                <TableCell class="text-gray-600 dark:text-gray-400">
                                    {{ user.email }}
                                </TableCell>
                                
                                <!-- Account Status -->
                                <TableCell>
                                    <span 
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                                        :class="getStatusClasses(user.status)"
                                    >
                                        {{ user.status }}
                                    </span>
                                </TableCell>
                                
                                <!-- Online Status -->
                                <TableCell>
                                    <span 
                                        class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="getColorClasses(user.last_seen_detailed.color)"
                                    >
                                        <component 
                                            :is="getStatusIcon(user.last_seen_detailed.icon)" 
                                            class="h-2.5 w-2.5" 
                                            :class="{
                                                'fill-green-500': user.last_seen_detailed.color === 'green',
                                                'fill-amber-500': user.last_seen_detailed.color === 'amber',
                                                'fill-gray-400': user.last_seen_detailed.color === 'gray',
                                            }" 
                                        />
                                        {{ user.online_status === 'online' ? 'Online' : user.online_status === 'recently' ? 'Recently Online' : 'Offline' }}
                                    </span>
                                </TableCell>
                                
                                <!-- Last Activity -->
                                <TableCell>
                                    <div class="flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-400">
                                        <Clock class="h-3.5 w-3.5" />
                                        <span>{{ user.last_seen_detailed.text }}</span>
                                    </div>
                                </TableCell>
                                
                                <!-- Actions -->
                                <TableCell>
                                    <div class="flex justify-end space-x-2">
                                        <Link
                                            :href="show(user.id)"
                                            class="p-1.5 text-green-500 hover:text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 rounded transition-colors"
                                            title="View Profile"
                                        >
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                        <Link
                                            :href="edit(user.id)"
                                            class="p-1.5 text-blue-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded transition-colors"
                                            title="Edit User"
                                        >
                                            <Edit class="h-4 w-4" />
                                        </Link>
                                        <Link
                                            :href="destroy(user.id)"
                                            class="p-1.5 text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors"
                                            title="Delete User"
                                        >
                                            <Trash class="h-4 w-4" />
                                        </Link>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </template>

                        <template v-else>
                            <TableRow>
                                <TableCell colspan="7" class="text-center py-12">
                                    <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                                        <WifiOff class="h-12 w-12 mb-4 opacity-50" />
                                        <p class="text-lg font-medium mb-2">No users found</p>
                                        <p class="text-sm">There are no users in the system yet.</p>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </template>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>