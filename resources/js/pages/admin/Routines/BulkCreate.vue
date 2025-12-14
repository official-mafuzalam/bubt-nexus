<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex flex-col gap-4 rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
        >
            <!-- Header -->
            <div
                class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center"
            >
                <div>
                    <h2
                        class="text-2xl font-semibold text-gray-800 dark:text-gray-100"
                    >
                        Bulk Routine Creation
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Add routines for multiple programs at once using
                        automated scraping
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button
                        @click="refreshPrograms"
                        class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                        :disabled="isLoading"
                    >
                        <RefreshCw
                            class="h-4 w-4"
                            :class="{ 'animate-spin': isLoading }"
                        />
                        {{ isLoading ? 'Refreshing...' : 'Refresh' }}
                    </button>
                </div>
            </div>

            <!-- Stats Summary -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div
                    class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-sm font-medium text-gray-600 dark:text-gray-400"
                            >
                                Active Programs
                            </p>
                            <p
                                class="text-2xl font-bold text-gray-900 dark:text-gray-100"
                            >
                                {{ programs.length }}
                            </p>
                        </div>
                        <div
                            class="rounded-lg bg-amber-100 p-2 dark:bg-amber-900"
                        >
                            <BookOpen
                                class="h-6 w-6 text-amber-600 dark:text-amber-400"
                            />
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-sm font-medium text-gray-600 dark:text-gray-400"
                            >
                                Pending Routines
                            </p>
                            <p
                                class="text-2xl font-bold text-green-600 dark:text-green-400"
                            >
                                {{ pendingCount }}
                            </p>
                        </div>
                        <div
                            class="rounded-lg bg-green-100 p-2 dark:bg-green-900"
                        >
                            <Clock
                                class="h-6 w-6 text-green-600 dark:text-green-400"
                            />
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-sm font-medium text-gray-600 dark:text-gray-400"
                            >
                                Success Rate
                            </p>
                            <p
                                class="text-2xl font-bold text-blue-600 dark:text-blue-400"
                            >
                                {{ successRate }}%
                            </p>
                        </div>
                        <div
                            class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900"
                        >
                            <TrendingUp
                                class="h-6 w-6 text-blue-600 dark:text-blue-400"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filters -->
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="relative flex-1">
                    <Search
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400"
                    />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search programs by name or code..."
                        class="w-full rounded-md border border-gray-300 bg-white py-2 pr-4 pl-10 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                    />
                </div>

                <div class="flex gap-2">
                    <select
                        v-model="filterStatus"
                        class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                    >
                        <option value="all">All Programs</option>
                        <option value="pending">Pending Only</option>
                        <option value="completed">Completed Only</option>
                    </select>

                    <button
                        @click="bulkAddRoutines"
                        class="inline-flex items-center gap-2 rounded-md bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600 focus:ring-2 focus:ring-amber-400 focus:outline-none disabled:opacity-50"
                        :disabled="!hasSelectedPrograms || isBulkProcessing"
                    >
                        <Plus class="h-4 w-4" />
                        {{
                            isBulkProcessing
                                ? 'Processing...'
                                : `Add Selected (${selectedPrograms.length})`
                        }}
                    </button>
                </div>
            </div>

            <!-- Programs Table -->
            <div
                class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700"
            >
                <div class="overflow-x-auto">
                    <table
                        class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
                    >
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400"
                                >
                                    <input
                                        type="checkbox"
                                        :checked="isAllSelected"
                                        @change="toggleSelectAll"
                                        class="h-4 w-4 rounded border-gray-300 text-amber-500 focus:ring-amber-500 dark:border-gray-600"
                                    />
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400"
                                >
                                    Program Code
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400"
                                >
                                    Program Name
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400"
                                >
                                    Status
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400"
                                >
                                    Last Updated
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"
                        >
                            <tr
                                v-for="program in filteredPrograms"
                                :key="program.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-700/50"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input
                                        type="checkbox"
                                        :value="program.code"
                                        v-model="selectedPrograms"
                                        class="h-4 w-4 rounded border-gray-300 text-amber-500 focus:ring-amber-500 dark:border-gray-600"
                                    />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center gap-2"
                                    >
                                        <Hash class="h-4 w-4 text-gray-400" />
                                        <span
                                            class="font-mono font-medium text-gray-900 dark:text-gray-100"
                                        >
                                            {{ program.code }}
                                        </span>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900"
                                        >
                                            <BookOpen
                                                class="h-4 w-4 text-blue-600 dark:text-blue-400"
                                            />
                                        </div>
                                        <div>
                                            <p
                                                class="font-medium text-gray-900 dark:text-gray-100"
                                            >
                                                {{ program.name }}
                                            </p>
                                            <p
                                                class="text-xs text-gray-500 dark:text-gray-400"
                                            >
                                                ID: {{ program.id }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="[
                                            'inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium',
                                            programStatus[program.code] ===
                                            'completed'
                                                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                                : programStatus[
                                                        program.code
                                                    ] === 'processing'
                                                  ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'
                                                  : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
                                        ]"
                                    >
                                        <template
                                            v-if="
                                                programStatus[program.code] ===
                                                'completed'
                                            "
                                        >
                                            <CheckCircle class="h-3 w-3" />
                                            Routine Added
                                        </template>
                                        <template
                                            v-else-if="
                                                programStatus[program.code] ===
                                                'processing'
                                            "
                                        >
                                            <Loader2
                                                class="h-3 w-3 animate-spin"
                                            />
                                            Processing...
                                        </template>
                                        <template v-else>
                                            <Clock class="h-3 w-3" />
                                            Pending
                                        </template>
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400"
                                >
                                    {{ program.last_updated || 'Never' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <button
                                            @click="addRoutine(program.code)"
                                            :disabled="
                                                programStatus[program.code] ===
                                                    'processing' ||
                                                programStatus[program.code] ===
                                                    'completed'
                                            "
                                            :class="[
                                                'inline-flex items-center gap-2 rounded-md px-3 py-1.5 text-xs font-medium transition-colors',
                                                programStatus[program.code] ===
                                                'completed'
                                                    ? 'bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/30 dark:text-green-400 dark:hover:bg-green-900/50'
                                                    : programStatus[
                                                            program.code
                                                        ] === 'processing'
                                                      ? 'cursor-not-allowed bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                                                      : 'bg-amber-100 text-amber-700 hover:bg-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:hover:bg-amber-900/50',
                                            ]"
                                        >
                                            <template
                                                v-if="
                                                    programStatus[
                                                        program.code
                                                    ] === 'completed'
                                                "
                                            >
                                                <CheckCircle class="h-3 w-3" />
                                                Added
                                            </template>
                                            <template
                                                v-else-if="
                                                    programStatus[
                                                        program.code
                                                    ] === 'processing'
                                                "
                                            >
                                                <Loader2
                                                    class="h-3 w-3 animate-spin"
                                                />
                                                Adding...
                                            </template>
                                            <template v-else>
                                                <Plus class="h-3 w-3" />
                                                Add Routine
                                            </template>
                                        </button>

                                        <Link
                                            v-if="
                                                programStatus[program.code] ===
                                                'completed'
                                            "
                                            :href="
                                                route('admin.routines.index', {
                                                    program: program.code,
                                                })
                                            "
                                            class="inline-flex items-center gap-1 rounded-md bg-blue-50 px-2.5 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/30"
                                            title="View Routines"
                                        >
                                            <Eye class="h-3 w-3" />
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-if="filteredPrograms.length === 0"
                class="rounded-xl border border-sidebar-border/70 py-16 text-center dark:border-sidebar-border"
            >
                <div
                    class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400"
                >
                    <BookOpen class="mb-4 h-16 w-16 opacity-50" />
                    <p class="mb-2 text-lg font-medium">
                        {{
                            searchQuery
                                ? 'No matching programs found'
                                : 'No active programs found'
                        }}
                    </p>
                    <p class="mb-4 text-sm">
                        {{
                            searchQuery
                                ? 'Try a different search term'
                                : 'All programs might be inactive'
                        }}
                    </p>
                    <button
                        v-if="searchQuery"
                        @click="searchQuery = ''"
                        class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                    >
                        <X class="h-4 w-4" />
                        Clear Search
                    </button>
                </div>
            </div>

            <!-- Progress Modal -->
            <div
                v-if="showProgressModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                @click.self="showProgressModal = false"
            >
                <div
                    class="w-full max-w-md rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-800"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <h3
                            class="text-lg font-semibold text-gray-900 dark:text-gray-100"
                        >
                            Bulk Routine Creation
                        </h3>
                        <button
                            @click="showProgressModal = false"
                            class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-gray-700"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <div class="mb-2 flex items-center justify-between">
                                <p
                                    class="text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Progress
                                </p>
                                <p
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
                                    {{ currentProgress }} / {{ totalProgress }}
                                </p>
                            </div>
                            <div
                                class="h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700"
                            >
                                <div
                                    class="h-full rounded-full bg-amber-500 transition-all duration-300"
                                    :style="{ width: progressPercentage + '%' }"
                                ></div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div
                                v-for="item in progressItems"
                                :key="item.code"
                                class="flex items-center justify-between rounded-lg border border-gray-200 p-3 dark:border-gray-700"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-8 w-8 items-center justify-center rounded-lg"
                                        :class="{
                                            'bg-green-100 dark:bg-green-900':
                                                item.status === 'completed',
                                            'bg-blue-100 dark:bg-blue-900':
                                                item.status === 'processing',
                                            'bg-gray-100 dark:bg-gray-900':
                                                item.status === 'pending',
                                        }"
                                    >
                                        <component
                                            :is="getStatusIcon(item.status)"
                                            class="h-4 w-4"
                                            :class="{
                                                'text-green-600 dark:text-green-400':
                                                    item.status === 'completed',
                                                'text-blue-600 dark:text-blue-400':
                                                    item.status ===
                                                    'processing',
                                                'text-gray-600 dark:text-gray-400':
                                                    item.status === 'pending',
                                            }"
                                        />
                                    </div>
                                    <div>
                                        <p
                                            class="font-medium text-gray-900 dark:text-gray-100"
                                        >
                                            {{ item.code }}
                                        </p>
                                        <p
                                            class="text-xs text-gray-500 dark:text-gray-400"
                                        >
                                            {{ item.name }}
                                        </p>
                                    </div>
                                </div>
                                <span
                                    :class="[
                                        'text-xs font-medium',
                                        item.status === 'completed'
                                            ? 'text-green-600 dark:text-green-400'
                                            : item.status === 'processing'
                                              ? 'text-blue-600 dark:text-blue-400'
                                              : 'text-gray-500 dark:text-gray-400',
                                    ]"
                                >
                                    {{
                                        item.status === 'completed'
                                            ? 'Done'
                                            : item.status === 'processing'
                                              ? 'Processing...'
                                              : 'Waiting'
                                    }}
                                </span>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <button
                                @click="cancelBulkProcess"
                                class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                                :disabled="isBulkProcessing"
                            >
                                Cancel
                            </button>
                            <button
                                v-if="!isBulkProcessing"
                                @click="showProgressModal = false"
                                class="rounded-md bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600"
                            >
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import {
    BookOpen,
    CheckCircle,
    Clock,
    Eye,
    Hash,
    Loader2,
    Plus,
    RefreshCw,
    Search,
    TrendingUp,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { route } from 'ziggy-js';

// Props
const props = defineProps<{
    programs: Array<{
        id: number;
        name: string;
        code: string;
        last_updated?: string;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Routines', href: route('admin.routines.index') },
    { title: 'Bulk Create', href: route('admin.routines.bulk-create') },
];

// Reactive state
const searchQuery = ref('');
const filterStatus = ref('all');
const selectedPrograms = ref<string[]>([]);
const programStatus = ref<
    Record<string, 'pending' | 'processing' | 'completed'>
>({});
const isLoading = ref(false);
const isBulkProcessing = ref(false);
const showProgressModal = ref(false);
const currentProgress = ref(0);
const totalProgress = ref(0);
const progressItems = ref<
    Array<{
        code: string;
        name: string;
        status: 'pending' | 'processing' | 'completed';
    }>
>([]);
const processingQueue = ref<string[]>([]);

// Computed properties
const filteredPrograms = computed(() => {
    let filtered = [...props.programs];

    // Apply search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(
            (program) =>
                program.name.toLowerCase().includes(query) ||
                program.code.toLowerCase().includes(query),
        );
    }

    // Apply status filter
    if (filterStatus.value === 'pending') {
        filtered = filtered.filter(
            (program) => programStatus.value[program.code] !== 'completed',
        );
    } else if (filterStatus.value === 'completed') {
        filtered = filtered.filter(
            (program) => programStatus.value[program.code] === 'completed',
        );
    }

    return filtered;
});

