<template>
    <Head title="Productos" />

    <AuthenticatedLayout>
        <template #header>
            <Link :href="route('producto.index')" class="text-indigo-600 hover:scale-105 hover:text-indigo-900"> Empresas/ </Link>
            {{ tienda.name }}
        </template>

        <div class="mt-6 rounded-xl bg-white p-8 shadow-xl">
            <button
                @click="abrirModal"
                class="text-2sm mb-4 flex items-center gap-2 rounded-lg bg-gradient-to-r from-purple-600 to-indigo-600 px-6 py-2 font-semibold text-white shadow-lg transition-all duration-300 hover:from-indigo-700 hover:to-purple-700"
            >
                <span class="text-2xl">+</span> Nuevo producto
            </button>

            <!-- Sección de productos compacta -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                <div
                    v-for="producto in productos.data"
                    :key="producto.id"
                    class="group relative overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm transition-all duration-300 hover:shadow-lg"
                >
                    <!-- Badge de estado -->
                    <div class="absolute right-2 top-2 z-10">
                        <span
                            class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium backdrop-blur-sm"
                            :class="{
                                'bg-green-100/90 text-green-800': producto.estado === 'publicado',
                                'bg-yellow-100/90 text-yellow-800': producto.estado === 'borrador',
                                
                            }"
                        >
                            <div
                                class="mr-1 h-1.5 w-1.5 rounded-full"
                                :class="{
                                    'bg-green-500': producto.estado === 'publicado',
                                    'bg-yellow-500': producto.estado === 'borrador',
                             
                                }"
                            ></div>
                            {{ producto.estado.charAt(0).toUpperCase() + producto.estado.slice(1) }}
                        </span>
                    </div>

                    <!-- Imagen del producto -->
                    <div class="aspect-square overflow-hidden bg-gradient-to-br from-gray-50 to-gray-100">
                        <img
                            :src="getImageUrl(producto.url_imagen)"
                            :alt="producto.nombre"
                            class="h-full w-full object-cover transition-transform duration-300"
                            @error="handleImageError"
                        />
                    </div>

                    <!-- Contenido compacto -->
                    <div class="p-3">
                        <!-- Nombre -->
                        <h3 class="mb-2 line-clamp-2 text-sm font-semibold text-gray-900 transition-colors group-hover:text-indigo-600">
                            {{ producto.nombre }}
                        </h3>

                        <!-- Puntos -->
                        <div class="mb-3 flex items-center justify-center rounded-lg bg-gradient-to-r from-yellow-50 to-orange-50 p-2">
                            <div class="flex items-center gap-1">
                                <svg class="h-4 w-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                                    />
                                </svg>
                                <span class="text-lg font-bold text-gray-900">{{ producto.puntos }}</span>
                                <span class="text-xs text-gray-600">pts</span>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="flex gap-1">
                            <button
                                @click="verDetalles(producto)"
                                class="flex flex-1 items-center justify-center gap-1 rounded-md bg-indigo-50 px-2 py-1.5 text-xs font-medium text-indigo-700 transition-colors hover:bg-indigo-100"
                            >
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                    ></path>
                                </svg>
                                Ver
                            </button>
                            <button
                                @click="editarProducto(producto)"
                                class="flex flex-1 items-center justify-center gap-1 rounded-md bg-blue-50 px-2 py-1.5 text-xs font-medium text-blue-700 transition-colors hover:bg-blue-100"
                            >
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                    ></path>
                                </svg>
                                Editar
                            </button>
                            <button
                                @click="eliminarProducto(producto.id)"
                                class="flex items-center justify-center rounded-md bg-red-50 px-2 py-1.5 text-xs font-medium text-red-700 transition-colors hover:bg-red-100"
                            >
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                    ></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Paginación -->
        <div class="mt-8 flex flex-col items-center justify-between gap-4 sm:flex-row">
            <div class="text-sm text-gray-600">
                Mostrando
                <span class="font-semibold">{{ (currentPage - 1) * productos.per_page + 1 }}</span>
                a
                <span class="font-semibold">{{ Math.min(currentPage * productos.per_page, productos.total) }}</span>
                de
                <span class="font-semibold">{{ productos.total }}</span>
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

        <!-- Modal de detalles del producto -->
        <div v-if="mostrarModalDetalles" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
            <div class="mx-4 w-full max-w-md rounded-xl bg-white shadow-2xl">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Detalles del Producto</h3>
                    <button @click="cerrarModalDetalles" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Contenido -->
                <div class="space-y-4 p-6">
                    <!-- Nombre -->
                    <div>
                        <h4 class="text-xl font-bold text-gray-900">{{ productoSeleccionado?.nombre }}</h4>
                    </div>

                    <!-- Estado y Tipo -->
                    <div class="flex gap-2">
                        <span
                            class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium"
                            :class="{
                                'bg-green-100 text-green-800': productoSeleccionado?.estado === 'publicado',
                                'bg-yellow-100 text-yellow-800': productoSeleccionado?.estado === 'borrador',
                                
                            }"
                        >
                            <div
                                class="mr-2 h-2 w-2 rounded-full"
                                :class="{
                                    'bg-green-500': productoSeleccionado?.estado === 'publicado',
                                    'bg-yellow-500': productoSeleccionado?.estado === 'borrador',
                                    
                                }"
                            ></div>
                            {{ productoSeleccionado?.estado?.charAt(0).toUpperCase() + productoSeleccionado?.estado?.slice(1) }}
                        </span>
                        <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-800">
                            {{ productoSeleccionado?.tipo_producto }}
                        </span>
                    </div>

                    <!-- Puntos -->
                    <div class="flex items-center justify-center rounded-lg bg-gradient-to-r from-yellow-50 to-orange-50 p-4">
                        <div class="flex items-center gap-2">
                            <svg class="h-6 w-6 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                                />
                            </svg>
                            <span class="text-2xl font-bold text-gray-900">{{ productoSeleccionado?.puntos }}</span>
                            <span class="text-sm text-gray-600">puntos</span>
                        </div>
                    </div>

                    <!-- Categoría -->
                    <div>
                        <p class="mb-1 text-sm font-medium text-gray-700">Categoría</p>
                        <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-700">
                            <svg class="mr-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    fill-rule="evenodd"
                                    d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            {{ productoSeleccionado?.categoria }}
                        </span>
                    </div>

                    <!-- Descripción -->
                    <div>
                        <p class="mb-2 text-sm font-medium text-gray-700">Descripción</p>
                        <p class="text-sm leading-relaxed text-gray-600">
                            {{ productoSeleccionado?.descripcion || 'Sin descripción disponible' }}
                        </p>
                    </div>

                    <!-- Dirección de reclamo -->
                    <div v-if="productoSeleccionado?.direccion_reclamo">
                        <p class="mb-2 text-sm font-medium text-gray-700">Dirección de reclamo</p>
                        <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                ></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <p class="text-sm text-gray-600">{{ productoSeleccionado?.direccion_reclamo }}</p>
                        </div>
                    </div>

                    <!-- Información adicional -->
                    <div class="border-t pt-4">
                        <div class="grid grid-cols-1 gap-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">ID:</span>
                                <span class="font-medium text-gray-900">#{{ productoSeleccionado?.id }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Creado:</span>
                                <span class="font-medium text-gray-900">{{ formatDate(productoSeleccionado?.created_at) }}</span>
                            </div>
                            <div v-if="productoSeleccionado?.updated_at !== productoSeleccionado?.created_at" class="flex justify-between">
                                <span class="text-gray-500">Actualizado:</span>
                                <span class="font-medium text-gray-900">{{ formatDate(productoSeleccionado?.updated_at) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="border-t border-gray-200 p-6">
                    <div class="flex gap-3">
                        <button
                            @click="editarProducto(productoSeleccionado)"
                            class="flex flex-1 items-center justify-center gap-2 rounded-lg bg-blue-50 px-4 py-2 text-sm font-medium text-blue-700 transition-colors hover:bg-blue-100"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                ></path>
                            </svg>
                            Editar
                        </button>
                        <button
                            @click="cerrarModalDetalles"
                            class="flex-1 rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-200"
                        >
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Nuevo/Editar Producto -->
        <div v-if="mostrarModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
            <div class="mx-4 flex max-h-[90vh] w-full max-w-lg flex-col rounded-lg bg-white">
                <!-- Header fijo -->
                <div class="flex items-center justify-between border-b border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ productoEditando ? 'Editar Producto' : 'Nuevo Producto' }}
                    </h3>
                    <button @click="cerrarModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Contenido scrollable -->
                <div class="flex-1 overflow-y-auto p-6">
                    <form @submit.prevent="productoEditando ? actualizarTienda() : crearTienda()" class="space-y-4">
                        <!-- Tipo de Producto -->
                        <div>
                            <label for="tipo_producto" class="mb-1 block text-sm font-medium text-gray-700">
                                Tipo del producto <span class="text-red-500">*</span></label
                            >
                            <select
                                id="tipo_producto"
                                v-model="form.tipo_producto"
                                required
                                class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-purple-500"
                            >
                                <option value="">Selecciona un tipo</option>
                                <option value="Físico">Físico</option>
                                <option value="Digital">Digital</option>
                                <option value="Servicio">Servicio</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.tipo_producto" />
                        </div>

                        <!-- Nombre -->
                        <div>
                            <label for="nombre" class="mb-1 block text-sm font-medium text-gray-700">
                                Nombre del producto <span class="text-red-500">*</span></label
                            >
                            <input
                                type="text"
                                id="nombre"
                                v-model="form.nombre"
                                required
                                class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-purple-500"
                                placeholder="Ingresa el nombre del producto"
                            />
                            <InputError class="mt-2" :message="form.errors.nombre" />
                        </div>

                        <!-- Descripción -->
                        <div>
                            <label for="descripcion" class="mb-1 block text-sm font-medium text-gray-700">
                                Descripción <span class="text-red-500">*</span></label
                            >
                            <textarea
                                id="descripcion"
                                v-model="form.descripcion"
                                rows="3"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-purple-500"
                                placeholder="Describe el producto..."
                            ></textarea>
                            <InputError class="mt-2" :message="form.errors.descripcion" />
                        </div>

                        <!-- Dirección de Reclamo -->
                        <div>
                            <label for="direccion_reclamo" class="mb-1 block text-sm font-medium text-gray-700">
                                Dirección de reclamo <span class="text-red-500">*</span></label
                            >
                            <input
                                type="text"
                                id="direccion_reclamo"
                                v-model="form.direccion_reclamo"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-purple-500"
                                placeholder="Dirección donde se puede reclamar"
                            />
                            <InputError class="mt-2" :message="form.errors.direccion_reclamo" />
                        </div>

                        <!-- URL de Imagen -->
                        <div>
                            <label for="url_imagen" class="mb-1 block text-sm font-medium text-gray-700">
                                Imagen del producto
                                <span v-if="!productoEditando" class="text-red-500">*</span>
                                <span v-else class="text-gray-500">(opcional - dejar vacío para mantener actual)</span>
                            </label>

                            <!-- Input de archivo con drag & drop -->
                            <div
                                class="relative rounded-md border-2 border-dashed border-gray-300 p-6 transition-colors hover:border-gray-400"
                                :class="{ 'border-purple-500 bg-purple-50': isDragging }"
                                @dragover.prevent="isDragging = true"
                                @dragleave.prevent="isDragging = false"
                                @drop.prevent="handleDrop"
                            >
                                <input
                                    type="file"
                                    id="url_imagen"
                                    ref="fileInput"
                                    @change="handleFileSelect"
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
                                            o arrastra y suelta una imagen
                                        </p>
                                        <p class="mt-1 text-xs text-gray-500">
                                            PNG, JPG, GIF hasta 10MB
                                            <span v-if="productoEditando" class="block text-purple-600">
                                                • Dejar vacío para mantener imagen actual
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Vista previa de la imagen -->
                            <div v-if="imagePreview" class="mt-4">
                                <div class="relative inline-block">
                                    <img :src="imagePreview" alt="Vista previa" class="h-32 w-32 rounded-lg object-cover shadow-md" />
                                    <button
                                        @click="removeImage"
                                        type="button"
                                        class="absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white transition-colors hover:bg-red-600"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                                <p class="mt-2 text-sm text-gray-600">
                                    {{ fileName }}
                                    <span v-if="productoEditando && imagenExistente && !form.url_imagen" class="text-green-600">
                                        (Imagen actual)
                                    </span>
                                    <span v-else-if="productoEditando && form.url_imagen" class="text-blue-600"> (Nueva imagen) </span>
                                </p>
                            </div>

                            <InputError class="mt-2" :message="form.errors.url_imagen" />
                        </div>

                        <!-- Categoría -->
                        <div>
                            <label for="categoria" class="mb-1 block text-sm font-medium text-gray-700">
                                Categoría <span class="text-red-500">*</span></label
                            >
                            <select
                                id="categoria"
                                v-model="form.categoria"
                                required
                                class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-purple-500"
                            >
                                <option value="">Selecciona una categoría</option>
                                <option value="Electrónicos">Electrónicos</option>
                                <option value="Ropa">Ropa</option>
                                <option value="Hogar">Hogar</option>
                                <option value="Deportes">Deportes</option>
                                <option value="Alimentación">Alimentación</option>
                                <option value="Salud">Salud</option>
                                <option value="Otros">Otros</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.categoria" />
                        </div>

                        <!-- Puntos y Estado en fila -->
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Puntos -->
                            <div>
                                <label for="puntos" class="mb-1 block text-sm font-medium text-gray-700">
                                    Puntos <span class="text-red-500">*</span></label
                                >
                                <input
                                    type="number"
                                    id="puntos"
                                    v-model="form.puntos"
                                    min="0"
                                    required
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-purple-500"
                                    placeholder="0"
                                />
                                <InputError class="mt-2" :message="form.errors.puntos" />
                            </div>

                            <!-- Estado -->
                            <div>
                                <label for="estado" class="mb-1 block text-sm font-medium text-gray-700">
                                    Estado <span class="text-red-500">*</span></label
                                >
                                <select
                                    id="estado"
                                    v-model="form.estado"
                                    required
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-purple-500"
                                >
                                    <option value="">Selecciona estado</option>
                                    <option value="publicado">Publicado</option>
                                    <option value="borrador">Borrador</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.estado" />
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Footer fijo -->
                <div class="border-t border-gray-200 p-6">
                    <div class="flex justify-end gap-3">
                        <button
                            type="button"
                            @click="cerrarModal"
                            class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-200"
                        >
                            Cancelar
                        </button>
                        <Button
                            @click="productoEditando ? actualizarTienda() : crearTienda()"
                            :disabled="form.processing"
                            class="rounded-lg bg-indigo-600 px-6 py-2 font-semibold text-white shadow transition-all duration-300 hover:bg-indigo-700"
                        >
                            {{ form.processing ? 'Guardando...' : productoEditando ? 'Actualizar' : 'Crear' }}
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import InputError from '@/Components/InputError.vue';
import { Button } from '@/Components/ui/button';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    tienda: Object,
    productos: Object,
});

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
    { immediate: true },
);

