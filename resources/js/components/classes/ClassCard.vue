<!-- resources/js/components/classes/ClassCard.vue -->
<template>
    <div
        class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
    >
        <!-- Header -->
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <div class="flex items-center gap-2">
                    <Building
                        class="h-5 w-5 text-gray-500 dark:text-gray-400"
                    />
                    <h3
                        class="text-lg font-semibold text-gray-900 dark:text-white"
                    >
                        {{ classItem.name }}
                    </h3>
                </div>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    {{ classItem.description || 'No description provided' }}
                </p>
            </div>
            <span
                class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium"
                :class="statusClasses"
            >
                {{ classItem.is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>

        <!-- Details -->
        <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-500 dark:text-gray-400">Subject Code</p>
                <p class="font-medium text-gray-900 dark:text-white">
                    {{ classItem.subject_code }}
                </p>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400">Semester</p>
                <p class="font-medium text-gray-900 dark:text-white">
                    {{ classItem.semester }}
                </p>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400">Section</p>
                <p class="font-medium text-gray-900 dark:text-white">
                    {{ classItem.section }}
                </p>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400">Enrolled</p>
                <p class="font-medium text-gray-900 dark:text-white">
                    {{ classItem.enrollments_count }} students
                </p>
            </div>
        </div>

        <!-- Enrollment Code -->
        <div v-if="isFaculty" class="mt-4">
            <div class="rounded-md bg-gray-50 p-3 dark:bg-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p
                            class="text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Enrollment Code
                        </p>
                        <p
                            class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                        >
                            Share this code with students
                        </p>
                    </div>
                    <code
                        class="rounded bg-gray-100 px-3 py-1 font-mono text-sm font-bold text-gray-800 dark:bg-gray-600 dark:text-gray-200"
                    >
                        {{ classItem.enrollment_code }}
                    </code>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex items-center justify-between">
            <Link
                :href="classShowUrl"
                class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400"
            >
                View Class →
            </Link>

            <div v-if="isFaculty" class="flex gap-2">
                <Link
                    :href="assignmentCreateUrl"
                    class="rounded-md bg-amber-500 px-3 py-1 text-xs font-medium text-white hover:bg-amber-600"
                >
                    Add Assignment
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Building } from 'lucide-vue-next';
import { computed } from 'vue';

// Import your Wayfinder route definitions
import assignments from '@/routes/admin/assignments'; // Import from assignments file
import classes from '@/routes/admin/classes'; // This should be from your classes file

interface Props {
    classItem?: {
        id: number;
        name: string;
        description?: string;
        subject_code: string;
        semester: number;
        section: string;
        enrollment_code: string;
        is_active: boolean;
        enrollments_count: number;
        assignments_count: number;
    };
    isFaculty?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    classItem: () => ({
        id: 0,
        name: '',
        description: '',
        subject_code: '',
        semester: 0,
        section: '',
        enrollment_code: '',
        is_active: false,
        enrollments_count: 0,
        assignments_count: 0,
    }),
    isFaculty: false,
});

const statusClasses = computed(() => {
    return props.classItem.is_active
        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
        : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
});

// Generate URLs using Wayfinder
const classShowUrl = computed(() => {
    return classes.show.url({ class: props.classItem.id });
});

// Generate assignment create URL
const assignmentCreateUrl = computed(() => {
    return assignments.create.url({ class: props.classItem.id });
});
</script>
