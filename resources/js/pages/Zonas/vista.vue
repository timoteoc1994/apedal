
<template>
    <Head title="Mapa de Zonas" />

    <AuthenticatedLayout>
        <template #header>
            Zonas
        </template>

        <div class="relative w-full h-[75vh] min-h-[400px]">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        

                        <!-- Grid de ciudades -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                            <div 
                                v-for="ciudad in ciudades" 
                                :key="ciudad.id"
                                @click="irACiudad(ciudad)"
                                class="group relative bg-gradient-to-br from-blue-50 to-indigo-100 rounded-xl p-6 border border-gray-200 hover:border-blue-300 transition-all duration-300 cursor-pointer hover:shadow-lg hover:scale-105 transform"
                            >
                                <!-- Icono de ciudad -->
                                <div class="flex items-center justify-center w-16 h-16 bg-blue-500 rounded-full mb-4 group-hover:bg-blue-600 transition-colors duration-300 mx-auto">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H9m0 0H5m0 0h4m6 0v-3.87a3.37 3.37 0 00-.94-2.61c-.26-.26-.61-.42-1.06-.42-.45 0-.8.16-1.06.42A3.37 3.37 0 0012 17.13V21m-9 0h4v-8a1 1 0 011-1h4a1 1 0 011 1v8"></path>
                                    </svg>
                                </div>

                                <!-- Nombre de la ciudad -->
                                <h4 class="text-xl font-semibold text-gray-800 text-center mb-2 group-hover:text-blue-700 transition-colors duration-300">
                                    {{ ciudad.name }}
                                </h4>

                                <!-- Indicador de hover -->
                                <div class="absolute inset-0 bg-blue-500 bg-opacity-0 group-hover:bg-opacity-5 rounded-xl transition-all duration-300"></div>
                                
                                <!-- Flecha -->
                                <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Mensaje si no hay ciudades -->
                        <div v-if="ciudades.length === 0" class="text-center py-12">
                            <div class="flex flex-col items-center">
                                <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H9m0 0H5m0 0h4m6 0v-3.87a3.37 3.37 0 00-.94-2.61c-.26-.26-.61-.42-1.06-.42-.45 0-.8.16-1.06.42A3.37 3.37 0 0012 17.13V21m-9 0h4v-8a1 1 0 011-1h4a1 1 0 011 1v8"></path>
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No hay ciudades disponibles</h3>
                                <p class="text-gray-500">Contacta al administrador para agregar ciudades.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';

// Definir props
const props = defineProps({
    ciudades: Array,
});

// Función para navegar a una ciudad específica
const irACiudad = (ciudad) => {
    // Puedes cambiar esta ruta según tu estructura de rutas
    router.visit(route('zonas.mapa', { ciudad: ciudad.name.toLowerCase() }));
    
    // O si prefieres usar el ID:
    // router.visit(route('zonas.ciudad', { id: ciudad.id }));
    
    // O si quieres pasar el nombre como parámetro:
    // router.visit(`/zonas/${ciudad.name.toLowerCase()}`);
};

// Función para formatear fecha
const formatearFecha = (fechaString) => {
    const fecha = new Date(fechaString);
    return fecha.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};
</script>