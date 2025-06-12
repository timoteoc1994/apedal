<template>
  <div class="max-w-3xl mx-auto p-6 bg-white rounded-lg shadow-md">
    <div class="text-center mb-6">
      <h1 class="text-3xl font-semibold text-gray-900">Crear Ciudadano</h1>
      <p class="text-gray-600">Complete la información para registrar un nuevo ciudadano</p>
    </div>

    <!-- Progress indicator -->
    <div class="my-4" v-if="form.processing">
      <div class="w-full h-2 bg-gray-200 rounded-full">
        <div class="h-2 bg-blue-500 rounded-full" style="width: 100%"></div>
      </div>
      <p class="text-center text-gray-600 mt-2">Procesando...</p>
    </div>

    <!-- Alert messages -->
    <div v-if="successMessage" class="bg-green-100 p-4 rounded-lg mb-4 text-green-800">
      <div class="flex items-center">
        <span class="material-icons text-2xl">check_circle</span>
        <div class="ml-2">
          <strong>¡Éxito!</strong>
          <p>{{ successMessage }}</p>
        </div>
      </div>
    </div>

    <!-- Main Form -->
    <form @submit.prevent="submitForm">
      <!-- Información Personal -->
      <div class="mb-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Información Personal</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
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

          <div>
            <label for="telefono" class="block text-sm font-medium text-gray-700">Teléfono</label>
            <input
              id="telefono"
              type="tel"
              v-model="form.telefono"
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              :class="{ 'border-red-500': form.errors.telefono }"
              placeholder="Ej: +593 99 123 4567"
            />
            <span v-if="form.errors.telefono" class="text-red-500 text-sm">{{ form.errors.telefono }}</span>
          </div>
        </div>
      </div>

      <!-- Ubicación -->
      <div class="mb-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Información de Ubicación</h3>
        <div class="mb-4">
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

        <div class="mb-4">
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
      </div>

      <!-- Cuenta -->
      <div class="mb-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Información de Cuenta</h3>
        <div class="mb-4">
          <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico <span class="text-red-600">*</span></label>
          <input
            id="email"
            type="email"
            v-model="form.email"
            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            :class="{ 'border-red-500': form.errors.email }"
            placeholder="ejemplo@correo.com"
            required
          />
          <span v-if="form.errors.email" class="text-red-500 text-sm">{{ form.errors.email }}</span>
        </div>

        <div class="mb-4">
          <label for="password" class="block text-sm font-medium text-gray-700">Contraseña <span class="text-red-600">*</span></label>
          <div class="relative">
            <input
              id="password"
              :type="showPassword ? 'text' : 'password'"
              v-model="form.password"
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              :class="{ 'border-red-500': form.errors.password }"
              placeholder="Mínimo 8 caracteres"
              required
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute right-3 top-1/2 transform -translate-y-1/2 text-xl"
            >
              {{ showPassword ? '👁️' : '👁️‍🗨️' }}
            </button>
          </div>
          <span v-if="form.errors.password" class="text-red-500 text-sm">{{ form.errors.password }}</span>
        </div>
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
          {{ form.processing ? 'Creando...' : 'Crear Ciudadano' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { useForm, usePage } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import Swal from 'sweetalert2'

const props = defineProps({
  cities: Array,
})

const page = usePage()
const showPassword = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

// Captura mensaje flash (si viene de redirect con with)
onMounted(() => {
  if (page.props.flash && page.props.flash.successMessage) {
    successMessage.value = page.props.flash.successMessage
  }
})

// Formulario
const form = useForm({
  name: '',
  telefono: '',
  direccion: '',
  ciudad: '',
  email: '',
  password: '',
  logo_url: '',
  role: 'ciudadano', // Se agrega el campo role con el valor predeterminado
})

// Validación
const isFormValid = computed(() =>
  form.name && form.direccion && form.ciudad && form.email && form.password
)

// Enviar formulario
const submitForm = () => {
  errorMessage.value = ''
  successMessage.value = ''

  form.post(route('ciudadano.create'), {
    preserveScroll: true,
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
        text: 'Ciudadano creado exitosamente',
        icon: 'success',
        confirmButtonText: 'Cerrar'
      })
    }
  })
}

// Limpiar
const resetForm = () => {
  form.reset()
  successMessage.value = ''
  errorMessage.value = ''
}
</script>

<style scoped>
/* Usamos Tailwind CSS */
</style>
