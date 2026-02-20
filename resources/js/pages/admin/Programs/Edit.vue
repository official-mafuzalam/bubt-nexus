<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
        >
            <!-- Header -->
            <div class="mb-6">
                <h2
                    class="text-2xl font-semibold text-gray-800 dark:text-gray-100"
                >
                    Edit Property
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Update the property details below
                </p>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Left Column -->
                    <div class="space-y-6">
                        <!-- Title -->
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Property Title *
                            </label>
                            <input
                                v-model="form.title"
                                type="text"
                                required
                                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                            />
                            <p
                                v-if="form.errors.title"
                                class="mt-1 text-sm text-red-600 dark:text-red-400"
                            >
                                {{ form.errors.title }}
                            </p>
                        </div>

                        <!-- Category -->
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Property Type *
                            </label>
                            <select
                                v-model="form.category"
                                required
                                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                            >
                                <option value="room">Room</option>
                                <option value="flat">Flat/Apartment</option>
                                <option value="single_bed">
                                    Single Bed Space
                                </option>
                                <option value="sublet">Sublet</option>
                            </select>
                            <p
                                v-if="form.errors.category"
                                class="mt-1 text-sm text-red-600 dark:text-red-400"
                            >
                                {{ form.errors.category }}
                            </p>
                        </div>

                        <!-- Rent -->
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Monthly Rent (BDT) *
                            </label>
                            <div class="relative">
                                <span
                                    class="absolute top-1/2 left-3 -translate-y-1/2 text-gray-500"
                                    >৳</span
                                >
                                <input
                                    v-model.number="form.rent"
                                    type="number"
                                    min="0"
                                    required
                                    class="w-full rounded-md border border-gray-300 bg-white py-2 pr-3 pl-8 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                />
                            </div>
                            <p
                                v-if="form.errors.rent"
                                class="mt-1 text-sm text-red-600 dark:text-red-400"
                            >
                                {{ form.errors.rent }}
                            </p>
                        </div>

                        <!-- Location -->
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Location/Area *
                            </label>
                            <input
                                v-model="form.location"
                                type="text"
                                required
                                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                            />
                            <p
                                v-if="form.errors.location"
                                class="mt-1 text-sm text-red-600 dark:text-red-400"
                            >
                                {{ form.errors.location }}
                            </p>
                        </div>

                        <!-- Contact Number -->
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Contact Number *
                            </label>
                            <input
                                v-model="form.contact_number"
                                type="tel"
                                required
                                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                            />
                            <p
                                v-if="form.errors.contact_number"
                                class="mt-1 text-sm text-red-600 dark:text-red-400"
                            >
                                {{ form.errors.contact_number }}
                            </p>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6">
                        <!-- Description -->
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Description
                            </label>
                            <textarea
                                v-model="form.description"
                                rows="4"
                                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                            ></textarea>
                            <p
                                v-if="form.errors.description"
                                class="mt-1 text-sm text-red-600 dark:text-red-400"
                            >
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <!-- Address Details -->
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Address Details
                            </label>
                            <input
                                v-model="form.address_detail"
                                type="text"
                                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                            />
                            <p
                                v-if="form.errors.address_detail"
                                class="mt-1 text-sm text-red-600 dark:text-red-400"
                            >
                                {{ form.errors.address_detail }}
                            </p>
                        </div>

                        <!-- Bedrooms & Washrooms -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Bedrooms
                                </label>
                                <input
                                    v-model.number="form.bedrooms"
                                    type="number"
                                    min="1"
                                    class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                />
                                <p
                                    v-if="form.errors.bedrooms"
                                    class="mt-1 text-sm text-red-600 dark:text-red-400"
                                >
                                    {{ form.errors.bedrooms }}
                                </p>
                            </div>
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Washrooms
                                </label>
                                <input
                                    v-model.number="form.washrooms"
                                    type="number"
                                    min="1"
                                    class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                />
                                <p
                                    v-if="form.errors.washrooms"
                                    class="mt-1 text-sm text-red-600 dark:text-red-400"
                                >
                                    {{ form.errors.washrooms }}
                                </p>
                            </div>
                        </div>

                        <!-- Available From -->
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Available From
                            </label>
                            <input
                                v-model="form.available_from"
                                type="date"
                                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                            />
                            <p
                                v-if="form.errors.available_from"
                                class="mt-1 text-sm text-red-600 dark:text-red-400"
                            >
                                {{ form.errors.available_from }}
                            </p>
                        </div>

                        <!-- Availability -->
                        <div class="flex items-center">
                            <input
                                v-model="form.is_available"
                                type="checkbox"
                                true-value="1"
                                false-value="0"
                                class="h-4 w-4 rounded border-gray-300 text-amber-500 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-700"
                            />
                            <label
                                class="ml-2 text-sm text-gray-700 dark:text-gray-300"
                            >
                                Property is available for rent
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div
                    class="flex items-center justify-between border-t border-gray-200 pt-6 dark:border-gray-700"
                >
                    <div class="flex gap-2">
                        <button
                            type="button"
                            @click="deletePost"
                            class="inline-flex items-center gap-2 rounded-md bg-red-50 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40"
                        >
                            <Trash class="h-4 w-4" />
                            Delete
                        </button>
                        <Link
                            :href="route('admin.rent-posts.show', post.id)"
                            class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                        >
                            <Eye class="h-4 w-4" />
                            View Property
                        </Link>
                    </div>

                    <div class="flex gap-4">
                        <Link
                            :href="route('admin.rent-posts.index')"
                            class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-2 rounded-md bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600 focus:ring-2 focus:ring-amber-400 focus:outline-none disabled:opacity-50"
                        >
                            <Save class="h-4 w-4" />
                            {{
                                form.processing
                                    ? 'Updating...'
                                    : 'Update Property'
                            }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Link, router, useForm } from '@inertiajs/vue3';
import { Eye, Save, Trash } from 'lucide-vue-next';
import { route } from 'ziggy-js';

const props = defineProps<{
    post: any;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Rent Management', href: route('admin.rent-posts.index') },
    {
        title: props.post.title,
        href: route('admin.rent-posts.show', props.post.id),
    },
    { title: 'Edit', href: route('admin.rent-posts.edit', props.post.id) },
];

const form = useForm({
    title: props.post.title,
    description: props.post.description || '',
    category: props.post.category,
    contact_number: props.post.contact_number,
    rent: props.post.rent,
    location: props.post.location,
    address_detail: props.post.address_detail || '',
    bedrooms: props.post.bedrooms || '',
    washrooms: props.post.washrooms || '',
    available_from: props.post.available_from
        ? props.post.available_from.split('T')[0]
        : '',
    is_available: props.post.is_available ? 1 : 0,
});

const submit = () => {
    form.put(route('admin.rent-posts.update', props.post.id));
};

const deletePost = () => {
    if (
        confirm(
            'Are you sure you want to delete this property? This action cannot be undone.',
        )
    ) {
        router.delete(route('admin.rent-posts.destroy', props.post.id));
    }
};
</script>
