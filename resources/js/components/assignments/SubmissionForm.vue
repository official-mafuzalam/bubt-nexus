<!-- resources/js/components/assignments/SubmissionForm.vue -->
<template>
    <div
        class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800"
    >
        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
            Submit Assignment
        </h3>

        <div
            v-if="assignment.status === 'closed'"
            class="rounded-md bg-red-50 p-4 dark:bg-red-900/20"
        >
            <div class="flex items-center gap-2">
                <AlertCircle class="h-5 w-5 text-red-400" />
                <p class="text-sm font-medium text-red-800 dark:text-red-200">
                    This assignment is closed. Submissions are no longer
                    accepted.
                </p>
            </div>
        </div>

        <form v-else @submit.prevent="submit" class="space-y-6">
            <!-- Submission Content -->
            <div>
                <label
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Your Submission (Optional)
                </label>
                <textarea
                    v-model="form.content"
                    rows="6"
                    class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    placeholder="Type your answer, code, or explanation here..."
                ></textarea>
                <p v-if="form.errors.content" class="mt-1 text-sm text-red-500">
                    {{ form.errors.content }}
                </p>
            </div>

            <!-- File Attachments -->
            <div>
                <label
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    File Attachments
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
                    Maximum file size: 10MB per file
                </p>
            </div>

            <!-- Deadline Warning -->
            <div
                v-if="isNearDeadline"
                class="rounded-md bg-yellow-50 p-4 dark:bg-yellow-900/20"
            >
                <div class="flex items-center gap-2">
                    <AlertTriangle class="h-5 w-5 text-yellow-400" />
                    <p
                        class="text-sm font-medium text-yellow-800 dark:text-yellow-200"
                    >
                        Deadline is approaching! Submission due in
                        {{ timeLeft }}.
                    </p>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button
                    type="submit"
                    :disabled="
                        form.processing || assignment.status === 'closed'
                    "
                    class="rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <span v-if="form.processing">Submitting...</span>
                    <span v-else>Submit Assignment</span>
                </button>
            </div>
        </form>
    </div>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { AlertCircle, AlertTriangle, Paperclip, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    assignment: {
        id: number;
        deadline: string;
        status: string;
    };
    classId: number;
    existingSubmission?: {
        content?: string;
        attachments?: File[];
    };
}>();

const emit = defineEmits<{
    submitted: [];
}>();

const fileInput = ref<HTMLInputElement>();

const form = useForm({
    content: props.existingSubmission?.content || '',
    attachments: props.existingSubmission?.attachments || [],
});

const isNearDeadline = computed(() => {
    const deadline = new Date(props.assignment.deadline);
    const now = new Date();
    const diffHours = Math.floor(
        (deadline.getTime() - now.getTime()) / (1000 * 60 * 60),
    );
    return diffHours > 0 && diffHours < 24;
});

const timeLeft = computed(() => {
    const deadline = new Date(props.assignment.deadline);
    const now = new Date();
    const diffMs = deadline.getTime() - now.getTime();

    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
    const diffMinutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));

    if (diffHours > 0) return `${diffHours}h ${diffMinutes}m`;
    return `${diffMinutes}m`;
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
    formData.append('content', form.content);

    form.attachments.forEach((file, index) => {
        formData.append(`attachments[${index}]`, file);
    });

    form.post(
        route('submissions.submit', [props.classId, props.assignment.id]),
        {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                emit('submitted');
            },
        },
    );
};
</script>
