<!-- resources/js/pages/Assignments/Create.vue -->
<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto px-4 py-8">
            <!-- Loading State -->
            <div v-if="!classData" class="py-12 text-center">
                <div
                    class="inline-block h-8 w-8 animate-spin rounded-full border-b-2 border-amber-500"
                ></div>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Loading...</p>
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
                                Create New Assignment
                            </h1>
                        </div>
                    </div>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Create a new assignment for {{ classData.name }}
                    </p>
                </div>

                <!-- Form -->
                <div class="mx-auto max-w-3xl">
                    <div
                        class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-800"
                    >
                        <AssignmentForm
                            :class-id="classData.id"
                            @submit="createAssignment"
                            @cancel="cancel"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AssignmentForm from '@/components/assignments/AssignmentForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { computed } from 'vue';

// Import your Wayfinder route definitions
import { dashboard } from '@/routes';
import assignments from '@/routes/admin/assignments';
import classes from '@/routes/admin/classes';

// Make classData optional since it might not be passed
const props = defineProps<{
    classData?: {
        id: number;
        name: string;
        subject_code?: string;
        semester?: number;
        section?: string;
    };
}>();

// Safe computed properties
const safeClassData = computed(
    () => props.classData || { id: 0, name: 'Class' },
);

// Generate URLs
const assignmentsIndexUrl = computed(() => {
    if (!safeClassData.value.id) return '#';
    return assignments.index.url({ class: safeClassData.value.id });
});

const classShowUrl = computed(() => {
    if (!safeClassData.value.id) return '#';
    return classes.show.url({ class: safeClassData.value.id });
});

// Breadcrumbs
const breadcrumbs = computed(() => [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Classes', href: classes.index.url() },
    { title: safeClassData.value.name || 'Class', href: classShowUrl.value },
    { title: 'Assignments', href: assignmentsIndexUrl.value },
    { title: 'Create', href: '' },
]);

const createAssignment = (formData: FormData) => {
    if (!safeClassData.value.id) return;

    router.post(
        assignments.store.url({ class: safeClassData.value.id }),
        formData,
        {
            preserveScroll: true,
            onError: (errors) => {
                console.error('Form errors:', errors);
            },
            onSuccess: () => {
                console.log('Assignment created successfully!');
            },
        },
    );
};

const cancel = () => {
    if (safeClassData.value.id) {
        router.visit(assignments.index.url({ class: safeClassData.value.id }));
    } else {
        router.visit(classes.index.url());
    }
};
</script>
