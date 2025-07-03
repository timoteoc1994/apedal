<template>
    <Head title="Editar" />

    <AuthenticatedLayout>
        <template #header>
            <Link class="text-indigo-600 hover:text-indigo-500" :href="route('asociation.index')">Asociaciones/</Link>
            <Link class="text-indigo-600 hover:text-indigo-500" :href="route('asociation.index.perfil', { id: nombreAsociacion.id })">{{ nombreAsociacion.asociacion.name }}/</Link>
            <Link class="text-indigo-600 hover:text-indigo-500" :href="route('asociation.index.recicladores', { id: nombreAsociacion.id })"> Recicladores/</Link>
            {{ form.name }}
            </template>
        <div class="bg-white shadow-xl rounded-xl p-8 mt-6 mx-auto">
            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <Label for="name" class="block text-gray-700 font-semibold mb-1">Nombre de la asociación <span class="text-red-500">*</span></Label>
                    <Input
                        id="name"
                        class="w-full rounded-lg border border-gray-300 py-3 px-4 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-gray-700 bg-white"
                        v-model="form.name"
                        required
                        autocomplete="name"
                        placeholder="Nombre de la asociación"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <Label for="email" class="block text-gray-700 font-semibold mb-1">Email <span class="text-red-500">*</span></Label>
                    <Input
                        id="email"
                        type="email"
                        class="w-full rounded-lg border border-gray-300 py-3 px-4 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-gray-700 bg-white"
                        v-model="form.email"
                        required
                        autocomplete="username"
                        placeholder="Email address"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                

                <div>
                    <Label for="telefono" class="block text-gray-700 font-semibold mb-1">Número de teléfono <span class="text-red-500">*</span></Label>
                    <Input
                        id="telefono"
                        type="text"
                        class="w-full rounded-lg border border-gray-300 py-3 px-4 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-gray-700 bg-white"
                        v-model="form.telefono"
                        required
                        autocomplete="telefono"
                        placeholder="Número de teléfono"
                    />
                    <InputError class="mt-2" :message="form.errors.telefono" />
                </div>

                

                <div>
                    <Label for="ciudad" class="block text-gray-700 font-semibold mb-1">Ciudad <span class="text-red-500">*</span></Label>
                    <select
                        id="ciudad"
                        class="w-full rounded-lg border border-gray-300 py-3 px-4 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-gray-700 bg-white"
                        v-model="form.ciudad"
                    >
                        <option v-for="ciudad in ciudades" :key="ciudad.name" :value="ciudad.name">{{ ciudad.name }}</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.city" />
                </div>

                <div>
                    <Label for="estado" class="block text-gray-700 font-semibold mb-1">Estado <span class="text-red-500">*</span></Label>
                    <select
                        id="estado"
                        class="w-full rounded-lg border border-gray-300 py-3 px-4 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-gray-700 bg-white"
                        v-model="form.estado"
                    >
                        <option :value="0">Inactivo</option>
                        <option :value="1">Activo</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.estado" />
                </div>
                <div>
                    <Label for="password" class="block text-gray-700 font-semibold mb-1">Contraseña</Label>
                    <Input
                        id="password"
                        type="password"
                        class="w-full rounded-lg border border-gray-300 py-3 px-4 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-gray-700 bg-white"
                        v-model="form.password"
                        autocomplete="new-password"
                        placeholder="Nueva contraseña (opcional)"
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div class="flex items-center gap-4 mt-6">
                    <Button :disabled="form.processing" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-6 rounded-lg shadow transition-all duration-300">
                        Guardar
                    </Button>
                    <Transition
                        enter-active-class="transition ease-in-out"
                        enter-from-class="opacity-0"
                        leave-active-class="transition ease-in-out"
                        leave-to-class="opacity-0"
                    >
            
                    </Transition>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import Password from '../settings/Password.vue';
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
const props = defineProps({
    reciclador: Array,
    ciudades: Object,
    nombreAsociacion: Object,
});

const form = useForm({
    id: props.reciclador?.id,
    id_asociacion: props.reciclador?.reciclador.id,
    name: props.reciclador?.reciclador.name,
    email: props.reciclador?.email,
    telefono: props.reciclador?.reciclador.telefono,
    ciudad: props.reciclador?.reciclador?.ciudad,
    estado: props.reciclador?.reciclador?.estado ? 1 : 0,
    password: '',
});

const submit = () => {
    form.patch(route('asociation.index.update.recicladores'), {
        preserveScroll: true,
    });
};


</script>