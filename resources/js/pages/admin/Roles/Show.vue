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
import { destroy, edit, index } from '@/routes/admin/roles';
import { give, revoke } from '@/routes/admin/roles/permissions';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    Calendar,
    Edit,
    Key,
    Plus,
    Shield,
    ShieldCheck,
    ShieldOff,
    Trash,
    Users,
    X,
} from 'lucide-vue-next';

// ✅ Receive role and allPermissions from Laravel
const props = defineProps<{
    role: {
        id: number;
        name: string;
        guard_name: string;
        created_at: string;
        updated_at: string;
        users_count: number;
        permissions: Array<{
            id: number;
            name: string;
            guard_name: string;
            created_at: string;
        }>;
    };
    allPermissions: Array<{
        id: number;
        name: string;
        guard_name: string;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Roles', href: index().url },
    { title: props.role.name, href: '#' },
];

// ✅ Form for adding permission
const permissionForm = useForm({
    permission: '',
});

// ✅ Filter available permissions (exclude already assigned ones)
const availablePermissions = props.allPermissions.filter(
    (permission) => !props.role.permissions.some((p) => p.id === permission.id),
);

// ✅ Function to assign permission
function assignPermission() {
    if (!permissionForm.permission) return;

    permissionForm.post(give(props.role.id), {
        preserveScroll: true,
        onSuccess: () => {
            permissionForm.reset();
        },
    });
}

// ✅ Function to revoke permission
function revokePermission(permissionId: number) {
    if (!confirm('Are you sure you want to revoke this permission?')) return;

    router.delete(
        revoke({ role: props.role.id, permission: permissionId }),
        {
            preserveScroll: true,
        },
    );
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

// ✅ Role type badge classes
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
</script>

<template>
    <Head :title="`Role: ${role.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6">
            <!-- Header Section -->
            <div
                class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div class="rounded-xl bg-amber-100 p-3 dark:bg-amber-900">
                        <Shield
                            class="h-8 w-8 text-amber-600 dark:text-amber-400"
                        />
                    </div>
                    <div>
                        <h1
                            class="text-3xl font-bold text-gray-900 dark:text-gray-100"
                        >
                            {{ role.name }}
                        </h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">
                            Role details and permission management
                        </p>
                    </div>
                </div>

                <div class="flex gap-2">
                    <Link
                        :href="edit(role.id)"
                        class="inline-flex items-center gap-2 rounded-md bg-blue-500 px-4 py-2 text-sm font-medium text-white hover:bg-blue-600 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                    >
                        <Edit class="h-4 w-4" />
                        Edit Role
                    </Link>
                    <Link
                        :href="destroy(role.id)"
                        method="delete"
                        as="button"
                        class="inline-flex items-center gap-2 rounded-md bg-red-500 px-4 py-2 text-sm font-medium text-white hover:bg-red-600 focus:ring-2 focus:ring-red-400 focus:outline-none"
                        :preserve-scroll="true"
                        onclick="return confirm('Are you sure you want to delete this role?')"
                    >
                        <Trash class="h-4 w-4" />
                        Delete Role
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
                            {{ role.users_count }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Users assigned to this role
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Permissions</CardTitle
                        >
                        <ShieldCheck class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div
                            class="text-2xl font-bold text-gray-900 dark:text-gray-100"
                        >
                            {{ role.permissions.length }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Permissions assigned to this role
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
                        <Key class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            <span
                                class="rounded-full px-2 py-1 text-sm font-medium capitalize"
                                :class="getGuardClasses(role.guard_name)"
                            >
                                {{ role.guard_name }}
                            </span>
                        </div>
                        <p class="mt-2 text-xs text-muted-foreground">
                            Authentication guard
                        </p>
                    </CardContent>
                </Card>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Role Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Shield class="h-5 w-5" />
                            Role Information
                        </CardTitle>
                        <CardDescription>
                            Basic details about this role
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Role Name
                                </label>
                                <p class="mt-1">
                                    <span
                                        class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium capitalize"
                                        :class="getRoleTypeClasses(role.name)"
                                    >
                                        {{ role.name }}
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
                                            getGuardClasses(role.guard_name)
                                        "
                                    >
                                        {{ role.guard_name }}
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
                                    {{ formatDate(role.created_at) }}
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
                                    {{ formatDate(role.updated_at) }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Assign Permission -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Plus class="h-5 w-5" />
                            Assign New Permission
                        </CardTitle>
                        <CardDescription>
                            Add a new permission to this role
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form
                            @submit.prevent="assignPermission"
                            class="space-y-4"
                        >
                            <div>
                                <label
                                    for="permission"
                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Select Permission
                                </label>
                                <select
                                    id="permission"
                                    v-model="permissionForm.permission"
                                    class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                    :disabled="
                                        availablePermissions.length === 0
                                    "
                                >
                                    <option value="">
                                        Choose a permission...
                                    </option>
                                    <option
                                        v-for="permission in availablePermissions"
                                        :key="permission.id"
                                        :value="permission.name"
                                    >
                                        {{ permission.name }} ({{
                                            permission.guard_name
                                        }})
                                    </option>
                                </select>
                                <p
                                    v-if="availablePermissions.length === 0"
                                    class="mt-2 text-sm text-amber-600 dark:text-amber-400"
                                >
                                    All available permissions are already
                                    assigned to this role.
                                </p>
                            </div>

                            <button
                                type="submit"
                                :disabled="
                                    !permissionForm.permission ||
                                    permissionForm.processing
                                "
                                class="flex w-full items-center justify-center gap-2 rounded-md bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600 focus:ring-2 focus:ring-amber-400 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <Plus class="h-4 w-4" />
                                <span v-if="permissionForm.processing"
                                    >Assigning...</span
                                >
                                <span v-else>Assign Permission</span>
                            </button>
                        </form>
                    </CardContent>
                </Card>
            </div>

            <!-- Assigned Permissions Table -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <ShieldCheck class="h-5 w-5" />
                        Assigned Permissions
                        <span
                            class="rounded-full bg-amber-100 px-2 py-1 text-sm text-amber-800"
                        >
                            {{ role.permissions.length }}
                        </span>
                    </CardTitle>
                    <CardDescription>
                        List of all permissions currently assigned to this role
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
                                    <TableHead>Permission Name</TableHead>
                                    <TableHead>Guard</TableHead>
                                    <TableHead>Assigned Date</TableHead>
                                    <TableHead class="text-right"
                                        >Actions</TableHead
                                    >
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <template v-if="role.permissions.length">
                                    <TableRow
                                        v-for="permission in role.permissions"
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
                                        <TableCell
                                            class="text-gray-600 dark:text-gray-400"
                                        >
                                            {{
                                                formatDate(
                                                    permission.created_at,
                                                )
                                            }}
                                        </TableCell>
                                        <TableCell>
                                            <div class="flex justify-end">
                                                <button
                                                    @click="
                                                        revokePermission(
                                                            permission.id,
                                                        )
                                                    "
                                                    class="rounded p-1.5 text-red-500 transition-colors hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20"
                                                    title="Revoke Permission"
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
                                                    No permissions assigned
                                                </p>
                                                <p class="text-sm">
                                                    This role doesn't have any
                                                    permissions yet.
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
