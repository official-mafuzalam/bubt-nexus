<script setup lang="ts">
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { index as routinesIndex, update } from '@/routes/admin/routines';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    AlertCircle,
    BookOpen,
    Building,
    CheckCircle,
    Clock,
    Edit,
    Home,
    XCircle,
} from 'lucide-vue-next';
import { ref, watch } from 'vue';

// ✅ Receive routine data from Laravel
const props = defineProps<{
    routine: {
        id: number;
        program_id: number;
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
        created_at: string;
        updated_at: string;
    };
    programs: Array<{
        id: number;
        name: string;
        code: string;
    }>;
    timeSlots: Array<{
        id: number;
        slot_name: string;
        start_time: string;
        end_time: string;
    }>;
    days: string[];
    statuses: string[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Class Routines', href: routinesIndex().url },
    { title: 'Edit Routine', href: '#' },
];

// ✅ Inertia form for editing a routine
const form = useForm({
    program_id: props.routine.program_id,
    intake: props.routine.intake,
    section: props.routine.section,
    semester: props.routine.semester,
    day: props.routine.day,
    time_slot: props.routine.time_slot,
    start_time: props.routine.start_time,
    end_time: props.routine.end_time,
    course_code: props.routine.course_code,
    course_name: props.routine.course_name || '',
    teacher_code: props.routine.teacher_code,
    teacher_name: props.routine.teacher_name || '',
    room_number: props.routine.room_number,
    room_type: props.routine.room_type || '',
    class_details: props.routine.class_details || '',
    status: props.routine.status,
    effective_date: props.routine.effective_date || '',
    slot_order: props.routine.slot_order,
});

// State for custom time input
const useCustomTime = ref(false);
const customStartTime = ref(props.routine.start_time);
const customEndTime = ref(props.routine.end_time);

// Days of week for dropdown
const daysOfWeek = props.days || [
    'MON',
    'TUE',
    'WED',
    'THU',
    'FRI',
    'SAT',
    'SUN',
];

// Status options
const statusOptions = props.statuses || ['active', 'cancelled', 'rescheduled'];

// Room types
const roomTypes = [
    'Classroom',
    'Lab',
    'Auditorium',
    'Seminar Room',
    'Computer Lab',
];

// ✅ Update times when time slot is selected
const updateTimes = () => {
    if (!useCustomTime.value) {
        const selectedSlot = props.timeSlots?.find(
            (slot) => slot.slot_name === form.time_slot,
        );
        if (selectedSlot) {
            form.start_time = selectedSlot.start_time;
            form.end_time = selectedSlot.end_time;
            customStartTime.value = selectedSlot.start_time;
            customEndTime.value = selectedSlot.end_time;
        }
    }
};

// Watch custom time changes
watch([customStartTime, customEndTime], () => {
    if (useCustomTime.value) {
        form.start_time = customStartTime.value;
        form.end_time = customEndTime.value;
        form.time_slot = 'Custom';
    }
});

// Auto-generate class details when course, teacher, or room changes
watch(
    () => [form.course_code, form.teacher_code, form.room_number],
    () => {
        if (form.course_code && form.teacher_code && form.room_number) {
            form.class_details = `${form.course_code}: ${form.teacher_code} R: ${form.room_number}`;
        }
    },
    { deep: true },
);

// ✅ Get status icon
function getStatusIcon(status: string) {
    switch (status) {
        case 'active':
            return CheckCircle;
        case 'cancelled':
            return XCircle;
        case 'rescheduled':
            return AlertCircle;
        default:
            return AlertCircle;
    }
}

// ✅ Format date
function formatDate(dateString: string | null) {
    if (!dateString) return 'Not set';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}
</script>

<template>
    <Head :title="`Edit Routine: ${routine.course_code}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6">
            <!-- Header Section -->
            <div
                class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div class="rounded-xl bg-amber-100 p-3 dark:bg-amber-900">
                        <Edit
                            class="h-8 w-8 text-amber-600 dark:text-amber-400"
                        />
                    </div>
                    <div>
                        <h1
                            class="text-3xl font-bold text-gray-900 dark:text-gray-100"
                        >
                            Edit Class Routine
                        </h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">
                            {{ routine.course_code }} •
                            {{ routine.program?.name }} • {{ routine.intake
                            }}{{
                                routine.section ? ' - ' + routine.section : ''
                            }}
                        </p>
                    </div>
                </div>

                <div class="flex gap-2">
                    <Link
                        :href="routinesIndex()"
                        class="inline-flex items-center gap-2 rounded-md bg-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600"
                    >
                        Back to Routines
                    </Link>
                </div>
            </div>

            <!-- Edit Form -->
            <form
                @submit.prevent="
                    form.put(update(routine.id), { preserveState: true })
                "
                class="space-y-6"
            >
                <!-- Program & Basic Details -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Building class="h-5 w-5" />
                            Program & Class Details
                        </CardTitle>
                        <CardDescription>
                            Basic information about the program and class
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div
                            class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3"
                        >
                            <!-- Program -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-200"
                                >
                                    Program *
                                </label>
                                <select
                                    v-model="form.program_id"
                                    required
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                >
                                    <option value="">Select Program</option>
                                    <option
                                        v-for="program in programs"
                                        :key="program.id"
                                        :value="program.id"
                                    >
                                        {{ program.name }} ({{ program.code }})
                                    </option>
                                </select>
                                <span
                                    v-if="form.errors.program_id"
                                    class="mt-1 text-sm text-red-500"
                                    >{{ form.errors.program_id }}</span
                                >
                            </div>

                            <!-- Intake -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-200"
                                >
                                    Intake *
                                </label>
                                <input
                                    v-model="form.intake"
                                    type="number"
                                    required
                                    min="1"
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                    placeholder="e.g., 2024"
                                />
                                <span
                                    v-if="form.errors.intake"
                                    class="mt-1 text-sm text-red-500"
                                    >{{ form.errors.intake }}</span
                                >
                            </div>

                            <!-- Section -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-200"
                                >
                                    Section *
                                </label>
                                <input
                                    v-model="form.section"
                                    type="number"
                                    required
                                    min="1"
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                    placeholder="e.g., 1"
                                />
                                <span
                                    v-if="form.errors.section"
                                    class="mt-1 text-sm text-red-500"
                                    >{{ form.errors.section }}</span
                                >
                            </div>

                            <!-- Semester -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-200"
                                >
                                    Semester *
                                </label>
                                <input
                                    v-model="form.semester"
                                    type="text"
                                    required
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                    placeholder="e.g., Fall 2025"
                                />
                                <span
                                    v-if="form.errors.semester"
                                    class="mt-1 text-sm text-red-500"
                                    >{{ form.errors.semester }}</span
                                >
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Schedule Details -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Clock class="h-5 w-5" />
                            Schedule Details
                        </CardTitle>
                        <CardDescription>
                            Time and day information for this class
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div
                            class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4"
                        >
                            <!-- Day -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-200"
                                >
                                    Day *
                                </label>
                                <select
                                    v-model="form.day"
                                    required
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                >
                                    <option value="">Select Day</option>
                                    <option
                                        v-for="day in daysOfWeek"
                                        :key="day"
                                        :value="day"
                                    >
                                        {{ day }}
                                    </option>
                                </select>
                                <span
                                    v-if="form.errors.day"
                                    class="mt-1 text-sm text-red-500"
                                    >{{ form.errors.day }}</span
                                >
                            </div>

                            <!-- Time Slot -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-200"
                                >
                                    Time Slot *
                                </label>
                                <select
                                    v-model="form.time_slot"
                                    @change="updateTimes"
                                    required
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                >
                                    <option value="">Select Time Slot</option>
                                    <option
                                        v-for="slot in timeSlots"
                                        :key="slot.id"
                                        :value="slot.slot_name"
                                    >
                                        {{ slot.slot_name }}
                                    </option>
                                </select>
                                <span
                                    v-if="form.errors.time_slot"
                                    class="mt-1 text-sm text-red-500"
                                    >{{ form.errors.time_slot }}</span
                                >
                            </div>

                            <!-- Start Time (read-only) -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-200"
                                >
                                    Start Time
                                </label>
                                <input
                                    :value="form.start_time"
                                    type="text"
                                    readonly
                                    class="mt-1 block w-full rounded-md border border-gray-300 bg-gray-100 px-3 py-2 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                />
                                <span
                                    v-if="form.errors.start_time"
                                    class="mt-1 text-sm text-red-500"
                                    >{{ form.errors.start_time }}</span
                                >
                            </div>

                            <!-- End Time (read-only) -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-200"
                                >
                                    End Time
                                </label>
                                <input
                                    :value="form.end_time"
                                    type="text"
                                    readonly
                                    class="mt-1 block w-full rounded-md border border-gray-300 bg-gray-100 px-3 py-2 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                />
                                <span
                                    v-if="form.errors.end_time"
                                    class="mt-1 text-sm text-red-500"
                                    >{{ form.errors.end_time }}</span
                                >
                            </div>

                            <!-- Slot Order -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-200"
                                >
                                    Slot Order
                                </label>
                                <input
                                    v-model="form.slot_order"
                                    type="number"
                                    min="1"
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                    placeholder="Order in schedule"
                                />
                                <span
                                    v-if="form.errors.slot_order"
                                    class="mt-1 text-sm text-red-500"
                                    >{{ form.errors.slot_order }}</span
                                >
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Course & Teacher Details -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <BookOpen class="h-5 w-5" />
                            Course & Teacher Details
                        </CardTitle>
                        <CardDescription>
                            Information about the course and assigned teacher
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <!-- Course Code -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-200"
                                >
                                    Course Code *
                                </label>
                                <input
                                    v-model="form.course_code"
                                    type="text"
                                    required
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                    placeholder="e.g., CSE101"
                                />
                                <span
                                    v-if="form.errors.course_code"
                                    class="mt-1 text-sm text-red-500"
                                    >{{ form.errors.course_code }}</span
                                >
                            </div>

                            <!-- Course Name -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-200"
                                >
                                    Course Name
                                </label>
                                <input
                                    v-model="form.course_name"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                    placeholder="Course full name (optional)"
                                />
                                <span
                                    v-if="form.errors.course_name"
                                    class="mt-1 text-sm text-red-500"
                                    >{{ form.errors.course_name }}</span
                                >
                            </div>

                            <!-- Teacher Code -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-200"
                                >
                                    Teacher Code *
                                </label>
                                <input
                                    v-model="form.teacher_code"
                                    type="text"
                                    required
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                    placeholder="e.g., TCH001"
                                />
                                <span
                                    v-if="form.errors.teacher_code"
                                    class="mt-1 text-sm text-red-500"
                                    >{{ form.errors.teacher_code }}</span
                                >
                            </div>

                            <!-- Teacher Name -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-200"
                                >
                                    Teacher Name
                                </label>
                                <input
                                    v-model="form.teacher_name"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                    placeholder="Teacher full name (optional)"
                                />
                                <span
                                    v-if="form.errors.teacher_name"
                                    class="mt-1 text-sm text-red-500"
                                    >{{ form.errors.teacher_name }}</span
                                >
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Room & Status Details -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Room Details -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Home class="h-5 w-5" />
                                Room Details
                            </CardTitle>
                            <CardDescription>
                                Venue information for this class
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <!-- Room Number -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-200"
                                >
                                    Room Number *
                                </label>
                                <input
                                    v-model="form.room_number"
                                    type="text"
                                    required
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                    placeholder="e.g., 401"
                                />
                                <span
                                    v-if="form.errors.room_number"
                                    class="mt-1 text-sm text-red-500"
                                    >{{ form.errors.room_number }}</span
                                >
                            </div>

                            <!-- Room Type -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-200"
                                >
                                    Room Type
                                </label>
                                <select
                                    v-model="form.room_type"
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                >
                                    <option value="">Select Type</option>
                                    <option
                                        v-for="type in roomTypes"
                                        :key="type"
                                        :value="type"
                                    >
                                        {{ type }}
                                    </option>
                                </select>
                                <span
                                    v-if="form.errors.room_type"
                                    class="mt-1 text-sm text-red-500"
                                    >{{ form.errors.room_type }}</span
                                >
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Status & Additional Details -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <component
                                    :is="getStatusIcon(form.status)"
                                    class="h-5 w-5"
                                />
                                Status & Additional Details
                            </CardTitle>
                            <CardDescription>
                                Current status and additional information
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <!-- Status -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-200"
                                >
                                    Status *
                                </label>
                                <select
                                    v-model="form.status"
                                    required
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                >
                                    <option value="">Select Status</option>
                                    <option
                                        v-for="status in statusOptions"
                                        :key="status"
                                        :value="status"
                                    >
                                        {{
                                            status.charAt(0).toUpperCase() +
                                            status.slice(1)
                                        }}
                                    </option>
                                </select>
                                <span
                                    v-if="form.errors.status"
                                    class="mt-1 text-sm text-red-500"
                                    >{{ form.errors.status }}</span
                                >
                            </div>

                            <!-- Effective Date -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-200"
                                >
                                    Effective Date
                                </label>
                                <input
                                    v-model="form.effective_date"
                                    type="date"
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                />
                                <span
                                    v-if="form.errors.effective_date"
                                    class="mt-1 text-sm text-red-500"
                                    >{{ form.errors.effective_date }}</span
                                >
                            </div>

                            <!-- Class Details -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-200"
                                >
                                    Class Details (Auto-generated)
                                </label>
                                <input
                                    v-model="form.class_details"
                                    type="text"
                                    readonly
                                    class="mt-1 block w-full rounded-md border border-gray-300 bg-gray-100 px-3 py-2 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                    placeholder="Will auto-generate from Course, Teacher, and Room"
                                />
                                <p
                                    class="mt-1 text-xs text-gray-500 dark:text-gray-400"
                                >
                                    Format: Course Code: Teacher Code R: Room
                                    Number
                                </p>
                                <span
                                    v-if="form.errors.class_details"
                                    class="mt-1 text-sm text-red-500"
                                    >{{ form.errors.class_details }}</span
                                >
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Form Actions -->
                <Card>
                    <CardContent class="pt-6">
                        <div class="flex items-center justify-between">
                            <div
                                class="text-sm text-gray-600 dark:text-gray-400"
                            >
                                <p class="font-medium">Last Updated:</p>
                                <p>{{ formatDate(routine.updated_at) }}</p>
                            </div>

                            <div class="flex gap-3">
                                <Link
                                    :href="routinesIndex()"
                                    class="rounded-md bg-gray-200 px-5 py-2.5 text-sm font-medium hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="reset"
                                    @click="form.reset()"
                                    class="rounded-md bg-gray-200 px-5 py-2.5 text-sm font-medium hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600"
                                >
                                    Reset
                                </button>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="rounded-md bg-amber-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-amber-600 focus:ring-2 focus:ring-amber-400 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <span v-if="form.processing"
                                        >Updating...</span
                                    >
                                    <span v-else>Update Routine</span>
                                </button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Custom styles */
select:focus,
input:focus,
textarea:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.1);
}

input:disabled,
select:disabled {
    cursor: not-allowed;
    background-color: #f9fafb;
}

.dark input:disabled,
.dark select:disabled {
    background-color: #374151;
}

input:read-only {
    cursor: not-allowed;
    background-color: #f3f4f6;
}

.dark input:read-only {
    background-color: #4b5563;
}
</style>
