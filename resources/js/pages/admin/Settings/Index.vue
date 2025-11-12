<script setup lang="ts">
import AdminLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { update } from '@/routes/admin/settings';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    groups: any;
}>();

const page = usePage();

// Form handling
const form = useForm(
    Object.keys(props.groups).reduce((acc, groupName) => {
        props.groups[groupName].forEach((setting: any) => {
            acc[setting.key] = setting.value;
        });
        return acc;
    }, {}),
);

// File inputs handling
const fileInputs = ref<{ [key: string]: File | null }>({});

const handleFileChange = (key: string, event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        fileInputs.value[key] = target.files[0];
    }
};

const removeImage = (key: string) => {
    form[key] = '';
    fileInputs.value[key] = null;
};

// Submit form
const submit = () => {
    // Append files to form data
    Object.keys(fileInputs.value).forEach((key) => {
        if (fileInputs.value[key]) {
            form[key] = fileInputs.value[key];
        }
    });

    form.put(update().url, { preserveState: true });
};

// Tab functionality
const activeTab = ref<string>(Object.keys(props.groups)[0] || '');

const setActiveTab = (tab: string) => {
    activeTab.value = tab;
};

// Boolean toggle labels
const getBooleanLabel = (value: string | boolean) => {
    return value === '1' || value === true || value === 'true'
        ? 'Enabled'
        : 'Disabled';
};

// Get image URL
const getImageUrl = (path: string) => {
    return path ? `/storage/${path}` : '';
};
</script>

