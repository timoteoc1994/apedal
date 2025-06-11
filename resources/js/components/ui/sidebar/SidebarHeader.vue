<script setup lang="ts">
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
    class?: HTMLAttributes['class'];
}>();
</script>

<template>
    <div data-sidebar="header" :class="cn('flex flex-col gap-2 p-2 bg-[#5849E2] text-[#E9E7FF]', props.class)">
        <slot />
    </div>
</template>
