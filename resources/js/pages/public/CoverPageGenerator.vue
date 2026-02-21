<script setup lang="ts">
import MainLayout from '@/layouts/PublicLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import axios from 'axios'; // Add axios for better HTTP handling
import { ref } from 'vue';
import { route } from 'ziggy-js';

interface Template {
    id: number;
    name: string;
    preview: string;
}

interface Props {
    templates: Template[];
}

const props = defineProps<Props>();

const selectedTemplate = ref<number | null>(null);
const previewLoading = ref(false);
const previewError = ref<string | null>(null);

const form = useForm({
    template_id: null as number | null,
    name: '',
    id: '',
    department: '',
    semester: '',
    session: '',
    subject: '',
    title: '',
});

const selectTemplate = (templateId: number) => {
    selectedTemplate.value = templateId;
    form.template_id = templateId;
};

const semesters = [
    '1st Semester',
    '2nd Semester',
    '3rd Semester',
    '4th Semester',
    '5th Semester',
    '6th Semester',
    '7th Semester',
    '8th Semester',
];

const departments = [
    'Computer Science & Engineering',
    'Electrical Engineering',
    'Mechanical Engineering',
    'Civil Engineering',
    'Business Administration',
    'Economics',
    'English',
    'Mathematics',
];

// Fixed preview method
const previewPDF = async () => {
    // Validate required fields
    if (
        !form.template_id ||
        !form.name ||
        !form.id ||
        !form.department ||
        !form.semester ||
        !form.session
    ) {
        previewError.value = 'Please fill in all required fields';
        setTimeout(() => {
            previewError.value = null;
        }, 3000);
        return;
    }

    previewLoading.value = true;
    previewError.value = null;

    try {
        // Use axios for better error handling
        const response = await axios.post(
            route('cover-page-generator.preview'),
            form.data(),
            {
                responseType: 'blob',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/pdf',
                    'X-CSRF-TOKEN': document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute('content'),
                },
            },
        );

        // Check if response is PDF
        if (response.data.type === 'application/pdf') {
            // Create blob URL and open in new tab
            const url = window.URL.createObjectURL(
                new Blob([response.data], { type: 'application/pdf' }),
            );
            window.open(url, '_blank');

            // Clean up
            setTimeout(() => {
                window.URL.revokeObjectURL(url);
            }, 100);
        } else {
            throw new Error('Response is not a PDF');
        }
    } catch (error: any) {
        console.error('Preview failed:', error);

        // Handle error response
        if (error.response && error.response.data) {
            // Try to get error message from response
            try {
                const text = await error.response.data.text();
                const errorData = JSON.parse(text);
                previewError.value =
                    errorData.message || 'Failed to generate preview';
            } catch {
                previewError.value =
                    'Failed to generate preview. Please check your input and try again.';
            }
        } else {
            previewError.value = 'Network error. Please try again.';
        }
    } finally {
        previewLoading.value = false;
    }
};

const resetForm = () => {
    form.reset();
    selectedTemplate.value = null;
    previewError.value = null;
};
</script>

