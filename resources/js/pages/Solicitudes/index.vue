<!-- resources/js/Pages/Solicitudes/index.vue -->
<script setup lang="ts">
import { ref } from 'vue';
import { Head, usePage, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface Solicitud {
  id: number;
  user: { name: string } | null;
  asociacion: { nombre: string } | null;
  zona: { nombre: string } | null;
  reciclador: { name: string } | null;
  usuarios_disponibles: { id: number; email: string }[];
  usuarios_notificados: { id: number; email: string }[];
  fecha_limite_asignacion: string | null;
  fecha: string;
  hora_inicio: string;
  hora_fin: string;
  direccion: string;
  referencia: string | null;
  latitud: number;
  longitud: number;
  peso_total: number;
  imagen: string | null; // JSON: e.g. '["solicitudes/1749764515_0_24.jpg"]'
  estado: string;
  fecha_completado: string | null;
  comentarios: string | null;
  ciudad: string;
  es_inmediata: boolean;
  calificacion_reciclador: number | null;
  calificacion_ciudadano: number | null;
  peso_total_revisado: number | null;
  created_at: string | null;
  updated_at: string | null;
}

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

const { props } = usePage<{
  solicitudes: {
    data: Solicitud[];
    links: PaginationLink[];
  };
}>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Solicitudes', href: '/solicitudes' },
];

// Modal state
const showModal = ref(false);
const modalImages = ref<string[]>([]);

// Parse JSON in sol.imagen into URLs
function getImagenes(sol: Solicitud): string[] {
  if (!sol.imagen) return [];

  let arr: string[] = [];
  if (typeof sol.imagen === 'string') {
    try {
      arr = JSON.parse(sol.imagen);
    } catch {
      arr = [sol.imagen];
    }
  } else if (Array.isArray(sol.imagen)) {
    arr = sol.imagen;
  }

  return arr.map(path =>
    path.startsWith('http')
      ? path
      : `/storage/${path.replace(/^\/+/, '')}`
  );
}

// Open image modal for a solicitud
function openImageModal(sol: Solicitud) {
  modalImages.value = getImagenes(sol);
  showModal.value = true;
}

// Close modal
function closeModal() {
  showModal.value = false;
  modalImages.value = [];
}

// Debug
props.solicitudes.data.forEach(sol => {
  console.log(`Solicitud ID ${sol.id} → Usuario:`, sol.user?.name);
});
</script>

<template>
  <Head title="Solicitudes" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="px-4 sm:px-6 lg:px-8">
      <!-- Tabla de solicitudes -->
      <div class="mt-4 shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asociación</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Zona</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reciclador</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usu. Disponibles</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usu. Notificados</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Límite Asig.</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Inicio</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fin</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dirección</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Referencia</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lat</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lng</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peso tot.</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Imagen</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completado</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comentarios</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ciudad</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Inmediata</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Calif. Rec.</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Calif. Ciu.</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peso rev.</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creado</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actualizado</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="sol in props.solicitudes.data" :key="sol.id">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ sol.id }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sol.user?.name ?? '–' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sol.asociacion?.nombre ?? '–' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sol.zona?.nombre ?? '–' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sol.reciclador?.name ?? '–' }}</td>

              <td class="px-6 py-4 whitespace-nowrap">
                <template v-if="sol.usuarios_disponibles.length">
                  <span
                    v-for="u in sol.usuarios_disponibles"
                    :key="u.id"
                    class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full mr-1 mb-1"
                  >{{ u.email }}</span>
                </template>
                <span v-else class="text-gray-400">–</span>
              </td>

              <td class="px-6 py-4 whitespace-nowrap">
                <template v-if="sol.usuarios_notificados.length">
                  <span
                    v-for="u in sol.usuarios_notificados"
                    :key="u.id"
                    class="inline-block bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full mr-1 mb-1"
                  >{{ u.email }}</span>
                </template>
                <span v-else class="text-gray-400">–</span>
              </td>

              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sol.fecha_limite_asignacion ?? '–' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sol.fecha }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sol.hora_inicio }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sol.hora_fin }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sol.direccion }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sol.referencia ?? '–' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sol.latitud }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sol.longitud }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sol.peso_total }}</td>

              <!-- Imagen -->
              <td class="px-6 py-4 whitespace-nowrap text-center">
                <button
                  @click="openImageModal(sol)"
                  :disabled="!sol.imagen"
                  class="p-2 bg-gray-100 hover:bg-gray-200 rounded-full disabled:opacity-50"
                  :title="sol.imagen ? 'Ver imágenes' : 'Sin imágenes'"
                >
                       <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none"
           viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M3 7h4l2-3h6l2 3h4v12H3V7z" />
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 11a4 4 0 100 8 4 4 0 000-8z" />
      </svg>
                </button>
              </td>

              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sol.estado }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sol.fecha_completado ?? '–' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sol.comentarios ?? '–' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sol.ciudad }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sol.es_inmediata ? 'Sí' : 'No' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sol.calificacion_reciclador ?? '–' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sol.calificacion_ciudadano ?? '–' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sol.peso_total_revisado ?? '–' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sol.created_at ?? '–' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sol.updated_at ?? '–' }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div class="py-3 flex justify-center">
        <nav aria-label="Paginación" class="inline-flex -space-x-px">
          <Link
            v-for="link in props.solicitudes.links"
            :key="link.label"
            :href="link.url || '#'"
            class="px-3 py-1 border mx-0.5 rounded"
            :class="link.active
              ? 'bg-indigo-500 text-white'
              : 'bg-white text-gray-700 hover:bg-gray-100'"
            v-html="link.label"
          />
        </nav>
      </div>
    </div>

    <!-- Modal de imágenes -->
    <transition name="fade">
      <div
        v-if="showModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      >
        <div class="bg-white rounded-lg overflow-hidden max-w-3xl w-full">
          <div class="flex justify-between items-center px-4 py-2 border-b">
            <h3 class="text-lg font-medium">Imágenes de la solicitud</h3>
            <button @click="closeModal" class="text-gray-500 hover:text-gray-700">✕</button>
          </div>
          <div class="p-4 grid grid-cols-2 md:grid-cols-3 gap-4 max-h-[70vh] overflow-y-auto">
            <template v-if="modalImages.length">
              <img
                v-for="(img, idx) in modalImages"
                :key="idx"
                :src="img"
                alt="Solicitud imagen"
                class="w-full h-full object-cover rounded"
              />
            </template>
            <div v-else class="col-span-full text-center text-gray-500">
              No hay imágenes disponibles.
            </div>
          </div>
        </div>
      </div>
    </transition>
  </AppLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity .2s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
