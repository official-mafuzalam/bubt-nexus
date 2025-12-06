<!-- resources/js/components/classes/StudentList.vue -->
<template>
    <div
        class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800"
    >
        <!-- Header -->
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Enrolled Students ({{ students.length }})
            </h3>
        </div>

        <!-- Student List -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr
                        class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-700"
                    >
                        <th
                            class="px-6 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Student
                        </th>
                        <th
                            class="px-6 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Student ID
                        </th>
                        <th
                            class="px-6 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Email
                        </th>
                        <th
                            v-if="isFaculty"
                            class="px-6 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr
                        v-for="student in students"
                        :key="student.id"
                        class="hover:bg-gray-50 dark:hover:bg-gray-800"
                    >
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-200 dark:bg-gray-600"
                                >
                                    <User
                                        class="h-4 w-4 text-gray-600 dark:text-gray-400"
                                    />
                                </div>
                                <div>
                                    <p
                                        class="font-medium text-gray-900 dark:text-white"
                                    >
                                        {{ student.name }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span
                                class="text-sm text-gray-600 dark:text-gray-400"
                            >
                                {{ student.user_detail?.student_id || 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span
                                class="text-sm text-gray-600 dark:text-gray-400"
                            >
                                {{ student.email }}
                            </span>
                        </td>
                        <td v-if="isFaculty" class="px-6 py-4">
                            <button
                                @click="removeStudent(student.id)"
                                class="rounded-md bg-red-100 px-3 py-1 text-xs font-medium text-red-700 hover:bg-red-200 dark:bg-red-900 dark:text-red-200"
                                title="Remove from class"
                            >
                                Remove
                            </button>
                        </td>
                    </tr>

                    <!-- Empty State -->
                    <tr v-if="!students.length">
                        <td colspan="4" class="px-6 py-12 text-center">
                            <div
                                class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400"
                            >
                                <Users class="mb-2 h-8 w-8 opacity-50" />
                                <p class="text-sm">No students enrolled yet</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { User, Users } from 'lucide-vue-next';

const props = defineProps<{
    students: Array<{
        id: number;
        name: string;
        email: string;
        user_detail?: {
            student_id?: string;
        };
    }>;
    classId: number;
    isFaculty: boolean;
}>();

const removeStudent = (studentId: number) => {
    if (
        confirm('Are you sure you want to remove this student from the class?')
    ) {
        router.delete(
            route('classes.removeStudent', {
                class: props.classId,
                student: studentId,
            }),
            {
                preserveScroll: true,
            },
        );
    }
};
</script>
