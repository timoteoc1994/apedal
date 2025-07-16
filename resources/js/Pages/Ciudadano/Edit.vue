<template>
    <Head title="Editar Ciudadano" />

    <AuthenticatedLayout>
        <template #header>
            <Link class="text-indigo-600 hover:text-indigo-500" :href="route('ciudadano.index')">Ciudadanos/</Link>{{ form.name }}

        </template>

        <div class="bg-white shadow-xl rounded-xl p-8 mt-6 mx-auto max-w-7xl">
            <form @submit.prevent="submitForm" class="space-y-6">
                <div>
                    <label for="name" class="block text-gray-700 font-semibold mb-1">Nombre Completo <span class="text-red-600">*</span></label>
                    <input
                        id="name"
                        type="text"
                        v-model="form.name"
                        class="w-full rounded-lg border border-gray-300 py-3 px-4 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-gray-700 bg-white"
                        :class="{ 'border-red-500': form.errors.name }"
                        placeholder="Ingrese el nombre completo"
                        required
                        autocomplete="name"
                    />
                    <span v-if="form.errors.name" class="text-red-500 text-sm mt-2">{{ form.errors.name }}</span>
                </div>
                <div>
                    <label for="nickname" class="block text-gray-700 font-semibold mb-1">Apodo <span class="text-red-600">*</span></label>
                    <input
                        id="nickname"
                        type="text"
                        v-model="form.nickname"
                        class="w-full rounded-lg border border-gray-300 py-3 px-4 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-gray-700 bg-white"
                        :class="{ 'border-red-500': form.errors.nickname }"
                        placeholder="Ingrese un apodo (opcional)"
                        autocomplete="nickname"
                    />
                    <span v-if="form.errors.nickname" class="text-red-500 text-sm mt-2">{{ form.errors.nickname }}</span>
                </div>

                <div>
                    <label for="email" class="block text-gray-700 font-semibold mb-1">Correo Electrónico <span class="text-red-600">*</span></label>
                    <input
                        id="email"
                        type="email"
                        v-model="form.email"
                        class="w-full rounded-lg border border-gray-300 py-3 px-4 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-gray-700 bg-white"
                        :class="{ 'border-red-500': form.errors.email }"
                        placeholder="Ej: usuario@ejemplo.com"
                    />
                    <span v-if="form.errors.email" class="text-red-500 text-sm mt-2">{{ form.errors.email }}</span>
                </div>
                <div>
                    <label for="email_verified_at" class="block text-gray-700 font-semibold mb-1">Verificación de Correo Electrónico</label>
                    <div class="flex gap-2 items-center">
                        <input
                            id="email_verified_at"
                            type="date"
                            v-model="emailVerifiedDate"
                            class="w-full rounded-lg border border-gray-300 py-3 px-4 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-gray-700 bg-white"
                            :class="{ 'border-red-500': form.errors.email_verified_at }"
                            :max="today"
                        />
                        <button type="button" @click="setEmailVerifiedNull" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-2 rounded transition-all" :disabled="!form.email_verified_at">No verificado</button>
                        <button type="button" @click="setEmailVerifiedNow" class="bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded transition-all">Verificar ahora</button>
                    </div>
                    <span v-if="form.errors.email_verified_at" class="text-red-500 text-sm mt-2">{{ form.errors.email_verified_at }}</span>
                </div>

                

                <div>
                    <label for="telefono" class="block text-gray-700 font-semibold mb-1">Teléfono <span class="text-red-600">*</span></label>
                    <input
                        id="telefono"
                        type="text"
                        v-model="form.telefono"
                        class="w-full rounded-lg border border-gray-300 py-3 px-4 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-gray-700 bg-white"
                        :class="{ 'border-red-500': form.errors.telefono }"
                        placeholder="Ej: +593 99 123 4567"
                        autocomplete="tel"
                    />
                    <span v-if="form.errors.telefono" class="text-red-500 text-sm mt-2">{{ form.errors.telefono }}</span>
                </div>
                


                

                <div>
                    <label for="ciudad" class="block text-gray-700 font-semibold mb-1">Ciudad <span class="text-red-600">*</span></label>
                    <select
                        id="ciudad"
                        v-model="form.ciudad"
                        class="w-full rounded-lg border border-gray-300 py-3 px-4 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-gray-700 bg-white"
                        :class="{ 'border-red-500': form.errors.ciudad }"
                        required
                    >
                        <option value="">Seleccione una ciudad</option>
                        <option v-for="city in cities" :key="city.id" :value="city.name">
                            {{ city.name }}
                        </option>
                    </select>
                    <span v-if="form.errors.ciudad" class="text-red-500 text-sm mt-2">{{ form.errors.ciudad }}</span>
                </div>

                <div>
                    <label for="password" class="block text-gray-700 font-semibold mb-1">Contraseña</label>
                    <input
                        id="password"
                        type="password"
                        v-model="form.password"
                        class="w-full rounded-lg border border-gray-300 py-3 px-4 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-gray-700 bg-white"
                        :class="{ 'border-red-500': form.errors.password }"
                        placeholder="Ingrese una nueva contraseña (opcional)"
                        autocomplete="new-password"
                    />
                    <span v-if="form.errors.password" class="text-red-500 text-sm mt-2">{{ form.errors.password }}</span>
                </div>

                <div class="flex items-center gap-4 mt-6">
                   
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-6 rounded-lg shadow transition-all duration-300 flex items-center"
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
  </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { useForm, usePage, Link } from '@inertiajs/vue3'
