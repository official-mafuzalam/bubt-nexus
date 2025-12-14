<template>
    <div
        class="group relative overflow-hidden rounded-xl border border-gray-200 bg-white transition-all hover:shadow-lg dark:border-gray-700 dark:bg-gray-800"
    >
        <!-- Availability Badge -->
        <div class="absolute top-4 right-4 z-10">
            <span
                :class="[
                    'rounded-full px-3 py-1 text-xs font-semibold',
                    post.is_available
                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                        : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                ]"
            >
                {{ post.is_available ? 'Available' : 'Not Available' }}
            </span>
        </div>

        <!-- Image/Placeholder -->
        <div class="relative h-48 w-full bg-gray-200 dark:bg-gray-700">
            <div class="flex h-full items-center justify-center">
                <Home class="h-16 w-16 text-gray-400" />
            </div>
            <!-- Category Badge -->
            <span
                class="absolute top-4 left-4 rounded-md bg-amber-500 px-2 py-1 text-xs font-semibold text-white"
            >
                {{ post.category_label }}
            </span>
        </div>

        <!-- Card Content -->
        <div class="p-5">
            <!-- Title -->
            <h3
                class="mb-2 truncate text-lg font-semibold text-gray-900 group-hover:text-amber-600 dark:text-gray-100"
            >
                {{ post.title }}
            </h3>

            <!-- Location -->
            <div
                class="mb-3 flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400"
            >
                <MapPin class="h-4 w-4 flex-shrink-0" />
                <span class="truncate">{{ post.location }}</span>
            </div>

            <!-- Property Details -->
            <div class="mb-4 grid grid-cols-2 gap-3">
                <div class="flex items-center gap-2">
                    <BedDouble class="h-4 w-4 text-gray-400" />
                    <span class="text-sm text-gray-700 dark:text-gray-300">
                        {{ post.bedrooms || 'N/A' }} Bed
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <Bath class="h-4 w-4 text-gray-400" />
                    <span class="text-sm text-gray-700 dark:text-gray-300">
                        {{ post.washrooms || 'N/A' }} Bath
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <Calendar class="h-4 w-4 text-gray-400" />
                    <span
                        class="text-sm text-gray-700 dark:text-gray-300"
                        :title="post.available_from"
                    >
                        {{ post.available_from_formatted || 'Flexible' }}
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <User class="h-4 w-4 text-gray-400" />
                    <span
                        class="truncate text-sm text-gray-700 dark:text-gray-300"
                        :title="post.user?.name"
                    >
                        {{ post.user?.name || 'Owner' }}
                    </span>
                </div>
            </div>

            <!-- Rent -->
            <div
                class="mb-4 border-t border-gray-100 pt-4 dark:border-gray-700"
            >
                <div class="text-center">
                    <p
                        class="text-2xl font-bold text-gray-900 dark:text-gray-100"
                    >
                        {{ post.formatted_rent }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        per month
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between">
                <Link
                    :href="route('admin.rent-posts.show', post.id)"
                    class="inline-flex items-center gap-2 text-sm font-medium text-amber-600 hover:text-amber-700 dark:text-amber-400"
                >
                    <Eye class="h-4 w-4" />
                    View Details
                </Link>

                <div v-if="isOwner" class="flex items-center gap-2">
                    <button
                        @click="$emit('toggle-availability', post.id)"
                        :class="[
                            'rounded p-1.5',
                            post.is_available
                                ? 'text-red-500 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20'
                                : 'text-green-500 hover:bg-green-50 hover:text-green-600 dark:hover:bg-green-900/20',
                        ]"
                        :title="
                            post.is_available
                                ? 'Mark as Unavailable'
                                : 'Mark as Available'
                        "
                    >
                        <component
                            :is="post.is_available ? XCircle : CheckCircle"
                            class="h-4 w-4"
                        />
                    </button>
                    <Link
                        :href="route('admin.rent-posts.edit', post.id)"
                        class="rounded p-1.5 text-blue-500 hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-900/20"
                        title="Edit"
                    >
                        <Edit class="h-4 w-4" />
                    </Link>
                    <button
                        @click="deletePost(post.id)"
                        class="rounded p-1.5 text-red-500 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20"
                        title="Delete"
                    >
                        <Trash class="h-4 w-4" />
                    </button>
                </div>

                <div v-else class="flex items-center gap-2">
                    <button
                        @click="copyContact(post.contact_number)"
                        class="rounded p-1.5 text-green-500 hover:bg-green-50 hover:text-green-600 dark:hover:bg-green-900/20"
                        title="Copy Contact"
                    >
                        <Phone class="h-4 w-4" />
                    </button>
                    <a
                        :href="`tel:${post.contact_number}`"
                        class="rounded p-1.5 text-blue-500 hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-900/20"
                        title="Call"
                    >
                        <PhoneCall class="h-4 w-4" />
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import {
    Bath,
    BedDouble,
    Calendar,
    CheckCircle,
    Edit,
    Eye,
    Home,
    MapPin,
    Phone,
    PhoneCall,
    Trash,
    User,
    XCircle,
} from 'lucide-vue-next';
import { route } from 'ziggy-js';

defineProps<{
    post: any;
    isOwner: boolean;
}>();

defineEmits<{
    'toggle-availability': [id: number];
}>();

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

// Delete post
function deletePost(postId: number) {
    if (
        confirm(
            'Are you sure you want to delete this property post? This action cannot be undone.',
        )
    ) {
        router.delete(route('rent-posts.destroy', postId), {
            preserveScroll: true,
        });
    }
}
</script>
