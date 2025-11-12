<script setup lang="ts">
import Header from '@/components/public/Header.vue';
import Footer from '@/components/public/Footer.vue';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]"
    >
        <!-- Header -->
        <Header />

        <!-- Main slot (the page content) -->
        <div
            class="flex w-full items-center justify-center opacity-100 transition-opacity duration-750"
        >
            <main
                class="flex w-full max-w-[335px] flex-col-reverse overflow-hidden rounded-lg lg:max-w-4xl lg:flex-row"
            >
                <slot />
            </main>
        </div>

        <!-- Footer -->
        <Footer />

        <!-- optional spacing -->
        <!-- <div class="hidden h-14.5 lg:block"></div> -->
    </div>
</template>
