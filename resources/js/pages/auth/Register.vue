<script setup lang="ts">
import RegisteredUserController from '@/actions/App/Http/Controllers/Auth/RegisteredUserController';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { ref } from 'vue';

// Add props if your controller passes programs data
interface Props {
    programs?: Array<{ id: number; name: string; code: string }>;
    semesterOptions?: Record<string, number>;
    departments?: string[];
    designations?: string[];
}

const props = defineProps<Props>();

const userType = ref<'student' | 'faculty'>('student');

// Student fields
const semester = ref('');
const intake = ref(''); // Changed from select to input
const section = ref('');
const studentId = ref('');
const cgpa = ref('');

// Faculty fields
const facultyCode = ref('');

// Common fields
const phone = ref('');

// Student section options
const sectionOptions = Array.from({ length: 20 }, (_, i) => (i + 1).toString());
</script>

<template>
    <AuthBase
        title="Create an account"
        description="Enter your details below to create your account"
        type="register"
    >
        <Head title="Register" />

        <Form
            v-bind="RegisteredUserController.store.form()"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="space-y-8"
        >
            <!-- User Type Tabs -->
            <div class="border-b">
                <nav class="-mb-px flex space-x-8">
                    <button
                        type="button"
                        :class="[
                            'border-b-2 px-1 py-4 text-sm font-medium transition-colors duration-200',
                            userType === 'student'
                                ? 'border-[#2563eb] text-[#2563eb] dark:border-[#3b82f6] dark:text-[#3b82f6]'
                                : 'border-transparent text-[#6b7280] hover:border-gray-300 hover:text-gray-700 dark:text-[#9ca3af] dark:hover:border-gray-600 dark:hover:text-gray-300',
                        ]"
                        @click="userType = 'student'"
                    >
                        Student Registration
                    </button>
                    <button
                        type="button"
                        :class="[
                            'border-b-2 px-1 py-4 text-sm font-medium transition-colors duration-200',
                            userType === 'faculty'
                                ? 'border-[#9333ea] text-[#9333ea] dark:border-[#a855f7] dark:text-[#a855f7]'
                                : 'border-transparent text-[#6b7280] hover:border-gray-300 hover:text-gray-700 dark:text-[#9ca3af] dark:hover:border-gray-600 dark:hover:text-gray-300',
                        ]"
                        @click="userType = 'faculty'"
                    >
                        Faculty Registration
                    </button>
                </nav>
            </div>

            <!-- Main Grid Layout -->
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                <!-- Left Column: Personal Information -->
                <div class="space-y-6">
                    <div
                        class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800"
                    >
                        <h3 class="mb-4 text-lg font-semibold">
                            Personal Information
                        </h3>

                        <div class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="name">Full Name</Label>
                                <Input
                                    id="name"
                                    type="text"
                                    required
                                    autofocus
                                    :tabindex="1"
                                    autocomplete="name"
                                    name="name"
                                    placeholder="Enter your full name"
                                />
                                <InputError :message="errors.name" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="email">Email Address</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    required
                                    :tabindex="2"
                                    autocomplete="email"
                                    name="email"
                                    placeholder="email@example.com"
                                />
                                <InputError :message="errors.email" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="phone">Phone Number</Label>
                                <Input
                                    id="phone"
                                    type="tel"
                                    required
                                    :tabindex="3"
                                    autocomplete="tel"
                                    name="user_details[phone]"
                                    placeholder="+8801XXXXXXXXX"
                                    v-model="phone"
                                />
                                <InputError
                                    :message="errors['user_details.phone']"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Password Section -->
                    <div
                        class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800"
                    >
                        <h3 class="mb-4 text-lg font-semibold">
                            Account Security
                        </h3>

                        <div class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="password">Password</Label>
                                <Input
                                    id="password"
                                    type="password"
                                    required
                                    :tabindex="10"
                                    autocomplete="new-password"
                                    name="password"
                                    placeholder="Create a strong password"
                                />
                                <InputError :message="errors.password" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="password_confirmation"
                                    >Confirm Password</Label
                                >
                                <Input
                                    id="password_confirmation"
                                    type="password"
                                    required
                                    :tabindex="11"
                                    autocomplete="new-password"
                                    name="password_confirmation"
                                    placeholder="Confirm your password"
                                />
                                <InputError
                                    :message="errors.password_confirmation"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: User Type Specific Information -->
                <div class="space-y-6">
                    <!-- Student Specific Fields -->
                    <div
                        v-if="userType === 'student'"
                        class="rounded-xl border border-blue-200 bg-blue-50 p-6 shadow-sm dark:border-blue-800 dark:bg-blue-900/20"
                    >
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold">
                                Student Information
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Enter your academic details
                            </p>
                        </div>

                        <div class="space-y-6">
                            <div class="grid gap-2">
                                <Label for="student_id">Student ID</Label>
                                <Input
                                    id="student_id"
                                    type="text"
                                    required
                                    :tabindex="4"
                                    name="user_details[student_id]"
                                    placeholder="e.g., 2024XXXXXX"
                                    v-model="studentId"
                                />
                                <InputError
                                    :message="errors['user_details.student_id']"
                                />
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="grid gap-2">
                                    <Label for="program_id">Program</Label>
                                    <Select
                                        name="user_details[program_id]"
                                        required
                                        :tabindex="5"
                                    >
                                        <SelectTrigger>
                                            <SelectValue
                                                placeholder="Select program"
                                            />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="program in programs"
                                                :key="program.id"
                                                :value="program.id"
                                            >
                                                {{ program.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError
                                        :message="
                                            errors['user_details.program_id']
                                        "
                                    />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="semester"
                                        >Current Semester</Label
                                    >
                                    <Select
                                        name="user_details[semester]"
                                        required
                                        :tabindex="6"
                                    >
                                        <SelectTrigger>
                                            <SelectValue
                                                placeholder="Select current semester"
                                            />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="(value, key) in semesterOptions"
                                                :key="value"
                                                :value="String(value)"
                                            >
                                                {{ key }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError
                                        :message="
                                            errors['user_details.semester']
                                        "
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div class="grid gap-2">
                                    <Label for="intake">Intake</Label>
                                    <Input
                                        id="intake"
                                        type="number"
                                        required
                                        :tabindex="7"
                                        name="user_details[intake]"
                                        placeholder="e.g., 45"
                                        v-model="intake"
                                        min="20"
                                        max="90"
                                    />
                                    <InputError
                                        :message="errors['user_details.intake']"
                                    />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="section">Section</Label>
                                    <Select
                                        name="user_details[section]"
                                        required
                                        :tabindex="8"
                                    >
                                        <SelectTrigger>
                                            <SelectValue
                                                placeholder="Select section"
                                            />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="sec in sectionOptions"
                                                :key="sec"
                                                :value="sec"
                                            >
                                                Section {{ sec }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError
                                        :message="
                                            errors['user_details.section']
                                        "
                                    />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="cgpa">CGPA</Label>
                                    <Input
                                        id="cgpa"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        max="4.00"
                                        :tabindex="9"
                                        name="user_details[cgpa]"
                                        placeholder="e.g., 3.75"
                                        v-model="cgpa"
                                    />
                                    <InputError
                                        :message="errors['user_details.cgpa']"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Faculty Specific Fields -->
                    <div
                        v-else
                        class="rounded-xl border border-purple-200 bg-purple-50 p-6 shadow-sm dark:border-purple-800 dark:bg-purple-900/20"
                    >
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold">
                                Faculty Information
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Enter your professional details
                            </p>
                        </div>

                        <div class="space-y-6">
                            <div class="grid gap-2">
                                <Label for="faculty_code">Faculty Code</Label>
                                <Input
                                    id="faculty_code"
                                    type="text"
                                    required
                                    :tabindex="4"
                                    name="user_details[faculty_code]"
                                    placeholder="e.g., SR"
                                    v-model="facultyCode"
                                />
                                <InputError
                                    :message="
                                        errors['user_details.faculty_code']
                                    "
                                />
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="grid gap-2">
                                    <Label for="department">Department</Label>
                                    <Select
                                        name="user_details[department]"
                                        required
                                        :tabindex="5"
                                    >
                                        <SelectTrigger>
                                            <SelectValue
                                                placeholder="Select department"
                                            />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="dept in departments"
                                                :key="dept"
                                                :value="dept"
                                            >
                                                {{ dept }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError
                                        :message="
                                            errors['user_details.department']
                                        "
                                    />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="designation">Designation</Label>
                                    <Select
                                        name="user_details[designation]"
                                        required
                                        :tabindex="6"
                                    >
                                        <SelectTrigger>
                                            <SelectValue
                                                placeholder="Select designation"
                                            />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="designation in designations"
                                                :key="designation"
                                                :value="designation"
                                            >
                                                {{ designation }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError
                                        :message="
                                            errors['user_details.designation']
                                        "
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden field for user type -->
                    <input type="hidden" name="user_type" :value="userType" />

                    <!-- Action Buttons -->
                    <div
                        class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800"
                    >
                        <div class="flex flex-col gap-4 sm:flex-row">
                            <Button
                                type="submit"
                                class="flex-1 bg-[#2563eb] py-3 text-base font-medium text-white hover:bg-[#1d4ed8]"
                                tabindex="12"
                                :disabled="processing"
                                data-test="register-user-button"
                            >
                                <LoaderCircle
                                    v-if="processing"
                                    class="mr-2 h-5 w-5 animate-spin"
                                />
                                Create
                                {{
                                    userType === 'student'
                                        ? 'Student'
                                        : 'Faculty'
                                }}
                                Account
                            </Button>

                            <Button
                                type="button"
                                variant="outline"
                                class="py-3 text-base font-medium"
                                @click="
                                    userType =
                                        userType === 'student'
                                            ? 'faculty'
                                            : 'student'
                                "
                            >
                                Switch to
                                {{
                                    userType === 'student'
                                        ? 'Faculty'
                                        : 'Student'
                                }}
                            </Button>
                        </div>

                        <div
                            class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400"
                        >
                            Already have an account?
                            <TextLink
                                :href="login()"
                                class="ml-2 font-medium text-[#2563eb] underline underline-offset-4 hover:text-[#1d4ed8] dark:text-[#3b82f6] dark:hover:text-[#60a5fa]"
                                :tabindex="13"
                            >
                                Log in here
                            </TextLink>
                        </div>
                    </div>
                </div>
            </div>
        </Form>
    </AuthBase>
</template>
