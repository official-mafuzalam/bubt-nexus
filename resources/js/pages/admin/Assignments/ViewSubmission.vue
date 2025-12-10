<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Calendar,
    ChevronLeft,
    ChevronRight,
    Download,
    ExternalLink,
    File,
    FileArchive,
    FileAudio,
    FileImage,
    FileSpreadsheet,
    FileText,
    FileVideo,
    User,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { route as ziggyRoute } from 'ziggy-js';

const props = defineProps<{
    classData: any;
    assignment: any;
    submission: any;
    navigation: {
        next_submission_id: number | null;
        prev_submission_id: number | null;
        total_submissions: number;
        current_position: number;
    };
}>();

const form = useForm({
    marks_obtained: props.submission.marks_obtained || '',
    feedback: props.submission.feedback || '',
});

const isEditing = ref(false);

// Safe computed properties
const safeAssignment = computed(() => ({
    ...props.assignment,
    total_marks: props.assignment.total_marks || 0,
    attachments: props.assignment.attachments || [],
}));

const safeSubmission = computed(() => ({
    ...props.submission,
    marks_obtained: props.submission.marks_obtained || null,
    feedback: props.submission.feedback || '',
    attachments: props.submission.attachments || [],
}));

const safeClassData = computed(() => ({
    ...props.classData,
    name: props.classData?.name || 'Class',
}));

const formatDate = (dateString: string) => {
    if (!dateString) return 'N/A';
    try {
        return new Date(dateString).toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    } catch (error) {
        return 'Invalid date';
    }
};

const formatRelativeTime = (dateString: string) => {
    if (!dateString) return 'N/A';

    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffMinutes = Math.floor(diffMs / (1000 * 60));
    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

    if (diffMinutes < 1) return 'Just now';
    if (diffMinutes < 60) return `${diffMinutes} minutes ago`;
    if (diffHours < 24) return `${diffHours} hours ago`;
    if (diffDays === 1) return 'Yesterday';
    if (diffDays < 7) return `${diffDays} days ago`;

    return formatDate(dateString);
};

const getStatusClasses = (status: string) => {
    const classes: Record<string, string> = {
        submitted:
            'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        graded: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        late: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        pending:
            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    };
    return classes[status] || classes.pending;
};

const getGradeColor = () => {
    if (
        !safeSubmission.value.marks_obtained ||
        !safeAssignment.value.total_marks
    )
        return 'text-gray-600 dark:text-gray-400';

    const percentage =
        (safeSubmission.value.marks_obtained /
            safeAssignment.value.total_marks) *
        100;

    if (percentage >= 80) return 'text-green-600 dark:text-green-400';
    if (percentage >= 60) return 'text-blue-600 dark:text-blue-400';
    if (percentage >= 40) return 'text-amber-600 dark:text-amber-400';
    return 'text-red-600 dark:text-red-400';
};

const calculatePercentage = computed(() => {
    if (
        !safeSubmission.value.marks_obtained ||
        !safeAssignment.value.total_marks
    )
        return null;

    return (
        (safeSubmission.value.marks_obtained /
            safeAssignment.value.total_marks) *
        100
    ).toFixed(1);
});

const getFileIcon = (fileName: string, mimeType: string = '') => {
    if (!fileName) return File;

    const extension = fileName.split('.').pop()?.toLowerCase();
    const mime = mimeType.toLowerCase();

    // Check by mime type first
    if (mime.includes('pdf')) return FileText;
    if (mime.includes('word') || mime.includes('document')) return FileText;
    if (mime.includes('excel') || mime.includes('spreadsheet'))
        return FileSpreadsheet;
    if (mime.includes('powerpoint') || mime.includes('presentation'))
        return File;
    if (mime.includes('image')) return FileImage;
    if (mime.includes('video')) return FileVideo;
    if (mime.includes('audio')) return FileAudio;
    if (mime.includes('zip') || mime.includes('compressed')) return FileArchive;

    // Fallback to extension
    switch (extension) {
        case 'pdf':
            return FileText;
        case 'doc':
        case 'docx':
        case 'odt':
            return FileText;
        case 'ppt':
        case 'pptx':
        case 'odp':
            return File;
        case 'xls':
        case 'xlsx':
        case 'ods':
        case 'csv':
            return FileSpreadsheet;
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
        case 'bmp':
        case 'svg':
            return FileImage;
        case 'mp4':
        case 'avi':
        case 'mov':
        case 'wmv':
            return FileVideo;
        case 'mp3':
        case 'wav':
        case 'ogg':
            return FileAudio;
        case 'zip':
        case 'rar':
        case '7z':
        case 'tar':
        case 'gz':
            return FileArchive;
        default:
            return File;
    }
};

