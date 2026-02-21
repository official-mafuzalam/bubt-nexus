<script setup lang="ts">
import { useSettings } from '@/composables/useSettings';
import MainLayout from '@/layouts/PublicLayout.vue';
import { Head } from '@inertiajs/vue3';

const { setting } = useSettings();

const envSiteName = import.meta.env.VITE_APP_NAME || 'My App';
const siteName = setting('site_name', envSiteName);
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <MainLayout :padded="true">
        <div class="min-h-screen bg-gray-50 py-12 dark:bg-gray-900">
            <section class="w-full text-center">
                <h1 class="text-3xl font-bold text-blue-800 dark:text-blue-100">
                    Welcome to {{ siteName }}
                </h1>
            </section>
        </div>
    </MainLayout>
</template>
