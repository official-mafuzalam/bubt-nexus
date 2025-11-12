<!-- GlobalToast.vue -->
<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { toast } from 'vue3-toastify';

const page = usePage();

watch(
    () => page.props.flash,
    (flash: any) => {
        if (flash?.success) toast.success(flash.success);
        if (flash?.error) toast.error(flash.error);
        if (flash?.info) toast.info(flash.info);
    },
    { deep: true, immediate: true },
);
</script>
