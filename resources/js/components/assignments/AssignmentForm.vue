<!-- resources/js/components/assignments/AssignmentForm.vue -->
<template>
    <form @submit.prevent="submit" class="space-y-6">
        <!-- Title -->
        <div>
            <label
                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
                Assignment Title *
            </label>
            <input
                v-model="form.title"
                type="text"
                class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                required
                placeholder="e.g., Python Programming Assignment 1"
            />
            <p v-if="errors.title" class="mt-1 text-sm text-red-500">
                {{ errors.title }}
            </p>
        </div>

        <!-- Description -->
        <div>
            <label
                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
                Description *
            </label>
            <textarea
                v-model="form.description"
                rows="4"
                class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                required
                placeholder="Detailed description of the assignment..."
            ></textarea>
            <p v-if="errors.description" class="mt-1 text-sm text-red-500">
                {{ errors.description }}
            </p>
        </div>

        <!-- Instructions -->
        <div>
            <label
                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
                Instructions (Optional)
            </label>
            <textarea
                v-model="form.instructions"
                rows="3"
                class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                placeholder="Any specific instructions for submission..."
            ></textarea>
            <p v-if="errors.instructions" class="mt-1 text-sm text-red-500">
                {{ errors.instructions }}
            </p>
        </div>

        <!-- Total Marks and Deadline -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Total Marks *
                </label>
                <input
                    v-model="form.total_marks"
                    type="number"
                    min="0"
                    max="1000"
                    step="0.01"
                    class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    required
                    placeholder="e.g., 100"
                />
                <p v-if="errors.total_marks" class="mt-1 text-sm text-red-500">
                    {{ errors.total_marks }}
                </p>
            </div>
            <div>
                <label
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Deadline *
                </label>
                <input
                    v-model="form.deadline"
                    type="datetime-local"
                    class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    required
                    :min="minDateTime"
                />
                <p v-if="errors.deadline" class="mt-1 text-sm text-red-500">
                    {{ errors.deadline }}
                </p>
            </div>
        </div>

        <!-- Attachments -->
        <div>
            <label
                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
                Attachments (Optional) [Only PDF, Image, Doc, and Zip files are allowed]
            </label>
            <div class="mt-2">
                <input
                    type="file"
                    ref="fileInput"
                    @change="handleFileUpload"
                    multiple
                    class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-full file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-gray-700 hover:file:bg-gray-200 dark:text-gray-400 dark:file:bg-gray-700 dark:file:text-gray-300"
                />
            </div>

            <!-- Selected Files -->
            <div v-if="form.attachments.length" class="mt-4">
                <h4
                    class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Selected Files ({{ form.attachments.length }})
                </h4>
                <div class="space-y-2">
                    <div
                        v-for="(file, index) in form.attachments"
                        :key="index"
                        class="flex items-center justify-between rounded-md bg-gray-50 px-3 py-2 dark:bg-gray-700"
                    >
                        <div class="flex items-center gap-2">
                            <Paperclip class="h-4 w-4 text-gray-400" />
                            <span
                                class="text-sm text-gray-600 dark:text-gray-300"
                            >
                                {{ file.name }}
                            </span>
                            <span class="text-xs text-gray-500">
                                ({{ formatFileSize(file.size) }})
                            </span>
                        </div>
                        <button
                            type="button"
                            @click="removeFile(index)"
                            class="text-red-500 hover:text-red-700"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>

            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                Maximum file size: 5MB. Allowed formats: PDF, Image, Doc, Zip.
            </p>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end gap-3">
            <button
                type="button"
                @click="$emit('cancel')"
                class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
            >
                Cancel
            </button>
            <button
                type="submit"
                :disabled="processing"
                class="rounded-md bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600 focus:ring-2 focus:ring-amber-400 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
            >
                <span v-if="processing">{{
                    editing ? 'Updating...' : 'Creating...'
                }}</span>
                <span v-else>{{
                    editing ? 'Update Assignment' : 'Create Assignment'
                }}</span>
            </button>
        </div>
    </form>
</template>

<script setup lang="ts">
import { Paperclip, X } from 'lucide-vue-next';
import { computed, reactive, ref } from 'vue';

interface Props {
    classId: number;
    editing?: boolean;
    initialData?: {
        title: string;
        description: string;
        instructions?: string;
        total_marks: number;
        deadline: string;
        attachments?: File[];
    };
    errors?: Record<string, string>;
    processing?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    editing: false,
    initialData: undefined,
    errors: () => ({}),
    processing: false,
});

const emit = defineEmits<{
    submit: [data: FormData];
    cancel: [];
}>();

const fileInput = ref<HTMLInputElement>();

// Use reactive instead of useForm since we're not using Inertia form directly
const form = reactive({
    title: props.initialData?.title || '',
    description: props.initialData?.description || '',
    instructions: props.initialData?.instructions || '',
    total_marks: props.initialData?.total_marks || 100,
    deadline: props.initialData?.deadline || '',
    attachments: props.initialData?.attachments || [],
});

const errors = computed(() => props.errors || {});
const processing = computed(() => props.processing || false);

const minDateTime = computed(() => {
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    return now.toISOString().slice(0, 16);
});

const handleFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        const files = Array.from(target.files);
        const updatedFiles = [...form.attachments, ...files];
        form.attachments = updatedFiles;
    }
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const removeFile = (index: number) => {
    const updatedFiles = form.attachments.filter((_, i) => i !== index);
    form.attachments = updatedFiles;
};

const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const submit = () => {
    const formData = new FormData();
    formData.append('title', form.title);
    formData.append('description', form.description);
    formData.append('instructions', form.instructions || '');
    formData.append('total_marks', form.total_marks.toString());
    formData.append('deadline', form.deadline);

    // Append attachments
    form.attachments.forEach((file, index) => {
        formData.append(`attachments[${index}]`, file);
    });

    emit('submit', formData);
};
</script>
