<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    BookOpen,
    Calendar,
    ChevronDown,
    Download,
    Edit,
    Eye,
    File,
    FileText,
    Filter,
    Hash,
    Image,
    Link as LinkIcon,
    Plus,
    Trash,
    X,
} from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { route } from 'ziggy-js';

// Props
const props = defineProps<{
    notes?: {
        data: {
            id: number;
            title: string;
            course_name: string | null;
            course_code: string | null;
            file_type: string;
            file_url: string;
            file_size: number;
            formatted_size: string;
            file_icon: string;
            created_at: string;
        }[];
        links: any[];
        meta: any;
    };
    filters: {
        course_name: string | null;
        course_code: string | null;
        title: string | null;
        file_type: string | null;
    };
    filterOptions: {
        course_names: string[];
        course_codes: string[];
        file_types: string[];
    };
    stats: {
        total_notes: number;
        pdf_notes: number;
        image_notes: number;
        courses_with_notes: number;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Notes Management', href: route('admin.notes.index') },
];

// Local filters state
const localFilters = ref({
    course_name: props.filters?.course_name ?? '',
    course_code: props.filters?.course_code ?? '',
    title: props.filters?.title ?? '',
    file_type: props.filters?.file_type ?? '',
});

const showFilters = ref(false);

// Apply filters
const applyFilters = () => {
    router.get(route('admin.notes.index'), localFilters.value, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

// Reset filters
const resetFilters = () => {
    localFilters.value = {
        course_name: '',
        course_code: '',
        title: '',
        file_type: '',
    };
    applyFilters();
};

// Watch for filter changes
watch(
    localFilters,
    () => {
        applyFilters();
    },
    { deep: true, immediate: false },
);

// File type badge classes
function getFileTypeBadgeClasses(fileType: string) {
    return {
        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200':
            fileType === 'pdf',
        'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200':
            fileType === 'image',
        'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200':
            fileType === 'other',
    };
}

// File type card background classes
function getFileTypeClasses(fileType: string) {
    return {
        'bg-red-50 dark:bg-red-900/20': fileType === 'pdf',
        'bg-blue-50 dark:bg-blue-900/20': fileType === 'image',
        'bg-gray-50 dark:bg-gray-900/20': fileType === 'other',
    };
}

// File icon color
function getFileIconColor(fileType: string) {
    return {
        'text-red-600 dark:text-red-400': fileType === 'pdf',
        'text-blue-600 dark:text-blue-400': fileType === 'image',
        'text-gray-600 dark:text-gray-400': fileType === 'other',
    };
}

// Get file icon component
function getFileIconComponent(iconName: string) {
    switch (iconName) {
        case 'FileText':
            return FileText;
        case 'Image':
            return Image;
        default:
            return File;
    }
}

// Format date
function formatDate(dateString: string) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

// Copy link to clipboard
function copyLink(url: string) {
    navigator.clipboard
        .writeText(url)
        .then(() => {
            // Show success message (you can add a toast notification here)
            alert('Link copied to clipboard!');
        })
        .catch((err) => {
            console.error('Failed to copy link: ', err);
        });
}

// Delete note
const deleteNote = (noteId: number) => {
    if (
        confirm(
            'Are you sure you want to delete this note? This action cannot be undone.',
        )
    ) {
        router.delete(route('admin.notes.destroy', noteId), {
            preserveScroll: true,
            onSuccess: () => {
                // Show success message
            },
        });
    }
};

// Export notes
const exportNotes = () => {
    const params = new URLSearchParams();
    Object.entries(localFilters.value).forEach(([key, value]) => {
        if (value) {
            params.append(key, value.toString());
        }
    });

    window.location.href = `${route('admin.notes.export')}?${params.toString()}`;
};
</script>

<template>
    <Head title="Notes Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex flex-col gap-4 rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
        >
            <!-- Header -->
            <div
                class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center"
            >
                <div>
                    <h2
                        class="text-2xl font-semibold text-gray-800 dark:text-gray-100"
                    >
                        Notes Management
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Manage and organize all your uploaded notes
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button
                        @click="exportNotes"
                        class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                    >
                        <Download class="h-4 w-4" />
                        Export CSV
                    </button>
                    <button
                        @click="showFilters = !showFilters"
                        class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                    >
                        <Filter class="h-4 w-4" />
                        Filters
                        <ChevronDown
                            class="h-4 w-4 transition-transform"
                            :class="{ 'rotate-180': showFilters }"
                        />
                    </button>
                    <Link
                        :href="route('admin.notes.create')"
                        class="inline-flex items-center gap-2 rounded-md bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600 focus:ring-2 focus:ring-amber-400 focus:outline-none"
                    >
                        <Plus class="h-4 w-4" />
                        Upload Note
                    </Link>
                </div>
            </div>

            <!-- Stats Summary -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div
                    class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-sm font-medium text-gray-600 dark:text-gray-400"
                            >
                                Total Notes
                            </p>
                            <p
                                class="text-2xl font-bold text-gray-900 dark:text-gray-100"
                            >
                                {{ stats.total_notes }}
                            </p>
                        </div>
                        <div
                            class="rounded-lg bg-amber-100 p-2 dark:bg-amber-900"
                        >
                            <FileText
                                class="h-6 w-6 text-amber-600 dark:text-amber-400"
                            />
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-sm font-medium text-gray-600 dark:text-gray-400"
                            >
                                PDF Notes
                            </p>
                            <p
                                class="text-2xl font-bold text-blue-600 dark:text-blue-400"
                            >
                                {{ stats.pdf_notes }}
                            </p>
                        </div>
                        <div
                            class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900"
                        >
                            <FileText
                                class="h-6 w-6 text-blue-600 dark:text-blue-400"
                            />
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-sm font-medium text-gray-600 dark:text-gray-400"
                            >
                                Image Notes
                            </p>
                            <p
                                class="text-2xl font-bold text-green-600 dark:text-green-400"
                            >
                                {{ stats.image_notes }}
                            </p>
                        </div>
                        <div
                            class="rounded-lg bg-green-100 p-2 dark:bg-green-900"
                        >
                            <Image
                                class="h-6 w-6 text-green-600 dark:text-green-400"
                            />
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-sm font-medium text-gray-600 dark:text-gray-400"
                            >
                                Courses with Notes
                            </p>
                            <p
                                class="text-2xl font-bold text-purple-600 dark:text-purple-400"
                            >
                                {{ stats.courses_with_notes }}
                            </p>
                        </div>
                        <div
                            class="rounded-lg bg-purple-100 p-2 dark:bg-purple-900"
                        >
                            <BookOpen
                                class="h-6 w-6 text-purple-600 dark:text-purple-400"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Panel -->
            <div
                v-if="showFilters"
                class="rounded-lg border border-gray-200 bg-white p-4 transition-all duration-300 dark:border-gray-700 dark:bg-gray-800"
            >
                <div class="mb-4 flex items-center justify-between">
                    <h3
                        class="text-lg font-medium text-gray-900 dark:text-gray-100"
                    >
                        Filter Notes
                    </h3>
                    <button
                        @click="resetFilters"
                        class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200"
                    >
                        <X class="h-4 w-4" />
                        Clear All
                    </button>
                </div>

                <div
                    class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4"
                >
                    <!-- Course Name Filter -->
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Course Name
                        </label>
                        <select
                            v-model="localFilters.course_name"
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                        >
                            <option value="">All Course Names</option>
                            <option
                                v-for="course in filterOptions.course_names"
                                :key="course"
                                :value="course"
                            >
                                {{ course }}
                            </option>
                        </select>
                    </div>

                    <!-- Course Code Filter -->
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Course Code
                        </label>
                        <select
                            v-model="localFilters.course_code"
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                        >
                            <option value="">All Course Codes</option>
                            <option
                                v-for="code in filterOptions.course_codes"
                                :key="code"
                                :value="code"
                            >
                                {{ code }}
                            </option>
                        </select>
                    </div>

                    <!-- Note Title Filter -->
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Note Title
                        </label>
                        <input
                            v-model="localFilters.title"
                            type="text"
                            placeholder="Search by title..."
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                        />
                    </div>

                    <!-- File Type Filter -->
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            File Type
                        </label>
                        <select
                            v-model="localFilters.file_type"
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                        >
                            <option value="">All File Types</option>
                            <option
                                v-for="type in filterOptions.file_types"
                                :key="type"
                                :value="type"
                            >
                                {{ type.toUpperCase() }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Notes Grid -->
            <div
                v-if="notes?.data?.length"
                class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            >
                <div
                    v-for="note in notes.data"
                    :key="note.id"
                    class="group relative overflow-hidden rounded-xl border border-gray-200 bg-white transition-all hover:shadow-lg dark:border-gray-700 dark:bg-gray-800"
                >
                    <!-- Card Content -->
                    <div class="p-5">
                        <!-- File Type Icon -->
                        <div
                            class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg"
                            :class="getFileTypeClasses(note.file_type)"
                        >
                            <component
                                :is="getFileIconComponent(note.file_icon)"
                                class="h-6 w-6"
                                :class="getFileIconColor(note.file_type)"
                            />
                        </div>

                        <!-- Note Title -->
                        <h3
                            class="mb-2 truncate text-lg font-semibold text-gray-900 group-hover:text-amber-600 dark:text-gray-100"
                        >
                            {{ note.title }}
                        </h3>

                        <!-- Course Info -->
                        <div class="mb-4 space-y-2">
                            <div
                                v-if="note.course_name"
                                class="flex items-center gap-2 text-sm"
                            >
                                <BookOpen class="h-4 w-4 text-gray-400" />
                                <span class="text-gray-600 dark:text-gray-400">
                                    {{ note.course_name }}
                                </span>
                            </div>
                            <div
                                v-if="note.course_code"
                                class="flex items-center gap-2 text-sm"
                            >
                                <Hash class="h-4 w-4 text-gray-400" />
                                <span class="text-gray-600 dark:text-gray-400">
                                    {{ note.course_code }}
                                </span>
                            </div>
                        </div>

                        <!-- File Info -->
                        <div class="mb-4 space-y-2">
                            <div
                                class="flex items-center justify-between text-sm"
                            >
                                <div class="flex items-center gap-2">
                                    <File class="h-4 w-4 text-gray-400" />
                                    <span
                                        class="rounded-full px-2 py-1 text-xs font-medium"
                                        :class="
                                            getFileTypeBadgeClasses(
                                                note.file_type,
                                            )
                                        "
                                    >
                                        {{ note.file_type.toUpperCase() }}
                                    </span>
                                </div>
                                <span class="text-gray-500 dark:text-gray-400">
                                    {{ note.formatted_size }}
                                </span>
                            </div>
                            <div
                                class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400"
                            >
                                <Calendar class="h-4 w-4" />
                                {{ formatDate(note.created_at) }}
                            </div>
                        </div>

                        <!-- Actions -->
                        <div
                            class="flex items-center justify-between border-t border-gray-100 pt-4 dark:border-gray-700"
                        >
                            <a
                                :href="route('admin.notes.show', note.id)"
                                class="inline-flex items-center gap-2 text-sm font-medium text-amber-600 hover:text-amber-700 dark:text-amber-400"
                            >
                                <Eye class="h-4 w-4" />
                                View
                            </a>
                            <div class="flex items-center gap-2">
                                <button
                                    @click="copyLink(note.file_url)"
                                    class="rounded p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700"
                                    title="Copy Link"
                                >
                                    <LinkIcon class="h-4 w-4" />
                                </button>
                                <Link
                                    :href="route('admin.notes.edit', note.id)"
                                    class="rounded p-1.5 text-blue-500 hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-900/20"
                                    title="Edit Note"
                                >
                                    <Edit class="h-4 w-4" />
                                </Link>
                                <button
                                    @click="deleteNote(note.id)"
                                    class="rounded p-1.5 text-red-500 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20"
                                    title="Delete Note"
                                >
                                    <Trash class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-else
                class="rounded-xl border border-sidebar-border/70 py-16 text-center dark:border-sidebar-border"
            >
                <div
                    class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400"
                >
                    <FileText class="mb-4 h-16 w-16 opacity-50" />
                    <p class="mb-2 text-lg font-medium">No notes found</p>
                    <p class="mb-4 text-sm">
                        Try adjusting your filters or upload your first note.
                    </p>
                    <Link
                        :href="route('admin.notes.create')"
                        class="inline-flex items-center gap-2 rounded-md bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600"
                    >
                        <Plus class="h-4 w-4" />
                        Upload First Note
                    </Link>
                </div>
            </div>

            <!-- Pagination -->
            <div
                v-if="notes?.data?.length > 0 && notes?.meta"
                class="flex items-center justify-between"
            >
                <div class="text-sm text-gray-700 dark:text-gray-300">
                    Showing {{ notes.meta.from ?? 0 }} to
                    {{ notes.meta.to ?? 0 }} of
                    {{ notes.meta.total ?? 0 }} results
                </div>
                <div class="flex gap-1">
                    <Link
                        v-for="link in notes.links || []"
                        :key="link.label"
                        :href="link.url || '#'"
                        :class="[
                            'rounded-md px-3 py-1 text-sm font-medium',
                            link.active
                                ? 'bg-amber-500 text-white'
                                : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700',
                            !link.url ? 'cursor-not-allowed opacity-50' : '',
                        ]"
                        :disabled="!link.url"
                        preserve-scroll
                    >
                        <span v-html="link.label"></span>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Smooth transitions */
.fade-enter-active,
.fade-leave-active {
    transition:
        opacity 0.3s ease,
        max-height 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    max-height: 0;
}

.fade-enter-to,
.fade-leave-from {
    opacity: 1;
    max-height: 500px;
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

.dark ::-webkit-scrollbar-track {
    background: #374151;
}

.dark ::-webkit-scrollbar-thumb {
    background: #6b7280;
}

.dark ::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}
</style>
