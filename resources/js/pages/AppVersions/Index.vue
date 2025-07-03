<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const { versions } = defineProps<{
  versions: Array<{
    id: number;
    platform: 'android' | 'ios';
    min_version: string;
    latest_version: string;
    update_url: string;
  }>;
}>();

const editing = ref<number | null>(null);
const creating = ref(false);

const formData = ref({
  platform: '',
  min_version: '',
  latest_version: '',
  update_url: '',
});

const platforms = ['android', 'ios'];

const startEdit = (version: typeof versions[number]) => {
  editing.value = version.id;
  formData.value = { ...version };
};

const saveEdit = (versionId: number) => {
  router.put(`/versiones/${versionId}`, formData.value, {
    onSuccess: () => editing.value = null,
  });
};

const startCreate = (platform: string) => {
  creating.value = true;
  formData.value = {
    platform,
    min_version: '',
    latest_version: '',
    update_url: '',
  };
};

const saveCreate = () => {
  router.post('/versiones', formData.value, {
    onSuccess: () => creating.value = false,
  });
};

const platformsExist = computed(() => versions.map(v => v.platform));
</script>

<template>
  <Head title="Versiones de la App" />

  <AuthenticatedLayout>
    <template #header>
      Versiones de la App
    </template>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
      <div class="p-6 border-b border-gray-200">
        <div class="max-w-6xl mx-auto">

          <!-- Encabezado -->
          <div class="flex justify-between items-center mb-6">
            <div class="flex gap-2">
              <template v-for="platform in platforms" :key="platform">
                <button
                  v-if="!platformsExist.includes(platform)"
                  @click="startCreate(platform)"
                  class="flex items-center rounded-lg bg-gradient-to-r from-purple-600 to-indigo-600 px-4 py-2 font-medium text-white shadow-md transition-transform hover:scale-105 hover:from-purple-700 hover:to-indigo-700"
                >
                  Crear versión {{ platform }}
                </button>
              </template>
            </div>
          </div>

          <!-- Tabla -->
          <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Plataforma</th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Versión Mínima</th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Última Versión</th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">URL de Actualización</th>
                  <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Acciones</th>
                </tr>
              </thead>
              <tbody class="bg-white">
                <!-- Crear nueva versión -->
                <tr v-if="creating" class="odd:bg-gray-50 even:bg-white hover:bg-gray-100">
                  <td class="px-6 py-4 font-bold text-gray-700">{{ formData.platform }}</td>
                  <td class="px-6 py-4"><input v-model="formData.min_version" class="input" /></td>
                  <td class="px-6 py-4"><input v-model="formData.latest_version" class="input" /></td>
                  <td class="px-6 py-4"><input v-model="formData.update_url" class="input" /></td>
                  <td class="px-6 py-4 text-right space-x-4">
                    <button @click="saveCreate" class="text-green-600 font-bold">Guardar</button>
                    <button @click="creating = false" class="text-gray-600">Cancelar</button>
                  </td>
                </tr>

                <!-- Versiones existentes -->
                <tr v-for="version in versions" :key="version.id"
                    class="odd:bg-gray-50 even:bg-white hover:bg-gray-100 transition-colors">
                  <td class="px-6 py-4 text-sm text-gray-700">{{ version.platform }}</td>

                  <td class="px-6 py-4 text-sm text-gray-700">
                    <input v-if="editing === version.id" v-model="formData.min_version" class="input" />
                    <span v-else>{{ version.min_version }}</span>
                  </td>

                  <td class="px-6 py-4 text-sm text-gray-700">
                    <input v-if="editing === version.id" v-model="formData.latest_version" class="input" />
                    <span v-else>{{ version.latest_version }}</span>
                  </td>

                  <td class="px-6 py-4 text-sm text-gray-700">
                    <input v-if="editing === version.id" v-model="formData.update_url" class="input" />
                    <span v-else>{{ version.update_url }}</span>
                  </td>

                  <td class="px-6 py-4 text-right text-sm font-medium">
                    <div v-if="editing === version.id" class="space-x-4">
                      <button @click="saveEdit(version.id)" class="text-green-600 font-bold">Guardar</button>
                      <button @click="editing = null" class="text-gray-600">Cancelar</button>
                    </div>
                    <div v-else>
                      <button @click="startEdit(version)" class="text-blue-600 hover:underline">Editar</button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
.input {
  @apply w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500;
}
</style>