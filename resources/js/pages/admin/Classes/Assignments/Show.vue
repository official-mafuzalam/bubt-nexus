<!-- resources/js/pages/Assignments/Show.vue -->
<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <Link
                            :href="route('assignments.index', classData.id)"
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
                            {{ assignment.title }}
                        </h1>
                    </div>
                    <div v-if="isFaculty" class="flex gap-2">
                        <button
                            @click="toggleAssignmentStatus"
                            class="rounded-md px-4 py-2 text-sm font-medium"
                            :class="
                                assignment.status === 'closed'
                                    ? 'bg-green-100 text-green-800 hover:bg-green-200 dark:bg-green-900 dark:text-green-200'
                                    : 'bg-red-100 text-red-800 hover:bg-red-200 dark:bg-red-900 dark:text-red-200'
                            "
                        >
                            {{
                                assignment.status === 'closed'
                                    ? 'Reopen'
                                    : 'Close'
                            }}
                        </button>
                    </div>
                </div>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Due: {{ formatDate(assignment.deadline) }}
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
                                {{ assignment.description }}
                            </div>
                        </div>

                        <!-- Instructions -->
                        <div v-if="assignment.instructions" class="mb-6">
                            <h4
                                class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Instructions
                            </h4>
                            <div
                                class="prose max-w-none text-gray-600 dark:text-gray-400"
                            >
                                {{ assignment.instructions }}
                            </div>
                        </div>

                        <!-- Attachments -->
                        <div v-if="assignment.attachments?.length" class="mb-6">
                            <h4
                                class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Attachments
                            </h4>
                            <div class="space-y-2">
                                <div
                                    v-for="(
                                        file, index
                                    ) in assignment.attachments"
                                    :key="index"
                                    class="flex items-center justify-between rounded-md border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-600 dark:bg-gray-700"
                                >
                                    <div class="flex items-center gap-3">
                                        <Paperclip
                                            class="h-5 w-5 text-gray-400"
                                        />
                                        <div>
                                            <p
                                                class="text-sm font-medium text-gray-900 dark:text-white"
                                            >
                                                {{ file.name }}
                                            </p>
                                            <p
                                                class="text-xs text-gray-500 dark:text-gray-400"
                                            >
                                                {{ formatFileSize(file.size) }}
                                            </p>
                                        </div>
                                    </div>
                                    <a
                                        :href="
                                            route('assignments.download', [
                                                classData.id,
                                                assignment.id,
                                                index,
                                            ])
                                        "
                                        class="rounded-md bg-amber-500 px-3 py-1 text-xs font-medium text-white hover:bg-amber-600"
                                        download
                                    >
                                        Download
                                    </a>
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
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium"
                                                :class="submissionStatusClasses"
                                            >
                                                {{ submission.status }}
                                            </span>
                                            <span
                                                v-if="submission.submitted_at"
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
                                                }}/{{ assignment.total_marks }}
                                            </p>
                                            <p
                                                class="text-sm text-gray-500 dark:text-gray-400"
                                            >
                                                {{
                                                    (
                                                        (submission.marks_obtained /
                                                            assignment.total_marks) *
                                                        100
                                                    ).toFixed(1)
                                                }}%
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Submission Content -->
                                    <div v-if="submission.content">
                                        <h5
                                            class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300"
                                        >
                                            Your Answer
                                        </h5>
                                        <div
                                            class="rounded-md bg-gray-50 p-4 dark:bg-gray-700"
                                        >
                                            <p
                                                class="whitespace-pre-wrap text-gray-700 dark:text-gray-300"
                                            >
                                                {{ submission.content }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Submission Attachments -->
                                    <div v-if="submission.attachments?.length">
                                        <h5
                                            class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300"
                                        >
                                            Your Files
                                        </h5>
                                        <div class="space-y-2">
                                            <div
                                                v-for="(
                                                    file, index
                                                ) in submission.attachments"
                                                :key="index"
                                                class="flex items-center justify-between rounded-md border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-600 dark:bg-gray-700"
                                            >
                                                <div
                                                    class="flex items-center gap-3"
                                                >
                                                    <Paperclip
                                                        class="h-5 w-5 text-gray-400"
                                                    />
                                                    <div>
                                                        <p
                                                            class="text-sm font-medium text-gray-900 dark:text-white"
                                                        >
                                                            {{ file.name }}
                                                        </p>
                                                        <p
                                                            class="text-xs text-gray-500 dark:text-gray-400"
                                                        >
                                                            {{
                                                                formatFileSize(
                                                                    file.size,
                                                                )
                                                            }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <a
                                                    :href="
                                                        route(
                                                            'submissions.download',
                                                            [
                                                                submission.id,
                                                                index,
                                                            ],
                                                        )
                                                    "
                                                    class="rounded-md bg-blue-500 px-3 py-1 text-xs font-medium text-white hover:bg-blue-600"
                                                    download
                                                >
                                                    Download
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Feedback -->
                                    <div v-if="submission.feedback">
                                        <h5
                                            class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300"
                                        >
                                            Instructor Feedback
                                        </h5>
                                        <div
                                            class="rounded-md bg-green-50 p-4 dark:bg-green-900/20"
                                        >
                                            <p
                                                class="whitespace-pre-wrap text-green-800 dark:text-green-200"
                                            >
                                                {{ submission.feedback }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submission Form (For Students) -->
                    <div v-if="!isFaculty && assignment.status !== 'closed'">
                        <SubmissionForm
                            :assignment="assignment"
                            :class-id="classData.id"
                            :existing-submission="submission"
                            @submitted="handleSubmission"
                        />
                    </div>

                    <!-- Submission List (For Faculty) -->
                    <div v-if="isFaculty">
                        <SubmissionList
                            :submissions="allSubmissions"
                            :total-marks="assignment.total_marks"
                            @grade="handleGrade"
                        />
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
                                    {{ assignment.status }}
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
                                    class="font-medium text-gray-900 dark:text-white"
                                >
                                    {{ formatDate(assignment.deadline) }}
                                </p>
                            </div>
                            <div>
                                <p
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
                                    Time Left
                                </p>
                                <p class="font-medium" :class="timeLeftClass">
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
                                    {{ assignment.submissions_count }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Grading Form (For Faculty) -->
                    <div v-if="isFaculty && gradingSubmission">
                        <div
                            class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800"
                        >
                            <h3
                                class="mb-4 text-lg font-semibold text-gray-900 dark:text-white"
                            >
                                Grade Submission
                            </h3>
                            <GradingForm
                                :assignment="assignment"
                                :submission="gradingSubmission"
                                @submit="submitGrade"
                                @cancel="cancelGrading"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import GradingForm from '@/components/assignments/GradingForm.vue';
import SubmissionForm from '@/components/assignments/SubmissionForm.vue';
import SubmissionList from '@/components/assignments/SubmissionList.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { index as classesIndex } from '@/routes/admin/classes';
import { Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Paperclip } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    classData: {
        id: number;
        name: string;
    };
    assignment: {
        id: number;
        title: string;
        description: string;
        instructions?: string;
        total_marks: number;
        deadline: string;
        status: string;
        attachments?: Array<{
            name: string;
            path: string;
            size: number;
        }>;
        submissions_count: number;
    };
    isFaculty: boolean;
    submission?: {
        id?: number;
        content?: string;
        attachments?: Array<{
            name: string;
            path: string;
            size: number;
        }>;
        marks_obtained?: number;
        feedback?: string;
        status: string;
        submitted_at?: string;
    };
    allSubmissions?: Array<{
        id: number;
        student: {
            id: number;
            name: string;
            email: string;
        };
        status: string;
        submitted_at?: string;
        marks_obtained?: number;
    }>;
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Classes', href: classesIndex().url },
    {
        title: props.classData.name,
        href: route('classes.show', props.classData.id),
    },
    {
        title: 'Assignments',
        href: route('assignments.index', props.classData.id),
    },
    { title: props.assignment.title, href: '' },
];

const gradingSubmission = ref<any>(null);

const statusClasses = computed(() => {
    const classes: Record<string, string> = {
        draft: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        published:
            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        closed: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    };
    return classes[props.assignment.status] || classes.draft;
});

const submissionStatusClasses = computed(() => {
    if (!props.submission) return '';

    if (props.submission.status === 'graded') {
        return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
    } else if (props.submission.status === 'late') {
        return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
    } else if (props.submission.status === 'submitted') {
        return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
    }
    return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
});

const timeLeftClass = computed(() => {
    const deadline = new Date(props.assignment.deadline);
    const now = new Date();
    const diffHours = Math.floor(
        (deadline.getTime() - now.getTime()) / (1000 * 60 * 60),
    );

    if (diffHours < 0) return 'text-red-600 dark:text-red-400';
    if (diffHours < 24) return 'text-amber-600 dark:text-amber-400';
    return 'text-gray-900 dark:text-gray-100';
});

const timeLeftText = computed(() => {
    const deadline = new Date(props.assignment.deadline);
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

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const toggleAssignmentStatus = () => {
    const newStatus =
        props.assignment.status === 'closed' ? 'published' : 'closed';
    const action = props.assignment.status === 'closed' ? 'reopen' : 'close';

    if (confirm(`Are you sure you want to ${action} this assignment?`)) {
        router.put(
            route('assignments.status', [
                props.classData.id,
                props.assignment.id,
            ]),
            { status: newStatus },
            { preserveScroll: true },
        );
    }
};

const handleSubmission = () => {
    // Refresh the page to show updated submission
    router.reload({ preserveScroll: true });
};

const handleGrade = (submissionId: number) => {
    // Find the submission to grade
    const submission = props.allSubmissions?.find((s) => s.id === submissionId);
    if (submission) {
        gradingSubmission.value = submission;
    }
};

const submitGrade = (formData: any) => {
    router.post(
        route('submissions.grade', gradingSubmission.value.id),
        formData,
        {
            preserveScroll: true,
            onSuccess: () => {
                gradingSubmission.value = null;
                router.reload({ preserveScroll: true });
            },
        },
    );
};

const cancelGrading = () => {
    gradingSubmission.value = null;
};
</script>
