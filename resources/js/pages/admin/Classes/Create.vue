<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { computed } from 'vue';

// Import your Wayfinder route definitions
import classes from '@/routes/admin/classes';

// Generate the index URL
const classesIndexUrl = computed(() => {
    return classes.index.url();
});

// Props with proper TypeScript typing
interface Program {
    id: number | string;
    name: string;
    code: string;
}

interface Props {
    programs: Program[];
}

const props = withDefaults(defineProps<Props>(), {
    programs: () => [],
});

// Use Inertia form directly
const form = useForm({
    name: '',
    description: '',
    subject_code: '',
    program_id: '',
    intake: '',
    section: '',
});

const submit = () => {
    // CORRECT WAY: Use the form.post method directly
    form.post(classes.store.url(), {
        preserveScroll: true,
        onSuccess: () => {
            // Success - redirected by controller
            console.log('Class created successfully!');
        },
        onError: (errors) => {
            console.error('Form errors:', errors);
        },
        onFinish: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <AppLayout>
        <div class="container mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center gap-4">
                    <Link
                        :href="classesIndexUrl"
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
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Name -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Class Name *
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                required
                                placeholder="e.g., Introduction to Programming"
                            />
                            <p
                                v-if="form.errors.name"
                                class="mt-1 text-sm text-red-500"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <!-- Subject Code -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Subject Code *
                                </label>
                                <input
                                    v-model="form.subject_code"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                    required
                                    placeholder="e.g., CSE101"
                                />
                                <p
                                    v-if="form.errors.subject_code"
                                    class="mt-1 text-sm text-red-500"
                                >
                                    {{ form.errors.subject_code }}
                                </p>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Program *
                                </label>
                                <select
                                    v-model="form.program_id"
                                    class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                    required
                                >
                                    <option value="">Select Program</option>
                                    <option
                                        v-for="program in props.programs"
                                        :key="program.id"
                                        :value="program.id"
                                    >
                                        {{ program.name }} ({{ program.code }})
                                    </option>
                                </select>
                                <p
                                    v-if="form.errors.program_id"
                                    class="mt-1 text-sm text-red-500"
                                >
                                    {{ form.errors.program_id }}
                                </p>
                            </div>
                        </div>

                        <!-- Program and Semester -->
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Intake *
                                </label>
                                <input
                                    v-model="form.intake"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                    required
                                    placeholder="e.g., A"
                                />
                                <p
                                    v-if="form.errors.intake"
                                    class="mt-1 text-sm text-red-500"
                                >
                                    {{ form.errors.intake }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Section *
                                </label>
                                <input
                                    v-model="form.section"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                    required
                                    placeholder="e.g., A"
                                />
                                <p
                                    v-if="form.errors.section"
                                    class="mt-1 text-sm text-red-500"
                                >
                                    {{ form.errors.section }}
                                </p>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Description
                            </label>
                            <textarea
                                v-model="form.description"
                                rows="4"
                                class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                placeholder="Brief description about the class..."
                            ></textarea>
                            <p
                                v-if="form.errors.description"
                                class="mt-1 text-sm text-red-500"
                            >
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-md bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600 focus:ring-2 focus:ring-amber-400 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <span v-if="form.processing">Creating...</span>
                                <span v-else>Create Class</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
