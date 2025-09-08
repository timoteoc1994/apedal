<template>
    <Head title="Formulario de recuperación" />
    <AuthenticatedLayout :auth="$page.props.auth" :errors="$page.props.errors">
        <template #header> Formulario de recuperación </template>

        <div class="mt-6 rounded-xl bg-white p-8 shadow-xl">
            <form @submit.prevent="buscarMatriz" class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
                <!-- Select de asociación -->
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Asociación</label>
                    <select v-model="asociacionSeleccionada" class="w-full rounded border px-3 py-2">
                        <option value="Todos">Todos</option>
                        <option v-for="a in asociacion" :key="a.id" :value="a.id">{{ a.nombre }}</option>
                    </select>
                </div>
                <!-- Select de mes -->
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Mes</label>
                    <select v-model="mesSeleccionado" class="w-full rounded border px-3 py-2">
                        <option v-for="(mes, idx) in meses" :key="idx" :value="mes">
                            {{ mes }}
                        </option>
                    </select>
                </div>
                <!-- Año como texto -->
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Año</label>
                    <input type="text" v-model="anioSeleccionado" class="w-full rounded border px-3 py-2" />
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
                        <tr v-for="datos in matriz_recuperacion.data" :key="datos.id" class="border-b hover:bg-gray-50">
                            <td class="text-center">{{ datos.asociacion.name }}</td>
                            <td class="text-center">{{ datos.mes }}</td>
                            <td class="text-center">{{ datos.anio }}</td>
                            <td class="text-center">
                                {{ datos.created_at ? datos.created_at.substring(0, 10) : '-' }}
                            </td>
                            <td class="text-center">{{ datos.lugar }}</td>
                            <td class="text-center">{{ datos.num_recicladores }}</td>
                            <td class="text-center">{{ datos.carton_kilos }}</td>
                            <td class="text-center">{{ datos.carton_precio }}</td>
                            <td class="text-center">{{ datos.duplex_cubeta_kilos }}</td>
                            <td class="text-center">{{ datos.duplex_cubeta_precio }}</td>

                            <td class="text-center">{{ datos.papel_comercio_kilos }}</td>
                            <td class="text-center">{{ datos.papel_comercio_precio }}</td>
                            <td class="text-center">{{ datos.papel_bond_kilos }}</td>
                            <td class="text-center">{{ datos.papel_bond_precio }}</td>
                            <td class="text-center">{{ datos.papel_mixto_kilos }}</td>
                            <td class="text-center">{{ datos.papel_mixto_precio }}</td>
                            <td class="text-center">{{ datos.papel_multicolor_kilos }}</td>
                            <td class="text-center">{{ datos.papel_multicolor_precio }}</td>
                            <td class="text-center">{{ datos.tetrapak_kilos }}</td>
                            <td class="text-center">{{ datos.tetrapak_precio }}</td>

                            <td class="text-center">{{ datos.plastico_soplado_kilos }}</td>
                            <td class="text-center">{{ datos.plastico_soplado_precio }}</td>
                            <td class="text-center">{{ datos.plastico_duro_kilos }}</td>
                            <td class="text-center">{{ datos.plastico_duro_precio }}</td>
                            <td class="text-center">{{ datos.plastico_fino_kilos }}</td>
                            <td class="text-center">{{ datos.plastico_fino_precio }}</td>
                            <td class="text-center">{{ datos.pet_kilos }}</td>
                            <td class="text-center">{{ datos.pet_precio }}</td>
                            <td class="text-center">{{ datos.vidrio_kilos }}</td>
                            <td class="text-center">{{ datos.vidrio_precio }}</td>
                            <td class="text-center">{{ datos.chatarra_kilos }}</td>
                            <td class="text-center">{{ datos.chatarra_precio }}</td>
                            <td class="text-center">{{ datos.bronce_kilos }}</td>
                            <td class="text-center">{{ datos.bronce_precio }}</td>
                            <td class="text-center">{{ datos.cobre_kilos }}</td>
                            <td class="text-center">{{ datos.cobre_precio }}</td>
                            <td class="text-center">{{ datos.aluminio_kilos }}</td>
                            <td class="text-center">{{ datos.aluminio_precio }}</td>
                            <td class="text-center">{{ datos.pvc_kilos }}</td>
                            <td class="text-center">{{ datos.pvc_precio }}</td>
                            <td class="text-center">{{ datos.baterias_kilos }}</td>
                            <td class="text-center">{{ datos.baterias_precio }}</td>
                            <td class="text-center">{{ datos.lona_kilos }}</td>
                            <td class="text-center">{{ datos.lona_precio }}</td>
                            <td class="text-center">{{ datos.caucho_kilos }}</td>
                            <td class="text-center">{{ datos.caucho_precio }}</td>
                            <td class="text-center">{{ datos.espuma_flex_kilos }}</td>
                            <td class="text-center">{{ datos.espuma_flex_precio }}</td>
                            <td class="text-center">{{ datos.polipropileno_kilos }}</td>
                            <td class="text-center">{{ datos.polipropileno_precio }}</td>

                            <td class="text-center">{{ datos.polipropileno_expandido_kilos }}</td>
                            <td class="text-center">{{ datos.polipropileno_expandido_precio }}</td>

                            <td class="text-center">{{ datos.otro_material_1_kilos }}</td>
                            <td class="text-center">{{ datos.otro_material_1_precio }}</td>

                            <td class="text-center">{{ datos.otro_material_2_kilos }}</td>
                            <td class="text-center">{{ datos.otro_material_2_precio }}</td>

                            <td class="text-center">{{ datos.otro_material_3_kilos }}</td>
                            <td class="text-center">{{ datos.otro_material_3_precio }}</td>

                            <td class="text-center">{{ datos.total_kilos }}</td>
                            <td class="text-center">
                                <template v-if="datos.archivos_adjuntos">
                                    <template v-for="(archivo, idx) in JSON.parse(datos.archivos_adjuntos)" :key="idx">
                                        <a :href="`/storage/${archivo}`" target="_blank" class="mr-1 text-blue-600 underline">
                                            Archivo {{ idx + 1 }}
                                        </a>
                                    </template>
                                </template>
                                <template v-else> - </template>
                            </td>
                        </tr>
                    </tbody>
                </table>

            <!-- Paginación avanzada -->
            <div class="mt-8 flex flex-col items-center justify-between gap-4 sm:flex-row">
                <div class="text-sm text-gray-600">
                    Mostrando
                    <span class="font-semibold">{{ (currentPage - 1) * perPage + 1 }}</span>
                    a
                    <span class="font-semibold">{{ Math.min(currentPage * perPage, total) }}</span>
                    de
                    <span class="font-semibold">{{ total }}</span>
                    resultados
                </div>
                <div class="flex items-center gap-1">
                    <button
                        @click="goToPage(currentPage - 1)"
                        :disabled="currentPage === 1"
                        class="rounded-md px-3 py-1 bg-gray-100 text-gray-400 hover:bg-gray-200 transition disabled:opacity-50"
                    >
                        <ChevronLeftIcon class="h-5 w-5" />
                    </button>
                    <button
                        v-for="page in displayedPages"
                        :key="page"
                        @click="goToPage(page)"
                        class="rounded-md px-3 py-1"
                        :class="{
                            'bg-indigo-600 text-white': currentPage === page,
                            'bg-white text-gray-700 hover:bg-indigo-100': currentPage !== page,
                        }"
                    >
                        {{ page }}
                    </button>
                    <button
                        @click="goToPage(currentPage + 1)"
                        :disabled="currentPage === totalPages"
                        class="rounded-md px-3 py-1 bg-gray-100 text-gray-400 hover:bg-gray-200 transition disabled:opacity-50"
                    >
                        <ChevronRightIcon class="h-5 w-5" />
                    </button>
                </div>
            </div>
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
});


