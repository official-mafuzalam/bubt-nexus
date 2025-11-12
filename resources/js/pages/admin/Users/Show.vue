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
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

// ✅ Import your route helpers
import { destroy, edit, index, status } from '@/routes/admin/users';
import { assign, remove } from '@/routes/admin/users/roles';
import { give, revoke } from '@/routes/admin/users/permissions';

import {
    Activity,
    ArrowLeft,
    Ban,
    Calendar,
    CheckCircle,
    Clock,
    Edit,
    Key,
    Mail,
    Plus,
    Shield,
    ShieldCheck,
    ShieldOff,
    Trash2,
    User,
    UserCog,
    Users,
    X,
} from 'lucide-vue-next';

// ✅ Receive user data from Laravel
const props = defineProps<{
    user: {
        id: number;
        name: string;
        email: string;
        status: 'active' | 'blocked';
        last_seen_at?: string;
        created_at?: string;
        updated_at?: string;
        roles: Array<{
            id: number;
            name: string;
            guard_name: string;
        }>;
        permissions: Array<{
            id: number;
            name: string;
            guard_name: string;
        }>;
    };
    allRoles: Array<{
        id: number;
        name: string;
        guard_name: string;
    }>;
    allPermissions: Array<{
        id: number;
        name: string;
        guard_name: string;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Users', href: index().url },
    { title: props.user.name, href: '' },
];

// ✅ Forms for role and permission assignment
const roleForm = useForm({
    role: '',
});

const permissionForm = useForm({
    permission: '',
});

// ✅ Filter available roles and permissions
const availableRoles = props.allRoles.filter(
    (role) => !props.user.roles.some((r) => r.id === role.id),
);

const availablePermissions = props.allPermissions.filter(
    (permission) => !props.user.permissions.some((p) => p.id === permission.id),
);

// ✅ Format date for display
function formatDate(dateString: string) {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

// ✅ Format relative time
function formatRelativeTime(dateString: string) {
    if (!dateString) return 'Never';

    const date = new Date(dateString);
    const now = new Date();
    const diffInMs = now.getTime() - date.getTime();
    const diffInMinutes = Math.floor(diffInMs / (1000 * 60));
    const diffInHours = Math.floor(diffInMs / (1000 * 60 * 60));
    const diffInDays = Math.floor(diffInMs / (1000 * 60 * 60 * 24));

    if (diffInMinutes < 1) return 'Just now';
    if (diffInMinutes < 60) return `${diffInMinutes} min ago`;
    if (diffInHours < 24) return `${diffInHours} hours ago`;
    if (diffInDays < 7) return `${diffInDays} days ago`;

    return formatDate(dateString);
}

// ✅ Check if user is recently active (within last 5 minutes)
function isRecentlyActive() {
    if (!props.user.last_seen_at) return false;
    const lastSeen = new Date(props.user.last_seen_at);
    const now = new Date();
    const diffInMinutes = (now.getTime() - lastSeen.getTime()) / (1000 * 60);
    return diffInMinutes < 5;
}

// ✅ Block/Unblock user function
function toggleUserStatus() {
    const action = props.user.status === 'active' ? 'block' : 'unblock';
    const actionText = props.user.status === 'active' ? 'block' : 'unblock';

    if (confirm(`Are you sure you want to ${actionText} this user?`)) {
        router.put(
            status(props.user.id).url,
            {
                status: props.user.status === 'active' ? 'blocked' : 'active',
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    // Success message will be shown automatically via flash
                },
                onError: (errors) => {
                    if (window.showToast) {
                        window.showToast.error(
                            'Failed to update user status. Please try again.',
                        );
                    }
                },
            },
        );
    }
}

// ✅ Delete user function
function deleteUser() {
    if (
        confirm(
            'Are you sure you want to delete this user? This action cannot be undone.',
        )
    ) {
        router.delete(destroy(props.user.id).url, {
            preserveScroll: true,
            onSuccess: () => {
                // Redirect will happen automatically
            },
            onError: (errors) => {
                if (window.showToast) {
                    window.showToast.error(
                        'Failed to delete user. Please try again.',
                    );
                }
            },
        });
    }
}

// ✅ Function to assign role
function assignRole() {
    if (!roleForm.role) return;

    roleForm.post(assign(props.user.id), {
        preserveScroll: true,
        onSuccess: () => {
            roleForm.reset();
        },
    });
}

// ✅ Function to remove role
function removeRole(roleId: number) {
    if (
        confirm(
            'Are you sure you want to remove this role from the user?',
        )
    ) {
        router.delete(
            remove({ user: props.user.id, role: roleId }),
            {
                preserveScroll: true,
            },
        );
    }
}

// ✅ Function to assign permission
function assignPermission() {
    if (!permissionForm.permission) return;

    permissionForm.post(give(props.user.id), {
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
        revoke({ user: props.user.id, permission: permissionId }),
        {
            preserveScroll: true,
        },
    );
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
</script>

<template>
    <Head :title="`User Profile - ${user.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6">
            <!-- Header -->
            <Card>
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <Link
                                :href="index()"
                                class="flex items-center gap-2 text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                            >
                                <ArrowLeft class="h-4 w-4" />
                                Back to Users
                            </Link>
                            <div
                                class="h-6 w-px bg-gray-300 dark:bg-gray-600"
                            ></div>
                            <h1
                                class="text-2xl font-bold text-gray-900 dark:text-gray-100"
                            >
                                User Profile
                            </h1>
                        </div>

                        <div class="flex space-x-2">
                            <Link
                                :href="edit(user.id)"
                                class="flex items-center gap-2 rounded-md bg-amber-500 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-amber-600"
                            >
                                <Edit class="h-4 w-4" />
                                Edit User
                            </Link>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Main Content -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Left Column - User Info & Management -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Profile Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <User class="h-5 w-5" />
                                Profile Information
                            </CardTitle>
                            <CardDescription>
                                Basic user details and account information
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-amber-500 to-orange-500"
                                    >
                                        <User class="h-8 w-8 text-white" />
                                    </div>
                                    <div>
                                        <h2
                                            class="text-2xl font-bold text-gray-900 dark:text-gray-100"
                                        >
                                            {{ user.name }}
                                        </h2>
                                        <p
                                            class="text-gray-600 dark:text-gray-400"
                                        >
                                            User ID: #{{ user.id }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-sm font-medium"
                                        :class="{
                                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200':
                                                user.status === 'active',
                                            'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200':
                                                user.status === 'blocked',
                                        }"
                                    >
                                        <Activity class="h-3 w-3" />
                                        {{
                                            user.status === 'active'
                                                ? 'Active'
                                                : 'Blocked'
                                        }}
                                    </span>
                                    <p
                                        class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        {{
                                            isRecentlyActive()
                                                ? 'Recently active'
                                                : 'Not recently active'
                                        }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="rounded-lg bg-blue-50 p-2 dark:bg-blue-900/20"
                                    >
                                        <Mail
                                            class="h-5 w-5 text-blue-600 dark:text-blue-400"
                                        />
                                    </div>
                                    <div>
                                        <p
                                            class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                        >
                                            Email
                                        </p>
                                        <p
                                            class="text-sm text-gray-600 dark:text-gray-400"
                                        >
                                            {{ user.email }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div
                                        class="rounded-lg bg-green-50 p-2 dark:bg-green-900/20"
                                    >
                                        <Calendar
                                            class="h-5 w-5 text-green-600 dark:text-green-400"
                                        />
                                    </div>
                                    <div>
                                        <p
                                            class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                        >
                                            Member Since
                                        </p>
                                        <p
                                            class="text-sm text-gray-600 dark:text-gray-400"
                                        >
                                            {{ formatDate(user.created_at) }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div
                                        class="rounded-lg bg-purple-50 p-2 dark:bg-purple-900/20"
                                    >
                                        <Clock
                                            class="h-5 w-5 text-purple-600 dark:text-purple-400"
                                        />
                                    </div>
                                    <div>
                                        <p
                                            class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                        >
                                            Last Seen
                                        </p>
                                        <p
                                            class="text-sm text-gray-600 dark:text-gray-400"
                                        >
                                            {{
                                                user.last_seen_at
                                                    ? formatRelativeTime(
                                                          user.last_seen_at,
                                                      )
                                                    : 'Never'
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div
                                        class="rounded-lg bg-amber-50 p-2 dark:bg-amber-900/20"
                                    >
                                        <Shield
                                            class="h-5 w-5 text-amber-600 dark:text-amber-400"
                                        />
                                    </div>
                                    <div>
                                        <p
                                            class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                        >
                                            Account Status
                                        </p>
                                        <p
                                            class="text-sm text-gray-600 capitalize dark:text-gray-400"
                                        >
                                            {{ user.status }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Roles Management -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <UserCog class="h-5 w-5" />
                                Roles Management
                                <span
                                    class="rounded-full bg-amber-100 px-2 py-1 text-sm text-amber-800"
                                >
                                    {{ user.roles.length }}
                                </span>
                            </CardTitle>
                            <CardDescription>
                                Manage user roles and permissions
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <!-- Assign Role Form -->
                            <div>
                                <form
                                    @submit.prevent="assignRole"
                                    class="space-y-4"
                                >
                                    <div>
                                        <label
                                            for="role"
                                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                        >
                                            Assign New Role
                                        </label>
                                        <select
                                            id="role"
                                            v-model="roleForm.role"
                                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                            :disabled="
                                                availableRoles.length === 0
                                            "
                                        >
                                            <option value="">
                                                Choose a role...
                                            </option>
                                            <option
                                                v-for="role in availableRoles"
                                                :key="role.id"
                                                :value="role.name"
                                            >
                                                {{ role.name }} ({{
                                                    role.guard_name
                                                }})
                                            </option>
                                        </select>
                                        <p
                                            v-if="availableRoles.length === 0"
                                            class="mt-2 text-sm text-amber-600 dark:text-amber-400"
                                        >
                                            User already has all available
                                            roles.
                                        </p>
                                    </div>

                                    <button
                                        type="submit"
                                        :disabled="
                                            !roleForm.role ||
                                            roleForm.processing
                                        "
                                        class="flex w-full items-center justify-center gap-2 rounded-md bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600 focus:ring-2 focus:ring-amber-400 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <Plus class="h-4 w-4" />
                                        <span v-if="roleForm.processing"
                                            >Assigning...</span
                                        >
                                        <span v-else>Assign Role</span>
                                    </button>
                                </form>
                            </div>

                            <!-- Current Roles -->
                            <div>
                                <h4
                                    class="mb-3 text-sm font-medium text-gray-900 dark:text-gray-100"
                                >
                                    Current Roles
                                </h4>
                                <div
                                    class="relative overflow-x-auto rounded-lg border border-sidebar-border/70 dark:border-sidebar-border"
                                >
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Role Name</TableHead>
                                                <TableHead>Guard</TableHead>
                                                <TableHead class="text-right"
                                                    >Actions</TableHead
                                                >
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <template v-if="user.roles.length">
                                                <TableRow
                                                    v-for="role in user.roles"
                                                    :key="role.id"
                                                    class="hover:bg-gray-50 dark:hover:bg-gray-800"
                                                >
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
                                                            {{
                                                                role.guard_name
                                                            }}
                                                        </span>
                                                    </TableCell>
                                                    <TableCell>
                                                        <div
                                                            class="flex justify-end"
                                                        >
                                                            <button
                                                                @click="
                                                                    removeRole(
                                                                        role.id,
                                                                    )
                                                                "
                                                                class="rounded p-1.5 text-red-500 transition-colors hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20"
                                                                title="Remove Role"
                                                            >
                                                                <X
                                                                    class="h-4 w-4"
                                                                />
                                                            </button>
                                                        </div>
                                                    </TableCell>
                                                </TableRow>
                                            </template>
                                            <template v-else>
                                                <TableRow>
                                                    <TableCell
                                                        colspan="3"
                                                        class="py-8 text-center"
                                                    >
                                                        <div
                                                            class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400"
                                                        >
                                                            <Users
                                                                class="mb-2 h-8 w-8 opacity-50"
                                                            />
                                                            <p class="text-sm">
                                                                No roles
                                                                assigned to this
                                                                user.
                                                            </p>
                                                        </div>
                                                    </TableCell>
                                                </TableRow>
                                            </template>
                                        </TableBody>
                                    </Table>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Direct Permissions -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Key class="h-5 w-5" />
                                Direct Permissions
                                <span
                                    class="rounded-full bg-amber-100 px-2 py-1 text-sm text-amber-800"
                                >
                                    {{ user.permissions.length }}
                                </span>
                            </CardTitle>
                            <CardDescription>
                                Manage direct permissions assigned to this user
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <!-- Assign Permission Form -->
                            <div>
                                <form
                                    @submit.prevent="assignPermission"
                                    class="space-y-4"
                                >
                                    <div>
                                        <label
                                            for="permission"
                                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                        >
                                            Assign Direct Permission
                                        </label>
                                        <select
                                            id="permission"
                                            v-model="permissionForm.permission"
                                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                            :disabled="
                                                availablePermissions.length ===
                                                0
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
                                            v-if="
                                                availablePermissions.length ===
                                                0
                                            "
                                            class="mt-2 text-sm text-amber-600 dark:text-amber-400"
                                        >
                                            User already has all available
                                            permissions.
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
                            </div>

                            <!-- Current Permissions -->
                            <div>
                                <h4
                                    class="mb-3 text-sm font-medium text-gray-900 dark:text-gray-100"
                                >
                                    Current Direct Permissions
                                </h4>
                                <div
                                    class="relative overflow-x-auto rounded-lg border border-sidebar-border/70 dark:border-sidebar-border"
                                >
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead
                                                    >Permission Name</TableHead
                                                >
                                                <TableHead>Guard</TableHead>
                                                <TableHead class="text-right"
                                                    >Actions</TableHead
                                                >
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <template
                                                v-if="user.permissions.length"
                                            >
                                                <TableRow
                                                    v-for="permission in user.permissions"
                                                    :key="permission.id"
                                                    class="hover:bg-gray-50 dark:hover:bg-gray-800"
                                                >
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
                                                            {{
                                                                permission.guard_name
                                                            }}
                                                        </span>
                                                    </TableCell>
                                                    <TableCell>
                                                        <div
                                                            class="flex justify-end"
                                                        >
                                                            <button
                                                                @click="
                                                                    revokePermission(
                                                                        permission.id,
                                                                    )
                                                                "
                                                                class="rounded p-1.5 text-red-500 transition-colors hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20"
                                                                title="Revoke Permission"
                                                            >
                                                                <X
                                                                    class="h-4 w-4"
                                                                />
                                                            </button>
                                                        </div>
                                                    </TableCell>
                                                </TableRow>
                                            </template>
                                            <template v-else>
                                                <TableRow>
                                                    <TableCell
                                                        colspan="3"
                                                        class="py-8 text-center"
                                                    >
                                                        <div
                                                            class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400"
                                                        >
                                                            <ShieldOff
                                                                class="mb-2 h-8 w-8 opacity-50"
                                                            />
                                                            <p class="text-sm">
                                                                No direct
                                                                permissions
                                                                assigned to this
                                                                user.
                                                            </p>
                                                        </div>
                                                    </TableCell>
                                                </TableRow>
                                            </template>
                                        </TableBody>
                                    </Table>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right Column - Actions & Details -->
                <div class="space-y-6">
                    <!-- Quick Actions -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Activity class="h-5 w-5" />
                                Quick Actions
                            </CardTitle>
                            <CardDescription>
                                Manage user account status and actions
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <Link
                                :href="edit(user.id)"
                                class="flex w-full items-center justify-center gap-2 rounded-lg bg-amber-500 px-4 py-3 text-sm font-medium text-white transition-colors hover:bg-amber-600"
                            >
                                <Edit class="h-4 w-4" />
                                Edit Profile
                            </Link>

                            <button
                                @click="toggleUserStatus"
                                class="flex w-full items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium transition-colors"
                                :class="{
                                    'bg-red-100 text-red-800 hover:bg-red-200 dark:bg-red-900 dark:text-red-200 dark:hover:bg-red-800':
                                        user.status === 'active',
                                    'bg-green-100 text-green-800 hover:bg-green-200 dark:bg-green-900 dark:text-green-200 dark:hover:bg-green-800':
                                        user.status === 'blocked',
                                }"
                            >
                                <Ban
                                    v-if="user.status === 'active'"
                                    class="h-4 w-4"
                                />
                                <CheckCircle v-else class="h-4 w-4" />
                                {{
                                    user.status === 'active'
                                        ? 'Block User'
                                        : 'Unblock User'
                                }}
                            </button>
                        </CardContent>
                    </Card>

                    <!-- Status Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <ShieldCheck class="h-5 w-5" />
                                Status Information
                            </CardTitle>
                            <CardDescription>
                                User account status and activity
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-sm text-gray-600 dark:text-gray-400"
                                    >Current Status</span
                                >
                                <span
                                    class="rounded-full px-2 py-1 text-xs font-medium"
                                    :class="{
                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200':
                                            user.status === 'active',
                                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200':
                                            user.status === 'blocked',
                                    }"
                                >
                                    {{
                                        user.status === 'active'
                                            ? 'Active'
                                            : 'Blocked'
                                    }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-sm text-gray-600 dark:text-gray-400"
                                    >Last Activity</span
                                >
                                <span
                                    class="text-sm text-gray-900 dark:text-gray-100"
                                >
                                    {{
                                        user.last_seen_at
                                            ? formatRelativeTime(
                                                  user.last_seen_at,
                                              )
                                            : 'Never'
                                    }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-sm text-gray-600 dark:text-gray-400"
                                    >Account Age</span
                                >
                                <span
                                    class="text-sm text-gray-900 dark:text-gray-100"
                                >
                                    {{
                                        user.created_at
                                            ? Math.floor(
                                                  (new Date().getTime() -
                                                      new Date(
                                                          user.created_at,
                                                      ).getTime()) /
                                                      (1000 * 60 * 60 * 24),
                                              ) + ' days'
                                            : 'N/A'
                                    }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-sm text-gray-600 dark:text-gray-400"
                                    >Total Roles</span
                                >
                                <span
                                    class="text-sm text-gray-900 dark:text-gray-100"
                                >
                                    {{ user.roles.length }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-sm text-gray-600 dark:text-gray-400"
                                    >Direct Permissions</span
                                >
                                <span
                                    class="text-sm text-gray-900 dark:text-gray-100"
                                >
                                    {{ user.permissions.length }}
                                </span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Danger Zone -->
                    <Card
                        class="border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20"
                    >
                        <CardHeader>
                            <CardTitle
                                class="flex items-center gap-2 text-red-800 dark:text-red-200"
                            >
                                <Trash2 class="h-5 w-5" />
                                Danger Zone
                            </CardTitle>
                            <CardDescription
                                class="text-red-700 dark:text-red-300"
                            >
                                Permanent actions that cannot be undone
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <p
                                class="mb-4 text-sm text-red-700 dark:text-red-300"
                            >
                                Once you delete a user, there is no going back.
                                Please be certain.
                            </p>
                            <button
                                @click="deleteUser"
                                class="flex w-full items-center justify-center gap-2 rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-700 transition-colors hover:bg-red-50 dark:border-red-600 dark:bg-red-900 dark:text-red-200 dark:hover:bg-red-800"
                            >
                                <Trash2 class="h-4 w-4" />
                                Delete This User
                            </button>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