// Estado del modal
const mostrarModal = ref(false);
const productoEditando = ref(null);

// Estado del modal de detalles
const mostrarModalDetalles = ref(false);
const productoSeleccionado = ref(null);

// Estados para el manejo de archivos
const fileInput = ref(null);
const isDragging = ref(false);
const imagePreview = ref(null);
const fileName = ref('');

// Formulario con useForm de Inertia
const form = useForm({
    tipo_producto: '',
    nombre: '',
    descripcion: '',
    direccion_reclamo: '',
    url_imagen: null, // Cambiar a null para archivos
    categoria: '',
    puntos: '',
    estado: 'publicado',
});

// Funciones para manejo de archivos
const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (file) {
        processFile(file);
    }
};

const handleDrop = (event) => {
    isDragging.value = false;
    const file = event.dataTransfer.files[0];
    if (file) {
        processFile(file);
    }
};

const processFile = (file) => {
    // Validar tipo de archivo
    if (!file.type.startsWith('image/')) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Solo se permiten archivos de imagen',
        });
        return;
    }

    // Validar tamaño (10MB máximo)
    if (file.size > 10 * 1024 * 1024) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'El archivo es demasiado grande. Máximo 10MB',
        });
        return;
    }

    // Asignar archivo al formulario
    form.url_imagen = file;
    fileName.value = file.name;

    // Crear vista previa
    const reader = new FileReader();
    reader.onload = (e) => {
        imagePreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

const removeImage = () => {
    form.url_imagen = null;
    imagePreview.value = null;
    fileName.value = '';
    // Si estamos editando, también limpiar la imagen existente
    if (productoEditando.value) {
        imagenExistente.value = null;
    }
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

// Funciones del modal
const abrirModal = () => {
    productoEditando.value = null;
    mostrarModal.value = true;
};

const cerrarModal = () => {
    mostrarModal.value = false;
    productoEditando.value = null;
    form.reset();
    form.estado = 'publicado'; // Valor por defecto

    // Limpiar estados de imagen
    imagePreview.value = null;
    fileName.value = '';
    isDragging.value = false;
    imagenExistente.value = null;
};

// Funciones del modal de detalles
const verDetalles = (producto) => {
    productoSeleccionado.value = producto;
    mostrarModalDetalles.value = true;
};

const cerrarModalDetalles = () => {
    mostrarModalDetalles.value = false;
    productoSeleccionado.value = null;
};

// Agregar nueva variable para manejar imagen existente
const imagenExistente = ref(null);
// Función para editar producto (ACTUALIZADA)
const editarProducto = (producto) => {
    productoEditando.value = producto;

    // Rellenar el formulario con los datos del producto
    form.tipo_producto = producto.tipo_producto;
    form.nombre = producto.nombre;
    form.descripcion = producto.descripcion;
    form.direccion_reclamo = producto.direccion_reclamo;
    form.categoria = producto.categoria;
    form.puntos = producto.puntos;
    form.estado = producto.estado;

    // Manejar imagen existente
    if (producto.url_imagen) {
        imagenExistente.value = producto.url_imagen;
        imagePreview.value = getImageUrl(producto.url_imagen);
        fileName.value = 'Imagen actual';
    } else {
        imagenExistente.value = null;
        imagePreview.value = null;
        fileName.value = '';
    }

    // No asignar archivo al formulario para mantener imagen existente
    form.url_imagen = null;

    // Cerrar modal de detalles si está abierto
    if (mostrarModalDetalles.value) {
        cerrarModalDetalles();
    }

    mostrarModal.value = true;
};

// Función para eliminar producto
const eliminarProducto = (id) => {
    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
    }).then((result) => {
        if (result.isConfirmed) {
            // Hacer la petición DELETE
            router.delete(route('producto.index.tienda.eliminar', [id]), {
                onSuccess: () => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Eliminado',
                        text: 'El producto ha sido eliminado correctamente',
                        timer: 2000,
                        showConfirmButton: false,
                    });
                },
                onError: () => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo eliminar el producto',
                    });
                },
            });
        }
    });
};

