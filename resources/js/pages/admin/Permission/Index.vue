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
import { create, destroy, edit, index, show } from '@/routes/admin/permissions'; // Make sure this path matches your permissions routes
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import {
    Edit,
    Eye,
    ShieldOff,
    Trash,
} from 'lucide-vue-next';

// ✅ Receive permissions from Laravel (Inertia props)
const props = defineProps<{
    permissions: {
        data: {
            id: number;
            name: string;
            guard_name: string;
            created_at: string;
            updated_at: string;
            roles_count: number;
            users_count: number;
            roles: Array<{
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

// ✅ Access permissions via props
const permissions = props.permissions;

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Permissions', href: index().url },
];

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
    <Head title="Permissions" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex flex-col gap-4 rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
        >
            <div class="flex items-center justify-between">
                <h2
                    class="text-2xl font-semibold text-gray-800 dark:text-gray-100"
                >
                    Permission Management
                </h2>

                <Link
                    :href="create()"
                    class="rounded-md bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600 focus:ring-2 focus:ring-amber-400 focus:outline-none"
                >
                    + Add Permission
                </Link>
            </div>

            <div
                class="relative overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <Table>
                    <TableCaption
                        >List of all system permissions with role and user
                        assignments</TableCaption
                    >

                    <TableHeader>
                        <TableRow>
                            <TableHead>ID</TableHead>
                            <TableHead>Name</TableHead>
                            <TableHead>Guard</TableHead>
                            <TableHead>Roles Count</TableHead>
                            <TableHead>Users Count</TableHead>
                            <TableHead>Assigned Roles</TableHead>
                            <TableHead>Created At</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <template v-if="permissions.data.length">
                            <TableRow
                                v-for="permission in permissions.data"
                                :key="permission.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-800"
                            >
                                <TableCell class="font-medium">
                                    {{ permission.id }}
                                </TableCell>
                                <TableCell
                                    class="font-medium text-gray-900 dark:text-gray-100"
                                >
                                    {{ permission.name }}
                                </TableCell>

                                <!-- Guard Name -->
                                <TableCell>
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"
                                        :class="
                                            getGuardClasses(
                                                permission.guard_name,
                                            )
                                        "
                                    >
                                        {{ permission.guard_name }}
                                    </span>
                                </TableCell>

                                <!-- Roles Count -->
                                <TableCell>
                                    <span
                                        class="text-sm text-gray-600 dark:text-gray-400"
                                    >
                                        {{ permission.roles_count }}
                                    </span>
                                </TableCell>

                                <!-- Users Count -->
                                <TableCell>
                                    <span
                                        class="text-sm text-gray-600 dark:text-gray-400"
                                    >
                                        {{ permission.users_count }}
                                    </span>
                                </TableCell>

                                <!-- Assigned Roles -->
                                <TableCell>
                                    <div
                                        class="flex max-w-[200px] flex-wrap gap-1"
                                    >
                                        <span
                                            v-for="role in permission.roles.slice(
                                                0,
                                                3,
                                            )"
                                            :key="role.id"
                                            class="inline-block rounded bg-gray-100 px-2 py-1 text-xs text-gray-700 dark:bg-gray-700 dark:text-gray-300"
                                        >
                                            {{ role.name }}
                                        </span>
                                        <span
                                            v-if="permission.roles.length > 3"
                                            class="inline-block rounded bg-gray-200 px-2 py-1 text-xs text-gray-600 dark:bg-gray-600 dark:text-gray-400"
                                        >
                                            +{{
                                                permission.roles.length - 3
                                            }}
                                            more
                                        </span>
                                        <span
                                            v-if="permission.roles.length === 0"
                                            class="inline-block rounded bg-gray-100 px-2 py-1 text-xs text-gray-500 italic dark:bg-gray-700 dark:text-gray-400"
                                        >
                                            No roles
                                        </span>
                                    </div>
                                </TableCell>

                                <!-- Created At -->
                                <TableCell>
                                    <span
                                        class="text-sm text-gray-600 dark:text-gray-400"
                                    >
                                        {{ formatDate(permission.created_at) }}
                                    </span>
                                </TableCell>

                                <!-- Actions -->
                                <TableCell>
                                    <div class="flex justify-end space-x-2">
                                        <Link
                                            :href="show(permission.id)"
                                            class="rounded p-1.5 text-green-500 transition-colors hover:bg-green-50 hover:text-green-600 dark:hover:bg-green-900/20"
                                            title="View Details"
                                        >
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                        <Link
                                            :href="edit(permission.id)"
                                            class="rounded p-1.5 text-blue-500 transition-colors hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-900/20"
                                            title="Edit Permission"
                                        >
                                            <Edit class="h-4 w-4" />
                                        </Link>
                                        <Link
                                            :href="destroy(permission.id)"
                                            class="rounded p-1.5 text-red-500 transition-colors hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20"
                                            title="Delete Permission"
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
                                            No permissions found
                                        </p>
                                        <p class="text-sm">
                                            There are no permissions in the
                                            system yet.
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
