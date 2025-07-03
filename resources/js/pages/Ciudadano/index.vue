<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, Link } from '@inertiajs/vue3';
import axios from 'axios';
import { ChevronLeftIcon, ChevronRightIcon, PencilIcon, PlusIcon, SearchIcon, TrashIcon, ChevronUpIcon, ChevronDownIcon, EyeIcon } from 'lucide-vue-next';
// Ordenamiento
const headers = [
    { key: 'name', label: 'Nombre' },
    { key: 'puntos', label: 'Puntos' },
    { key: 'telefono', label: 'Teléfono' },
    { key: 'ciudad', label: 'Ciudad' },
];
const sort = ref('puntos');
const direction = ref('desc');

const ordenarPor = (campo: string) => {
    let newDirection = 'asc';
    if (sort.value === campo && direction.value === 'asc') {
        newDirection = 'desc';
    }
    sort.value = campo;
    direction.value = newDirection;
    router.get(props.ciudadanos.path, {
        search: filtro.value,
        sort: campo,
        direction: newDirection,
    }, { preserveState: true, replace: true });
};
import Swal from 'sweetalert2';
import { computed, ref, watch } from 'vue';

// Props Inertia con paginación
const props = defineProps<{
    ciudadanos: {
        data: any[];
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
        path: string;
    };
}>();

// Estado local y búsqueda
const ciudadanosLocal = ref([...props.ciudadanos.data]);
const filtro = ref('');

// Al cambiar el filtro, consulta al backend con ?search=valor
watch(filtro, (val) => {
    router.get(props.ciudadanos.path, { search: val }, { preserveState: true, replace: true });
});

// Paginación
const currentPage = computed(() => props.ciudadanos.current_page);
const totalPages = computed(() => props.ciudadanos.last_page);
const displayedPages = computed(() => Array.from({ length: totalPages.value }, (_, i) => i + 1));
const goToPage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) {
        router.get(props.ciudadanos.path, { page, search: filtro.value }, { preserveState: true, replace: true });
    }
};

// Mantener data local actualizada al cambiar props
watch(
    () => props.ciudadanos.data,
    (newVal) => {
        ciudadanosLocal.value = [...newVal];
    },
);

// Breadcrumbs y navegación
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Ciudadanos', href: '/ciudadanos' }];
const createCiudadano = () => {
    window.location.href = '/crear-ciudadano';
};
const editCiudadano = (id: number) => {
    router.get(`/ciudadanos/${id}/editar`);
};

// Eliminar ciudadano
const deleteCiudadano = async (id: number) => {
    const { isConfirmed } = await Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar',
    });
    if (!isConfirmed) return;

    try {
        await axios.delete(`/ciudadanos/${id}`);
        Swal.fire('¡Eliminado!', 'El ciudadano ha sido eliminado.', 'success');
        ciudadanosLocal.value = ciudadanosLocal.value.filter((c) => c.id !== id);
    } catch {
        Swal.fire('Error', 'No se pudo eliminar. Intenta de nuevo.', 'error');
    }
};
</script>

<template>
    <Head title="Ciudadanos" />
    <AuthenticatedLayout>
        <template #header> Ciudadanos </template>
        <div class="mt-6 rounded-xl bg-white p-8 shadow-xl">
            <!-- Botón Crear Ciudadano -->
            <div class="mb-4 flex justify-end">
               <!--  <button
                    @click="createCiudadano"
                    class="text-2sm flex items-center gap-2 rounded-lg bg-gradient-to-r from-purple-600 to-indigo-600 px-6 py-2 font-semibold text-white shadow-lg transition-all duration-300 hover:from-indigo-700 hover:to-purple-700"
                >
                    <PlusIcon class="text-2xl" /> Crear Ciudadano
                </button> -->
            </div>
            <!-- Barra de búsqueda -->
            <div class="relative mb-8">
                <input
                    type="text"
                    v-model="filtro"
                    @keyup.enter="() => goToPage(1)"
                    placeholder="Buscar ciudadanos..."
                    class="w-full rounded-lg border border-gray-300 py-3 pl-12 pr-4 text-gray-700 shadow focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                />
                <SearchIcon class="absolute left-4 top-3 h-5 w-5 text-indigo-400" />
            </div>

            <!-- Tabla -->
            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px] text-left text-sm text-gray-700">
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
                        <tr v-if="!ciudadanosLocal.length">
                            <td :colspan="5" class="px-6 py-6 text-center text-gray-400">No se encontraron ciudadanos.</td>
                        </tr>
                        <tr v-for="c in ciudadanosLocal" :key="c.id" class="transition hover:bg-indigo-50">
                            <td class="flex items-center gap-3 px-6 py-4 font-medium">
                                <img v-if="c.logo_url" :src="c.logo_url" class="h-8 w-8 rounded-full object-cover" alt="Logo" />
                                <img
                                    v-else
                                    src="https://e7.pngegg.com/pngimages/836/345/png-clipart-ecole-centrale-de-lyon-organization-solidarity-humanitarian-aid-voluntary-association-student-people-area-thumbnail.png"
                                    class="h-8 w-8 rounded-full object-cover"
                                    alt="Logo"
                                />
                                {{ c.name }} ({{ c.nickname }})
                            </td>
                            <td class="px-6 py-4">{{ c.puntos }} </td>
                            <td class="px-6 py-4">{{ c.telefono }}</td>
                            
                            <td class="px-6 py-4">{{ c.ciudad }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                     <Link :href="route('ciudadano.perfil', c.id)"
                                        class="text-indigo-600 hover:text-indigo-900 hover:scale-105"
                                        title="Ver">
                                        <EyeIcon class="h-5 w-5" />
                                    </Link>
                                    <button @click="editCiudadano(c.id)" class="text-indigo-600 hover:scale-105 hover:text-indigo-900" title="Editar">
                                        <PencilIcon class="h-5 w-5" />
                                    </button>
                                    <button @click="deleteCiudadano(c.id)" class="text-red-600 hover:scale-105 hover:text-red-900" title="Eliminar">
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
                    <span class="font-semibold">{{ (currentPage - 1) * props.ciudadanos.per_page + 1 }}</span>
                    a
                    <span class="font-semibold">{{ Math.min(currentPage * props.ciudadanos.per_page, props.ciudadanos.total) }}</span>
                    de
                    <span class="font-semibold">{{ props.ciudadanos.total }}</span>
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
                            'bg-indigo-600 text-white': currentPage === page,
                            'bg-white text-gray-700 hover:bg-indigo-100': currentPage !== page,
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

<style scoped>
/* Todo el styling está en Tailwind – no requiere CSS adicional */
</style>