// Función para crear producto
const crearTienda = () => {
    form.post(route('producto.index.imagen.tiendas', props.tienda.id), {
        onSuccess: () => {
            cerrarModal();
        },
        onError: (errors) => {
            console.log('Errores:', errors);
        },
    });
};

// Función para actualizar producto (ACTUALIZADA)
const actualizarTienda = () => {
    // Siempre usar FormData para manejar archivos correctamente
    const formData = new FormData();
    formData.append('_method', 'PUT');
    formData.append('tipo_producto', form.tipo_producto);
    formData.append('nombre', form.nombre);
    formData.append('descripcion', form.descripcion);
    formData.append('direccion_reclamo', form.direccion_reclamo);
    formData.append('categoria', form.categoria);
    formData.append('puntos', form.puntos);
    formData.append('estado', form.estado);
    
    // Solo agregar imagen si hay una nueva
    if (form.url_imagen) {
        formData.append('url_imagen', form.url_imagen);
    }

    router.post(route('producto.index.tienda.actualizar', [productoEditando.value.id]), formData, {
        onSuccess: () => {
            cerrarModal();
        },
        onError: (errors) => {
            console.log('Errores:', errors);
        },
    });
};
// Agregar esta computed property
const baseUrl = computed(() => {
    return window.location.origin;
});

