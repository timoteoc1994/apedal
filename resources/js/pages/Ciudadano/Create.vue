<template>
  <Head title="Ciudadanos" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 m-5 bg-white dark:bg-gray-900 rounded-xl shadow-md">
      <!-- Encabezado -->
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Crear Ciudadano</h1>
        <p class="text-gray-600 dark:text-gray-300">Complete la información para registrar un nuevo ciudadano</p>
      </div>

      <!-- Barra de progreso -->
      <div v-if="form.processing" class="mb-6">
        <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
          <div class="h-full bg-blue-500 animate-pulse" style="width: 100%"></div>
        </div>
        <p class="text-center text-sm text-gray-600 mt-2">Procesando...</p>
      </div>

      <!-- Mensaje de éxito -->
      <div v-if="successMessage" class="flex items-center gap-3 p-4 mb-6 text-green-800 bg-green-100 rounded-lg">
        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <div>
          <strong class="block font-semibold">¡Éxito!</strong>
          <p>{{ successMessage }}</p>
        </div>
      </div>

      <!-- Formulario -->
      <form @submit.prevent="submitForm" class="space-y-8">
        <!-- Información Personal -->
        <section>
          <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Información Personal</h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label for="name" class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre Completo <span class="text-red-600">*</span></label>
              <input id="name" type="text" v-model="form.name"
                     class="mt-1 w-full px-4 py-2 border rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                     :class="{ 'border-red-500': form.errors.name }"
                     placeholder="Ingrese el nombre completo" required />
              <span v-if="form.errors.name" class="text-red-500 text-sm">{{ form.errors.name }}</span>
            </div>
            <div>
              <label for="telefono" class="text-sm font-medium text-gray-700 dark:text-gray-300">Teléfono</label>
              <input id="telefono" type="tel" v-model="form.telefono"
                     class="mt-1 w-full px-4 py-2 border rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                     :class="{ 'border-red-500': form.errors.telefono }"
                     placeholder="+593 99 123 4567" />
              <span v-if="form.errors.telefono" class="text-red-500 text-sm">{{ form.errors.telefono }}</span>
            </div>
          </div>
        </section>

        <!-- Información de Ubicación -->
        <section>
          <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Ubicación</h2>
          <div class="space-y-4">
            <div>
              <label for="ciudad" class="text-sm font-medium text-gray-700 dark:text-gray-300">Ciudad <span class="text-red-600">*</span></label>
              <select id="ciudad" v-model="form.ciudad"
                      class="mt-1 w-full px-4 py-2 border rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                      :class="{ 'border-red-500': form.errors.ciudad }" required>
                <option value="">Seleccione una ciudad</option>
                <option v-for="city in cities" :key="city.id" :value="city.name">{{ city.name }}</option>
              </select>
              <span v-if="form.errors.ciudad" class="text-red-500 text-sm">{{ form.errors.ciudad }}</span>
            </div>
            <div>
              <label for="direccion" class="text-sm font-medium text-gray-700 dark:text-gray-300">Dirección <span class="text-red-600">*</span></label>
              <textarea id="direccion" rows="3" v-model="form.direccion"
                        class="mt-1 w-full px-4 py-2 border rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                        :class="{ 'border-red-500': form.errors.direccion }"
                        placeholder="Ingrese la dirección completa" required></textarea>
              <span v-if="form.errors.direccion" class="text-red-500 text-sm">{{ form.errors.direccion }}</span>
            </div>
          </div>
        </section>

        <!-- Cuenta -->
        <section>
          <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Cuenta</h2>
          <div class="space-y-4">
            <div>
              <label for="email" class="text-sm font-medium text-gray-700 dark:text-gray-300">Correo Electrónico <span class="text-red-600">*</span></label>
              <input id="email" type="email" v-model="form.email"
                     class="mt-1 w-full px-4 py-2 border rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                     :class="{ 'border-red-500': form.errors.email }"
                     placeholder="ejemplo@correo.com" required />
              <span v-if="form.errors.email" class="text-red-500 text-sm">{{ form.errors.email }}</span>
            </div>
            <div>
              <label for="password" class="text-sm font-medium text-gray-700 dark:text-gray-300">Contraseña <span class="text-red-600">*</span></label>
              <div class="relative">
                <input id="password" :type="showPassword ? 'text' : 'password'" v-model="form.password"
                       class="mt-1 w-full px-4 py-2 border rounded-md shadow-sm pr-10 focus:ring-blue-500 focus:border-blue-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                       :class="{ 'border-red-500': form.errors.password }"
                       placeholder="Mínimo 8 caracteres" required />
                <button type="button" @click="showPassword = !showPassword"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                  <svg v-if="showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                       viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M2.458 12C3.732 7.943 7.522 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.478 0-8.268-2.943-9.542-7z"/>
                  </svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                       viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.054 10.054 0 012.26-3.592M15 12a3 3 0 00-3-3m0 0a3 3 0 00-3 3m3-3L3 3m18 18L9.879 9.879"/>
                  </svg>
                </button>
              </div>
              <span v-if="form.errors.password" class="text-red-500 text-sm">{{ form.errors.password }}</span>
            </div>
          </div>
        </section>

        <!-- Botones -->
        <div class="flex flex-col sm:flex-row justify-between gap-4 mt-8">
          <button type="button" @click="resetForm"
                  class="bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 px-4 rounded-md transition"
                  :disabled="form.processing">
            Limpiar Formulario
          </button>

          <button type="submit"
                  class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md transition flex items-center justify-center"
                  :disabled="form.processing || !isFormValid">
            <svg v-if="form.processing" class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
            {{ form.processing ? 'Creando...' : 'Crear Ciudadano' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>


<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

import { useForm, usePage } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import Swal from 'sweetalert2'


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Ciudadanos',
        href: '/ciudadanos',
    },
];

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
