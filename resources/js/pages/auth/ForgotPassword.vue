<template>
    <Head title="Recuperar contraseña" />

    <!-- component -->
    <div
        class="flex h-screen w-full items-center justify-center bg-cover bg-no-repeat"
        style="background-color: #5849e2;"
    >
        <div
            class="rounded-xl bg-gray-800 bg-opacity-50 px-16 py-10 shadow-lg backdrop-blur-md max-sm:px-8"
        >
            <div class="text-white">
                <div class="mb-8 flex flex-col items-center pt-5">
                    <img src="storage/plataforma/logoplataforma.png" width="200px" alt="">
                    <div class="flex justify-center items-center space-x-1 pt-5">
                        <span class="w-3 h-3 rounded-full bg-white inline-block"></span>
                        <span class="w-3 h-3 rounded-full" style="background-color: #ffb000;"></span>
                        <span class="w-3 h-3 rounded-full bg-green-400 inline-block"></span>
                        <span class="w-3 h-3 rounded-full bg-pink-400 inline-block"></span>
                    </div>
                    <h1 class="mb-2 text-2xl">Recuperar Contraseña</h1>
                    <span class="text-gray-300 text-center leading-4">
                        ¿Olvidaste tu contraseña? No hay problema. <br>
                        Ingresa tu correo electrónico y te enviaremos <br>
                        un enlace para restablecer tu contraseña.
                    </span>
                </div>

                <div v-if="status" class="mb-4 text-sm font-medium text-green-400 text-center">
                    {{ status }}
                </div>

                <form @submit.prevent="submit">
                    <div class="mb-4 text-lg">
                        <TextInput
                            id="email"
                            type="email"
                            class="rounded-3xl border-none bg-white text-black px-6 py-2 text-center placeholder-black-200 shadow-lg outline-none backdrop-blur-md w-full"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="Correo electrónico"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div class="mt-8 flex justify-center text-lg text-black">
                        <PrimaryButton
                            class="rounded-3xl bg-white px-10 py-2 !text-black shadow-xl backdrop-blur-md transition-colors duration-300 hover:bg-indigo-600 w-full"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            Enviar enlace de recuperación
                        </PrimaryButton>
                    </div>

                    <div class="mt-6 flex justify-center">
                        <Link
                            :href="route('login')"
                            class="text-sm text-white underline hover:text-gray-300"
                        >
                            Volver al inicio de sesión
                        </Link>
                    </div>

                    <div class="mt-8 flex justify-center">
                        <img src="storage/plataforma/renarec.png" width="150px" alt="">
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>