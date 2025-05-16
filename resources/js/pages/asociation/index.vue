<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ChevronLeftIcon, ChevronRightIcon, PencilIcon, PlusIcon, SearchIcon, TrashIcon } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    Asociations: Object,
});

const cities = ref([]);

onMounted(async () => {
    try {
        const response = await fetch('/cities');
        cities.value = await response.json();
    } catch (error) {
        console.error('Error al cargar las ciudades:', error);
    }
});
//formatear fecha
const formatDate = (date) => {
    if (!date) return ''; // Manejar valores nulos o vacíos
    return new Date(date).toISOString().split('T')[0]; // Extrae solo la fecha
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Asociación',
        href: route('asociation.index'),
    },
];

// Table headers
const headers = [
    { key: 'name', label: 'Asociación' },
    { key: 'created_at', label: 'Fecha de Registro' },
    { key: 'created_at', label: 'Ciudad' },
    { key: 'role', label: 'ESTADO' },
];

// Páginas actuales
const currentPage = computed(() => props.Asociations.current_page);
const totalPages = computed(() => props.Asociations.last_page);

// Función para navegar entre páginas sin recargar
const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        router.get(props.Asociations.path, { page }, { preserveState: true });
    }
};

// Definir las páginas a mostrar en la paginación
const displayedPages = computed(() => {
    let pages = [];
    for (let i = 1; i <= totalPages.value; i++) {
        pages.push(i);
    }
    return pages;
});

//eliminar pago
const EliminarAsociation = (dato) => {
    console.log("Datos de la asociación a eliminar:", dato);

    Swal.fire({
        title: "¿Estás seguro?",
        text: "No podrás revertir esto",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isConfirmed) {
            console.log("ID de la asociación que se va a eliminar:", dato.profile_id);

            const deleteUrl = route("asociation.index.delete", { id: dato.profile_id });
            console.log("URL para eliminar la asociación:", deleteUrl);

            router.delete(deleteUrl, {
                onSuccess: () => {
                    Swal.fire('Eliminado', 'La asociación ha sido eliminada', 'success')
                        .then(() => {
                            router.visit(route('asociation.index'), {
                                preserveScroll: true
                            });
                        });
                },
                onError: (error) => {
                    console.error("Error al eliminar la asociación:", error);
                    Swal.fire('Error', 'No se pudo eliminar la asociación', 'error');
                }
            });
        }
    });
};


const abrirFormulario = ref(false);
const nuevaAsociacion = ref<{
    name: string;
    number_phone: string;
    direccion: string;
    city: string;
    descripcion: string;
    logo_url: File | string | null;
    verified: boolean;
    color: string;
    email: string;
    password: string;
    confirmPassword: string;
}>({
    name: '',
    number_phone: '',
    direccion: '',
    city: '',
    descripcion: '',
    logo_url: null,
    verified: false,
    color: '#0000FF',
    email: '',
    password: '',
    confirmPassword: ''
});

const formKey = ref(Date.now());

const resetFormulario = () => {
    nuevaAsociacion.value = {
        name: '',
        number_phone: '',
        direccion: '',
        city: '',
        descripcion: '',
        logo_url: null,
        verified: false,
        color: '#0000FF',
        email: '',
        password: '',
        confirmPassword: ''
    };

    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
        previewUrl.value = null;
    }

    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.value = '';
    });

    abrirFormulario.value = false;
    formKey.value = Date.now();
};

