<script setup lang="ts">
import FlashMessage from '@/components/FlashMessage.vue';
import AuthCardLayout from '@/layouts/auth/AuthCardLayout.vue';
import AuthSimpleLayout from '@/layouts/auth/AuthSimpleLayout.vue';

const props = defineProps<{
    title?: string;
    description?: string;
    type?: 'login' | 'register';
}>();

// Select the correct layout based on `type`
const AuthLayout = props.type === 'login' ? AuthCardLayout : AuthSimpleLayout;
</script>

<template>
    <AuthLayout :title="title" :description="description">
        <slot />
        <FlashMessage />
    </AuthLayout>
</template>