const pendingCount = computed(() => {
    return props.programs.filter(
        (program) => programStatus.value[program.code] !== 'completed',
    ).length;
});

const successRate = computed(() => {
    const completed = Object.values(programStatus.value).filter(
        (status) => status === 'completed',
    ).length;
    const total = props.programs.length;
    return total > 0 ? Math.round((completed / total) * 100) : 0;
});

const hasSelectedPrograms = computed(() => selectedPrograms.value.length > 0);

const isAllSelected = computed(() => {
    return (
        filteredPrograms.value.length > 0 &&
        filteredPrograms.value.every((program) =>
            selectedPrograms.value.includes(program.code),
        )
    );
});

const progressPercentage = computed(() => {
    return totalProgress.value > 0
        ? (currentProgress.value / totalProgress.value) * 100
        : 0;
});

// Methods
const refreshPrograms = async () => {
    isLoading.value = true;
    try {
        await router.reload({ only: ['programs'] });
        // Reset status for programs that are no longer in the list
        const programCodes = props.programs.map((p) => p.code);
        Object.keys(programStatus.value).forEach((code) => {
            if (!programCodes.includes(code)) {
                delete programStatus.value[code];
            }
        });
    } finally {
        isLoading.value = false;
    }
};

const addRoutine = async (programCode: string) => {
    if (programStatus.value[programCode] === 'processing') return;

    // Update status
    programStatus.value[programCode] = 'processing';

    try {
        // Call the scraper route
        await router.get(
            route('admin.scraper.program.scrape', { code: programCode }),
            {},
            {
                onSuccess: () => {
                    programStatus.value[programCode] = 'completed';
                    // Remove from selected if it was selected
                    const index = selectedPrograms.value.indexOf(programCode);
                    if (index > -1) {
                        selectedPrograms.value.splice(index, 1);
                    }
                },
                onError: () => {
                    programStatus.value[programCode] = 'pending';
                    alert(
                        `Failed to add routine for program ${programCode}. Please try again.`,
                    );
                },
            },
        );
    } catch (error) {
        programStatus.value[programCode] = 'pending';
        console.error('Error adding routine:', error);
        alert(
            `Failed to add routine for program ${programCode}. Please try again.`,
        );
    }
};

