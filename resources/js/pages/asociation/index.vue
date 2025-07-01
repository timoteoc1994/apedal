<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ChevronLeftIcon, ChevronRightIcon, PencilIcon, SearchIcon, TrashIcon } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    Asociations: Object,
});

const cities = ref([]);

onMounted(async () => {
    try {
        const response = await fetch('/cities');
        cities.value = await response.json();
    } catch (error) {
        console.error('Error al cargar las ciudades:', error);
    }
});
//formatear fecha
const formatDate = (date) => {
    if (!date) return ''; // Manejar valores nulos o vacíos
    return new Date(date).toISOString().split('T')[0]; // Extrae solo la fecha
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Asociación',
        href: route('asociation.index'),
    },
];

// Table headers
const headers = [
    { key: 'name', label: 'Asociación' },
    { key: 'created_at', label: 'Recicladores' },
    { key: 'created_at', label: 'Fecha de Registro' },
    { key: 'created_at', label: 'Ciudad' },
    { key: 'role', label: 'ESTADO' },
];

// Páginas actuales
const currentPage = computed(() => props.Asociations.current_page);
const totalPages = computed(() => props.Asociations.last_page);

// Función para navegar entre páginas sin recargar
const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        router.get(props.Asociations.path, { page }, { preserveState: true });
    }
};

// Definir las páginas a mostrar en la paginación
const displayedPages = computed(() => {
    let pages = [];
    for (let i = 1; i <= totalPages.value; i++) {
        pages.push(i);
    }
    return pages;
});

//eliminar pago
const EliminarAsociation = (dato) => {
    console.log("Datos de la asociación a eliminar:", dato);

    Swal.fire({
        title: "¿Estás seguro?",
        text: "No podrás revertir esto",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isConfirmed) {
            console.log("ID de la asociación que se va a eliminar:", dato.profile_id);

            const deleteUrl = route("asociation.index.delete", { id: dato.profile_id });
            console.log("URL para eliminar la asociación:", deleteUrl);

            router.delete(deleteUrl, {
                onSuccess: () => {
                    Swal.fire('Eliminado', 'La asociación ha sido eliminada', 'success')
                        .then(() => {
                            router.visit(route('asociation.index'), {
                                preserveScroll: true
                            });
                        });
                },
                onError: (error) => {
                    console.error("Error al eliminar la asociación:", error);
                    Swal.fire('Error', 'No se pudo eliminar la asociación', 'error');
                }
            });
        }
    });
};
</script>

<template>
    <Head title="Asociaciones inscritas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="rounded-lg bg-white p-6 shadow-lg dark:bg-gray-900">
                <!-- Header with title -->
                <div class="mb-6 flex flex-col items-center justify-between sm:flex-row">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Asociaciones inscritas</h2>
                </div>

                <!-- Search bar -->
                <div class="relative mb-6">
                    <input type="text" placeholder="Buscar usuarios..."
                        class="w-full rounded-lg border border-gray-300 py-2 pl-10 pr-4 focus:border-transparent focus:ring-2 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                    <SearchIcon class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" />
                </div>

                <!-- Table -->
                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                    <table class="min-w-max w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th v-for="header in headers" :key="header.key"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                    {{ header.label }}
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                            <tr v-if="Asociations.length === 0" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td :colspan="headers.length + 1"
                                    class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    No se encontraron usuarios
                                </td>
                            </tr>
                            <tr v-for="Asociation in Asociations.data" :key="Asociation.id"
                                class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center">
                                        <div v-if="Asociation.asociacion.logo_url != null" class="h-10 w-10 flex-shrink-0">
                                            <img :src="Asociation.asociacion.logo_url" alt=""
                                                class="h-10 w-10 rounded-full object-cover" />
                                        </div>
                                        <div v-else class="h-10 w-10 flex-shrink-0">
                                            <img src="https://e7.pngegg.com/pngimages/836/345/png-clipart-ecole-centrale-de-lyon-organization-solidarity-humanitarian-aid-voluntary-association-student-people-area-thumbnail.png"
                                                alt="" class="h-10 w-10 rounded-full object-cover" />
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{
                                                Asociation.asociacion.name }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ Asociation.email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ Asociation.numero_recicladores }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ formatDate(Asociation.created_at) }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ Asociation.asociacion.city }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span :class="[
                                        'rounded-full px-2 py-1 text-xs font-semibold',
                                        Asociation.asociacion.verified == false
                                            ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                            : Asociation.asociacion.verified == true
                                                ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'
                                                : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                    ]">

                                        <span v-if="Asociation.asociacion.verified === false">Inactivo</span>
                                        <span v-if="Asociation.asociacion.verified === true">Activo</span>
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <Link :href="route('asociation.index.show', Asociation.id)"
                                            class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 hover:scale-105"
                                            title="Editar">
                                        <EyeIcon class="h-5 w-5" />
                                        </Link>

                                        <Link :href="route('asociation.index.show', Asociation.id)"
                                            class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 hover:scale-105"
                                            title="Editar">
                                        <PencilIcon class="h-5 w-5" />
                                        </Link>

                                        <button
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 hover:scale-105"
                                            title="Eliminar" @click="EliminarAsociation(Asociation)">
                                            <TrashIcon class="h-5 w-5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="mt-6 flex flex-col items-center justify-between sm:flex-row">
                    <div class="mb-4 text-sm text-gray-700 dark:text-gray-300 sm:mb-0">
                        Mostrando <span class="font-medium">{{ (currentPage - 1) * Asociations.per_page + 1 }}</span> a
                        <span class="font-medium">{{ Math.min(currentPage * Asociations.per_page, Asociations.total)
                            }}</span> de
                        <span class="font-medium">{{ Asociations.total }}</span> resultados
                    </div>

                    <div class="flex items-center space-x-2">
                        <!-- Botón Anterior -->
                        <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1"
                            class="rounded-md px-3 py-1" :class="{
                                'cursor-not-allowed bg-gray-100 text-gray-400 dark:bg-gray-800': currentPage === 1,
                                'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700': currentPage > 1,
                            }">
                            <ChevronLeftIcon class="h-5 w-5" />
                        </button>

                        <!-- Números de Página -->
                        <button v-for="page in displayedPages" :key="page" @click="goToPage(page)"
                            class="rounded-md px-3 py-1" :class="{
                                'bg-purple-600 text-white': currentPage === page,
                                'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700':
                                    currentPage !== page,
                            }">
                            {{ page }}
                        </button>

                        <!-- Botón Siguiente -->
                        <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages"
                            class="rounded-md px-3 py-1" :class="{
                                'cursor-not-allowed bg-gray-100 text-gray-400 dark:bg-gray-800': currentPage === totalPages,
                                'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700':
                                    currentPage < totalPages,
                            }">
                            <ChevronRightIcon class="h-5 w-5" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>