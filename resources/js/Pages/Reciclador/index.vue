<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Swal from 'sweetalert2'
import axios from 'axios'
import { Dialog, DialogTrigger } from '@/Components/ui/dialog'
import {
  PlusIcon,
  SearchIcon,
  PencilIcon,
  TrashIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
} from 'lucide-vue-next'
import { type BreadcrumbItem } from '@/types'

// Props con paginación
const props = defineProps<{
  recicladores: {
    data: Array<{
      id: number
      name: string
      telefono: string
      ciudad: string
      estado: string
      asociacion?: { name: string }
    }>
    total: number
    per_page: number
    current_page: number
    last_page: number
    path: string
  }
}>()

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Recicladores', href: '/recicladores' },
]

// Estado local y búsqueda
const recicladoresLocal = ref([...props.recicladores.data])
const search = ref('')

// Buscar globalmente en backend
watch(search, (val) => {
  router.get(
    props.recicladores.path,
    { search: val },
    { preserveState: true, replace: true }
  )
})

// Paginación
const currentPage = computed(() => props.recicladores.current_page)
const totalPages = computed(() => props.recicladores.last_page)
const displayedPages = computed(() =>
  Array.from({ length: totalPages.value }, (_, i) => i + 1)
)
const goToPage = (page: number) => {
  if (page >= 1 && page <= totalPages.value) {
    router.get(
      props.recicladores.path,
      { page, search: search.value },
      { preserveState: true, replace: true }
    )
  }
}

// Sincronizar props → local
watch(
  () => props.recicladores.data,
  newVal => { recicladoresLocal.value = [...newVal] }
)

// Navegación
const createReciclador = () => {
  router.visit('/crear-reciclador');
};

const editReciclador = (id: number) => {
  router.get(`/recicladores/${id}/editar`)
}
const deleteReciclador = async (id: number) => {
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
    const { data } = await axios.delete(`/recicladores/${id}`)
    if (data.success) {
      Swal.fire('¡Eliminado!', 'El reciclador ha sido eliminado.', 'success')
      recicladoresLocal.value = recicladoresLocal.value.filter(r => r.id !== id)
    } else {
      throw new Error()
    }
  } catch {
    Swal.fire('Error', 'No se pudo eliminar. Intenta de nuevo.', 'error')
  }
}
</script>

<template>
  <Head title="Recicladores" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 p-4">
      <div class="rounded-xl bg-white dark:bg-gray-900 shadow-md border border-gray-200 dark:border-gray-700 p-8">
        <div class="max-w-6xl mx-auto">

          <!-- Header -->
          <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
              Listado de Recicladores
            </h1>
            <Dialog>
              <DialogTrigger as-child>
                <button
                  class="flex items-center rounded-lg bg-gradient-to-r from-purple-600 to-indigo-600 px-4 py-2 font-medium text-white shadow-md transition-transform hover:scale-105 hover:from-purple-700 hover:to-indigo-700"
                  @click="createReciclador"
                >
                  <PlusIcon class="mr-2 h-5 w-5" />
                  Crear Reciclador
                </button>
              </DialogTrigger>
            </Dialog>
          </div>

          <!-- Buscador -->
          <div class="relative mb-8">
            <SearchIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
            <input
              v-model="search"
              type="text"
              placeholder="Buscar reciclador..."
              class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:border-purple-500 focus:ring focus:ring-purple-200 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:focus:ring-purple-800"
            />
          </div>

          <!-- Sin datos -->
          <div
            v-if="!recicladoresLocal.length"
            class="text-center text-lg text-red-600 bg-red-100 p-4 rounded-md mb-6"
          >
            No hay recicladores disponibles. Por favor, agrega uno.
          </div>

          <!-- Tabla -->
          <div
            v-else
            class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700"
          >
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-800 sticky top-0">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Nombre</th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Teléfono</th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Ciudad</th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Estado</th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Asociación</th>
                  <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Acciones</th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-900">
                <tr
                  v-for="r in recicladoresLocal"
                  :key="r.id"
                  class="odd:bg-gray-50 even:bg-white dark:odd:bg-gray-800 dark:even:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                >
                  <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ r.name }}</td>
                  <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ r.telefono }}</td>
                  <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ r.ciudad }}</td>
                  <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ r.estado }}</td>
                  <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ r.asociacion?.name ?? 'No asignada' }}</td>
                  <td class="px-6 py-4 text-right text-sm font-medium flex justify-end space-x-2">
                    <button @click="editReciclador(r.id)" class="text-indigo-600 hover:text-indigo-900 transition" title="Editar">
                      <PencilIcon class="h-5 w-5" />
                    </button>
                    <button @click="deleteReciclador(r.id)" class="text-red-600 hover:text-red-900 transition" title="Eliminar">
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
              <span class="font-medium">{{ (currentPage - 1) * props.recicladores.per_page + 1 }}</span>
              a
              <span class="font-medium">{{ Math.min(currentPage * props.recicladores.per_page, props.recicladores.total) }}</span>
              de
              <span class="font-medium">{{ props.recicladores.total }}</span>
              resultados
            </div>

            <!-- Botones de página -->
            <div class="flex justify-center gap-2">
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
