<!-- resources/js/components/assignments/SubmissionList.vue -->
<template>
    <div
        class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800"
    >
        <!-- Header -->
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Submissions ({{ submissions.length }})
                </h3>
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {{ submittedCount }} submitted •
                        {{ gradedCount }} graded
                    </span>
                    <div class="relative">
                        <select
                            v-model="filterStatus"
                            class="appearance-none rounded-md border border-gray-300 bg-white px-3 py-1 pr-8 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        >
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="submitted">Submitted</option>
                            <option value="graded">Graded</option>
                            <option value="late">Late</option>
                        </select>
                        <ChevronDown
                            class="pointer-events-none absolute top-1/2 right-2 h-4 w-4 -translate-y-1/2 transform text-gray-400"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Submission List -->
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
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr
                        v-for="submission in filteredSubmissions"
                        :key="submission.id"
                        class="hover:bg-gray-50 dark:hover:bg-gray-800"
                    >
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
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
                                        {{ submission.student.name }}
                                    </p>
                                    <p
                                        class="text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        {{ submission.student.email }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span
                                class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium"
                                :class="getStatusClasses(submission.status)"
                            >
                                {{ submission.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span
                                class="text-sm text-gray-600 dark:text-gray-400"
                            >
                                {{
                                    submission.submitted_at
                                        ? formatDate(submission.submitted_at)
                                        : 'Not submitted'
                                }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div
                                v-if="submission.marks_obtained !== null"
                                class="flex items-center gap-2"
                            >
                                <span
                                    class="font-medium text-gray-900 dark:text-white"
                                >
                                    {{ submission.marks_obtained }}/{{
                                        totalMarks
                                    }}
                                </span>
                                <span
                                    class="text-xs text-gray-500 dark:text-gray-400"
                                >
                                    ({{
                                        (
                                            (submission.marks_obtained /
                                                totalMarks) *
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
                                <Link
                                    v-if="submission.submitted_at"
                                    :href="
                                        route('submissions.download', [
                                            submission.id,
                                            0,
                                        ])
                                    "
                                    class="rounded-md bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700 hover:bg-blue-200 dark:bg-blue-900 dark:text-blue-200"
                                    title="Download Submission"
                                >
                                    Download
                                </Link>
                                <button
                                    @click="gradeSubmission(submission)"
                                    class="rounded-md bg-green-100 px-3 py-1 text-xs font-medium text-green-700 hover:bg-green-200 dark:bg-green-900 dark:text-green-200"
                                    title="Grade Submission"
                                >
                                    Grade
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Empty State -->
                    <tr v-if="!filteredSubmissions.length">
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div
                                class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400"
                            >
                                <FileText class="mb-2 h-8 w-8 opacity-50" />
                                <p class="text-sm">No submissions found</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronDown, FileText, User } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    submissions: Array<{
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
    totalMarks: number;
}>();

const emit = defineEmits<{
    grade: [submissionId: number];
}>();

const filterStatus = ref('');

const filteredSubmissions = computed(() => {
    if (!filterStatus.value) return props.submissions;
    return props.submissions.filter((sub) => sub.status === filterStatus.value);
});

const submittedCount = computed(() => {
    return props.submissions.filter((sub) => sub.submitted_at).length;
});

const gradedCount = computed(() => {
    return props.submissions.filter((sub) => sub.status === 'graded').length;
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
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const gradeSubmission = (submission: any) => {
    emit('grade', submission.id);
};
</script>
