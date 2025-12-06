<!-- resources/js/pages/Classes/Create.vue -->
<template>
    <AppLayout>
        <div class="container mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('classes.index')"
                        class="flex items-center gap-2 text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Back to Classes
                    </Link>
                    <div class="h-6 w-px bg-gray-300 dark:bg-gray-600"></div>
                    <h1
                        class="text-3xl font-bold text-gray-900 dark:text-white"
                    >
                        Create New Class
                    </h1>
                </div>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Set up a new class for your students
                </p>
            </div>

            <!-- Form Card -->
            <div class="mx-auto max-w-2xl">
                <div
                    class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-800"
                >
                    <ClassForm @submit="createClass" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import ClassForm from '@/components/classes/ClassForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

const createClass = (formData: any) => {
    router.post(route('classes.store'), formData, {
        preserveScroll: true,
        onError: () => {
            // Errors will be displayed in the form component
        },
    });
};
</script>
