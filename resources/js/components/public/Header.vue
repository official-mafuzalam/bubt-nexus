<script setup lang="ts">
import {
    NavigationMenu,
    NavigationMenuContent,
    NavigationMenuItem,
    NavigationMenuLink,
    NavigationMenuList,
    NavigationMenuTrigger,
    navigationMenuTriggerStyle,
} from '@/components/ui/navigation-menu';
import { useSettings } from '@/composables/useSettings';
import { home } from '@/routes';
import { Link, usePage } from '@inertiajs/vue3';
import AppLogoIcon from '../AppLogoIcon.vue';

const { setting } = useSettings();

const appName = setting('site_name', import.meta.env.VITE_APP_NAME || 'My App');
const page = usePage();

const components: { title: string; href: string; description: string }[] = [
    {
        title: 'Alert Dialog',
        href: '/docs/components/alert-dialog',
        description:
            'A modal dialog that interrupts the user with important content and expects a response.',
    },
    {
        title: 'Hover Card',
        href: '/docs/components/hover-card',
        description:
            'For sighted users to preview content available behind a link.',
    },
    {
        title: 'Progress',
        href: '/docs/components/progress',
        description:
            'Displays an indicator showing the completion progress of a task.',
    },
    {
        title: 'Scroll-area',
        href: '/docs/components/scroll-area',
        description: 'Visually or semantically separates content.',
    },
    {
        title: 'Tabs',
        href: '/docs/components/tabs',
        description:
            'A set of layered sections of content displayed one at a time.',
    },
    {
        title: 'Tooltip',
        href: '/docs/components/tooltip',
        description:
            'A popup that displays information related to an element when hovered.',
    },
];

// route helpers
const dashboard = () => '/admin-dashboard';
const login = () => '/login';
const register = () => '/register';
</script>

<template>
    <header
        class="fixed top-0 left-0 z-50 w-full border-b border-gray-200 bg-white/90 shadow-sm backdrop-blur-md dark:border-gray-800 dark:bg-gray-900/90"
    >
        <nav
            class="mx-auto flex max-w-7xl items-center justify-between px-6 py-3 lg:px-8"
        >
            <!-- Brand / Logo -->
            <div class="flex items-center space-x-2">
                <AppLogoIcon class="mr-2 size-8 fill-current text-amber-500" />
                <Link
                    :href="home()"
                    class="text-lg font-semibold text-gray-800 dark:text-gray-100"
                >
                    {{ appName }}
                </Link>
            </div>

            <!-- Nav Links -->
            <NavigationMenu>
                <NavigationMenuList>
                    <!-- Centered Dropdown Menu -->
                    <NavigationMenuItem class="relative">
                        <NavigationMenuTrigger
                            class="bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-800"
                            >Components</NavigationMenuTrigger
                        >
                        <NavigationMenuContent
                            class="absolute left-1/2 z-50 mt-2 w-[400px] -translate-x-1/2 rounded-lg border border-gray-200 bg-white shadow-lg md:w-[500px] lg:w-[600px] dark:border-gray-700 dark:bg-gray-800"
                        >
                            <ul class="grid gap-3 p-4 md:grid-cols-2">
                                <li
                                    v-for="component in components"
                                    :key="component.title"
                                >
                                    <NavigationMenuLink as-child>
                                        <Link
                                            :href="component.href"
                                            class="block space-y-1 rounded-md p-3 leading-none no-underline transition-colors outline-none select-none hover:bg-amber-50 hover:text-amber-700 focus:bg-amber-50 focus:text-amber-700 dark:hover:bg-gray-700 dark:focus:bg-gray-700"
                                        >
                                            <div class="text-sm font-medium">
                                                {{ component.title }}
                                            </div>
                                            <p
                                                class="line-clamp-2 text-sm text-muted-foreground"
                                            >
                                                {{ component.description }}
                                            </p>
                                        </Link>
                                    </NavigationMenuLink>
                                </li>
                            </ul>
                        </NavigationMenuContent>
                    </NavigationMenuItem>

                    <NavigationMenuItem className="hidden md:block">
                        <NavigationMenuTrigger
                            class="bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-800"
                            >Simple</NavigationMenuTrigger
                        >
                        <NavigationMenuContent>
                            <ul className="grid w-[200px] gap-4">
                                <li>
                                    <NavigationMenuLink asChild>
                                        <Link href="#">Components</Link>
                                    </NavigationMenuLink>
                                    <NavigationMenuLink asChild>
                                        <Link href="#">Documentation</Link>
                                    </NavigationMenuLink>
                                    <NavigationMenuLink asChild>
                                        <Link href="#">Blocks</Link>
                                    </NavigationMenuLink>
                                </li>
                            </ul>
                        </NavigationMenuContent>
                    </NavigationMenuItem>

                    <NavigationMenuItem
                        class="bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-800"
                    >
                        <NavigationMenuLink
                            href="/docs/introduction"
                            :class="navigationMenuTriggerStyle()"
                        >
                            Documentation
                        </NavigationMenuLink>
                    </NavigationMenuItem>

                    <!-- Auth buttons -->
                    <Link
                        v-if="page.props.auth && page.props.auth.user"
                        :href="dashboard()"
                        class="rounded-md bg-amber-500 px-5 py-2 text-sm font-medium text-white hover:bg-amber-600 focus:ring-2 focus:ring-amber-400 focus:outline-none dark:bg-amber-600 dark:hover:bg-amber-500"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="rounded-md px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800"
                        >
                            Log in
                        </Link>
                        <Link
                            :href="register()"
                            class="rounded-md border border-amber-500 px-5 py-2 text-sm font-medium text-amber-600 hover:bg-amber-50 dark:border-amber-400 dark:text-amber-400 dark:hover:bg-gray-800"
                        >
                            Register
                        </Link>
                    </template>
                </NavigationMenuList>
            </NavigationMenu>
        </nav>
    </header>
</template>
