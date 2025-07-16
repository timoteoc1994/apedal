<template>
    <Link class="block px-4 py-2 text-sm  hover:bg-indigo-600  text-black">
        <slot />
    </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>
