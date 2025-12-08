<!-- resources/js/pages/Assignments/Index.vue -->
<template>
    <AppLayout>
        <div class="container mx-auto px-4 py-8">
            <!-- Content -->
            <div v-if="extractedClassId">
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <Link
                                :href="classShowUrl"
                                class="flex items-center gap-2 text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                            >
                                <ArrowLeft class="h-4 w-4" />
                                Back to Class
                            </Link>
                            <div
                                class="h-6 w-px bg-gray-300 dark:bg-gray-600"
                            ></div>
                            <h1
                                class="text-3xl font-bold text-gray-900 dark:text-white"
                            >
                                Assignments - {{ extractedClassName }}
                            </h1>
                        </div>
                        <div v-if="isFaculty" class="flex gap-2">
                            <Link
                                :href="assignmentCreateUrl"
                                class="flex items-center gap-2 rounded-md bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600 focus:ring-2 focus:ring-amber-400 focus:outline-none"
                            >
                                <Plus class="h-4 w-4" />
                                New Assignment
                            </Link>
                        </div>
                    </div>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        View and manage assignments for this class
                    </p>
                </div>

                <!-- Statistics -->
                <div class="mb-8 grid gap-6 md:grid-cols-4">
                    <div
                        class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800"
                    >
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Total Assignments
                        </p>
                        <p
                            class="text-3xl font-bold text-gray-900 dark:text-white"
                        >
                            {{ assignmentsData.length }}
                        </p>
                    </div>
                    <div
                        class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800"
                    >
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Published
                        </p>
                        <p
                            class="text-3xl font-bold text-green-600 dark:text-green-400"
                        >
                            {{ publishedCount }}
                        </p>
                    </div>
                    <div
                        class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800"
                    >
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Closed
                        </p>
                        <p
                            class="text-3xl font-bold text-red-600 dark:text-red-400"
                        >
                            {{ closedCount }}
                        </p>
                    </div>
                    <div
                        class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800"
                    >
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Upcoming
                        </p>
                        <p
                            class="text-3xl font-bold text-amber-600 dark:text-amber-400"
                        >
                            {{ upcomingCount }}
                        </p>
                    </div>
                </div>

                <!-- Assignments Grid -->
                <div
                    v-if="assignmentsData.length"
                    class="grid gap-6 md:grid-cols-2"
                >
                    <AssignmentCard
                        v-for="assignment in assignmentsData"
                        :key="assignment.id"
                        :assignment="assignment"
                        :class-id="extractedClassId"
                        :is-faculty="isFaculty"
                    />
                </div>

                <!-- Empty State -->
                <div
                    v-else
                    class="rounded-lg border-2 border-dashed border-gray-300 p-12 text-center dark:border-gray-700"
                >
                    <FileText class="mx-auto h-12 w-12 text-gray-400" />
                    <h3
                        class="mt-4 text-lg font-medium text-gray-900 dark:text-white"
                    >
                        No assignments found
                    </h3>
                    <p class="mt-2 text-gray-500 dark:text-gray-400">
                        {{
                            isFaculty
                                ? 'Create your first assignment for this class'
                                : 'No assignments have been posted yet'
                        }}
                    </p>
                </div>
            </div>

            <!-- Error State -->
            <div v-else class="py-12 text-center">
                <div class="mb-2 text-lg font-semibold text-red-500">
                    Error Loading Class Data
                </div>
                <p class="text-gray-600">
                    Unable to load class information. The data received was: "{{
                        props.class
                    }}"
                </p>
                <p class="mt-2 text-sm text-gray-500">
                    Please check the console for more details.
                </p>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AssignmentCard from '@/components/assignments/AssignmentCard.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, FileText, Plus } from 'lucide-vue-next';
import { computed } from 'vue';

// Import your Wayfinder route definitions
import assignmentsRoute from '@/routes/admin/assignments';
import classes from '@/routes/admin/classes';

const props = defineProps<{
    class?: any; // Could be string or object
    assignments?: any;
    isFaculty?: boolean;
}>();

// Extract class ID from URL as primary source
const extractClassIdFromUrl = (): number => {
    try {
        const url = window.location.pathname;
        const match = url.match(/\/classes\/(\d+)\/assignments/);
        return match ? parseInt(match[1]) : 0;
    } catch (error) {
        console.error('Error extracting class ID from URL:', error);
        return 0;
    }
};

const extractedClassId = computed(() => extractClassIdFromUrl());

// Try to extract class name from the string
const extractedClassName = computed(() => {
    // If props.class is an object
    if (props.class && typeof props.class === 'object' && props.class.name) {
        return props.class.name;
    }

    // If it's the string of column names, try to get actual data from server
    if (typeof props.class === 'string') {
        // The string contains column names, not actual data
        // For now, return a generic name
        return 'Class ' + extractedClassId.value;
    }

    return 'Class ' + extractedClassId.value;
});

// Parse assignments data
const assignmentsData = computed(() => {
    if (!props.assignments) return [];

    // If assignments has a data property
    if (props.assignments.data) {
        return props.assignments.data;
    }

    // If assignments is directly an array
    if (Array.isArray(props.assignments)) {
        return props.assignments;
    }

    return [];
});

const isFaculty = computed(() => props.isFaculty || false);

// Generate URLs using the extracted class ID
const classShowUrl = computed(() => {
    return classes.show.url({ class: extractedClassId.value });
});

const assignmentCreateUrl = computed(() => {
    return assignmentsRoute.create.url({ class: extractedClassId.value });
});

// Statistics
const publishedCount = computed(() => {
    return assignmentsData.value.filter((a: any) => a.status === 'published')
        .length;
});

const closedCount = computed(() => {
    return assignmentsData.value.filter((a: any) => a.status === 'closed')
        .length;
});

const upcomingCount = computed(() => {
    const now = new Date();
    return assignmentsData.value.filter((a: any) => {
        if (a.status !== 'published') return false;
        const deadline = new Date(a.deadline);
        return deadline > now;
    }).length;
});
</script>
