<template>
    <Head title="Historial de Canjes" />

    <AuthenticatedLayout>
        <template #header> Historial de Canjes </template>

        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="border-b border-gray-200 p-6">
                <template v-if="canjes && canjes.data && canjes.data.length > 0">
                    <!-- Tabla de canjes -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full rounded-lg border border-gray-200 bg-white">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="border-b border-gray-200 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Código
                                    </th>
                                    <th
                                        class="border-b border-gray-200 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Cliente
                                    </th>
                                    <th
                                        class="border-b border-gray-200 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Producto
                                    </th>
                                    <th
                                        class="border-b border-gray-200 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Puntos
                                    </th>
                                    <th
                                        class="border-b border-gray-200 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Estado
                                    </th>
                                    <th
                                        class="border-b border-gray-200 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Fecha Solicitado
                                    </th>
                                    <th
                                        class="border-b border-gray-200 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Fecha Canjeado
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="canje in canjes.data" :key="canje.codigo" class="hover:bg-gray-50">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                        {{ canje.codigo }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 flex">
                                        <img v-if="canje.logo_url" :src="canje.logo_url" class="h-8 w-8 rounded-full object-cover" alt="Logo" />
                                        <img
                                            v-else
                                            src="https://e7.pngegg.com/pngimages/836/345/png-clipart-ecole-centrale-de-lyon-organization-solidarity-humanitarian-aid-voluntary-association-student-people-area-thumbnail.png"
                                            class="h-8 w-8 rounded-full object-cover"
                                            alt="Logo"
                                        />
                                        <div>
                                            <div class="ml-2 font-medium">{{ canje.nombre || 'Sin nombre' }}</div>
                                            <div class="ml-2 text-gray-500">{{ canje.email }}</div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                        <div v-if="canje.producto" class="flex items-center">
                                            <img
                                                v-if="canje.producto.url_imagen"
                                                :src="getImageUrl(canje.producto.url_imagen)"
                                                :alt="canje.producto.nombre"
                                                class="mr-3 h-10 w-10 rounded-lg object-cover"
                                            />
                                            <div v-else class="mr-3 flex h-10 w-10 items-center justify-center rounded-lg bg-gray-200 text-gray-400">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                    />
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="font-medium">{{ canje.producto.nombre }}</div>
                                                <div class="text-xs text-gray-500">{{ canje.producto.descripcion }}</div>
                                            </div>
                                        </div>
                                        <div v-else class="text-gray-500">Producto no disponible</div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                        <span
                                            class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800"
                                        >
                                            {{ canje.puntos }} pts
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                            :class="{
                                                'bg-green-100 text-green-800': canje.estado === 'canjeado',
                                                'bg-yellow-100 text-yellow-800': canje.estado === 'pendiente',
                                                'bg-red-100 text-red-800': canje.estado === 'cancelado',
                                            }"
                                        >
                                            {{ canje.estado.charAt(0).toUpperCase() + canje.estado.slice(1) }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                        {{ formatDate(canje.fecha_solicitado) }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                        {{ canje.fecha_canjeado ? formatDate(canje.fecha_canjeado) : '-' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div v-if="canjes.links && canjes.links.length > 3" class="mt-8 flex justify-center">
                        <nav class="inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                            <button
                                v-for="(link, idx) in canjes.links"
                                :key="idx"
                                :disabled="!link.url"
                                @click="goToPage(link.url)"
                                v-html="link.label"
                                :class="[
                                    'border border-gray-300 px-3 py-2 text-sm font-medium',
                                    link.active ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100',
                                    link.url ? 'cursor-pointer' : 'cursor-not-allowed opacity-50',
                                ]"
                                style="min-width: 36px"
                            ></button>
                        </nav>
                    </div>
                </template>

                <template v-else>
                    <div class="py-8 text-center text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No hay canjes</h3>
                        <p class="mt-1 text-sm text-gray-500">Aún no se han realizado canjes en esta tienda.</p>
                    </div>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed } from 'vue';

// Props
defineProps({
    canjes: Object,
});

// Función para formatear fechas
const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Función para paginación con Inertia
const goToPage = (url) => {
    if (url) {
        router.visit(url, { preserveScroll: true, preserveState: true });
    }
};

// Función para obtener URL de imagen
const baseUrl = computed(() => {
    return window.location.origin;
});

const getImageUrl = (urlImagen) => {
    if (!urlImagen) return '/images/placeholder-product.jpg';

    // Si es una ruta absoluta de Windows, probablemente sea un error
    if (urlImagen.includes('C:\\Windows\\Temp\\')) {
        return '/images/placeholder-product.jpg';
    }

    // Si ya tiene el dominio, devolverla tal como está
    if (urlImagen.startsWith('http')) {
        return urlImagen;
    }

    // Si es una ruta relativa, agregar el baseUrl
    return `${baseUrl.value}/storage/${urlImagen}`;
};
</script>
