<template>
    <Head title="Upload New Note" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-2xl">
            <div
                class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
            >
                <!-- Header -->
                <div class="mb-8">
                    <h2
                        class="text-2xl font-semibold text-gray-800 dark:text-gray-100"
                    >
                        Upload New Note
                    </h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Upload lecture notes, study materials, or any
                        course-related files
                    </p>
                </div>

                <!-- Upload Form -->
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
                            placeholder="e.g., Calculus Chapter 3 Notes"
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
                                placeholder="e.g., Calculus I"
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
                                placeholder="e.g., MATH101"
                                class="w-full rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                            />
                        </div>
                    </div>

                    <!-- File Upload -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            File *
                        </label>

                        <!-- Upload Area -->
                        <div
                            @dragover.prevent="dragOver = true"
                            @dragleave="dragOver = false"
                            @drop.prevent="handleFileDrop"
                            class="relative cursor-pointer rounded-lg border-2 border-dashed transition-colors"
                            :class="[
                                dragOver
                                    ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/10'
                                    : 'border-gray-300 hover:border-amber-400 dark:border-gray-600',
                                form.errors.file ? 'border-red-500' : '',
                            ]"
                        >
                            <input
                                ref="fileInput"
                                type="file"
                                @change="handleFileSelect"
                                class="absolute inset-0 z-50 h-full w-full cursor-pointer opacity-0"
                                accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx,.txt"
                            />

                            <div class="p-8 text-center">
                                <div v-if="!selectedFile" class="space-y-4">
                                    <div
                                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700"
                                    >
                                        <Upload class="h-8 w-8 text-gray-400" />
                                    </div>
                                    <div>
                                        <p
                                            class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                        >
                                            Drag & drop your file here
                                        </p>
                                        <p
                                            class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                                        >
                                            or click to browse
                                        </p>
                                    </div>
                                    <p class="text-xs text-gray-400">
                                        Max file size:
                                        {{ formatFileSize(maxFileSize) }}
                                    </p>
                                </div>

                                <!-- Selected File Preview -->
                                <div v-else class="space-y-4">
                                    <div
                                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-lg"
                                        :class="
                                            getFileTypeClasses(
                                                selectedFile.type,
                                            )
                                        "
                                    >
                                        <component
                                            :is="getFileIcon(selectedFile.type)"
                                            class="h-8 w-8"
                                            :class="
                                                getFileIconColor(
                                                    selectedFile.type,
                                                )
                                            "
                                        />
                                    </div>
                                    <div>
                                        <p
                                            class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                        >
                                            {{ selectedFile.name }}
                                        </p>
                                        <p
                                            class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                                        >
                                            {{
                                                formatFileSize(
                                                    selectedFile.size,
                                                )
                                            }}
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        @click="removeFile"
                                        class="inline-flex items-center gap-1 text-sm text-red-600 hover:text-red-700 dark:text-red-400"
                                    >
                                        <X class="h-4 w-4" />
                                        Remove File
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- File Info -->
                        <div class="mt-3 space-y-2">
                            <p
                                v-if="form.errors.file"
                                class="text-sm text-red-600"
                            >
                                {{ form.errors.file }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Supported formats: PDF, JPG, PNG, GIF, DOC,
                                DOCX, TXT
                            </p>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end gap-3 pt-6">
                        <Link
                            :href="route('admin.notes.index')"
                            class="rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing || !selectedFile"
                            class="inline-flex items-center gap-2 rounded-md bg-amber-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-amber-600 focus:ring-2 focus:ring-amber-500 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <Upload v-if="!form.processing" class="h-4 w-4" />
                            <svg
                                v-else
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
                            {{
                                form.processing ? 'Uploading...' : 'Upload Note'
                            }}
                        </button>
                    </div>
                </form>

                <!-- Upload Progress -->
                <div
                    v-if="uploadProgress > 0"
                    class="mt-6 space-y-2 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50"
                >
                    <div class="flex items-center justify-between text-sm">
                        <span
                            class="font-medium text-gray-700 dark:text-gray-300"
                        >
                            Upload Progress
                        </span>
                        <span class="text-gray-600 dark:text-gray-400">
                            {{ uploadProgress }}%
                        </span>
                    </div>
                    <div
                        class="h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700"
                    >
                        <div
                            class="h-full rounded-full bg-amber-500 transition-all duration-300"
                            :style="{ width: uploadProgress + '%' }"
                        ></div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Please don't close this page while uploading
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    File,
    FileSpreadsheet,
    FileText,
    Image,
    Upload,
    X,
} from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';

