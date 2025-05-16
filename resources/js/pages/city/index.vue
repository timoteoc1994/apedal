<script setup lang="ts">
import { Button } from '@/components/ui/button';
import InputError from '@/components/InputError.vue';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ChevronLeftIcon, ChevronRightIcon, PencilIcon, PlusIcon, SearchIcon, TrashIcon } from 'lucide-vue-next';
import { ref } from 'vue';
import { computed } from 'vue';

const props = defineProps({
    cities: Object,
});

//formatear fecha
const formatDate = (date) => {
    if (!date) return ''; // Manejar valores nulos o vacíos
    return new Date(date).toISOString().split('T')[0]; // Extrae solo la fecha
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Ciudades',
        href: route('asociation.index'),
    },
];

// Table headers
const headers = [
    { key: 'name', label: 'Ciudad' },
    { key: 'created_at', label: 'Fecha de Registro' },
    { key: 'created_at', label: 'Usuario' },
];

// Páginas actuales
const currentPage = computed(() => props.cities.current_page);
const totalPages = computed(() => props.cities.last_page);

// Función para navegar entre páginas sin recargar
const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        router.get(props.cities.path, { page }, { preserveState: true });
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

//formulario crear ciudad
const form_ciudad = useForm({
    id:'',
    name: '',
});
const closeButtonRef = ref(null);
const closeModal = () => {
    form_ciudad.clearErrors();
    form_ciudad.reset();
};

const submit = () => {
    form_ciudad.post(route('ciudad.index.nuevo'), {
        preserveScroll: true,
        onSuccess: (response) => {
            closeModal(); // Opcional: Cerrar el modal después de enviar
            if (closeButtonRef.value) {
                closeButtonRef.value.$el.click();
            }
        },
        onError: (errors) => {
          
        },
    });
};

//eliminar pago
const EliminarCiudad = (dato) => {
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
            router.delete(route('ciudad.index.delete', dato.id), {
                preserveState: true,
                preserveScroll: true,
            });
        }
    });
};
</script>

<template>
    <Head title="Ciudades" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="rounded-lg bg-white p-6 shadow-lg dark:bg-gray-900">
                <!-- Header with title and create button -->
                <div class="mb-6 flex flex-col items-center justify-between sm:flex-row">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Ciudades</h2>
                    <Dialog>
                    <DialogTrigger as-child>
                        <Button
                            class="mt-4 flex transform items-center rounded-lg bg-gradient-to-r from-purple-600 to-indigo-600 px-4 py-2 font-medium text-white shadow-md transition-all hover:scale-105 hover:from-purple-700 hover:to-indigo-700 sm:mt-0"
                            variant="destructive"
                            ><PlusIcon class="mr-2 h-5 w-5" /> Nueva ciudad</Button
                        >
                    </DialogTrigger>
                    <DialogContent>
                        <form class="space-y-6" @submit.prevent="submit">
                            <DialogHeader class="space-y-3">
                                <DialogTitle>Ciudad</DialogTitle>
                                <DialogDescription> Este apartado servira para poner mas ciudades al inicio de la aplicacion </DialogDescription>
                            </DialogHeader>

                            <div class="grid gap-2">
                                <Label class="sr-only">Ciudad</Label>
                                <input class="mt-1 w-full border rounded p-2" type="text" v-model="form_ciudad.name" placeholder="Ciudad" />
                                <InputError :message="form_ciudad.errors.name" />
                            </div>

                            <DialogFooter class="gap-2">
                                <DialogClose  ref="closeButtonRef" as-child>
                                    <Button variant="secondary" @click="closeModal"> Cancelar </Button>
                                </DialogClose>

                                <Button  :disabled="form_ciudad.processing">
                                    <button  type="submit">Agregar</button>
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
                </div>

                

                <!-- Search bar -->
                <div class="relative mb-6">
                    <input
                        type="text"
                        placeholder="Buscar usuarios..."
                        class="w-full rounded-lg border border-gray-300 py-2 pl-10 pr-4 focus:border-transparent focus:ring-2 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    />
                    <SearchIcon class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" />
                </div>

                <!-- Table -->
                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th
                                    v-for="header in headers"
                                    :key="header.key"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300"
                                >
                                    {{ header.label }}
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                            <tr v-if="cities.total === 0" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td :colspan="headers.length + 1" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    No se encontraron usuarios
                                </td>
                            </tr>
                            <tr v-for="city in cities.data" :key="city.id" class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ city.name }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ formatDate(city.created_at) }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ city.user_id }}</td>

                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <Link
                                            :href="route('asociation.index.show', city.id)"
                                            class="text-indigo-600 hover:scale-105 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                            title="Editar"
                                        >
                                            <PencilIcon class="h-5 w-5" />
                                        </Link>

                                        <button
                                            class="text-red-600 hover:scale-105 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                            title="Eliminar"
                                            @click="EliminarCiudad(city)"
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
                <div class="mt-6 flex flex-col items-center justify-between sm:flex-row">
                    <div class="mb-4 text-sm text-gray-700 dark:text-gray-300 sm:mb-0">
                        Mostrando <span class="font-medium">{{ (currentPage - 1) * cities.per_page + 1 }}</span> a
                        <span class="font-medium">{{ Math.min(currentPage * cities.per_page, cities.total) }}</span> de
                        <span class="font-medium">{{ cities.total }}</span> resultados
                    </div>

                    <div class="flex items-center space-x-2">
                        <!-- Botón Anterior -->
                        <button
                            @click="goToPage(currentPage - 1)"
                            :disabled="currentPage === 1"
                            class="rounded-md px-3 py-1"
                            :class="{
                                'cursor-not-allowed bg-gray-100 text-gray-400 dark:bg-gray-800': currentPage === 1,
                                'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700': currentPage > 1,
                            }"
                        >
                            <ChevronLeftIcon class="h-5 w-5" />
                        </button>

                        <!-- Números de Página -->
                        <button
                            v-for="page in displayedPages"
                            :key="page"
                            @click="goToPage(page)"
                            class="rounded-md px-3 py-1"
                            :class="{
                                'bg-purple-600 text-white': currentPage === page,
                                'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700':
                                    currentPage !== page,
                            }"
                        >
                            {{ page }}
                        </button>

                        <!-- Botón Siguiente -->
                        <button
                            @click="goToPage(currentPage + 1)"
                            :disabled="currentPage === totalPages"
                            class="rounded-md px-3 py-1"
                            :class="{
                                'cursor-not-allowed bg-gray-100 text-gray-400 dark:bg-gray-800': currentPage === totalPages,
                                'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700':
                                    currentPage < totalPages,
                            }"
                        >
                            <ChevronRightIcon class="h-5 w-5" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
