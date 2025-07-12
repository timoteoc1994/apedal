<template>
    <Head title="Inciar sesión" />

    <!-- component -->
    <div
        class="flex h-screen w-full items-center justify-center  bg-cover bg-no-repeat"
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
                    <h1 class="mb-2 text-2xl">Iniciar Sesion</h1>
                    <span class="text-gray-300 text-center leading-4">
                        Ingresa tu correo electrónico y contraseña <br>
                        a continuación para iniciar sesión.
                    </span>
                </div>
                <form @submit.prevent="submit">
                    <div class="mb-4 text-lg">
                        <TextInput
                            id="email"
                            type="email"
                            class="rounded-3xl border-none bg-white text-black  px-6 py-2 text-center  placeholder-black-200 shadow-lg outline-none backdrop-blur-md"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="Correo electrónico"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div class="mb-4 text-lg">
                        <div class="mt-3">
                            <TextInput
                                id="password"
                                type="password"
                                class="rounded-3xl border-none text-black bg-white  text-black px-6 py-2 text-center  placeholder-black-200 shadow-lg outline-none backdrop-blur-md"
                                v-model="form.password"
                                required
                                autocomplete="current-password"
                                placeholder="Contraseña"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.password"
                            />
                        </div>
                    </div>

                    <div class="mt-4 flex justify-between">
                        <label class="inline-flex items-center">
                            <Checkbox
                                name="remember"
                                v-model:checked="form.remember"
                            />
                            <span class="mx-2 text-sm text-white"
                                >Recuerdame</span
                            >
                        </label>

                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-sm text-white underline hover:text-gray-900"
                        >
                            Recuperar contraseña?
                        </Link>
                    </div>
                    <div class="mt-8 flex justify-center text-lg text-black">
                        <PrimaryButton
                            class="rounded-3xl bg-white px-10 py-2 !text-black shadow-xl backdrop-blur-md transition-colors duration-300 hover:bg-indigo-600"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            Iniciar sesión
                        </PrimaryButton>
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
import Checkbox from "@/Components/Checkbox.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>
