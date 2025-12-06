<!-- resources/js/pages/Classes/Show.vue -->
<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-4">
                            <Link
                                :href="route('classes.index')"
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
                            :href="route('assignments.create', classData.id)"
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
                                    {{ classData.faculty.name }}
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
                                    {{ assignments.length }}
                                </p>
                            </div>
                            <div v-if="userType === 'student'">
                                <p
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
                                    Your Submissions
                                </p>
                                <p
                                    class="text-2xl font-bold text-gray-900 dark:text-white"
                                >
                                    {{ submittedCount }}
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
                                :href="route('assignments.index', classData.id)"
                                class="block rounded-md bg-blue-100 px-4 py-3 text-center text-sm font-medium text-blue-700 hover:bg-blue-200 dark:bg-blue-900 dark:text-blue-200"
                            >
                                View All Assignments
                            </Link>
                            <div v-if="isFaculty">
                                <StudentList
                                    :students="classData.enrollments"
                                    :class-id="classData.id"
                                    :is-faculty="true"
                                />
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
                            v-if="userType === 'student'"
                            :href="route('assignments.index', classData.id)"
                            class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300"
                        >
                            View All
                        </Link>
                    </div>
                </div>

                <!-- Assignments Grid -->
                <div
                    v-if="assignments.length"
                    class="grid gap-6 md:grid-cols-2"
                >
                    <AssignmentCard
                        v-for="assignment in assignments"
                        :key="assignment.id"
                        :assignment="assignment"
                        :class-id="classData.id"
                        :is-faculty="isFaculty"
                        :submission="getSubmission(assignment.id)"
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
    </AppLayout>
</template>

<script setup lang="ts">
import AssignmentCard from '@/components/assignments/AssignmentCard.vue';
import StudentList from '@/components/classes/StudentList.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { index as classesIndex } from '@/routes/admin/classes';
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, FileText, Plus } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    classData: {
        id: number;
        name: string;
        description?: string;
        subject_code: string;
        semester: number;
        section: string;
        enrollment_code: string;
        created_at: string;
        faculty: {
            id: number;
            name: string;
        };
        enrollments: Array<{
            id: number;
            student: {
                id: number;
                name: string;
                email: string;
                user_detail?: {
                    student_id?: string;
                };
            };
        }>;
    };
    assignments: Array<{
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
    userType: 'faculty' | 'student';
    isEnrolled: boolean;
    enrollmentCount: number;
    studentSubmissions: Array<{
        assignment_id: number;
        status: string;
        marks_obtained?: number;
    }>;
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Classes', href: classesIndex().url },
    { title: props.classData.name, href: '' },
];

const isFaculty = props.userType === 'faculty';

const submittedCount = computed(() => {
    return props.studentSubmissions.filter(
        (sub) => sub.status === 'submitted' || sub.status === 'graded',
    ).length;
});

const getSubmission = (assignmentId: number) => {
    return props.studentSubmissions.find(
        (sub) => sub.assignment_id === assignmentId,
    );
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>
