<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            Dashboard
        </template>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-6">
               
                <div class="bg-gray-100 p-4 rounded-lg shadow">
                    <img src="/storage/images_plataforma/reciclador-icono.png" alt="Solicitudes" class="w-16 h-16 mx-auto mb-4">
                    <h1 class="text-5xl font-semibold text-center">{{ recicladoresCount }}</h1>
                    <p class="text-center">Recicladores </p>
                </div>
                
                 <Link :href="route('asociation.index')">
                <div class="bg-gray-100 p-4 rounded-lg shadow">
                    <img src="/storage/images_plataforma/icono_asociacion.png" alt="Solicitudes" class="w-16 h-16 mx-auto mb-4">
                    <h1 class="text-5xl font-semibold text-center">{{ asociacionesCount }}</h1>
                    <p class="text-center">Asociaciones</p>
                </div>
                </Link>
                <Link :href="route('ciudadano.index')">
                <div class="bg-gray-100 p-4 rounded-lg shadow">
                    <img src="/storage/images_plataforma/icono_ciudadano.png" alt="Solicitudes" class="w-16 h-16 mx-auto mb-4">
                    <h1 class="text-5xl font-semibold text-center">{{ ciudadanosCount }}</h1>
                    <p class="text-center">Ciudadanos</p>
                </div>
                </Link>
            </div>
            <div class="p-6 border-b border-gray-200">
                <canvas ref="barChart"></canvas>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
    solicitudes: Object,
    recicladoresCount: Number,
    asociacionesCount: Number,
    ciudadanosCount: Number
});
const barChart = ref(null);

onMounted(() => {
    if (barChart.value) {
        new Chart(barChart.value, {
            type: 'bar',
            data: {
                labels: props.solicitudes.fechas,
                datasets: [
                    {
                        label: 'Completado',
                        data: props.solicitudes.datos.completado,
                        backgroundColor: '#10B981',
                    },
                    {
                        label: 'Cancelado',
                        data: props.solicitudes.datos.cancelado,
                        backgroundColor: '#EF4444',
                    },
                    {
                        label: 'Buscando Reciclador',
                        data: props.solicitudes.datos.buscando_reciclador,
                        backgroundColor: '#F59E0B',
                    },
                    {
                        label: 'Pendiente',
                        data: props.solicitudes.datos.pendiente,
                        backgroundColor: '#6B7280',
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    title: { display: true, text: 'Ultimos 10 dias' }
                },
                scales: {
                    x: { stacked: true },
                    y: { beginAtZero: true, stacked: true }
                }
            }
        });
    }
});
</script>
