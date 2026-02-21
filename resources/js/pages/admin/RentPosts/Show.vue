<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Property Header -->
            <div
                class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
            >
                <div
                    class="flex flex-col items-start justify-between gap-4 md:flex-row md:items-center"
                >
                    <div>
                        <div class="mb-2 flex items-center gap-3">
                            <span
                                :class="[
                                    'rounded-full px-3 py-1 text-xs font-semibold',
                                    post.is_available
                                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                        : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                ]"
                            >
                                {{
                                    post.is_available
                                        ? 'Available'
                                        : 'Not Available'
                                }}
                            </span>
                            <span
                                class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-900 dark:text-amber-200"
                            >
                                {{ post.category_label }}
                            </span>
                        </div>
                        <h1
                            class="text-2xl font-bold text-gray-900 dark:text-gray-100"
                        >
                            {{ post.title }}
                        </h1>
                        <div
                            class="mt-2 flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400"
                        >
                            <div class="flex items-center gap-1">
                                <MapPin class="h-4 w-4" />
                                <span>{{ post.location }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <Calendar class="h-4 w-4" />
                                <span
                                    >Posted
                                    {{ formatDate(post.created_at) }}</span
                                >
                            </div>
                            <!-- Show owner badge if authorized -->
                            <div
                                v-if="isAuthorized"
                                class="flex items-center gap-1"
                            >
                                <User class="h-4 w-4" />
                                <span
                                    class="text-amber-600 dark:text-amber-400"
                                >
                                    {{ post.user?.name }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <span
                            class="text-3xl font-bold text-gray-900 dark:text-gray-100"
                        >
                            {{ post.formatted_rent }}
                            <span
                                class="text-sm font-normal text-gray-500 dark:text-gray-400"
                                >/month</span
                            >
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Left Column - Property Details -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Property Images -->
                    <div
                        class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                    >
                        <h2
                            class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100"
                        >
                            Property Images
                        </h2>
                        <div class="grid grid-cols-2 gap-4 md:grid-cols-3">
                            <!-- Image Placeholder - Replace with actual images -->
                            <div
                                class="flex aspect-video items-center justify-center rounded-lg bg-gray-200 dark:bg-gray-700"
                            >
                                <Home class="h-12 w-12 text-gray-400" />
                            </div>
                            <div
                                class="flex aspect-video items-center justify-center rounded-lg bg-gray-200 dark:bg-gray-700"
                            >
                                <Home class="h-12 w-12 text-gray-400" />
                            </div>
                            <div
                                class="flex aspect-video items-center justify-center rounded-lg bg-gray-200 dark:bg-gray-700"
                            >
                                <Home class="h-12 w-12 text-gray-400" />
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div
                        class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                    >
                        <h2
                            class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100"
                        >
                            Description
                        </h2>
                        <div
                            class="prose max-w-none text-gray-700 dark:text-gray-300"
                        >
                            <p
                                v-if="post.description"
                                class="whitespace-pre-line"
                            >
                                {{ post.description }}
                            </p>
                            <p
                                v-else
                                class="text-gray-500 italic dark:text-gray-400"
                            >
                                No description provided.
                            </p>
                        </div>
                    </div>

                    <!-- Address Details -->
                    <div
                        v-if="post.address_detail"
                        class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                    >
                        <h2
                            class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100"
                        >
                            Address Details
                        </h2>
                        <div class="flex items-start gap-3">
                            <MapPin class="mt-1 h-5 w-5 text-gray-400" />
                            <div class="text-gray-700 dark:text-gray-300">
                                {{ post.address_detail }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Sidebar -->
                <div class="space-y-6">
                    <!-- Contact Information -->
                    <div
                        class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                    >
                        <h2
                            class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100"
                        >
                            Contact Information
                        </h2>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <User class="h-5 w-5 text-gray-400" />
                                    <div>
                                        <p
                                            class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                        >
                                            {{ post.user?.name }}
                                        </p>
                                        <p
                                            class="text-xs text-gray-500 dark:text-gray-400"
                                        >
                                            Owner
                                        </p>
                                    </div>
                                </div>
                                <span
                                    class="text-xs text-gray-500 dark:text-gray-400"
                                >
                                    Member since
                                    {{ formatDate(post.user?.created_at) }}
                                </span>
                            </div>

                            <div
                                class="border-t border-gray-200 pt-4 dark:border-gray-700"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <Phone class="h-5 w-5 text-gray-400" />
                                        <div>
                                            <p
                                                class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                            >
                                                {{ post.contact_number }}
                                            </p>
                                            <p
                                                class="text-xs text-gray-500 dark:text-gray-400"
                                            >
                                                Contact Number
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <button
                                            @click="
                                                copyContact(post.contact_number)
                                            "
                                            class="rounded-lg bg-green-50 p-2 text-green-600 hover:bg-green-100 dark:bg-green-900/20 dark:text-green-400 dark:hover:bg-green-900/40"
                                            title="Copy number"
                                        >
                                            <Copy class="h-4 w-4" />
                                        </button>
                                        <a
                                            :href="`tel:${post.contact_number}`"
                                            class="rounded-lg bg-blue-50 p-2 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/40"
                                            title="Call now"
                                        >
                                            <PhoneCall class="h-4 w-4" />
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Property Specifications -->
                    <div
                        class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                    >
                        <h2
                            class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100"
                        >
                            Property Specifications
                        </h2>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 dark:text-gray-400"
                                    >Property Type</span
                                >
                                <span
                                    class="font-medium text-gray-900 dark:text-gray-100"
                                    >{{ post.category_label }}</span
                                >
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 dark:text-gray-400"
                                    >Bedrooms</span
                                >
                                <span
                                    class="font-medium text-gray-900 dark:text-gray-100"
                                >
                                    {{ post.bedrooms || 'N/A' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 dark:text-gray-400"
                                    >Washrooms</span
                                >
                                <span
                                    class="font-medium text-gray-900 dark:text-gray-100"
                                >
                                    {{ post.washrooms || 'N/A' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 dark:text-gray-400"
                                    >Available From</span
                                >
                                <span
                                    class="font-medium text-gray-900 dark:text-gray-100"
                                >
                                    {{
                                        post.available_from_formatted ||
                                        'Immediate'
                                    }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 dark:text-gray-400"
                                    >Posted Date</span
                                >
                                <span
                                    class="font-medium text-gray-900 dark:text-gray-100"
                                    >{{ formatDate(post.created_at) }}</span
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons (Only show if authorized) -->
                    <div
                        v-if="isAuthorized"
                        class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                    >
                        <h2
                            class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100"
                        >
                            Manage Property
                        </h2>
                        <div class="space-y-3">
                            <Link
                                :href="route('admin.rent-posts.edit', post.id)"
                                class="flex w-full items-center justify-center gap-2 rounded-md bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600"
                            >
                                <Edit class="h-4 w-4" />
                                Edit Property
                            </Link>
                            <button
                                @click="toggleAvailability"
                                :class="[
                                    'flex w-full items-center justify-center gap-2 rounded-md px-4 py-2 text-sm font-medium',
                                    post.is_available
                                        ? 'bg-red-500 text-white hover:bg-red-600'
                                        : 'bg-green-500 text-white hover:bg-green-600',
                                ]"
                            >
                                <component
                                    :is="
                                        post.is_available
                                            ? XCircle
                                            : CheckCircle
                                    "
                                    class="h-4 w-4"
                                />
                                {{
                                    post.is_available
                                        ? 'Mark as Unavailable'
                                        : 'Mark as Available'
                                }}
                            </button>
                            <button
                                @click="deletePost"
                                class="flex w-full items-center justify-center gap-2 rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:bg-gray-800 dark:text-red-400 dark:hover:bg-red-900/20"
                            >
                                <Trash class="h-4 w-4" />
                                Delete Property
                            </button>
                        </div>
                    </div>

                    <!-- Contact Owner Section (Show when not authorized) -->
                    <div
                        v-else
                        class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                    >
                        <h2
                            class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100"
                        >
                            Interested in this property?
                        </h2>
                        <div class="space-y-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Contact the owner directly using the buttons
                                above or save this property for later.
                            </p>
                            <button
                                @click="saveProperty"
                                class="flex w-full items-center justify-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                            >
                                <Bookmark class="h-4 w-4" />
                                Save Property
                            </button>
                            <button
                                @click="shareProperty"
                                class="flex w-full items-center justify-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                            >
                                <Share2 class="h-4 w-4" />
                                Share Property
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Similar Properties -->
            <div
                v-if="similarPosts?.length"
                class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
            >
                <h2
                    class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100"
                >
                    Similar Properties in {{ post.location }}
                </h2>
                <div
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4"
                >
                    <div
                        v-for="similar in similarPosts"
                        :key="similar.id"
                        class="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800"
                    >
                        <div class="p-4">
                            <div class="mb-2 flex items-center justify-between">
                                <span
                                    class="text-xs font-medium text-amber-600 dark:text-amber-400"
                                >
                                    {{ similar.category_label }}
                                </span>
                                <span
                                    class="text-xs text-gray-500 dark:text-gray-400"
                                >
                                    ৳{{ similar.rent.toLocaleString() }}
                                </span>
                            </div>
                            <h3
                                class="mb-2 text-sm font-medium text-gray-900 dark:text-gray-100"
                            >
                                {{ similar.title }}
                            </h3>
                            <div
                                class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400"
                            >
                                <MapPin class="h-3 w-3" />
                                <span class="truncate">{{
                                    similar.location
                                }}</span>
                            </div>
                            <Link
                                :href="
                                    route('admin.rent-posts.show', similar.id)
                                "
                                class="mt-3 block text-center text-xs font-medium text-amber-600 hover:text-amber-700 dark:text-amber-400"
                            >
                                View Details →
                            </Link>
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
    Bookmark,
    Calendar,
    CheckCircle,
    Copy,
    Edit,
    Home,
    MapPin,
    Phone,
    PhoneCall,
    Share2,
    Trash,
    User,
    XCircle,
} from 'lucide-vue-next';
import { route } from 'ziggy-js';

const props = defineProps<{
    post: any;
    similarPosts?: any[];
    userPostsCount?: number;
    isAuthorized?: boolean; // Add this prop
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Rent Management', href: route('admin.rent-posts.index') },
    {
        title: props.post.title,
        href: route('admin.rent-posts.show', props.post.id),
    },
];

// Format date
function formatDate(dateString: string) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

// Copy contact number
function copyContact(contactNumber: string) {
    navigator.clipboard
        .writeText(contactNumber)
        .then(() => {
            alert('Contact number copied to clipboard!');
        })
        .catch((err) => {
            console.error('Failed to copy: ', err);
        });
}

// Toggle availability - only if authorized
function toggleAvailability() {
    if (!props.isAuthorized) {
        alert('You are not authorized to perform this action.');
        return;
    }

    router.patch(
        route('admin.rent-posts.toggle-availability', props.post.id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ['post'] });
            },
            onError: (errors) => {
                console.error('Toggle error:', errors);
                alert('Failed to toggle availability. Please try again.');
            },
        },
    );
}

// Delete post - only if authorized
function deletePost() {
    if (!props.isAuthorized) {
        alert('You are not authorized to perform this action.');
        return;
    }

    if (
        confirm(
            'Are you sure you want to delete this property? This action cannot be undone.',
        )
    ) {
        router.delete(route('admin.rent-posts.destroy', props.post.id), {
            onSuccess: () => {
                router.visit(route('admin.rent-posts.index'));
            },
        });
    }
}

// Save property for later
function saveProperty() {
    alert('Property saved to your favorites!');
    // You can implement actual save functionality here
}

// Share property
function shareProperty() {
    if (navigator.share) {
        navigator.share({
            title: props.post.title,
            text: `Check out this property: ${props.post.title} - ${props.post.formatted_rent}/month`,
            url: window.location.href,
        });
    } else {
        navigator.clipboard.writeText(window.location.href);
        alert('Link copied to clipboard!');
    }
}
</script>
