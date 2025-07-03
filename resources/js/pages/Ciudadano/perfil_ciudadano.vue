<template>
    <Head :title="`Perfil de ${ciudadano.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <Link class="text-indigo-600 hover:text-indigo-500" :href="route('ciudadano.index')">Ciudadanos/</Link>
            {{ ciudadano.name }}
        </template>

        <div class="mx-auto mt-8 max-w-7xl space-y-8">
            <!-- Card principal -->
            <div class="flex flex-col items-center gap-8 rounded-xl bg-white p-8 shadow-xl md:flex-row">
                <!-- Avatar -->
                <div class="flex-shrink-0">
                    <div class="flex flex-col items-center">
                        <div
                            class="relative flex h-32 w-32 items-center justify-center overflow-hidden rounded-full bg-indigo-200 text-5xl font-bold"
                            :style="{ backgroundColor: '#6366f1', color: '#fff' }"
                        >
                            <img
                                v-if="ciudadano.logo_url"
                                :src="ciudadano.logo_url"
                                alt="Foto"
                                class="h-32 w-32 cursor-pointer rounded-full border-4 border-indigo-200 object-cover shadow transition-transform hover:scale-105"
                                @click="abrirModalImagen(ciudadano.logo_url)"
                            />
                            <span v-else>
                                {{ ciudadano.name.charAt(0) }}
                            </span>
                        </div>
                        <div class="mb-2 mt-4 flex items-center gap-2">
                            <div class="relative flex items-center">
                                <span class="flex items-center gap-2 text-4xl font-extrabold text-yellow-400 drop-shadow-lg md:text-5xl">
                                    {{ ciudadano.auth_user?.puntos ?? 0 }}
                                </span>
                                <span
                                    class="absolute -bottom-5 left-1/2 -translate-x-1/2 rounded-full border-2 border-white bg-yellow-400 px-3 py-1 text-xs font-bold text-white shadow-lg"
                                >
                                    PUNTOS
                                </span>
                            </div>
                        </div>
                    </div>
                    <!-- Modal de imagen -->
                    <div v-if="modalImagen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70" @click.self="modalImagen = null">
                        <img :src="modalImagen" class="max-h-[90vh] max-w-[90vw] rounded-xl border-4 border-white shadow-2xl" />
                        <button class="absolute right-8 top-6 text-3xl font-bold text-white hover:text-indigo-400" @click="modalImagen = null">
                            &times;
                        </button>
                    </div>
                </div>
                <!-- Info principal -->
                <div class="flex-1">
                    <h2 class="mb-2 flex items-center gap-2 text-2xl font-bold text-gray-800 md:text-3xl">
                        {{ ciudadano.name }} ({{ ciudadano.nickname }})
                    </h2>

                    <div class="mb-2 flex flex-wrap gap-4">
                        <div class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M17.657 16.657L13.414 12.414a2 2 0 00-2.828 0l-4.243 4.243M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                />
                            </svg>
                            <span>{{ ciudadano.ciudad }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                />
                            </svg>
                            <span>{{ ciudadano.auth_user?.email }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m0 4v2m0 4v2m0 4v2" />
                            </svg>
                            <span>{{ ciudadano.telefono }}</span>
                        </div>
                    </div>

                    <div class="mt-2 flex flex-wrap gap-2">
                        <span class="inline-block rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                            ID: {{ ciudadano.id }}
                        </span>
                        <span class="inline-block rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">
                            Email verificado:
                            <span v-if="ciudadano.auth_user?.email_verified_at" class="text-green-600">Sí</span>
                            <span v-else class="text-red-600">No</span>
                        </span>
                        <span class="inline-block rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                            Fecha de registro: {{ formatDate(ciudadano.created_at) }}
                        </span>
                    </div>
                    <div class="mt-2">
                        <span class="font-semibold">Referencia:</span>
                        <span>{{ ciudadano.referencias_ubicacion }}</span>
                    </div>
                </div>
                <!-- Gráfico de estadísticas -->
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


            <!-- Tabla de solicitudes del ciudadano paginada -->
            <div class="mt-10">
                <h3 class="mb-4 text-xl font-bold text-indigo-700">Solicitudes realizadas</h3>
                <div class="overflow-x-auto rounded-xl shadow">
                    <table class="min-w-full bg-white text-sm">
                        <thead class="bg-indigo-100 text-indigo-700">
                            <tr>
                                <th class="px-4 py-3 text-left">ID</th>
                                <th class="px-4 py-3 text-left">Reciclador</th>
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
                                class="cursor-pointer transition hover:bg-indigo-50"
                                @click="abrirModalSolicitud(sol)"
                            >
                                <td class="px-4 py-2 font-semibold">#{{ sol.id }}</td>
                                <td class="flex items-center gap-2 px-4 py-2">
                                    <img
                                        v-if="sol.auth_user?.reciclador?.logo_url"
                                        :src="sol.auth_user?.reciclador?.logo_url"
                                        alt="Avatar"
                                        class="h-8 w-8 rounded-full border border-indigo-200 object-cover"
                                    />
                                    <span>{{ sol.auth_user?.reciclador?.name ?? 'Sin reciclador' }}</span>
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
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m6 0a6 6 0 11-12 0 6 6 0 0112 0z" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!solicitudes.data || solicitudes.data.length === 0">
                                <td colspan="7" class="px-4 py-6 text-center text-gray-400">No hay solicitudes registradas.</td>
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
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-1 rounded-lg bg-white p-6 shadow">
                <span class="font-semibold">Dirección:</span>
                <span>{{ ciudadano.direccion }}</span>
                <div v-if="coords.lat && coords.lng" class="mt-2 overflow-hidden rounded-lg shadow">
                    <iframe
                        :src="`https://maps.google.com/maps?q=${coords.lat},${coords.lng}&z=16&output=embed`"
                        width="100%"
                        height="250"
                        style="border: 0"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                    ></iframe>
                </div>
            </div>

            <!-- Modal de detalle de solicitud -->
            <div v-if="modalSolicitud" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70" @click.self="modalSolicitud = null">
                <div class="relative max-h-[90vh] w-full max-w-3xl overflow-y-auto bg-white p-6 shadow-2xl">
                    <button class="absolute right-4 top-4 text-2xl text-gray-400 hover:text-indigo-600" @click="modalSolicitud = null">
                        &times;
                    </button>
                    <h3 class="mb-2 text-lg font-bold text-indigo-700">
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
                                class="absolute right-8 top-6 text-3xl font-bold text-white hover:text-indigo-400"
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
                        <span class="font-semibold">Comentario reciclador:</span> {{ modalSolicitud.comentario_reciclador ?? 'N/A' }} kg
                    </div>
                    <div class="mb-2">
                        <span class="font-semibold">Comentario ciudadano:</span> {{ modalSolicitud.comentario_ciudadano ?? 'N/A' }} kg
                    </div>
                    <div class="mb-2">
                        <span class="font-semibold">Calificacion ciudadano:</span> {{ modalSolicitud.calificacion_ciudadano ?? 'N/A' }} puntos
                    </div>

                    <div class="mb-2">
                        <span class="font-semibold">Materiales:</span>
                        <div class="overflow-x-auto">
                            <table class="mt-2 min-w-full rounded-lg border text-sm">
                                <thead class="bg-indigo-100 text-indigo-700">
                                    <tr>
                                        <th class="px-4 py-2 text-left">Material</th>
                                        <th class="px-4 py-2 text-left">Peso (kg)</th>
                                        <th class="px-4 py-2 text-left">Peso revisado (kg)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="mat in modalSolicitud.materiales ?? []" :key="mat.id">
                                        <td class="px-4 py-2">
                                            <span>{{ mat.tipo }}</span>
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
import MiniBarChart from '@/components/MiniBarChart.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    ciudadano: Object,
    estadisticas: Object,
    solicitudes: Object,
});

const ciudadano = props.ciudadano;

const modalImagen = ref<string | null>(null);
function abrirModalImagen(url: string) {
    modalImagen.value = url;
}

// Modal para detalle de solicitud
const modalSolicitud = ref<any>(null);
function abrirModalSolicitud(sol: any) {
    modalSolicitud.value = sol;
}

// Modal para imagen de solicitud
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
    let ruta = path.startsWith('/storage/') ? path : '/storage/' + path.replace(/^\/+/,'');
    const base = window.location.origin;
    return base + ruta;
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

const coords = getLatLngFromDireccion(ciudadano.direccion);

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
    if (range[0] > 1) range.unshift(1);
    if (range[range.length - 1] < total) range.push(total);
    return range.filter((v, i, arr) => i === 0 || v !== arr[i - 1]);
});
</script>
