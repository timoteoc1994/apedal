<template>
    <Head title="Soporte - ADRI" />

    <div class="min-h-screen bg-gray-50 flex flex-col">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-gray-900">ADRI</h1>
                        <span class="ml-3 text-lg text-gray-600">Soporte y Ayuda</span>
                    </div>
                    <div class="text-sm text-gray-500">
                        Reciclaje Inclusivo Ecuador
                    </div>
                </div>
            </div>
        </header>

        <!-- Contenido principal -->
        <main class="flex-1">
            <div class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm rounded-lg p-8">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">¿Necesitas ayuda con ADRI?</h2>
                    <p class="text-gray-700 mb-6">
                        Si tienes dudas, problemas técnicos o necesitas soporte sobre el uso de la aplicación ADRI, aquí encontrarás información útil y formas de contactarnos.
                    </p>

                    <section class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">¿Qué es ADRI?</h3>
                        <p class="text-gray-600">
                            ADRI es una aplicación móvil que conecta a la ciudadanía con recicladoras y recicladores de base en Ecuador, facilitando la gestión responsable de residuos reciclables y promoviendo la inclusión social y ambiental.
                        </p>
                    </section>

                    <section class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">Preguntas Frecuentes</h3>
                        <ul class="list-disc list-inside text-gray-700 space-y-2">
                            <li><strong>¿Cómo creo una cuenta?</strong> Descarga la app y sigue el proceso de registro con tus datos personales.</li>
                            <li><strong>¿Por qué necesito compartir mi ubicación?</strong> Para conectar con recicladores cercanos y coordinar recolecciones de manera eficiente.</li>
                            <li><strong>¿Cómo solicito una recolección?</strong> Ingresa a la app, selecciona el tipo de residuo y tu ubicación, y envía la solicitud.</li>
                            <li><strong>¿Qué hago si tengo un problema técnico?</strong> Contáctanos usando los medios abajo indicados.</li>
                        </ul>
                    </section>

                    <section class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">Contacto de Soporte</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="mb-2"><strong>Email-Coordinador:</strong> <a href="mailto:coordinacion@renarec.org" class="text-blue-700 underline">coordinacion@renarec.org</a></p>
                            <p class="mb-2"><strong>Email-Soporte:</strong> <a href="mailto:timoteo.cain@ninari.org" class="text-blue-700 underline">timoteo.cain@ninari.org</a></p>
                            <p class="mb-2"><strong>Teléfono:</strong> <a href="tel:+593999999999" class="text-blue-700 underline">+593 987862071</a></p>
                            <p><strong>Dirección:</strong> Quito, Avenida Colón E5-56 y Juan León Mera, Edificio Ave María, bloque B, piso 6.</p>
                        </div>
                    </section>

                    <section>
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">¿Tienes sugerencias?</h3>
                        <p class="text-gray-600">
                            Tu opinión es importante para mejorar ADRI. Escríbenos si tienes ideas, comentarios o sugerencias.
                        </p>
                    </section>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 py-4 text-center text-gray-500 text-sm">
            © 2025 ADRI - Reciclaje Inclusivo Ecuador
        </footer>
    </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
</script>