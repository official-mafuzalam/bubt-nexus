<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';

// ✅ Import your route helpers
import { index, store } from '@/routes/admin/users';

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Users', href: index().url },
    { title: 'Create', href: '' },
];

// ✅ Inertia form for creating a user
const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});
</script>

<template>
    <Head title="Create User" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex flex-col gap-4 rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
        >
            <div class="flex items-center justify-between">
                <h2
                    class="text-2xl font-semibold text-gray-800 dark:text-gray-100"
                >
                    Create New User
                </h2>
                <Link
                    :href="index()"
                    class="rounded-md bg-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600"
                >
                    Back to Users
                </Link>
            </div>

            <form
                @submit.prevent="form.post(store().url, { preserveState: true })"
                class="mt-4 space-y-4"
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

                <div>
                    <label
                        class="block text-sm font-medium text-gray-700 dark:text-gray-200"
                    >
                        Password
                    </label>
                    <input
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Enter password"
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
                        Confirm Password
                    </label>
                    <input
                        v-model="form.password_confirmation"
                        type="password"
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Confirm password"
                    />
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
                        Create
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
