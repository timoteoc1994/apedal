<script setup lang="ts">
import Input from '@/Components/ui/input/Input.vue';
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
    class?: HTMLAttributes['class'];
}>();
</script>

<template>
    <Input data-sidebar="input" :class="cn('h-8 w-full bg-background shadow-none focus-visible:ring-2 focus-visible:ring-sidebar-ring', props.class)">
        <slot />
    </Input>
</template>
