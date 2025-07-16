<template>
    <Head title="Tiendas" />

    <AuthenticatedLayout>
        <template #header>
            Empresas
        </template>

        <div class="bg-white shadow-xl rounded-xl p-8 mt-6">
            <button
                    @click="abrirModal"
                    class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold py-2 px-6 rounded-lg shadow-lg transition-all duration-300 flex items-center gap-2 text-2sm mb-4"
                >
                    <span class="text-2xl">+</span> Nueva tienda
                </button>

            <!-- Barra de búsqueda -->
            <div class="relative mb-8">
                <input
                    type="text"
                    v-model="filtro"
                    @keyup.enter="() => goToPage(1)"
                    placeholder="Buscar tiendas..."
                    class="w-full rounded-lg border border-gray-300 py-3 pl-12 pr-4 text-gray-700 shadow focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                />
                <SearchIcon class="absolute left-4 top-3 h-5 w-5 text-indigo-400" />
            </div>

            <!-- Tabla -->
            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px] text-left text-sm text-gray-700">
                    <thead class="bg-gradient-to-r from-indigo-500 to-purple-500 text-white">
                        <tr>
                            <th
                                v-for="header in headers"
                                :key="header.key"
                                @click="ordenarPor(header.key)"
                                class="px-6 py-4 font-semibold uppercase tracking-wider cursor-pointer select-none transition hover:bg-indigo-600"
                            >
                                <div class="flex items-center gap-1">
                                    {{ header.label }}
                                    <span v-if="sort === header.key">
                                        <ChevronUpIcon v-if="direction === 'asc'" class="inline h-4 w-4" />
                                        <ChevronDownIcon v-else class="inline h-4 w-4" />
                                    </span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-right font-semibold uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!tiendasLocal.length">
                            <td :colspan="5" class="px-6 py-6 text-center text-gray-400">No se encontraron tiendas.</td>
                        </tr>
                        <tr v-for="tienda in tiendasLocal" :key="tienda.id" class="transition hover:bg-indigo-50">
                            <td class="flex items-center gap-3 px-6 py-4 font-medium">
                                <div class="h-8 w-8 rounded-full bg-gradient-to-r from-purple-500 to-indigo-500 flex items-center justify-center text-white font-bold overflow-hidden">
                                    <img v-if="tienda.logo_url" :src="getLogoUrl(tienda.logo_url)" :alt="tienda.name" class="h-full w-full object-cover" @error="$event.target.style.display='none'" />
                                    <span v-else>{{ tienda.name.charAt(0).toUpperCase() }}</span>
                                </div>
                                {{ tienda.name }}
                            </td>
                            <td class="px-6 py-4">{{ tienda.email }}</td>
                            <td class="px-6 py-4">{{ tienda.ciudad || 'Sin ciudad' }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Activo
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <Link :href="route('producto.index.show', tienda.id)" class="text-indigo-600 hover:scale-105 hover:text-indigo-900" title="Ver">
                                        <EyeIcon class="h-5 w-5" />
                                    </Link>
                                    <button @click="editTienda(tienda.id)" class="text-indigo-600 hover:scale-105 hover:text-indigo-900" title="Editar">
                                        <PencilIcon class="h-5 w-5" />
                                    </button>
                                    <button @click="deleteTienda(tienda.id)" class="text-red-600 hover:scale-105 hover:text-red-900" title="Eliminar">
                                        <TrashIcon class="h-5 w-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="mt-8 flex flex-col items-center justify-between gap-4 sm:flex-row">
                <div class="text-sm text-gray-600">
                    Mostrando
                    <span class="font-semibold">{{ (currentPage - 1) * user_tiendas.per_page + 1 }}</span>
                    a
                    <span class="font-semibold">{{ Math.min(currentPage * user_tiendas.per_page, user_tiendas.total) }}</span>
                    de
                    <span class="font-semibold">{{ user_tiendas.total }}</span>
                    resultados
                </div>
                <div class="flex items-center gap-1">
                    <button
                        @click="goToPage(currentPage - 1)"
                        :disabled="currentPage === 1"
                        class="rounded-md bg-gray-100 px-3 py-1 text-gray-400 transition hover:bg-gray-200 disabled:opacity-50"
                    >
                        <ChevronLeftIcon class="h-5 w-5" />
                    </button>
                    <button
                        v-for="page in displayedPages"
                        :key="page"
                        @click="goToPage(page)"
                        class="rounded-md px-3 py-1"
                        :class="{
                            'bg-indigo-600 text-white': currentPage === page,
                            'bg-white text-gray-700 hover:bg-indigo-100': currentPage !== page,
                        }"
                    >
                        {{ page }}
                    </button>
                    <button
                        @click="goToPage(currentPage + 1)"
                        :disabled="currentPage === totalPages"
                        class="rounded-md bg-gray-100 px-3 py-1 text-gray-400 transition hover:bg-gray-200 disabled:opacity-50"
                    >
                        <ChevronRightIcon class="h-5 w-5" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Nueva/Editar Tienda -->
        <div v-if="mostrarModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg w-full max-w-md mx-4 max-h-[90vh] flex flex-col">
                <div class="flex justify-between items-center p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ tiendaEditando ? 'Editar Tienda' : 'Nueva Tienda' }}
                    </h3>
                    <button @click="cerrarModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-6">

                <form @submit.prevent="tiendaEditando ? actualizarTienda() : crearTienda()" class="space-y-4">
                    <!-- Nombre -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            Nombre de la tienda
                        </label>
                        <input
                            type="text"
                            id="name"
                            v-model="form.name"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            placeholder="Ingresa el nombre de la tienda"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            Email
                        </label>
                        <input
                            type="email"
                            id="email"
                            v-model="form.email"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            placeholder="ejemplo@correo.com"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <!-- Ciudad -->
                    <div>
                        <label for="ciudad" class="block text-sm font-medium text-gray-700 mb-1">
                            Ciudad
                        </label>
                        <select
                            id="ciudad"
                            v-model="form.ciudad"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        >
                            <option value="">Selecciona una ciudad</option>
                            <option v-for="ciudad in ciudades" :key="ciudad.id" :value="ciudad.name">
                                {{ ciudad.name }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.ciudad" />
                    </div>

                    <!-- Contraseña -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            Contraseña
                            <span v-if="tiendaEditando" class="text-sm text-gray-500">(opcional)</span>
                        </label>
                        <input
                            type="password"
                            id="password"
                            v-model="form.password"
                            :required="!tiendaEditando"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            :placeholder="tiendaEditando ? 'Dejar vacío para mantener la actual' : 'Ingresa la contraseña'"
                        />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <!-- Logo de la tienda -->
                    <div>
                        <label for="logo_url" class="mb-1 block text-sm font-medium text-gray-700">
                            Logo de la tienda
                            <span v-if="tiendaEditando" class="text-gray-500">(opcional - dejar vacío para mantener actual)</span>
                        </label>

                        <!-- Input de archivo con drag & drop -->
                        <div
                            class="relative rounded-md border-2 border-dashed border-gray-300 p-6 transition-colors hover:border-gray-400"
                            :class="{ 'border-purple-500 bg-purple-50': isDraggingLogo }"
                            @dragover.prevent="isDraggingLogo = true"
                            @dragleave.prevent="isDraggingLogo = false"
                            @drop.prevent="handleLogoDrop"
                        >
                            <input
                                type="file"
                                id="logo_url"
                                ref="logoInput"
                                @change="handleLogoFileSelect"
                                accept="image/*"
                                class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                            />

                            <div class="text-center">
                                <!-- Icono de imagen -->
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path
                                        d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>

                                <!-- Texto de instrucción -->
                                <div class="mt-2">
                                    <p class="text-sm text-gray-600">
                                        <span class="font-medium text-purple-600 hover:text-purple-500"> Haz clic para seleccionar </span>
                                        o arrastra y suelta el logo
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500">
                                        PNG, JPG, GIF hasta 5MB
                                        <span v-if="tiendaEditando" class="block text-purple-600">
                                            • Dejar vacío para mantener logo actual
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Vista previa del logo -->
                        <div v-if="logoPreview" class="mt-4">
                            <div class="relative inline-block">
                                <img :src="logoPreview" alt="Vista previa del logo" class="h-20 w-20 rounded-lg object-cover shadow-md" />
                                <button
                                    @click="removeLogo"
                                    type="button"
                                    class="absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white transition-colors hover:bg-red-600"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <p class="mt-2 text-sm text-gray-600">{{ logoFileName }}</p>
                        </div>

                        <InputError class="mt-2" :message="form.errors.logo_url" />
                    </div>

                    <!-- Botones -->
                    <div class="flex justify-end gap-3 mt-6">
                        <button
                            type="button"
                            @click="cerrarModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors"
                        >
                            Cancelar
                        </button>
                        <Button :disabled="form.processing" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-6 rounded-lg shadow transition-all duration-300">
                            {{ form.processing ? 'Guardando...' : (tiendaEditando ? 'Actualizar' : 'Crear') }}
                        </Button>
                    </div>
                </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm, Link, usePage, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { Button } from '@/Components/ui/button';
import InputError from '@/Components/InputError.vue';
import { ChevronLeftIcon, ChevronRightIcon, PencilIcon, SearchIcon, TrashIcon, ChevronUpIcon, ChevronDownIcon, EyeIcon } from 'lucide-vue-next';
import Swal from 'sweetalert2';

const props = defineProps({
    ciudades: Object,
    user_tiendas: Object // Formato: { data: [], total: 0, per_page: 10, current_page: 1, last_page: 1, path: '/productos' }
});

// Configuración de headers para ordenamiento
const headers = [
    { key: 'name', label: 'Nombre' },
    { key: 'email', label: 'Email' },
    { key: 'ciudad', label: 'Ciudad' },
    { key: 'created_at', label: 'Estado' },
];

// Estado para ordenamiento
const sort = ref('name');
const direction = ref('asc');

// Estado local y búsqueda
const tiendasLocal = ref([...(props.user_tiendas.data || [])]);
const filtro = ref('');

// Paginación
const currentPage = computed(() => props.user_tiendas.current_page || 1);
const totalPages = computed(() => props.user_tiendas.last_page || 1);
const displayedPages = computed(() => Array.from({ length: totalPages.value }, (_, i) => i + 1));

// Función para ordenar
const ordenarPor = (campo) => {
    let newDirection = 'asc';
    if (sort.value === campo && direction.value === 'asc') {
        newDirection = 'desc';
    }
    sort.value = campo;
    direction.value = newDirection;
    
    router.get(props.user_tiendas.path || route('producto.index'), {
        search: filtro.value,
        sort: campo,
        direction: newDirection,
    }, { preserveState: true, replace: true });
};

// Función para ir a página
const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        router.get(props.user_tiendas.path || route('producto.index'), { 
            page, 
            search: filtro.value,
            sort: sort.value,
            direction: direction.value
        }, { preserveState: true, replace: true });
    }
};

