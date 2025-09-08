<template>
    <Head title="Ayuda y Soporte" />

    <AuthenticatedLayout>
        <template #header>
            Ayuda y Soporte
        </template>
        

        <div class="bg-white shadow-xl rounded-xl p-8 mt-6">
            

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
                        <tr v-if="!(soportes?.data && soportes.data.length)">
                            <td :colspan="headers.length + 1" class="px-6 py-6 text-center text-gray-400">
                                No se encontraron soportes.
                            </td>
                        </tr>
                        <template v-for="soporte in soportes.data || []" :key="soporte.id">
                            <tr class="hover:bg-indigo-50 transition">
                        <template v-if="soporte.auth_user?.role === 'reciclador'">
                            <td class="px-6 py-4">{{ soporte.auth_user?.role ?? 'Sin rol' }}</td>
                            <td class="px-6 py-4"><span :class="badgeClass(soporte.estado)">{{ estadoLabel(soporte.estado) }}</span></td>
                            <td class="px-6 py-4">
  {{ soporte.auth_user?.profile?.name ?? 'Sin nombre' }} - {{ soporte.auth_user?.email ?? 'Sin email' }}
</td>
             
                            <td class="px-6 py-4">{{ soporte.mensaje }}</td>
                            <td class="px-6 py-4">{{ formatDate(soporte.created_at) }}</td>
                             
                        </template>

                        <template v-if="soporte.auth_user?.role === 'ciudadano'">
                            <td class="px-6 py-4">{{ soporte.auth_user?.role ?? 'Sin rol' }}</td>
                            <td class="px-6 py-4"><span :class="badgeClass(soporte.estado)">{{ estadoLabel(soporte.estado) }}</span></td>
                            <td class="px-6 py-4">
  {{ soporte.auth_user?.profile?.name ?? 'Sin nombre' }} - {{ soporte.auth_user?.email ?? 'Sin email' }}
</td>
                            <td class="px-6 py-4">{{ soporte.mensaje }}</td>
                            <td class="px-6 py-4">{{ formatDate(soporte.created_at) }}</td>
                            
                        </template>
                        <template v-if="soporte.auth_user?.role === 'asociacion'">
                            <td class="px-6 py-4">{{ soporte.auth_user?.role ?? 'Sin rol' }}</td>
                            <td class="px-6 py-4"><span :class="badgeClass(soporte.estado)">{{ estadoLabel(soporte.estado) }}</span></td>
                            <td class="px-6 py-4">
  {{ soporte.auth_user?.profile?.name ?? 'Sin nombre' }} - {{ soporte.auth_user?.email ?? 'Sin email' }}
</td>
                            <td class="px-6 py-4">{{ soporte.mensaje }}</td>
                            <td class="px-6 py-4">{{ formatDate(soporte.created_at) }}</td>
                            
                        </template>
                        <template v-if="!soporte.auth_user">
                            <td class="px-6 py-4">Sin usuario</td>
                            <td class="px-6 py-4"><span :class="badgeClass(soporte.estado)">{{ estadoLabel(soporte.estado) }}</span></td>
                            <td class="px-6 py-4">Sin datos - {{ soporte.user_email ?? 'Sin email' }}</td>
                            <td class="px-6 py-4">{{ soporte.mensaje }}</td>
                            <td class="px-6 py-4">{{ formatDate(soporte.created_at) }}</td>
                            
                        </template>

                            <!-- celda de acciones común al final de la fila -->
                            <td class="px-6 py-4 text-right">
                                <button
                                    @click="cambiarEstado(soporte)"
                                 class="text-indigo-600 hover:text-indigo-900 hover:scale-105"
                                        title="Editar"
                                >
                                   <PencilIcon class="h-5 w-5" />
                                </button>
                            </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Paginación estilo ciudad -->
            <div v-if="soportes" class="mt-8 flex flex-col items-center justify-between gap-4 sm:flex-row">
                <div class="text-sm text-gray-600">
                    Mostrando
                    <span class="font-semibold">{{ (currentPage - 1) * (soportes.per_page ?? 10) + 1 }}</span>
                    a
                    <span class="font-semibold">{{ Math.min(currentPage * (soportes.per_page ?? 10), soportes.total ?? 0) }}</span>
                    de
                    <span class="font-semibold">{{ soportes.total ?? 0 }}</span>
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
import Swal from 'sweetalert2';
import { computed, ref } from 'vue';

const props = defineProps({
    soportes: { type: Object, default: () => ({ data: [] , current_page: 1, last_page: 1, per_page: 10, total: 0, path: route('ayuda_soporte.index') }) },
    filters: { type: Object, default: () => ({}) },
});

const headers = [
    { key: 'Usuario', label: 'Usuario' },
    { key: 'Estado', label: 'Estado' },
    { key: 'Datos', label: 'Datos' },
    { key: 'Mensaje', label: 'Mensaje' },
    { key: 'Fecha', label: 'Fecha' },
];