const crearAsociacion = () => {
    console.log('Iniciando creación de asociación...');

    const errors = {};

    if (!nuevaAsociacion.value.name?.trim()) {
        errors.name = 'Nombre es obligatorio';
    }

    if (!nuevaAsociacion.value.email?.trim()) {
        errors.email = 'Email es obligatorio';
    } else if (!/^\S+@\S+\.\S+$/.test(nuevaAsociacion.value.email)) {
        errors.email = 'Email no tiene un formato válido';
    }

    if (!nuevaAsociacion.value.password) {
        errors.password = 'Contraseña es obligatoria';
    } else if (nuevaAsociacion.value.password.length < 6) {
        errors.password = 'La contraseña debe tener al menos 6 caracteres';
    }

    if (nuevaAsociacion.value.password !== nuevaAsociacion.value.confirmPassword) {
        errors.confirmPassword = 'Las contraseñas no coinciden';
    }

    if (!nuevaAsociacion.value.city) {
        errors.city = 'Ciudad es obligatoria';
    }

    if (Object.keys(errors).length > 0) {
        console.error('Errores de validación:', errors);
        return;
    }

    const formData = new FormData();

    formData.append('name', nuevaAsociacion.value.name.trim());
    formData.append('number_phone', nuevaAsociacion.value.number_phone?.trim() || '');
    formData.append('direccion', nuevaAsociacion.value.direccion?.trim() || '');
    formData.append('city', nuevaAsociacion.value.city?.toString() || ''); // Ahora enviamos el nombre de la ciudad
    formData.append('descripcion', nuevaAsociacion.value.descripcion?.trim() || '');
    formData.append('verified', nuevaAsociacion.value.verified ? '1' : '0');
    formData.append('color', nuevaAsociacion.value.color || '#0000FF');
    formData.append('email', nuevaAsociacion.value.email.trim());
    formData.append('password', nuevaAsociacion.value.password);
    formData.append('password_confirmation', nuevaAsociacion.value.confirmPassword);

    if (nuevaAsociacion.value.logo_url instanceof File) {
        formData.append('logo_url', nuevaAsociacion.value.logo_url);
    }

    console.log('Contenido de FormData:');
    for (let [key, value] of formData.entries()) {
        console.log(`${key}:`, value instanceof File ? `File(${value.name}, ${value.size} bytes)` : value);
    }

    console.log('Enviando solicitud al servidor...');
    router.post(route('asociation.store'), formData, {
        forceFormData: true,
        onBefore: () => {
            console.log('Preparando envío...');
        },
        onStart: () => console.log('Solicitud iniciada'),
        onProgress: (progress) => {
            console.log(`Progreso: ${progress.percentage}%`);
        },
        onSuccess: () => {
            console.log('¡Asociación y usuario creados exitosamente!');
            resetFormulario();
            abrirFormulario.value = false;
            nuevaAsociacion.value = {
                name: '',
                number_phone: '',
                direccion: '',
                city: '',
                descripcion: '',
                logo_url: null,
                verified: false,
                color: '#0000FF',
                email: '',
                password: '',
                confirmPassword: ''
            };
        },
        onError: (errors) => {
            console.error('Error en la respuesta del servidor:', errors);
        },
        onFinish: () => {
            console.log('Proceso completado');
        }
    });
};

const previewUrl = ref<string | null>(null);

const onFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
    }

    if (file) {
        nuevaAsociacion.value.logo_url = file;
        previewUrl.value = URL.createObjectURL(file);
    } else {
        previewUrl.value = null;
    }
};

const soloNumerosTelefono = (event: Event) => {
    const target = event.target as HTMLInputElement;
    target.value = target.value.replace(/\D/g, '');
    nuevaAsociacion.value.number_phone = target.value;
};


const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && abrirFormulario.value) {
        abrirFormulario.value = false;
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});

</script>

