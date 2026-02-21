<script setup lang="ts">
import AssignmentCard from '@/components/assignments/AssignmentCard.vue';
import StudentList from '@/components/classes/StudentList.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CheckCircle,
    FileText,
    Plus,
    XCircle,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

// Import your Wayfinder route definitions
import { route } from 'ziggy-js';

// Props should match what your controller sends
const props = defineProps<{
    classData: any; // From controller: 'class' => $class
    isEnrolled: boolean;
    isFaculty: boolean;
    enrollmentCount: number;
    isActive: boolean;
}>();

// Loading state for status update
const isUpdatingStatus = ref(false);

// Create a local reactive reference for isActive that syncs with props
const localIsActive = ref(props.isActive);

// Watch for changes in props.isActive and update localIsActive
watch(
    () => props.isActive,
    (newValue) => {
        localIsActive.value = newValue;
    },
    { immediate: true },
);

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

const classStatusUrl = computed(() => {
    if (!classData.value?.id) return '';
    return route('admin.classes.status', { class: classData.value.id });
});

const formatDate = (dateString: string) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const joinClass = () => {
    if (confirm('Are you sure you want to join this class?')) {
        router.post(
            route('admin.classes.join'),
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

// Toggle class status (active/inactive)
const toggleClassStatus = () => {
    const newStatus = !localIsActive.value;
    const action = newStatus ? 'reopen' : 'close';
    const message = `Are you sure you want to ${action} this class? ${
        action === 'close'
            ? 'Students will not be able to access or join this class.'
            : 'Students will be able to access and join this class again.'
    }`;

    if (confirm(message)) {
        isUpdatingStatus.value = true;

        router.put(
            classStatusUrl.value,
            {}, // No additional data needed
            {
                preserveScroll: true,
                onSuccess: () => {
                    // Update local state immediately for better UX
                    localIsActive.value = newStatus;

                    // Reload only the necessary data to get fresh data from server
                    router.reload({
                        only: ['classData', 'enrollmentCount', 'isActive'],
                        preserveUrl: true,
                        onSuccess: () => {
                            isUpdatingStatus.value = false;
                            // DO NOT show alert here - let the flash message handle it
                        },
                        onError: (errors) => {
                            console.error('Failed to reload:', errors);
                            isUpdatingStatus.value = false;
                        },
                    });
                },
                onError: (errors) => {
                    console.error(`Failed to ${action} class:`, errors);
                    // Show error message (this is not a success, so it's okay)
                    alert(`Failed to ${action} class. Please try again.`);
                    isUpdatingStatus.value = false;
                },
            },
        );
    }
};

const leaveClass = () => {
    if (confirm('Are you sure you want to leave this class?')) {
        router.post(
            route('admin.classes.leave'),
            { class_id: classData.value.id },
            {
                preserveScroll: true,
                onSuccess: () => router.reload(),
                onError: (errors) =>
                    console.error('Failed to leave class:', errors),
            },
        );
    }
};

const deleteClass = () => {
    if (
        confirm(
            'Are you sure you want to delete this class? This action cannot be undone.',
        )
    ) {
        router.delete(
            route('admin.classes.destroy', { class: classData.value.id }),
            {
                preserveScroll: true,
                onSuccess: () => router.visit(route('admin.classes.index')),
                onError: (errors) =>
                    console.error('Failed to delete class:', errors),
            },
        );
    }
};

// Check if class is active - use localIsActive for immediate UI updates
const isClassActive = computed(() => {
    return localIsActive.value !== false;
});

// Status badge class
const statusBadgeClass = computed(() => {
    return isClassActive.value
        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
        : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
});

// Status icon component
const StatusIcon = computed(() => {
    return isClassActive.value ? CheckCircle : XCircle;
});

// Button text and class for status toggle
const statusButtonConfig = computed(() => {
    return {
        text: isClassActive.value ? 'Close Class' : 'Reopen Class',
        class: isClassActive.value
            ? 'bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900 dark:text-red-200 dark:hover:bg-red-800'
            : 'bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900 dark:text-green-200 dark:hover:bg-green-800',
        icon: isClassActive.value ? XCircle : CheckCircle,
    };
});
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
                                <!-- Status Badge -->
                                <span
                                    class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-sm font-medium"
                                    :class="statusBadgeClass"
                                >
                                    <component
                                        :is="StatusIcon"
                                        class="h-4 w-4"
                                    />
                                    {{ isClassActive ? 'Active' : 'Closed' }}
                                </span>
                            </div>
                            <p class="mt-2 text-gray-600 dark:text-gray-400">
                                {{ classData.subject_code }} • Semester
                                {{ classData.semester }} • Section
                                {{ classData.section }}
                            </p>
                        </div>
                        <div v-if="isFaculty" class="flex gap-2">
                            <!-- New Assignment Button -->
                            <Link
                                :href="assignmentCreateUrl"
                                class="flex items-center gap-2 rounded-md bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600 focus:ring-2 focus:ring-amber-400 focus:outline-none"
                            >
                                <Plus class="h-4 w-4" />
                                New Assignment
                            </Link>
                            <!-- Close/Reopen Class Button -->
                            <button
                                @click="toggleClassStatus"
                                :disabled="isUpdatingStatus"
                                class="flex items-center gap-2 rounded-md px-4 py-2 text-sm font-medium focus:ring-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                :class="statusButtonConfig.class"
                            >
                                <component
                                    :is="statusButtonConfig.icon"
                                    class="h-4 w-4"
                                />
                                <span v-if="!isUpdatingStatus">{{
                                    statusButtonConfig.text
                                }}</span>
                                <span v-else>Updating...</span>
                            </button>
                            <button
                                @click="deleteClass()"
                                class="flex items-center gap-2 rounded-md bg-red-500 px-4 py-2 text-sm font-medium text-white hover:bg-red-600 focus:ring-2 focus:ring-red-400 focus:outline-none"
                            >
                                <XCircle class="h-4 w-4" />
                                Delete Class
                            </button>
                        </div>
                        <div v-if="!isFaculty" class="flex gap-2">
                            <button
                                @click="leaveClass()"
                                class="flex items-center gap-2 rounded-md bg-red-500 px-4 py-2 text-sm font-medium text-white hover:bg-red-600 focus:ring-2 focus:ring-red-400 focus:outline-none"
                            >
                                <XCircle class="h-4 w-4" />
                                Leave Class
                            </button>
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
                                        Status
                                    </p>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium"
                                            :class="statusBadgeClass"
                                        >
                                            <component
                                                :is="StatusIcon"
                                                class="h-3 w-3"
                                            />
                                            {{
                                                isClassActive
                                                    ? 'Active'
                                                    : 'Closed'
                                            }}
                                        </span>
                                        <span
                                            v-if="
                                                !isClassActive &&
                                                classData.closed_at
                                            "
                                            class="text-xs text-gray-500"
                                        >
                                            Closed:
                                            {{
                                                formatDate(classData.closed_at)
                                            }}
                                        </span>
                                    </div>
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
                                        class="block w-full rounded-md px-4 py-3 text-center text-sm font-medium"
                                        :class="{
                                            'bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900 dark:text-green-200':
                                                isClassActive,
                                            'cursor-not-allowed bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400':
                                                !isClassActive,
                                        }"
                                        :disabled="!isClassActive"
                                    >
                                        Join This Class
                                    </button>
                                    <p
                                        v-if="!isClassActive"
                                        class="mt-2 text-center text-xs text-red-500"
                                    >
                                        This class is currently closed and
                                        cannot accept new students
                                    </p>
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
