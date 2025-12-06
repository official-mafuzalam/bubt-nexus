<template></template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { toast } from 'vue3-toastify';

const page = usePage();

watch(
    () => page.props.flash,
    (flash: any) => {
        const success = typeof flash.success === 'function' ? flash.success() : flash.success;
        const error   = typeof flash.error === 'function' ? flash.error() : flash.error;
        const warning = typeof flash.warning === 'function' ? flash.warning() : flash.warning;
        const info    = typeof flash.info === 'function' ? flash.info() : flash.info;

        if (success) toast.success(success);
        if (error) toast.error(error);
        if (warning) toast.warning(warning);
        if (info) toast.info(info);
    },
    { deep: true, immediate: true },
);
</script>