const getFileColor = (fileName: string, mimeType: string = '') => {
    if (!fileName) return 'text-gray-500';

    const extension = fileName.split('.').pop()?.toLowerCase();
    const mime = mimeType.toLowerCase();

    // Check by mime type first
    if (mime.includes('pdf')) return 'text-red-500 dark:text-red-400';
    if (mime.includes('word') || mime.includes('document'))
        return 'text-blue-500 dark:text-blue-400';
    if (mime.includes('excel') || mime.includes('spreadsheet'))
        return 'text-green-500 dark:text-green-400';
    if (mime.includes('powerpoint') || mime.includes('presentation'))
        return 'text-orange-500 dark:text-orange-400';
    if (mime.includes('image')) return 'text-purple-500 dark:text-purple-400';
    if (mime.includes('video')) return 'text-pink-500 dark:text-pink-400';
    if (mime.includes('audio')) return 'text-indigo-500 dark:text-indigo-400';
    if (mime.includes('zip') || mime.includes('compressed'))
        return 'text-yellow-500 dark:text-yellow-400';

    // Fallback to extension
    switch (extension) {
        case 'pdf':
            return 'text-red-500 dark:text-red-400';
        case 'doc':
        case 'docx':
        case 'odt':
            return 'text-blue-500 dark:text-blue-400';
        case 'ppt':
        case 'pptx':
        case 'odp':
            return 'text-orange-500 dark:text-orange-400';
        case 'xls':
        case 'xlsx':
        case 'ods':
        case 'csv':
            return 'text-green-500 dark:text-green-400';
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
        case 'bmp':
        case 'svg':
            return 'text-purple-500 dark:text-purple-400';
        case 'mp4':
        case 'avi':
        case 'mov':
        case 'wmv':
            return 'text-pink-500 dark:text-pink-400';
        case 'mp3':
        case 'wav':
        case 'ogg':
            return 'text-indigo-500 dark:text-indigo-400';
        case 'zip':
        case 'rar':
        case '7z':
        case 'tar':
        case 'gz':
            return 'text-yellow-500 dark:text-yellow-400';
        default:
            return 'text-gray-500 dark:text-gray-400';
    }
};

