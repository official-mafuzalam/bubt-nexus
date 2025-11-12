<script setup lang="ts">
import { useSettings } from '@/composables/useSettings';
import { Facebook, Github, Linkedin, Twitter, Youtube, Mail } from 'lucide-vue-next';

const { setting } = useSettings();

const envSiteName = import.meta.env.VITE_APP_NAME || 'My App';
const siteName = setting('site_name', envSiteName);
const currentYear = new Date().getFullYear();
</script>

<template>
    <footer
        class="border-t border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800"
    >
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div
                class="flex flex-col items-center justify-between gap-4 md:flex-row"
            >
                <!-- Copyright -->
                <div class="text-center md:text-left">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        &copy; {{ currentYear }} {{ siteName }}. All rights
                        reserved.
                    </p>
                </div>

                <!-- Additional Info -->
                <!-- <div class="text-center md:text-right">
                    <p class="text-xs text-gray-500 dark:text-gray-500">
                        Crafted with
                        <span class="mx-1 text-red-500">❤️</span>
                        using Laravel, Inertia.js & Vue 3
                    </p>
                </div> -->
            </div>

            <!-- Optional: Footer Links -->
            <div class="mt-6 flex justify-center space-x-6">
                <a
                    href="#"
                    class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                >
                    Privacy Policy
                </a>
                <a
                    href="#"
                    class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                >
                    Terms of Service
                </a>
                <a
                    href="#"
                    class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                >
                    Contact
                </a>
            </div>

            <!-- Optional: Social Links -->
            <div class="mt-4 flex justify-center space-x-4">
                <a
                    v-if="setting('facebook_url')"
                    :href="setting('facebook_url')"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
                >
                    <span class="sr-only">Facebook</span>
                    <!-- Facebook Icon -->
                    <Facebook class="h-5 w-5" />
                </a>
                <a
                    v-if="setting('twitter_url')"
                    :href="setting('twitter_url')"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
                >
                    <span class="sr-only">Twitter</span>
                    <!-- Twitter Icon -->
                    <Twitter class="h-5 w-5" />
                </a>
                <a
                    v-if="setting('github_url')"
                    :href="setting('github_url')"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
                >
                    <span class="sr-only">GitHub</span>
                    <!-- GitHub Icon -->
                    <Github class="h-5 w-5" />
                </a>
                <a
                    v-if="setting('linkedin_url')"
                    :href="setting('linkedin_url')"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
                >
                    <span class="sr-only">LinkedIn</span>
                    <!-- LinkedIn Icon -->
                    <Linkedin class="h-5 w-5" />
                </a>
                <a
                    v-if="setting('youtube_url')"
                    :href="setting('youtube_url')"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
                >
                    <span class="sr-only">Youtube</span>
                    <!-- Youtube Icon -->
                    <Youtube class="h-5 w-5" />
                </a>
                <a
                    v-if="setting('site_email')"
                    :href="`mailto:${setting('site_email')}`"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
                >
                    <span class="sr-only">Email</span>
                    <!-- Email Icon -->
                    <Mail class="h-5 w-5" />
                </a>
            </div>
        </div>
    </footer>
</template>
