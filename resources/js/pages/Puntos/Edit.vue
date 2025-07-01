<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

// Define el tipo para flash
interface FlashProps {
  success?: string;
}

// Define el tipo para los props de la página
interface PageProps {
  flash?: FlashProps;
}

const props = defineProps({
  puntos: Object
});

// Usa el tipo en usePage
const page = usePage<PageProps>();
const success = computed(() => page.props.flash?.success ?? '');

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Configuración de Puntos', href: '/puntos' },
];

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
<template>
  <Head title="Configuración de Puntos" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="bg-white p-5 relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min">
        <h2 class="text-2xl font-bold mb-6 text-gray-800 dark:text-white">Configuración de Puntos</h2>
        <div v-if="success" class="mb-4 p-3 rounded bg-green-100 text-green-800 border border-green-300">
          {{ success }}
        </div>
        <form @submit.prevent="guardar" class="space-y-4 max-w-xl">
          <div>
            <label class="block font-medium">Fecha hasta</label>
            <input type="date" v-model="form.fecha_hasta" class="input" />
          </div>
          <div>
            <label class="block font-medium">Puntos registro promocional</label>
            <input type="number" v-model="form.puntos_registro_promocional" class="input" />
          </div>
          <div>
            <label class="block font-medium">Puntos reciclado normal</label>
            <input type="number" v-model="form.puntos_reciclado_normal" class="input" />
          </div>
          <div>
            <label class="block font-medium">Puntos reciclado referido</label>
            <input type="number" v-model="form.puntos_reciclado_referido" class="input" />
          </div>
          <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
            Guardar
          </button>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.input {
  @apply w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500;
}
</style>