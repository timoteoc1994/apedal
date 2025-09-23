<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

const isVisible = ref(false);
const page = usePage();

const user = computed(() => (page.props as any).auth?.user);

onMounted(() => {
    setTimeout(() => {
        isVisible.value = true;
    }, 100);
});
</script>

<template>
    <Head title="Bienvenida a ADRI">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="relative min-h-screen overflow-hidden">
        <!-- Fondo principal con degradado más suave -->
        <div class="animate-gradient-xy absolute inset-0 bg-gradient-to-br from-[#55f094] via-[#5e53e9] to-[#ffb000]"></div>

        <!-- Overlay para mejor contraste -->
        <div class="absolute inset-0 bg-black/20"></div>

        <!-- Formas geométricas flotantes más sutiles -->
        <div class="animate-float absolute left-16 top-16 h-40 w-40 rounded-full bg-[#fb6185] opacity-10 blur-xl"></div>
        <div class="animate-float-delayed absolute right-24 top-1/4 h-32 w-32 rounded-full bg-[#ffb000] opacity-15 blur-lg"></div>
        <div class="opacity-8 animate-float-slow absolute bottom-24 left-1/3 h-56 w-56 rounded-full bg-[#5e53e9] blur-2xl"></div>
        <div class="opacity-12 absolute bottom-40 right-1/4 h-24 w-24 animate-bounce rounded-full bg-[#55f094] blur-md"></div>

        <!-- Contenido principal -->
        <div class="relative z-10 flex min-h-screen flex-col">
            <!-- Header con navegación -->
            <header class="p-6 lg:p-8">
                <nav class="mx-auto flex max-w-7xl items-center justify-between">
                    <!-- Logo -->
                    <div class="flex items-center space-x-3">
                        <div class="p-2">
                            <img src="/storage/plataforma/logocolor.png" alt="ADRI Logo" class="h-10 w-auto" />
                        </div>
                    </div>

                    <!-- Botones de navegación -->
                    <div class="flex items-center gap-3">
                        <Link
                            v-if="user"
                            :href="route('dashboard')"
                            class="rounded-xl bg-white/95 px-6 py-3 font-semibold text-[#5e53e9] backdrop-blur-sm transition-all duration-300 hover:scale-105 hover:bg-white hover:shadow-lg"
                        >
                            Dashboard
                        </Link>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="rounded-xl border border-white/30 bg-white/10 px-6 py-3 font-medium text-white backdrop-blur-sm transition-all duration-300 hover:scale-105 hover:border-white/50 hover:bg-white/20"
                            >
                                Iniciar Sesión
                            </Link>
                        </template>
                    </div>
                </nav>
            </header>

            <!-- Contenido principal -->
            <main class="flex flex-1 items-center justify-center px-6 lg:px-8">
                <div class="mx-auto max-w-6xl text-center">
                    <div :class="['transform transition-all duration-1000', isVisible ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0']">
                        <!-- Título principal -->
                        <h1 class="mb-8 text-6xl font-black leading-tight text-white drop-shadow-2xl lg:text-8xl">
                            Bienvenidos a la
                            <span class="mt-2 block bg-gradient-to-r from-[#ffb000] via-[#fb6185] to-[#ffb000] bg-clip-text text-transparent">
                                Nueva Forma
                            </span>
                            <span class="mt-2 block">de Reciclar</span>
                        </h1>

                        <!-- Botones de descarga de apps -->
                        <div class="space-y-6">
                            <h3 class="mb-8 text-2xl font-bold text-white drop-shadow-lg">Descarga nuestra app</h3>
                            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 w-full">
                                <!-- Google Play (activo) -->
                                <a
                                    href="https://play.google.com/store/apps/details?id=org.renarec.adri" aria-label="Descargar en Google Play" target="_blank"
                                    class="download-badge group flex items-center justify-center rounded-2xl bg-gray-900/90 px-6 py-3 shadow-xl backdrop-blur-sm transition-transform duration-300 transform hover:scale-105 hover:shadow-2xl mx-auto"
                                >
                                    <img src="/storage/plataforma/google-play.png" alt="Google Play" class="w-40 sm:w-48 md:w-56 lg:w-64 object-contain transition-transform duration-300 group-hover:scale-105 mx-auto block"/>
                                </a>

                                <!-- App Store (placeholder, ahora con imagen) -->
                                <a
                                    href="https://apps.apple.com/ec/app/adri-app/id6749082803" target="_blank" aria-label="Próximamente en App Store"
                                    class="download-badge group flex items-center justify-center rounded-2xl bg-gray-700/70 px-6 py-3 shadow-lg backdrop-blur-sm transition-transform duration-300 transform hover:scale-105 hover:shadow-2xl mx-auto"
                                >
                                    <img src="/storage/plataforma/app-store.png" alt="App Store" class="w-40 sm:w-48 md:w-56 lg:w-64 object-contain transition-transform duration-300 group-hover:scale-105 mx-auto block"/>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <footer class="p-6 text-center lg:p-8">
                <div class="mx-auto max-w-7xl">
                    <p class="text-sm font-medium text-white/80 drop-shadow-sm">
                        © 2025 ADRI - Reciclaje Inclusivo. Construyendo un futuro más verde juntos.
                    </p>
                </div>
            </footer>
        </div>
    </div>