<template>
    <Head title="Cover Page Generator">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <MainLayout :padded="true">
        <div class="min-h-screen bg-gray-50 py-12 dark:bg-gray-900">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-12 text-center">
                    <h1
                        class="mb-4 text-4xl font-bold text-gray-900 dark:text-white"
                    >
                        Cover Page Generator
                    </h1>
                    <p class="text-lg text-gray-600 dark:text-gray-400">
                        Select a template and fill in your details to generate a
                        professional cover page
                    </p>
                </div>

                <!-- Template Selection -->
                <div class="mb-12">
                    <h2
                        class="mb-6 text-2xl font-semibold text-gray-900 dark:text-white"
                    >
                        1. Choose a Template
                    </h2>
                    <div
                        class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4"
                    >
                        <div
                            v-for="template in templates"
                            :key="template.id"
                            @click="selectTemplate(template.id)"
                            class="transform cursor-pointer transition-all duration-300 hover:scale-105"
                            :class="{
                                'rounded-lg ring-4 ring-blue-500':
                                    selectedTemplate === template.id,
                            }"
                        >
                            <div
                                class="overflow-hidden rounded-lg bg-white shadow-lg dark:bg-gray-800"
                            >
                                <div
                                    class="flex h-40 items-center justify-center bg-gradient-to-r from-blue-500 to-purple-600"
                                >
                                    <span class="font-bold text-white">{{
                                        template.name
                                    }}</span>
                                </div>
                                <div class="p-4">
                                    <h3
                                        class="text-center font-medium text-gray-900 dark:text-white"
                                    >
                                        {{ template.name }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Section -->
                <div
                    v-if="selectedTemplate"
                    class="rounded-lg bg-white p-8 shadow-xl dark:bg-gray-800"
                >
                    <h2
                        class="mb-6 text-2xl font-semibold text-gray-900 dark:text-white"
                    >
                        2. Fill in Your Details
                    </h2>

                    <!-- Error Message -->
                    <div
                        v-if="previewError"
                        class="mb-6 rounded-lg border border-red-400 bg-red-100 p-4 text-red-700"
                    >
                        {{ previewError }}
                    </div>

                    <form class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Name -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Full Name *
                                </label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    placeholder="Enter your full name"
                                />
                                <p
                                    v-if="form.errors.name"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <!-- Student ID -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Student ID *
                                </label>
                                <input
                                    v-model="form.id"
                                    type="text"
                                    required
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    placeholder="Enter your student ID"
                                />
                                <p
                                    v-if="form.errors.id"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.id }}
                                </p>
                            </div>

                            <!-- Department -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Department *
                                </label>
                                <select
                                    v-model="form.department"
                                    required
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                >
                                    <option value="">Select Department</option>
                                    <option
                                        v-for="dept in departments"
                                        :key="dept"
                                        :value="dept"
                                    >
                                        {{ dept }}
                                    </option>
                                </select>
                                <p
                                    v-if="form.errors.department"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.department }}
                                </p>
                            </div>

                            <!-- Semester -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Semester *
                                </label>
                                <select
                                    v-model="form.semester"
                                    required
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                >
                                    <option value="">Select Semester</option>
                                    <option
                                        v-for="sem in semesters"
                                        :key="sem"
                                        :value="sem"
                                    >
                                        {{ sem }}
                                    </option>
                                </select>
                                <p
                                    v-if="form.errors.semester"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.semester }}
                                </p>
                            </div>

                            <!-- Session -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Session *
                                </label>
                                <input
                                    v-model="form.session"
                                    type="text"
                                    required
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    placeholder="e.g., 2023-2024"
                                />
                                <p
                                    v-if="form.errors.session"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.session }}
                                </p>
                            </div>

                            <!-- Subject (Optional) -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Subject (Optional)
                                </label>
                                <input
                                    v-model="form.subject"
                                    type="text"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    placeholder="Enter subject name"
                                />
                            </div>

                            <!-- Title (Optional) -->
                            <div class="md:col-span-2">
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Document Title (Optional)
                                </label>
                                <input
                                    v-model="form.title"
                                    type="text"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    placeholder="e.g., Final Year Project, Lab Report, etc."
                                />
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end space-x-4 pt-6">
                            <button
                                type="button"
                                @click="resetForm"
                                class="rounded-lg border border-gray-300 px-6 py-3 text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                            >
                                Reset
                            </button>
                            <button
                                type="button"
                                @click="previewPDF"
                                :disabled="previewLoading || form.processing"
                                class="rounded-lg border border-gray-300 px-6 py-3 text-gray-700 transition-colors hover:bg-gray-50 disabled:opacity-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                            >
                                <span v-if="previewLoading"
                                    >Generating Preview...</span
                                >
                                <span v-else>Preview</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- No Template Selected Message -->
                <div
                    v-else
                    class="rounded-lg bg-white py-12 text-center shadow-xl dark:bg-gray-800"
                >
                    <svg
                        class="mx-auto h-12 w-12 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                        />
                    </svg>
                    <h3
                        class="mt-4 text-lg font-medium text-gray-900 dark:text-white"
                    >
                        No Template Selected
                    </h3>
                    <p class="mt-2 text-gray-500 dark:text-gray-400">
                        Please select a template from above to continue
                    </p>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
