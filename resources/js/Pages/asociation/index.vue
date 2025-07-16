<template>
    <Head title="Asociaciones inscritas" />

    <AuthenticatedLayout>
        <template #header>
            Asociaciones inscritas
        </template>

        <div class="bg-white shadow-xl rounded-xl p-8 mt-6">
            <!-- Botón Nueva Asociación (opcional, si tienes esta funcionalidad) -->
            <!-- <button
                @click="nuevaAsociacion"
                class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold py-2 px-6 rounded-lg shadow-lg transition-all duration-300 flex items-center gap-2 text-2sm mb-4"
            >
                <span class="text-2xl">+</span> Nueva asociación
            </button> -->

            <!-- Search bar -->
            <div class="relative mb-8">
                <input
                    type="text"
                    v-model="search"
                    @keyup.enter="buscar"
                    placeholder="Buscar asociaciones..."
                    class="w-full rounded-lg border border-gray-300 py-3 pl-12 pr-4 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-gray-700 shadow"
                />
                <SearchIcon class="absolute left-4 top-3 h-5 w-5 text-indigo-400" />
            </div>

            <!-- Tabla -->
            <div class="overflow-x-auto">
                <table class="min-w-[700px] w-full text-sm text-left text-gray-700">
                    <thead class="bg-gradient-to-r from-indigo-500 to-purple-500 text-white">
                        <tr>
                            <th v-for="header in headers"
                                :key="header.key"
                                @click="ordenarPor(header.key)"
                                class="px-6 py-4 font-semibold uppercase tracking-wider cursor-pointer select-none transition hover:bg-indigo-600"
                            >
                                <div class="flex items-center gap-1">
                                    {{ header.label }}
                                    <span v-if="sort === header.key">
                                        <ChevronUpIcon v-if="direction === 'asc'" class="inline h-4 w-4" />
                                        <ChevronDownIcon v-else class="inline h-4 w-4" />
                                    </span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-right font-semibold uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="Asociations.data.length === 0">
                            <td :colspan="headers.length + 1" class="px-6 py-6 text-center text-gray-400">
                                No se encontraron asociaciones.
                            </td>
                        </tr>
                        <tr
                            v-for="asoc in Asociations.data"
                            :key="asoc.id"
                            class="hover:bg-indigo-50 transition"
                        >
                            <td class="px-6 py-4 font-medium flex items-center gap-3">
                                <img
                                    v-if="asoc.asociacion.logo_url"
                                    :src="asoc.asociacion.logo_url"
                                    class="h-8 w-8 rounded-full object-cover"
                                    alt="Logo"
                                />
                                <img
                                    v-else
                                    src="https://e7.pngegg.com/pngimages/836/345/png-clipart-ecole-centrale-de-lyon-organization-solidarity-humanitarian-aid-voluntary-association-student-people-area-thumbnail.png"
                                    class="h-8 w-8 rounded-full object-cover"
                                    alt="Logo"
                                />
                                <span>{{ asoc.asociacion.name }}</span>
                            </td>
                            <td class="px-6 py-4">{{ asoc.numero_recicladores }}</td>
                            <td class="px-6 py-4">{{ formatDate(asoc.created_at) }}</td>
                            <td class="px-6 py-4">{{ asoc.asociacion.city }}</td>
                            <td class="px-6 py-4">
                                <span :class="[
                                    'rounded-full px-2 py-1 text-xs font-semibold',
                                    asoc.asociacion.verified == false
                                        ? 'bg-red-100 text-red-800'
                                        : asoc.asociacion.verified == true
                                            ? 'bg-blue-100 text-blue-800'
                                            : 'bg-green-100 text-green-800',
                                ]">
                                    <span v-if="asoc.asociacion.verified === false">Inactivo</span>
                                    <span v-if="asoc.asociacion.verified === true">Activo</span>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <Link :href="route('asociation.index.perfil', asoc.id)"
                                        class="text-indigo-600 hover:text-indigo-900 hover:scale-105"
                                        title="Ver">
                                        <EyeIcon class="h-5 w-5" />
                                    </Link>
                                    <Link :href="route('asociation.index.show', asoc.id)"
                                        class="text-indigo-600 hover:text-indigo-900 hover:scale-105"
                                        title="Editar">
                                        <PencilIcon class="h-5 w-5" />
                                    </Link>
                                    <button
                                        class="text-red-600 hover:text-red-900 hover:scale-105"
                                        title="Eliminar"
                                        @click="EliminarAsociation(asoc)"
                                    >
                                        <TrashIcon class="h-5 w-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="mt-8 flex flex-col items-center justify-between gap-4 sm:flex-row">
                <div class="text-sm text-gray-600">
                    Mostrando
                    <span class="font-semibold">{{ (currentPage - 1) * Asociations.per_page + 1 }}</span>
                    a
                    <span class="font-semibold">{{ Math.min(currentPage * Asociations.per_page, Asociations.total) }}</span>
                    de
                    <span class="font-semibold">{{ Asociations.total }}</span>
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
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, router } from '@inertiajs/vue3';
import { ChevronLeftIcon, ChevronRightIcon, TrashIcon, SearchIcon, ChevronUpIcon, ChevronDownIcon, PencilIcon, EyeIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Link } from "@inertiajs/vue3";
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
const page = usePage();

