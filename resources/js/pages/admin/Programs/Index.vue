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
                        Programs
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Manage your academic programs
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <!-- Create New Program Button -->
                    <Link
                        :href="route('admin.programs.create')"
                        class="inline-flex items-center gap-2 rounded-md bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600 focus:ring-2 focus:ring-amber-400 focus:outline-none"
                    >
                        <Plus class="h-4 w-4" />
                        Add Program
                    </Link>
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
                                Total Programs
                            </p>
                            <p
                                class="text-2xl font-bold text-gray-900 dark:text-gray-100"
                            >
                                {{ stats.total_programs }}
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
                                Active Programs
                            </p>
                            <p
                                class="text-2xl font-bold text-green-600 dark:text-green-400"
                            >
                                {{ stats.active_programs }}
                            </p>
                        </div>
                        <div
                            class="rounded-lg bg-green-100 p-2 dark:bg-green-900"
                        >
                            <CheckCircle
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
                                Total Students
                            </p>
                            <p
                                class="text-2xl font-bold text-blue-600 dark:text-blue-400"
                            >
                                {{ stats.total_students }}
                            </p>
                        </div>
                        <div
                            class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900"
                        >
                            <Users
                                class="h-6 w-6 text-blue-600 dark:text-blue-400"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Programs Table -->
            <div
                class="relative overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <Table>
                    <TableCaption>List of all academic programs</TableCaption>

                    <TableHeader class="bg-gray-50 dark:bg-gray-800">
                        <TableRow>
                            <TableHead class="font-medium">Code</TableHead>
                            <TableHead class="font-medium">Name</TableHead>
                            <TableHead class="font-medium"
                                >Description</TableHead
                            >
                            <TableHead class="font-medium">Status</TableHead>
                            <TableHead class="font-medium">Students</TableHead>
                            <TableHead class="text-right font-medium"
                                >Actions</TableHead
                            >
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <template v-if="programs?.length">
                            <TableRow
                                v-for="program in programs"
                                :key="program.id"
                                class="border-b hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                            >
                                <!-- Code -->
                                <TableCell>
                                    <div
                                        class="font-medium text-gray-900 dark:text-gray-100"
                                    >
                                        {{ program.code }}
                                    </div>
                                </TableCell>

                                <!-- Name -->
                                <TableCell>
                                    <div
                                        class="text-gray-900 dark:text-gray-100"
                                    >
                                        {{ program.name }}
                                    </div>
                                </TableCell>

                                <!-- Description -->
                                <TableCell>
                                    <div
                                        class="text-gray-500 dark:text-gray-400"
                                    >
                                        {{ program.description || '-' }}
                                    </div>
                                </TableCell>

                                <!-- Status -->
                                <TableCell>
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"
                                        :class="
                                            program.is_active
                                                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                                : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                        "
                                    >
                                        {{
                                            program.is_active
                                                ? 'Active'
                                                : 'Inactive'
                                        }}
                                    </span>
                                </TableCell>

                                <!-- Students -->
                                <TableCell>
                                    <div
                                        class="text-gray-500 dark:text-gray-400"
                                    >
                                        {{ program.students_count || 0 }}
                                    </div>
                                </TableCell>

                                <!-- Actions -->
                                <TableCell>
                                    <div class="flex justify-end space-x-2">
                                        <Link
                                            :href="
                                                route(
                                                    'admin.programs.show',
                                                    program.id,
                                                )
                                            "
                                            class="rounded p-1.5 text-green-500 transition-colors hover:bg-green-50 hover:text-green-600 dark:hover:bg-green-900/20"
                                            title="View Details"
                                        >
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                        <Link
                                            :href="
                                                route(
                                                    'admin.programs.edit',
                                                    program.id,
                                                )
                                            "
                                            class="rounded p-1.5 text-blue-500 transition-colors hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-900/20"
                                            title="Edit Program"
                                        >
                                            <Edit class="h-4 w-4" />
                                        </Link>
                                        <button
                                            @click="deleteProgram(program.id)"
                                            class="rounded p-1.5 text-red-500 transition-colors hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20"
                                            title="Delete Program"
                                        >
                                            <Trash class="h-4 w-4" />
                                        </button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </template>

                        <template v-else>
                            <TableRow>
                                <TableCell
                                    colspan="6"
                                    class="py-12 text-center"
                                >
                                    <div
                                        class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400"
                                    >
                                        <BookOpen
                                            class="mb-4 h-12 w-12 opacity-50"
                                        />
                                        <p class="mb-2 text-lg font-medium">
                                            No programs found
                                        </p>
                                        <p class="mb-4 text-sm">
                                            Get started by creating your first
                                            program.
                                        </p>
                                        <Link
                                            :href="
                                                route('admin.programs.create')
                                            "
                                            class="inline-flex items-center gap-2 rounded-md bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600"
                                        >
                                            <Plus class="h-4 w-4" />
                                            Add Program
                                        </Link>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </template>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import {
    BookOpen,
    CheckCircle,
    Edit,
    Eye,
    Plus,
    Trash,
    Users,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { route } from 'ziggy-js';

// Import shadcn-vue table components
import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

// Props
const props = defineProps<{
    programs?: Array<{
        id: number;
        code: string;
        name: string;
        description: string | null;
        is_active: boolean;
        students_count?: number;
    }>;
}>();

// Breadcrumbs
const breadcrumbs = computed((): BreadcrumbItem[] => [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Programs', href: route('admin.programs.index') },
]);

// Calculate stats
const stats = computed(() => {
    if (!props.programs) {
        return {
            total_programs: 0,
            active_programs: 0,
            total_students: 0,
        };
    }

    return {
        total_programs: props.programs.length,
        active_programs: props.programs.filter((p) => p.is_active).length,
        total_students: props.programs.reduce(
            (sum, p) => sum + (p.students_count || 0),
            0,
        ),
    };
});

// Delete program
function deleteProgram(id: number) {
    if (confirm('Are you sure you want to delete this program?')) {
        router.delete(route('admin.programs.destroy', id), {
            onSuccess: () => {
                // Optionally show a success message
            },
            onError: (errors) => {
                console.error('Delete error:', errors);
                alert('Failed to delete program. Please try again.');
            },
        });
    }
}
</script>
