<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps({
    links: Array,
});
</script>

<template>
    <div v-if="links.length > 3">
        <div class="-mb-1 flex flex-wrap">
            <template v-for="(link, key) in links" :key="key">
                <div
                    v-if="link.url === null"
                    v-html="link.label"
                    class="mr-1 mb-1 rounded border px-4 py-3 text-sm leading-4 text-gray-400"
                />
                <Link
                    v-else
                    :href="link.url"
                    v-html="link.label"
                    class="mr-1 mb-1 rounded border px-4 py-3 text-sm leading-4 hover:bg-white focus:border-indigo-500 focus:text-indigo-500"
                    :class="{ 'bg-blue-500 text-white': link.active }"
                />
            </template>
        </div>
    </div>
</template>