const props = defineProps<{
    max_file_size: number;
    allowed_types: string[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Notes', href: route('admin.notes.index') },
    { title: 'Upload Note', href: route('admin.notes.create') },
];

const form = useForm({
    title: '',
    course_name: '',
    course_code: '',
    file: null as File | null,
});

const fileInput = ref<HTMLInputElement | null>(null);
const selectedFile = ref<File | null>(null);
const dragOver = ref(false);
const uploadProgress = ref(0);

// Format file size
const formatFileSize = (bytes: number) => {
    if (bytes >= 1073741824) {
        return (bytes / 1073741824).toFixed(2) + ' GB';
    } else if (bytes >= 1048576) {
        return (bytes / 1048576).toFixed(2) + ' MB';
    } else if (bytes >= 1024) {
        return (bytes / 1024).toFixed(2) + ' KB';
    } else {
        return bytes + ' bytes';
    }
};

// Get file type from extension
const getFileTypeFromExtension = (filename: string) => {
    const extension = filename.split('.').pop()?.toLowerCase() || '';
    const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
    const pdfExtensions = ['pdf'];
    const docExtensions = ['doc', 'docx'];
    const spreadsheetExtensions = ['xls', 'xlsx', 'csv'];

    if (imageExtensions.includes(extension)) return 'image';
    if (pdfExtensions.includes(extension)) return 'pdf';
    if (docExtensions.includes(extension)) return 'document';
    if (spreadsheetExtensions.includes(extension)) return 'spreadsheet';
    return 'other';
};

// Get file icon component
const getFileIcon = (filename: string) => {
    const fileType = getFileTypeFromExtension(filename);
    switch (fileType) {
        case 'pdf':
            return FileText;
        case 'image':
            return Image;
        case 'document':
            return File;
        case 'spreadsheet':
            return FileSpreadsheet;
        default:
            return File;
    }
};

// Get file type classes for background
const getFileTypeClasses = (filename: string) => {
    const fileType = getFileTypeFromExtension(filename);
    return {
        'bg-red-50 dark:bg-red-900/20': fileType === 'pdf',
        'bg-blue-50 dark:bg-blue-900/20': fileType === 'image',
        'bg-green-50 dark:bg-green-900/20': fileType === 'document',
        'bg-purple-50 dark:bg-purple-900/20': fileType === 'spreadsheet',
        'bg-gray-50 dark:bg-gray-900/20': fileType === 'other',
    };
};

// Get file icon color
const getFileIconColor = (filename: string) => {
    const fileType = getFileTypeFromExtension(filename);
    return {
        'text-red-600 dark:text-red-400': fileType === 'pdf',
        'text-blue-600 dark:text-blue-400': fileType === 'image',
        'text-green-600 dark:text-green-400': fileType === 'document',
        'text-purple-600 dark:text-purple-400': fileType === 'spreadsheet',
        'text-gray-600 dark:text-gray-400': fileType === 'other',
    };
};

// Handle file selection
const handleFileSelect = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files[0]) {
        const file = input.files[0];

        // Validate file size
        if (file.size > props.max_file_size) {
            alert(
                `File size exceeds maximum limit of ${formatFileSize(props.max_file_size)}`,
            );
            input.value = '';
            return;
        }

        // Validate file type
        const extension = file.name.split('.').pop()?.toLowerCase() || '';
        if (!props.allowed_types.includes(extension)) {
            alert(
                `File type .${extension} is not supported. Allowed types: ${props.allowed_types.join(', ')}`,
            );
            input.value = '';
            return;
        }

        selectedFile.value = file;
        form.file = file;
    }
};

// Handle file drop
const handleFileDrop = (event: DragEvent) => {
    dragOver.value = false;

    if (event.dataTransfer?.files && event.dataTransfer.files[0]) {
        const file = event.dataTransfer.files[0];

        // Validate file size
        if (file.size > props.max_file_size) {
            alert(
                `File size exceeds maximum limit of ${formatFileSize(props.max_file_size)}`,
            );
            return;
        }

        // Validate file type
        const extension = file.name.split('.').pop()?.toLowerCase() || '';
        if (!props.allowed_types.includes(extension)) {
            alert(
                `File type .${extension} is not supported. Allowed types: ${props.allowed_types.join(', ')}`,
            );
            return;
        }

        selectedFile.value = file;
        form.file = file;
    }
};

// Remove selected file
const removeFile = () => {
    selectedFile.value = null;
    form.file = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

// Submit form
const submitForm = () => {
    if (!selectedFile.value) {
        alert('Please select a file to upload');
        return;
    }

    // Simulate upload progress (in real app, this would come from your upload handler)
    uploadProgress.value = 10;
    const progressInterval = setInterval(() => {
        if (uploadProgress.value < 90) {
            uploadProgress.value += 10;
        }
    }, 300);

    form.post(route('admin.notes.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            clearInterval(progressInterval);
            uploadProgress.value = 100;
        },
        onError: () => {
            clearInterval(progressInterval);
            uploadProgress.value = 0;
        },
        onFinish: () => {
            clearInterval(progressInterval);
            setTimeout(() => {
                uploadProgress.value = 0;
            }, 1000);
        },
    });
};

// Prevent accidental navigation during upload
onMounted(() => {
    const handleBeforeUnload = (e: BeforeUnloadEvent) => {
        if (form.processing) {
            e.preventDefault();
            e.returnValue = '';
        }
    };

    window.addEventListener('beforeunload', handleBeforeUnload);

    onUnmounted(() => {
        window.removeEventListener('beforeunload', handleBeforeUnload);
    });
});
</script>

<style scoped>
/* Custom file input styling */
input[type='file']::-webkit-file-upload-button {
    display: none;
}

input[type='file']::file-selector-button {
    display: none;
}

/* Smooth transitions */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
