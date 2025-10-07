

<style>
@import 'leaflet/dist/leaflet.css';
@import 'leaflet-draw/dist/leaflet.draw.css';

.mapa-fullscreen {
    position: relative;
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
    left: 20px;
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
    <Head title="Mapa de Zonas" />

    <AuthenticatedLayout>
        <template #header>
            <Link class="text-indigo-600 hover:text-indigo-500" :href="route('zonas.index')">Zonas/</Link> Mapa de Zonas
        </template>
                <div class="relative w-full" style="height: calc(100vh - 140px); min-height: 600px;">
                <MapaZonas 
                    :zonas="zonas" 
                    :asociaciones="asociaciones" 
                    class="mapa-fullscreen"
                    fullscreen-mode
                />
            </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3';
import MapaZonas from '@/Components/MapaZonas.vue';


// Definir props
const props = defineProps({
    zonas: Array,
    asociaciones: Array,
});


</script>