const formatFileSize = (bytes: number): string => {
    if (!bytes || bytes === 0) return '0 Bytes';

    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const getFileName = (attachment: any): string => {
    return (
        attachment.name ||
        attachment.original_name ||
        basename(attachment.path) ||
        'Attachment'
    );
};

const basename = (path: string): string => {
    return path.split('/').pop() || 'file';
};

const downloadFile = (attachment: any) => {
    if (attachment.url) {
        const link = document.createElement('a');
        link.href = attachment.url;
        link.download = getFileName(attachment);
        link.target = '_blank';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
};

const previewFile = (attachment: any) => {
    if (attachment.url) {
        window.open(attachment.url, '_blank');
    }
};

const toggleEditMode = () => {
    isEditing.value = !isEditing.value;
    if (isEditing.value) {
        form.marks_obtained = safeSubmission.value.marks_obtained || '';
        form.feedback = safeSubmission.value.feedback || '';
    }
};

const submitGrading = () => {
    form.put(
        ziggyRoute('admin.submissions.update', {
            class: safeClassData.value.id,
            assignment: safeAssignment.value.id,
            submission: safeSubmission.value.id,
        }),
        {
            preserveScroll: true,
            onSuccess: () => {
                isEditing.value = false;
            },
        },
    );
};

const navigateToSubmission = (submissionId: number | null) => {
    if (!submissionId) return;

    router.visit(
        ziggyRoute('admin.submissions.view', {
            class: safeClassData.value.id,
            assignment: safeAssignment.value.id,
            submission: submissionId,
        }),
    );
};

// Helper function to generate route URLs
const assignmentShowRoute = computed(() => {
    return ziggyRoute('admin.assignments.show', {
        class: safeClassData.value.id,
        assignment: safeAssignment.value.id,
    });
});
</script>

<template>
    <AppLayout>
        <div class="container mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <Link
                            :href="assignmentShowRoute"
                            class="flex items-center gap-2 text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                        >
                            <ArrowLeft class="h-4 w-4" />
                            Back to Assignment
                        </Link>
                        <div
                            class="h-6 w-px bg-gray-300 dark:bg-gray-600"
                        ></div>
                        <div>
                            <h1
                                class="text-2xl font-bold text-gray-900 dark:text-white"
                            >
                                Submission Review
                            </h1>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ assignment.title }} •
                                {{ safeClassData.name }}
                            </p>
                        </div>
                    </div>

                    <!-- Navigation Controls -->
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            {{ navigation.current_position }} of
                            {{ navigation.total_submissions }}
                        </span>
                        <div class="flex gap-2">
                            <button
                                @click="
                                    navigateToSubmission(
                                        navigation.prev_submission_id,
                                    )
                                "
                                :disabled="!navigation.prev_submission_id"
                                class="rounded-md p-2 transition-colors"
                                :class="
                                    navigation.prev_submission_id
                                        ? 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'
                                        : 'cursor-not-allowed bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-600'
                                "
                            >
                                <ChevronLeft class="h-5 w-5" />
                            </button>
                            <button
                                @click="
                                    navigateToSubmission(
                                        navigation.next_submission_id,
                                    )
                                "
                                :disabled="!navigation.next_submission_id"
                                class="rounded-md p-2 transition-colors"
                                :class="
                                    navigation.next_submission_id
                                        ? 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'
                                        : 'cursor-not-allowed bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-600'
                                "
                            >
                                <ChevronRight class="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Left Column - Submission Content -->
                <div class="lg:col-span-2">
                    <!-- Student Info Card -->
                    <div
                        class="mb-6 rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700"
                                >
                                    <User
                                        class="h-6 w-6 text-gray-600 dark:text-gray-400"
                                    />
                                </div>
                                <div>
                                    <h3
                                        class="text-lg font-semibold text-gray-900 dark:text-white"
                                    >
                                        {{ submission.student.name }}
                                    </h3>
                                    <p
                                        class="text-sm text-gray-600 dark:text-gray-400"
                                    >
                                        {{ submission.student.email }}
                                        <span
                                            v-if="
                                                submission.student.roll_number
                                            "
                                            class="ml-2"
                                        >
                                            • Roll No:
                                            {{ submission.student.roll_number }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span
                                    class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium"
                                    :class="getStatusClasses(submission.status)"
                                >
                                    {{ submission.status }}
                                </span>
                                <p
                                    v-if="submission.late_submission"
                                    class="mt-1 text-sm text-red-600 dark:text-red-400"
                                >
                                    Late Submission
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Submission Content -->
                    <div
                        class="mb-6 rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800"
                    >
                        <div
                            class="mb-6 flex items-center justify-between border-b border-gray-200 pb-4 dark:border-gray-700"
                        >
                            <h3
                                class="text-lg font-semibold text-gray-900 dark:text-white"
                            >
                                Submission Content
                            </h3>
                            <div
                                class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400"
                            >
                                <Calendar class="h-4 w-4" />
                                <span
                                    >Submitted
                                    {{
                                        formatRelativeTime(
                                            submission.submitted_at,
                                        )
                                    }}</span
                                >
                            </div>
                        </div>

                        <!-- Text Content -->
                        <div v-if="submission.content" class="mb-6">
                            <div class="prose dark:prose-invert max-w-none">
                                <div
                                    class="rounded-md bg-gray-50 p-4 whitespace-pre-wrap dark:bg-gray-900"
                                >
                                    {{ submission.content }}
                                </div>
                            </div>
                        </div>
                        <div
                            v-else
                            class="mb-6 rounded-md bg-gray-50 p-8 text-center dark:bg-gray-900"
                        >
                            <FileText
                                class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500"
                            />
                            <p class="mt-2 text-gray-600 dark:text-gray-400">
                                No written content submitted
                            </p>
                        </div>

                        <!-- Student Attachments -->
                        <div
                            v-if="
                                submission.attachments &&
                                submission.attachments.length
                            "
                            class="mt-8"
                        >
                            <h4
                                class="mb-4 text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Student Attachments ({{
                                    submission.attachments.length
                                }})
                            </h4>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div
                                    v-for="(
                                        attachment, index
                                    ) in submission.attachments"
                                    :key="index"
                                    class="flex items-center justify-between rounded-md border border-gray-200 bg-gray-50 p-4 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:hover:bg-gray-800"
                                >
                                    <div
                                        class="flex min-w-0 flex-1 items-center gap-3"
                                    >
                                        <component
                                            :is="
                                                getFileIcon(
                                                    getFileName(attachment),
                                                    attachment.mime_type,
                                                )
                                            "
                                            class="h-5 w-5 flex-shrink-0"
                                            :class="
                                                getFileColor(
                                                    getFileName(attachment),
                                                    attachment.mime_type,
                                                )
                                            "
                                        />
                                        <div class="min-w-0 flex-1">
                                            <p
                                                class="truncate text-sm font-medium text-gray-900 dark:text-white"
                                            >
                                                {{ getFileName(attachment) }}
                                            </p>
                                            <div
                                                class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400"
                                            >
                                                <span v-if="attachment.size">
                                                    {{
                                                        formatFileSize(
                                                            attachment.size,
                                                        )
                                                    }}
                                                </span>
                                                <span
                                                    v-if="attachment.mime_type"
                                                    class="truncate"
                                                >
                                                    • {{ attachment.mime_type }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ml-2 flex flex-shrink-0 gap-1">
                                        <button
                                            @click="downloadFile(attachment)"
                                            class="rounded-md p-2 text-gray-500 hover:bg-gray-200 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800"
                                            title="Download"
                                        >
                                            <Download class="h-4 w-4" />
                                        </button>
                                        <button
                                            v-if="attachment.url"
                                            @click="previewFile(attachment)"
                                            class="rounded-md p-2 text-gray-500 hover:bg-gray-200 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800"
                                            title="Preview"
                                        >
                                            <ExternalLink class="h-4 w-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Assignment Attachments -->
                    <div
                        v-if="
                            assignment.attachments &&
                            assignment.attachments.length
                        "
                        class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800"
                    >
                        <h3
                            class="mb-4 text-lg font-semibold text-gray-900 dark:text-white"
                        >
                            Assignment Materials ({{
                                assignment.attachments.length
                            }})
                        </h3>
                        <p
                            class="mb-4 text-sm text-gray-600 dark:text-gray-400"
                        >
                            Files provided by the instructor for this assignment
                        </p>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div
                                v-for="(
                                    attachment, index
                                ) in assignment.attachments"
                                :key="index"
                                class="flex items-center justify-between rounded-md border border-gray-200 bg-gray-50 p-4 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:hover:bg-gray-800"
                            >
                                <div
                                    class="flex min-w-0 flex-1 items-center gap-3"
                                >
                                    <component
                                        :is="
                                            getFileIcon(
                                                getFileName(attachment),
                                                attachment.mime_type,
                                            )
                                        "
                                        class="h-5 w-5 flex-shrink-0"
                                        :class="
                                            getFileColor(
                                                getFileName(attachment),
                                                attachment.mime_type,
                                            )
                                        "
                                    />
                                    <div class="min-w-0 flex-1">
                                        <p
                                            class="truncate text-sm font-medium text-gray-900 dark:text-white"
                                        >
                                            {{ getFileName(attachment) }}
                                        </p>
                                        <div
                                            class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400"
                                        >
                                            <span v-if="attachment.size">
                                                {{
                                                    formatFileSize(
                                                        attachment.size,
                                                    )
                                                }}
                                            </span>
                                            <span
                                                v-if="attachment.mime_type"
                                                class="truncate"
                                            >
                                                • {{ attachment.mime_type }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="ml-2 flex flex-shrink-0 gap-1">
                                    <button
                                        @click="downloadFile(attachment)"
                                        class="rounded-md p-2 text-gray-500 hover:bg-gray-200 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800"
                                        title="Download"
                                    >
                                        <Download class="h-4 w-4" />
                                    </button>
                                    <button
                                        v-if="attachment.url"
                                        @click="previewFile(attachment)"
                                        class="rounded-md p-2 text-gray-500 hover:bg-gray-200 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800"
                                        title="Preview"
                                    >
                                        <ExternalLink class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Grading Panel -->
                <div class="space-y-6">
                    <!-- Grading Form -->
                    <div
                        class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800"
                    >
                        <div class="mb-6 flex items-center justify-between">
                            <h3
                                class="text-lg font-semibold text-gray-900 dark:text-white"
                            >
                                Grading
                            </h3>
                            <button
                                v-if="
                                    !isEditing && submission.status !== 'graded'
                                "
                                @click="toggleEditMode"
                                class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                            >
                                Grade Submission
                            </button>
                        </div>

                        <!-- Current Grade Display -->
                        <div v-if="!isEditing" class="space-y-6">
                            <div>
                                <p
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
                                    Marks Obtained
                                </p>
                                <div class="mt-2">
                                    <p
                                        v-if="
                                            submission.marks_obtained !== null
                                        "
                                        class="text-3xl font-bold"
                                        :class="getGradeColor()"
                                    >
                                        {{ submission.marks_obtained }}/{{
                                            assignment.total_marks
                                        }}
                                    </p>
                                    <p
                                        v-else
                                        class="text-lg text-gray-500 dark:text-gray-400"
                                    >
                                        Not graded yet
                                    </p>
                                    <p
                                        v-if="calculatePercentage"
                                        class="mt-1 text-sm"
                                        :class="getGradeColor()"
                                    >
                                        ({{ calculatePercentage }}%)
                                    </p>
                                </div>
                            </div>

                            <div v-if="submission.feedback">
                                <p
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
                                    Feedback
                                </p>
                                <div
                                    class="mt-2 rounded-md bg-gray-50 p-4 dark:bg-gray-900"
                                >
                                    <p
                                        class="whitespace-pre-wrap text-gray-700 dark:text-gray-300"
                                    >
                                        {{ submission.feedback }}
                                    </p>
                                </div>
                            </div>

                            <div
                                v-if="submission.status === 'graded'"
                                class="border-t border-gray-200 pt-4 dark:border-gray-700"
                            >
                                <p
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
                                    Graded On
                                </p>
                                <p
                                    class="text-sm text-gray-700 dark:text-gray-300"
                                >
                                    {{ formatDate(submission.updated_at) }}
                                </p>
                            </div>

                            <button
                                v-if="submission.status === 'graded'"
                                @click="toggleEditMode"
                                class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                            >
                                Update Grade
                            </button>
                        </div>

                        <!-- Edit Form -->
                        <form
                            v-else
                            @submit.prevent="submitGrading"
                            class="space-y-6"
                        >
                            <div>
                                <label
                                    for="marks_obtained"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Marks (out of {{ assignment.total_marks }})
                                </label>
                                <input
                                    id="marks_obtained"
                                    v-model="form.marks_obtained"
                                    type="number"
                                    min="0"
                                    :max="assignment.total_marks"
                                    step="0.5"
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    required
                                />
                                <p
                                    class="mt-1 text-xs text-gray-500 dark:text-gray-400"
                                >
                                    Enter marks between 0 and
                                    {{ assignment.total_marks }}
                                </p>
                            </div>

                            <div>
                                <label
                                    for="feedback"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Feedback
                                </label>
                                <textarea
                                    id="feedback"
                                    v-model="form.feedback"
                                    rows="4"
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    placeholder="Provide constructive feedback to the student..."
                                ></textarea>
                            </div>

                            <div class="flex gap-2 pt-4">
                                <button
                                    type="button"
                                    @click="toggleEditMode"
                                    class="flex-1 rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="flex-1 rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 disabled:opacity-50"
                                >
                                    {{
                                        form.processing
                                            ? 'Saving...'
                                            : 'Save Grade'
                                    }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Assignment Details -->
                    <div
                        class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800"
                    >
                        <h3
                            class="mb-4 text-lg font-semibold text-gray-900 dark:text-white"
                        >
                            Assignment Details
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <p
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
                                    Total Marks
                                </p>
                                <p
                                    class="text-xl font-bold text-gray-900 dark:text-white"
                                >
                                    {{ assignment.total_marks }}
                                </p>
                            </div>
                            <div>
                                <p
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
                                    Deadline
                                </p>
                                <p
                                    class="text-sm text-gray-700 dark:text-gray-300"
                                >
                                    {{ formatDate(assignment.deadline) }}
                                </p>
                            </div>
                            <div v-if="assignment.instructions">
                                <p
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
                                    Instructions
                                </p>
                                <p
                                    class="line-clamp-3 text-sm text-gray-700 dark:text-gray-300"
                                >
                                    {{ assignment.instructions }}
                                </p>
                            </div>
                            <div v-if="assignment.description">
                                <p
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
                                    Description
                                </p>
                                <p
                                    class="line-clamp-2 text-sm text-gray-700 dark:text-gray-300"
                                >
                                    {{ assignment.description }}
                                </p>
                            </div>
                            <div
                                v-if="
                                    assignment.attachments &&
                                    assignment.attachments.length
                                "
                            >
                                <p
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
                                    Materials
                                </p>
                                <p
                                    class="text-sm text-gray-700 dark:text-gray-300"
                                >
                                    {{ assignment.attachments.length }} file(s)
                                    attached
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>
