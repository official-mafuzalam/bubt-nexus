<script setup lang="ts">
import SubmissionForm from '@/components/assignments/SubmissionForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Download,
    Edit,
    Eye,
    FileText,
    User,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

// Import your Wayfinder route definitions
import { route } from 'ziggy-js';

const props = defineProps<{
    class?: any; // Could be string or object
    assignment?: any;
    isFaculty?: boolean;
    submission?: any;
    allSubmissions?: any; // Could be paginator object or array
}>();

// Preview modal state
const showPreviewModal = ref(false);
const previewAttachment = ref<any>(null);
const previewLoading = ref(true);
const textFileContent = ref('');

// File type detection functions
const isImageFile = (filename: string, type?: string): boolean => {
    if (type?.includes('image')) return true;
    const extensions = [
        '.jpg',
        '.jpeg',
        '.png',
        '.gif',
        '.bmp',
        '.svg',
        '.webp',
    ];
    return extensions.some((ext) => filename?.toLowerCase().endsWith(ext));
};

const isPdfFile = (filename: string, type?: string): boolean => {
    if (type?.includes('pdf')) return true;
    return filename?.toLowerCase().endsWith('.pdf');
};

// Plain text and code files that browsers can actually read
const isTextFile = (filename: string, type?: string): boolean => {
    if (type?.includes('text/plain') || type?.includes('application/json'))
        return true;
    const textExtensions = [
        '.txt',
        '.csv',
        '.json',
        '.xml',
        '.html',
        '.css',
        '.js',
        '.ts',
        '.php',
        '.vue',
        '.md',
        '.sql',
        '.log',
    ];
    return textExtensions.some((ext) => filename?.toLowerCase().endsWith(ext));
};

const isVideoFile = (filename: string, type?: string): boolean => {
    if (type?.includes('video')) return true;
    const videoExtensions = ['.mp4', '.webm', '.ogg', '.mov', '.avi', '.mkv'];
    return videoExtensions.some((ext) => filename?.toLowerCase().endsWith(ext));
};

const isAudioFile = (filename: string, type?: string): boolean => {
    if (type?.includes('audio')) return true;
    const audioExtensions = ['.mp3', '.wav', '.ogg', '.m4a', '.flac'];
    return audioExtensions.some((ext) => filename?.toLowerCase().endsWith(ext));
};

// Handle preview errors
const handlePreviewError = () => {
    previewLoading.value = false;
    console.error('Failed to load preview for:', previewAttachment.value?.name);
};

// Extract IDs from URL as fallback
const extractClassIdFromUrl = (): number => {
    try {
        const url = window.location.pathname;
        const match = url.match(/\/classes\/(\d+)\/assignments\/\d+/);
        return match ? parseInt(match[1]) : 0;
    } catch (error) {
        console.error('Error extracting class ID from URL:', error);
        return 0;
    }
};

const extractAssignmentIdFromUrl = (): number => {
    try {
        const url = window.location.pathname;
        const match = url.match(/\/classes\/\d+\/assignments\/(\d+)/);
        return match ? parseInt(match[1]) : 0;
    } catch (error) {
        console.error('Error extracting assignment ID from URL:', error);
        return 0;
    }
};

const extractedClassId = computed(() => extractClassIdFromUrl());
const extractedAssignmentId = computed(() => extractAssignmentIdFromUrl());

// Safe computed properties
const safeAssignment = computed(() => {
    if (props.assignment && typeof props.assignment === 'object') {
        // Parse attachments if they're stored as JSON string
        let attachments = props.assignment.attachments || [];
        if (typeof attachments === 'string') {
            try {
                attachments = JSON.parse(attachments);
            } catch (e) {
                attachments = [];
            }
        }

        return {
            id: props.assignment.id || extractedAssignmentId.value,
            title: props.assignment.title || 'Untitled Assignment',
            description: props.assignment.description || '',
            instructions: props.assignment.instructions || '',
            total_marks: props.assignment.total_marks || 0,
            deadline: props.assignment.deadline || '',
            status: props.assignment.status || 'draft',
            attachments: attachments,
            submissions_count: props.assignment.submissions_count || 0,
        };
    }
    return {
        id: extractedAssignmentId.value,
        title: 'Assignment',
        description: '',
        total_marks: 0,
        deadline: '',
        status: 'draft',
        attachments: [],
        submissions_count: 0,
    };
});

const isFaculty = computed(() => props.isFaculty || false);

const submission = computed(() => {
    if (props.submission && typeof props.submission === 'object') {
        return props.submission;
    }
    return null;
});

