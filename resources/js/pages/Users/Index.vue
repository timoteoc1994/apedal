<template>
    <Head title="Usuarios" />

    <AuthenticatedLayout>
        <template #header>
            Usuarios
        </template>
        

        <div class="bg-white shadow-xl rounded-xl p-8 mt-6">
            <button
                    @click="nuevoUsuario"
                    class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold py-2 px-6 rounded-lg shadow-lg transition-all duration-300 flex items-center gap-2 text-2sm mb-4"
                >
                    <span class="text-2xl">+</span> Nuevo usuario
                </button>
            <!-- Search bar -->
            <div class="relative mb-8">
                <input
                    type="text"
                    v-model="search"
                    @keyup.enter="buscar"
                    placeholder="Buscar usuarios..."
                    class="w-full rounded-lg border border-gray-300 py-3 pl-12 pr-4 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-gray-700 shadow"
                />
                <SearchIcon class="absolute left-4 top-3 h-5 w-5 text-indigo-400" />
            </div>

            <!-- Tabla -->
            <div class="overflow-x-auto">
                <table class="min-w-[700px] w-full text-sm text-left text-gray-700">
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
                        <tr v-if="users.data.length === 0">
                            <td :colspan="headers.length + 1" class="px-6 py-6 text-center text-gray-400">
                                No se encontraron usuarios.
                            </td>
                        </tr>
                        <tr
                            v-for="user in users.data"
                            :key="user.id"
                            class="hover:bg-indigo-50 transition"
                        >
                            <td class="px-6 py-4 font-medium">{{ user.name }}</td>
                            <td class="px-6 py-4">{{ user.email }}</td>
                            <td class="px-6 py-4">
                                <span v-if="user.roles && user.roles.length > 0" 
                                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    {{ user.roles.map(role => role.name).join(', ') }}
                                </span>
                                <span v-else class="text-gray-400 text-sm">Sin rol</span>
                            </td>
                            <td class="px-6 py-4">{{ formatDate(user.created_at) }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        @click="editarUsuario(user)"
                                        class="bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-full p-2 transition"
                                        title="Editar"
                                    >
                                        <PencilIcon class="h-5 w-5" />
                                    </button>
                                    <button
                                        @click="EliminarUsuario(user)"
                                        class="bg-red-100 hover:bg-red-200 text-red-600 rounded-full p-2 transition"
                                        title="Eliminar"
                                    >
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
                    <span class="font-semibold">{{ (currentPage - 1) * users.per_page + 1 }}</span>
                    a
                    <span class="font-semibold">{{ Math.min(currentPage * users.per_page, users.total) }}</span>
                    de
                    <span class="font-semibold">{{ users.total }}</span>
                    resultados
                </div>
                <div class="flex items-center gap-1">
                    <button
                        @click="goToPage(currentPage - 1)"
                        :disabled="currentPage === 1"
                        class="rounded-md px-3 py-1 bg-gray-100 text-gray-400 hover:bg-gray-200 transition disabled:opacity-50"
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
                        class="rounded-md px-3 py-1 bg-gray-100 text-gray-400 hover:bg-gray-200 transition disabled:opacity-50"
                    >
                        <ChevronRightIcon class="h-5 w-5" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal para crear/editar usuario -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ isEditing ? 'Editar Usuario' : 'Nuevo Usuario' }}
                    </h3>
                    <button
                        @click="cerrarModal"
                        class="text-gray-400 hover:text-gray-600 transition"
                    >
                        <XIcon class="h-6 w-6" />
                    </button>
                </div>

                <form @submit.prevent="guardarUsuario" class="p-6">
                    <div class="space-y-4">
                        <!-- Nombre -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                Nombre
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-gray-700"
                                placeholder="Ingresa el nombre completo"
                            />
                            <span v-if="errors.name" class="text-red-500 text-sm">{{ errors.name }}</span>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                Email
                            </label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-gray-700"
                                placeholder="Ingresa el email"
                            />
                            <span v-if="errors.email" class="text-red-500 text-sm">{{ errors.email }}</span>
                        </div>

                        <!-- Rol -->
                        <div>
                            <label for="role_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Rol
                            </label>
                            <select
                                id="role_id"
                                v-model="form.role_id"
                                required
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-gray-700"
                            >
                                <option value="">Selecciona un rol</option>
                                <option 
                                    v-for="role in roles" 
                                    :key="role.id" 
                                    :value="role.id.toString()"
                                >
                                    {{ role.name }}
                                </option>
                            </select>
                            <span v-if="errors.role_id" class="text-red-500 text-sm">{{ errors.role_id }}</span>
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ isEditing ? 'Nueva Contraseña (opcional)' : 'Contraseña' }}
                            </label>
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                :required="!isEditing"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-gray-700"
                                placeholder="Ingresa la contraseña"
                            />
                            <span v-if="errors.password" class="text-red-500 text-sm">{{ errors.password }}</span>
                        </div>

                        <!-- Confirmar Password -->
                        <div v-if="form.password">
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                                Confirmar Contraseña
                            </label>
                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-gray-700"
                                placeholder="Confirma la contraseña"
                            />
                            <span v-if="errors.password_confirmation" class="text-red-500 text-sm">{{ errors.password_confirmation }}</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 mt-6">
                        <button
                            type="button"
                            @click="cerrarModal"
                            class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="loading"
                            class="px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-lg transition disabled:opacity-50"
                        >
                            <span v-if="loading">Guardando...</span>
                            <span v-else>{{ isEditing ? 'Actualizar' : 'Crear' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3';
import { ChevronLeftIcon, ChevronRightIcon, TrashIcon, SearchIcon, ChevronUpIcon, ChevronDownIcon, PencilIcon, XIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps({
    users: Object,
    filters: Object,
    roles: Array,
});

const page = usePage();
const user = page.props.auth.user;

const headers = [
    { key: 'name', label: 'Nombre' },
    { key: 'email', label: 'Email' },
    { key: 'role', label: 'Rol' },
    { key: 'created_at', label: 'Fecha de Registro' },
];

const sort = ref(props.filters?.sort ?? 'name');
const direction = ref(props.filters?.direction ?? 'asc');
const search = ref(props.filters?.search ?? '');

// Modal state
const showModal = ref(false);
const isEditing = ref(false);
const loading = ref(false);
const errors = ref({});

// Form data
const form = ref({
    id: null,
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role_id: ''
});

const resetForm = () => {
    form.value = {
        id: null,
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role_id: ''
    };
    errors.value = {};
};

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toISOString().split('T')[0];
};

const currentPage = computed(() => props.users.current_page);
const totalPages = computed(() => props.users.last_page);

const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        router.get(props.users.path, {
            ...props.filters,
            search: search.value,
            sort: sort.value,
            direction: direction.value,
            page,
        }, { preserveState: true, replace: true });
    }
};

