<template>

    <Head title="Ciudadanos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div
                class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow-md p-6 min-h-[100vh] md:min-h-min">
                <div class="max-w-6xl mx-auto">

                    <!-- Título -->
                    <h1 class="text-3xl font-bold text-center text-gray-800 dark:text-gray-100 mb-6">Listado de
                        Ciudadanos</h1>

                    <!-- Barra superior: buscador + botón -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4 w-full">
                        <!-- Buscador -->
                        <input v-model="filtro" type="text" placeholder="Buscar por nombre..."
                            class="w-full md:flex-1 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white" />

                        <!-- Botón crear -->
                        <button
                            class="w-full md:w-auto bg-green-600 hover:bg-green-700 text-white font-medium px-6 py-2 rounded-md shadow transition"
                            @click="createCiudadano">
                            Crear Ciudadano
                        </button>
                    </div>

                    <!-- Mensaje cuando no hay ciudadanos -->
                    <div v-if="ciudadanosFiltrados.length === 0"
                        class="text-center text-lg text-red-600 bg-red-100 p-4 rounded-md">
                        No hay ciudadanos disponibles o no hay coincidencias.
                    </div>

                    <!-- Tabla de ciudadanos -->
                    <div v-else class="overflow-x-auto">
                        <table
                            class="w-full table-auto text-left bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow">
                            <thead
                                class="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-100 uppercase text-sm">
                                <tr>
                                    <th class="px-4 py-3">Nombre</th>
                                    <th class="px-4 py-3">Teléfono</th>
                                    <th class="px-4 py-3">Dirección</th>
                                    <th class="px-4 py-3">Ciudad</th>
                                    <th class="px-4 py-3 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="ciudadano in ciudadanosFiltrados" :key="ciudadano.id"
                                    class="border-t border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-3">{{ ciudadano.name }}</td>
                                    <td class="px-4 py-3">{{ ciudadano.telefono }}</td>
                                    <td class="px-4 py-3">{{ ciudadano.direccion }}</td>
                                    <td class="px-4 py-3">{{ ciudadano.ciudad }}</td>
                                    <td class="px-4 py-3 text-center flex justify-center gap-2">
                                        <button
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1 rounded-md transition"
                                            @click="editCiudadano(ciudadano.id)">
                                            Editar
                                        </button>
                                        <button
                                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-1 rounded-md transition"
                                            @click="deleteCiudadano(ciudadano.id)">
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Swal from 'sweetalert2';
import axios from 'axios';
import { type BreadcrumbItem } from '@/types';

const props = defineProps<{
    ciudadanos: Array<any>
}>();

const ciudadanosLocal = ref([...props.ciudadanos]);
const filtro = ref('');

// Computed para aplicar filtro por nombre
const ciudadanosFiltrados = computed(() => {
    const term = filtro.value.toLowerCase().trim();
    return ciudadanosLocal.value.filter(ciudadano =>
        ciudadano.name.toLowerCase().includes(term)
    );
});

watch(() => props.ciudadanos, (newVal) => {
    ciudadanosLocal.value = [...newVal];
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Ciudadanos', href: '/ciudadano' }
];

const createCiudadano = () => {
    window.location.href = '/crear-ciudadano';
};

const editCiudadano = (id: number) => {
    window.location.href = `/ciudadanos/${id}/editar`;
};

const deleteCiudadano = async (id: number) => {
    const confirmation = await Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar',
    });

    if (confirmation.isConfirmed) {
        try {
            const response = await axios.delete(`/ciudadanos/${id}`);

            if (response.data.success) {
                Swal.fire({
                    title: '¡Eliminado!',
                    text: 'El ciudadano ha sido eliminado.',
                    icon: 'success',
                    confirmButtonText: 'Cerrar',
                });

                ciudadanosLocal.value = ciudadanosLocal.value.filter(c => c.id !== id);
            } else {
                Swal.fire({
                    title: 'Error',
                    text: 'Hubo un problema al eliminar el ciudadano.',
                    icon: 'error',
                    confirmButtonText: 'Cerrar',
                });
            }
        } catch (error) {
            console.error('Error al eliminar ciudadano:', error);
            Swal.fire({
                title: 'Error',
                text: 'Hubo un problema al eliminar el ciudadano.',
                icon: 'error',
                confirmButtonText: 'Cerrar',
            });
        }
    }
};
</script>