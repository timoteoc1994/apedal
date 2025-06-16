<template>
  <Head title="Recicladores" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div
        class="bg-white p-5 relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min"
      >
          <h1 class="text-3xl font-semibold text-center text-gray-900 mb-6">
            Crear Reciclador
          </h1>

          <form @submit.prevent="submitForm">
            <!-- Nombre y Teléfono -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label
                  for="name"
                  class="block text-sm font-medium text-gray-700"
                >
                  Nombre
                </label>
                <input
                  v-model="form.name"
                  id="name"
                  type="text"
                  required
                  class="mt-1 block w-full px-4 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                />
                <p v-if="errors.name" class="text-red-600 text-sm">
                  {{ errors.name[0] }}
                </p>
              </div>
              <div>
                <label
                  for="telefono"
                  class="block text-sm font-medium text-gray-700"
                >
                  Teléfono
                </label>
                <input
                  v-model="form.telefono"
                  id="telefono"
                  type="text"
                  required
                  class="mt-1 block w-full px-4 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                />
                <p v-if="errors.telefono" class="text-red-600 text-sm">
                  {{ errors.telefono[0] }}
                </p>
              </div>
            </div>

            <!-- Ciudad y Asociación -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
              <div>
                <label
                  for="ciudad"
                  class="block text-sm font-medium text-gray-700"
                >
                  Ciudad
                </label>
                <select
                  v-model="form.ciudad"
                  id="ciudad"
                  required
                  class="mt-1 block w-full px-4 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option disabled value="">Selecciona una ciudad</option>
                  <option v-for="ciudad in ciudades" :key="ciudad.id" :value="ciudad.name">
                    {{ ciudad.name }}
                  </option>
                </select>
                <p v-if="errors.ciudad" class="text-red-600 text-sm">
                  {{ errors.ciudad[0] }}
                </p>
              </div>

              <div>
                <label
                  for="asociacion_id"
                  class="block text-sm font-medium text-gray-700"
                >
                  Asociación
                </label>
                <select
                  v-model="form.asociacion_id"
                  id="asociacion_id"
                  required
                  class="mt-1 block w-full px-4 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option disabled value="">Selecciona una asociación</option>
                  <option v-for="a in asociaciones" :key="a.id" :value="a.id">
                    {{ a.name }}
                  </option>
                </select>
                <p v-if="errors.asociacion_id" class="text-red-600 text-sm">
                  {{ errors.asociacion_id[0] }}
                </p>
              </div>
            </div>

            <!-- Email y Contraseña -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
              <div>
                <label
                  for="email"
                  class="block text-sm font-medium text-gray-700"
                >
                  Correo Electrónico
                </label>
                <input
                  v-model="form.email"
                  id="email"
                  type="email"
                  required
                  class="mt-1 block w-full px-4 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                />
                <p v-if="errors.email" class="text-red-600 text-sm">
                  {{ errors.email[0] }}
                </p>
              </div>
              <div>
                <label
                  for="password"
                  class="block text-sm font-medium text-gray-700"
                >
                  Contraseña
                </label>
                <input
                  v-model="form.password"
                  id="password"
                  type="password"
                  required
                  class="mt-1 block w-full px-4 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                />
                <p v-if="errors.password" class="text-red-600 text-sm">
                  {{ errors.password[0] }}
                </p>
              </div>
            </div>

            <!-- Botón -->
            <div class="flex justify-center mt-6">
              <button
                type="submit"
                class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md shadow-sm focus:ring-2 focus:ring-indigo-500 hover:bg-indigo-700"
              >
                Crear Reciclador
              </button>
            </div>
          </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import Swal from 'sweetalert2';

// Props
interface Ciudad {
  id: number | string;
  name: string;
}
interface Asociacion {
  id: number | string;
  name: string;
}
const props = defineProps<{
  asociaciones: Asociacion[];
  ciudades: Ciudad[];
  error?: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Recicladores',
    href: '/recicladores',
  },
];

// Estado reactivo para el formulario y errores
const form = reactive({
  name: '',
  telefono: '',
  ciudad: '',
  asociacion_id: '',
  email: '',
  password: '',
  estado: 'Activo',
});

const errors = reactive<Record<string, string[]>>({});

// Si hay error inicial, mostrar alerta
onMounted(() => {
  if (props.error) {
    Swal.fire('¡Error!', props.error, 'error');
  }
});

async function submitForm() {
  try {
    await axios.post('/crear-reciclador', form);
    await Swal.fire('¡Éxito!', 'Reciclador creado exitosamente', 'success');
    // Navegar después de cerrar alerta
    window.location.href = '/recicladores'; // o usa $inertia si tienes acceso
  } catch (err: any) {
    console.error(err);
    if (err.response?.data?.errors) {
      // Reiniciar errores y asignar nuevos
      for (const key in errors) delete errors[key];
      Object.assign(errors, err.response.data.errors);
      Swal.fire('¡Error!', 'Revisa los campos marcados', 'error');
    } else {
      Swal.fire(
        '¡Error!',
        err.response?.data?.message || err.message || 'Error desconocido',
        'error'
      );
    }
  }
}
</script>

<style scoped>
/* Ya estás usando Tailwind para estilos */
</style>
