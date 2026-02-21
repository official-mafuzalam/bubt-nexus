<!-- resources/js/Pages/admin/Routines/MyRoutines.vue -->
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
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import {
    Building,
    Calendar,
    CalendarDays,
    Clock,
    Download,
    Edit,
    GraduationCap,
    MapPin,
    PlusCircle,
    Printer,
    Share2,
    User,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { route } from 'ziggy-js';

// Define props
const props = defineProps<{
    routines: {
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
        course_name: string;
        teacher_code: string;
        teacher_name: string;
        room_number: string;
        room_type: string;
        status: string;
        effective_date: string;
        slot_order: number;
        class_details: string;
        created_at: string;
        updated_at: string;
        formatted_time: string;
        intake_full: string;
        course_teacher_room: string;
    }[];
    userHasPermission: boolean;
    semesters: Record<string, number>;
}>();

// Create a mapping from ID to semester name
const semesterIdToName = computed(() => {
    const mapping: Record<string, string> = {};
    Object.entries(props.semesters).forEach(([name, id]) => {
        mapping[id.toString()] = name;
    });
    return mapping;
});

// Helper function to get semester name from ID
const getSemesterName = (semesterId: string): string => {
    return semesterIdToName.value[semesterId] || semesterId;
};

// Define currentSemester as a computed property
const currentSemester = computed(() => {
    if (!props.routines || props.routines.length === 0) return 'N/A';
    const semesterId = props.routines[0].semester;
    return getSemesterName(semesterId);
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'My Routines', href: '/admin-dashboard/routines/my' }, // Update with your actual route
];

// console.log('User Has Permission:', props.userHasPermission);
// Access routines via props
const routines = props.routines;

// Check if Web Share API is available (client-side only)
const canShare = ref(false);

onMounted(() => {
    // Only check on client side
    canShare.value = typeof navigator !== 'undefined' && 'share' in navigator;
});

// Calculate stats
const totalClasses = routines.length;
const uniqueDays = computed(() => [...new Set(routines.map((r) => r.day))]);
const totalHours = computed(() => {
    const totalMinutes = routines.reduce((total, routine) => {
        const [startHour, startMinute] = routine.start_time
            .split(':')
            .map(Number);
        const [endHour, endMinute] = routine.end_time.split(':').map(Number);
        const startTotal = startHour * 60 + startMinute;
        const endTotal = endHour * 60 + endMinute;
        return total + (endTotal - startTotal);
    }, 0);
    return (totalMinutes / 60).toFixed(1);
});
const uniqueTeachers = computed(() => [
    ...new Set(routines.map((r) => r.teacher_name)),
]);

// Group routines by day for better organization
const routinesByDay = computed(() => {
    const daysOrder = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'];
    const grouped: { [key: string]: typeof routines } = {};

    daysOrder.forEach((day) => {
        grouped[day] = routines
            .filter((r) => r.day === day)
            .sort((a, b) => {
                const [aHour] = a.start_time.split(':').map(Number);
                const [bHour] = b.start_time.split(':').map(Number);
                return aHour - bHour;
            });
    });

    return grouped;
});

// Get day name from abbreviation
function getFullDayName(abbr: string): string {
    const days: { [key: string]: string } = {
        MON: 'Monday',
        TUE: 'Tuesday',
        WED: 'Wednesday',
        THU: 'Thursday',
        FRI: 'Friday',
        SAT: 'Saturday',
        SUN: 'Sunday',
    };
    return days[abbr] || abbr;
}

// Format date
function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}

// Get time slot color based on time
function getTimeSlotColor(startTime: string): string {
    const hour = parseInt(startTime.split(':')[0]);
    if (hour < 12)
        return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
    if (hour < 16)
        return 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200';
    return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200';
}

