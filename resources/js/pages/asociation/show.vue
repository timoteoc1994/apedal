<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    asociation: Array,
    ciudades: Object,
});

//navegacion
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Asociación',
        href: route('asociation.index'),
    },
    {
        title: props.asociation.name,
        href: route('asociation.index'),
    },
];

//formatear fecha
const formatDate = (date) => {
    if (!date) return ''; // Manejar valores nulos o vacíos
    return new Date(date).toISOString().split('T')[0]; // Extrae solo la fecha
};

const form = useForm({
    id: props.asociation.id,
    id_asociacion: props.asociation.asociacion.id,
    name: props.asociation.asociacion.name,
    email: props.asociation.email,
    number_phone: props.asociation.asociacion.number_phone,
    city: props.asociation.asociacion.city,
    estado: props.asociation.asociacion.verified ? 1 : 0, // Convertir a número
    color: props.asociation.asociacion.color,
    direccion: props.asociation.asociacion.direccion,
    descripcion: props.asociation.asociacion.descripcion,
});

const submit = () => {
    form.patch(route('asociation.index.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Editar" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="rounded-lg bg-white p-6 shadow-lg dark:bg-gray-900">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="name">Nombre de la asociación</Label>
                        <Input id="name" class="mt-1 block w-full" v-model="form.name" required autocomplete="name" placeholder="Full name" />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            v-model="form.email"
                            required
                            autocomplete="username"
                            placeholder="Email address"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="direccion">Dirección</Label>
                        <Input
                            id="direccion"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.direccion"
                            autocomplete="direccion"
                            placeholder="Dirección"
                        />
                        <InputError class="mt-2" :message="form.errors.direccion" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="descripcion">Descripción</Label>
                        <Input
                            id="descripcion"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.descripcion"
                            autocomplete="descripcion"
                            placeholder="Descripción"
                        />
                        <InputError class="mt-2" :message="form.errors.descripcion" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="number_phone">Número de teléfono</Label>
                        <Input
                            id="number_phone"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.number_phone"
                            required
                            autocomplete="number_phone"
                            placeholder="Número de teléfono"
                        />
                        <InputError class="mt-2" :message="form.errors.number_phone" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="number_phone">Color</Label>
                        <Input type="color" class="mt-1 block w-full" v-model="form.color" required />
                        <InputError class="mt-2" :message="form.errors.color" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="city">Ciudad</Label>
                        <select
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
                            v-model="form.city"
                        >
                            <option v-for="ciudad in ciudades" :value="ciudad.name">{{ ciudad.name }}</option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.city" />
                    </div>

                    <!--cambiar por select el input-->
                    <div class="grid gap-2">
                        <Label for="estado">Estado</Label>
                        <select
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
                            v-model="form.estado"
                        >
                        <option :value="0">Inactivo</option>
                        <option :value="1">Activo</option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.estado" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">Guardar</Button>
                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">Guardado.</p>
                        </Transition>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