</template>

<style scoped>
@keyframes gradient-xy {
    0%,
    100% {
        background-size: 400% 400%;
        background-position: left center;
    }
    50% {
        background-size: 200% 200%;
        background-position: right center;
    }
}

@keyframes float {
    0%,
    100% {
        transform: translateY(0px) rotate(0deg);
    }
    50% {
        transform: translateY(-20px) rotate(5deg);
    }
}

@keyframes float-delayed {
    0%,
    100% {
        transform: translateY(0px) rotate(0deg);
    }
    50% {
        transform: translateY(-30px) rotate(-5deg);
    }
}

@keyframes float-slow {
    0%,
    100% {
        transform: translateY(0px) rotate(0deg);
    }
    50% {
        transform: translateY(-15px) rotate(3deg);
    }
}

.animate-gradient-xy {
    animation: gradient-xy 20s ease infinite;
}

.animate-float {
    animation: float 8s ease-in-out infinite;
}

.animate-float-delayed {
    animation: float-delayed 10s ease-in-out infinite;
}

.animate-float-slow {
    animation: float-slow 12s ease-in-out infinite;
}

/* Efectos de sombra mejorados */
.drop-shadow-2xl {
    filter: drop-shadow(0 25px 25px rgb(0 0 0 / 0.4));
}

.drop-shadow-3xl {
    filter: drop-shadow(0 35px 35px rgb(0 0 0 / 0.5));
}

.shadow-3xl {
    box-shadow: 0 35px 60px -12px rgba(0, 0, 0, 0.25);
}

/* Blur personalizados */
.blur-3xl {
    filter: blur(64px);
}

/* Animación de texto */
@keyframes shimmer {
    0% {
        background-position: -200% 0;
    }
    100% {
        background-position: 200% 0;
    }
}

.text-shimmer {
    background: linear-gradient(45deg, #ffb000, #fb6185, #55f094, #5e53e9, #ffb000);
    background-size: 400% 400%;
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: shimmer 3s ease-in-out infinite;
}

.download-badge {
    /* pequeños ajustes por si se necesita un ancho mínimo y overflow oculto */
    min-width: 120px;
    overflow: hidden;
}

/* Soporte para hover en escritorio y toque en móviles: escala al tocar */
@media (hover: hover) and (pointer: fine) {
    .download-badge:hover img {
        transform: scale(1.06);
    }
}

@media (hover: none) and (pointer: coarse) {
    .download-badge:active img {
        transform: scale(1.06);
    }
}

/* Transición suave para las imágenes dentro de los badges */
.download-badge img {
    transition: transform 250ms ease, box-shadow 250ms ease;
}

/* Sombra ligera al hacer hover */
.download-badge:hover {
    box-shadow: 0 12px 30px rgba(0,0,0,0.25);
}
</style>
