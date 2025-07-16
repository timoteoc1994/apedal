<script setup lang="ts">
import AppContent from '@/Components/AppContent.vue';
import AppShell from '@/Components/AppShell.vue';
import AppSidebar from '@/Components/AppSidebar.vue';
import AppSidebarHeader from '@/Components/AppSidebarHeader.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>
    </AppShell>
</template>
