<!-- resources/js/components/classes/ClassForm.vue -->
<template>
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
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">
                {{ form.errors.name }}
            </p>
        </div>

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

        <!-- Semester and Section -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Semester *
                </label>
                <select
                    v-model="form.semester"
                    class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    required
                >
                    <option value="">Select Semester</option>
                    <option v-for="n in 12" :key="n" :value="n">
                        Semester {{ n }}
                    </option>
                </select>
                <p
                    v-if="form.errors.semester"
                    class="mt-1 text-sm text-red-500"
                >
                    {{ form.errors.semester }}
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
                <p v-if="form.errors.section" class="mt-1 text-sm text-red-500">
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
            <p v-if="form.errors.description" class="mt-1 text-sm text-red-500">
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
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

const props = defineProps<{
    classId?: number;
    initialData?: {
        name: string;
        description?: string;
        subject_code: string;
        semester: number;
        section: string;
    };
}>();

const form = useForm({
    name: props.initialData?.name || '',
    description: props.initialData?.description || '',
    subject_code: props.initialData?.subject_code || '',
    semester: props.initialData?.semester || '',
    section: props.initialData?.section || '',
});

const emit = defineEmits<{
    submit: [data: typeof form.data];
}>();

const submit = () => {
    emit('submit', form.data);
};
</script>
