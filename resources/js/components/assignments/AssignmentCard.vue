<!-- resources/js/components/assignments/AssignmentCard.vue -->
<template>
    <div
        class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
    >
        <!-- Header -->
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ assignment.title }}
                </h3>
                <p
                    class="mt-1 line-clamp-2 text-sm text-gray-500 dark:text-gray-400"
                >
                    {{ assignment.description }}
                </p>
            </div>
            <span
                class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium"
                :class="statusClasses"
            >
                {{ assignment.status }}
            </span>
        </div>

        <!-- Details -->
        <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-500 dark:text-gray-400">Total Marks</p>
                <p class="font-medium text-gray-900 dark:text-white">
                    {{ assignment.total_marks }}
                </p>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400">Deadline</p>
                <p class="font-medium text-gray-900 dark:text-white">
                    {{ formatDate(assignment.deadline) }}
                </p>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400">Submissions</p>
                <p class="font-medium text-gray-900 dark:text-white">
                    {{ assignment.submissions_count }}
                </p>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400">Time Left</p>
                <p class="font-medium" :class="timeLeftClass">
                    {{ timeLeftText }}
                </p>
            </div>
        </div>

        <!-- Attachments -->
        <div v-if="assignment.attachments?.length" class="mt-4">
            <p
                class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300"
            >
                Attachments:
            </p>
            <div class="space-y-1">
                <div
                    v-for="(file, index) in assignment.attachments"
                    :key="index"
                    class="flex items-center gap-2 rounded-md bg-gray-50 px-3 py-2 dark:bg-gray-700"
                >
                    <Paperclip class="h-4 w-4 text-gray-400" />
                    <span class="text-sm text-gray-600 dark:text-gray-300">
                        {{ file.name }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Student Submission Status -->
        <div v-if="submission" class="mt-4">
            <div class="rounded-md p-3" :class="submissionStatusClasses">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <CheckCircle class="h-4 w-4" />
                        <span class="text-sm font-medium">
                            {{
                                submission.status === 'submitted'
                                    ? 'Submitted'
                                    : 'Pending'
                            }}
                        </span>
                    </div>
                    <div
                        v-if="submission.marks_obtained !== null"
                        class="text-sm"
                    >
                        Marks: {{ submission.marks_obtained }}/{{
                            assignment.total_marks
                        }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex items-center justify-between">
            <Link
                :href="route('assignments.show', [classId, assignment.id])"
                class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400"
            >
                View Details →
            </Link>

            <div v-if="isFaculty" class="flex gap-2">
                <button
                    v-if="assignment.status !== 'closed'"
                    @click="updateStatus('closed')"
                    class="rounded-md bg-red-100 px-3 py-1 text-xs font-medium text-red-700 hover:bg-red-200 dark:bg-red-900 dark:text-red-200"
                >
                    Close
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { CheckCircle, Paperclip } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    assignment: {
        id: number;
        title: string;
        description: string;
        total_marks: number;
        deadline: string;
        status: 'draft' | 'published' | 'closed';
        attachments?: Array<{
            name: string;
            path: string;
            size: number;
        }>;
        submissions_count: number;
    };
    classId: number;
    isFaculty?: boolean;
    submission?: {
        status: string;
        marks_obtained?: number;
    };
}>();

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
    if (props.submission?.status === 'submitted') {
        return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
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

const updateStatus = (status: string) => {
    if (confirm('Are you sure you want to change the assignment status?')) {
        router.put(
            route('assignments.status', [props.classId, props.assignment.id]),
            { status },
            { preserveScroll: true },
        );
    }
};
</script>
