<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Swal from 'sweetalert2'
import axios from 'axios'
import { Dialog, DialogTrigger } from '@/components/ui/dialog'
import {
  PlusIcon,
  SearchIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  PencilIcon,
  TrashIcon,
} from 'lucide-vue-next'
import { type BreadcrumbItem } from '@/types'

// Props Inertia con paginación
const props = defineProps<{
  ciudadanos: {
    data: any[]
    total: number
    per_page: number
    current_page: number
    last_page: number
    path: string
  }
}>()

// Estado local y búsqueda
const ciudadanosLocal = ref([...props.ciudadanos.data])
const filtro = ref('')

// Al cambiar el filtro, consulta al backend con ?search=valor
watch(filtro, (val) => {
  router.get(
    props.ciudadanos.path,
    { search: val },
    { preserveState: true, replace: true }
  )
})

// Paginación
const currentPage = computed(() => props.ciudadanos.current_page)
const totalPages = computed(() => props.ciudadanos.last_page)
const displayedPages = computed(() =>
  Array.from({ length: totalPages.value }, (_, i) => i + 1)
)
const goToPage = (page: number) => {
  if (page >= 1 && page <= totalPages.value) {
    router.get(
      props.ciudadanos.path,
      { page, search: filtro.value },
      { preserveState: true, replace: true }
    )
  }
}

// Mantener data local actualizada al cambiar props
watch(
  () => props.ciudadanos.data,
  newVal => { ciudadanosLocal.value = [...newVal] }
)

// Breadcrumbs y navegación
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Ciudadanos', href: '/ciudadanos' },
]
const createCiudadano = () => {
    window.location.href = '/crear-ciudadano';
};
const editCiudadano = (id: number) => { router.get(`/ciudadanos/${id}/editar`) }

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
  })
  if (!isConfirmed) return

  try {
    await axios.delete(`/ciudadanos/${id}`)
    Swal.fire('¡Eliminado!', 'El ciudadano ha sido eliminado.', 'success')
    ciudadanosLocal.value = ciudadanosLocal.value.filter(c => c.id !== id)
  } catch {
    Swal.fire('Error', 'No se pudo eliminar. Intenta de nuevo.', 'error')
  }
}
</script>

<template>
  <Head title="Ciudadanos" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 p-4">
      <div class="rounded-xl bg-white dark:bg-gray-900 shadow-md border border-gray-200 dark:border-gray-700 p-8">
        <div class="max-w-6xl mx-auto">

          <!-- Header: Título + Botón Crear -->
          <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
              Listado de Ciudadanos
            </h1>
            <Dialog>
              <DialogTrigger as-child>
                <button
                  class="flex items-center rounded-lg bg-gradient-to-r from-purple-600 to-indigo-600 px-4 py-2 font-medium text-white shadow-md transition-transform hover:scale-105 hover:from-purple-700 hover:to-indigo-700"
                  @click="createCiudadano"
                >
                  <PlusIcon class="mr-2 h-5 w-5" />
                  Crear Ciudadano
                </button>
              </DialogTrigger>
            </Dialog>
          </div>

          <!-- Buscador full-width -->
          <div class="relative mb-8">
            <SearchIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
            <input
              v-model="filtro"
              type="text"
              placeholder="Buscar por nombre..."
              class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:border-purple-500 focus:ring focus:ring-purple-200 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:focus:ring-purple-800"
            />
          </div>

          <!-- Estado: sin datos -->
          <div
            v-if="!ciudadanosLocal.length"
            class="text-center text-lg text-red-600 bg-red-100 p-4 rounded-md mb-6"
          >
            No hay ciudadanos disponibles.
          </div>

          <!-- Tabla -->
          <div
            v-else
            class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700"
          >
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-800 sticky top-0">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                    Nombre
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                    Teléfono
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                    Dirección
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                    Ciudad
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                    Acciones
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-900">
                <tr
                  v-for="c in ciudadanosLocal"
                  :key="c.id"
                  class="odd:bg-gray-50 even:bg-white dark:odd:bg-gray-800 dark:even:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                >
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                    {{ c.name }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                    {{ c.telefono }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                    {{ c.direccion }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                    {{ c.ciudad }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium flex justify-end space-x-2">
                    <button
                      @click="editCiudadano(c.id)"
                      class="text-indigo-600 hover:text-indigo-900 transition"
                      title="Editar"
                    >
                      <PencilIcon class="h-5 w-5" />
                    </button>
                    <button
                      @click="deleteCiudadano(c.id)"
                      class="text-red-600 hover:text-red-900 transition"
                      title="Eliminar"
                    >
                      <TrashIcon class="h-5 w-5" />
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Paginación -->
          <div class="mt-6 flex flex-col items-center justify-between sm:flex-row">
            <div class="mb-4 text-sm text-gray-700 dark:text-gray-300 sm:mb-0">
              Mostrando
              <span class="font-medium">{{ (currentPage - 1) * props.ciudadanos.per_page + 1 }}</span>
              a
              <span class="font-medium">{{ Math.min(currentPage * props.ciudadanos.per_page, props.ciudadanos.total) }}</span>
              de
              <span class="font-medium">{{ props.ciudadanos.total }}</span>
              resultados
            </div>
            <div class="flex items-center space-x-2">
              <button
                @click="goToPage(currentPage - 1)"
                :disabled="currentPage === 1"
                class="rounded-md px-3 py-1 transition"
                :class="{
                  'opacity-50 cursor-not-allowed': currentPage === 1,
                  'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700': currentPage > 1
                }"
              >
                <ChevronLeftIcon class="h-5 w-5" />
              </button>
              <button
                v-for="page in displayedPages"
                :key="page"
                @click="goToPage(page)"
                class="rounded-md px-3 py-1 transition"
                :class="{
                  'bg-purple-600 text-white ring-2 ring-purple-600': currentPage === page,
                  'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700': currentPage !== page
                }"
              >
                {{ page }}
              </button>
              <button
                @click="goToPage(currentPage + 1)"
                :disabled="currentPage === totalPages"
                class="rounded-md px-3 py-1 transition"
                :class="{
                  'opacity-50 cursor-not-allowed': currentPage === totalPages,
                  'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700': currentPage < totalPages
                }"
              >
                <ChevronRightIcon class="h-5 w-5" />
              </button>
            </div>
          </div>

        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
/* Todo el styling está en Tailwind – no requiere CSS adicional */
</style>
