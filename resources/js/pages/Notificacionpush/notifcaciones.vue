<template>
    <Head title="Notificaciones push" />

    <AuthenticatedLayout>
        <template #header>
            Notificaciones push
        </template>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 border-b border-gray-200">
                <!-- Formulario para enviar notificación push -->
                <form @submit.prevent="enviarNotificacion" class="space-y-4 max-w-4xl mx-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block font-semibold mb-1">Enviar a <span class="text-red-500">*</span></label>
                            <select v-model="form.destinatario" class="w-full border rounded px-3 py-2" required>
                                <option value="">Selecciona el tipo de usuario</option>
                                <option value="todos">Todos</option>
                                <option value="asociacion">Asociación</option>
                                <option value="reciclador">Reciclador</option>
                                <option value="ciudadano">Ciudadano</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Selecciona a quién enviar la notificación.</p>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Título <span class="text-red-500">*</span></label>
                            <input v-model="form.title" type="text" class="w-full border rounded px-3 py-2" required placeholder="Título de la notificación">
                            <p class="text-xs text-gray-500 mt-1">El título que verá el usuario en la notificación.</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block font-semibold mb-1">Descripción <span class="text-red-500">*</span></label>
                            <textarea v-model="form.body" class="w-full border rounded px-3 py-2" required placeholder="Mensaje o descripción"></textarea>
                            <p class="text-xs text-gray-500 mt-1">El mensaje principal de la notificación.</p>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Ruta/URL (opcional)</label>
                            <input v-model="form.route" type="text" class="w-full border rounded px-3 py-2" placeholder="/ruta_destino">
                            <p class="text-xs text-gray-500 mt-1">Ruta interna o URL a la que navegará la app al tocar la notificación.</p>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Imagen (opcional)</label>
                            <input v-model="form.image" type="url" class="w-full border rounded px-3 py-2" placeholder="https://ejemplo.com/imagen.jpg">
                            <p class="text-xs text-gray-500 mt-1">URL de una imagen para mostrar en la notificación (algunos dispositivos la soportan).</p>
                        </div>
                    </div>
                    <div class="pt-4">
                        <button
                            type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="enviando"
                        >
                            <span v-if="enviando">Enviando notificación...</span>
                            <span v-else>Enviar notificación</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3';
import { reactive } from 'vue';

// Estado del formulario
const form = reactive({
    destinatario: '', // Puede ser 'asociacion', 'reciclador' o 'ciudadano'
    title: '',
    body: '',
    route: '',
    solicitud_id: '',
    image: ''
});

import axios from 'axios';
import { ref } from 'vue';

const enviando = ref(false);

// Función para enviar la notificación al backend
async function enviarNotificacion() {
    enviando.value = true;
    try {
        const { data } = await axios.post('/notificaciones/enviar', {
            destinatario: form.destinatario,
            title: form.title,
            body: form.body,
            route: form.route,
            image: form.image
        });
        if (data.success) {
            alert('✅ ' + data.message);
            // Limpiar formulario si quieres
            form.destinatario = '';
            form.title = '';
            form.body = '';
            form.route = '';
            form.image = '';
        } else {
            alert('❌ ' + data.message);
        }
    } catch (error) {
        if (error.response && error.response.data && error.response.data.errors) {
            // Mostrar errores de validación
            const errores = Object.values(error.response.data.errors).flat().join('\n');
            alert('Errores de validación:\n' + errores);
        } else {
            alert('Ocurrió un error al enviar la notificación.');
        }
    } finally {
        enviando.value = false;
    }
}
</script>
