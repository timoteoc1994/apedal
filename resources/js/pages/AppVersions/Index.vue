<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { PencilIcon, XIcon, CheckIcon } from 'lucide-vue-next';

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

const platforms = ['android', 'ios'] as const;

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

    <div class="bg-white shadow-xl rounded-xl p-8 mt-6">
      <!-- Botones de crear versión -->
      <div class="flex gap-3 mb-6">
        <template v-for="platform in platforms" :key="platform">
          <button
            v-if="!platformsExist.includes(platform)"
            @click="startCreate(platform)"
            class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold py-2 px-6 rounded-lg shadow-lg transition-all duration-300 flex items-center gap-2 text-sm"
          >
            <span class="text-2xl">+</span> Crear versión {{ platform }}
          </button>
        </template>
      </div>

      <!-- Tabla -->
      <div class="overflow-x-auto">
        <table class="min-w-[700px] w-full text-sm text-left text-gray-700">
          <thead class="bg-gradient-to-r from-indigo-500 to-purple-500 text-white">
            <tr>
              <th class="px-6 py-4 font-semibold uppercase tracking-wider">Plataforma</th>
              <th class="px-6 py-4 font-semibold uppercase tracking-wider">Versión Mínima</th>
              <th class="px-6 py-4 font-semibold uppercase tracking-wider">Última Versión</th>
              <th class="px-6 py-4 font-semibold uppercase tracking-wider">URL de Actualización</th>
              <th class="px-6 py-4 text-right font-semibold uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <!-- Crear nueva versión -->
            <tr v-if="creating" class="hover:bg-indigo-50 transition border-b border-gray-100">
              <td class="px-6 py-4 font-medium">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 capitalize">
                  {{ formData.platform }}
                </span>
              </td>
              <td class="px-6 py-4">
                <input 
                  v-model="formData.min_version" 
                  class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-gray-700"
                  placeholder="Ej: 1.0.0"
                />
              </td>
              <td class="px-6 py-4">
                <input 
                  v-model="formData.latest_version" 
                  class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-gray-700"
                  placeholder="Ej: 1.2.0"
                />
              </td>
              <td class="px-6 py-4">
                <input 
                  v-model="formData.update_url" 
                  class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-gray-700"
                  placeholder="https://..."
                />
              </td>
              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button 
                    @click="saveCreate" 
                    class="bg-green-100 hover:bg-green-200 text-green-600 rounded-full p-2 transition"
                    title="Guardar"
                  >
                    <CheckIcon class="h-5 w-5" />
                  </button>
                  <button 
                    @click="creating = false" 
                    class="bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-full p-2 transition"
                    title="Cancelar"
                  >
                    <XIcon class="h-5 w-5" />
                  </button>
                </div>
              </td>
            </tr>

            <!-- Mensaje si no hay versiones -->
            <tr v-if="versions.length === 0 && !creating">
              <td colspan="5" class="px-6 py-6 text-center text-gray-400">
                No hay versiones configuradas.
              </td>
            </tr>

            <!-- Versiones existentes -->
            <tr v-for="version in versions" :key="version.id" class="hover:bg-indigo-50 transition border-b border-gray-100">
              <td class="px-6 py-4 font-medium">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium capitalize"
                      :class="version.platform === 'android' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'">
                  {{ version.platform }}
                </span>
              </td>

              <td class="px-6 py-4">
                <input 
                  v-if="editing === version.id" 
                  v-model="formData.min_version" 
                  class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-gray-700"
                />
                <span v-else class="font-mono text-sm bg-gray-100 px-2 py-1 rounded">{{ version.min_version }}</span>
              </td>

              <td class="px-6 py-4">
                <input 
                  v-if="editing === version.id" 
                  v-model="formData.latest_version" 
                  class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-gray-700"
                />
                <span v-else class="font-mono text-sm bg-gray-100 px-2 py-1 rounded">{{ version.latest_version }}</span>
              </td>

              <td class="px-6 py-4">
                <input 
                  v-if="editing === version.id" 
                  v-model="formData.update_url" 
                  class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-gray-700"
                />
                <span v-else class="text-sm text-gray-600 truncate max-w-xs block">{{ version.update_url }}</span>
              </td>

              <td class="px-6 py-4 text-right">
                <div v-if="editing === version.id" class="flex items-center justify-end gap-2">
                  <button 
                    @click="saveEdit(version.id)" 
                    class="bg-green-100 hover:bg-green-200 text-green-600 rounded-full p-2 transition"
                    title="Guardar"
                  >
                    <CheckIcon class="h-5 w-5" />
                  </button>
                  <button 
                    @click="editing = null" 
                    class="bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-full p-2 transition"
                    title="Cancelar"
                  >
                    <XIcon class="h-5 w-5" />
                  </button>
                </div>
                <div v-else class="flex items-center justify-end gap-2">
                  <button 
                    @click="startEdit(version)" 
                    class="bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-full p-2 transition"
                    title="Editar"
                  >
                    <PencilIcon class="h-5 w-5" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AuthenticatedLayout>
</template>