<template>
    <Link :href="href" :class="classes"  :style="props.active ? 'background-color: #ffb000; color: #fff;' : ''">
        <slot name="icon" />
        <span class="mx-3">
            <slot />
        </span>
    </Link>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps(['href', 'active']);

const classes = computed(() => props.active
    ? 'flex items-center  py-2 px-6 bg-gray-700 bg-opacity-25 text-gray-100'
    : 'flex items-center  py-2 px-6 text-gray-100'
);
</script>
