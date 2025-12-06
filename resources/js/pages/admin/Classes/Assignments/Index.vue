<!-- resources/js/pages/Assignments/Index.vue -->
<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <Link
                            :href="route('classes.show', classData.id)"
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
                            Assignments - {{ classData.name }}
                        </h1>
                    </div>
                    <div v-if="isFaculty" class="flex gap-2">
                        <Link
                            :href="route('assignments.create', classData.id)"
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
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">
                        {{ assignments.data.length }}
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

            <!-- Filter Controls -->
            <div class="mb-6 flex items-center justify-between">
                <div class="flex gap-3">
                    <button
                        v-for="filter in filters"
                        :key="filter.value"
                        @click="activeFilter = filter.value"
                        class="rounded-full px-4 py-1.5 text-sm font-medium transition-colors"
                        :class="
                            activeFilter === filter.value
                                ? 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200'
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'
                        "
                    >
                        {{ filter.label }}
                    </button>
                </div>
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search assignments..."
                            class="rounded-md border border-gray-300 bg-white px-4 py-2 pl-10 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        />
                        <Search
                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-gray-400"
                        />
                    </div>
                </div>
            </div>

            <!-- Assignments Grid -->
            <div
                v-if="filteredAssignments.length"
                class="grid gap-6 md:grid-cols-2"
            >
                <AssignmentCard
                    v-for="assignment in filteredAssignments"
                    :key="assignment.id"
                    :assignment="assignment"
                    :class-id="classData.id"
                    :is-faculty="isFaculty"
                    :submission="getSubmission(assignment.id)"
                />
            </div>

            <!-- Pagination -->
            <div v-if="assignments.data.length" class="mt-8">
                <Pagination :links="assignments.links" />
            </div>

            <!-- Empty State -->
            <div
                v-if="!filteredAssignments.length"
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
                        searchQuery || activeFilter !== 'all'
                            ? 'Try adjusting your search or filter'
                            : isFaculty
                              ? 'Create your first assignment for this class'
                              : 'No assignments have been posted yet'
                    }}
                </p>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AssignmentCard from '@/components/assignments/AssignmentCard.vue';
import Pagination from '@/components/ui/Pagination.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { index as classesIndex } from '@/routes/admin/classes';
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, FileText, Plus, Search } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    classData: {
        id: number;
        name: string;
    };
    assignments: {
        data: Array<{
            id: number;
            title: string;
            description: string;
            total_marks: number;
            deadline: string;
            status: string;
            attachments?: Array<{
                name: string;
                path: string;
                size: number;
            }>;
            submissions_count: number;
        }>;
        links: any[];
    };
    isFaculty: boolean;
    studentSubmissions: Array<{
        assignment_id: number;
        status: string;
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
    { title: 'Assignments', href: '' },
];

const activeFilter = ref('all');
const searchQuery = ref('');

const filters = [
    { value: 'all', label: 'All' },
    { value: 'published', label: 'Published' },
    { value: 'closed', label: 'Closed' },
    { value: 'upcoming', label: 'Upcoming' },
    { value: 'graded', label: 'Graded' },
];

const publishedCount = computed(() => {
    return props.assignments.data.filter((a) => a.status === 'published')
        .length;
});

const closedCount = computed(() => {
    return props.assignments.data.filter((a) => a.status === 'closed').length;
});

const upcomingCount = computed(() => {
    const now = new Date();
    return props.assignments.data.filter((a) => {
        if (a.status !== 'published') return false;
        const deadline = new Date(a.deadline);
        return deadline > now;
    }).length;
});

const filteredAssignments = computed(() => {
    let filtered = props.assignments.data;

    // Apply status filter
    if (activeFilter.value !== 'all') {
        if (activeFilter.value === 'upcoming') {
            const now = new Date();
            filtered = filtered.filter((a) => {
                if (a.status !== 'published') return false;
                const deadline = new Date(a.deadline);
                return deadline > now;
            });
        } else if (activeFilter.value === 'graded') {
            // For students, show assignments they've submitted
            if (!props.isFaculty) {
                filtered = filtered.filter((a) => {
                    const submission = props.studentSubmissions.find(
                        (s) => s.assignment_id === a.id,
                    );
                    return submission && submission.status === 'graded';
                });
            } else {
                filtered = filtered.filter(
                    (a) => a.status === 'published' || a.status === 'closed',
                );
            }
        } else {
            filtered = filtered.filter((a) => a.status === activeFilter.value);
        }
    }

    // Apply search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(
            (a) =>
                a.title.toLowerCase().includes(query) ||
                a.description.toLowerCase().includes(query),
        );
    }

    return filtered;
});

const getSubmission = (assignmentId: number) => {
    return props.studentSubmissions.find(
        (sub) => sub.assignment_id === assignmentId,
    );
};
</script>
