<template>
    <Head :title="note.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-4xl">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2
                            class="text-2xl font-semibold text-gray-800 dark:text-gray-100"
                        >
                            Note Details
                        </h2>
                        <p
                            class="mt-1 text-sm text-gray-600 dark:text-gray-400"
                        >
                            View and manage note information
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <!-- Only show Edit button if user is logged in and owns the note or is admin -->
                        <Link
                            v-if="canEdit"
                            :href="route('admin.notes.edit', note.id)"
                            class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                        >
                            <Edit class="h-4 w-4" />
                            Edit
                        </Link>
                        <Link
                            :href="route('admin.notes.index')"
                            class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                        >
                            <ArrowLeft class="h-4 w-4" />
                            Back
                        </Link>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Left Column: Note Info -->
                <div class="lg:col-span-2">
                    <div
                        class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                    >
                        <!-- File Preview Section -->
                        <div class="mb-8">
                            <div
                                class="mb-4 flex items-center justify-between border-b border-gray-200 pb-4 dark:border-gray-700"
                            >
                                <h3
                                    class="text-lg font-medium text-gray-900 dark:text-gray-100"
                                >
                                    File Preview
                                </h3>
                                <span
                                    class="rounded-full px-3 py-1 text-xs font-medium"
                                    :class="
                                        getFileTypeBadgeClasses(note.file_type)
                                    "
                                >
                                    {{ note.file_type.toUpperCase() }}
                                </span>
                            </div>

                            <!-- Preview Container -->
                            <div
                                class="flex flex-col items-center justify-center rounded-lg border border-gray-200 bg-gray-50 p-8 dark:border-gray-700 dark:bg-gray-800/50"
                            >
                                <!-- File Icon -->
                                <div
                                    class="mb-6 flex h-24 w-24 items-center justify-center rounded-2xl"
                                    :class="getFileTypeClasses(note.file_type)"
                                >
                                    <component
                                        :is="getFileIcon(note.file_type)"
                                        class="h-12 w-12"
                                        :class="
                                            getFileIconColor(note.file_type)
                                        "
                                    />
                                </div>

                                <!-- File Info -->
                                <div class="text-center">
                                    <h4
                                        class="mb-2 text-lg font-semibold text-gray-900 dark:text-gray-100"
                                    >
                                        {{ note.title }}
                                    </h4>
                                    <p
                                        class="mb-4 text-sm text-gray-600 dark:text-gray-400"
                                    >
                                        Size: {{ note.formatted_size }}
                                    </p>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex flex-wrap gap-3">
                                    <a
                                        :href="note.file_url"
                                        target="_blank"
                                        class="inline-flex items-center gap-2 rounded-md bg-blue-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-600"
                                    >
                                        <Eye class="h-4 w-4" />
                                        View in Browser
                                    </a>
                                    <a
                                        :href="
                                            route(
                                                'admin.notes.download',
                                                note.id,
                                            )
                                        "
                                        class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                                    >
                                        <Download class="h-4 w-4" />
                                        Download
                                    </a>
                                    <button
                                        @click="copyLink"
                                        class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                                    >
                                        <LinkIcon class="h-4 w-4" />
                                        Copy Link
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Note Details -->
                        <div>
                            <h3
                                class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100"
                            >
                                Note Information
                            </h3>
                            <div class="space-y-6">
                                <!-- Title -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-500 dark:text-gray-400"
                                    >
                                        Title
                                    </label>
                                    <p
                                        class="mt-1 text-sm text-gray-900 dark:text-gray-100"
                                    >
                                        {{ note.title }}
                                    </p>
                                </div>

                                <!-- Course Details -->
                                <div
                                    class="grid grid-cols-1 gap-6 md:grid-cols-2"
                                >
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-500 dark:text-gray-400"
                                        >
                                            Course Name
                                        </label>
                                        <p
                                            class="mt-1 text-sm text-gray-900 dark:text-gray-100"
                                        >
                                            {{
                                                note.course_name ||
                                                'Not specified'
                                            }}
                                        </p>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-500 dark:text-gray-400"
                                        >
                                            Course Code
                                        </label>
                                        <p
                                            class="mt-1 text-sm text-gray-900 dark:text-gray-100"
                                        >
                                            {{
                                                note.course_code ||
                                                'Not specified'
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Uploader Information -->
                                <div
                                    v-if="note.user"
                                    class="border-t border-gray-200 pt-6 dark:border-gray-700"
                                >
                                    <label
                                        class="mb-2 block text-sm font-medium text-gray-500 dark:text-gray-400"
                                    >
                                        Uploaded By
                                    </label>
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30"
                                        >
                                            <User
                                                class="h-5 w-5 text-amber-600 dark:text-amber-400"
                                            />
                                        </div>
                                        <div>
                                            <p
                                                class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                            >
                                                {{ note.user.name }}
                                            </p>
                                            <p
                                                v-if="note.user.email"
                                                class="text-xs text-gray-500 dark:text-gray-400"
                                            >
                                                {{ note.user.email }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- File Information -->
                                <div
                                    class="grid grid-cols-1 gap-6 md:grid-cols-2"
                                >
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-500 dark:text-gray-400"
                                        >
                                            File Type
                                        </label>
                                        <p
                                            class="mt-1 text-sm text-gray-900 dark:text-gray-100"
                                        >
                                            {{ note.file_type.toUpperCase() }}
                                        </p>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-500 dark:text-gray-400"
                                        >
                                            File Size
                                        </label>
                                        <p
                                            class="mt-1 text-sm text-gray-900 dark:text-gray-100"
                                        >
                                            {{ note.formatted_size }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Timestamps -->
                                <div
                                    class="grid grid-cols-1 gap-6 md:grid-cols-2"
                                >
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-500 dark:text-gray-400"
                                        >
                                            Created At
                                        </label>
                                        <p
                                            class="mt-1 text-sm text-gray-900 dark:text-gray-100"
                                        >
                                            {{ formatDate(note.created_at) }}
                                        </p>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-500 dark:text-gray-400"
                                        >
                                            Last Updated
                                        </label>
                                        <p
                                            class="mt-1 text-sm text-gray-900 dark:text-gray-100"
                                        >
                                            {{ formatDate(note.updated_at) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Actions & Stats -->
                <div class="space-y-6">
                    <!-- Quick Actions -->
                    <div
                        class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                    >
                        <h3
                            class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100"
                        >
                            Quick Actions
                        </h3>
                        <div class="space-y-3">
                            <!-- Only show Edit if user can edit -->
                            <Link
                                v-if="canEdit"
                                :href="route('admin.notes.edit', note.id)"
                                class="flex w-full items-center gap-3 rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                            >
                                <Edit class="h-4 w-4" />
                                Edit Note Details
                            </Link>
                            <a
                                :href="note.file_url"
                                target="_blank"
                                class="flex w-full items-center gap-3 rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                            >
                                <ExternalLink class="h-4 w-4" />
                                Open in New Tab
                            </a>
                            <button
                                @click="copyLink"
                                class="flex w-full items-center gap-3 rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                            >
                                <LinkIcon class="h-4 w-4" />
                                Copy Shareable Link
                            </button>
                            <!-- Only show Delete if user can edit -->
                            <button
                                v-if="canEdit"
                                @click="showDeleteModal = true"
                                class="flex w-full items-center gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 hover:bg-red-100 dark:border-red-700 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/30"
                            >
                                <Trash class="h-4 w-4" />
                                Delete Note
                            </button>
                        </div>
                    </div>

                    <!-- File URL -->
                    <div
                        class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                    >
                        <h3
                            class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100"
                        >
                            File URL
                        </h3>
                        <div class="relative">
                            <input
                                :value="note.file_url"
                                type="text"
                                readonly
                                class="w-full rounded-md border border-gray-300 bg-gray-50 px-4 py-2.5 pr-12 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                            />
                            <button
                                @click="copyLink"
                                class="absolute top-1/2 right-2 -translate-y-1/2 rounded-md p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                title="Copy URL"
                            >
                                <Copy class="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    <!-- Login Prompt for non-logged-in users -->
                    <div
                        v-if="!isLoggedIn"
                        class="rounded-xl border border-amber-200 bg-amber-50 p-6 dark:border-amber-700 dark:bg-amber-900/20"
                    >
                        <h3
                            class="mb-3 text-lg font-medium text-amber-800 dark:text-amber-300"
                        >
                            Want to contribute?
                        </h3>
                        <p
                            class="mb-4 text-sm text-amber-700 dark:text-amber-400"
                        >
                            Login to upload your own notes and help others!
                        </p>
                        <Link
                            :href="route('login')"
                            class="inline-flex items-center gap-2 rounded-md bg-amber-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-amber-600"
                        >
                            <LogIn class="h-4 w-4" />
                            Login to Contribute
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal
            v-if="showDeleteModal"
            @close="showDeleteModal = false"
            max-width="md"
        >
            <div class="p-6">
                <div class="mb-6 flex items-center gap-3">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30"
                    >
                        <Trash class="h-6 w-6 text-red-600 dark:text-red-400" />
                    </div>
                    <div>
                        <h3
                            class="text-lg font-semibold text-gray-900 dark:text-gray-100"
                        >
                            Delete Note
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Are you sure you want to delete this note?
                        </p>
                    </div>
                </div>

                <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">
                    This action will permanently delete "{{ note.title }}" and
                    remove the file from storage. This action cannot be undone.
                </p>

                <div class="flex justify-end gap-3">
                    <button
                        @click="showDeleteModal = false"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                    >
                        Cancel
                    </button>
                    <Link
                        :href="route('admin.notes.destroy', note.id)"
                        method="delete"
                        as="button"
                        class="rounded-md bg-red-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-red-600"
                        @click="showDeleteModal = false"
                    >
                        Delete Note
                    </Link>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>

<script setup lang="ts">
import Modal from '@/components/ui/Modal.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Copy,
    Download,
    Edit,
    ExternalLink,
    Eye,
    File,
    FileText,
    Image,
    Link as LinkIcon,
    LogIn,
    Trash,
    User,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
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
        updated_at: string;
        user?: {
            id: number;
            name: string;
            email: string;
        };
    };
}>();

// Get the page props using usePage()
const page = usePage();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Notes', href: route('admin.notes.index') },
    { title: props.note.title, href: route('admin.notes.show', props.note.id) },
];

const showDeleteModal = ref(false);

// Check if user is logged in
const isLoggedIn = computed(() => {
    return !!page.props.auth?.user;
});

// Check if current user can edit/delete this note
const canEdit = computed(() => {
    if (!isLoggedIn.value) {
        return false;
    }

    const authUser = page.props.auth.user;

    // User can edit if they own the note OR if they are admin
    // Check both possible admin property names (is_admin or isAdmin)
    const isAdmin = authUser.is_admin === true || authUser.isAdmin === true;

    return props.note.user?.id === authUser.id || isAdmin;
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
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Copy link to clipboard
const copyLink = async () => {
    try {
        await navigator.clipboard.writeText(props.note.file_url);

        // Show success message (you could use a toast notification here)
        alert('Link copied to clipboard!');
    } catch (err) {
        console.error('Failed to copy link: ', err);
        alert('Failed to copy link. Please try again.');
    }
};
</script>

<style scoped>
/* Custom scrollbar for long URLs */
input[readonly] {
    cursor: default;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