const sort = ref('');
const direction = ref<'asc'|'desc'>('asc');

function ordenarPor(key: string) {
    if (sort.value === key) {
        direction.value = direction.value === 'asc' ? 'desc' : 'asc';
    } else {
        sort.value = key;
        direction.value = 'asc';
    }
    // Aquí podrías usar Inertia to change query params y reload
}

function goToUrl(url: string | null) {
    if (!url) return;
    // Usar Inertia para navegación SPA
    router.visit(url);
}

const currentPage = computed(() => props.soportes?.current_page ?? 1);
const totalPages = computed(() => props.soportes?.last_page ?? 1);

const goToPage = (page: number) => {
    if (page >= 1 && page <= totalPages.value && props.soportes?.path) {
        router.get(props.soportes.path, {
            ...(props.filters || {}),
            page,
        }, { preserveState: true, replace: true });
    }
};

const displayedPages = computed(() => {
    const pages = [];
    for (let i = 1; i <= totalPages.value; i++) pages.push(i);
    return pages;
});

/** Formatea fecha ISO a formato legible para Ecuador */
const formatDate = (iso: string | null) => {
    if (!iso) return '';
    try {
        const d = new Date(iso);
        return d.toLocaleString('es-EC', {
            timeZone: 'America/Guayaquil',
            year: 'numeric', month: 'long', day: '2-digit',
            hour: '2-digit', minute: '2-digit'
        });
    } catch (e) {
        return iso;
    }
};

/** Mapear estado a etiqueta legible */
const estadoLabel = (estado: string | null) => {
    if (!estado) return 'Pendiente';
    const map: Record<string, string> = {
        pendiente: 'Pendiente',
        contestado: 'Contestado',
        resuelto: 'Resuelto',
        no_encontrado: 'No se encontró solución',
    };
    return map[estado] ?? estado;
};

/** Mapear estado a clases de badge Tailwind */
const badgeClass = (estado: string | null) => {
    const base = 'inline-block px-3 py-1 text-sm font-semibold rounded-full';
    switch (estado) {
        case 'resuelto':
            return base + ' bg-green-100 text-green-800';
        case 'contestado':
            return base + ' bg-blue-100 text-blue-800';
        case 'no_encontrado':
            return base + ' bg-red-100 text-red-800';
        case 'pendiente':
        default:
            return base + ' bg-gray-100 text-gray-800';
    }
};

/** Cambiar estado de un soporte usando SweetAlert y llamada PATCH a la ruta */
const cambiarEstado = (soporte: any) => {
    Swal.fire({
        title: 'Cambiar estado',
        text: 'Seleccione el nuevo estado para este mensaje',
        icon: 'question',
        showCancelButton: true,
        showCloseButton: true,
        confirmButtonText: 'Actualizar',
        cancelButtonText: 'Cancelar',
        html:
            '<div class="flex flex-col gap-2 mt-4">' +
            '<button id="opt-pendiente" class="swal-option px-4 py-2 rounded bg-gray-100 w-full">Pendiente</button>' +
            '<button id="opt-contestado" class="swal-option px-4 py-2 rounded bg-green-100 w-full">Contestado</button>' +
            '<button id="opt-resuelto" class="swal-option px-4 py-2 rounded bg-indigo-100 w-full">Resuelto</button>' +
            '<button id="opt-no_encontrado" class="swal-option px-4 py-2 rounded bg-red-100 w-full">No se encontró solución</button>' +
            '</div>',
        didOpen: () => {
            const getBtn = (id: string) => document.getElementById(id);
            const setSelected = (val: string) => {
                (document.querySelectorAll('.swal-option') || []).forEach((el) => el.classList.remove('ring-2', 'ring-indigo-600'));
                const el = getBtn('opt-' + val);
                if (el) el.classList.add('ring-2', 'ring-indigo-600');
                // store selected value on modal element
                (Swal as any).selectedEstado = val;
            };

            ['pendiente', 'contestado', 'resuelto', 'no_encontrado'].forEach((key) => {
                const btn = getBtn('opt-' + key);
                if (!btn) return;
                btn.addEventListener('click', () => setSelected(key));
            });

            // default selected from current soporte
            setSelected(soporte.estado ?? 'pendiente');
        },
        preConfirm: () => {
            const val = (Swal as any).selectedEstado || 'pendiente';
            return val;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const estado = result.value as string;
                    router.patch(route('ayuda_soporte.update.estado', soporte.id), { estado }, {
                preserveState: true,
                onSuccess: () => {
                    Swal.fire('Actualizado', 'El estado fue actualizado', 'success').then(() => {
                                // recargar la página para reflejar cambios — usar path si existe
                                const path = props.soportes?.path ?? route('ayuda_soporte.index');
                                router.get(path, { ...(props.filters || {}) }, { preserveState: true });
                    });
                },
                onError: (err: any) => {
                    Swal.fire('Error', 'No se pudo actualizar el estado', 'error');
                }
            });
        }
    });
};


</script>