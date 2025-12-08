<script setup lang="ts">
import AssignmentCard from '@/components/assignments/AssignmentCard.vue';
import StudentList from '@/components/classes/StudentList.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ArrowLeft, FileText, Plus } from 'lucide-vue-next';
import { computed } from 'vue';

// Import your Wayfinder route definitions
import { route } from 'ziggy-js';

// Props should match what your controller sends
const props = defineProps<{
    classData: any; // From controller: 'class' => $class
    isEnrolled: boolean;
    isFaculty: boolean;
    enrollmentCount: number;
}>();

// Create computed property with better name
const classData = computed(() => props.classData);
const safeEnrollments = computed(() => {
    return classData.value?.enrollments?.map((e: any) => e.student) || [];
});

// Generate URLs
const classesIndexUrl = computed(() => route('admin.classes.index'));
const assignmentIndexUrl = computed(() => {
    if (!classData.value?.id) return '';
    return route('admin.assignments.index', { class: classData.value.id });
});

const assignmentCreateUrl = computed(() => {
    if (!classData.value?.id) return '';
    return route('admin.assignments.create', { class: classData.value.id });
});

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const joinClass = () => {
    if (confirm('Are you sure you want to join this class?')) {
        router.post(
            route('admin.classes.join'), // <-- use Ziggy route
            { class_id: classData.value.id },
            {
                preserveScroll: true,
                onSuccess: () => router.reload(),
                onError: (errors) =>
                    console.error('Failed to join class:', errors),
            },
        );
    }
};
</script>

<template>
    <AppLayout>
        <div class="container mx-auto px-4 py-8">
            <!-- Loading State -->
            <div v-if="!classData" class="py-12 text-center">
                <div
                    class="inline-block h-8 w-8 animate-spin rounded-full border-b-2 border-amber-500"
                ></div>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Loading class details...
                </p>
            </div>

            <!-- Content -->
            <div v-else>
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-4">
                                <Link
                                    :href="classesIndexUrl"
                                    class="flex items-center gap-2 text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                                >
                                    <ArrowLeft class="h-4 w-4" />
                                    Back to Classes
                                </Link>
                                <div
                                    class="h-6 w-px bg-gray-300 dark:bg-gray-600"
                                ></div>
                                <h1
                                    class="text-3xl font-bold text-gray-900 dark:text-white"
                                >
                                    {{ classData.name }}
                                </h1>
                            </div>
                            <p class="mt-2 text-gray-600 dark:text-gray-400">
                                {{ classData.subject_code }} • Semester
                                {{ classData.semester }} • Section
                                {{ classData.section }}
                            </p>
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
                </div>

                <!-- Class Info Card -->
                <div class="mb-8">
                    <div class="grid gap-6 md:grid-cols-3">
                        <!-- Class Details -->
                        <div
                            class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800"
                        >
                            <h3
                                class="mb-4 text-lg font-semibold text-gray-900 dark:text-white"
                            >
                                Class Information
                            </h3>
                            <div class="space-y-3">
                                <div>
                                    <p
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        Faculty
                                    </p>
                                    <p
                                        class="font-medium text-gray-900 dark:text-white"
                                    >
                                        {{ classData.faculty?.name || 'N/A' }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        Enrollment Code
                                    </p>
                                    <code
                                        class="rounded bg-gray-100 px-3 py-1 font-mono text-sm font-bold text-gray-800 dark:bg-gray-600 dark:text-gray-200"
                                    >
                                        {{ classData.enrollment_code }}
                                    </code>
                                </div>
                                <div>
                                    <p
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        Created
                                    </p>
                                    <p
                                        class="font-medium text-gray-900 dark:text-white"
                                    >
                                        {{ formatDate(classData.created_at) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Statistics -->
                        <div
                            class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800"
                        >
                            <h3
                                class="mb-4 text-lg font-semibold text-gray-900 dark:text-white"
                            >
                                Statistics
                            </h3>
                            <div class="space-y-3">
                                <div>
                                    <p
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        Total Students
                                    </p>
                                    <p
                                        class="text-2xl font-bold text-gray-900 dark:text-white"
                                    >
                                        {{ enrollmentCount }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        Total Assignments
                                    </p>
                                    <p
                                        class="text-2xl font-bold text-gray-900 dark:text-white"
                                    >
                                        {{ classData.assignments?.length || 0 }}
                                    </p>
                                </div>
                                <div v-if="!isFaculty && isEnrolled">
                                    <p
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        Your Submissions
                                    </p>
                                    <p
                                        class="text-2xl font-bold text-gray-900 dark:text-white"
                                    >
                                        0
                                        <!-- You can calculate this if you track submissions -->
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div
                            class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800"
                        >
                            <h3
                                class="mb-4 text-lg font-semibold text-gray-900 dark:text-white"
                            >
                                Quick Actions
                            </h3>
                            <div class="space-y-3">
                                <Link
                                    :href="assignmentIndexUrl"
                                    class="block rounded-md bg-blue-100 px-4 py-3 text-center text-sm font-medium text-blue-700 hover:bg-blue-200 dark:bg-blue-900 dark:text-blue-200"
                                >
                                    View All Assignments
                                </Link>
                                <div v-if="isFaculty">
                                    <StudentList
                                        :students="safeEnrollments"
                                        :class-id="classData.id"
                                        :is-faculty="true"
                                    />
                                </div>
                                <div v-if="!isFaculty && !isEnrolled">
                                    <button
                                        @click="joinClass"
                                        class="block w-full rounded-md bg-green-100 px-4 py-3 text-center text-sm font-medium text-green-700 hover:bg-green-200 dark:bg-green-900 dark:text-green-200"
                                    >
                                        Join This Class
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assignments Section -->
                <div>
                    <div class="mb-6 flex items-center justify-between">
                        <h2
                            class="text-2xl font-bold text-gray-900 dark:text-white"
                        >
                            Assignments
                        </h2>
                        <div class="flex gap-2">
                            <Link
                                v-if="!isFaculty && isEnrolled"
                                :href="assignmentIndexUrl"
                                class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300"
                            >
                                View All
                            </Link>
                        </div>
                    </div>

                    <!-- Assignments Grid -->
                    <div
                        v-if="classData.assignments?.length"
                        class="grid gap-6 md:grid-cols-2"
                    >
                        <AssignmentCard
                            v-for="assignment in classData.assignments"
                            :key="assignment.id"
                            :assignment="assignment"
                            :class-id="classData.id"
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
                            No assignments yet
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
            </div>
        </div>
    </AppLayout>
</template>
