<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';

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
    
    <div class="min-h-screen overflow-hidden relative">
        <!-- Fondo principal con degradado más suave -->
        <div class="absolute inset-0 bg-gradient-to-br from-[#55f094] via-[#5e53e9] to-[#ffb000] animate-gradient-xy"></div>
        
        <!-- Overlay para mejor contraste -->
        <div class="absolute inset-0 bg-black/20"></div>
        
        <!-- Formas geométricas flotantes más sutiles -->
        <div class="absolute top-16 left-16 w-40 h-40 bg-[#fb6185] rounded-full opacity-10 animate-float blur-xl"></div>
        <div class="absolute top-1/4 right-24 w-32 h-32 bg-[#ffb000] rounded-full opacity-15 animate-float-delayed blur-lg"></div>
        <div class="absolute bottom-24 left-1/3 w-56 h-56 bg-[#5e53e9] rounded-full opacity-8 animate-float-slow blur-2xl"></div>
        <div class="absolute bottom-40 right-1/4 w-24 h-24 bg-[#55f094] rounded-full opacity-12 animate-bounce blur-md"></div>
        
       
        
        <!-- Contenido principal -->
        <div class="relative z-10 min-h-screen flex flex-col">
            <!-- Header con navegación -->
            <header class="p-6 lg:p-8">
                <nav class="flex items-center justify-between max-w-7xl mx-auto">
                    <!-- Logo -->
                    <div class="flex items-center space-x-3">
                        <div class="p-2  ">
                            <img src="/storage/plataforma/logocolor.png" alt="ADRI Logo" class="h-10 w-auto" />
                        </div>
                    </div>
                    
                    <!-- Botones de navegación -->
                    <div class="flex items-center gap-3">
                        <Link
                            v-if="user"
                            :href="route('dashboard')"
                            class="px-6 py-3 bg-white/95 backdrop-blur-sm rounded-xl text-[#5e53e9] hover:bg-white hover:shadow-lg transition-all duration-300 hover:scale-105 font-semibold"
                        >
                            Dashboard
                        </Link>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="px-6 py-3 bg-white/10 backdrop-blur-sm border border-white/30 rounded-xl text-white hover:bg-white/20 hover:border-white/50 transition-all duration-300 hover:scale-105 font-medium"
                            >
                                Iniciar Sesión
                            </Link>
                            <Link
                                :href="route('register')"
                                class="px-6 py-3 bg-white/95 backdrop-blur-sm rounded-xl text-[#5e53e9] hover:bg-white hover:shadow-lg transition-all duration-300 hover:scale-105 font-semibold"
                            >
                                Registrarse
                            </Link>
                        </template>
                    </div>
                </nav>
            </header>
            
            <!-- Contenido principal -->
            <main class="flex-1 flex items-center justify-center px-6 lg:px-8">
                <div class="max-w-6xl mx-auto text-center">
                    <div :class="['transition-all duration-1000 transform', isVisible ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0']">
                        <!-- Título principal -->
                        <h1 class="text-6xl lg:text-8xl font-black text-white mb-8 leading-tight drop-shadow-2xl">
                            Bienvenidos a la
                            <span class="block mt-2 bg-gradient-to-r from-[#ffb000] via-[#fb6185] to-[#ffb000] bg-clip-text text-transparent">
                                Nueva Forma
                            </span>
                            <span class="block mt-2">de Reciclar</span>
                        </h1>
                        
                        
                        
                        
                        
                        <!-- Botones de descarga de apps -->
                        <div class="space-y-6">
                            <h3 class="text-2xl font-bold text-white mb-8 drop-shadow-lg">Descarga nuestra app</h3>
                            <div class="flex flex-col sm:flex-row gap-6 justify-center items-center">
                                <!-- Botón Android -->
                                <a 
                                    href="#" 
                                    class="group flex items-center space-x-4 px-8 py-4 bg-gray-900/90 backdrop-blur-sm rounded-2xl hover:bg-gray-900 transition-all duration-300 hover:scale-105 shadow-xl hover:shadow-2xl"
                                >
                                    <div class="w-12 h-12 bg-gradient-to-r from-[#55f094] to-[#5e53e9] rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                        <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="currentColor">
    <path d="M3,20.5V3.5C3,2.91 3.34,2.39 3.84,2.15L13.69,12L3.84,21.85C3.34,21.61 3,21.09 3,20.5M16.81,15.12L6.05,21.34L14.54,12.85L16.81,15.12M20.16,10.81C20.5,11.08 20.75,11.5 20.75,12C20.75,12.5 20.53,12.9 20.18,13.18L17.89,14.5L15.39,12L17.89,9.5L20.16,10.81M6.05,2.66L16.81,8.88L14.54,11.15L6.05,2.66Z"/>
</svg>
                                    </div>
                                    <div class="text-left">
                                        <div class="text-xs text-white/70 font-medium">Disponible en</div>
                                        <div class="text-lg font-bold text-white">Google Play</div>
                                    </div>
                                </a>
                                
                                <!-- Botón iOS (desactivado) -->
                                <div class="flex items-center space-x-4 px-8 py-4 bg-gray-600/30 backdrop-blur-sm rounded-2xl cursor-not-allowed opacity-60 shadow-lg">
                                    <div class="w-12 h-12 bg-gray-500/50 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white/50" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                                        </svg>
                                    </div>
                                    <div class="text-left">
                                        <div class="text-xs text-white/50 font-medium">Próximamente en</div>
                                        <div class="text-lg font-bold text-white/60">App Store</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            
            <!-- Footer -->
            <footer class="p-6 lg:p-8 text-center">
                <div class="max-w-7xl mx-auto">
                    <p class="text-white/80 text-sm font-medium drop-shadow-sm">
                        © 2025 ADRI - Reciclaje Inclusivo. Construyendo un futuro más verde juntos.
                    </p>
                </div>
            </footer>
        </div>
    </div>
</template>

<style scoped>
@keyframes gradient-xy {
    0%, 100% {
        background-size: 400% 400%;
        background-position: left center;
    }
    50% {
        background-size: 200% 200%;
        background-position: right center;
    }
}

@keyframes float {
    0%, 100% {
        transform: translateY(0px) rotate(0deg);
    }
    50% {
        transform: translateY(-20px) rotate(5deg);
    }
}

@keyframes float-delayed {
    0%, 100% {
        transform: translateY(0px) rotate(0deg);
    }
    50% {
        transform: translateY(-30px) rotate(-5deg);
    }
}

@keyframes float-slow {
    0%, 100% {
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
</style>