watch(
    () => page.props.flash,
    (flash) => {
        if (flash && flash.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: flash.success,
                timer: 2000,
                showConfirmButton: false,
            });
        }
        if (flash && flash.error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: flash.error,
                timer: 3000,
                showConfirmButton: false,
            });
        }
    },
    { immediate: true }
);
const props = defineProps({
    Asociations: Object,
    filters: Object,
});


const headers = [
    { key: 'name', label: 'Asociación' },
    { key: 'numero_recicladores', label: 'Recicladores' },
    { key: 'created_at', label: 'Fecha de Registro' },
    { key: 'city', label: 'Ciudad' },
    { key: 'estado', label: 'Estado' },
];

const sort = ref(props.filters?.sort ?? 'name');
const direction = ref(props.filters?.direction ?? 'asc');
const search = ref(props.filters?.search ?? '');

const formatDate = (date: string) => {
    if (!date) return '';
    return new Date(date).toISOString().split('T')[0];
};

const currentPage = computed(() => props.Asociations.current_page);
const totalPages = computed(() => props.Asociations.last_page);

const goToPage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) {
        router.get(props.Asociations.path, {
            ...props.filters,
            search: search.value,
            sort: sort.value,
            direction: direction.value,
            page,
        }, { preserveState: true });
    }
};

const displayedPages = computed(() => {
    let pages = [];
    for (let i = 1; i <= totalPages.value; i++) {
        pages.push(i);
    }
    return pages;
});

const ordenarPor = (campo: string) => {
    let newDirection = 'asc';
    if (sort.value === campo && direction.value === 'asc') {
        newDirection = 'desc';
    }
    sort.value = campo;
    direction.value = newDirection;
    router.get(props.Asociations.path, {
        ...props.filters,
        search: search.value,
        sort: campo,
        direction: newDirection,
    }, { preserveState: true });
};

const EliminarAsociation = (dato) => {
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
            const deleteUrl = route("asociation.index.delete", { id: dato.profile_id });
            router.delete(deleteUrl, {
                preserveScroll: true,
                onSuccess: () => {
                    // Espera un tick para que Inertia actualice los props
                    setTimeout(() => {
                        if (page.props.flash && page.props.flash.error) {
                            // No recargues, el watcher mostrará el error
                            return;
                        }
                        // Si no hay error, recarga la página
                        router.visit(route('asociation.index'), {
                            preserveScroll: true
                        });
                    }, 100);
                },
                onError: () => {
                    Swal.fire('Error', 'No se pudo eliminar la asociación', 'error');
                }
            });
        }
    });
};

// Opcional: función para crear nueva asociación
const nuevaAsociacion = () => {
    // Aquí puedes abrir un modal o redirigir a la creación
    Swal.fire('Funcionalidad de crear asociación', '', 'info');
};

const buscar = () => {
    router.get(route('asociation.index'), {
        ...props.filters,
        search: search.value,
        sort: sort.value,
        direction: direction.value,
    }, { preserveState: true });
};
</script>