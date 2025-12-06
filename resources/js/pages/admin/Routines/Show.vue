<script setup lang="ts">
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { destroy, edit, index as routinesIndex } from '@/routes/admin/routines';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import {
    AlertCircle,
    BookOpen,
    Building,
    Calendar as CalendarIcon,
    CheckCircle,
    Clock,
    Edit,
    FileText,
    GraduationCap,
    Home,
    MapPin,
    Trash,
    User,
    Users,
    XCircle,
} from 'lucide-vue-next';

// ✅ Receive routine data from Laravel
const props = defineProps<{
    routine: {
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
        course_credit_hours: number | null;
        teacher_code: string;
        teacher_name: string | null;
        teacher_department: string | null;
        room_number: string;
        room_type: string;
        room_capacity: number | null;
        class_details: string;
        status: 'active' | 'cancelled' | 'rescheduled';
        effective_date: string | null;
        slot_order: number;
        formatted_time: string;
        intake_full: string;
        course_teacher_room: string;
        is_currently_active: boolean;
        created_at: string;
        updated_at: string;
        // Additional computed fields
        duration_minutes: number;
        is_conflict: boolean;
        conflict_details: Array<{
            type: string;
            description: string;
        }>;
    };
    program_stats: {
        total_routines: number;
        active_routines: number;
        unique_courses: number;
        unique_teachers: number;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Class Routines', href: routinesIndex().url },
    { title: `Routine #${props.routine.id}`, href: '#' },
];

// ✅ Format date
function formatDate(dateString: string | null) {
    if (!dateString) return 'Not set';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}

// ✅ Format time
function formatTime(timeString: string) {
    return new Date(`2000-01-01T${timeString}`).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
    });
}

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
            !roomType || roomType === 'General',
    };
}

// ✅ Conflict badge classes
function getConflictClasses(hasConflict: boolean) {
    return {
        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200':
            hasConflict,
        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200':
            !hasConflict,
    };
}

// ✅ Calculate duration
function calculateDuration() {
    const start = new Date(`2000-01-01T${props.routine.start_time}`);
    const end = new Date(`2000-01-01T${props.routine.end_time}`);
    const diffMs = end.getTime() - start.getTime();
    const diffMins = Math.round(diffMs / 60000);

    if (diffMins < 60) {
        return `${diffMins} minutes`;
    } else {
        const hours = Math.floor(diffMins / 60);
        const minutes = diffMins % 60;
        return `${hours}h ${minutes}m`;
    }
}

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
</script>

