<script lang="ts" setup>
import { cn } from '@/lib/utils';
import { Primitive, type PrimitiveProps } from 'radix-vue';
import type { HTMLAttributes } from 'vue';

const props = withDefaults(defineProps<PrimitiveProps & { class?: HTMLAttributes['class'] }>(), {
    as: 'a',
});
</script>

<template>
    <Primitive :as="as" :as-child="asChild" :class="cn('transition-colors hover:text-foreground text-white', props.class)">
        <slot />
    </Primitive>
</template>
