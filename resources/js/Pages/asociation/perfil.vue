<template>
    <Head title="Perfil de la Asociación" />

    <AuthenticatedLayout>
        <template #header>
            <Link class="text-indigo-600 hover:text-indigo-500" :href="route('asociation.index')">Asociaciones</Link>
            / {{ asociation.asociacion.name }}
        </template>

        <div class="mx-auto mt-8 max-w-4xl space-y-8">
            <!-- Card principal -->
            <div class="flex flex-col items-center gap-8 rounded-xl bg-white p-8 shadow-xl md:flex-row">
                <!-- Avatar o logo -->
                <div class="flex-shrink-0">
                    <div
                        class="relative flex h-32 w-32 items-center justify-center overflow-hidden rounded-full text-5xl font-bold"
                        :style="{ backgroundColor: asociation.asociacion.color || '#6366f1', color: '#fff' }"
                    >
                        <img
                            v-if="asociation.asociacion.logo_url"
                            :src="asociation.asociacion.logo_url"
                            alt="Logo"
                            class="h-32 w-32 cursor-pointer rounded-full object-cover transition-transform hover:scale-105"
                            @click="abrirModalImagen(asociation.asociacion.logo_url)"
                        />
                        <span v-else>
                            {{ asociation.asociacion.name.charAt(0) }}
                        </span>
                    </div>
                </div>
                <!-- Info principal -->
                <div class="flex-1">
                    <h2 class="mb-2 flex items-center gap-2 text-3xl font-bold text-gray-800">
                        {{ asociation.asociacion.name }}
                        <span
                            v-if="asociation.asociacion.verified"
                            class="ml-2 inline-flex items-center rounded bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-800"
                        >
                            Verificada
                        </span>
                    </h2>
                    <p class="mb-2 text-gray-600">{{ asociation.asociacion.descripcion }}</p>
                    <div class="mb-2 flex flex-wrap gap-4">
                        <div class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M17.657 16.657L13.414 12.414a2 2 0 00-2.828 0l-4.243 4.243M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                />
                            </svg>
                            <span>{{ asociation.asociacion.city }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                />
                            </svg>
                            <span>{{ asociation.email }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m0 4v2m0 4v2m0 4v2" />
                            </svg>
                            <span>{{ asociation.asociacion.number_phone }}</span>
                        </div>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <span class="inline-block rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                            ID: {{ asociation.id }}
                        </span>
                    </div>
                </div>
                <Link :href="route('asociation.index.recicladores', asociation.id)" >
                    <div
                        class="flex cursor-pointer items-center gap-2 rounded-xl border-2 border-emerald-200 bg-gradient-to-r from-emerald-400 to-emerald-600 px-4 py-2 text-white shadow transition-all duration-200 hover:scale-105 hover:shadow-lg"
                        title="Ver recicladores de la asociación"
                    >
                        <img src="/storage/images_plataforma/reciclador-icono.png" width="100px" alt="" />
                        <div class="flex flex-col items-start">
                            <span class="text-xl font-extrabold text-white drop-shadow">{{ asociation.numero_recicladores ?? 0 }}</span>
                            <span class="text-xs font-bold uppercase tracking-widest text-white/90">Recicladores</span>
                            <span class="mt-0.5 text-[10px] text-white/80">Ver todos</span>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Imágenes referenciales con modal -->
            <div v-if="imagenesReferenciales.length" class="rounded-xl bg-white p-6 shadow">
                <h3 class="mb-4 text-lg font-semibold text-indigo-700">Imágenes Referenciales</h3>
                <div class="flex flex-wrap gap-4">
                    <img
                        v-for="(img, i) in imagenesReferenciales"
                        :key="i"
                        :src="`/storage/${img}`"
                        alt="Imagen referencial"
                        class="h-32 w-40 cursor-pointer rounded-lg border object-cover transition-transform hover:scale-105"
                        @click="abrirModalImagen(`/storage/${img}`)"
                    />
                </div>
                <!-- Modal de imagen ampliada -->
                <transition name="fade">
                    <div
                        v-if="modalImagenAbierta"
                        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-70"
                        @click.self="cerrarModalImagen"
                    >
                        <div class="relative w-full max-w-3xl p-4">
                            <button
                                @click="cerrarModalImagen"
                                class="absolute right-2 top-2 rounded-full bg-black bg-opacity-50 p-2 text-white hover:bg-opacity-80 focus:outline-none"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                            <img
                                :src="imagenModalUrl"
                                alt="Imagen ampliada"
                                class="mx-auto max-h-[70vh] rounded-lg border-4 border-white shadow-lg"
                            />
                        </div>
                    </div>
                </transition>
            </div>

            <!-- Secciones adicionales -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <!-- Días y horarios -->
                <div class="rounded-xl bg-white p-6 shadow">
                    <h3 class="mb-2 text-lg font-semibold text-indigo-700">Días y Horarios de Atención</h3>
                    <div class="mb-2">
                        <span class="font-medium text-gray-700">Días:</span>
                        <span class="ml-2 text-gray-600">
                            <template v-for="(dia, i) in diasAtencion" :key="i">
                                <span class="mb-1 mr-1 inline-block rounded bg-indigo-50 px-2 py-0.5 text-indigo-700">{{ dia }}</span>
                            </template>
                        </span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">Horario:</span>
                        <span class="ml-2 text-gray-600"> {{ asociation.asociacion.hora_apertura }} - {{ asociation.asociacion.hora_cierre }} </span>
                    </div>
                </div>
                <!-- Materiales aceptados -->
                <div class="rounded-xl bg-white p-6 shadow">
                    <h3 class="mb-2 text-lg font-semibold text-indigo-700">Materiales Aceptados</h3>
                    <div class="flex flex-wrap gap-2">
                        <template v-for="(mat, i) in materialesAceptados" :key="i">
                            <span class="inline-block rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">
                                {{ mat }}
                            </span>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Mapa de ubicación -->
            <div v-if="lat && lng" class="mt-8 rounded-xl bg-white p-6 shadow">
                <h3 class="mb-2 text-lg font-semibold text-indigo-700">Ubicación</h3>
                <div class="mb-2 text-gray-700">
                    <span class="font-medium">Dirección geoespacial:</span>
                    <span class="ml-2">{{ asociation.asociacion.direccion }}</span>
                </div>
                <iframe
                    class="h-64 w-full rounded-lg border"
                    :src="`https://www.google.com/maps?q=${lat},${lng}&hl=es&z=16&output=embed`"
                    allowfullscreen
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                ></iframe>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    asociation: Object,
});