const getImageUrl = (urlImagen) => {
    if (!urlImagen) return '/images/placeholder-product.jpg';

    // Si es una ruta absoluta de Windows, probablemente sea un error
    if (urlImagen.includes('C:\\Windows\\Temp\\')) {
        return '/images/placeholder-product.jpg';
    }

    // Si ya tiene el dominio, devolverla tal como está
    if (urlImagen.startsWith('http')) {
        return urlImagen;
    }

    // Si es una ruta relativa, agregar el baseUrl
    return `${baseUrl.value}/storage/${urlImagen}`;
};

// Función para manejar errores de imagen
const handleImageError = (event) => {
    event.target.src = '/images/placeholder-product.jpg';
};

// Función para formatear fechas
const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
// Computed properties para la paginación
const currentPage = computed(() => {
    return props.productos.current_page || 1;
});

const totalPages = computed(() => {
    return props.productos.last_page || 1;
});

const displayedPages = computed(() => {
    const pages = [];
    const total = totalPages.value;
    const current = currentPage.value;

    // Mostrar máximo 5 páginas
    const start = Math.max(1, current - 2);
    const end = Math.min(total, current + 2);

    for (let i = start; i <= end; i++) {
        pages.push(i);
    }

    return pages;
});

// Función para navegar a una página específica
const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value && page !== currentPage.value) {
        router.get(
            route('productos.index', props.tienda.id),
            {
                page: page,
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    }
};
</script>