// Get room type color
function getRoomTypeColor(type: string): string {
    const colors: { [key: string]: string } = {
        Classroom:
            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        Lab: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
        Auditorium: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        Seminar:
            'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    };
    return (
        colors[type] ||
        'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
    );
}

// Current day highlighting
function isToday(dayAbbr: string): boolean {
    const today = new Date()
        .toLocaleDateString('en-US', { weekday: 'short' })
        .toUpperCase();
    return dayAbbr === today;
}

// Get today's routines
function getTodayRoutines() {
    const today = new Date()
        .toLocaleDateString('en-US', { weekday: 'short' })
        .toUpperCase();
    const now = new Date();
    const currentHour = now.getHours();
    const currentMinute = now.getMinutes();

    return routines
        .filter((routine) => {
            if (routine.day !== today) return false;

            const [startHour, startMinute] = routine.start_time
                .split(':')
                .map(Number);
            const routineTime = startHour * 60 + startMinute;
            const currentTime = currentHour * 60 + currentMinute;

            // Only show upcoming classes (within next 24 hours)
            return routineTime >= currentTime;
        })
        .sort((a, b) => {
            const [aHour] = a.start_time.split(':').map(Number);
            const [bHour] = b.start_time.split(':').map(Number);
            return aHour - bHour;
        });
}

// Get teacher's courses
function getTeacherCourses(teacherName: string) {
    return routines
        .filter((r) => r.teacher_name === teacherName)
        .map((r) => r.course_code)
        .filter((value, index, self) => self.indexOf(value) === index);
}

// Export functions
const exportToPDF = () => {
    // Implement PDF export logic
    console.log('Exporting to PDF...');
    alert('PDF export feature coming soon!');
};

const printRoutine = () => {
    window.print();
};

const shareRoutine = () => {
    if (canShare.value) {
        navigator
            .share({
                title: 'My Class Routine',
                text: `Check out my class routine for ${currentSemester}! I have ${totalClasses} classes this week.`,
                url: window.location.href,
            })
            .catch((error) => {
                console.log('Sharing cancelled or failed:', error);
            });
    }
};
</script>

<template>
    <Head title="My Routines" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex flex-col gap-6 rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
        >
            <!-- Header -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <h2
                        class="text-2xl font-semibold text-gray-800 dark:text-gray-100"
                    >
                        My Class Routine
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400">
                        View your class schedule and upcoming sessions
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button
                        @click="exportToPDF"
                        class="inline-flex items-center gap-2 rounded-md bg-blue-500 px-4 py-2 text-sm font-medium text-white hover:bg-blue-600 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                    >
                        <Download class="h-4 w-4" />
                        Export PDF
                    </button>
                    <button
                        @click="printRoutine"
                        class="inline-flex items-center gap-2 rounded-md bg-gray-500 px-4 py-2 text-sm font-medium text-white hover:bg-gray-600 focus:ring-2 focus:ring-gray-400 focus:outline-none"
                    >
                        <Printer class="h-4 w-4" />
                        Print
                    </button>
                    <button
                        @click="shareRoutine"
                        v-if="canShare"
                        class="inline-flex items-center gap-2 rounded-md bg-green-500 px-4 py-2 text-sm font-medium text-white hover:bg-green-600 focus:ring-2 focus:ring-green-400 focus:outline-none"
                    >
                        <Share2 class="h-4 w-4" />
                        Share
                    </button>
                    <Link
                        v-if="props.userHasPermission"
                        :href="route('admin.routines.create')"
                        class="inline-flex items-center gap-2 rounded-md bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600 focus:ring-2 focus:ring-amber-400 focus:outline-none"
                    >
                        <PlusCircle class="h-4 w-4" />
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
                                Total Classes
                            </p>
                            <p
                                class="text-2xl font-bold text-gray-900 dark:text-gray-100"
                            >
                                {{ totalClasses }}
                            </p>
                        </div>
                        <div
                            class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900"
                        >
                            <Calendar
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
                                Current Semester
                            </p>
                            <p
                                class="text-2xl font-bold text-amber-600 dark:text-amber-400"
                            >
                                {{ currentSemester }}
                            </p>
                        </div>
                        <div
                            class="rounded-lg bg-amber-100 p-2 dark:bg-amber-900"
                        >
                            <GraduationCap
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
                                Weekly Hours
                            </p>
                            <p
                                class="text-2xl font-bold text-green-600 dark:text-green-400"
                            >
                                {{ totalHours }}h
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
                                Active Days
                            </p>
                            <p
                                class="text-2xl font-bold text-purple-600 dark:text-purple-400"
                            >
                                {{ uniqueDays.length }}
                            </p>
                        </div>
                        <div
                            class="rounded-lg bg-purple-100 p-2 dark:bg-purple-900"
                        >
                            <CalendarDays
                                class="h-6 w-6 text-purple-600 dark:text-purple-400"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Routine Table -->
            <div
                class="relative overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <Table>
                    <TableCaption>
                        Your class routine for {{ currentSemester }}. Last
                        updated:
                        {{
                            routines.length > 0
                                ? formatDate(routines[0].updated_at)
                                : 'N/A'
                        }}
                    </TableCaption>

                    <TableHeader>
                        <TableRow>
                            <TableHead>Day</TableHead>
                            <TableHead>Time Slot</TableHead>
                            <TableHead>Course</TableHead>
                            <TableHead>Teacher</TableHead>
                            <TableHead>Room</TableHead>
                            <TableHead>Details</TableHead>
                            <TableHead>Status</TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <template v-if="routines.length">
                            <template
                                v-for="(dayRoutines, day) in routinesByDay"
                                :key="day"
                            >
                                <template v-if="dayRoutines.length > 0">
                                    <!-- Day Header -->
                                    <TableRow
                                        class="bg-gray-50 dark:bg-gray-800/50"
                                    >
                                        <TableCell colspan="7" class="py-3">
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <CalendarDays
                                                    class="h-4 w-4 text-gray-500 dark:text-gray-400"
                                                />
                                                <span
                                                    class="text-lg font-semibold text-gray-800 dark:text-gray-100"
                                                    :class="{
                                                        'text-blue-600 dark:text-blue-400':
                                                            isToday(day),
                                                    }"
                                                >
                                                    {{ getFullDayName(day) }}
                                                    <span
                                                        v-if="isToday(day)"
                                                        class="ml-2 rounded-full bg-blue-100 px-2 py-1 text-xs font-normal text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                                                    >
                                                        Today
                                                    </span>
                                                </span>
                                                <span
                                                    class="ml-auto text-sm text-gray-500 dark:text-gray-400"
                                                >
                                                    {{ dayRoutines.length }}
                                                    class{{
                                                        dayRoutines.length !== 1
                                                            ? 'es'
                                                            : ''
                                                    }}
                                                </span>
                                            </div>
                                        </TableCell>
                                    </TableRow>

                                    <!-- Class Entries for this day -->
                                    <TableRow
                                        v-for="routine in dayRoutines"
                                        :key="routine.id"
                                        class="border-t border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                                    >
                                        <TableCell
                                            class="font-medium text-gray-500 dark:text-gray-400"
                                        >
                                            {{ day }}
                                        </TableCell>
                                        <TableCell>
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <Clock
                                                    class="h-4 w-4 text-gray-400"
                                                />
                                                <span
                                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                                    :class="
                                                        getTimeSlotColor(
                                                            routine.start_time,
                                                        )
                                                    "
                                                >
                                                    {{ routine.formatted_time }}
                                                </span>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="flex flex-col">
                                                <span
                                                    class="font-medium text-gray-900 dark:text-gray-100"
                                                >
                                                    {{ routine.course_code }}
                                                </span>
                                                <span
                                                    class="max-w-[200px] truncate text-xs text-gray-500 dark:text-gray-400"
                                                >
                                                    {{ routine.course_name }}
                                                </span>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <User
                                                    class="h-4 w-4 text-gray-400"
                                                />
                                                <div class="flex flex-col">
                                                    <span
                                                        class="font-medium text-gray-900 dark:text-gray-100"
                                                    >
                                                        {{
                                                            routine.teacher_code
                                                        }}
                                                    </span>
                                                    <span
                                                        class="text-xs text-gray-500 dark:text-gray-400"
                                                    >
                                                        {{
                                                            routine.teacher_name
                                                        }}
                                                    </span>
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <Building
                                                    class="h-4 w-4 text-gray-400"
                                                />
                                                <div class="flex flex-col">
                                                    <span
                                                        class="font-medium text-gray-900 dark:text-gray-100"
                                                    >
                                                        {{
                                                            routine.room_number
                                                        }}
                                                    </span>
                                                    <span
                                                        class="mt-1 inline-flex items-center rounded px-2 py-0.5 text-xs font-medium"
                                                        :class="
                                                            getRoomTypeColor(
                                                                routine.room_type,
                                                            )
                                                        "
                                                    >
                                                        {{ routine.room_type }}
                                                    </span>
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell
                                            class="text-sm text-gray-600 dark:text-gray-400"
                                        >
                                            <div class="flex flex-col">
                                                <span
                                                    >Intake:
                                                    {{
                                                        routine.intake_full
                                                    }}</span
                                                >
                                                <span
                                                    >Slot:
                                                    {{
                                                        routine.slot_order
                                                    }}</span
                                                >
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="flex flex-col">
                                                <span
                                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"
                                                    :class="{
                                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200':
                                                            routine.status ===
                                                            'active',
                                                        'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200':
                                                            routine.status ===
                                                            'inactive',
                                                    }"
                                                >
                                                    {{ routine.status }}
                                                </span>
                                                <Link
                                                    v-if="
                                                        props.userHasPermission
                                                    "
                                                    :href="
                                                        route(
                                                            'admin.routines.edit',
                                                            routine.id,
                                                        )
                                                    "
                                                    class="rounded p-1.5 text-blue-500 transition-colors hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-900/20"
                                                    title="Edit Routine"
                                                >
                                                    <Edit class="h-4 w-4" />
                                                </Link>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                </template>
                            </template>
                        </template>

                        <template v-else>
                            <TableRow>
                                <TableCell
                                    colspan="7"
                                    class="py-12 text-center"
                                >
                                    <div
                                        class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400"
                                    >
                                        <Calendar
                                            class="mb-4 h-12 w-12 opacity-50"
                                        />
                                        <p class="mb-2 text-lg font-medium">
                                            No routine found
                                        </p>
                                        <p class="text-sm">
                                            Your class routine hasn't been
                                            scheduled yet.
                                        </p>
                                        <p class="mt-2 text-xs">
                                            Please contact your department
                                            administrator.
                                        </p>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </template>
                    </TableBody>
                </Table>
            </div>

            <!-- Additional Information -->
            <div
                v-if="routines.length > 0"
                class="grid grid-cols-1 gap-6 md:grid-cols-2"
            >
                <!-- Upcoming Classes -->
                <div
                    class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800"
                >
                    <div class="mb-4 flex items-center gap-2">
                        <Clock class="h-5 w-5 text-amber-500" />
                        <h3
                            class="text-lg font-semibold text-gray-800 dark:text-gray-100"
                        >
                            Upcoming Today
                        </h3>
                    </div>
                    <div v-if="getTodayRoutines().length > 0" class="space-y-3">
                        <div
                            v-for="routine in getTodayRoutines()"
                            :key="routine.id"
                            class="rounded-lg border border-gray-100 p-3 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/50"
                        >
                            <div class="flex items-start justify-between">
                                <div>
                                    <span
                                        class="font-medium text-gray-900 dark:text-gray-100"
                                        >{{ routine.course_code }}</span
                                    >
                                    <span
                                        class="ml-2 text-sm text-gray-500 dark:text-gray-400"
                                        >{{ routine.course_teacher_room }}</span
                                    >
                                </div>
                                <span
                                    class="text-sm font-medium text-blue-600 dark:text-blue-400"
                                >
                                    {{ routine.formatted_time }}
                                </span>
                            </div>
                            <div
                                class="mt-2 flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400"
                            >
                                <span class="flex items-center gap-1">
                                    <User class="h-3 w-3" />
                                    {{ routine.teacher_name }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <MapPin class="h-3 w-3" /> Room
                                    {{ routine.room_number }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div
                        v-else
                        class="py-4 text-center text-gray-500 dark:text-gray-400"
                    >
                        <p>No more classes scheduled for today.</p>
                    </div>
                </div>

                <!-- Teachers Information -->
                <div
                    class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800"
                >
                    <div class="mb-4 flex items-center gap-2">
                        <User class="h-5 w-5 text-blue-500" />
                        <h3
                            class="text-lg font-semibold text-gray-800 dark:text-gray-100"
                        >
                            Your Teachers
                        </h3>
                    </div>
                    <div v-if="uniqueTeachers.length > 0" class="space-y-3">
                        <div
                            v-for="teacher in uniqueTeachers"
                            :key="teacher"
                            class="rounded-lg border border-gray-100 p-3 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/50"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900"
                                >
                                    <User
                                        class="h-4 w-4 text-blue-600 dark:text-blue-400"
                                    />
                                </div>
                                <div>
                                    <span
                                        class="font-medium text-gray-900 dark:text-gray-100"
                                        >{{ teacher }}</span
                                    >
                                    <p
                                        class="text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        {{
                                            getTeacherCourses(teacher).join(
                                                ', ',
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        v-else
                        class="py-4 text-center text-gray-500 dark:text-gray-400"
                    >
                        <p>No teacher information available.</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
@media print {
    .no-print {
        display: none !important;
    }

    .rounded-xl {
        border-radius: 0 !important;
    }

    .border {
        border: 1px solid #000 !important;
    }

    .dark\\:border-sidebar-border {
        border-color: #000 !important;
    }

    .bg-white {
        background-color: white !important;
    }

    .text-gray-800 {
        color: #000 !important;
    }

    /* Hide action buttons when printing */
    button {
        display: none !important;
    }
}
</style>
