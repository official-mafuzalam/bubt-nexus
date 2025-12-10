<!-- resources/js/components/classes/EnrollmentForm.vue -->
<template>
    <div
        class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800"
    >
        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
            Join a Class
        </h3>
        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label
                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Enrollment Code
                </label>
                <div class="flex gap-2">
                    <input
                        v-model="form.enrollment_code"
                        type="text"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        placeholder="Enter class code"
                        required
                        :disabled="form.processing"
                    />
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <span v-if="form.processing">Joining...</span>
                        <span v-else>Join</span>
                    </button>
                </div>
                <p
                    v-if="form.errors.enrollment_code"
                    class="mt-1 text-sm text-red-500"
                >
                    {{ form.errors.enrollment_code }}
                </p>
            </div>

            <p class="text-sm text-gray-500 dark:text-gray-400">
                Ask your teacher for the enrollment code to join their class.
            </p>
        </form>
    </div>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

const form = useForm({
    enrollment_code: '',
});

const submit = () => {
    form.post(route('admin.classes.join'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>
