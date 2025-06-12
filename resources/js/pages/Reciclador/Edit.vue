<template>
    <div class="max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-md">
      <h1 class="text-3xl font-semibold text-center text-gray-900 mb-6">
        Editar Reciclador
      </h1>
  
      <form @submit.prevent="submitForm">
        <!-- Nombre y Teléfono -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700">
              Nombre
            </label>
            <input
              v-model="form.name"
              id="name"
              type="text"
              required
              class="mt-1 block w-full px-4 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500"
            />
            <p v-if="errors.name" class="text-red-600 text-sm">{{ errors.name[0] }}</p>
          </div>
          <div>
            <label for="telefono" class="block text-sm font-medium text-gray-700">
              Teléfono
            </label>
            <input
              v-model="form.telefono"
              id="telefono"
              type="text"
              required
              class="mt-1 block w-full px-4 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500"
            />
            <p v-if="errors.telefono" class="text-red-600 text-sm">{{ errors.telefono[0] }}</p>
          </div>
        </div>
  
        <!-- Ciudad y Asociación -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
          <div>
            <label for="ciudad" class="block text-sm font-medium text-gray-700">
              Ciudad
            </label>
            <select
              v-model="form.ciudad"
              id="ciudad"
              required
              class="mt-1 block w-full px-4 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500"
            >
              <option value="" disabled>Selecciona una ciudad</option>
              <option v-for="ciudad in ciudades" :key="ciudad.id" :value="ciudad.name">
                {{ ciudad.name }}
              </option>
            </select>
            <p v-if="errors.ciudad" class="text-red-600 text-sm">{{ errors.ciudad[0] }}</p>
          </div>
  
          <div>
            <label for="asociacion_id" class="block text-sm font-medium text-gray-700">
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
            <p v-if="errors.asociacion_id" class="text-red-600 text-sm">{{ errors.asociacion_id[0] }}</p>
          </div>
        </div>
  
        <!-- Estado -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
          <div>
            <label for="estado" class="block text-sm font-medium text-gray-700">
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
  </template>
  
  <script>
  import axios from 'axios';
  import Swal from 'sweetalert2';
  
  export default {
    props: {
      reciclador: Object,
      asociaciones: Array,
      ciudades: Array,  // Recibe las ciudades desde el servidor
    },
    data() {
      return {
        form: {
          name: this.reciclador.name,
          telefono: this.reciclador.telefono,
          ciudad: this.reciclador.ciudad,
          asociacion_id: this.reciclador.asociacion_id,
          estado: this.reciclador.estado,
        },
        errors: {},
      };
    },
    methods: {
      async submitForm() {
        try {
          const response = await axios.put(`/recicladores/${this.reciclador.id}`, this.form);
          Swal.fire('¡Éxito!', 'Reciclador actualizado exitosamente', 'success').then(() => {
            this.$inertia.visit('/recicladores');
          });
        } catch (err) {
          console.error(err);
          if (err.response?.data?.errors) {
            this.errors = err.response.data.errors;
            Swal.fire('¡Error!', 'Revisa los campos marcados', 'error');
          } else {
            Swal.fire('¡Error!', err.response?.data?.message || err.message, 'error');
          }
        }
      },
    },
  };
  </script>
  