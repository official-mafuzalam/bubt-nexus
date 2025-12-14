<template>
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
                        {{
                            isMyPostsPage ? 'My Properties' : 'Rent Properties'
                        }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{
                            isMyPostsPage
                                ? 'Manage your rental properties'
                                : 'Find your perfect rental property'
                        }}
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
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

                    <!-- Switch between My Posts and All Posts -->
                    <Link
                        v-if="!isMyPostsPage && $page.props.auth.user"
                        :href="route('admin.rent-posts.my-posts')"
                        class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                    >
                        <Home class="h-4 w-4" />
                        My Properties
                    </Link>
                    <Link
                        v-else-if="isMyPostsPage"
                        :href="route('admin.rent-posts.index')"
                        class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                    >
                        <Globe class="h-4 w-4" />
                        All Properties
                    </Link>

                    <!-- Create New Post Button -->
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('admin.rent-posts.create')"
                        class="inline-flex items-center gap-2 rounded-md bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600 focus:ring-2 focus:ring-amber-400 focus:outline-none"
                    >
                        <Plus class="h-4 w-4" />
                        {{ isMyPostsPage ? 'Add Property' : 'Post Property' }}
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
                                Total Properties
                            </p>
                            <p
                                class="text-2xl font-bold text-gray-900 dark:text-gray-100"
                            >
                                {{ stats.total_posts }}
                            </p>
                        </div>
                        <div
                            class="rounded-lg bg-amber-100 p-2 dark:bg-amber-900"
                        >
                            <Home
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
                                Available Now
                            </p>
                            <p
                                class="text-2xl font-bold text-green-600 dark:text-green-400"
                            >
                                {{ stats.available_posts }}
                            </p>
                        </div>
                        <div
                            class="rounded-lg bg-green-100 p-2 dark:bg-green-900"
                        >
                            <CheckCircle
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
                                Average Rent
                            </p>
                            <p
                                class="text-2xl font-bold text-blue-600 dark:text-blue-400"
                            >
                                ৳{{ stats.average_rent.toLocaleString() }}
                            </p>
                        </div>
                        <div
                            class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900"
                        >
                            <DollarSign
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
                                Categories
                            </p>
                            <p
                                class="text-2xl font-bold text-purple-600 dark:text-purple-400"
                            >
                                {{ stats.total_categories }}
                            </p>
                        </div>
                        <div
                            class="rounded-lg bg-purple-100 p-2 dark:bg-purple-900"
                        >
                            <Layers
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
                        Filter Properties
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
                    <!-- Category Filter -->
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Property Type
                        </label>
                        <select
                            v-model="localFilters.category"
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                        >
                            <option value="">All Types</option>
                            <option
                                v-for="category in filterOptions.categories"
                                :key="category"
                                :value="category"
                            >
                                {{ formatCategory(category) }}
                            </option>
                        </select>
                    </div>

                    <!-- Location Search -->
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Location
                        </label>
                        <input
                            v-model="localFilters.location"
                            type="text"
                            placeholder="Enter location..."
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                        />
                    </div>

                    <!-- Bedrooms Filter -->
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Bedrooms
                        </label>
                        <select
                            v-model="localFilters.bedrooms"
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                        >
                            <option value="">Any</option>
                            <option
                                v-for="num in filterOptions.bedrooms"
                                :key="num"
                                :value="num"
                            >
                                {{ num }}
                                {{ num === 1 ? 'Bedroom' : 'Bedrooms' }}
                            </option>
                        </select>
                    </div>

                    <!-- Availability Filter -->
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Availability
                        </label>
                        <select
                            v-model="localFilters.is_available"
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                        >
                            <option value="">All</option>
                            <option value="true">Available Now</option>
                            <option value="false">Not Available</option>
                        </select>
                    </div>

                    <!-- Rent Range -->
                    <div class="md:col-span-2">
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Rent Range: ৳{{ minRent }} - ৳{{ maxRent }}
                        </label>
                        <div class="flex items-center gap-4">
                            <input
                                v-model="minRent"
                                type="range"
                                :min="filterOptions.min_rent"
                                :max="filterOptions.max_rent"
                                step="1000"
                                class="flex-1"
                                @input="updateRentFilter"
                            />
                            <div class="flex gap-2">
                                <input
                                    v-model="localFilters.min_rent"
                                    type="number"
                                    placeholder="Min"
                                    class="w-24 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                />
                                <input
                                    v-model="localFilters.max_rent"
                                    type="number"
                                    placeholder="Max"
                                    class="w-24 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Properties Grid -->
            <div
                v-if="rentPosts?.data?.length"
                class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            >
                <RentPostCard
                    v-for="post in rentPosts.data"
                    :key="post.id"
                    :post="post"
                    :is-owner="
                        isMyPostsPage ||
                        post.user_id === $page.props.auth.user?.id
                    "
                    @toggle-availability="toggleAvailability(post.id)"
                />
            </div>

            <!-- Empty State -->
            <div
                v-else
                class="rounded-xl border border-sidebar-border/70 py-16 text-center dark:border-sidebar-border"
            >
                <div
                    class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400"
                >
                    <Home class="mb-4 h-16 w-16 opacity-50" />
                    <p class="mb-2 text-lg font-medium">
                        {{
                            isMyPostsPage
                                ? 'No properties found'
                                : 'No properties found'
                        }}
                    </p>
                    <p class="mb-4 text-sm">
                        {{
                            isMyPostsPage
                                ? "You haven't posted any properties yet."
                                : 'Try adjusting your filters.'
                        }}
                    </p>
                    <Link
                        v-if="$page.props.auth.user && isMyPostsPage"
                        :href="route('admin.rent-posts.create')"
                        class="inline-flex items-center gap-2 rounded-md bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600"
                    >
                        <Plus class="h-4 w-4" />
                        Add Your First Property
                    </Link>
                    <div
                        v-else-if="!isMyPostsPage"
                        class="flex flex-col gap-2 sm:flex-row"
                    >
                        <button
                            @click="resetFilters"
                            class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                        >
                            <Filter class="h-4 w-4" />
                            Clear Filters
                        </button>
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('admin.rent-posts.create')"
                            class="inline-flex items-center gap-2 rounded-md bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600"
                        >
                            <Plus class="h-4 w-4" />
                            Post a Property
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div
                v-if="(rentPosts?.data?.length ?? 0) > 0 && rentPosts?.meta"
                class="flex items-center justify-between"
            >
                <div class="text-sm text-gray-700 dark:text-gray-300">
                    Showing {{ rentPosts.meta.from ?? 0 }} to
                    {{ rentPosts.meta.to ?? 0 }} of
                    {{ rentPosts.meta.total ?? 0 }} results
                </div>
                <div class="flex gap-1">
                    <Link
                        v-for="link in rentPosts.links || []"
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

