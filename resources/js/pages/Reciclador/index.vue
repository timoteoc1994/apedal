<template>
  <Head title="Recicladores" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="bg-white p-5 relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min">
        <div class="container mx-auto p-6">
          <h1 class="text-3xl font-semibold text-center text-gray-900 mb-6">
            Listado de Recicladores
          </h1>

          <!-- Buscar y Crear -->
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6 w-full">
            <input v-model="search" type="text" placeholder="Buscar reciclador..."
              class="w-full md:flex-1 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white" />

            <button class="w-full md:w-auto bg-green-500 text-white py-2 px-4 rounded-lg shadow-sm hover:bg-green-600"
              @click="createReciclador">
              Crear Reciclador
            </button>
          </div>

          <!-- Mensaje cuando no hay recicladores -->
          <div v-if="recicladoresFiltrados.length === 0" class="bg-red-100 text-center p-4 rounded-md mb-4">
            <p class="text-red-600 text-lg">No hay recicladores disponibles. Por favor, agrega un reciclador.</p>
          </div>

          <!-- Tabla de recicladores -->
          <table v-else class="min-w-full bg-white shadow-md rounded-lg">
            <thead>
              <tr>
                <th class="px-4 py-2 text-left border-b font-semibold text-gray-700">Nombre</th>
                <th class="px-4 py-2 text-left border-b font-semibold text-gray-700">Teléfono</th>
                <th class="px-4 py-2 text-left border-b font-semibold text-gray-700">Ciudad</th>
                <th class="px-4 py-2 text-left border-b font-semibold text-gray-700">Estado</th>
                <th class="px-4 py-2 text-left border-b font-semibold text-gray-700">Asociación</th> <!-- Nueva columna para Asociación -->
                <th class="px-4 py-2 text-left border-b font-semibold text-gray-700">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="reciclador in recicladoresFiltrados" :key="reciclador.id">
                <td class="px-4 py-2 border-b text-gray-600">{{ reciclador.name }}</td>
                <td class="px-4 py-2 border-b text-gray-600">{{ reciclador.telefono }}</td>
                <td class="px-4 py-2 border-b text-gray-600">{{ reciclador.ciudad }}</td>
                <td class="px-4 py-2 border-b text-gray-600">{{ reciclador.estado }}</td>
                <td class="px-4 py-2 border-b text-gray-600">{{ reciclador.asociacion?.name || 'No asignada' }}</td> <!-- Mostrar Asociación -->
                <td class="px-4 py-2 border-b flex gap-4">
                  <button class="bg-blue-500 text-white py-1 px-4 rounded-lg shadow-sm hover:bg-blue-600"
                    @click="editReciclador(reciclador.id)">
                    Editar
                  </button>
                  <button class="bg-red-500 text-white py-1 px-4 rounded-lg shadow-sm hover:bg-red-600"
                    @click="deleteReciclador(reciclador.id)">
                    Eliminar
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { usePage, router, Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Swal from 'sweetalert2';
import axios from 'axios';
import type { BreadcrumbItem } from '@/types';

// Props
const props = defineProps<{
  recicladores: any[]; // Asegúrate de que `recicladores` sea un array de objetos
}>();

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Recicladores', href: '/recicladores' },
];

// Reactive state
const recicladoresLocal = ref([...props.recicladores]);
const search = ref('');

// Watch prop changes
watch(
  () => props.recicladores,
  (newVal) => {
    recicladoresLocal.value = [...newVal];
  }
);

// Computed: filtrar por nombre
const recicladoresFiltrados = computed(() =>
  recicladoresLocal.value.filter((r) =>
    r.name.toLowerCase().includes(search.value.toLowerCase())
  )
);

// Navegación
const createReciclador = () => {
  router.visit('/crear-reciclador');
};

const editReciclador = (id: number) => {
  router.visit(`/recicladores/${id}/editar`);
};

const deleteReciclador = async (id: number) => {
  const confirmation = await Swal.fire({
    title: '¿Estás seguro?',
    text: 'Esta acción no se puede deshacer.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Eliminar',
    cancelButtonText: 'Cancelar',
  });

  if (confirmation.isConfirmed) {
    try {
      const response = await axios.delete(`/recicladores/${id}`);
      if (response.data.success) {
        Swal.fire('¡Eliminado!', 'El reciclador ha sido eliminado.', 'success');
        recicladoresLocal.value = recicladoresLocal.value.filter((r) => r.id !== id);
      } else {
        Swal.fire('Error', 'Hubo un problema al eliminar el reciclador.', 'error');
      }
    } catch (error) {
      console.error('Error al eliminar reciclador:', error);
      Swal.fire('Error', 'Hubo un problema al eliminar el reciclador.', 'error');
    }
  }
};
</script>

<style scoped>
/* No se necesita CSS adicional, Tailwind cubre el estilo */
</style>