import { ref, onMounted, computed } from 'vue'
import Swal from 'sweetalert2'


interface City {
  id: number;
  name: string;
}
interface Ciudadano {
  id: number;
  name?: string;
  nickname?: string;
  telefono?: string;
  email?: string;
  email_verification?: string;
  password?: string;
  ciudad?: string;
  id_usuario?: number;
}
const props = defineProps<{ cities?: City[]; ciudadano?: Ciudadano }>()

const page = usePage()
const successMessage = ref('')

// Inicializar formulario
const form = useForm({
  name: props.ciudadano?.name ?? '',
  telefono: props.ciudadano?.telefono ?? '',
  nickname: props.ciudadano?.nickname ?? '',
  email: props.ciudadano?.auth_user?.email ?? '',
  email_verified_at: props.ciudadano?.auth_user?.email_verified_at ?? null,
  password: '', // No se debe enviar el password si no se cambia
  ciudad: props.ciudadano?.ciudad ?? '',
  id_usuario: props.ciudadano?.auth_user?.id ?? null,
})

// Manejo de fecha para email_verified_at
import { computed as vueComputed } from 'vue'
const today = new Date().toISOString().split('T')[0]

// Computed para el input date (solo la parte de fecha)
const emailVerifiedDate = vueComputed({
  get() {
    if (!form.email_verified_at) return ''
    // Si es string tipo fecha completa, extraer solo la fecha
    return form.email_verified_at.substring(0, 10)
  },
  set(val: string) {
    if (!val) {
      form.email_verified_at = null
    } else {
      // Guardar como fecha completa con hora 00:00:00Z
      form.email_verified_at = val + 'T00:00:00.000000Z'
    }
  }
})

function setEmailVerifiedNull() {
  form.email_verified_at = null
}
function setEmailVerifiedNow() {
  const now = new Date()
  // Formato YYYY-MM-DDTHH:mm:ss.000000Z
  const pad = (n: number) => n.toString().padStart(2, '0')
  const fecha = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}.000000Z`
  form.email_verified_at = fecha
}



// Enviar formulario
const submitForm = () => {
  successMessage.value = ''

  if (!props.ciudadano) return;
  form.put(route('ciudadano.update', props.ciudadano.id), {
    // Quitar "data" porque useForm ya envía los datos correctos
    onError: (errors: Record<string, string | string[]>) => {
      let errorText = '';
      for (const key in errors) {
        const val = errors[key];
        errorText += `${key}: ${Array.isArray(val) ? val.join(', ') : val}\n`;
      }
      Swal.fire({
        title: 'Errores de Validación',
        text: errorText,
        icon: 'error',
        confirmButtonText: 'Cerrar',
      });
    },
    onSuccess: () => {
      Swal.fire({
        title: '¡Éxito!',
        text: 'Ciudadano actualizado exitosamente',
        icon: 'success',
        confirmButtonText: 'Cerrar',
      });
    },
  });
}


onMounted(() => {
  if (page.props.flash && typeof page.props.flash === 'object' && 'successMessage' in page.props.flash) {
    // @ts-ignore
    successMessage.value = page.props.flash.successMessage;
  }
});
</script>