const displayedPages = computed(() => {
    let pages = [];
    for (let i = 1; i <= totalPages.value; i++) {
        pages.push(i);
    }
    return pages;
});

const ordenarPor = (campo) => {
    let newDirection = 'asc';
    if (sort.value === campo && direction.value === 'asc') {
        newDirection = 'desc';
    }
    sort.value = campo;
    direction.value = newDirection;
    router.get(props.users.path, {
        ...props.filters,
        search: search.value,
        sort: campo,
        direction: newDirection,
    }, { preserveState: true, replace: true });
};

const EliminarUsuario = (user) => {
    Swal.fire({
        title: '¿Estás seguro?',
        text: 'No podrás revertir esto',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('users.destroy', user.id), {
                preserveState: true,
                preserveScroll: true,
            });
        }
    });
};

const nuevoUsuario = () => {
    resetForm();
    isEditing.value = false;
    showModal.value = true;
};

const editarUsuario = (user) => {
    resetForm();
    isEditing.value = true;
    form.value.id = user.id;
    form.value.name = user.name;
    form.value.email = user.email;
    // Asignar el rol actual del usuario
    if (user.roles && user.roles.length > 0) {
        form.value.role_id = user.roles[0].id.toString();
    }
    showModal.value = true;
};

const cerrarModal = () => {
    showModal.value = false;
    resetForm();
};

const guardarUsuario = () => {
    loading.value = true;
    errors.value = {};

    const data = {
        name: form.value.name,
        email: form.value.email,
        role_id: form.value.role_id,
    };

    // Solo incluir password si se proporciona
    if (form.value.password) {
        data.password = form.value.password;
        data.password_confirmation = form.value.password_confirmation;
    }

    if (isEditing.value) {
        // Actualizar usuario existente
        router.put(route('users.update', form.value.id), data, {
            onSuccess: () => {
                loading.value = false;
                showModal.value = false;
                resetForm();
                Swal.fire('¡Usuario actualizado!', '', 'success');
            },
            onError: (responseErrors) => {
                loading.value = false;
                errors.value = responseErrors;
                Swal.fire('Error', 'No se pudo actualizar el usuario', 'error');
            }
        });
    } else {
        // Crear nuevo usuario
        router.post(route('users.store'), data, {
            onSuccess: () => {
                loading.value = false;
                showModal.value = false;
                resetForm();
                Swal.fire('¡Usuario creado!', '', 'success');
            },
            onError: (responseErrors) => {
                loading.value = false;
                errors.value = responseErrors;
                Swal.fire('Error', 'No se pudo crear el usuario', 'error');
            }
        });
    }
};

const buscar = () => {
    router.get(route('users.index'), {
        ...props.filters,
        search: search.value,
        sort: sort.value,
        direction: direction.value,
    }, { preserveState: true, replace: true });
};
</script>
