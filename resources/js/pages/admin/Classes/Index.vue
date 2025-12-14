<script setup lang="ts">
import ClassCard from '@/components/classes/ClassCard.vue';
import EnrollmentForm from '@/components/classes/EnrollmentForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { Plus, Users } from 'lucide-vue-next';
import { computed } from 'vue';

// Import your Wayfinder route definitions - make sure this is the correct path
import classes from '@/routes/admin/classes'; // Or '@/routes/admin/classes'
import { BreadcrumbItem } from '@/types';
import { route } from 'ziggy-js';

interface Props {
    classes?: {
        data: any[];
        links: any[];
    };
    userType?: 'faculty' | 'student';
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Class Management', href: route('admin.classes.index') },
];

const props = withDefaults(defineProps<Props>(), {
    classes: undefined,
    userType: 'student',
});

// Safe computed properties
const safeClasses = computed(() => {
    return props.classes || { data: [], links: [] };
});

const hasClasses = computed(() => {
    return safeClasses.value.data && safeClasses.value.data.length > 0;
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex flex-col gap-4 rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
        >
            <div
                class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center"
            >
                <!-- Header -->
                <div class="mb-8">
                    <h1
                        class="text-3xl font-bold text-gray-900 dark:text-white"
                    >
                        My Classes
                    </h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        {{
                            userType === 'faculty'
                                ? 'Manage your classes and assignments'
                                : 'Browse and join classes'
                        }}
                    </p>
                </div>

                <!-- Create Class Button (Faculty only) -->
                <div v-if="userType === 'faculty'" class="mb-6">
                    <Link
                        :href="classes.create.url()"
                        class="inline-flex items-center gap-2 rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600 focus:ring-2 focus:ring-amber-400 focus:outline-none"
                    >
                        <Plus class="h-4 w-4" />
                        Create New Class
                    </Link>
                </div>
            </div>

            <!-- Join Class Form (Student only) -->
            <div v-if="userType === 'student'" class="mb-8">
                <EnrollmentForm />
            </div>

            <!-- Classes Grid -->
            <div
                v-if="hasClasses"
                class="grid gap-6 md:grid-cols-2 lg:grid-cols-3"
            >
                <ClassCard
                    v-for="classItem in safeClasses.data"
                    :key="classItem.id"
                    :class-item="classItem"
                    :is-faculty="userType === 'faculty'"
                />
            </div>

            <!-- Empty State -->
            <div
                v-if="!hasClasses"
                class="rounded-lg border-2 border-dashed border-gray-300 p-12 text-center dark:border-gray-700"
            >
                <Users class="mx-auto h-12 w-12 text-gray-400" />
                <h3
                    class="mt-4 text-lg font-medium text-gray-900 dark:text-white"
                >
                    No classes found
                </h3>
                <p class="mt-2 text-gray-500 dark:text-gray-400">
                    {{
                        userType === 'faculty'
                            ? 'Create your first class to get started'
                            : 'Join a class using an enrollment code'
                    }}
                </p>
            </div>
        </div>
    </AppLayout>
</template>