const bulkAddRoutines = async () => {
    if (selectedPrograms.value.length === 0) return;

    // Set up progress tracking
    progressItems.value = selectedPrograms.value.map((code) => {
        const program = props.programs.find((p) => p.code === code);
        return {
            code,
            name: program?.name || code,
            status: 'pending' as const,
        };
    });

    totalProgress.value = selectedPrograms.value.length;
    currentProgress.value = 0;
    isBulkProcessing.value = true;
    showProgressModal.value = true;

    // Process each program sequentially
    for (let i = 0; i < selectedPrograms.value.length; i++) {
        const code = selectedPrograms.value[i];

        if (programStatus.value[code] === 'completed') {
            currentProgress.value++;
            continue;
        }

        // Update status to processing
        programStatus.value[code] = 'processing';
        const itemIndex = progressItems.value.findIndex(
            (item) => item.code === code,
        );
        if (itemIndex > -1) {
            progressItems.value[itemIndex].status = 'processing';
        }

        try {
            await router.get(
                route('admin.scraper.program.scrape', { code }),
                {},
                {
                    onSuccess: () => {
                        programStatus.value[code] = 'completed';
                        if (itemIndex > -1) {
                            progressItems.value[itemIndex].status = 'completed';
                        }
                    },
                    onError: () => {
                        programStatus.value[code] = 'pending';
                        if (itemIndex > -1) {
                            progressItems.value[itemIndex].status = 'pending';
                        }
                    },
                },
            );
        } catch (error) {
            programStatus.value[code] = 'pending';
            if (itemIndex > -1) {
                progressItems.value[itemIndex].status = 'pending';
            }
            console.error(`Error processing ${code}:`, error);
        }

        currentProgress.value++;
    }

    isBulkProcessing.value = false;
    selectedPrograms.value = [];
};