<script setup lang="ts">
import RentPostCard from '@/components/RentPostCard.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import {
    CheckCircle,
    ChevronDown,
    DollarSign,
    Filter,
    Globe,
    Home,
    Layers,
    Plus,
    X,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { route } from 'ziggy-js';

// Props
const props = defineProps<{
    rentPosts?: {
        data: any[];
        links: any[];
        meta: any;
    };
    myPosts?: any[];
    filters: {
        search: string | null;
        category: string | null;
        min_rent: number | null;
        max_rent: number | null;
        bedrooms: number | null;
        is_available: string | null;
        location: string | null;
    };
    filterOptions: {
        categories: string[];
        bedrooms: number[];
        max_rent: number;
        min_rent: number;
    };
    stats: {
        total_posts: number;
        available_posts: number;
        average_rent: number;
        total_categories: number;
    };
    isMyPostsPage?: boolean;
}>();

// Use appropriate breadcrumbs based on page type
const breadcrumbs = computed((): BreadcrumbItem[] => {
    const base: BreadcrumbItem[] = [
        { title: 'Dashboard', href: dashboard().url },
    ];

    if (props.isMyPostsPage) {
        base.push(
            { title: 'Rent Management', href: route('admin.rent-posts.index') },
            {
                title: 'My Properties',
                href: route('admin.rent-posts.my-posts'),
            },
        );
    } else {
        base.push({
            title: 'Rent Properties',
            href: route('admin.rent-posts.index'),
        });
    }

    return base;
});

// Local filters state
const localFilters = ref({
    search: props.filters?.search ?? '',
    category: props.filters?.category ?? '',
    min_rent: props.filters?.min_rent ?? null,
    max_rent: props.filters?.max_rent ?? null,
    bedrooms: props.filters?.bedrooms ?? null,
    is_available: props.filters?.is_available ?? '',
    location: props.filters?.location ?? '',
});

const showFilters = ref(false);
const minRent = ref(props.filterOptions?.min_rent || 0);
const maxRent = ref(props.filterOptions?.max_rent || 100000);

// Apply filters based on current page
const applyFilters = () => {
    const routeName = props.isMyPostsPage
        ? 'admin.rent-posts.my-posts'
        : 'admin.rent-posts.index';

    router.get(route(routeName), localFilters.value, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

// Reset filters
const resetFilters = () => {
    localFilters.value = {
        search: '',
        category: '',
        min_rent: null,
        max_rent: null,
        bedrooms: null,
        is_available: '',
        location: '',
    };
    minRent.value = props.filterOptions?.min_rent || 0;
    maxRent.value = props.filterOptions?.max_rent || 100000;
    applyFilters();
};

// Update rent range from slider
const updateRentFilter = () => {
    localFilters.value.min_rent = minRent.value;
    localFilters.value.max_rent = maxRent.value;
};

// Watch for filter changes with debounce
let filterTimeout: NodeJS.Timeout;
watch(
    localFilters,
    () => {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(() => {
            applyFilters();
        }, 500);
    },
    { deep: true, immediate: false },
);

// Format category
function formatCategory(category: string) {
    return category.replace('_', ' ').replace(/\b\w/g, (l) => l.toUpperCase());
}

// Toggle availability
function toggleAvailability(postId: number) {
    router.patch(
        route('admin.rent-posts.toggle-availability', postId),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                // Reload the current page
                router.reload({ only: ['rentPosts', 'stats'] });
            },
            onError: (errors) => {
                console.error('Toggle error:', errors);
                alert('Failed to toggle availability. Please try again.');
            },
        },
    );
}
</script>
