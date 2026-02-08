
<script setup lang="ts">
import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import {
    create,
    destroy,
    edit,
    exportMethod as exportRoute,
    index as routinesIndex,
    show,
    bulkCreate
} from '@/routes/admin/routines';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Building,
    Calendar,
    ChevronDown,
    Clock,
    Download,
    Edit,
    Eye,
    Filter,
    Plus,
    Trash,
    User,
    Users,
    X,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { route } from 'ziggy-js';

// ✅ Receive data from Laravel (Inertia props) with optional chaining
const props = defineProps<{
    routines?: {
        data: {
            id: number;
            program: {
                id: number;
                name: string;
                code: string;
            };
            intake: number;
            section: number;
            semester: string;
            day: string;
            time_slot: string;
            start_time: string;
            end_time: string;
            course_code: string;
            course_name: string | null;
            teacher_code: string;
            teacher_name: string | null;
            room_number: string;
            room_type: string;
            class_details: string;
            status: 'active' | 'cancelled' | 'rescheduled';
            effective_date: string | null;
            slot_order: number;
            formatted_time: string;
            intake_full: string;
            course_teacher_room: string;
            is_currently_active: boolean;
        }[];
        links: any[];
        meta: any;
    };
    programs: Array<{
        id: number;
        name: string;
        code: string;
    }>;
    filters: {
        program_id: string | null;
        intake: string | null;
        section: string | null;
        semester: string | null;
        day: string | null;
        teacher_code: string | null;
        course_code: string | null;
        room_number: string | null;
        status: string;
    };
    filterOptions: {
        intakes: string[];
        sections: number[];
        semesters: string[];
        days: string[];
        teacher_codes: string[];
        course_codes: string[];
        room_numbers: string[];
        statuses: string[];
    };
    stats: {
        total_routines: number;
        active_routines: number;
        programs_with_routines: number;
        unique_intakes: number;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Class Routines', href: routinesIndex().url },
];

// Local filters state with safe defaults
const localFilters = ref({
    program_id: props.filters?.program_id ?? '',
    intake: props.filters?.intake ?? '',
    section: props.filters?.section ?? '',
    semester: props.filters?.semester ?? '',
    day: props.filters?.day ?? '',
    teacher_code: props.filters?.teacher_code ?? '',
    course_code: props.filters?.course_code ?? '',
    room_number: props.filters?.room_number ?? '',
    status: props.filters?.status ?? 'active',
});

const showFilters = ref(false);

// Calculate stats from props with defaults
const totalRoutines = props.stats?.total_routines ?? 0;
const activeRoutines = props.stats?.active_routines ?? 0;
const programsWithRoutines = props.stats?.programs_with_routines ?? 0;
const uniqueIntakes = props.stats?.unique_intakes ?? 0;

// ✅ Apply filters
const applyFilters = () => {
    router.get(routinesIndex().url, localFilters.value, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

// ✅ Reset filters
const resetFilters = () => {
    localFilters.value = {
        program_id: '',
        intake: '',
        section: '',
        semester: '',
        day: '',
        teacher_code: '',
        course_code: '',
        room_number: '',
        status: 'active',
    };
    applyFilters();
};

// ✅ Watch for filter changes
watch(
    localFilters,
    () => {
        applyFilters();
    },
    { deep: true, immediate: false },
);

// ✅ Status badge classes
function getStatusClasses(status: string) {
    return {
        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200':
            status === 'active',
        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200':
            status === 'cancelled',
        'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200':
            status === 'rescheduled',
    };
}

// ✅ Room type badge classes
function getRoomTypeClasses(roomType: string) {
    return {
        'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200':
            roomType === 'Classroom',
        'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200':
            roomType === 'Lab',
        'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200':
            roomType === 'Auditorium',
        'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200':
            !roomType,
    };
}

// ✅ Day badge classes
function getDayClasses(day: string) {
    const dayColors: Record<string, string> = {
        MON: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        TUE: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        WED: 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200',
        THU: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        FRI: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
        SAT: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200',
        SUN: 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-200',
    };
    return (
        dayColors[day] ||
        'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
    );
}

// ✅ Delete routine handler
const deleteRoutine = (routineId: number) => {
    if (confirm('Are you sure you want to delete this routine?')) {
        router.delete(destroy(routineId), {
            preserveScroll: true,
            onSuccess: () => {
                // Show success message
            },
        });
    }
};

// ✅ Get filtered options based on selected program
const filteredSections = computed(() => {
    if (!localFilters.value.program_id && !localFilters.value.intake) {
        return props.filterOptions?.sections ?? [];
    }
    return props.filterOptions?.sections ?? [];
});

// ✅ Export routines
const exportRoutines = () => {
    const params = new URLSearchParams();
    Object.entries(localFilters.value).forEach(([key, value]) => {
        if (value) {
            params.append(key, value.toString());
        }
    });

    window.location.href = `${exportRoute().url}?${params.toString()}`;
};
</script>

<template>
    <Head title="Class Routines" />

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
                        Class Routine Management
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Manage all class schedules and timetables
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button
                        @click="exportRoutines"
                        class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                    >
                        <Download class="h-4 w-4" />
                        Export CSV
                    </button>
                    <button
                        @click="showFilters = !showFilters"
                        class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                    >
                        <Filter class="h-4 w-4" />
                        Filters
                        <ChevronDown
                            class="h-4 w-4 transition-transform"
                            :class="{ 'rotate-180': showFilters }"
                        />
                    </button>
                    <Link
                        :href="route('admin.routines.bulk-create')"
                        class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                    >
                        <Plus class="h-4 w-4" />
                        Bulk Create
                    </Link>
                    <Link
                        :href="route('admin.routines.create')"
                        class="inline-flex items-center gap-2 rounded-md bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600 focus:ring-2 focus:ring-amber-400 focus:outline-none"
                    >
                        <Plus class="h-4 w-4" />
                        Add Routine
                    </Link>
                </div>
            </div>

            <!-- Stats Summary -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div
                    class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-sm font-medium text-gray-600 dark:text-gray-400"
                            >
                                Total Routines
                            </p>
                            <p
                                class="text-2xl font-bold text-gray-900 dark:text-gray-100"
                            >
                                {{ totalRoutines }}
                            </p>
                        </div>
                        <div
                            class="rounded-lg bg-amber-100 p-2 dark:bg-amber-900"
                        >
                            <Calendar
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
                                Active Routines
                            </p>
                            <p
                                class="text-2xl font-bold text-green-600 dark:text-green-400"
                            >
                                {{ activeRoutines }}
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
                                Programs
                            </p>
                            <p
                                class="text-2xl font-bold text-blue-600 dark:text-blue-400"
                            >
                                {{ programsWithRoutines }}
                            </p>
                        </div>
                        <div
                            class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900"
                        >
                            <Building
                                class="h-6 w-6 text-blue-600 dark:text-blue-400"
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
                                Intakes
                            </p>
                            <p
                                class="text-2xl font-bold text-purple-600 dark:text-purple-400"
                            >
                                {{ uniqueIntakes }}
                            </p>
                        </div>
                        <div
                            class="rounded-lg bg-purple-100 p-2 dark:bg-purple-900"
                        >
                            <Users
                                class="h-6 w-6 text-purple-600 dark:text-purple-400"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Panel -->
            <div
                v-if="showFilters"
                class="rounded-lg border border-gray-200 bg-white p-4 transition-all duration-300 dark:border-gray-700 dark:bg-gray-800"
            >
                <div class="mb-4 flex items-center justify-between">
                    <h3
                        class="text-lg font-medium text-gray-900 dark:text-gray-100"
                    >
                        Filter Routines
                    </h3>
                    <button
                        @click="resetFilters"
                        class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200"
                    >
                        <X class="h-4 w-4" />
                        Clear All
                    </button>
                </div>

                <div
                    class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4"
                >
                    <!-- Program Filter -->
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Program
                        </label>
                        <select
                            v-model="localFilters.program_id"
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                        >
                            <option value="">All Programs</option>
                            <option
                                v-for="program in programs"
                                :key="program.id"
                                :value="program.id"
                            >
                                {{ program.name }} ({{ program.code }})
                            </option>
                        </select>
                    </div>

                    <!-- Intake Filter -->
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Intake
                        </label>
                        <select
                            v-model="localFilters.intake"
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                        >
                            <option value="">All Intakes</option>
                            <option
                                v-for="intake in filterOptions.intakes"
                                :key="intake"
                                :value="intake"
                            >
                                {{ intake }}
                            </option>
                        </select>
                    </div>

                    <!-- Section Filter -->
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Section
                        </label>
                        <select
                            v-model="localFilters.section"
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                        >
                            <option value="">All Sections</option>
                            <option
                                v-for="section in filteredSections"
                                :key="section"
                                :value="section"
                            >
                                {{ section }}
                            </option>
                        </select>
                    </div>

                    <!-- Semester Filter -->
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Semester
                        </label>
                        <select
                            v-model="localFilters.semester"
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                        >
                            <option value="">All Semesters</option>
                            <option
                                v-for="semester in filterOptions.semesters"
                                :key="semester"
                                :value="semester"
                            >
                                {{ semester }}
                            </option>
                        </select>
                    </div>

                    <!-- Day Filter -->
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Day
                        </label>
                        <select
                            v-model="localFilters.day"
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                        >
                            <option value="">All Days</option>
                            <option
                                v-for="day in filterOptions.days"
                                :key="day"
                                :value="day"
                            >
                                {{ day }}
                            </option>
                        </select>
                    </div>

                    <!-- Teacher Filter -->
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Teacher
                        </label>
                        <select
                            v-model="localFilters.teacher_code"
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                        >
                            <option value="">All Teachers</option>
                            <option
                                v-for="teacher in filterOptions.teacher_codes"
                                :key="teacher"
                                :value="teacher"
                            >
                                {{ teacher }}
                            </option>
                        </select>
                    </div>

                    <!-- Course Filter -->
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Course
                        </label>
                        <select
                            v-model="localFilters.course_code"
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                        >
                            <option value="">All Courses</option>
                            <option
                                v-for="course in filterOptions.course_codes"
                                :key="course"
                                :value="course"
                            >
                                {{ course }}
                            </option>
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Status
                        </label>
                        <select
                            v-model="localFilters.status"
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                        >
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="rescheduled">Rescheduled</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Routines Table -->
            <div
                class="relative overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <Table>
                    <TableCaption
                        >List of all class routines with filtering
                        options</TableCaption
                    >

                    <TableHeader class="bg-gray-50 dark:bg-gray-800">
                        <TableRow>
                            <TableHead class="font-medium">Program</TableHead>
                            <TableHead class="font-medium"
                                >Intake/Section</TableHead
                            >
                            <TableHead class="font-medium"
                                >Day & Time</TableHead
                            >
                            <TableHead class="font-medium">Course</TableHead>
                            <TableHead class="font-medium">Teacher</TableHead>
                            <TableHead class="font-medium">Room</TableHead>
                            <TableHead class="font-medium">Status</TableHead>
                            <TableHead class="text-right font-medium"
                                >Actions</TableHead
                            >
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <template v-if="routines?.data?.length">
                            <TableRow
                                v-for="routine in routines.data"
                                :key="routine.id"
                                class="border-b hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                            >
                                <!-- Program -->
                                <TableCell>
                                    <div
                                        class="font-medium text-gray-900 dark:text-gray-100"
                                    >
                                        {{ routine.program.name }}
                                    </div>
                                    <div
                                        class="text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        {{ routine.semester }}
                                    </div>
                                </TableCell>

                                <!-- Intake/Section -->
                                <TableCell>
                                    <div
                                        class="font-medium text-gray-900 dark:text-gray-100"
                                    >
                                        {{ routine.intake_full }}
                                    </div>
                                </TableCell>

                                <!-- Day & Time -->
                                <TableCell>
                                    <div class="flex flex-col gap-1">
                                        <span
                                            class="inline-flex w-fit items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                            :class="getDayClasses(routine.day)"
                                        >
                                            {{ routine.day }}
                                        </span>
                                        <div
                                            class="text-sm text-gray-600 dark:text-gray-400"
                                        >
                                            {{ routine.time_slot }}
                                        </div>
                                        <!-- <div
                                            class="text-xs text-gray-500 dark:text-gray-400"
                                        >
                                            {{ routine.formatted_time }}
                                        </div> -->
                                    </div>
                                </TableCell>

                                <!-- Course -->
                                <TableCell>
                                    <div
                                        class="font-medium text-gray-900 dark:text-gray-100"
                                    >
                                        {{ routine.course_code }}
                                    </div>
                                    <div
                                        v-if="routine.course_name"
                                        class="max-w-[200px] truncate text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        {{ routine.course_name }}
                                    </div>
                                </TableCell>

                                <!-- Teacher -->
                                <TableCell>
                                    <div class="flex items-center gap-2">
                                        <User class="h-4 w-4 text-gray-400" />
                                        <div>
                                            <div
                                                class="font-medium text-gray-900 dark:text-gray-100"
                                            >
                                                {{ routine.teacher_code }}
                                            </div>
                                            <div
                                                v-if="routine.teacher_name"
                                                class="text-xs text-gray-500 dark:text-gray-400"
                                            >
                                                {{ routine.teacher_name }}
                                            </div>
                                        </div>
                                    </div>
                                </TableCell>

                                <!-- Room -->
                                <TableCell>
                                    <div class="flex flex-col gap-1">
                                        <div
                                            class="font-medium text-gray-900 dark:text-gray-100"
                                        >
                                            {{ routine.room_number }}
                                        </div>
                                        <span
                                            v-if="routine.room_type"
                                            class="inline-flex w-fit items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                            :class="
                                                getRoomTypeClasses(
                                                    routine.room_type,
                                                )
                                            "
                                        >
                                            {{ routine.room_type }}
                                        </span>
                                    </div>
                                </TableCell>

                                <!-- Status -->
                                <TableCell>
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"
                                        :class="
                                            getStatusClasses(routine.status)
                                        "
                                    >
                                        {{ routine.status }}
                                    </span>
                                </TableCell>

                                <!-- Actions -->
                                <TableCell>
                                    <div class="flex justify-end space-x-2">
                                        <Link
                                            :href="show(routine.id)"
                                            class="rounded p-1.5 text-green-500 transition-colors hover:bg-green-50 hover:text-green-600 dark:hover:bg-green-900/20"
                                            title="View Details"
                                        >
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                        <Link
                                            :href="edit(routine.id)"
                                            class="rounded p-1.5 text-blue-500 transition-colors hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-900/20"
                                            title="Edit Routine"
                                        >
                                            <Edit class="h-4 w-4" />
                                        </Link>
                                        <button
                                            @click="deleteRoutine(routine.id)"
                                            class="rounded p-1.5 text-red-500 transition-colors hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20"
                                            title="Delete Routine"
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
                                    colspan="9"
                                    class="py-12 text-center"
                                >
                                    <div
                                        class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400"
                                    >
                                        <Calendar
                                            class="mb-4 h-12 w-12 opacity-50"
                                        />
                                        <p class="mb-2 text-lg font-medium">
                                            No routines found
                                        </p>
                                        <p class="mb-4 text-sm">
                                            Try adjusting your filters or add a
                                            new routine.
                                        </p>
                                        <Link
                                            :href="create()"
                                            class="inline-flex items-center gap-2 rounded-md bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600"
                                        >
                                            <Plus class="h-4 w-4" />
                                            Add First Routine
                                        </Link>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </template>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination - Fixed with null checks -->
            <div
                v-if="
                    routines &&
                    routines.data &&
                    routines.data.length > 0 &&
                    routines.meta
                "
                class="flex items-center justify-between"
            >
                <div class="text-sm text-gray-700 dark:text-gray-300">
                    Showing {{ routines.meta.from ?? 0 }} to
                    {{ routines.meta.to ?? 0 }} of
                    {{ routines.meta.total ?? 0 }} results
                </div>
                <div class="flex gap-1">
                    <Link
                        v-for="link in routines.links || []"
                        :key="link.label"
                        :href="link.url || '#'"
                        :class="[
                            'rounded-md px-3 py-1 text-sm font-medium',
                            link.active
                                ? 'bg-amber-500 text-white'
                                : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700',
                            !link.url ? 'cursor-not-allowed opacity-50' : '',
                        ]"
                        :disabled="!link.url"
                        preserve-scroll
                    >
                        <span v-html="link.label"></span>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Smooth transitions */
.fade-enter-active,
.fade-leave-active {
    transition:
        opacity 0.3s ease,
        max-height 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    max-height: 0;
}

.fade-enter-to,
.fade-leave-from {
    opacity: 1;
    max-height: 500px;
}

/* Custom scrollbar for table */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

.dark ::-webkit-scrollbar-track {
    background: #374151;
}

.dark ::-webkit-scrollbar-thumb {
    background: #6b7280;
}

.dark ::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}
</style>
