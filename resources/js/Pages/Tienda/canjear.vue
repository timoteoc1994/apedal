<template>
    <Head title="Canjear" />

    <AuthenticatedLayout>
         <template #header>
            Canjear Códigos
        </template>

        <div class="max-w-4xl mx-auto">
            <!-- Barra de búsqueda principal -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <div class="space-y-4">
                    <!-- Título y descripción -->
                    <div class="text-center">
                        <h2 class="text-2xl font-bold text-gray-800 mb-2">Buscar para Canjear</h2>
                        <p class="text-gray-600">Busca por código de canje o email del cliente</p>
                    </div>

                    <!-- Solo búsqueda por código -->
                    <div class="flex justify-center mb-4">
                        <div class="flex bg-blue-100 rounded-lg p-1">
                            <button disabled class="px-6 py-2 rounded-md font-semibold bg-blue-500 text-white shadow-md cursor-default flex items-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                                </svg>
                                <span>Búsqueda por Código</span>
                            </button>
                        </div>
                    </div>

                    <!-- Campo de búsqueda -->
                    <div class="relative">
                        <div class="flex">
                            <!-- Prefijo para código -->
                            <div v-if="searchType === 'codigo'" class="flex items-center bg-blue-50 border border-r-0 border-blue-200 rounded-l-lg px-3">
                                <span class="text-blue-600 font-medium">adri-</span>
                            </div>
                            
                            <!-- Input de búsqueda -->
                            <input 
                                v-model="searchValue"
                                placeholder="Ingresa el código de canje"
                                class="flex-1 px-4 py-3 border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all duration-200 rounded-r-lg"
                                type="text"
                            >
                        </div>
                        
                        <!-- Botón de búsqueda -->
                        <button 
                            @click="buscar"
                            :disabled="!searchValue.trim()"
                            :class="[
                                'absolute right-2 top-1/2 transform -translate-y-1/2 px-4 py-2 rounded-md font-medium transition-all duration-200',
                                searchValue.trim() 
                                    ? 'bg-blue-500 text-white hover:bg-blue-600 shadow-md hover:shadow-lg' 
                                    : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                            ]"
                        >
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                <span>Buscar</span>
                            </div>
                        </button>
                    </div>

                    <!-- Información adicional -->
                    <div class="bg-blue-50 rounded-lg p-4 mt-4">
                        <div class="flex items-start space-x-3">
                            <svg class="w-5 h-5 text-blue-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="text-sm text-blue-700">
                                <p class="font-medium mb-1">Instrucciones:</p>
                                <ul class="space-y-1 text-blue-600">
                                    <li>• <strong>Código:</strong> Solo ingresa el código después de "adri-"</li>
                                    <li>• Los resultados aparecerán automáticamente después de la búsqueda</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resultados de búsqueda -->
            <div v-if="loading" class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                    <span class="ml-3 text-gray-600">Buscando...</span>
                </div>
            </div>

            <!-- Área de resultados -->
            <div v-if="searchResults.length > 0" class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Resultados de Búsqueda</h3>
                    <p class="text-gray-600 mt-1">{{ searchResults.length }} resultado(s) encontrado(s)</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-1 gap-6 p-6">
                    <div v-for="result in searchResults" :key="result.id" class="bg-gradient-to-br from-blue-50 to-white rounded-xl shadow p-5 flex flex-col h-full hover:scale-[1.02] transition-transform">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0">
                                <img
                                    v-if="result.producto && result.producto.url_imagen"
                                    :src="'storage/' + result.producto.url_imagen"
                                    class="h-20 w-20 rounded-lg object-cover border-2 border-blue-200 shadow"
                                    alt="Producto"
                                />
                                <div v-else class="h-16 w-16 rounded-lg bg-gray-200 flex items-center justify-center text-gray-400">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 21V7a2 2 0 00-2-2H6a2 2 0 00-2 2v14m16 0H4m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0V7m0 14a2 2 0 01-2 2H6a2 2 0 01-2-2" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <h4 class="text-lg font-bold text-blue-800 mb-1">{{ result.producto?.nombre || 'Producto desconocido' }}</h4>
                                <p class="text-sm text-gray-600 mb-1">{{ result.producto?.descripcion }}</p>
                                <div class="flex flex-wrap gap-2 mt-2">
                                    <span class="inline-block bg-blue-100 text-blue-700 text-xs px-2 py-1 rounded">Código: <span class="font-semibold">adri-{{ result.codigo }}</span></span>
                                    <span class="inline-block bg-green-100 text-green-700 text-xs px-2 py-1 rounded">Puntos: <span class="font-semibold">{{ result.puntos }}</span></span>
                                    <span class="inline-block bg-yellow-100 text-yellow-700 text-xs px-2 py-1 rounded">Estado: <span class="font-semibold">{{ result.estado }}</span></span>
                                </div>
                            </div>
                        </div>

                        <div v-if="result.estado === 'pendiente'" class="flex justify-end mt-auto">
                            <button 
                                @click="procesarCanje(result)"
                                class="bg-gradient-to-r from-green-400 to-green-600 text-white px-6 py-2 rounded-lg font-semibold shadow hover:from-green-500 hover:to-green-700 transition-colors"
                            >
                                Canjear
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mensaje cuando no hay resultados -->
            <div v-if="searchPerformed && searchResults.length === 0 && !loading" class="bg-white rounded-xl shadow-lg p-6">
                <div class="text-center py-8">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No se encontraron resultados</h3>
                    <p class="text-gray-600">Verifica que el {{ searchType === 'codigo' ? 'código' : 'email' }} sea correcto e intenta nuevamente</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';

// Variables reactivas

const searchType = ref('codigo');
const searchValue = ref('');
const loading = ref(false);
const searchPerformed = ref(false);

// Recibe los resultados desde el backend como prop
const props = defineProps({
  searchResults: {
    type: Array,
    default: () => []
  }
});
const searchResults = ref([]);

// Sincroniza la prop con la variable reactiva
onMounted(() => {
  searchResults.value = props.searchResults || [];
});
watch(
  () => props.searchResults,
  (newVal) => {
    searchResults.value = newVal || [];
  }
);

// Función para buscar usando Inertia
const buscar = () => {
    if (!searchValue.value.trim()) return;

    loading.value = true;
    searchPerformed.value = true;

    router.get(
        route('canje.index'),
        {
            tipo: searchType.value,
            query: searchType.value === 'codigo' 
                ? `adri-${searchValue.value.trim()}`
                : searchValue.value.trim()
        },
        {
            preserveState: true,
            replace: true,
            onFinish: () => {
                loading.value = false;
            }
        }
    );
};

const procesarCanje = (resultado) => {
    Swal.fire({
        title: "¿Estás seguro?",
        text: "Vas a canjear este código. No podrás revertir esto.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, canjear",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route("canje.store"), {
                canje_id: resultado.id,
                codigo: resultado.codigo,
            }, {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire('¡Canje realizado!', 'El canje se procesó correctamente.', 'success');
                    
                },
                onError: () => {
                    Swal.fire('Error', 'No se pudo realizar el canje', 'error');
                }
            });
        }
    });
};

</script>