<template>
    <Head title="Site Settings" />

    <AdminLayout>
        <div
            class="flex flex-col gap-4 rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
        >
            <div class="flex items-center justify-between">
                <h2
                    class="text-2xl font-semibold text-gray-800 dark:text-gray-100"
                >
                    Site Settings
                </h2>
                <div class="flex space-x-2">
                    <a
                        :href="dashboard()"
                        class="rounded-md bg-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600"
                    >
                        Back to Dashboard
                    </a>
                </div>
            </div>

            <form @submit.prevent="submit" class="mt-4 space-y-6">
                <!-- Tabs Navigation -->
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button
                            v-for="(groupSettings, groupName) in groups"
                            :key="groupName"
                            type="button"
                            class="border-b-2 px-1 py-4 text-sm font-medium whitespace-nowrap transition-colors"
                            :class="
                                activeTab === groupName
                                    ? 'border-amber-500 text-amber-600 dark:text-amber-400'
                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
                            "
                            @click="setActiveTab(groupName)"
                        >
                            {{
                                groupName.charAt(0).toUpperCase() +
                                groupName.slice(1)
                            }}
                        </button>
                    </nav>
                </div>

                <!-- Tabs Content -->
                <div class="space-y-6">
                    <div
                        v-for="(groupSettings, groupName) in groups"
                        :key="groupName"
                        :class="{ hidden: activeTab !== groupName }"
                    >
                        <div class="mb-6">
                            <h3
                                class="text-lg font-medium text-gray-800 dark:text-gray-200"
                            >
                                {{
                                    groupName.charAt(0).toUpperCase() +
                                    groupName.slice(1)
                                }}
                                Settings
                            </h3>
                            <p
                                class="mt-1 text-sm text-gray-600 dark:text-gray-400"
                            >
                                Configure
                                {{ groupName.toLowerCase() }}
                                related settings
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div
                                v-for="setting in groupSettings"
                                :key="setting.key"
                                :class="{
                                    'md:col-span-2':
                                        setting.type === 'textarea',
                                }"
                            >
                                <div class="space-y-2">
                                    <label
                                        :for="`setting-${setting.key}`"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-200"
                                    >
                                        {{ setting.label }}
                                    </label>

                                    <p
                                        v-if="setting.description"
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        {{ setting.description }}
                                    </p>

                                    <!-- Text, Email, Number Inputs -->
                                    <input
                                        v-if="
                                            [
                                                'text',
                                                'email',
                                                'number',
                                            ].includes(setting.type)
                                        "
                                        :type="setting.type"
                                        :id="`setting-${setting.key}`"
                                        v-model="form[setting.key]"
                                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                    />

                                    <!-- Textarea -->
                                    <textarea
                                        v-else-if="setting.type === 'textarea'"
                                        :id="`setting-${setting.key}`"
                                        v-model="form[setting.key]"
                                        rows="4"
                                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                    ></textarea>

                                    <!-- Image Upload -->
                                    <div
                                        v-else-if="setting.type === 'image'"
                                        class="space-y-3"
                                    >
                                        <div
                                            v-if="form[setting.key]"
                                            class="flex items-center space-x-4"
                                        >
                                            <div
                                                class="h-20 w-20 overflow-hidden rounded-lg border border-gray-300 bg-gray-200 dark:border-gray-500 dark:bg-gray-600"
                                            >
                                                <img
                                                    :src="
                                                        getImageUrl(
                                                            form[setting.key],
                                                        )
                                                    "
                                                    :alt="setting.label"
                                                    class="h-full w-full object-cover"
                                                />
                                            </div>
                                            <div
                                                class="text-sm text-gray-600 dark:text-gray-400"
                                            >
                                                <p>Current image</p>
                                                <button
                                                    type="button"
                                                    @click="
                                                        removeImage(setting.key)
                                                    "
                                                    class="mt-1 text-xs text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                                >
                                                    Remove
                                                </button>
                                            </div>
                                        </div>
                                        <input
                                            type="file"
                                            :id="`setting-${setting.key}`"
                                            @change="
                                                handleFileChange(
                                                    setting.key,
                                                    $event,
                                                )
                                            "
                                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm file:mr-4 file:rounded-md file:border-0 file:bg-amber-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-amber-700 hover:file:bg-amber-100 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:file:bg-amber-900/30 dark:file:text-amber-300"
                                            accept="image/*"
                                        />
                                    </div>

                                    <!-- Select Dropdown -->
                                    <select
                                        v-else-if="setting.type === 'select'"
                                        :id="`setting-${setting.key}`"
                                        v-model="form[setting.key]"
                                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                    >
                                        <option
                                            v-for="(label, value) in JSON.parse(
                                                setting.options || '{}',
                                            )"
                                            :key="value"
                                            :value="value"
                                        >
                                            {{ label }}
                                        </option>
                                    </select>

                                    <!-- Boolean Toggle -->
                                    <div
                                        v-else-if="setting.type === 'boolean'"
                                        class="flex items-center space-x-3"
                                    >
                                        <div
                                            class="relative inline-block h-6 w-11 cursor-pointer"
                                        >
                                            <input
                                                type="checkbox"
                                                :id="`setting-${setting.key}`"
                                                v-model="form[setting.key]"
                                                true-value="1"
                                                false-value="0"
                                                class="peer sr-only"
                                            />
                                            <div
                                                class="peer h-6 w-11 rounded-full bg-gray-200 peer-checked:bg-amber-500 peer-focus:ring-2 peer-focus:ring-amber-300 peer-focus:outline-none after:absolute after:top-[2px] after:left-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-5 peer-checked:after:border-white dark:border-gray-600 dark:bg-gray-700 dark:peer-focus:ring-amber-800"
                                            ></div>
                                        </div>
                                        <label
                                            :for="`setting-${setting.key}`"
                                            class="text-sm font-medium text-gray-700 dark:text-gray-200"
                                        >
                                            {{
                                                getBooleanLabel(
                                                    form[setting.key],
                                                )
                                            }}
                                        </label>
                                    </div>

                                    <!-- Validation Error -->
                                    <span
                                        v-if="form.errors[setting.key]"
                                        class="text-sm text-red-500"
                                    >
                                        {{ form.errors[setting.key] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div
                    class="flex justify-end space-x-3 border-t border-gray-200 pt-6 dark:border-gray-700"
                >
                    <button
                        type="button"
                        @click="form.reset()"
                        class="rounded-md bg-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600"
                    >
                        Reset
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-md bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600 focus:ring-2 focus:ring-amber-400 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {{ form.processing ? 'Saving...' : 'Save Settings' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>