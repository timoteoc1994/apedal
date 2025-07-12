<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            Dashboard
        </template>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 border-b border-gray-200">

                <template v-if="productos && productos.data && productos.data.length > 0">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        <div v-for="producto in productos.data" :key="producto.id" class="group relative overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm transition-all duration-300 hover:shadow-lg">
                            <!-- Badge de estado -->
                            <div class="absolute right-2 top-2 z-10">
                                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium backdrop-blur-sm"
                                    :class="{
                                        'bg-green-100/90 text-green-800': producto.estado === 'publicado',
                                        'bg-yellow-100/90 text-yellow-800': producto.estado === 'borrador',
                                    }">
                                    <span class="mr-1 h-1.5 w-1.5 rounded-full"
                                        :class="{
                                            'bg-green-500': producto.estado === 'publicado',
                                            'bg-yellow-500': producto.estado === 'borrador',
                                        }"></span>
                                    {{ producto.estado.charAt(0).toUpperCase() + producto.estado.slice(1) }}
                                </span>
                            </div>
                            <!-- Imagen -->
                            <div class="aspect-square overflow-hidden bg-gradient-to-br from-gray-50 to-gray-100">
                                <img
                            :src="getImageUrl(producto.url_imagen)"
                            :alt="producto.nombre"
                            class="h-full w-full object-cover transition-transform duration-300"
                            @error="handleImageError"
                        />
                            </div>
                            <!-- Contenido -->
                            <div class="p-3 flex flex-col h-full">
                                <h3 class="mb-2 line-clamp-2 text-sm font-semibold text-gray-900 transition-colors group-hover:text-indigo-600 text-center">{{ producto.nombre }}</h3>
                                <div class="mb-2 flex items-center justify-center gap-2">
                                    <span class="inline-block bg-green-100 text-green-700 text-xs px-2 py-1 rounded">Puntos: <span class="font-semibold">{{ producto.puntos }}</span></span>
                                    <span v-if="producto.categoria" class="inline-block bg-blue-100 text-blue-700 text-xs px-2 py-1 rounded">{{ producto.categoria }}</span>
                                </div>
                                <p class="text-xs text-gray-600 text-center mb-2 line-clamp-2">{{ producto.descripcion }}</p>
                                <div v-if="producto.direccion_reclamo" class="mb-2 text-xs text-purple-700 bg-purple-50 px-2 py-1 rounded text-center">
                                    <span class="font-semibold">Dirección:</span> {{ producto.direccion_reclamo }}
                                </div>
                                <div class="flex justify-center mt-2">
                                    <button
                                        @click="verDetalles(producto)"
                                        class="bg-blue-600 hover:bg-blue-800 text-white px-4 py-2 rounded-lg font-semibold shadow text-xs focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 border border-blue-700"
                                        style="min-width: 110px; display: block;"
                                    >
                                        Ver Detalles
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Modal de detalles -->
                    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
                        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full p-8 relative animate-fade-in">
                            <button @click="closeModal" class="absolute top-3 right-3 text-gray-400 hover:text-blue-600 text-2xl font-bold">&times;</button>
                            <div class="flex flex-col items-center">
                                <img v-if="modalProducto.url_imagen" :src="getImageUrl(modalProducto.url_imagen)" class="h-32 w-32 rounded-lg object-cover border-2 border-blue-200 shadow mb-4" alt="Producto" />
                                <div v-else class="h-32 w-32 rounded-lg bg-gray-200 flex items-center justify-center text-gray-400 mb-4">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 21V7a2 2 0 00-2-2H6a2 2 0 00-2 2v14m16 0H4m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0V7m0 14a2 2 0 01-2 2H6a2 2 0 01-2-2" />
                                    </svg>
                                </div>
                                <h3 class="text-2xl font-bold text-blue-800 mb-2 text-center">{{ modalProducto.nombre }}</h3>
                                <div class="flex flex-wrap gap-2 mb-2 justify-center">
                                    <span class="inline-block bg-green-100 text-green-700 text-xs px-2 py-1 rounded">Puntos: <span class="font-semibold">{{ modalProducto.puntos }}</span></span>
                                    <span v-if="modalProducto.categoria" class="inline-block bg-blue-100 text-blue-700 text-xs px-2 py-1 rounded">{{ modalProducto.categoria }}</span>
                                    <span v-if="modalProducto.estado" class="inline-block bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded">Estado: {{ modalProducto.estado }}</span>
                                </div>
                                <div v-if="modalProducto.direccion_reclamo" class="mb-2 text-xs text-purple-700 bg-purple-50 px-2 py-1 rounded text-center">
                                    <span class="font-semibold">Dirección de Reclamo:</span> {{ modalProducto.direccion_reclamo }}
                                </div>
                                <div class="w-full grid grid-cols-1 md:grid-cols-2 gap-2 mb-2 mt-2">
                                    <div v-if="modalProducto.stock !== undefined" class="text-xs text-gray-700"><span class="font-semibold">Stock:</span> {{ modalProducto.stock }}</div>
                                    <div v-if="modalProducto.codigo" class="text-xs text-gray-700"><span class="font-semibold">Código:</span> {{ modalProducto.codigo }}</div>
                                    <div v-if="modalProducto.fecha_inicio" class="text-xs text-gray-700"><span class="font-semibold">Fecha Inicio:</span> {{ modalProducto.fecha_inicio }}</div>
                                    <div v-if="modalProducto.fecha_fin" class="text-xs text-gray-700"><span class="font-semibold">Fecha Fin:</span> {{ modalProducto.fecha_fin }}</div>
                                    
                                    <div v-if="modalProducto.tienda_nombre" class="text-xs text-gray-700"><span class="font-semibold">Tienda:</span> {{ modalProducto.tienda_nombre }}</div>
                                    <div v-if="modalProducto.tienda_id" class="text-xs text-gray-700"><span class="font-semibold">ID Tienda:</span> {{ modalProducto.tienda_id }}</div>
                                    <div v-if="modalProducto.limite_por_usuario" class="text-xs text-gray-700"><span class="font-semibold">Límite por usuario:</span> {{ modalProducto.limite_por_usuario }}</div>
                                    <div v-if="modalProducto.tipo" class="text-xs text-gray-700"><span class="font-semibold">Tipo:</span> {{ modalProducto.tipo }}</div>
                                    <div v-if="modalProducto.activo !== undefined" class="text-xs text-gray-700"><span class="font-semibold">Activo:</span> {{ modalProducto.activo ? 'Sí' : 'No' }}</div>
                                </div>
                                <p v-if="modalProducto.descripcion" class="text-gray-600 mb-2 text-center">{{ modalProducto.descripcion }}</p>
                            </div>
                        </div>
                    </div>
                </template>
                <template v-else>
                    <div class="text-center text-gray-500 py-8">
                        No hay productos para mostrar.
                    </div>
                </template>
            </div>
        </div>

    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

// Recibe los resultados desde el backend como prop
const props = defineProps({
  tienda: Object,
  productos: {
    type: Array,
    default: () => []
  },
});

import { ref } from 'vue';

const showModal = ref(false);
const modalProducto = ref({});

function verDetalles(producto) {
  modalProducto.value = producto;
  showModal.value = true;
}
function closeModal() {
  showModal.value = false;
  modalProducto.value = {};
}
// Agregar esta computed property
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