<template>

    <Head title="Asociaciones inscritas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="rounded-lg bg-white p-6 shadow-lg dark:bg-gray-900">
                <!-- Header with title and create button -->
                <div class="mb-6 flex flex-col items-center justify-between sm:flex-row">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Asociaciones inscritas</h2>
                    <button @click="abrirFormulario = true"
                        class="mt-4 flex transform items-center rounded-lg bg-gradient-to-r from-purple-600 to-indigo-600 px-4 py-2 font-medium text-white shadow-md transition-all hover:scale-105 hover:from-purple-700 hover:to-indigo-700 sm:mt-0">
                        <PlusIcon class="mr-2 h-5 w-5" />
                        Nuevo Asociación
                    </button>

                </div>

                <!-- Search bar -->
                <div class="relative mb-6">
                    <input type="text" placeholder="Buscar usuarios..."
                        class="w-full rounded-lg border border-gray-300 py-2 pl-10 pr-4 focus:border-transparent focus:ring-2 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                    <SearchIcon class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" />
                </div>

                <!-- Table -->
                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th v-for="header in headers" :key="header.key"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                    {{ header.label }}
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                            <tr v-if="Asociations.length === 0" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td :colspan="headers.length + 1"
                                    class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    No se encontraron usuarios
                                </td>
                            </tr>
                            <tr v-for="Asociation in Asociations.data" :key="Asociation.id"
                                class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center">
                                        <div v-if="Asociation.photo != null" class="h-10 w-10 flex-shrink-0">
                                            <img :src="Asociation.photo" alt=""
                                                class="h-10 w-10 rounded-full object-cover" />
                                        </div>
                                        <div v-else class="h-10 w-10 flex-shrink-0">
                                            <img src="https://e7.pngegg.com/pngimages/836/345/png-clipart-ecole-centrale-de-lyon-organization-solidarity-humanitarian-aid-voluntary-association-student-people-area-thumbnail.png"
                                                alt="" class="h-10 w-10 rounded-full object-cover" />
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{
                                                Asociation.asociacion.name }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ Asociation.email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ formatDate(Asociation.created_at) }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ Asociation.asociacion.city }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span :class="[
                                        'rounded-full px-2 py-1 text-xs font-semibold',
                                        Asociation.asociacion.verified == false
                                            ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                            : Asociation.asociacion.verified == true
                                                ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'
                                                : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                    ]">

                                        <span v-if="Asociation.asociacion.verified === false">Inactivo</span>
                                        <span v-if="Asociation.asociacion.verified === true">Activo</span>
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">

                                        <Link :href="route('asociation.index.show', Asociation.id)"
                                            class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 hover:scale-105"
                                            title="Editar">
                                        <PencilIcon class="h-5 w-5" />
                                        </Link>

                                        <button
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 hover:scale-105"
                                            title="Eliminar" @click="EliminarAsociation(Asociation)">
                                            <TrashIcon class="h-5 w-5" />
                                        </button>


                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <!-- Formulario Modal para Crear Asociación -->
                    <div v-if="abrirFormulario" @click.self="abrirFormulario = false"
                        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 overflow-y-auto">
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg w-full max-w-2xl shadow-xl my-8 mx-4">
                            <div class="max-h-[80vh] overflow-y-auto">
                                <h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Nueva Asociación</h2>

                                <form @submit.prevent="crearAsociacion" :key="formKey"
                                    class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Nombre</label>
                                        <input v-model="nuevaAsociacion.name" type="text" required
                                            class="w-full rounded border px-3 py-2 dark:bg-gray-700 dark:text-white" />
                                    </div>

                                    <div>
                                        <label
                                            class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Teléfono</label>
                                        <input v-model="nuevaAsociacion.number_phone" type="tel" inputmode="numeric"
                                            maxlength="10" pattern="\d{10}" @input="soloNumerosTelefono" required
                                            class="w-full rounded border px-3 py-2 dark:bg-gray-700 dark:text-white" />
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label
                                            class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Dirección</label>
                                        <input v-model="nuevaAsociacion.direccion" type="text"
                                            class="w-full rounded border px-3 py-2 dark:bg-gray-700 dark:text-white" />
                                    </div>

                                    <div>
                                        <label
                                            class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Ciudad</label>
                                        <select v-model="nuevaAsociacion.city"
                                            class="w-full rounded border px-3 py-2 dark:bg-gray-700 dark:text-white">
                                            <option value="" disabled>Selecciona una ciudad</option>
                                            <option v-for="city in cities" :key="city.id" :value="city.name">
                                                {{ city.name }}
                                            </option>
                                        </select>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Color</label>
                                        <input v-model="nuevaAsociacion.color" type="color"
                                            class="w-full h-10 rounded border dark:bg-gray-700" />
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label
                                            class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Descripción</label>
                                        <textarea v-model="nuevaAsociacion.descripcion" rows="3"
                                            class="w-full rounded border px-3 py-2 dark:bg-gray-700 dark:text-white"></textarea>
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label
                                            class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Correo
                                            electrónico</label>
                                        <input v-model="nuevaAsociacion.email" type="email" required
                                            class="w-full rounded border px-3 py-2 dark:bg-gray-700 dark:text-white" />
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label
                                            class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Contraseña</label>
                                        <input v-model="nuevaAsociacion.password" type="password" required
                                            class="w-full rounded border px-3 py-2 dark:bg-gray-700 dark:text-white" />
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label
                                            class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Confirmar
                                            Contraseña</label>
                                        <input v-model="nuevaAsociacion.confirmPassword" type="password" required
                                            class="w-full rounded border px-3 py-2 dark:bg-gray-700 dark:text-white" />
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label
                                            class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Logo
                                            de la Asociación</label>
                                        <div class="mt-1 flex items-center">
                                            <div class="relative group">
                                                <div
                                                    class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-purple-500 dark:hover:border-purple-400 transition-colors bg-white dark:bg-gray-700">
                                                    <img v-if="previewUrl" :src="previewUrl"
                                                        class="h-full w-full object-cover"
                                                        alt="Vista previa del logo" />
                                                    <div v-else class="text-center">
                                                        <PlusIcon
                                                            class="mx-auto h-10 w-10 text-gray-400 dark:text-gray-500" />
                                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Subir
                                                            logo</p>
                                                    </div>
                                                </div>
                                                <input type="file" @change="onFileChange" accept="image/*"
                                                    class="absolute inset-0 cursor-pointer opacity-0 w-24 h-24" />
                                            </div>
                                            <div class="ml-5 space-y-1">
                                                <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG o GIF</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">Tamaño recomendado:
                                                    512x512px</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">Máximo 2MB</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="sm:col-span-2 flex justify-end gap-2 mt-4">
                                        <button type="button" @click="resetFormulario"
                                            class="px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-500">
                                            Cancelar
                                        </button>
                                        <button type="submit"
                                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                            Crear
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Paginación -->
                <div class="mt-6 flex flex-col items-center justify-between sm:flex-row">
                    <div class="mb-4 text-sm text-gray-700 dark:text-gray-300 sm:mb-0">
                        Mostrando <span class="font-medium">{{ (currentPage - 1) * Asociations.per_page + 1 }}</span> a
                        <span class="font-medium">{{ Math.min(currentPage * Asociations.per_page, Asociations.total)
                            }}</span> de
                        <span class="font-medium">{{ Asociations.total }}</span> resultados
                    </div>

                    <div class="flex items-center space-x-2">
                        <!-- Botón Anterior -->
                        <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1"
                            class="rounded-md px-3 py-1" :class="{
                                'cursor-not-allowed bg-gray-100 text-gray-400 dark:bg-gray-800': currentPage === 1,
                                'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700': currentPage > 1,
                            }">
                            <ChevronLeftIcon class="h-5 w-5" />
                        </button>

                        <!-- Números de Página -->
                        <button v-for="page in displayedPages" :key="page" @click="goToPage(page)"
                            class="rounded-md px-3 py-1" :class="{
                                'bg-purple-600 text-white': currentPage === page,
                                'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700':
                                    currentPage !== page,
                            }">
                            {{ page }}
                        </button>

                        <!-- Botón Siguiente -->
                        <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages"
                            class="rounded-md px-3 py-1" :class="{
                                'cursor-not-allowed bg-gray-100 text-gray-400 dark:bg-gray-800': currentPage === totalPages,
                                'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700':
                                    currentPage < totalPages,
                            }">
                            <ChevronRightIcon class="h-5 w-5" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </AppLayout>
</template>
