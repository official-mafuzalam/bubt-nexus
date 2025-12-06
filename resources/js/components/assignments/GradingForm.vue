<!-- resources/js/components/assignments/GradingForm.vue -->
<template>
    <form @submit.prevent="submit" class="space-y-6">
        <!-- Student Information -->
        <div
            class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800"
        >
            <h4
                class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300"
            >
                Student Information
            </h4>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Student Name
                    </p>
                    <p class="font-medium text-gray-900 dark:text-white">
                        {{ submission.student.name }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Submitted At
                    </p>
                    <p class="font-medium text-gray-900 dark:text-white">
                        {{ formatDate(submission.submitted_at) }}
                    </p>
                </div>
                <div v-if="submission.student.user_detail?.student_id">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Student ID
                    </p>
                    <p class="font-medium text-gray-900 dark:text-white">
                        {{ submission.student.user_detail.student_id }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Submission Status
                    </p>
                    <span
                        class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium"
                        :class="submissionStatusClasses"
                    >
                        {{ submission.status }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Marks -->
        <div>
            <label
                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
                Marks Obtained *
            </label>
            <div class="mt-2 flex items-center gap-3">
                <input
                    v-model="form.marks_obtained"
                    type="number"
                    :min="0"
                    :max="assignment.total_marks"
                    step="0.01"
                    class="w-32 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    required
                    placeholder="0-100"
                />
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    out of {{ assignment.total_marks }} total marks
                </span>
            </div>
            <div v-if="form.marks_obtained !== null" class="mt-2">
                <div
                    class="h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700"
                >
                    <div
                        class="h-full rounded-full bg-green-500"
                        :style="{ width: percentage + '%' }"
                    ></div>
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Percentage: {{ percentage.toFixed(1) }}%
                </p>
            </div>
            <p
                v-if="form.errors.marks_obtained"
                class="mt-1 text-sm text-red-500"
            >
                {{ form.errors.marks_obtained }}
            </p>
        </div>

        <!-- Feedback -->
        <div>
            <label
                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
                Feedback
            </label>
            <textarea
                v-model="form.feedback"
                rows="4"
                class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                placeholder="Provide constructive feedback to the student..."
            ></textarea>
            <p v-if="form.errors.feedback" class="mt-1 text-sm text-red-500">
                {{ form.errors.feedback }}
            </p>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end gap-3">
            <button
                type="button"
                @click="$emit('cancel')"
                class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
            >
                Cancel
            </button>
            <button
                type="submit"
                :disabled="form.processing"
                class="rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
            >
                <span v-if="form.processing">Submitting Grade...</span>
                <span v-else>Submit Grade</span>
            </button>
        </div>
    </form>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    assignment: {
        id: number;
        total_marks: number;
    };
    submission: {
        id: number;
        student: {
            id: number;
            name: string;
            user_detail?: {
                student_id?: string;
            };
        };
        submitted_at: string;
        status: string;
        marks_obtained?: number;
        feedback?: string;
    };
}>();

const emit = defineEmits<{
    submit: [data: typeof form.data];
    cancel: [];
}>();

const form = useForm({
    marks_obtained: props.submission.marks_obtained || 0,
    feedback: props.submission.feedback || '',
});

const submissionStatusClasses = computed(() => {
    if (props.submission.status === 'graded') {
        return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
    } else if (props.submission.status === 'late') {
        return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
    }
    return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
});

const percentage = computed(() => {
    if (!form.marks_obtained || !props.assignment.total_marks) return 0;
    return (form.marks_obtained / props.assignment.total_marks) * 100;
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

const submit = () => {
    emit('submit', form.data);
};
</script>