// Modal de imagen ampliada
const modalImagenAbierta = ref(false);
const imagenModalUrl = ref('');
function abrirModalImagen(url: string) {
    imagenModalUrl.value = url;
    modalImagenAbierta.value = true;
}
function cerrarModalImagen() {
    modalImagenAbierta.value = false;
    imagenModalUrl.value = '';
}

// Navegación a la lista de recicladores
function irARecicladores() {
    // Cambia la ruta según tu configuración de rutas
    window.location.href = `/asociation/${props.asociation.id}/recicladores`;
}

// Imágenes referenciales
const imagenesReferenciales = computed(() => {
    try {
        return props.asociation.asociacion.imagen_referencial ? JSON.parse(props.asociation.asociacion.imagen_referencial) : [];
    } catch {
        return [];
    }
});

// Días de atención
const diasAtencion = computed(() => {
    try {
        return props.asociation.asociacion.dias_atencion ? JSON.parse(props.asociation.asociacion.dias_atencion) : [];
    } catch {
        return [];
    }
});

// Materiales aceptados
const materialesAceptados = computed(() => {
    try {
        return props.asociation.asociacion.materiales_aceptados ? JSON.parse(props.asociation.asociacion.materiales_aceptados) : [];
    } catch {
        return [];
    }
});

// Extraer lat/lng de la dirección
const latLng = computed(() => {
    const dir = props.asociation.asociacion.direccion || '';
    const match = dir.match(/Lat:\s*(-?\d+(\.\d+)?),\s*Long:\s*(-?\d+(\.\d+)?)/);
    if (match) {
        return { lat: match[1], lng: match[3] };
    }
    return { lat: null, lng: null };
});
const lat = computed(() => latLng.value.lat);
const lng = computed(() => latLng.value.lng);
</script>
