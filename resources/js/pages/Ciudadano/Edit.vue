<template>
  <div class="max-w-3xl mx-auto p-6 bg-white rounded-lg shadow-md">
    <div class="text-center mb-6">
      <h1 class="text-3xl font-semibold text-gray-900">Editar Ciudadano</h1>
      <p class="text-gray-600">Modifica la información del ciudadano</p>
    </div>

    <!-- Indicador de progreso -->
    <div class="my-4" v-if="form.processing">
      <div class="w-full h-2 bg-gray-200 rounded-full">
        <div class="h-2 bg-blue-500 rounded-full" style="width: 100%"></div>
      </div>
      <p class="text-center text-gray-600 mt-2">Procesando...</p>
    </div>

    <!-- Formulario -->
    <form @submit.prevent="submitForm">
      <!-- Nombre -->
      <div class="mb-6">
        <label for="name" class="block text-sm font-medium text-gray-700">Nombre Completo <span class="text-red-600">*</span></label>
        <input
          id="name"
          type="text"
          v-model="form.name"
          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
          :class="{ 'border-red-500': form.errors.name }"
          placeholder="Ingrese el nombre completo"
          required
        />
        <span v-if="form.errors.name" class="text-red-500 text-sm">{{ form.errors.name }}</span>
      </div>

      <!-- Teléfono -->
      <div class="mb-6">
        <label for="telefono" class="block text-sm font-medium text-gray-700">Teléfono</label>
        <input
          id="telefono"
          type="text"
          v-model="form.telefono"
          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
          :class="{ 'border-red-500': form.errors.telefono }"
          placeholder="Ej: +593 99 123 4567"
        />
        <span v-if="form.errors.telefono" class="text-red-500 text-sm">{{ form.errors.telefono }}</span>
      </div>

      <!-- Dirección -->
      <div class="mb-6">
        <label for="direccion" class="block text-sm font-medium text-gray-700">Dirección <span class="text-red-600">*</span></label>
        <textarea
          id="direccion"
          v-model="form.direccion"
          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
          :class="{ 'border-red-500': form.errors.direccion }"
          placeholder="Ingrese la dirección completa"
          rows="3"
          required
        ></textarea>
        <span v-if="form.errors.direccion" class="text-red-500 text-sm">{{ form.errors.direccion }}</span>
      </div>

      <!-- Ciudad -->
      <div class="mb-6">
        <label for="ciudad" class="block text-sm font-medium text-gray-700">Ciudad <span class="text-red-600">*</span></label>
        <select
          id="ciudad"
          v-model="form.ciudad"
          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
          :class="{ 'border-red-500': form.errors.ciudad }"
          required
        >
          <option value="">Seleccione una ciudad</option>
          <option v-for="city in cities" :key="city.id" :value="city.name">
            {{ city.name }}
          </option>
        </select>
        <span v-if="form.errors.ciudad" class="text-red-500 text-sm">{{ form.errors.ciudad }}</span>
      </div>

      <!-- Botones -->
      <div class="flex justify-between mt-8">
        <button
          type="button"
          @click="resetForm"
          class="bg-gray-300 text-gray-700 py-2 px-4 rounded-md"
          :disabled="form.processing"
        >
          Limpiar Formulario
        </button>

        <button
          type="submit"
          :disabled="form.processing || !isFormValid"
          class="bg-blue-500 hover:bg-blue-600 transition-colors duration-200 text-white py-2 px-4 rounded-md flex items-center"
        >
          <svg v-if="form.processing" class="animate-spin h-5 w-5 mr-2 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
          </svg>
          {{ form.processing ? 'Actualizando...' : 'Actualizar Ciudadano' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { useForm, usePage } from '@inertiajs/vue3'
import { ref, onMounted, computed } from 'vue'
import Swal from 'sweetalert2'

const props = defineProps({
  cities: Array,
  ciudadano: Object
})

const page = usePage()
const successMessage = ref('')

// Inicializar formulario
const form = useForm({
  name: props.ciudadano.name || '',
  telefono: props.ciudadano.telefono || '',
  direccion: props.ciudadano.direccion || '',
  ciudad: props.ciudadano.ciudad || ''
})

// Validación básica sin email ni password
const isFormValid = computed(() =>
  form.name && form.direccion && form.ciudad
)

// Enviar formulario
const submitForm = () => {
  successMessage.value = ''

  // Solo enviamos los campos necesarios
  const { name, telefono, direccion, ciudad } = form.data()

  form.put(route('ciudadano.update', props.ciudadano.id), {
    data: { name, telefono, direccion, ciudad },
    onError: (errors) => {
      let errorText = ''
      for (const key in errors) {
        errorText += `${key}: ${errors[key].join(', ')}\n`
      }

      Swal.fire({
        title: 'Errores de Validación',
        text: errorText,
        icon: 'error',
        confirmButtonText: 'Cerrar'
      })
    },
    onSuccess: () => {
      Swal.fire({
        title: '¡Éxito!',
        text: 'Ciudadano actualizado exitosamente',
        icon: 'success',
        confirmButtonText: 'Cerrar'
      })
    }
  })
}

// Limpiar formulario
const resetForm = () => {
  form.reset()
  successMessage.value = ''
}

onMounted(() => {
  if (page.props.flash && page.props.flash.successMessage) {
    successMessage.value = page.props.flash.successMessage
  }
})
</script>
