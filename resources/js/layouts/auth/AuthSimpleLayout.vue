<script setup lang="ts">
import AppLogoIcon from '@/Components/AppLogoIcon.vue';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10">
        <div class="w-full max-w-sm bg-gradient-to-b from-[#6A69FD] to-[#594AE3] p-8 rounded-3xl text-white">
            <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-4">
                    <Link :href="route('home')" class="flex flex-col items-center gap-2 font-medium">
                    <div class="mb-6 flex h-9 w-9 items-center justify-center rounded-md">
                        <img src="/storage/images/logoLogin.png" alt="Adri"
                            class="w-48 h-auto object-cover absolute z-0"/>
                    </div>
                    <div class="flex gap-1 mt-4 justify-center">
                    <div class="w-3 h-3 rounded-full bg-white"></div>
                    <div class="w-3 h-3 rounded-full bg-[#FFB000]"></div>
                    <div class="w-3 h-3 rounded-full bg-[#55F094]"></div>
                    <div class="w-3 h-3 rounded-full bg-[#FB6185]"></div>
                </div>
                    <span class="sr-only mt-4">{{ title }}</span>
                    </Link>
                    <div class="space-y-4 text-center">
                        <h1 class="text-3xl font-extrabold">{{ title }}</h1>
                        <p class="text-center text-md text-muted-foreground text-white font-semibold">{{ description }}</p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
