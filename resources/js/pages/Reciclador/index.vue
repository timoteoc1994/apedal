<template>
    <div class="container mx-auto p-6">
      <h1 class="text-3xl font-semibold text-center text-gray-900 mb-6">
        Listado de Recicladores
      </h1>
  
      <!-- Botón para Crear Reciclador -->
      <div class="flex justify-center mb-6">
        <button 
          class="bg-green-500 text-white py-2 px-4 rounded-lg shadow-sm hover:bg-green-600"
          @click="createReciclador"
        >
          Crear Reciclador
        </button>
      </div>
  
      <!-- Mensaje cuando no hay recicladores -->
      <div v-if="recicladoresLocal.length === 0" class="bg-red-100 text-center p-4 rounded-md mb-4">
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
            <th class="px-4 py-2 text-left border-b font-semibold text-gray-700">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="reciclador in recicladoresLocal" :key="reciclador.id">
            <td class="px-4 py-2 border-b text-gray-600">{{ reciclador.name }}</td>
            <td class="px-4 py-2 border-b text-gray-600">{{ reciclador.telefono }}</td>
            <td class="px-4 py-2 border-b text-gray-600">{{ reciclador.ciudad }}</td>
            <td class="px-4 py-2 border-b text-gray-600">{{ reciclador.estado }}</td>
            <td class="px-4 py-2 border-b flex gap-4">
              <button
                class="bg-blue-500 text-white py-1 px-4 rounded-lg shadow-sm hover:bg-blue-600"
                @click="editReciclador(reciclador.id)"
              >
                Editar
              </button>
              <button
                class="bg-red-500 text-white py-1 px-4 rounded-lg shadow-sm hover:bg-red-600"
                @click="deleteReciclador(reciclador.id)"
              >
                Eliminar
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </template>
  
  <script>
  import Swal from 'sweetalert2';
  import axios from 'axios';
  
  export default {
    props: {
      recicladores: Array,
    },
  
    data() {
      return {
        recicladoresLocal: [...this.recicladores], // Copia local de la prop
      };
    },
  
    watch: {
      // Mantener sincronizado si la prop cambia desde el padre
      recicladores(newVal) {
        this.recicladoresLocal = [...newVal];
      },
    },
  
    methods: {
      createReciclador() {
        this.$inertia.visit('/crear-reciclador');  // Cambia la ruta a la de reciclador
      },
  
      editReciclador(id) {
        // Lógica para editar un reciclador
        this.$inertia.visit(`/recicladores/${id}/editar`); // Cambia la ruta a la de reciclador
      },
  
      async deleteReciclador(id) {
        const confirmation = await Swal.fire({
          title: '¿Estás seguro?',
          text: "Esta acción no se puede deshacer.",
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
              Swal.fire({
                title: '¡Eliminado!',
                text: 'El reciclador ha sido eliminado.',
                icon: 'success',
                confirmButtonText: 'Cerrar',
              });
  
              // Filtrar el reciclador eliminado desde la copia local
              this.recicladoresLocal = this.recicladoresLocal.filter((reciclador) => reciclador.id !== id);
            } else {
              Swal.fire({
                title: 'Error',
                text: 'Hubo un problema al eliminar el reciclador.',
                icon: 'error',
                confirmButtonText: 'Cerrar',
              });
            }
          } catch (error) {
            console.error('Error al eliminar reciclador:', error);
            Swal.fire({
              title: 'Error',
              text: 'Hubo un problema al eliminar el reciclador.',
              icon: 'error',
              confirmButtonText: 'Cerrar',
            });
          }
        }
      },
    },
  };
  </script>
  
  <style scoped>
  /* Tailwind CSS ya está siendo utilizado a través de clases */
  </style>
  