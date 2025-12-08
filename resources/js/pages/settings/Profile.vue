<script setup lang="ts">
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
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
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import { Form, Head, usePage } from '@inertiajs/vue3';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
    isFaculty: boolean;
    isStudent: boolean;
}

const props = defineProps<Props>();

const page = usePage();
const user = page.props.user;

const isFaculty = props.isFaculty;
const isStudent = props.isStudent;
const semesterOptions = page.props.semesterOptions;
const departmentOptions = page.props.departmentOptions;
const designationOptions = page.props.designationOptions;

// Breadcrumb
const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Profile settings', href: '/settings/profile' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall
                    title="Profile information"
                    description="Update your information"
                />

                <Form
                    v-bind="ProfileController.update.form()"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <!-- Common fields -->
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            name="name"
                            :default-value="user.name"
                            required
                            placeholder="Full name"
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            :default-value="user.email"
                            required
                            placeholder="Email"
                        />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="phone">Phone</Label>
                        <Input
                            id="phone"
                            name="phone"
                            :default-value="user.user_detail?.phone"
                            placeholder="Phone number"
                        />
                        <InputError :message="errors.phone" />
                    </div>

                    <!-- Student-specific fields -->
                    <template v-if="isStudent">
                        <div class="grid gap-2">
                            <Label for="semester">Semester</Label>
                            <Select
                                name="semester"
                                :default-value="user.user_detail?.semester"
                            >
                                <SelectTrigger>
                                    <SelectValue
                                        placeholder="Select Semester"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="(id, name) in semesterOptions"
                                        :key="id"
                                        :value="name"
                                    >
                                        {{ name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.semester" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="intake">Intake</Label>
                            <Input
                                id="intake"
                                name="intake"
                                type="number"
                                :default-value="user.user_detail?.intake"
                            />
                            <InputError :message="errors.intake" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="section">Section</Label>
                            <Input
                                id="section"
                                name="section"
                                :default-value="user.user_detail?.section"
                            />
                            <InputError :message="errors.section" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="cgpa">CGPA</Label>
                            <Input
                                id="cgpa"
                                name="cgpa"
                                type="number"
                                step="0.01"
                                :default-value="user.user_detail?.cgpa"
                            />
                            <InputError :message="errors.cgpa" />
                        </div>
                    </template>

                    <!-- Faculty-specific fields -->
                    <template v-if="isFaculty">
                        <div class="grid gap-2">
                            <Label for="department">Department</Label>
                            <Select
                                name="department"
                                :default-value="user.user_detail?.department"
                            >
                                <SelectTrigger>
                                    <SelectValue
                                        placeholder="Select Department"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="dept in departmentOptions"
                                        :key="dept"
                                        :value="dept"
                                    >
                                        {{ dept }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.department" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="designation">Designation</Label>
                            <Select
                                name="designation"
                                :default-value="user.user_detail?.designation"
                            >
                                <SelectTrigger>
                                    <SelectValue
                                        placeholder="Select Designation"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="des in designationOptions"
                                        :key="des"
                                        :value="des"
                                    >
                                        {{ des }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.designation" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="faculty_code">Faculty Code</Label>
                            <Input
                                id="faculty_code"
                                name="faculty_code"
                                :default-value="user.user_detail?.faculty_code"
                            />
                            <InputError :message="errors.faculty_code" />
                        </div>
                    </template>

                    <div class="flex items-center gap-4">
                        <Button :disabled="processing">Save</Button>
                        <p
                            v-show="recentlySuccessful"
                            class="text-sm text-neutral-600"
                        >
                            Saved.
                        </p>
                    </div>
                </Form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
