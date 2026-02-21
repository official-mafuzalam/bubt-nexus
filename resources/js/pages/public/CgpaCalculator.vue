<template>
    <MainLayout :padded="true">
        <Head title="CGPA Calculator">
            <link rel="preconnect" href="https://rsms.me/" />
            <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
        </Head>

        <div class="min-h-screen bg-gray-50 py-12 dark:bg-gray-900">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-12 text-center">
                    <h1
                        class="mb-4 text-4xl font-bold text-gray-900 dark:text-white"
                    >
                        University CGPA Calculator
                    </h1>
                    <p class="text-lg text-gray-600 dark:text-gray-400">
                        Calculate your Semester GPA and Cumulative GPA easily
                    </p>
                </div>

                <!-- Semesters Container -->
                <div class="space-y-6">
                    <div
                        v-for="(semester, sIndex) in semesters"
                        :key="sIndex"
                        class="overflow-hidden rounded-lg border-l-4 border-orange-500 bg-white shadow-xl dark:bg-gray-800"
                    >
                        <!-- Semester Header -->
                        <div
                            class="border-b border-orange-200 bg-gradient-to-r from-orange-50 to-orange-100 px-6 py-4 dark:border-orange-800 dark:from-orange-900/20 dark:to-orange-800/20"
                        >
                            <div class="flex items-center justify-between">
                                <h3
                                    class="text-xl font-semibold text-orange-800 dark:text-orange-300"
                                >
                                    Semester {{ sIndex + 1 }}
                                </h3>
                                <div
                                    class="rounded-full bg-orange-200 px-3 py-1 text-sm text-orange-800 dark:bg-orange-700 dark:text-orange-200"
                                >
                                    GPA:
                                    <span class="font-bold">{{
                                        calculateSGPA(semester).toFixed(2)
                                    }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Courses Table -->
                        <div class="p-6">
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead>
                                        <tr
                                            class="border-b-2 border-gray-200 dark:border-gray-700"
                                        >
                                            <th
                                                class="px-4 py-3 text-left text-sm font-medium text-gray-600 dark:text-gray-400"
                                            >
                                                Course Name
                                            </th>
                                            <th
                                                class="px-4 py-3 text-left text-sm font-medium text-gray-600 dark:text-gray-400"
                                            >
                                                Credits
                                            </th>
                                            <th
                                                class="px-4 py-3 text-left text-sm font-medium text-gray-600 dark:text-gray-400"
                                            >
                                                Grade (0.0 - 4.0)
                                            </th>
                                            <th
                                                class="w-20 px-4 py-3 text-center text-sm font-medium text-gray-600 dark:text-gray-400"
                                            >
                                                Action
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(
                                                course, cIndex
                                            ) in semester.courses"
                                            :key="cIndex"
                                            class="border-b border-gray-100 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/50"
                                        >
                                            <td class="px-4 py-3">
                                                <input
                                                    v-model="course.name"
                                                    type="text"
                                                    placeholder="e.g. Data Structures"
                                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:ring-2 focus:ring-orange-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                                />
                                            </td>
                                            <td class="px-4 py-3">
                                                <input
                                                    v-model.number="
                                                        course.credits
                                                    "
                                                    type="number"
                                                    step="0.5"
                                                    min="0"
                                                    class="w-24 rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:ring-2 focus:ring-orange-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                                />
                                            </td>
                                            <td class="px-4 py-3">
                                                <input
                                                    v-model.number="
                                                        course.grade
                                                    "
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    max="4"
                                                    class="w-24 rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:ring-2 focus:ring-orange-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                                />
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <button
                                                    @click="
                                                        removeCourse(
                                                            sIndex,
                                                            cIndex,
                                                        )
                                                    "
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-red-100 text-red-600 transition-colors hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-800/50"
                                                    title="Remove course"
                                                >
                                                    <span
                                                        class="text-lg font-bold"
                                                        >×</span
                                                    >
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Add Course Button -->
                            <div class="mt-4">
                                <button
                                    @click="addCourse(sIndex)"
                                    class="inline-flex items-center rounded-lg bg-orange-100 px-4 py-2 text-sm font-medium text-orange-700 transition-colors hover:bg-orange-200 dark:bg-orange-900/30 dark:text-orange-300 dark:hover:bg-orange-800/50"
                                >
                                    <svg
                                        class="mr-2 h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 4v16m8-8H4"
                                        />
                                    </svg>
                                    Add Course
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Semester Button -->
                <div class="mt-8 text-center">
                    <button
                        @click="addSemester"
                        class="inline-flex items-center rounded-lg bg-orange-600 px-6 py-3 font-medium text-white shadow-lg transition-all duration-200 hover:bg-orange-700 hover:shadow-xl"
                    >
                        <svg
                            class="mr-2 h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 4v16m8-8H4"
                            />
                        </svg>
                        Add New Semester
                    </button>
                </div>

                <!-- CGPA Result Card -->
                <div
                    class="mt-12 overflow-hidden rounded-2xl bg-gradient-to-br from-orange-600 to-orange-700 shadow-2xl"
                >
                    <div class="px-8 py-10 text-center">
                        <h3 class="mb-4 text-2xl font-medium text-orange-100">
                            Cumulative GPA (CGPA)
                        </h3>
                        <div
                            class="mb-2 text-7xl font-bold tracking-tight text-white"
                        >
                            {{ totalCGPA }}
                        </div>
                        <div class="text-sm text-orange-200">
                            Based on {{ totalCourses }} courses across
                            {{ semesters.length }} semester{{
                                semesters.length !== 1 ? 's' : ''
                            }}
                        </div>

                        <!-- Statistics Row -->
                        <div
                            class="mt-8 grid grid-cols-1 gap-4 border-t border-orange-500 pt-8 md:grid-cols-3"
                        >
                            <div class="text-center">
                                <div class="text-3xl font-bold text-white">
                                    {{ totalCredits.toFixed(1) }}
                                </div>
                                <div class="text-sm text-orange-200">
                                    Total Credits
                                </div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-white">
                                    {{ totalPoints.toFixed(2) }}
                                </div>
                                <div class="text-sm text-orange-200">
                                    Grade Points
                                </div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-white">
                                    {{ averageSemesterGPA.toFixed(2) }}
                                </div>
                                <div class="text-sm text-orange-200">
                                    Avg Semester GPA
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Tips -->
                <div class="mt-8 rounded-lg bg-blue-50 p-6 dark:bg-blue-900/20">
                    <h4
                        class="mb-3 text-sm font-semibold tracking-wider text-blue-800 uppercase dark:text-blue-300"
                    >
                        Quick Tips
                    </h4>
                    <ul
                        class="space-y-2 text-sm text-blue-700 dark:text-blue-300"
                    >
                        <li class="flex items-start">
                            <svg
                                class="mt-0.5 mr-2 h-4 w-4 flex-shrink-0"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                            <span
                                >Grades should be between 0.0 and 4.0 (A = 4.0,
                                A- = 3.7, B+ = 3.3, B = 3.0, etc.)</span
                            >
                        </li>
                        <li class="flex items-start">
                            <svg
                                class="mt-0.5 mr-2 h-4 w-4 flex-shrink-0"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                            <span
                                >Credits can be decimal values (e.g., 1.5, 3.0,
                                4.0)</span
                            >
                        </li>
                        <li class="flex items-start">
                            <svg
                                class="mt-0.5 mr-2 h-4 w-4 flex-shrink-0"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                            <span
                                >CGPA is calculated as (Total Grade Points) /
                                (Total Credits)</span
                            >
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import MainLayout from '@/layouts/PublicLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';

// Initialize with one semester and one course
const semesters = reactive([
    { courses: [{ name: '', credits: 3, grade: 4.0 }] },
]);

// Add new semester
const addSemester = () => {
    semesters.push({ courses: [{ name: '', credits: 3, grade: 0 }] });
};

// Add course to specific semester
const addCourse = (semesterIndex) => {
    semesters[semesterIndex].courses.push({ name: '', credits: 3, grade: 0 });
};

// Remove course from semester
const removeCourse = (semesterIndex, courseIndex) => {
    if (semesters[semesterIndex].courses.length > 1) {
        semesters[semesterIndex].courses.splice(courseIndex, 1);
    }
};

// Calculate Semester GPA
const calculateSGPA = (semester) => {
    let totalPoints = 0;
    let totalCredits = 0;

    semester.courses.forEach((course) => {
        const credits = parseFloat(course.credits) || 0;
        const grade = parseFloat(course.grade) || 0;
        totalPoints += grade * credits;
        totalCredits += credits;
    });

    return totalCredits === 0 ? 0 : totalPoints / totalCredits;
};

// Calculate total credits
const totalCredits = computed(() => {
    let total = 0;
    semesters.forEach((semester) => {
        semester.courses.forEach((course) => {
            total += parseFloat(course.credits) || 0;
        });
    });
    return total;
});

// Calculate total grade points
const totalPoints = computed(() => {
    let total = 0;
    semesters.forEach((semester) => {
        semester.courses.forEach((course) => {
            const credits = parseFloat(course.credits) || 0;
            const grade = parseFloat(course.grade) || 0;
            total += grade * credits;
        });
    });
    return total;
});

// Calculate total courses
const totalCourses = computed(() => {
    let count = 0;
    semesters.forEach((semester) => {
        count += semester.courses.length;
    });
    return count;
});

// Calculate average semester GPA
const averageSemesterGPA = computed(() => {
    if (semesters.length === 0) return 0;

    let totalGPA = 0;
    semesters.forEach((semester) => {
        totalGPA += calculateSGPA(semester);
    });
    return totalGPA / semesters.length;
});

// Calculate CGPA
const totalCGPA = computed(() => {
    const credits = totalCredits.value;
    const points = totalPoints.value;

    return credits === 0 ? '0.00' : (points / credits).toFixed(2);
});
</script>

<style scoped>
/* Remove all previous styles as they're now handled by Tailwind */
</style>
