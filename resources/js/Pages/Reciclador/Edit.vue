<template>
  <Head title="Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div
        class="bg-white p-5 relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min"
      >
        
          <h1 class="text-3xl font-semibold text-center text-gray-900 mb-6">
            Editar Reciclador
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
                  <option value="" disabled>Selecciona una ciudad</option>
                  <option
                    v-for="ciudad in ciudades"
                    :key="ciudad.id"
                    :value="ciudad.name"
                  >
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

            <!-- Estado -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
              <div>
                <label
                  for="estado"
                  class="block text-sm font-medium text-gray-700"
                >
                  Estado
                </label>
                <select
                  v-model="form.estado"
                  id="estado"
                  required
                  class="mt-1 block w-full px-4 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option value="Activo">Activo</option>
                  <option value="Inactivo">Inactivo</option>
                </select>
              </div>
            </div>

            <!-- Botón -->
            <div class="flex justify-center mt-6">
              <button
                type="submit"
                class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md shadow-sm focus:ring-2 focus:ring-indigo-500 hover:bg-indigo-700"
              >
                Actualizar Reciclador
              </button>
            </div>
          </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Swal from 'sweetalert2';
import axios from 'axios';
import type { BreadcrumbItem } from '@/types';

// Props recibidas desde el servidor
interface Ciudad {
  id: number;
  name: string;
}

interface Asociacion {
  id: number;
  name: string;
}

interface Reciclador {
  id: number;
  name: string;
  telefono: string;
  ciudad: string;
  asociacion_id: number;
  estado: string;
}

const props = defineProps<{
  reciclador: Reciclador;
  asociaciones: Asociacion[];
  ciudades: Ciudad[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Recicladores',
    href: '/recicladores',
  },
];

// Formulario reactivo
const form = reactive({
  name: props.reciclador.name,
  telefono: props.reciclador.telefono,
  ciudad: props.reciclador.ciudad,
  asociacion_id: props.reciclador.asociacion_id,
  estado: props.reciclador.estado,
});

// Objeto para errores de validación
const errors = reactive<Record<string, string[]>>({});

// Función para enviar el formulario
async function submitForm() {
  try {
    await axios.put(`/recicladores/${props.reciclador.id}`, form);
    await Swal.fire('¡Éxito!', 'Reciclador actualizado exitosamente', 'success');
    // Redirigir usando inertia
    window.location.href = '/recicladores';
  } catch (err: any) {
    if (err.response?.data?.errors) {
      Object.assign(errors, err.response.data.errors);
      await Swal.fire('¡Error!', 'Revisa los campos marcados', 'error');
    } else {
      await Swal.fire('¡Error!', err.response?.data?.message || err.message, 'error');
    }
  }
}
</script>