// Watch para búsqueda
watch(filtro, (val) => {
    router.get(props.user_tiendas.path || route('producto.index'), { 
        search: val,
        sort: sort.value,
        direction: direction.value
    }, { preserveState: true, replace: true });
});

// Mantener data local actualizada
watch(
    () => props.user_tiendas.data,
    (newVal) => {
        tiendasLocal.value = [...(newVal || [])];
    },
);

// Funciones para manejo de archivos del logo
const handleLogoFileSelect = (event) => {
    const file = event.target.files[0];
    if (file) {
        processLogoFile(file);
    }
};

const handleLogoDrop = (event) => {
    isDraggingLogo.value = false;
    const file = event.dataTransfer.files[0];
    if (file) {
        processLogoFile(file);
    }
};

const processLogoFile = (file) => {
    // Validar tipo de archivo
    if (!file.type.startsWith('image/')) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Solo se permiten archivos de imagen para el logo',
        });
        return;
    }
    
    // Validar tamaño (5MB máximo para logo)
    if (file.size > 5 * 1024 * 1024) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'El archivo es demasiado grande. Máximo 5MB',
        });
        return;
    }
    
    // Asignar archivo al formulario
    form.logo_url = file;
    logoFileName.value = file.name;
    
    // Crear vista previa
    const reader = new FileReader();
    reader.onload = (e) => {
        logoPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

const removeLogo = () => {
    form.logo_url = null;
    logoPreview.value = null;
    logoFileName.value = '';
    if (logoInput.value) {
        logoInput.value.value = '';
    }
};

// Funciones CRUD
const editTienda = (id) => {
    const tienda = tiendasLocal.value.find(t => t.id === id);
    if (tienda) {
        tiendaEditando.value = tienda;
        form.name = tienda.name;
        form.email = tienda.email;
        form.ciudad = tienda.ciudad || '';
        form.password = ''; // Siempre vacío para edición
        form.logo_url = null; // No asignar archivo existente
        
        // Manejar logo existente para vista previa
        if (tienda.logo_url) {
            logoPreview.value = getLogoUrl(tienda.logo_url);
            logoFileName.value = 'Logo actual';
        } else {
            logoPreview.value = null;
            logoFileName.value = '';
        }
        
        mostrarModal.value = true;
    }
};

const deleteTienda = async (id) => {
    const { isConfirmed } = await Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar',
    });
    
    if (!isConfirmed) return;
    
    try {
        await router.delete(route('producto.index.eliminar', { id }));
    } catch {
        Swal.fire('Error', 'No se pudo eliminar. Intenta de nuevo.', 'error');
    }
};

