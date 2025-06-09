<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

// Props con tipado
const { versions } = defineProps<{
    versions: Array<{
        id: number;
        platform: 'android' | 'ios';
        min_version: string;
        latest_version: string;
        update_url: string;
    }>;
}>();

// Breadcrumbs
const breadcrumbs = [{ title: 'Versiones', href: '/versiones' }];

// Estado para edición/creación
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

const platformsExist = computed(() =>
    versions.map(v => v.platform)
);
</script>

<template>
    <Head title="Versiones de la App" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 bg-white dark:bg-gray-900 shadow rounded-lg">
            <h1 class="text-2xl font-bold mb-4">Versiones de la App</h1>

            <div class="mb-4 flex gap-2">
                <template v-for="platform in platforms" :key="platform">
                    <button
                        v-if="!platformsExist.includes(platform)"
                        @click="startCreate(platform)"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                    >
                        Crear versión {{ platform }}
                    </button>
                </template>
            </div>

            <table class="w-full table-auto border border-gray-200 dark:border-gray-700">
                <thead class="bg-gray-100 dark:bg-gray-800 text-left">
                    <tr>
                        <th class="p-2">Plataforma</th>
                        <th class="p-2">Versión Mínima</th>
                        <th class="p-2">Última Versión</th>
                        <th class="p-2">URL de Actualización</th>
                        <th class="p-2">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Crear nueva versión -->
                    <tr v-if="creating" class="border-t dark:border-gray-700">
                        <td class="p-2 font-bold">{{ formData.platform }}</td>
                        <td class="p-2"><input v-model="formData.min_version" class="input" /></td>
                        <td class="p-2"><input v-model="formData.latest_version" class="input" /></td>
                        <td class="p-2"><input v-model="formData.update_url" class="input" /></td>
                        <td class="p-2">
                            <button @click="saveCreate" class="text-green-600 font-bold mr-2">Guardar</button>
                            <button @click="creating = false" class="text-gray-600">Cancelar</button>
                        </td>
                    </tr>

                    <!-- Lista de versiones -->
                    <tr v-for="version in versions" :key="version.id" class="border-t dark:border-gray-700">
                        <td class="p-2">{{ version.platform }}</td>
                        <td class="p-2">
                            <input v-if="editing === version.id" v-model="formData.min_version" class="input" />
                            <span v-else>{{ version.min_version }}</span>
                        </td>
                        <td class="p-2">
                            <input v-if="editing === version.id" v-model="formData.latest_version" class="input" />
                            <span v-else>{{ version.latest_version }}</span>
                        </td>
                        <td class="p-2">
                            <input v-if="editing === version.id" v-model="formData.update_url" class="input" />
                            <span v-else>{{ version.update_url }}</span>
                        </td>
                        <td class="p-2">
                            <div v-if="editing === version.id">
                                <button @click="saveEdit(version.id)" class="text-green-600 font-bold mr-2">Guardar</button>
                                <button @click="editing = null" class="text-gray-600">Cancelar</button>
                            </div>
                            <div v-else>
                                <button @click="startEdit(version)" class="text-blue-600">Editar</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>

<style scoped>
.input {
    @apply border px-2 py-1 rounded w-full;
}
</style>
