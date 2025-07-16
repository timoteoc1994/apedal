<template>
  <Head title="Configuración de Puntos" />

  <AuthenticatedLayout>
    <template #header>
      Configuración de Puntos
    </template>

    <div class="bg-white overflow-hidden shadow-xl rounded-xl p-8 mt-6 max-w-2xl mx-auto">
      <!-- Tarjeta informativa -->
      <div class="mb-8 p-4 bg-indigo-50 border-l-4 border-indigo-400 rounded">
        <h2 class="font-bold text-indigo-700 mb-1">¿Cómo funciona la fecha de puntos promocionales?</h2>
        <p class="text-gray-700 text-sm">
          La <span class="font-semibold">fecha hasta</span> indica el último día en que los usuarios podrán recibir los <span class="font-semibold">puntos de registro promocional</span>. 
          Después de esa fecha, solo se otorgarán los puntos normales por reciclaje y referidos.
        </p>
      </div>

      <form @submit.prevent="guardar" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block font-medium mb-1 text-gray-700">Fecha hasta <span class="text-indigo-600">*</span></label>
            <input
              type="date"
              v-model="form.fecha_hasta"
              class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"
              required
            />
            <p class="text-xs text-gray-500 mt-1">Hasta esta fecha se entregan puntos promocionales por registro.</p>
          </div>
          <div>
            <label class="block font-medium mb-1 text-gray-700">Puntos registro promocional <span class="text-indigo-600">*</span></label>
            <input
              type="number"
              v-model="form.puntos_registro_promocional"
              min="0"
              class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"
              required
            />
            <p class="text-xs text-gray-500 mt-1">Puntos que recibe un usuario al registrarse antes de la fecha indicada.</p>
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block font-medium mb-1 text-gray-700">Puntos reciclado normal <span class="text-indigo-600">*</span></label>
            <input
              type="number"
              v-model="form.puntos_reciclado_normal"
              min="0"
              class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"
              required
            />
            <p class="text-xs text-gray-500 mt-1">Puntos por cada reciclaje realizado.</p>
          </div>
          <div>
            <label class="block font-medium mb-1 text-gray-700">Puntos reciclado referido <span class="text-indigo-600">*</span></label>
            <input
              type="number"
              v-model="form.puntos_reciclado_referido"
              min="0"
              class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"
              required
            />
            <p class="text-xs text-gray-500 mt-1">Puntos extra por reciclaje de un usuario referido.</p>
          </div>
        </div>
        <div class="flex justify-end">
          <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700 font-semibold shadow">
            Guardar
          </button>
        </div>
        <div v-if="success" class="mt-4 p-3 rounded bg-green-100 text-green-800 border border-green-300 text-center">
          {{ success }}
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

interface FlashProps {
  success?: string;
}
interface PageProps {
  flash?: FlashProps;
}

const props = defineProps({
  puntos: Object
});

const page = usePage<PageProps>();
const success = computed(() => page.props.flash?.success ?? '');

const form = ref({
  fecha_hasta: props.puntos?.fecha_hasta ?? '',
  puntos_registro_promocional: props.puntos?.puntos_registro_promocional ?? 0,
  puntos_reciclado_normal: props.puntos?.puntos_reciclado_normal ?? 0,
  puntos_reciclado_referido: props.puntos?.puntos_reciclado_referido ?? 0,
});

const guardar = () => {
  if (props.puntos) {
    router.put('/puntos', form.value);
  } else {
    router.post('/puntos', form.value);
  }
};
</script>