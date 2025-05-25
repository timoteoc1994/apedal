<script setup lang="ts">
import MapaZonas from '@/components/MapaZonas.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

// Definir props
const props = defineProps({
    zonas: Array,
    asociaciones: Array,
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];
</script>

<style>
@import 'leaflet/dist/leaflet.css';
@import 'leaflet-draw/dist/leaflet.draw.css';

.mapa-fullscreen {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    height: 100%;
    width: 100%;
    z-index: 5;
}

.controls-overlay {
    position: absolute;
    top: 20px;
    right: 20px;
    z-index: 1000;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    padding: 12px;
    max-width: 320px;
}

.legend-overlay {
    position: absolute;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    padding: 12px;
    max-width: 320px;
    max-height: 300px;
    overflow-y: auto;
}

.form-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 1010;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    padding: 20px;
    width: 90%;
    max-width: 500px;
}

.overlay-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1005;
}
</style>

<template>
    <Head title="Zonas por Asociación" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="relative h-full w-full" style="height: calc(100vh - 64px);">
            <!-- Mapa a pantalla completa -->
            <MapaZonas 
                :zonas="zonas" 
                :asociaciones="asociaciones" 
                class="mapa-fullscreen"
                fullscreen-mode
            />
        </div>
    </AppLayout>
</template>