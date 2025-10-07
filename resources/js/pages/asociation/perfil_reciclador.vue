<template>
    <Head :title="`Perfil de ${reciclador.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <Link class="text-indigo-600 hover:text-indigo-500" :href="route('asociation.index')">Asociaciones/</Link>
            <Link class="text-indigo-600 hover:text-indigo-500" :href="route('asociation.index.perfil', { id: asociacion?.id })">
                {{ asociacion?.asociacion.name }}/
            </Link>
            <Link class="text-indigo-600 hover:text-indigo-500" :href="route('asociation.index.recicladores', { id: asociacion?.id })">
                Recicladores/
            </Link>
            {{ reciclador.name }}
        </template>
        <div class="mx-auto mt-8 max-w-7xl space-y-8">
            <!-- Card principal -->
            <div class="flex flex-col items-center gap-8 rounded-xl bg-white p-8 shadow-xl md:flex-row">
                <!-- ...dentro de tu template, reemplaza solo el div del avatar por esto... -->

                <div class="flex-shrink-0">
                    <div
                        class="relative flex h-32 w-32 items-center justify-center overflow-hidden rounded-full bg-indigo-200 text-5xl font-bold"
                        :style="{ backgroundColor: '#059669', color: '#fff' }"
                    >
                        <img
                            v-if="reciclador.logo_url"
                            :src="reciclador.logo_url"
                            alt="Foto"
                            class="h-32 w-32 cursor-pointer rounded-full border-4 border-emerald-200 object-cover shadow transition-transform hover:scale-105"
                            @click="abrirModalImagen(reciclador.logo_url)"
                        />
                        <span v-else>
                            {{ reciclador.name.charAt(0) }}
                        </span>
                    </div>
                    <!-- Modal de imagen -->
                    <div v-if="modalImagen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70" @click.self="modalImagen = null">
                        <img :src="modalImagen" class="max-h-[90vh] max-w-[90vw] rounded-xl border-4 border-white shadow-2xl" />
                        <button class="absolute right-8 top-6 text-3xl font-bold text-white hover:text-emerald-400" @click="modalImagen = null">
                            &times;
                        </button>
                    </div>
                </div>
                <!-- Info principal -->
                <div class="flex-1">
                    <h2 class="mb-2 flex items-center gap-2 text-2xl font-bold text-gray-800 md:text-3xl">
                        {{ reciclador.name }}
                        <span
                            v-if="reciclador.estado === 'Activo'"
                            class="ml-2 inline-flex items-center rounded bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-800"
                        >
                            Activo
                        </span>
                        <span v-else class="ml-2 inline-flex items-center rounded bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-800">
                            Inactivo
                        </span>
                    </h2>
                    <div class="mb-2 flex flex-wrap gap-4">
                        <div class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M17.657 16.657L13.414 12.414a2 2 0 00-2.828 0l-4.243 4.243M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                />
                            </svg>
                            <span>{{ reciclador.ciudad }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                />
                            </svg>
                            <span>{{ reciclador.auth_user?.email }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m0 4v2m0 4v2m0 4v2" />
                            </svg>
                            <span>{{ reciclador.telefono }}</span>
                        </div>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <span class="inline-block rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                            ID: {{ reciclador.id }}
                        </span>
                        <span class="inline-block rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                            Estado: {{ reciclador.status }}
                        </span>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <span class="inline-block rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">
                            Última sincronización: {{ reciclador.ultima_sincronizacion ? formatDateTime(reciclador.ultima_sincronizacion) : 'N/A' }}
                        </span>
                        <span class="inline-block rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                            Fecha de registro: {{ formatDate(reciclador.created_at) }}
                        </span>
                    </div>
                </div>
                <div class="flex w-full items-center justify-center md:w-64">
                    <MiniBarChart
                        :labels="['Completadas', 'Pendientes', 'Asignadas', 'En camino', 'Buscando', 'Cancelado']"
                        :values="[
                            estadisticas?.completadas || 0,
                            estadisticas?.pendientes || 0,
                            estadisticas?.asignadas || 0,
                            estadisticas?.en_camino || 0,
                            estadisticas?.buscando_reciclador || 0,
                            estadisticas?.cancelado || 0,
                        ]"
                    />
                </div>
            </div>
            <!-- Tabla de solicitudes del reciclador paginada -->
            <div class="mt-10">
                <h3 class="mb-4 text-xl font-bold text-emerald-700">Solicitudes realizadas</h3>
                <div class="overflow-x-auto rounded-xl shadow">
                    <table class="min-w-full bg-white text-sm">
                        <thead class="bg-emerald-100 text-emerald-700">
                            <tr>
                                <th class="px-4 py-3 text-left">ID</th>
                                <th class="px-4 py-3 text-left">Ciudadano</th>
                                <th class="px-4 py-3 text-left">Fecha</th>
                                <th class="px-4 py-3 text-left">Horario</th>
                                <th class="px-4 py-3 text-left">Estado</th>
                                <th class="px-4 py-3 text-left">Peso total</th>
                                <th class="px-4 py-3 text-left">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="sol in solicitudes.data"
                                :key="sol.id"
                                class="cursor-pointer transition hover:bg-emerald-50"
                                @click="abrirModalSolicitud(sol)"
                            >
                                <td class="px-4 py-2 font-semibold">#{{ sol.id }}</td>
                                <td class="flex items-center gap-2 px-4 py-2">
                                    <img
                                        v-if="sol.auth_user?.ciudadano?.logo_url"
                                        :src="sol.auth_user.ciudadano.logo_url"
                                        alt="Avatar"
                                        class="h-8 w-8 rounded-full border border-emerald-200 object-cover"
                                    />
                                    <span>{{ sol.auth_user?.ciudadano?.name ?? 'Sin nombre' }} ({{ sol.auth_user?.ciudadano?.nickname ?? 'Sin apodo' }})</span>
                                </td>
                                <td class="px-4 py-2">{{ formatDate(sol.fecha) }}</td>
                                <td class="px-4 py-2">{{ sol.hora_inicio }} - {{ sol.hora_fin }}</td>
                                <td class="px-4 py-2">
                                    <span
                                        :class="[
                                            'rounded-full px-2 py-1 text-xs font-semibold',
                                            sol.estado === 'completado'
                                                ? 'bg-green-100 text-green-700'
                                                : sol.estado === 'cancelado'
                                                  ? 'bg-red-100 text-red-700'
                                                  : 'bg-yellow-100 text-yellow-700',
                                        ]"
                                    >
                                        {{ sol.estado }}
                                    </span>
                                </td>
                                <td class="px-4 py-2">{{ sol.peso_total ?? 'N/A' }} kg</td>
                                <td class="px-4 py-2">
                                    <button class="text-indigo-600 hover:scale-105 hover:text-indigo-900" title="Ver detalle">
                                        <EyeIcon class="h-5 w-5" />
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!solicitudes.data || solicitudes.data.length === 0">
                                <td colspan="6" class="px-4 py-6 text-center text-gray-400">No hay solicitudes registradas.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Paginación -->
                <div v-if="solicitudes.total > solicitudes.per_page" class="mt-6 flex flex-col items-center justify-between gap-4 sm:flex-row">
                    <div class="text-sm text-gray-600">
                        Mostrando
                        <span class="font-semibold">{{ (currentPage - 1) * solicitudes.per_page + 1 }}</span>
                        a
                        <span class="font-semibold">{{ Math.min(currentPage * solicitudes.per_page, solicitudes.total) }}</span>
                        de
                        <span class="font-semibold">{{ solicitudes.total }}</span>
                        resultados
                    </div>
                    <div class="flex items-center gap-1">
                        <button
                            @click="goToPage(currentPage - 1)"
                            :disabled="currentPage === 1"
                            class="rounded-md bg-gray-100 px-3 py-1 text-gray-400 transition hover:bg-gray-200 disabled:opacity-50"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
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
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal de detalle de solicitud -->
            <div v-if="modalSolicitud" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70" @click.self="modalSolicitud = null">
                <div class="relative max-h-[90vh] w-full max-w-3xl overflow-y-auto bg-white p-6 shadow-2xl">
                    <button class="absolute right-4 top-4 text-2xl text-gray-400 hover:text-emerald-600" @click="modalSolicitud = null">
                        &times;
                    </button>
                    <h3 class="mb-2 text-lg font-bold text-emerald-700">
                        Detalle de Solicitud #{{ modalSolicitud.id }}
                        <span class="ml-2 text-lg text-yellow-500">
                            <span v-for="n in 5" :key="n">
                                <span v-if="n <= modalSolicitud.calificacion_reciclador">&#9733;</span>
                                <span v-else class="text-gray-300">&#9733;</span>
                            </span>
                        </span>
                    </h3>
                    <div class="mb-2">
                        <div class="mb-2">
                            <span class="font-semibold">Imágenes:</span>
                            <div class="mt-2 flex flex-wrap gap-3">
                                <template v-for="(img, idx) in parseImagenes(modalSolicitud.imagen)" :key="idx">
                                    <img
                                        :src="getUrlImagen(img)"
                                        alt="Imagen de la solicitud"
                                        class="h-20 w-20 cursor-pointer rounded-lg border object-cover transition hover:scale-105"
                                        @click="abrirModalImagenSolicitud(getUrlImagen(img))"
                                    />
                                </template>
                                <span v-if="!parseImagenes(modalSolicitud.imagen).length" class="text-gray-400">Sin imágenes</span>
                            </div>
                        </div>
                        <!-- Modal para ver imagen de solicitud en grande -->
                        <div
                            v-if="modalImagenSolicitud"
                            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80"
                            @click.self="modalImagenSolicitud = null"
                        >
                            <img :src="modalImagenSolicitud" class="max-h-[90vh] max-w-[90vw] rounded-xl border-4 border-white shadow-2xl" />
                            <button
                                class="absolute right-8 top-6 text-3xl font-bold text-white hover:text-emerald-400"
                                @click="modalImagenSolicitud = null"
                            >
                                &times;
                            </button>
                        </div>
                        <span class="font-semibold">Estado:</span>
                        <span
                            :class="[
                                'ml-2 rounded-full px-2 py-1 text-xs font-semibold',
                                modalSolicitud.estado === 'Completado'
                                    ? 'bg-green-100 text-green-700'
                                    : modalSolicitud.estado === 'Cancelado'
                                      ? 'bg-red-100 text-red-700'
                                      : 'bg-yellow-100 text-yellow-700',
                            ]"
                        >
                            {{ modalSolicitud.estado }}
                        </span>
                    </div>

                    <div class="mb-2"><span class="font-semibold">Fecha:</span> {{ formatDate(modalSolicitud.fecha) }}</div>
                    <div class="mb-2">
                        <span class="font-semibold">Horario:</span> {{ modalSolicitud.hora_inicio }} - {{ modalSolicitud.hora_fin }}
                    </div>
                    <div class="mb-2"><span class="font-semibold">Peso total:</span> {{ modalSolicitud.peso_total ?? 'N/A' }} kg</div>
                    <div class="mb-2">
                        <span class="font-semibold">Peso total revisado:</span> {{ modalSolicitud.peso_total_revisado ?? 'N/A' }} kg
                    </div>
                    <div class="mb-2">
                        <span class="font-semibold">Comentario reciclador:</span> {{ modalSolicitud.comentario_reciclador ?? 'N/A' }}
                    </div>
                    <div class="mb-2">
                        <span class="font-semibold">Comentario ciudadano:</span> {{ modalSolicitud.comentario_ciudadano ?? 'N/A' }}
                    </div>
                    <div class="mb-2">
                        <span class="font-semibold">Calificacion ciudadano:</span> {{ modalSolicitud.calificacion_ciudadano ?? 'N/A' }} puntos
                    </div>

                    <div class="mb-2">
                        <span class="font-semibold">Materiales:</span>
                        <div class="overflow-x-auto">
                            <table class="mt-2 min-w-full rounded-lg border text-sm">
                                <thead class="bg-emerald-100 text-emerald-700">
                                    <tr>
                                        <th class="px-4 py-2 text-left">Material</th>
                                        <th class="px-4 py-2 text-left">Peso (kg)</th>
                                        <th class="px-4 py-2 text-left">Peso revisado (kg)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="mat in modalSolicitud.materiales ?? []" :key="mat.id">
                                        <td class="px-4 py-2">
                                            <MaterialReciclaje :tipo="mat.tipo" />
                                        </td>
                                        <td class="px-4 py-2">{{ mat.peso ?? '-' }}</td>
                                        <td class="px-4 py-2">{{ mat.peso_revisado ?? '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="mb-2">
                        <span class="font-semibold">Ubicación:</span>
                        <div v-if="modalSolicitud.direccion">
                            <iframe
                                class="mt-2 h-48 w-full rounded-lg border"
                                :src="
                                    (() => {
                                        const { lat, lng } = getLatLngFromDireccion(modalSolicitud.direccion);
                                        return lat && lng ? `https://www.google.com/maps?q=${lat},${lng}&hl=es&z=16&output=embed` : '';
                                    })()
                                "
                                allowfullscreen
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                            ></iframe>
                        </div>
                        <div v-else class="text-gray-400">No disponible</div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
const modalImagenSolicitud = ref<string | null>(null);

function abrirModalImagenSolicitud(url: string) {
    modalImagenSolicitud.value = url;
}

function parseImagenes(imagen: string | null) {
    if (!imagen) return [];
    try {
        return JSON.parse(imagen);
    } catch {
        return [];
    }
}

function getUrlImagen(path: string) {
    if (!path) return '';
    if (path.startsWith('http')) return path;
    // Asegura que la ruta incluya /storage/ si no lo tiene
    let ruta = path.startsWith('/storage/') ? path : '/storage/' + path.replace(/^\/+/, '');
    const base = window.location.origin;
    return base + ruta;
}

import MaterialReciclaje from '@/Components/MaterialReciclaje.vue';
import MiniBarChart from '@/Components/MiniBarChart.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { EyeIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps({
    reciclador: Object,
    asociacion: Object,
    solicitudes: Object,
    estadisticas: Object,
});

