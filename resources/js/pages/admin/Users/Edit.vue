<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

// ✅ Import your route helpers
import { index, show, update, status } from '@/routes/admin/users';
import { ref } from 'vue';

// ✅ Receive user data from Laravel
const props = defineProps<{
    user: {
        id: number;
        name: string;
        email: string;
        status: 'active' | 'blocked';
        created_at?: string;
        updated_at?: string;
    };
}>();

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Users', href: index().url },
    { title: `Edit ${props.user.name}`, href: '' },
];

// ✅ Inertia form for editing a user
const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
});

// ✅ Show/hide password fields
const showPasswordFields = ref(false);

// ✅ Block/Unblock user function
function toggleUserStatus() {
    if (
        confirm(
            `Are you sure you want to ${props.user.status === 'active' ? 'block' : 'unblock'} this user?`,
        )
    ) {
        // ✅ Use the route helper function
        router.put(
            status(props.user.id).url,
            {
                status: props.user.status === 'active' ? 'blocked' : 'active',
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    // Success handled by Laravel
                },
            },
        );
    }
}
</script>

<template>
    <Head :title="`Edit User - ${user.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex flex-col gap-4 rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
        >
            <div class="flex items-center justify-between">
                <h2
                    class="text-2xl font-semibold text-gray-800 dark:text-gray-100"
                >
                    Edit User
                </h2>
                <div class="flex space-x-2">
                    <Link
                        :href="show(user.id)"
                        class="rounded-md bg-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600"
                    >
                        View Profile
                    </Link>
                    <Link
                        :href="index()"
                        class="rounded-md bg-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600"
                    >
                        Back to Users
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <form
                    @submit.prevent="
                        form.put(update(user.id).url, { preserveState: true })
                    "
                    class="col-span-2 mt-4 space-y-4"
                >
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-200"
                        >
                            Name
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                            placeholder="Enter name"
                        />
                        <span
                            v-if="form.errors.name"
                            class="text-sm text-red-500"
                            >{{ form.errors.name }}</span
                        >
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-200"
                        >
                            Email
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                            placeholder="Enter email"
                        />
                        <span
                            v-if="form.errors.email"
                            class="text-sm text-red-500"
                            >{{ form.errors.email }}</span
                        >
                    </div>

                    <!-- Password Update Section -->
                    <div
                        class="border-t border-gray-200 pt-6 dark:border-gray-600"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <h4
                                class="text-lg font-medium text-gray-900 dark:text-gray-100"
                            >
                                Password Update
                            </h4>
                            <button
                                type="button"
                                @click="
                                    showPasswordFields = !showPasswordFields
                                "
                                class="text-sm text-amber-600 hover:text-amber-700 dark:text-amber-400 dark:hover:text-amber-300"
                            >
                                {{
                                    showPasswordFields
                                        ? 'Cancel'
                                        : 'Change Password'
                                }}
                            </button>
                        </div>

                        <div
                            v-if="showPasswordFields"
                            class="space-y-4 rounded-lg bg-gray-50 p-4 dark:bg-gray-800"
                        >
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-200"
                                >
                                    New Password
                                </label>
                                <input
                                    v-model="form.password"
                                    type="password"
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                    placeholder="Enter new password"
                                />
                                <span
                                    v-if="form.errors.password"
                                    class="text-sm text-red-500"
                                    >{{ form.errors.password }}</span
                                >
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-200"
                                >
                                    Confirm New Password
                                </label>
                                <input
                                    v-model="form.password_confirmation"
                                    type="password"
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                    placeholder="Confirm new password"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end space-x-2">
                        <button
                            type="reset"
                            @click="form.reset()"
                            class="rounded-md bg-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600"
                        >
                            Reset
                        </button>
                        <button
                            type="submit"
                            class="rounded-md bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600 focus:ring-2 focus:ring-amber-400 focus:outline-none"
                        >
                            Update User
                        </button>
                    </div>
                </form>

                <!-- User Information Card -->
                <div class="col-span-1 space-y-4">
                    <div
                        class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                    >
                        <h3
                            class="mb-4 text-lg font-semibold text-gray-800 dark:text-gray-100"
                        >
                            User Information
                        </h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400"
                                    >User ID:</span
                                >
                                <span
                                    class="font-medium text-gray-900 dark:text-gray-100"
                                    >#{{ user.id }}</span
                                >
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400"
                                    >Created:</span
                                >
                                <span
                                    class="font-medium text-gray-900 dark:text-gray-100"
                                >
                                    {{
                                        new Date(
                                            user.created_at,
                                        ).toLocaleDateString()
                                    }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400"
                                    >Last Updated:</span
                                >
                                <span
                                    class="font-medium text-gray-900 dark:text-gray-100"
                                >
                                    {{
                                        new Date(
                                            user.updated_at,
                                        ).toLocaleDateString()
                                    }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400"
                                    >Status:</span
                                >
                                <span
                                    class="rounded-full px-2 py-1 text-xs font-medium capitalize"
                                    :class="{
                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200':
                                            user.status === 'active',
                                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200':
                                            user.status === 'blocked',
                                    }"
                                >
                                    {{ user.status }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div
                        class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                    >
                        <h3
                            class="mb-4 text-lg font-semibold text-gray-800 dark:text-gray-100"
                        >
                            Quick Actions
                        </h3>
                        <div class="space-y-2">
                            <button
                                @click="toggleUserStatus"
                                class="w-full rounded-md px-3 py-2 text-sm font-medium transition-colors"
                                :class="{
                                    'bg-red-100 text-red-800 hover:bg-red-200 dark:bg-red-900 dark:text-red-200 dark:hover:bg-red-800':
                                        user.status === 'active',
                                    'bg-green-100 text-green-800 hover:bg-green-200 dark:bg-green-900 dark:text-green-200 dark:hover:bg-green-800':
                                        user.status === 'blocked',
                                }"
                            >
                                {{
                                    user.status === 'active'
                                        ? 'Block User'
                                        : 'Unblock User'
                                }}
                            </button>
                            <Link
                                :href="show(user.id)"
                                class="flex w-full items-center justify-center rounded-md bg-blue-100 px-3 py-2 text-sm font-medium text-blue-800 hover:bg-blue-200 dark:bg-blue-900 dark:text-blue-200 dark:hover:bg-blue-800"
                            >
                                View Full Profile
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
