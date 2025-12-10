<script setup lang="ts">
import SubmissionForm from '@/components/assignments/SubmissionForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ArrowLeft, User } from 'lucide-vue-next';
import { computed } from 'vue';

// Import your Wayfinder route definitions
import { route } from 'ziggy-js';

const props = defineProps<{
    class?: any; // Could be string or object
    assignment?: any;
    isFaculty?: boolean;
    submission?: any;
    allSubmissions?: any; // Could be paginator object or array
}>();

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
        return {
            id: props.assignment.id || extractedAssignmentId.value,
            title: props.assignment.title || 'Untitled Assignment',
            description: props.assignment.description || '',
            instructions: props.assignment.instructions || '',
            total_marks: props.assignment.total_marks || 0,
            deadline: props.assignment.deadline || '',
            status: props.assignment.status || 'draft',
            attachments: props.assignment.attachments || [],
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

const safeAllSubmissionsData = computed(() => {
    if (!props.allSubmissions) return [];

    // If it's a paginator with data property
    if (props.allSubmissions.data) {
        return props.allSubmissions.data;
    }

    // If it's directly an array
    if (Array.isArray(props.allSubmissions)) {
        return props.allSubmissions;
    }

    // If it's an object, try to extract data
    if (typeof props.allSubmissions === 'object') {
        // Try common paginator formats
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
    // Refresh the page to show updated submission
    router.reload({ preserveScroll: true });
};

const gradeSubmission = (submissionItem: any) => {
    // You'll need to implement grading functionality
    console.log('Grade submission:', submissionItem);
    // This would typically open a modal or navigate to a grading page
};

// Note: Removed breadcrumbs for now since they rely on classData.name
// which might not be available due to the string issue
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
    </AppLayout>
</template>
