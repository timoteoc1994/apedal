<template>
    <Head title="Ciudades" />

    <AuthenticatedLayout>
        <template #header>
            Ciudades
        </template>
        

        <div class="bg-white shadow-xl rounded-xl p-8 mt-6">
            <button
                    @click="nuevaCiudad"
                    class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold py-2 px-6 rounded-lg shadow-lg transition-all duration-300 flex items-center gap-2 text-2sm mb-4"
                >
                    <span class="text-2xl">+</span> Nueva ciudad
                </button>
            <!-- Search bar -->
            <div class="relative mb-8">
                <input
                    type="text"
                    v-model="search"
                    @keyup.enter="buscar"
                    placeholder="Buscar ciudades..."
                    class="w-full rounded-lg border border-gray-300 py-3 pl-12 pr-4 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-gray-700 shadow"
                />
                <SearchIcon class="absolute left-4 top-3 h-5 w-5 text-indigo-400" />
            </div>

            <!-- Tabla -->
            <div class="overflow-x-auto">
                <table class="min-w-[700px] w-full text-sm text-left text-gray-700">
                    <thead class="bg-gradient-to-r from-indigo-500 to-purple-500 text-white">
                        <tr>
                            <th
                                v-for="header in headers"
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
                        <tr v-if="cities.data.length === 0">
                            <td :colspan="headers.length + 1" class="px-6 py-6 text-center text-gray-400">
                                No se encontraron ciudades.
                            </td>
                        </tr>
                        <tr
                            v-for="city in cities.data"
                            :key="city.id"
                            class="hover:bg-indigo-50 transition"
                        >
                            <td class="px-6 py-4 font-medium">{{ city.name }}</td>
                            <td class="px-6 py-4">{{ formatDate(city.created_at) }}</td>
                            <td class="px-6 py-4 text-right">
                                <button
                                    @click="EliminarCiudad(city)"
                                    class="bg-red-100 hover:bg-red-200 text-red-600 rounded-full p-2 transition"
                                    title="Eliminar"
                                >
                                    <TrashIcon class="h-5 w-5" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="mt-8 flex flex-col items-center justify-between gap-4 sm:flex-row">
                <div class="text-sm text-gray-600">
                    Mostrando
                    <span class="font-semibold">{{ (currentPage - 1) * cities.per_page + 1 }}</span>
                    a
                    <span class="font-semibold">{{ Math.min(currentPage * cities.per_page, cities.total) }}</span>
                    de
                    <span class="font-semibold">{{ cities.total }}</span>
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
import { ChevronLeftIcon, ChevronRightIcon, TrashIcon, SearchIcon, ChevronUpIcon, ChevronDownIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps({
    cities: Object,
    filters: Object,
});

const headers = [
    { key: 'name', label: 'Ciudad' },
    { key: 'created_at', label: 'Fecha de Registro' },
];

const sort = ref(props.filters?.sort ?? 'name');
const direction = ref(props.filters?.direction ?? 'asc');
const search = ref(props.filters?.search ?? '');

const formatDate = (date: string) => {
    if (!date) return '';
    return new Date(date).toISOString().split('T')[0];
};

const currentPage = computed(() => props.cities.current_page);
const totalPages = computed(() => props.cities.last_page);

const goToPage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) {
        router.get(props.cities.path, {
            ...props.filters,
            search: search.value,
            sort: sort.value,
            direction: direction.value,
            page,
        }, { preserveState: true, replace: true });
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
    router.get(props.cities.path, {
        ...props.filters,
        search: search.value,
        sort: campo,
        direction: newDirection,
    }, { preserveState: true, replace: true });
};

const EliminarCiudad = (city: any) => {
    Swal.fire({
        title: '¿Estás seguro?',
        text: 'No podrás revertir esto',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('ciudad.index.delete', city.id), {
                preserveState: true,
                preserveScroll: true,
            });
        }
    });
};

const nuevaCiudad = () => {
    Swal.fire({
        title: 'Nueva ciudad',
        input: 'text',
        inputLabel: 'Nombre de la ciudad',
        inputPlaceholder: 'Ej: Ambato',
        showCancelButton: true,
        confirmButtonText: 'Crear',
        cancelButtonText: 'Cancelar',
        inputValidator: (value) => {
            if (!value) {
                return '¡Debes ingresar un nombre!';
            }
        }
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            router.post(route('ciudad.index.nuevo'), { name: result.value }, {
                onSuccess: () => {
                    Swal.fire('¡Ciudad creada!', '', 'success');
                },
                onError: () => {
                    Swal.fire('Error', 'No se pudo crear la ciudad', 'error');
                }
            });
        }
    });
};

const buscar = () => {
    router.get(route('ciudad.index'), {
        ...props.filters,
        search: search.value,
        sort: sort.value,
        direction: direction.value,
    }, { preserveState: true, replace: true });
};
</script>