// Flash messages
const page = usePage();
watch(
    () => page.props.flash,
    (flash) => {
        if (flash && flash.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: flash.success,
                timer: 2000,
                showConfirmButton: false,
            });
        }
        if (flash && flash.error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: flash.error,
                timer: 3000,
                showConfirmButton: false,
            });
        }
    },
    { immediate: true }
);

// Estado del modal
const mostrarModal = ref(false);
const tiendaEditando = ref(null);

// Estados para el manejo de archivos del logo
const logoInput = ref(null);
const isDraggingLogo = ref(false);
const logoPreview = ref(null);
const logoFileName = ref('');

// Formulario con useForm de Inertia
const form = useForm({
    name: '',
    email: '',
    ciudad: '',
    password: '',
    logo_url: null, // Agregamos el campo para el logo
    forceFormData: true // Forzar el uso de FormData para archivos
});

// Funciones del modal
const abrirModal = () => {
    tiendaEditando.value = null;
    mostrarModal.value = true;
};

const cerrarModal = () => {
    mostrarModal.value = false;
    tiendaEditando.value = null;
    form.reset();
    
    // Limpiar estados del logo
    logoPreview.value = null;
    logoFileName.value = '';
    isDraggingLogo.value = false;
};

// Función para crear tienda
const crearTienda = () => {
    form.post(route('producto.index.nuevo'), {
        forceFormData: true, // Forzar FormData para archivos
        onSuccess: () => {
            cerrarModal();
        },
        onError: (errors) => {
            console.log('Errores:', errors);
        }
    });
};

// Función para obtener URL del logo
const getLogoUrl = (logoUrl) => {
    if (!logoUrl) return null;
    
    // Si es una ruta absoluta de Windows, probablemente sea un error
    if (logoUrl.includes('C:\\Windows\\Temp\\')) {
        return null;
    }
    
    // Si ya tiene el dominio, devolverla tal como está
    if (logoUrl.startsWith('http')) {
        return logoUrl;
    }
    
    // Si es una ruta relativa, agregar el baseUrl
    return `${window.location.origin}/storage/${logoUrl}`;
};

// Función para actualizar tienda
const actualizarTienda = () => {
    form.transform((data) => ({
        ...data,
        _method: 'PUT'
    })).post(route('producto.update', tiendaEditando.value.id), {
        forceFormData: true, // Forzar FormData para archivos
        onSuccess: () => {
            cerrarModal();
        },
        onError: (errors) => {
            console.log('Errores:', errors);
        }
    });
};
</script>
