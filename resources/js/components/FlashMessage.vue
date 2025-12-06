<!-- FlashMessage.vue -->
<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { AlertCircle, CheckCircle, Info, XCircle } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();

const flash = computed(() => page.props.flash);

const messageType = computed(() => {
    if (flash.value?.success) return 'success';
    if (flash.value?.error) return 'error';
    if (flash.value?.info) return 'info';
    if (flash.value?.warning) return 'warning';
    return null;
});

const messageText = computed(() => {
    if (flash.value?.success) return flash.value.success;
    if (flash.value?.error) return flash.value.error;
    if (flash.value?.info) return flash.value.info;
    if (flash.value?.warning) return flash.value.warning;
    return null;
});

const typeStyles = {
    success: 'bg-green-50 border-green-200 text-green-800',
    error: 'bg-red-50 border-red-200 text-red-800',
    info: 'bg-blue-50 border-blue-200 text-blue-800',
    warning: 'bg-yellow-50 border-yellow-200 text-yellow-800',
};

const typeIcons = {
    success: CheckCircle,
    error: XCircle,
    info: Info,
    warning: AlertCircle,
};
</script>

<template>
    <transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="transform translate-y-2 opacity-0"
        enter-to-class="transform translate-y-0 opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="transform translate-y-0 opacity-100"
        leave-to-class="transform translate-y-2 opacity-0"
    >
        <div
            v-if="messageType && messageText"
            :class="[
                'fixed top-4 right-4 z-50 w-full max-w-md rounded-lg border p-4 shadow-lg',
                typeStyles[messageType],
            ]"
            role="alert"
        >
            <div class="flex items-start gap-3">
                <component
                    :is="typeIcons[messageType]"
                    class="mt-0.5 h-5 w-5 flex-shrink-0"
                />
                <div class="flex-1">
                    <p class="text-sm font-medium">
                        {{ messageText }}
                    </p>
                </div>
                <button
                    @click="page.props.flash = {}"
                    class="ml-4 text-gray-400 transition-colors hover:text-gray-600"
                >
                    <span class="sr-only">Close</span>
                    <svg
                        class="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>
        </div>
    </transition>
</template>
