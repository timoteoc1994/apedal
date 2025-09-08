<template>
    <Head title="Estadistica Solicitudes" />
    <AuthenticatedLayout :auth="$page.props.auth" :errors="$page.props.errors">
        <template #header> Estadistica Solicitudes </template>

        <div class="mt-6 rounded-xl bg-white p-8 shadow-xl">
            <form @submit.prevent="buscarMatriz" class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-5">
                <!-- Select de ciudades (múltiple) -->
                 <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Ciudad</label>
                    <select multiple v-model="ciudadSeleccionada" class="w-full rounded border px-3 py-2" size="4">
                        <option v-for="c in ciudades" :key="c.id" :value="c.name">{{ c.name }}</option>
                    </select>
                </div>
                 <!-- Select de asociación (múltiple) -->
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Asociación</label>
                    <select multiple v-model="asociacionSeleccionada" class="w-full rounded border px-3 py-2" size="4">
                        <option v-for="a in asociacion" :key="a.id" :value="a.id">{{ a.nombre }}</option>
                    </select>
                </div>
                <!-- Select de mes (múltiple) -->
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Mes</label>
                    <select multiple v-model="mesSeleccionado" class="w-full rounded border px-3 py-2" size="4">
                        <option v-for="(mes, idx) in meses" :key="idx" :value="mes">
                            {{ mes }}
                        </option>
                    </select>
                </div>
                <!-- Selección múltiple de años (hasta 6 años) -->
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Año</label>
                    <!-- Ajuste: tamaño menor y scroll para que no sea tan grande -->
                    <select multiple v-model="anioSeleccionado" class="w-full rounded border px-3 py-2 max-h-36 overflow-auto" size="4">
                        <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                    </select>
                </div>
                <!-- Botón buscar -->
                <div class="flex items-end">
                    <button type="submit" class="w-full rounded bg-blue-600 px-4 py-2 font-semibold text-white shadow hover:bg-blue-700">
                        Buscar
                    </button>
                </div>
            </form>

            <PrimaryButton @click="downloadExcel" :disabled="isDownloading">
                <template v-if="isDownloading"> <i class="fa-solid fa-spinner fa-spin"></i> Procesando... </template>
                <template v-else> <i class="fa-solid fa-file-excel"></i> Descargar xlm </template>
            </PrimaryButton>

            <!-- Tabla -->
            <div class="mt-4 overflow-x-auto">
                <table class="w-full min-w-[700px] text-left text-sm text-gray-700">
                    <thead class="bg-gradient-to-r from-indigo-500 to-purple-500 text-white">
                        <tr>
                            <th
                                v-for="header in headers"
                                :key="header.key"
                                @click="ordenarPor(header.key)"
                                class="cursor-pointer select-none px-6 py-4 font-semibold uppercase tracking-wider transition hover:bg-indigo-600"
                            >
                                <div class="flex items-center gap-1">
                                    {{ header.label }}
                                    <span v-if="sort === header.key">
                                        <ChevronUpIcon v-if="direction === 'asc'" class="inline h-4 w-4" />
                                        <ChevronDownIcon v-else class="inline h-4 w-4" />
                                    </span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        
                    </tbody>
                </table>

            
        </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    asociacion: Object,
    matriz_recuperacion: Object,
    ciudades: Object
});


import { ref, computed } from 'vue';

// Selecciones múltiples: usar arrays
const anioSeleccionado = ref([]);
const asociacionSeleccionada = ref([]);
const ciudadSeleccionada = ref([]);

const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
const mesSeleccionado = ref([]);

// Generar lista de últimos 6 años (incluye año actual)
const years = [];
const currentYear = new Date().getFullYear();
for (let i = 0; i < 6; i++) {
    years.push(currentYear - i);
}

function buscarMatriz() {
    // Enviar arrays al backend. Inertia serializa arrays por default si se pasan como parámetros.
    router.get(
        route('estadisticasolicitudes.index'),
        {
            anio: anioSeleccionado.value,
            mes: mesSeleccionado.value,
            asociacion: asociacionSeleccionada.value,
            ciudadSeleccionada: ciudadSeleccionada.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
}
const headers = [
    { key: 'name', label: 'Asociación' },
    { key: 'mes', label: 'Mes' },
    { key: 'anio', label: 'Año' },
    { key: 'total_agendada', label: 'Total Agendada' },
    { key: 'total_inmediatas', label: 'Total Inmediatas' },
    { key: 'kg_aproximadas', label: 'Kg Aproximadas' },
    { key: 'kg_revisado', label: 'Kg Revisado' },
    { key: 'total_solicitudes', label: 'Total Solicitudes' },
];

// --- Paginación avanzada ---
const currentPage = computed(() => props.matriz_recuperacion.current_page);
const perPage = computed(() => props.matriz_recuperacion.per_page);
const total = computed(() => props.matriz_recuperacion.total);
const totalPages = computed(() => props.matriz_recuperacion.last_page);

const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        router.get(route('matrizrecuperacion.index'), {
            anio: anioSeleccionado.value,
            mes: mesSeleccionado.value,
            asociacion: asociacionSeleccionada.value,
            ciudadSeleccionada: ciudadSeleccionada.value,
            page,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    }
};

const displayedPages = computed(() => {
    // Lógica para mostrar máximo 5 páginas a la vez (puedes ajustar)
    const pages = [];
    const maxPages = 5;
    let start = Math.max(1, currentPage.value - Math.floor(maxPages / 2));
    let end = Math.min(totalPages.value, start + maxPages - 1);
    if (end - start < maxPages - 1) {
        start = Math.max(1, end - maxPages + 1);
    }
    for (let i = start; i <= end; i++) {
        pages.push(i);
    }
    return pages;
});

const isDownloading = ref(false); // Estado de la descarga
const downloadExcel = async () => {
    isDownloading.value = true;
    try {
        const params = {
            anio: anioSeleccionado.value,
            mes: mesSeleccionado.value,
            asociacion: asociacionSeleccionada.value,
            ciudadSeleccionada: ciudadSeleccionada.value,
        };
        // Axios con params que contienen arrays genera query like ?anio[]=2025&anio[]=2024
        const response = await axios.get(route('descargar_excel'), {
            params,
            paramsSerializer: (p) => {
                // Dejar que URLSearchParams haga el trabajo para arrays
                return new URLSearchParams(p).toString();
            },
            responseType: 'blob',
        });

        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'usuarios.xlsx');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } catch (error) {
        console.error('Error al descargar el archivo:', error);
    } finally {
        isDownloading.value = false;
    }
};
</script>
