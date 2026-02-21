<script setup lang="ts">
import Footer from '@/components/public/Footer.vue';
import Header from '@/components/public/Header.vue';

defineProps<{
    padded?: boolean; // Control padding from parent components
}>();
</script>

<template>
    <div class="flex min-h-screen flex-col bg-[#FDFDFC] dark:bg-[#0a0a0a]">
        <!-- Header -->
        <Header />

        <!-- Main content with optional padding -->
        <main
            class="w-full flex-1"
            :class="{ 'px-6 py-8 lg:px-8 lg:py-12': padded }"
        >
            <slot />
        </main>

        <!-- Footer -->
        <Footer />
    </div>
</template>
