<!-- resources/js/pages/Classes/Index.vue -->
<template>
    <AppLayout>
        <div class="container mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
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
                    :href="route('classes.create')"
                    class="inline-flex items-center gap-2 rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600 focus:ring-2 focus:ring-amber-400 focus:outline-none"
                >
                    <Plus class="h-4 w-4" />
                    Create New Class
                </Link>
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

            <!-- Pagination -->
            <!-- <div v-if="hasClasses && hasPagination" class="mt-8">
                <Pagination :links="safeClasses.links" />
            </div> -->

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

<script setup lang="ts">
import ClassCard from '@/components/classes/ClassCard.vue';
import EnrollmentForm from '@/components/classes/EnrollmentForm.vue';
// import Pagination from '@/components/ui/Pagination.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Plus, Users } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    classes?: {
        data: any[];
        links: any[];
    };
    userType?: 'faculty' | 'student';
}

const props = withDefaults(defineProps<Props>(), {
    classes: undefined,
    userType: 'student',
});

// Get route helper from Inertia
const { route } = usePage();

// Safe computed properties
const safeClasses = computed(() => {
    return props.classes || { data: [], links: [] };
});

const hasClasses = computed(() => {
    return safeClasses.value.data && safeClasses.value.data.length > 0;
});

const hasPagination = computed(() => {
    return safeClasses.value.links && safeClasses.value.links.length > 3;
});
</script>