// Paginación para solicitudes
const currentPage = computed(() => props.solicitudes.current_page);
const totalPages = computed(() => props.solicitudes.last_page);

const goToPage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) {
        router.get(
            props.solicitudes.path,
            {
                page,
            },
            { preserveState: true, preserveScroll: true },
        );
    }
};

const displayedPages = computed(() => {
    const total = totalPages.value;
    const current = currentPage.value;
    const delta = 2;
    const range = [];
    for (let i = Math.max(1, current - delta); i <= Math.min(total, current + delta); i++) {
        range.push(i);
    }
    // Elipsis opcional (puedes mejorar esto si quieres)
    if (range[0] > 1) range.unshift(1);
    if (range[range.length - 1] < total) range.push(total);
    return range.filter((v, i, arr) => i === 0 || v !== arr[i - 1]);
});
const modalImagen = ref<string | null>(null);

function abrirModalImagen(url: string) {
    modalImagen.value = url;
}

const modalSolicitud = ref(null);

function abrirModalSolicitud(sol) {
    modalSolicitud.value = sol;
}
function formatDate(date: string) {
    if (!date) return '';
    return new Date(date).toLocaleDateString();
}
function formatDateTime(date: string) {
    if (!date) return '';
    const d = new Date(date);
    return d.toLocaleDateString() + ' ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}
function getLatLngFromDireccion(direccion: string) {
    if (!direccion) return { lat: '', lng: '' };
    const latMatch = direccion.match(/Lat:\s*(-?\d+(\.\d+)?)/i);
    const lngMatch = direccion.match(/Long:\s*(-?\d+(\.\d+)?)/i);
    return {
        lat: latMatch ? latMatch[1] : '',
        lng: lngMatch ? lngMatch[1] : '',
    };
}
</script>
