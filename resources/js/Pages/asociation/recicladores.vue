<template>
    <Head title="Recicladores" />

    <AuthenticatedLayout>
        <template #header>
            <Link class="text-indigo-600 hover:text-indigo-500" :href="route('asociation.index')">Asociaciones/</Link>
            <Link class="text-indigo-600 hover:text-indigo-500" :href="route('asociation.index.perfil', { id: nombreAsociacion.id })">
                {{ nombreAsociacion.asociacion.name }}/
            </Link>

            Recicladores
        </template>

        <div class="mt-6 rounded-xl bg-white p-8 shadow-xl">
            <div class="relative mb-8">
                <input
                    type="text"
                    v-model="search"
                    @keyup.enter="buscar"
                    placeholder="Buscar recicladores..."
                    class="w-full rounded-lg border border-gray-300 py-3 pl-12 pr-4 text-gray-700 shadow focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                />
                <SearchIcon class="absolute left-4 top-3 h-5 w-5 text-indigo-400" />
            </div>

            <!-- Tabla de recicladores estilo asociaciones -->
            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px] text-left text-sm text-gray-700">
                    <thead class="bg-gradient-to-r from-emerald-500 to-teal-500 text-white">
                        <tr>
                            <th
                                v-for="header in headers"
                                :key="header.key"
                                :class="[
                                    'select-none px-6 py-4 font-semibold uppercase tracking-wider',
                                    header.key !== 'email' ? 'cursor-pointer transition hover:bg-emerald-600' : 'bg-emerald-500',
                                ]"
                                @click="header.key !== 'email' ? ordenarPor(header.key) : null"
                            >
                                <div class="flex items-center gap-1">
                                    {{ header.label }}
                                    <template v-if="header.key !== 'email' && sort === header.key">
                                        <ChevronUpIcon v-if="direction === 'asc'" class="inline h-4 w-4" />
                                        <ChevronDownIcon v-else class="inline h-4 w-4" />
                                    </template>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-right font-semibold uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="recicladores.data.length === 0">
                            <td :colspan="headers.length + 1" class="px-6 py-6 text-center text-gray-400">No se encontraron recicladores.</td>
                        </tr>
                        <tr v-for="rec in recicladores.data" :key="rec.id" class="transition hover:bg-emerald-50">
                            <td class="flex items-center gap-3 px-6 py-4 font-medium">
                                <img v-if="rec.logo_url" :src="rec.logo_url" class="h-8 w-8 rounded-full object-cover" alt="Avatar" />
                                <img
                                    v-else
                                    src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                                    class="h-8 w-8 rounded-full object-cover"
                                    alt="Avatar"
                                />
                                <span>{{ rec.name }}</span>
                            </td>
                            <td class="px-6 py-4">{{ rec.ciudad }}</td>
                            <td class="px-6 py-4">{{ rec.auth_user?.email }}</td>
                            <td class="px-6 py-4">
                                <span
                                    :class="[
                                        'rounded-full px-2 py-1 text-xs font-semibold',
                                        statusClass(rec.status),
                                    ]"
                                >
                                    {{ formatStatus(rec.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">{{ formatDate(rec.created_at) }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <Link :href="route('asociation.index.recicladoresperfil', { id: rec.id })">
                                        <EyeIcon class="h-5 w-5 text-indigo-600 hover:text-indigo-900" />
                                    </Link>
                                    <Link
                                        :href="route('asociation.index.show.recicladores', rec.auth_user?.id)"
                                        class="text-indigo-600 hover:scale-105 hover:text-indigo-900"
                                        title="Editar"
                                    >
                                        <PencilIcon class="h-5 w-5" />
                                    </Link>
                                    <button
                                        @click="confirmarEliminar(rec.id)"
                                        class="text-red-600 hover:scale-105 hover:text-red-900"
                                        title="Eliminar"
                                    >
                                        <TrashIcon class="h-5 w-5" />
                                    </button>
                                    <a
    target="_blank"
    v-if="rec.status === 'disponible' || rec.status === 'en_ruta'"
    :href="route('tracking.show', rec.auth_user.id)"
>
    <MapPinIcon class="h-5 w-5 text-emerald-600 hover:text-emerald-900" />
</a>                             
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación estilo asociaciones -->
            <div class="mt-8 flex flex-col items-center justify-between gap-4 sm:flex-row">
                <div class="text-sm text-gray-600">
                    Mostrando
                    <span class="font-semibold">{{ (currentPage - 1) * recicladores.per_page + 1 }}</span>
                    a
                    <span class="font-semibold">{{ Math.min(currentPage * recicladores.per_page, recicladores.total) }}</span>
                    de
                    <span class="font-semibold">{{ recicladores.total }}</span>
                    resultados
                </div>
                <div class="flex items-center gap-1">
                    <button
                        @click="goToPage(currentPage - 1)"
                        :disabled="currentPage === 1"
                        class="rounded-md bg-gray-100 px-3 py-1 text-gray-400 transition hover:bg-gray-200 disabled:opacity-50"
                    >
                        <ChevronLeftIcon class="h-5 w-5" />
                    </button>
                    <button
                        v-for="page in displayedPages"
                        :key="page"
                        @click="goToPage(page)"
                        class="rounded-md px-3 py-1"
                        :class="{
                            'bg-emerald-600 text-white': currentPage === page,
                            'bg-white text-gray-700 hover:bg-emerald-100': currentPage !== page,
                        }"
                    >
                        {{ page }}
                    </button>
                    <button
                        @click="goToPage(currentPage + 1)"
                        :disabled="currentPage === totalPages"
                        class="rounded-md bg-gray-100 px-3 py-1 text-gray-400 transition hover:bg-gray-200 disabled:opacity-50"
                    >
                        <ChevronRightIcon class="h-5 w-5" />
                    </button>
                    
                    
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ChevronDownIcon, ChevronLeftIcon, ChevronRightIcon, ChevronUpIcon, EyeIcon, PencilIcon, SearchIcon, TrashIcon, MapPinIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps({
    recicladores: Object, // Cambia a Object para paginación
    nombreAsociacion: Object,
    filters: Object,
});

const headers = [
    { key: 'name', label: 'Reciclador' },
    { key: 'ciudad', label: 'Ciudad' },
    { key: 'email', label: 'Email' },
    { key: 'estado', label: 'Estado' },
    { key: 'created_at', label: 'Fecha de Registro' },
];

const sort = ref(props.filters?.sort ?? 'name');
const direction = ref(props.filters?.direction ?? 'asc');
const search = ref(props.filters?.search ?? '');

const currentPage = computed(() => props.recicladores?.current_page ?? 1);
const totalPages = computed(() => props.recicladores?.last_page ?? 1);

const displayedPages = computed(() => {
    const total = totalPages.value;
    const current = currentPage.value;
    const delta = 2;
    let pages = [];
    for (let i = Math.max(1, current - delta); i <= Math.min(total, current + delta); i++) {
        pages.push(i);
    }
    return pages;
});

function goToPage(page: number) {
    if (page < 1 || page > totalPages.value) return;
    router.get(
        route('asociation.index.recicladores', { id: props.nombreAsociacion.id }),
        { search: search.value, sort: sort.value, direction: direction.value, page },
        { preserveState: true },
    );
}

const formatDate = (date: string) => {
    if (!date) return '';
    return new Date(date).toISOString().split('T')[0];
};

const ordenarPor = (campo: string) => {
    let newDirection = 'asc';
    if (sort.value === campo && direction.value === 'asc') {
        newDirection = 'desc';
    }
    sort.value = campo;
    direction.value = newDirection;
    router.get(
        route('asociation.index.recicladores', { id: props.nombreAsociacion.id }),
        { search: search.value, sort: campo, direction: newDirection },
        { preserveState: true },
    );
};

function buscar() {
    router.get(
        route('asociation.index.recicladores', { id: props.nombreAsociacion.id }),
        { search: search.value, sort: sort.value, direction: direction.value },
        { preserveState: true, replace: true },
    );
}
function confirmarEliminar(recicladorId: number) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¡Esta acción no se puede deshacer!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('asociation.index.reciclador.delete', { id: recicladorId }), {
                preserveScroll: true,
            });
        }
    });
}

// Helpers para el estado
function statusClass(status: string | null | undefined) {
    if (!status) return 'bg-gray-100 text-gray-600';
    const s = String(status).toLowerCase();
    // Normalizar posibles valores
    if (s === 'activo' || s === 'disponible') return 'bg-green-100 text-green-800';
    if (s === 'en_ruta' || s === 'en ruta' || s === 'enruta') return 'bg-blue-100 text-blue-800';
    if (s === 'inactivo' || s === 'inactivo' || s === 'pendiente') return 'bg-red-100 text-red-800';
    // fallback
    return 'bg-gray-100 text-gray-600';
}

function formatStatus(status: string | null | undefined) {
    if (!status) return '';
    // Reemplazar guiones bajos por espacios y capitalizar
    const s = String(status).replace(/_/g, ' ').toLowerCase();
    return s.charAt(0).toUpperCase() + s.slice(1);
}
</script>
