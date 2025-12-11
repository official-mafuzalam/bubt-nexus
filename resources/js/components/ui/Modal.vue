<template>
    <teleport to="body">
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black/50" @click="emit('close')"></div>

            <!-- Modal Container -->
            <div class="flex min-h-full items-center justify-center p-4">
                <!-- Modal Content -->
                <div
                    ref="modalRef"
                    class="relative w-full max-w-md transform overflow-hidden rounded-xl bg-white shadow-xl transition-all dark:bg-gray-800"
                    :class="maxWidthClass"
                    @click.stop
                >
                    <!-- Close Button -->
                    <button
                        v-if="showClose"
                        @click="emit('close')"
                        class="absolute right-4 top-4 text-gray-400 hover:text-gray-500"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <slot />
                </div>
            </div>
        </div>
    </teleport>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue';

interface Props {
    show?: boolean;
    maxWidth?: 'sm' | 'md' | 'lg' | 'xl' | '2xl' | 'full';
    closeable?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    show: true,
    maxWidth: 'md',
    closeable: true,
});

const emit = defineEmits<{
    close: [];
}>();

const modalRef = ref<HTMLElement>();

const maxWidthClass = {
    'sm': 'max-w-sm',
    'md': 'max-w-md',
    'lg': 'max-w-lg',
    'xl': 'max-w-xl',
    '2xl': 'max-w-2xl',
    'full': 'max-w-full',
}[props.maxWidth];

const showClose = props.closeable;

// Close modal on Escape key
const closeOnEscape = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && props.show && props.closeable) {
        emit('close');
    }
};

// Close modal on backdrop click
const closeOnBackdrop = (e: MouseEvent) => {
    if (props.show && props.closeable && modalRef.value && !modalRef.value.contains(e.target as Node)) {
        emit('close');
    }
};

onMounted(() => {
    document.addEventListener('keydown', closeOnEscape);
    document.addEventListener('click', closeOnBackdrop);
    document.body.style.overflow = 'hidden';
});

onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);
    document.removeEventListener('click', closeOnBackdrop);
    document.body.style.overflow = '';
});

watch(
    () => props.show,
    (show) => {
        if (show) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    }
);
</script>

<style scoped>
/* Fade in animation */
:deep(.modal-enter-active),
:deep(.modal-leave-active) {
    transition: opacity 0.2s ease;
}

:deep(.modal-enter-from),
:deep(.modal-leave-to) {
    opacity: 0;
}
</style>