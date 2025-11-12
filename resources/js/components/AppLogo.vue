<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { useSettings } from '@/composables/useSettings';

const { setting } = useSettings();

const envSiteName = import.meta.env.VITE_APP_NAME || 'My App';
const siteName = setting('site_name', envSiteName);
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground"
    >
        <AppLogoIcon class="size-8 fill-current text-white dark:text-black" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold">
            {{ siteName }}
        </span>
    </div>
</template>