import { ref, computed } from 'vue';

const anioSeleccionado = ref(new Date().getFullYear());
const asociacionSeleccionada = ref('Todos');

const meses = ['Todos', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
const mesSeleccionado = ref('Todos');

function buscarMatriz() {
    router.get(
        route('matrizrecuperacion.index'),
        {
            anio: anioSeleccionado.value,
            mes: mesSeleccionado.value,
            asociacion: asociacionSeleccionada.value,
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
    { key: 'created_at', label: 'Fecha de reporte' },
    { key: 'lugar', label: 'Lugar' },

    { key: 'num_recicladores', label: 'Número de Recicladores' },

    { key: 'carton_kilos', label: 'Cartón Kilos' },
    { key: 'carton_precio', label: 'Cartón Precio' },

    { key: 'duplex_cubeta_kilos', label: 'Duplex Cubeta Kilos' },
    { key: 'duplex_cubeta_precio', label: 'Duplex Cubeta Precio' },

    { key: 'papel_comercio_kilos', label: 'Papel Comercio Kilos' },
    { key: 'papel_comercio_precio', label: 'Papel Comercio Precio' },

    { key: 'papel_bond_kilos', label: 'Papel Bond Kilos' },
    { key: 'papel_bond_precio', label: 'Papel Bond Precio' },

    { key: 'papel_mixto_kilos', label: 'Papel Mixto Kilos' },
    { key: 'papel_mixto_precio', label: 'Papel Mixto Precio' },

    { key: 'papel_multicolor_kilos', label: 'Papel Multicolor Kilos' },
    { key: 'papel_multicolor_precio', label: 'Papel Multicolor Precio' },

    { key: 'tetrapak_kilos', label: 'Tetrapak Kilos' },
    { key: 'tetrapak_precio', label: 'Tetrapak Precio' },

    { key: 'plastico_soplado_kilos', label: 'Plástico Soplado Kilos' },
    { key: 'plastico_soplado_precio', label: 'Plástico Soplado Precio' },

    { key: 'plastico_duro_kilos', label: 'Plástico Duro Kilos' },
    { key: 'plastico_duro_precio', label: 'Plástico Duro Precio' },

    { key: 'plastico_fino_kilos', label: 'Plástico Fino Kilos' },
    { key: 'plastico_fino_precio', label: 'Plástico Fino Precio' },

    { key: 'pet_kilos', label: 'PET Kilos' },
    { key: 'pet_precio', label: 'PET Precio' },

    { key: 'vidrio_kilos', label: 'Vidrio Kilos' },
    { key: 'vidrio_precio', label: 'Vidrio Precio' },

    { key: 'chatarra_kilos', label: 'Chatarra Kilos' },
    { key: 'chatarra_precio', label: 'Chatarra Precio' },

    { key: 'bronce_kilos', label: 'Bronce Kilos' },
    { key: 'bronce_precio', label: 'Bronce Precio' },

    { key: 'cobre_kilos', label: 'Cobre Kilos' },
    { key: 'cobre_precio', label: 'Cobre Precio' },

    { key: 'aluminio_kilos', label: 'Aluminio Kilos' },
    { key: 'aluminio_precio', label: 'Aluminio Precio' },

    { key: 'pvc_kilos', label: 'PVC Kilos' },
    { key: 'pvc_precio', label: 'PVC Precio' },

    { key: 'baterias_kilos', label: 'Baterías Kilos' },
    { key: 'baterias_precio', label: 'Baterías Precio' },

    { key: 'lona_kilos', label: 'Lona Kilos' },
    { key: 'lona_precio', label: 'Lona Precio' },

    { key: 'caucho_kilos', label: 'Caucho Kilos' },
    { key: 'caucho_precio', label: 'Caucho Precio' },

    { key: 'espuma_flex_kilos', label: 'Espuma Flex Kilos' },
    { key: 'espuma_flex_precio', label: 'Espuma Flex Precio' },

    { key: 'polipropileno_kilos', label: 'Polipropileno Kilos' },
    { key: 'polipropileno_precio', label: 'Polipropileno Precio' },

    { key: 'polipropileno_expandido_kilos', label: 'Polipropileno Expandido Kilos' },
    { key: 'polipropileno_expandido_precio', label: 'Polipropileno Expandido Precio' },

    { key: 'otro_material_1_kilos', label: 'Otro Material 1 Kilos' },
    { key: 'otro_material_1_precio', label: 'Otro Material 1 Precio' },
    { key: 'otro_material_2_kilos', label: 'Otro Material 2 Kilos' },
    { key: 'otro_material_2_precio', label: 'Otro Material 2 Precio' },
    { key: 'otro_material_3_kilos', label: 'Otro Material 3 Kilos' },
    { key: 'otro_material_3_precio', label: 'Otro Material 3 Precio' },

    { key: 'total_kilos', label: 'Total Kilos' },
    { key: 'archivos_adjuntos', label: 'Archivos Adjuntos' },
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
        };
        const response = await axios.get(route('descargar_excel'), {
            params,
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
