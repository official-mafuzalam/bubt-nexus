<!-- resources/js/pages/Assignments/Create.vue -->
<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <Link
                            :href="route('assignments.index', classData.id)"
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
    </AppLayout>
</template>

<script setup lang="ts">
import AssignmentForm from '@/components/assignments/AssignmentForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { index as classesIndex } from '@/routes/admin/classes';
import { router } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps<{
    classData: {
        id: number;
        name: string;
    };
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Classes', href: classesIndex().url },
    {
        title: props.classData.name,
        href: route('classes.show', props.classData.id),
    },
    {
        title: 'Assignments',
        href: route('assignments.index', props.classData.id),
    },
    { title: 'Create', href: '' },
];

const createAssignment = (formData: FormData) => {
    router.post(route('assignments.store', props.classData.id), formData, {
        preserveScroll: true,
        onError: () => {
            // Errors will be displayed in the form component
        },
    });
};

const cancel = () => {
    router.visit(route('assignments.index', props.classData.id));
};
</script>
