<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
      <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR4MPGhOeemwM5TS3WGi-QTN9fFv3thD0Gj3w&s" alt="">
</template>