const toggleSelectAll = () => {
    if (isAllSelected.value) {
        // Deselect all
        filteredPrograms.value.forEach((program) => {
            const index = selectedPrograms.value.indexOf(program.code);
            if (index > -1) {
                selectedPrograms.value.splice(index, 1);
            }
        });
    } else {
        // Select all
        filteredPrograms.value.forEach((program) => {
            if (!selectedPrograms.value.includes(program.code)) {
                selectedPrograms.value.push(program.code);
            }
        });
    }
};

const cancelBulkProcess = () => {
    isBulkProcessing.value = false;
    showProgressModal.value = false;
    selectedPrograms.value = [];
    currentProgress.value = 0;
    totalProgress.value = 0;
};

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'completed':
            return CheckCircle;
        case 'processing':
            return Loader2;
        default:
            return Clock;
    }
};

// Initialize status for all programs
props.programs.forEach((program) => {
    if (!programStatus.value[program.code]) {
        programStatus.value[program.code] = 'pending';
    }
});
</script>

<style scoped>
/* Smooth transitions for progress bar */
.progress-bar {
    transition: width 0.3s ease-in-out;
}

/* Animation for processing status */
@keyframes pulse {
    0%,
    100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.processing {
    animation: pulse 2s ease-in-out infinite;
}
</style>
