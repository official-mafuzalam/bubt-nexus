<template>
    <Head :title="'Edit Note: ' + form.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-2xl">
            <div
                class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
            >
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2
                                class="text-2xl font-semibold text-gray-800 dark:text-gray-100"
                            >
                                Edit Note
                            </h2>
                            <p
                                class="mt-2 text-sm text-gray-600 dark:text-gray-400"
                            >
                                Update note information and details
                            </p>
                        </div>
                        <span
                            class="rounded-full px-3 py-1 text-xs font-medium"
                            :class="getFileTypeBadgeClasses(note.file_type)"
                        >
                            {{ note.file_type.toUpperCase() }}
                        </span>
                    </div>
                </div>

                <!-- Current File Info -->
                <div
                    class="mb-6 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-lg"
                            :class="getFileTypeClasses(note.file_type)"
                        >
                            <component
                                :is="getFileIcon(note.file_type)"
                                class="h-6 w-6"
                                :class="getFileIconColor(note.file_type)"
                            />
                        </div>
                        <div class="flex-1">
                            <p
                                class="text-sm font-medium text-gray-900 dark:text-gray-100"
                            >
                                {{ note.title }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ note.formatted_size }} • Uploaded
                                {{ formatDate(note.created_at) }}
                            </p>
                        </div>
                        <a
                            :href="note.file_url"
                            target="_blank"
                            class="text-sm text-amber-600 hover:text-amber-700 dark:text-amber-400"
                        >
                            View File
                        </a>
                    </div>
                </div>

                <!-- Edit Form -->
                <form @submit.prevent="submitForm" class="space-y-6">
                    <!-- Title -->
                    <div>
                        <label
                            for="title"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Note Title *
                        </label>
                        <input
                            v-model="form.title"
                            type="text"
                            id="title"
                            required
                            class="w-full rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                            :class="{ 'border-red-500': form.errors.title }"
                        />
                        <p
                            v-if="form.errors.title"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.title }}
                        </p>
                    </div>

                    <!-- Course Details -->
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Course Name -->
                        <div>
                            <label
                                for="course_name"
                                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Course Name
                            </label>
                            <input
                                v-model="form.course_name"
                                type="text"
                                id="course_name"
                                class="w-full rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                            />
                        </div>

                        <!-- Course Code -->
                        <div>
                            <label
                                for="course_code"
                                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Course Code
                            </label>
                            <input
                                v-model="form.course_code"
                                type="text"
                                id="course_code"
                                class="w-full rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                            />
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end gap-3 pt-6">
                        <Link
                            :href="route('admin.notes.show', note.id)"
                            class="rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-2 rounded-md bg-amber-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-amber-600 focus:ring-2 focus:ring-amber-500 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <svg
                                v-if="form.processing"
                                class="h-4 w-4 animate-spin"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                ></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                            <Check v-else class="h-4 w-4" />
                            {{ form.processing ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Check, File, FileText, Image } from 'lucide-vue-next';
import { route } from 'ziggy-js';

const props = defineProps<{
    note: {
        id: number;
        title: string;
        course_name: string | null;
        course_code: string | null;
        file_type: string;
        file_url: string;
        file_size: number;
        formatted_size: string;
        created_at: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Notes', href: route('admin.notes.index') },
    { title: props.note.title, href: route('admin.notes.show', props.note.id) },
    { title: 'Edit', href: route('admin.notes.edit', props.note.id) },
];

const form = useForm({
    title: props.note.title,
    course_name: props.note.course_name || '',
    course_code: props.note.course_code || '',
});

// Get file icon component
const getFileIcon = (fileType: string) => {
    switch (fileType) {
        case 'pdf':
            return FileText;
        case 'image':
            return Image;
        default:
            return File;
    }
};

// Get file type badge classes
const getFileTypeBadgeClasses = (fileType: string) => {
    return {
        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200':
            fileType === 'pdf',
        'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200':
            fileType === 'image',
        'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200':
            fileType === 'other',
    };
};

// Get file type classes for background
const getFileTypeClasses = (fileType: string) => {
    return {
        'bg-red-50 dark:bg-red-900/20': fileType === 'pdf',
        'bg-blue-50 dark:bg-blue-900/20': fileType === 'image',
        'bg-gray-50 dark:bg-gray-900/20': fileType === 'other',
    };
};

// Get file icon color
const getFileIconColor = (fileType: string) => {
    return {
        'text-red-600 dark:text-red-400': fileType === 'pdf',
        'text-blue-600 dark:text-blue-400': fileType === 'image',
        'text-gray-600 dark:text-gray-400': fileType === 'other',
    };
};

// Format date
const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

// Submit form
const submitForm = () => {
    form.put(route('admin.notes.update', props.note.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Success message could be shown here
        },
    });
};
</script>
