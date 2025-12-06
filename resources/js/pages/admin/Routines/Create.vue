<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';

// ✅ Import your route helpers
import { index as routinesIndex, store } from '@/routes/admin/routines';
import { watch } from 'vue';

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Class Routines', href: routinesIndex().url },
    { title: 'Create Routine', href: '' },
];

// ✅ Inertia form for creating a routine
const form = useForm({
    program_id: '',
    intake: '',
    section: '',
    semester: '',
    day: '',
    time_slot: '',
    start_time: '',
    end_time: '',
    course_code: '',
    course_name: '',
    teacher_code: '',
    teacher_name: '',
    room_number: '',
    room_type: 'Classroom',
    class_details: '',
    status: 'active',
    effective_date: '',
    slot_order: 1,
});

// Define props for dropdown data
const props = defineProps<{
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
    semesters: string[];
    statuses: string[];
}>();

// Default room types
const roomTypes = [
    'Classroom',
    'Lab',
    'Auditorium',
    'Seminar Room',
    'Computer Lab',
];

// Update times when time slot is selected
const updateTimes = () => {
    const selectedSlot = props.timeSlots.find(
        (slot) => slot.slot_name === form.time_slot,
    );
    if (selectedSlot) {
        form.start_time = selectedSlot.start_time;
        form.end_time = selectedSlot.end_time;
    }
};

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
</script>

<template>
    <Head title="Create Class Routine" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex flex-col gap-4 rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
        >
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h2
                        class="text-2xl font-semibold text-gray-800 dark:text-gray-100"
                    >
                        Create New Class Routine
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Add a new class schedule to the timetable
                    </p>
                </div>
                <Link
                    :href="routinesIndex()"
                    class="rounded-md bg-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600"
                >
                    Back to Routines
                </Link>
            </div>

            <!-- Form -->
            <form
                @submit.prevent="
                    form.post(store().url, { preserveState: true })
                "
                class="mt-4 space-y-6"
            >
                <!-- Program & Basic Details -->
                <div
                    class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800"
                >
                    <h3
                        class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100"
                    >
                        Program & Class Details
                    </h3>
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
                                class="text-sm text-red-500"
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
                                class="text-sm text-red-500"
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
                                class="text-sm text-red-500"
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
                            <select
                                v-model="form.semester"
                                required
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                            >
                                <option value="">Select Semester</option>
                                <option
                                    v-for="semester in semesters"
                                    :key="semester"
                                    :value="semester"
                                >
                                    {{ semester }}
                                </option>
                            </select>
                            <span
                                v-if="form.errors.semester"
                                class="text-sm text-red-500"
                                >{{ form.errors.semester }}</span
                            >
                        </div>
                    </div>
                </div>

                <!-- Schedule Details -->
                <div
                    class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800"
                >
                    <h3
                        class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100"
                    >
                        Schedule Details
                    </h3>
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
                                    v-for="day in days"
                                    :key="day"
                                    :value="day"
                                >
                                    {{ day }}
                                </option>
                            </select>
                            <span
                                v-if="form.errors.day"
                                class="text-sm text-red-500"
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
                                    {{ slot.slot_name }} ({{
                                        slot.start_time
                                    }}
                                    - {{ slot.end_time }})
                                </option>
                            </select>
                            <span
                                v-if="form.errors.time_slot"
                                class="text-sm text-red-500"
                                >{{ form.errors.time_slot }}</span
                            >
                        </div>

                        <!-- Start Time (auto-filled) -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-200"
                            >
                                Start Time
                            </label>
                            <input
                                v-model="form.start_time"
                                type="text"
                                readonly
                                class="mt-1 block w-full rounded-md border border-gray-300 bg-gray-100 px-3 py-2 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                            />
                            <span
                                v-if="form.errors.start_time"
                                class="text-sm text-red-500"
                                >{{ form.errors.start_time }}</span
                            >
                        </div>

                        <!-- End Time (auto-filled) -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-200"
                            >
                                End Time
                            </label>
                            <input
                                v-model="form.end_time"
                                type="text"
                                readonly
                                class="mt-1 block w-full rounded-md border border-gray-300 bg-gray-100 px-3 py-2 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                            />
                            <span
                                v-if="form.errors.end_time"
                                class="text-sm text-red-500"
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
                                class="text-sm text-red-500"
                                >{{ form.errors.slot_order }}</span
                            >
                        </div>
                    </div>
                </div>

                <!-- Course & Teacher Details -->
                <div
                    class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800"
                >
                    <h3
                        class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100"
                    >
                        Course & Teacher Details
                    </h3>
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
                                class="text-sm text-red-500"
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
                                class="text-sm text-red-500"
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
                                class="text-sm text-red-500"
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
                                class="text-sm text-red-500"
                                >{{ form.errors.teacher_name }}</span
                            >
                        </div>
                    </div>
                </div>

                <!-- Room Details -->
                <div
                    class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800"
                >
                    <h3
                        class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100"
                    >
                        Room Details
                    </h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
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
                                class="text-sm text-red-500"
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
                                class="text-sm text-red-500"
                                >{{ form.errors.room_type }}</span
                            >
                        </div>
                    </div>
                </div>

                <!-- Additional Details -->
                <div
                    class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800"
                >
                    <h3
                        class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100"
                    >
                        Additional Details
                    </h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
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
                                    v-for="status in statuses"
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
                                class="text-sm text-red-500"
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
                                class="text-sm text-red-500"
                                >{{ form.errors.effective_date }}</span
                            >
                        </div>

                        <!-- Class Details -->
                        <div class="md:col-span-2">
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
                                Format: Course Code: Teacher Code R: Room Number
                            </p>
                            <span
                                v-if="form.errors.class_details"
                                class="text-sm text-red-500"
                                >{{ form.errors.class_details }}</span
                            >
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div
                    class="flex justify-end space-x-3 border-t border-gray-200 pt-6 dark:border-gray-700"
                >
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
                        <span v-if="form.processing">Creating...</span>
                        <span v-else>Create Routine</span>
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Custom styling for disabled state */
input:read-only {
    cursor: not-allowed;
}

/* Form field focus styles */
select:focus,
input:focus,
textarea:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.1);
}
</style>
