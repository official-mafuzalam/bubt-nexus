<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

const { className } = defineProps<Props>();

const logoSrc = '/assets/icon.png';

</script>

<template>
    <img
        :src="logoSrc"
        :class="className"
        v-bind="$attrs"
        alt="App logo"
    />
</template>