<template>
    <Head :title="`Class Routine: ${routine.course_code}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6">
            <!-- Header Section -->
            <div
                class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div class="rounded-xl bg-amber-100 p-3 dark:bg-amber-900">
                        <CalendarIcon
                            class="h-8 w-8 text-amber-600 dark:text-amber-400"
                        />
                    </div>
                    <div>
                        <h1
                            class="text-3xl font-bold text-gray-900 dark:text-gray-100"
                        >
                            {{ routine.course_code }}
                            <span
                                v-if="routine.course_name"
                                class="text-xl font-normal text-gray-600 dark:text-gray-400"
                            >
                                - {{ routine.course_name }}
                            </span>
                        </h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">
                            {{ routine.program.name }} •
                            {{ routine.intake_full }} •
                            {{ routine.semester }}
                        </p>
                    </div>
                </div>

                <div class="flex gap-2">
                    <Link
                        :href="edit(routine.id)"
                        class="inline-flex items-center gap-2 rounded-md bg-blue-500 px-4 py-2 text-sm font-medium text-white hover:bg-blue-600 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                    >
                        <Edit class="h-4 w-4" />
                        Edit Routine
                    </Link>
                    <Link
                        :href="destroy(routine.id)"
                        method="delete"
                        as="button"
                        class="inline-flex items-center gap-2 rounded-md bg-red-500 px-4 py-2 text-sm font-medium text-white hover:bg-red-600 focus:ring-2 focus:ring-red-400 focus:outline-none"
                        :preserve-scroll="true"
                        onclick="return confirm('Are you sure you want to delete this routine?')"
                    >
                        <Trash class="h-4 w-4" />
                        Delete Routine
                    </Link>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Program Routines</CardTitle
                        >
                        <CalendarIcon class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div
                            class="text-2xl font-bold text-gray-900 dark:text-gray-100"
                        >
                            {{ program_stats.total_routines }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Total routines in {{ routine.program.name }}
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Active Routines</CardTitle
                        >
                        <CheckCircle class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div
                            class="text-2xl font-bold text-green-600 dark:text-green-400"
                        >
                            {{ program_stats.active_routines }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Currently active in program
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Course Usage</CardTitle
                        >
                        <BookOpen class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div
                            class="text-2xl font-bold text-blue-600 dark:text-blue-400"
                        >
                            {{ program_stats.unique_courses }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Unique courses in program
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Teachers</CardTitle
                        >
                        <GraduationCap class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div
                            class="text-2xl font-bold text-purple-600 dark:text-purple-400"
                        >
                            {{ program_stats.unique_teachers }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Teachers in program
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
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
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Day
                                </label>
                                <p class="mt-1">
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-sm font-medium"
                                        :class="getDayClasses(routine.day)"
                                    >
                                        <CalendarIcon class="h-3 w-3" />
                                        {{ routine.day }}
                                    </span>
                                </p>
                            </div>
                            <div>
                                <label
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Time Slot
                                </label>
                                <p
                                    class="mt-1 text-lg font-medium text-gray-900 dark:text-gray-100"
                                >
                                    {{ routine.time_slot }}
                                </p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Start Time
                                </label>
                                <p
                                    class="mt-1 flex items-center gap-2 text-lg font-medium text-gray-900 dark:text-gray-100"
                                >
                                    <Clock class="h-4 w-4" />
                                    {{ formatTime(routine.start_time) }}
                                </p>
                            </div>
                            <div>
                                <label
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    End Time
                                </label>
                                <p
                                    class="mt-1 flex items-center gap-2 text-lg font-medium text-gray-900 dark:text-gray-100"
                                >
                                    <Clock class="h-4 w-4" />
                                    {{ formatTime(routine.end_time) }}
                                </p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Duration
                                </label>
                                <p
                                    class="mt-1 text-lg font-medium text-gray-900 dark:text-gray-100"
                                >
                                    {{ calculateDuration() }}
                                </p>
                            </div>
                            <div>
                                <label
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Slot Order
                                </label>
                                <p
                                    class="mt-1 text-lg font-medium text-gray-900 dark:text-gray-100"
                                >
                                    #{{ routine.slot_order }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Status & Program Info -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <component
                                :is="getStatusIcon(routine.status)"
                                class="h-5 w-5"
                            />
                            Status & Program Information
                        </CardTitle>
                        <CardDescription>
                            Current status and academic details
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Status
                                </label>
                                <p class="mt-1">
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-sm font-medium capitalize"
                                        :class="
                                            getStatusClasses(routine.status)
                                        "
                                    >
                                        <component
                                            :is="getStatusIcon(routine.status)"
                                            class="h-3 w-3"
                                        />
                                        {{ routine.status }}
                                    </span>
                                </p>
                            </div>
                            <div>
                                <label
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Conflict Check
                                </label>
                                <p class="mt-1">
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-sm font-medium"
                                        :class="
                                            getConflictClasses(
                                                routine.is_conflict,
                                            )
                                        "
                                    >
                                        <AlertCircle
                                            v-if="routine.is_conflict"
                                            class="h-3 w-3"
                                        />
                                        <CheckCircle v-else class="h-3 w-3" />
                                        {{
                                            routine.is_conflict
                                                ? 'Has Conflicts'
                                                : 'No Conflicts'
                                        }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Program
                                </label>
                                <p
                                    class="mt-1 flex items-center gap-2 text-gray-900 dark:text-gray-100"
                                >
                                    <Building class="h-4 w-4" />
                                    {{ routine.program.name }}
                                    <span class="text-sm text-gray-500">
                                        ({{ routine.program.code }})
                                    </span>
                                </p>
                            </div>
                            <div>
                                <label
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Semester
                                </label>
                                <p
                                    class="mt-1 text-lg font-medium text-gray-900 dark:text-gray-100"
                                >
                                    {{ routine.semester }}
                                </p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Intake & Section
                                </label>
                                <p
                                    class="mt-1 flex items-center gap-2 text-lg font-medium text-gray-900 dark:text-gray-100"
                                >
                                    <Users class="h-4 w-4" />
                                    {{ routine.intake_full }}
                                </p>
                            </div>
                            <div>
                                <label
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Effective Date
                                </label>
                                <p
                                    class="mt-1 flex items-center gap-2 text-gray-900 dark:text-gray-100"
                                >
                                    <CalendarIcon class="h-4 w-4" />
                                    {{ formatDate(routine.effective_date) }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Course & Teacher Details -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Course Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <BookOpen class="h-5 w-5" />
                            Course Information
                        </CardTitle>
                        <CardDescription>
                            Details about the course being taught
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Course Code
                                </label>
                                <p
                                    class="mt-1 text-lg font-medium text-gray-900 dark:text-gray-100"
                                >
                                    {{ routine.course_code }}
                                </p>
                            </div>
                            <div>
                                <label
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Credit Hours
                                </label>
                                <p
                                    v-if="routine.course_credit_hours"
                                    class="mt-1 text-lg font-medium text-gray-900 dark:text-gray-100"
                                >
                                    {{ routine.course_credit_hours }} credits
                                </p>
                                <p v-else class="mt-1 text-gray-500">
                                    Not specified
                                </p>
                            </div>
                        </div>
                        <div>
                            <label
                                class="text-sm font-medium text-gray-500 dark:text-gray-400"
                            >
                                Course Name
                            </label>
                            <p
                                v-if="routine.course_name"
                                class="mt-1 text-gray-900 dark:text-gray-100"
                            >
                                {{ routine.course_name }}
                            </p>
                            <p v-else class="mt-1 text-gray-500 italic">
                                No course name provided
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Teacher Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <User class="h-5 w-5" />
                            Teacher Information
                        </CardTitle>
                        <CardDescription>
                            Details about the assigned teacher
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Teacher Code
                                </label>
                                <p
                                    class="mt-1 text-lg font-medium text-gray-900 dark:text-gray-100"
                                >
                                    {{ routine.teacher_code }}
                                </p>
                            </div>
                            <div>
                                <label
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Department
                                </label>
                                <p
                                    v-if="routine.teacher_department"
                                    class="mt-1 text-gray-900 dark:text-gray-100"
                                >
                                    {{ routine.teacher_department }}
                                </p>
                                <p v-else class="mt-1 text-gray-500">
                                    Not specified
                                </p>
                            </div>
                        </div>
                        <div>
                            <label
                                class="text-sm font-medium text-gray-500 dark:text-gray-400"
                            >
                                Teacher Name
                            </label>
                            <p
                                v-if="routine.teacher_name"
                                class="mt-1 text-gray-900 dark:text-gray-100"
                            >
                                {{ routine.teacher_name }}
                            </p>
                            <p v-else class="mt-1 text-gray-500 italic">
                                No teacher name provided
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Room & Additional Details -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Room Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Home class="h-5 w-5" />
                            Room Information
                        </CardTitle>
                        <CardDescription>
                            Venue details for this class
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Room Number
                                </label>
                                <p
                                    class="mt-1 text-lg font-medium text-gray-900 dark:text-gray-100"
                                >
                                    {{ routine.room_number }}
                                </p>
                            </div>
                            <div>
                                <label
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Room Type
                                </label>
                                <p class="mt-1">
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-sm font-medium"
                                        :class="
                                            getRoomTypeClasses(
                                                routine.room_type,
                                            )
                                        "
                                    >
                                        <MapPin class="h-3 w-3" />
                                        {{ routine.room_type || 'General' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div>
                            <label
                                class="text-sm font-medium text-gray-500 dark:text-gray-400"
                            >
                                Room Capacity
                            </label>
                            <p
                                v-if="routine.room_capacity"
                                class="mt-1 flex items-center gap-2 text-gray-900 dark:text-gray-100"
                            >
                                <Users class="h-4 w-4" />
                                {{ routine.room_capacity }} students
                            </p>
                            <p v-else class="mt-1 text-gray-500">
                                Capacity not specified
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Additional Details & Timeline -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <FileText class="h-5 w-5" />
                            Additional Details & Timeline
                        </CardTitle>
                        <CardDescription>
                            Class notes and creation timeline
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <label
                                class="text-sm font-medium text-gray-500 dark:text-gray-400"
                            >
                                Class Details
                            </label>
                            <p
                                v-if="routine.class_details"
                                class="mt-1 rounded-md bg-gray-50 p-3 text-gray-700 dark:bg-gray-800 dark:text-gray-300"
                            >
                                {{ routine.class_details }}
                            </p>
                            <p v-else class="mt-1 text-gray-500 italic">
                                No additional details provided
                            </p>
                        </div>
                        <div class="grid grid-cols-2 gap-4 border-t pt-4">
                            <div>
                                <label
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Created At
                                </label>
                                <p
                                    class="mt-1 flex items-center gap-1 text-sm text-gray-900 dark:text-gray-100"
                                >
                                    <CalendarIcon class="h-4 w-4" />
                                    {{ formatDate(routine.created_at) }}
                                </p>
                            </div>
                            <div>
                                <label
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Last Updated
                                </label>
                                <p
                                    class="mt-1 flex items-center gap-1 text-sm text-gray-900 dark:text-gray-100"
                                >
                                    <CalendarIcon class="h-4 w-4" />
                                    {{ formatDate(routine.updated_at) }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Conflict Details (if any) -->
            <Card v-if="routine.is_conflict && routine.conflict_details.length">
                <CardHeader>
                    <CardTitle
                        class="flex items-center gap-2 text-red-600 dark:text-red-400"
                    >
                        <AlertCircle class="h-5 w-5" />
                        Schedule Conflicts
                        <span
                            class="rounded-full bg-red-100 px-2 py-1 text-sm text-red-800"
                        >
                            {{ routine.conflict_details.length }}
                        </span>
                    </CardTitle>
                    <CardDescription>
                        This routine has scheduling conflicts that need
                        attention
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div
                        class="relative overflow-x-auto rounded-lg border border-red-200 dark:border-red-800"
                    >
                        <Table>
                            <TableHeader class="bg-red-50 dark:bg-red-900/20">
                                <TableRow>
                                    <TableHead>Conflict Type</TableHead>
                                    <TableHead>Description</TableHead>
                                    <TableHead>Severity</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="(
                                        conflict, index
                                    ) in routine.conflict_details"
                                    :key="index"
                                    class="hover:bg-red-50 dark:hover:bg-red-900/20"
                                >
                                    <TableCell class="font-medium">
                                        {{ conflict.type }}
                                    </TableCell>
                                    <TableCell
                                        class="text-gray-700 dark:text-gray-300"
                                    >
                                        {{ conflict.description }}
                                    </TableCell>
                                    <TableCell>
                                        <span
                                            class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900 dark:text-red-200"
                                        >
                                            High
                                        </span>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Custom styles for better spacing */
.border-t {
    border-top-width: 1px;
    border-top-style: solid;
}
</style>
