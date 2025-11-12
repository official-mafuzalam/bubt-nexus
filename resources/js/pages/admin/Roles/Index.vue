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
import { create, destroy, edit, index, show } from '@/routes/admin/roles';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import {
    Edit,
    Eye,
    ShieldCheck,
    ShieldOff,
    Trash,
    Users,
} from 'lucide-vue-next';

// ✅ Receive roles from Laravel (Inertia props)
const props = defineProps<{
    roles: {
        data: {
            id: number;
            name: string;
            guard_name: string;
            created_at: string;
            updated_at: string;
            users_count: number;
            permissions: Array<{
                id: number;
                name: string;
            }>;
        }[];
        links: any[];
        meta: {
            current_page: number;
            last_page: number;
            from: number;
            to: number;
            total: number;
        };
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Roles', href: index().url },
];

// ✅ Access roles via props
const roles = props.roles;

// ✅ Guard name badge classes
function getGuardClasses(guard: string) {
    return {
        'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200':
            guard === 'web',
        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200':
            guard === 'api',
        'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200':
            guard === 'sanctum',
    };
}

// ✅ Role type badge classes (you can customize based on your role naming convention)
function getRoleTypeClasses(roleName: string) {
    const lowerName = roleName.toLowerCase();

    if (lowerName.includes('admin') || lowerName === 'super admin') {
        return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
    } else if (
        lowerName.includes('manager') ||
        lowerName.includes('moderator')
    ) {
        return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200';
    } else if (lowerName.includes('user') || lowerName === 'user') {
        return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
    } else {
        return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
    }
}

// ✅ Format date
function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}
</script>

<template>
    <Head title="Roles" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex flex-col gap-4 rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
        >
            <div class="flex items-center justify-between">
                <h2
                    class="text-2xl font-semibold text-gray-800 dark:text-gray-100"
                >
                    Role Management
                </h2>

                <Link
                    :href="create()"
                    class="rounded-md bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600 focus:ring-2 focus:ring-amber-400 focus:outline-none"
                >
                    + Add Role
                </Link>
            </div>

            <div
                class="relative overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <Table>
                    <TableCaption
                        >List of all system roles with user counts and
                        permission assignments</TableCaption
                    >

                    <TableHeader>
                        <TableRow>
                            <TableHead>ID</TableHead>
                            <TableHead>Name</TableHead>
                            <TableHead>Guard</TableHead>
                            <TableHead>Users Count</TableHead>
                            <TableHead>Permissions Count</TableHead>
                            <TableHead>Assigned Permissions</TableHead>
                            <TableHead>Created At</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <template v-if="roles.data.length">
                            <TableRow
                                v-for="role in roles.data"
                                :key="role.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-800"
                            >
                                <TableCell class="font-medium">
                                    {{ role.id }}
                                </TableCell>

                                <!-- Role Name -->
                                <TableCell
                                    class="font-medium text-gray-900 dark:text-gray-100"
                                >
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"
                                            :class="
                                                getRoleTypeClasses(role.name)
                                            "
                                        >
                                            {{ role.name }}
                                        </span>
                                    </div>
                                </TableCell>

                                <!-- Guard Name -->
                                <TableCell>
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"
                                        :class="
                                            getGuardClasses(role.guard_name)
                                        "
                                    >
                                        {{ role.guard_name }}
                                    </span>
                                </TableCell>

                                <!-- Users Count -->
                                <TableCell>
                                    <div class="flex items-center gap-1.5">
                                        <Users class="h-4 w-4 text-gray-400" />
                                        <span
                                            class="text-sm font-medium text-gray-600 dark:text-gray-400"
                                        >
                                            {{ role.users_count }}
                                        </span>
                                    </div>
                                </TableCell>

                                <!-- Permissions Count -->
                                <TableCell>
                                    <div class="flex items-center gap-1.5">
                                        <ShieldCheck
                                            class="h-4 w-4 text-gray-400"
                                        />
                                        <span
                                            class="text-sm font-medium text-gray-600 dark:text-gray-400"
                                        >
                                            {{ role.permissions.length }}
                                        </span>
                                    </div>
                                </TableCell>

                                <!-- Assigned Permissions -->
                                <TableCell>
                                    <div
                                        class="flex max-w-[200px] flex-wrap gap-1"
                                    >
                                        <span
                                            v-for="permission in role.permissions.slice(
                                                0,
                                                3,
                                            )"
                                            :key="permission.id"
                                            class="inline-block truncate rounded bg-gray-100 px-2 py-1 text-xs text-gray-700 dark:bg-gray-700 dark:text-gray-300"
                                            :title="permission.name"
                                        >
                                            {{ permission.name }}
                                        </span>
                                        <span
                                            v-if="role.permissions.length > 3"
                                            class="inline-block rounded bg-gray-200 px-2 py-1 text-xs text-gray-600 dark:bg-gray-600 dark:text-gray-400"
                                        >
                                            +{{
                                                role.permissions.length - 3
                                            }}
                                            more
                                        </span>
                                        <span
                                            v-if="role.permissions.length === 0"
                                            class="inline-block rounded bg-gray-100 px-2 py-1 text-xs text-gray-500 italic dark:bg-gray-700 dark:text-gray-400"
                                        >
                                            No permissions
                                        </span>
                                    </div>
                                </TableCell>

                                <!-- Created At -->
                                <TableCell>
                                    <span
                                        class="text-sm text-gray-600 dark:text-gray-400"
                                    >
                                        {{ formatDate(role.created_at) }}
                                    </span>
                                </TableCell>

                                <!-- Actions -->
                                <TableCell>
                                    <div class="flex justify-end space-x-2">
                                        <Link
                                            :href="show(role.id)"
                                            class="rounded p-1.5 text-green-500 transition-colors hover:bg-green-50 hover:text-green-600 dark:hover:bg-green-900/20"
                                            title="View Details"
                                        >
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                        <Link
                                            :href="edit(role.id)"
                                            class="rounded p-1.5 text-blue-500 transition-colors hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-900/20"
                                            title="Edit Role"
                                        >
                                            <Edit class="h-4 w-4" />
                                        </Link>
                                        <Link
                                            :href="destroy(role.id)"
                                            class="rounded p-1.5 text-red-500 transition-colors hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20"
                                            title="Delete Role"
                                            method="delete"
                                            as="button"
                                            :preserve-scroll="true"
                                        >
                                            <Trash class="h-4 w-4" />
                                        </Link>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </template>

                        <template v-else>
                            <TableRow>
                                <TableCell
                                    colspan="8"
                                    class="py-12 text-center"
                                >
                                    <div
                                        class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400"
                                    >
                                        <ShieldOff
                                            class="mb-4 h-12 w-12 opacity-50"
                                        />
                                        <p class="mb-2 text-lg font-medium">
                                            No roles found
                                        </p>
                                        <p class="text-sm">
                                            There are no roles in the system
                                            yet.
                                        </p>
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
