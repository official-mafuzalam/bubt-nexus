<script setup lang="ts">
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { destroy, edit, index } from '@/routes/admin/permissions';
import { assign, remove } from '@/routes/admin/permissions/roles';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    Calendar,
    Edit,
    Key,
    Plus,
    Shield,
    ShieldOff,
    Trash,
    UserCog,
    Users,
    X,
} from 'lucide-vue-next';

// ✅ Receive permission and allRoles from Laravel
const props = defineProps<{
    permission: {
        id: number;
        name: string;
        guard_name: string;
        created_at: string;
        updated_at: string;
        users_count: number;
        roles_count: number;
        roles: Array<{
            id: number;
            name: string;
            guard_name: string;
            created_at: string;
        }>;
    };
    allRoles: Array<{
        id: number;
        name: string;
        guard_name: string;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Permissions', href: index().url },
    { title: props.permission.name, href: '#' },
];

// ✅ Form for assigning role
const roleForm = useForm({
    role: '',
});

// ✅ Filter available roles (exclude already assigned ones)
const availableRoles = props.allRoles.filter(
    (role) => !props.permission.roles.some((r) => r.id === role.id),
);

// ✅ Function to assign role
function assignRole() {
    if (!roleForm.role) return;

    roleForm.post(assign(props.permission.id), {
        preserveScroll: true,
        onSuccess: () => {
            roleForm.reset();
        },
    });
}

// ✅ Function to remove role
function removeRole(roleId: number) {
    if (!confirm('Are you sure you want to revoke this permission?')) return;

    router.delete(remove({ role: roleId, permission: props.permission.id }), {
        preserveScroll: true,
    });
}

// ✅ Format date
function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

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

// ✅ Permission type badge classes
function getPermissionTypeClasses(permissionName: string) {
    const lowerName = permissionName.toLowerCase();

    if (lowerName.includes('create') || lowerName.includes('add')) {
        return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
    } else if (lowerName.includes('edit') || lowerName.includes('update')) {
        return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
    } else if (lowerName.includes('delete') || lowerName.includes('remove')) {
        return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
    } else if (lowerName.includes('view') || lowerName.includes('read')) {
        return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200';
    } else {
        return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
    }
}
</script>

<template>
    <Head :title="`Permission: ${permission.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6">
            <!-- Header Section -->
            <div
                class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div class="rounded-xl bg-amber-100 p-3 dark:bg-amber-900">
                        <Key class="dark:amber-400 h-8 w-8 text-amber-600" />
                    </div>
                    <div>
                        <h1
                            class="text-3xl font-bold text-gray-900 dark:text-gray-100"
                        >
                            {{ permission.name }}
                        </h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">
                            Permission details and role assignments
                        </p>
                    </div>
                </div>

                <div class="flex gap-2">
                    <Link
                        :href="edit(permission.id)"
                        class="inline-flex items-center gap-2 rounded-md bg-blue-500 px-4 py-2 text-sm font-medium text-white hover:bg-blue-600 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                    >
                        <Edit class="h-4 w-4" />
                        Edit Permission
                    </Link>
                    <Link
                        :href="destroy(permission.id)"
                        method="delete"
                        as="button"
                        class="inline-flex items-center gap-2 rounded-md bg-red-500 px-4 py-2 text-sm font-medium text-white hover:bg-red-600 focus:ring-2 focus:ring-red-400 focus:outline-none"
                        :preserve-scroll="true"
                        onclick="return confirm('Are you sure you want to delete this permission?')"
                    >
                        <Trash class="h-4 w-4" />
                        Delete Permission
                    </Link>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Total Users</CardTitle
                        >
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div
                            class="text-2xl font-bold text-gray-900 dark:text-gray-100"
                        >
                            {{ permission.users_count }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Users with this permission
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Assigned Roles</CardTitle
                        >
                        <UserCog class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div
                            class="text-2xl font-bold text-gray-900 dark:text-gray-100"
                        >
                            {{ permission.roles.length }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Roles with this permission
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Guard Name</CardTitle
                        >
                        <Shield class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            <span
                                class="rounded-full px-2 py-1 text-sm font-medium capitalize"
                                :class="getGuardClasses(permission.guard_name)"
                            >
                                {{ permission.guard_name }}
                            </span>
                        </div>
                        <p class="mt-2 text-xs text-muted-foreground">
                            Authentication guard
                        </p>
                    </CardContent>
                </Card>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Permission Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Key class="h-5 w-5" />
                            Permission Information
                        </CardTitle>
                        <CardDescription>
                            Basic details about this permission
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Permission Name
                                </label>
                                <p class="mt-1">
                                    <span
                                        class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium capitalize"
                                        :class="
                                            getPermissionTypeClasses(
                                                permission.name,
                                            )
                                        "
                                    >
                                        {{ permission.name }}
                                    </span>
                                </p>
                            </div>
                            <div>
                                <label
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Guard
                                </label>
                                <p class="mt-1">
                                    <span
                                        class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium capitalize"
                                        :class="
                                            getGuardClasses(
                                                permission.guard_name,
                                            )
                                        "
                                    >
                                        {{ permission.guard_name }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Created At
                                </label>
                                <p
                                    class="mt-1 flex items-center gap-1 text-sm text-gray-900 dark:text-gray-100"
                                >
                                    <Calendar class="h-4 w-4" />
                                    {{ formatDate(permission.created_at) }}
                                </p>
                            </div>
                            <div>
                                <label
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Last Updated
                                </label>
                                <p
                                    class="mt-1 flex items-center gap-1 text-sm text-gray-900 dark:text-gray-100"
                                >
                                    <Calendar class="h-4 w-4" />
                                    {{ formatDate(permission.updated_at) }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Assign Role -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Plus class="h-5 w-5" />
                            Assign to Role
                        </CardTitle>
                        <CardDescription>
                            Assign this permission to a role
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="assignRole" class="space-y-4">
                            <div>
                                <label
                                    for="role"
                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Select Role
                                </label>
                                <select
                                    id="role"
                                    v-model="roleForm.role"
                                    class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                    :disabled="availableRoles.length === 0"
                                >
                                    <option value="">Choose a role...</option>
                                    <option
                                        v-for="role in availableRoles"
                                        :key="role.id"
                                        :value="role.name"
                                    >
                                        {{ role.name }} ({{ role.guard_name }})
                                    </option>
                                </select>
                                <p
                                    v-if="availableRoles.length === 0"
                                    class="mt-2 text-sm text-amber-600 dark:text-amber-400"
                                >
                                    This permission is already assigned to all
                                    available roles.
                                </p>
                            </div>

                            <button
                                type="submit"
                                :disabled="
                                    !roleForm.role || roleForm.processing
                                "
                                class="flex w-full items-center justify-center gap-2 rounded-md bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600 focus:ring-2 focus:ring-amber-400 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <Plus class="h-4 w-4" />
                                <span v-if="roleForm.processing"
                                    >Assigning...</span
                                >
                                <span v-else>Assign to Role</span>
                            </button>
                        </form>
                    </CardContent>
                </Card>
            </div>

            <!-- Assigned Roles Table -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <UserCog class="h-5 w-5" />
                        Assigned Roles
                        <span
                            class="rounded-full bg-amber-100 px-2 py-1 text-sm text-amber-800"
                        >
                            {{ permission.roles.length }}
                        </span>
                    </CardTitle>
                    <CardDescription>
                        List of all roles that have this permission
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div
                        class="relative overflow-x-auto rounded-lg border border-sidebar-border/70 dark:border-sidebar-border"
                    >
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>ID</TableHead>
                                    <TableHead>Role Name</TableHead>
                                    <TableHead>Guard</TableHead>
                                    <TableHead>Assigned Date</TableHead>
                                    <TableHead class="text-right"
                                        >Actions</TableHead
                                    >
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <template v-if="permission.roles.length">
                                    <TableRow
                                        v-for="role in permission.roles"
                                        :key="role.id"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-800"
                                    >
                                        <TableCell class="font-medium">
                                            {{ role.id }}
                                        </TableCell>
                                        <TableCell
                                            class="font-medium text-gray-900 dark:text-gray-100"
                                        >
                                            {{ role.name }}
                                        </TableCell>
                                        <TableCell>
                                            <span
                                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"
                                                :class="
                                                    getGuardClasses(
                                                        role.guard_name,
                                                    )
                                                "
                                            >
                                                {{ role.guard_name }}
                                            </span>
                                        </TableCell>
                                        <TableCell
                                            class="text-gray-600 dark:text-gray-400"
                                        >
                                            {{ formatDate(role.created_at) }}
                                        </TableCell>
                                        <TableCell>
                                            <div class="flex justify-end">
                                                <button
                                                    @click="removeRole(role.id)"
                                                    class="rounded p-1.5 text-red-500 transition-colors hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20"
                                                    title="Remove from Role"
                                                >
                                                    <X class="h-4 w-4" />
                                                </button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                </template>
                                <template v-else>
                                    <TableRow>
                                        <TableCell
                                            colspan="5"
                                            class="py-12 text-center"
                                        >
                                            <div
                                                class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400"
                                            >
                                                <ShieldOff
                                                    class="mb-4 h-12 w-12 opacity-50"
                                                />
                                                <p
                                                    class="mb-2 text-lg font-medium"
                                                >
                                                    No roles assigned
                                                </p>
                                                <p class="text-sm">
                                                    This permission is not
                                                    assigned to any roles yet.
                                                </p>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                </template>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