// Format file size
const formatFileSize = (bytes: number): string => {
    if (!bytes || bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
};

// Get file icon component based on type
const getFileIcon = (type: string, filename: string) => {
    if (isImageFile(filename, type)) return FileText;
    if (isPdfFile(filename, type)) return FileText;
    if (isTextFile(filename, type)) return FileText;
    if (isVideoFile(filename, type)) return FileText;
    if (isAudioFile(filename, type)) return FileText;
    return FileText;
};

// Main check: Should we even show the preview button?
const canPreview = (filename: string, type?: string): boolean => {
    return (
        isImageFile(filename, type) ||
        isPdfFile(filename, type) ||
        isTextFile(filename, type)
    );
};

// Open preview modal
const openPreview = (attachment: any) => {
    if (!canPreview(attachment.name, attachment.type)) return;

    previewAttachment.value = attachment;
    showPreviewModal.value = true;
    previewLoading.value = true;

    if (isTextFile(attachment.name, attachment.type)) {
        fetch(attachment.url || `/storage/${attachment.path}`)
            .then((response) => response.text())
            .then((text) => {
                textFileContent.value = text;
                previewLoading.value = false;
            })
            .catch(() => {
                textFileContent.value = 'Error loading file content.';
                previewLoading.value = false;
            });
    } else {
        // Images and PDFs load via browser native elements
        previewLoading.value = false;
    }
};

// Close preview modal
const closePreview = () => {
    showPreviewModal.value = false;
    previewAttachment.value = null;
    textFileContent.value = '';
    previewLoading.value = true;
};

const safeAllSubmissionsData = computed(() => {
    if (!props.allSubmissions) return [];

    if (props.allSubmissions.data) {
        return props.allSubmissions.data;
    }

    if (Array.isArray(props.allSubmissions)) {
        return props.allSubmissions;
    }

    if (typeof props.allSubmissions === 'object') {
        if (
            'Illuminate\\Pagination\\LengthAwarePaginator' in
            props.allSubmissions
        ) {
            return (
                props.allSubmissions[
                    'Illuminate\\Pagination\\LengthAwarePaginator'
                ].data || []
            );
        }
    }

    return [];
});

// Generate URLs using extracted IDs
const assignmentsIndexUrl = computed(() => {
    return route('admin.assignments.index', { class: extractedClassId.value });
});

const editAssignmentUrl = computed(() => {
    return route('admin.assignments.edit', {
        class: extractedClassId.value,
        assignment: safeAssignment.value.id,
    });
});

const statusClasses = computed(() => {
    const status = safeAssignment.value.status;
    const classes: Record<string, string> = {
        draft: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        published:
            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        closed: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    };
    return classes[status] || classes.draft;
});

const submissionStatusClasses = computed(() => {
    if (!submission.value) return '';

    if (submission.value.status === 'graded') {
        return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
    } else if (submission.value.status === 'late') {
        return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
    } else if (submission.value.status === 'submitted') {
        return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
    }
    return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
});

const timeLeftClass = computed(() => {
    const deadline = new Date(safeAssignment.value.deadline);
    if (isNaN(deadline.getTime())) return 'text-gray-900 dark:text-gray-100';

    const now = new Date();
    const diffHours = Math.floor(
        (deadline.getTime() - now.getTime()) / (1000 * 60 * 60),
    );

    if (diffHours < 0) return 'text-red-600 dark:text-red-400';
    if (diffHours < 24) return 'text-amber-600 dark:text-amber-400';
    return 'text-gray-900 dark:text-gray-100';
});

const timeLeftText = computed(() => {
    const deadline = new Date(safeAssignment.value.deadline);
    if (isNaN(deadline.getTime())) return 'No deadline';

    const now = new Date();
    const diffMs = deadline.getTime() - now.getTime();

    if (diffMs < 0) return 'Overdue';

    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
    const diffHours = Math.floor(
        (diffMs % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60),
    );

    if (diffDays > 0) return `${diffDays}d ${diffHours}h left`;
    if (diffHours > 0) return `${diffHours}h left`;
    return '< 1h left';
});

const getStatusClasses = (status: string) => {
    const classes: Record<string, string> = {
        pending:
            'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        submitted:
            'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        graded: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        late: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    };
    return classes[status] || classes.pending;
};

const formatDate = (dateString: string) => {
    if (!dateString) return 'N/A';
    try {
        return new Date(dateString).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    } catch (error) {
        return 'Invalid date';
    }
};

const toggleAssignmentStatus = () => {
    const newStatus =
        safeAssignment.value.status === 'closed' ? 'published' : 'closed';
    const action =
        safeAssignment.value.status === 'closed' ? 'reopen' : 'close';

    if (confirm(`Are you sure you want to ${action} this assignment?`)) {
        router.put(
            route('admin.assignments.status', {
                class: extractedClassId.value,
                assignment: safeAssignment.value.id,
            }),
            { status: newStatus },
            { preserveScroll: true },
        );
    }
};

const handleSubmission = () => {
    router.reload({ preserveScroll: true });
};

const gradeSubmission = (submissionItem: any) => {
    console.log('Grade submission:', submissionItem);
};
</script>

<template>
    <AppLayout>
        <div class="container mx-auto px-4 py-8">
            <!-- Loading State -->
            <div v-if="!safeAssignment.id" class="py-12 text-center">
                <div
                    class="inline-block h-8 w-8 animate-spin rounded-full border-b-2 border-amber-500"
                ></div>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Loading assignment details...
                </p>
            </div>

            <!-- Content -->
            <div v-else>
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <Link
                                :href="assignmentsIndexUrl"
                                class="flex items-center gap-2 text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                            >
                                <ArrowLeft class="h-4 w-4" />
                                Back to Assignments
                            </Link>
                            <div
                                class="h-6 w-px bg-gray-300 dark:bg-gray-600"
                            ></div>
                            <h1
                                class="text-3xl font-bold text-gray-900 dark:text-white"
                            >
                                {{ safeAssignment.title || 'Assignment' }}
                            </h1>
                        </div>
                        <div v-if="isFaculty" class="flex gap-2">
                            <!-- Edit Button -->
                            <Link
                                :href="editAssignmentUrl"
                                class="inline-flex items-center gap-2 rounded-md bg-blue-500 px-4 py-2 text-sm font-medium text-white hover:bg-blue-600 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                            >
                                <Edit class="h-4 w-4" />
                                Edit
                            </Link>
                            <!-- Status Toggle Button -->
                            <button
                                @click="toggleAssignmentStatus"
                                class="rounded-md px-4 py-2 text-sm font-medium"
                                :class="
                                    safeAssignment.status === 'closed'
                                        ? 'bg-green-100 text-green-800 hover:bg-green-200 dark:bg-green-900 dark:text-green-200'
                                        : 'bg-red-100 text-red-800 hover:bg-red-200 dark:bg-red-900 dark:text-red-200'
                                "
                            >
                                {{
                                    safeAssignment.status === 'closed'
                                        ? 'Reopen'
                                        : 'Close'
                                }}
                            </button>
                        </div>
                    </div>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Due: {{ formatDate(safeAssignment.deadline) }}
                    </p>
                </div>

                <div class="grid gap-6 lg:grid-cols-3">
                    <!-- Left Column - Assignment Details -->
                    <div class="space-y-6 lg:col-span-2">
                        <!-- Assignment Details Card -->
                        <div
                            class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800"
                        >
                            <h3
                                class="mb-4 text-lg font-semibold text-gray-900 dark:text-white"
                            >
                                Assignment Details
                            </h3>

                            <!-- Description -->
                            <div class="mb-6">
                                <h4
                                    class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Description
                                </h4>
                                <div
                                    class="prose max-w-none text-gray-600 dark:text-gray-400"
                                >
                                    {{
                                        safeAssignment.description ||
                                        'No description provided'
                                    }}
                                </div>
                            </div>

                            <!-- Instructions -->
                            <div
                                v-if="safeAssignment.instructions"
                                class="mb-6"
                            >
                                <h4
                                    class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Instructions
                                </h4>
                                <div
                                    class="prose max-w-none text-gray-600 dark:text-gray-400"
                                >
                                    {{ safeAssignment.instructions }}
                                </div>
                            </div>

                            <!-- Assignment Attachments with Preview -->
                            <div
                                v-if="
                                    safeAssignment.attachments &&
                                    safeAssignment.attachments.length
                                "
                                class="mb-6"
                            >
                                <h4
                                    class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Attachments ({{
                                        safeAssignment.attachments.length
                                    }})
                                </h4>
                                <div class="space-y-2">
                                    <div
                                        v-for="(
                                            attachment, index
                                        ) in safeAssignment.attachments"
                                        :key="index"
                                        class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800"
                                    >
                                        <div
                                            class="flex flex-1 items-center gap-3"
                                        >
                                            <!-- File Icon -->
                                            <div
                                                class="rounded-md bg-blue-100 p-2 dark:bg-blue-900"
                                            >
                                                <FileText
                                                    class="h-5 w-5 text-blue-600 dark:text-blue-400"
                                                />
                                            </div>

                                            <!-- File Info -->
                                            <div class="flex-1">
                                                <p
                                                    class="text-sm font-medium text-gray-900 dark:text-white"
                                                >
                                                    {{ attachment.name }}
                                                </p>
                                                <p
                                                    class="text-xs text-gray-500 dark:text-gray-400"
                                                >
                                                    {{
                                                        formatFileSize(
                                                            attachment.size,
                                                        )
                                                    }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Action Buttons -->
                                        <!-- Action Buttons -->
                                        <div class="flex items-center gap-2">
                                            <!-- ONLY show Preview button if the file type is supported -->
                                            <button
                                                v-if="
                                                    canPreview(
                                                        attachment.name,
                                                        attachment.type,
                                                    )
                                                "
                                                @click="openPreview(attachment)"
                                                class="inline-flex items-center gap-1 rounded-md bg-purple-100 px-3 py-1.5 text-xs font-medium text-purple-700 hover:bg-purple-200 dark:bg-purple-900 dark:text-purple-200 dark:hover:bg-purple-800"
                                            >
                                                <Eye class="h-3 w-3" />
                                                Preview
                                            </button>

                                            <!-- Download Button (Always visible) -->
                                            <a
                                                :href="
                                                    attachment.url ||
                                                    `/storage/${attachment.path}`
                                                "
                                                :download="attachment.name"
                                                target="_blank"
                                                class="inline-flex items-center gap-1 rounded-md bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                                            >
                                                <Download class="h-3 w-3" />
                                                Download
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submission Info (For Students) -->
                            <div v-if="!isFaculty">
                                <div
                                    class="rounded-md border border-gray-200 p-4 dark:border-gray-600"
                                >
                                    <h4
                                        class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >
                                        Your Submission
                                    </h4>
                                    <div v-if="submission" class="space-y-4">
                                        <!-- Submission Status -->
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <div
                                                class=""
                                            >
                                                <div
                                                    class="flex items-center gap-2"
                                                >
                                                    <span
                                                        class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium"
                                                        :class="
                                                            submissionStatusClasses
                                                        "
                                                    >
                                                        {{ submission.status }}
                                                    </span>
                                                    <span
                                                        v-if="
                                                            submission.submitted_at
                                                        "
                                                        class="text-sm text-gray-600 dark:text-gray-400"
                                                    >
                                                        Submitted:
                                                        {{
                                                            formatDate(
                                                                submission.submitted_at,
                                                            )
                                                        }}
                                                    </span>
                                                </div>
                                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                                    Feedback: {{
                                                        submission.feedback ||
                                                        'No feedback provided'
                                                    }}
                                                </p>
                                            </div>
                                            <div
                                                v-if="
                                                    submission.marks_obtained !==
                                                    null
                                                "
                                                class="text-right"
                                            >
                                                <p
                                                    class="text-lg font-bold text-gray-900 dark:text-white"
                                                >
                                                    {{
                                                        submission.marks_obtained
                                                    }}/{{
                                                        safeAssignment.total_marks
                                                    }}
                                                </p>
                                                <p
                                                    class="text-sm text-gray-500 dark:text-gray-400"
                                                >
                                                    {{
                                                        (
                                                            (submission.marks_obtained /
                                                                safeAssignment.total_marks) *
                                                            100
                                                        ).toFixed(1)
                                                    }}%
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else>
                                        <p
                                            class="text-gray-600 dark:text-gray-400"
                                        >
                                            You haven't submitted this
                                            assignment yet.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submission Form (For Students) -->
                        <div
                            v-if="
                                !isFaculty && safeAssignment.status !== 'closed'
                            "
                        >
                            <SubmissionForm
                                :assignment="safeAssignment"
                                :class-id="extractedClassId"
                                :existing-submission="submission"
                                @submitted="handleSubmission"
                            />
                        </div>

                        <!-- Submission List (For Faculty) -->
                        <div v-if="isFaculty && safeAllSubmissionsData.length">
                            <div
                                class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800"
                            >
                                <div
                                    class="border-b border-gray-200 px-6 py-4 dark:border-gray-700"
                                >
                                    <h3
                                        class="text-lg font-semibold text-gray-900 dark:text-white"
                                    >
                                        Submissions ({{
                                            safeAllSubmissionsData.length
                                        }})
                                    </h3>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full">
                                        <thead>
                                            <tr
                                                class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-700"
                                            >
                                                <th
                                                    class="px-6 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300"
                                                >
                                                    Student
                                                </th>
                                                <th
                                                    class="px-6 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300"
                                                >
                                                    Status
                                                </th>
                                                <th
                                                    class="px-6 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300"
                                                >
                                                    Submitted
                                                </th>
                                                <th
                                                    class="px-6 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300"
                                                >
                                                    Marks
                                                </th>
                                                <th
                                                    class="px-6 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300"
                                                >
                                                    Actions
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody
                                            class="divide-y divide-gray-200 dark:divide-gray-700"
                                        >
                                            <tr
                                                v-for="submissionItem in safeAllSubmissionsData"
                                                :key="submissionItem.id"
                                                class="hover:bg-gray-50 dark:hover:bg-gray-800"
                                            >
                                                <td class="px-6 py-4">
                                                    <div
                                                        class="flex items-center gap-3"
                                                    >
                                                        <div
                                                            class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-200 dark:bg-gray-600"
                                                        >
                                                            <User
                                                                class="h-4 w-4 text-gray-600 dark:text-gray-400"
                                                            />
                                                        </div>
                                                        <div>
                                                            <p
                                                                class="font-medium text-gray-900 dark:text-white"
                                                            >
                                                                {{
                                                                    submissionItem
                                                                        .student
                                                                        ?.name ||
                                                                    'Unknown'
                                                                }}
                                                            </p>
                                                            <p
                                                                class="text-xs text-gray-500 dark:text-gray-400"
                                                            >
                                                                {{
                                                                    submissionItem
                                                                        .student
                                                                        ?.email ||
                                                                    ''
                                                                }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <span
                                                        class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium"
                                                        :class="
                                                            getStatusClasses(
                                                                submissionItem.status,
                                                            )
                                                        "
                                                    >
                                                        {{
                                                            submissionItem.status
                                                        }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <span
                                                        class="text-sm text-gray-600 dark:text-gray-400"
                                                    >
                                                        {{
                                                            submissionItem.submitted_at
                                                                ? formatDate(
                                                                      submissionItem.submitted_at,
                                                                  )
                                                                : 'Not submitted'
                                                        }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div
                                                        v-if="
                                                            submissionItem.marks_obtained !==
                                                                null &&
                                                            submissionItem.marks_obtained !==
                                                                undefined
                                                        "
                                                        class="flex items-center gap-2"
                                                    >
                                                        <span
                                                            class="font-medium text-gray-900 dark:text-white"
                                                        >
                                                            {{
                                                                submissionItem.marks_obtained
                                                            }}/{{
                                                                safeAssignment.total_marks
                                                            }}
                                                        </span>
                                                        <span
                                                            class="text-xs text-gray-500 dark:text-gray-400"
                                                        >
                                                            ({{
                                                                (
                                                                    (submissionItem.marks_obtained /
                                                                        safeAssignment.total_marks) *
                                                                    100
                                                                ).toFixed(1)
                                                            }}%)
                                                        </span>
                                                    </div>
                                                    <span
                                                        v-else
                                                        class="text-sm text-gray-500 dark:text-gray-400"
                                                    >
                                                        Not graded
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="flex gap-2">
                                                        <button
                                                            @click="
                                                                gradeSubmission(
                                                                    submissionItem,
                                                                )
                                                            "
                                                            class="rounded-md bg-green-100 px-3 py-1 text-xs font-medium text-green-700 hover:bg-green-200 dark:bg-green-900 dark:text-green-200"
                                                            title="Grade Submission"
                                                        >
                                                            Grade
                                                        </button>
                                                        <Link
                                                            :href="
                                                                route(
                                                                    'admin.assignments.submissions.view',
                                                                    {
                                                                        class: extractedClassId,
                                                                        assignment:
                                                                            safeAssignment.id,
                                                                        submission:
                                                                            submissionItem.id,
                                                                    },
                                                                )
                                                            "
                                                            class="rounded-md bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700 hover:bg-blue-200 dark:bg-blue-900 dark:text-blue-200"
                                                            title="View Submission"
                                                        >
                                                            View
                                                        </Link>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Sidebar -->
                    <div class="space-y-6">
                        <!-- Assignment Stats -->
                        <div
                            class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800"
                        >
                            <h3
                                class="mb-4 text-lg font-semibold text-gray-900 dark:text-white"
                            >
                                Assignment Stats
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <p
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        Status
                                    </p>
                                    <span
                                        class="mt-1 inline-flex items-center rounded-full px-3 py-1 text-sm font-medium"
                                        :class="statusClasses"
                                    >
                                        {{ safeAssignment.status }}
                                    </span>
                                </div>
                                <div>
                                    <p
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        Total Marks
                                    </p>
                                    <p
                                        class="text-2xl font-bold text-gray-900 dark:text-white"
                                    >
                                        {{ safeAssignment.total_marks }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        Deadline
                                    </p>
                                    <p
                                        class="font-medium text-gray-900 dark:text-white"
                                    >
                                        {{
                                            formatDate(safeAssignment.deadline)
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        Time Left
                                    </p>
                                    <p
                                        class="font-medium"
                                        :class="timeLeftClass"
                                    >
                                        {{ timeLeftText }}
                                    </p>
                                </div>
                                <div v-if="isFaculty">
                                    <p
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        Submissions
                                    </p>
                                    <p
                                        class="text-2xl font-bold text-gray-900 dark:text-white"
                                    >
                                        {{ safeAllSubmissionsData.length }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Modal -->
        <div
            v-if="showPreviewModal && previewAttachment"
            class="bg-opacity-75 fixed inset-0 z-50 flex items-center justify-center bg-black p-4"
            @click.self="closePreview"
        >
            <div
                class="relative max-h-[90vh] w-full max-w-4xl overflow-auto rounded-lg bg-white dark:bg-gray-800"
            >
                <!-- Modal Header -->
                <div
                    class="sticky top-0 flex items-center justify-between border-b border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800"
                >
                    <div class="min-w-0 flex-1">
                        <h3
                            class="truncate pr-4 text-lg font-semibold text-gray-900 dark:text-white"
                        >
                            {{ previewAttachment.name }}
                        </h3>
                        <p
                            class="mt-1 text-xs text-gray-500 dark:text-gray-400"
                        >
                            {{ formatFileSize(previewAttachment.size) }} •
                            {{ previewAttachment.type || 'Unknown type' }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <!-- Download button in modal -->
                        <a
                            :href="
                                previewAttachment.url ||
                                `/storage/${previewAttachment.path}`
                            "
                            :download="previewAttachment.name"
                            target="_blank"
                            class="inline-flex items-center gap-1 rounded-md bg-blue-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-600"
                            title="Download"
                        >
                            <Download class="h-3 w-3" />
                            Download
                        </a>
                        <button
                            @click="closePreview"
                            class="rounded-lg p-1 hover:bg-gray-100 dark:hover:bg-gray-700"
                        >
                            <X
                                class="h-5 w-5 text-gray-500 dark:text-gray-400"
                            />
                        </button>
                    </div>
                </div>

                <!-- Modal Content -->
                <!-- Modal Content -->
                <div class="flex min-h-[300px] items-center justify-center p-4">
                    <div v-if="previewLoading" class="py-8 text-center">
                        <div
                            class="inline-block h-8 w-8 animate-spin rounded-full border-b-2 border-amber-500"
                        ></div>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">
                            Loading preview...
                        </p>
                    </div>

                    <div v-else class="w-full">
                        <!-- Image Preview -->
                        <template
                            v-if="
                                isImageFile(
                                    previewAttachment.name,
                                    previewAttachment.type,
                                )
                            "
                        >
                            <img
                                :src="
                                    previewAttachment.url ||
                                    `/storage/${previewAttachment.path}`
                                "
                                class="mx-auto max-h-[70vh] w-auto max-w-full object-contain shadow-lg"
                            />
                        </template>

                        <!-- PDF Preview -->
                        <template
                            v-else-if="
                                isPdfFile(
                                    previewAttachment.name,
                                    previewAttachment.type,
                                )
                            "
                        >
                            <iframe
                                :src="`${previewAttachment.url || `/storage/${previewAttachment.path}`}#toolbar=0`"
                                class="h-[70vh] w-full rounded border dark:border-gray-700"
                            ></iframe>
                        </template>

                        <!-- Text File Preview -->
                        <template
                            v-else-if="
                                isTextFile(
                                    previewAttachment.name,
                                    previewAttachment.type,
                                )
                            "
                        >
                            <div
                                class="max-h-[70vh] overflow-auto rounded-lg bg-gray-50 p-4 dark:bg-gray-900"
                            >
                                <pre
                                    class="font-mono text-sm whitespace-pre-wrap text-gray-800 dark:text-gray-200"
                                    >{{ textFileContent }}</pre
                                >
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
