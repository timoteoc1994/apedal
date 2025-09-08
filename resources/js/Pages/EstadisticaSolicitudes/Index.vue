<template>
    <Head title="Estadistica Solicitudes" />
    <AuthenticatedLayout :auth="$page.props.auth" :errors="$page.props.errors">
        <template #header> 
            <div class="flex items-center gap-3">
                <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                Estadísticas de Solicitudes
            </div>
        </template>

        <!-- Formulario de Búsqueda Mejorado -->
        <div class="mt-6">
            <div class="bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-xl border border-blue-100 overflow-hidden">
                <!-- Header del formulario -->
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-8 py-6">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-white/20 flex items-center justify-center">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-white">Filtros de Búsqueda</h2>
                            <p class="text-blue-100 text-sm">Configure los parámetros para generar las estadísticas</p>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="buscarMatriz" class="p-8 space-y-8">
                    <!-- Grid de filtros -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Ciudad -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2">
                                <div class="h-8 w-8 rounded-lg bg-green-100 flex items-center justify-center">
                                    <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <label class="text-lg font-semibold text-gray-800">Ciudad</label>
                                <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full font-medium">Requerido</span>
                            </div>
                            
                            <!-- Select oculto para funcionalidad -->
                            <select multiple v-model="ciudadSeleccionada" @change="onCiudadChange" class="sr-only">
                                <option v-for="c in ciudades" :key="c.id" :value="c.name">{{ c.name }}</option>
                            </select>
                            
                            <!-- Lista de opciones clickeables -->
                            <div class="max-h-48 overflow-y-auto bg-white rounded-xl border-2 border-gray-200 p-4">
                                <div class="grid gap-2">
                                    <label 
                                        v-for="c in ciudades" 
                                        :key="c.id"
                                        class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-green-300 hover:bg-green-50 cursor-pointer transition-all duration-200"
                                        :class="{ 'border-green-400 bg-green-50': ciudadSeleccionada.includes(c.name) }"
                                    >
                                        <input 
                                            type="checkbox" 
                                            :value="c.name" 
                                            v-model="ciudadSeleccionada"
                                            @change="onCiudadChange"
                                            class="h-4 w-4 text-green-600 border-2 border-gray-300 rounded focus:ring-green-500"
                                        >
                                        <span class="font-medium text-gray-700">{{ c.name }}</span>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Tags seleccionadas -->
                            <div v-if="ciudadSeleccionada.length > 0" class="flex flex-wrap gap-2">
                                <span 
                                    v-for="ciudad in ciudadSeleccionada" 
                                    :key="ciudad"
                                    class="inline-flex items-center gap-2 bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium"
                                >
                                    {{ ciudad }}
                                    <button 
                                        type="button" 
                                        @click="removeTag(ciudadSeleccionada, ciudad)"
                                        class="hover:bg-green-200 rounded-full p-0.5 transition-colors"
                                    >
                                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                        </svg>
                                    </button>
                                </span>
                            </div>
                        </div>

                        <!-- Asociación -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2">
                                <div class="h-8 w-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                    <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <label class="text-lg font-semibold text-gray-800">Asociación</label>
                                <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full font-medium">Requerido</span>
                            </div>

                            <select multiple v-model="asociacionSeleccionada" class="sr-only">
                                <option v-for="a in asociacionesFiltradas" :key="a.id" :value="a.id">{{ a.nombre }}</option>
                            </select>

                            <div class="max-h-48 overflow-y-auto bg-white rounded-xl border-2 border-gray-200 p-4">
                                <div v-if="asociacionesFiltradas.length === 0" class="text-center py-8">
                                    <div class="h-16 w-16 mx-auto rounded-full bg-gray-100 flex items-center justify-center mb-4">
                                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <p class="text-gray-500 font-medium">Seleccione ciudad(es) primero</p>
                                    <p class="text-gray-400 text-sm">Las asociaciones se filtrarán automáticamente</p>
                                </div>
                                <div v-else class="grid gap-2">
                                    <label 
                                        v-for="a in asociacionesFiltradas" 
                                        :key="a.id"
                                        class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50 cursor-pointer transition-all duration-200"
                                        :class="{ 'border-blue-400 bg-blue-50': asociacionSeleccionada.includes(a.id) }"
                                    >
                                        <input 
                                            type="checkbox" 
                                            :value="a.id" 
                                            v-model="asociacionSeleccionada"
                                            class="h-4 w-4 text-blue-600 border-2 border-gray-300 rounded focus:ring-blue-500"
                                        >
                                        <span class="font-medium text-gray-700">{{ a.nombre }}</span>
                                    </label>
                                </div>
                            </div>

                            <div v-if="asociacionSeleccionada.length > 0" class="flex flex-wrap gap-2">
                                <span 
                                    v-for="asociacionId in asociacionSeleccionada" 
                                    :key="asociacionId"
                                    class="inline-flex items-center gap-2 bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium"
                                >
                                    {{ getAsociacionNombre(asociacionId) }}
                                    <button 
                                        type="button" 
                                        @click="removeTag(asociacionSeleccionada, asociacionId)"
                                        class="hover:bg-blue-200 rounded-full p-0.5 transition-colors"
                                    >
                                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                        </svg>
                                    </button>
                                </span>
                            </div>
                        </div>

                        <!-- Mes -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2">
                                <div class="h-8 w-8 rounded-lg bg-purple-100 flex items-center justify-center">
                                    <svg class="h-4 w-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <label class="text-lg font-semibold text-gray-800">Mes</label>
                                <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full font-medium">Requerido</span>
                            </div>

                            <select multiple v-model="mesSeleccionado" class="sr-only">
                                <option v-for="(mes, idx) in meses" :key="idx" :value="numeromeses[idx]">{{ mes }}</option>
                            </select>

                            <div class="max-h-48 overflow-y-auto bg-white rounded-xl border-2 border-gray-200 p-4">
                                <div class="grid grid-cols-2 gap-2">
                                    <label 
                                        v-for="(mes, idx) in meses" 
                                        :key="idx"
                                        class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-purple-300 hover:bg-purple-50 cursor-pointer transition-all duration-200"
                                        :class="{ 'border-purple-400 bg-purple-50': mesSeleccionado.includes(numeromeses[idx]) }"
                                    >
                                        <input 
                                            type="checkbox" 
                                            :value="numeromeses[idx]" 
                                            v-model="mesSeleccionado"
                                            class="h-4 w-4 text-purple-600 border-2 border-gray-300 rounded focus:ring-purple-500"
                                        >
                                        <span class="font-medium text-gray-700 text-sm">{{ mes }}</span>
                                    </label>
                                </div>
                            </div>

                            <div v-if="mesSeleccionado.length > 0" class="flex flex-wrap gap-2">
                                <span 
                                    v-for="mesNum in mesSeleccionado" 
                                    :key="mesNum"
                                    class="inline-flex items-center gap-2 bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm font-medium"
                                >
                                    {{ getMesNombre(mesNum) }}
                                    <button 
                                        type="button" 
                                        @click="removeTag(mesSeleccionado, mesNum)"
                                        class="hover:bg-purple-200 rounded-full p-0.5 transition-colors"
                                    >
                                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                        </svg>
                                    </button>
                                </span>
                            </div>
                        </div>

                        <!-- Año -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2">
                                <div class="h-8 w-8 rounded-lg bg-orange-100 flex items-center justify-center">
                                    <svg class="h-4 w-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <label class="text-lg font-semibold text-gray-800">Año</label>
                                <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full font-medium">Requerido</span>
                            </div>

                            <select multiple v-model="anioSeleccionado" class="sr-only">
                                <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                            </select>

                            <div class="max-h-48 overflow-y-auto bg-white rounded-xl border-2 border-gray-200 p-4">
                                <div class="grid grid-cols-3 gap-2">
                                    <label 
                                        v-for="y in years" 
                                        :key="y"
                                        class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-orange-300 hover:bg-orange-50 cursor-pointer transition-all duration-200"
                                        :class="{ 'border-orange-400 bg-orange-50': anioSeleccionado.includes(y) }"
                                    >
                                        <input 
                                            type="checkbox" 
                                            :value="y" 
                                            v-model="anioSeleccionado"
                                            class="h-4 w-4 text-orange-600 border-2 border-gray-300 rounded focus:ring-orange-500"
                                        >
                                        <span class="font-bold text-gray-700">{{ y }}</span>
                                    </label>
                                </div>
                            </div>

                            <div v-if="anioSeleccionado.length > 0" class="flex flex-wrap gap-2">
                                <span 
                                    v-for="anio in anioSeleccionado" 
                                    :key="anio"
                                    class="inline-flex items-center gap-2 bg-orange-100 text-orange-800 px-3 py-1 rounded-full text-sm font-medium"
                                >
                                    {{ anio }}
                                    <button 
                                        type="button" 
                                        @click="removeTag(anioSeleccionado, anio)"
                                        class="hover:bg-orange-200 rounded-full p-0.5 transition-colors"
                                    >
                                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                        </svg>
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="pt-6 border-t border-gray-200">
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <!-- Botón Buscar -->
                            <button 
                                type="submit" 
                                :disabled="!canSearch"
                                :class="[
                                    'px-8 py-4 rounded-xl font-bold text-lg shadow-lg transition-all duration-300 transform',
                                    canSearch 
                                        ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white hover:from-blue-700 hover:to-purple-700 hover:scale-105 hover:shadow-xl' 
                                        : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                                ]"
                            >
                                <div class="flex items-center justify-center gap-3">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    <span>{{ isSearching ? 'Buscando...' : 'Generar Estadísticas' }}</span>
                                    <div v-if="isSearching" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></div>
                                </div>
                            </button>

                            <!-- Botón Limpiar -->
                            <button 
                                type="button" 
                                @click="limpiarFiltros"
                                class="px-6 py-4 rounded-xl font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-all duration-300 flex items-center gap-2"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Limpiar Todo
                            </button>
                        </div>

                        <!-- Resumen de selección -->
                        <div v-if="hasSelections" class="mt-6 p-4 bg-blue-50 rounded-xl border border-blue-200">
                            <h3 class="font-semibold text-blue-800 mb-2 flex items-center gap-2">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Resumen de la consulta
                            </h3>
                            <div class="text-sm text-blue-700 space-y-1">
                                <p><strong>Ciudades:</strong> {{ ciudadSeleccionada.length }} seleccionada(s)</p>
                                <p><strong>Asociaciones:</strong> {{ asociacionSeleccionada.length }} seleccionada(s)</p>
                                <p><strong>Meses:</strong> {{ mesSeleccionado.length }} seleccionado(s)</p>
                                <p><strong>Años:</strong> {{ anioSeleccionado.length }} seleccionado(s)</p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de Resultados -->
        <div v-if="datos && Object.keys(datos).length > 0" class="mt-8">
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <!-- Header de la tabla -->
                <div class="bg-gradient-to-r from-indigo-500 to-purple-500 px-8 py-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-full bg-white/20 flex items-center justify-center">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 3H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V11a2 2 0 002 2v2a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-white">Resultados de la Consulta</h2>
                                <p class="text-indigo-100 text-sm">{{ Object.keys(datos).length }} registros encontrados</p>
                            </div>
                        </div>
                       <!--  <button 
                            @click="downloadExcel" 
                            :disabled="isDownloading"
                            class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg font-semibold transition-all duration-300 flex items-center gap-2"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V11a2 2 0 002 2v2a2 2 0 01-2 2z" />
                            </svg>
                            {{ isDownloading ? 'Descargando...' : 'Descargar Excel' }}
                        </button> -->
                    </div>
                </div>

                <!-- Tabla -->
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[700px] text-left text-sm text-gray-700">
                        <thead class="bg-gray-50 border-b-2 border-gray-200">
                            <tr>
                                <th
                                    v-for="header in headers"
                                    :key="header.key"
                                    @click="ordenarPor(header.key)"
                                    class="px-6 py-4 font-semibold uppercase tracking-wider cursor-pointer select-none transition hover:bg-gray-100 text-gray-600"
                                >
                                    <div class="flex items-center gap-2">
                                        {{ header.label }}
                                        <span v-if="sort === header.key" class="text-indigo-500">
                                            <svg v-if="direction === 'asc'" class="inline h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                            </svg>
                                            <svg v-else class="inline h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="(c, index) in datos" :key="c.id || index" class="transition hover:bg-indigo-50">
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                            <span class="text-indigo-600 font-bold text-sm">{{ c.asociacion ? c.asociacion.charAt(0).toUpperCase() : '' }}</span>
                                        </div>
                                        {{ c.asociacion }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1 bg-green-100 text-green-800 px-2 py-1 rounded-full text-sm font-medium">
                                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        {{ c.solicitudes_inmediatas }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1 bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-sm font-medium">
                                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                        </svg>
                                        {{ c.solicitudes_agendadas }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1 bg-orange-100 text-orange-800 px-2 py-1 rounded-full text-sm font-bold">
                                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        {{ c.suma_peso_kg }} Kg
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    asociacion: Array,
    matriz_recuperacion: Object,
    ciudades: Array,
    datos: Object,
});

import { ref, computed } from 'vue';

// Estado del formulario (múltiples)
const ciudadSeleccionada = ref([]);
const asociacionSeleccionada = ref([]);
const mesSeleccionado = ref([]);
const anioSeleccionado = ref([]);

// Estados de carga
const isSearching = ref(false);
const isDownloading = ref(false);

// Computed properties para validación
const canSearch = computed(() => {
    return ciudadSeleccionada.value.length > 0 && 
           asociacionSeleccionada.value.length > 0 && 
           mesSeleccionado.value.length > 0 && 
           anioSeleccionado.value.length > 0;
});

const hasSelections = computed(() => {
    return ciudadSeleccionada.value.length > 0 || 
           asociacionSeleccionada.value.length > 0 || 
           mesSeleccionado.value.length > 0 || 
           anioSeleccionado.value.length > 0;
});

// Ordenamiento
const sort = ref('');
const direction = ref('asc');

const headers = [
    { key: 'asociacion', label: 'Nombre' },
    { key: 'solicitudes_inmediatas', label: 'Solicitudes Inmediatas' },
    { key: 'solicitudes_agendadas', label: 'Solicitudes Agendadas' },
    { key: 'suma_peso_kg', label: 'Suma Peso (kg)' },
];

const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
const numeromeses = ['01','02','03','04','05','06','07','08','09','10','11','12'];

// años (últimos 6)
const years = [];
const currentYear = new Date().getFullYear();
for (let i = 0; i < 6; i++) {
    years.push(currentYear - i);
}

// Asociaciones recibidas (array de {id, nombre, city})
const asociaciones = ref(props.asociacion || []);
const asociacionesFiltradas = ref([]);

// Funciones helper
const removeTag = (array, item) => {
    const index = array.indexOf(item);
    if (index > -1) {
        array.splice(index, 1);
    }
};

const getAsociacionNombre = (id) => {
    const asociacion = asociacionesFiltradas.value.find(a => a.id === id);
    return asociacion ? asociacion.nombre : id;
};

const getMesNombre = (numMes) => {
    const index = numeromeses.indexOf(numMes);
    return index !== -1 ? meses[index] : numMes;
};

const limpiarFiltros = () => {
    ciudadSeleccionada.value = [];
    asociacionSeleccionada.value = [];
    mesSeleccionado.value = [];
    anioSeleccionado.value = [];
    asociacionesFiltradas.value = [];
};

const ordenarPor = (key) => {
    if (sort.value === key) {
        direction.value = direction.value === 'asc' ? 'desc' : 'asc';
    } else {
        sort.value = key;
        direction.value = 'asc';
    }
};

// Helper para serializar arrays como repeated keys: key[]=a&key[]=b
const serializeParams = (obj) => {
    const params = new URLSearchParams();
    Object.keys(obj).forEach((key) => {
        const val = obj[key];
        if (Array.isArray(val)) {
            val.forEach((v) => params.append(`${key}[]`, v));
        } else if (val !== undefined && val !== null) {
            params.append(key, val);
        }
    });
    return params.toString();
};

const fetchAsociacionesPorCiudad = async (ciudades) => {
    try {
        if (!ciudades || ciudades.length === 0) {
            // si no hay ciudades seleccionadas, usar todas las asociaciones
            asociacionesFiltradas.value = asociaciones.value;
            return;
        }
    const response = await axios.get(route('estadistica.asociaciones') + '?' + serializeParams({ ciudades }));
        asociacionesFiltradas.value = response.data;
        // limpiar asociaciones seleccionadas que ya no existan
        asociacionSeleccionada.value = asociacionSeleccionada.value.filter(id => asociacionesFiltradas.value.find(a => a.id === id));
    } catch (e) {
        console.error('Error cargando asociaciones:', e);
        asociacionesFiltradas.value = [];
    }
};

function onCiudadChange() {
    fetchAsociacionesPorCiudad(ciudadSeleccionada.value);
}

function buscarMatriz() {
    // Validaciones mínimas
    if (!ciudadSeleccionada.value || ciudadSeleccionada.value.length === 0) {
        alert('Seleccione al menos una ciudad');
        return;
    }
    if (!asociacionSeleccionada.value || asociacionSeleccionada.value.length === 0) {
        alert('Seleccione al menos una asociación');
        return;
    }
    if (!mesSeleccionado.value || mesSeleccionado.value.length === 0) {
        alert('Seleccione al menos un mes');
        return;
    }
    if (!anioSeleccionado.value || anioSeleccionado.value.length === 0) {
        alert('Seleccione al menos un año');
        return;
    }

    isSearching.value = true;
    
    // Enviar arrays al backend (Inertia serializa arrays automáticamente)
    router.get(route('estadisticasolicitudes.index'), {
        ciudad: ciudadSeleccionada.value,
        asociacion: asociacionSeleccionada.value,
        mes: mesSeleccionado.value,
        anio: anioSeleccionado.value,
    }, { 
        preserveState: true, 
        preserveScroll: true,
        onFinish: () => {
            isSearching.value = false;
        }
    });
}

const downloadExcel = async () => {
    if (!ciudadSeleccionada.value || ciudadSeleccionada.value.length === 0 || !asociacionSeleccionada.value || asociacionSeleccionada.value.length === 0) {
        alert('Seleccione ciudad(es) y asociación(es) antes de descargar');
        return;
    }

    isDownloading.value = true;
    try {
        const params = {
            ciudad: ciudadSeleccionada.value,
            asociacion: asociacionSeleccionada.value,
            mes: mesSeleccionado.value,
            anio: anioSeleccionado.value,
        };
    const downloadUrl = route('descargar_excel') + '?' + serializeParams(params);
    const response = await axios.get(downloadUrl, { responseType: 'blob' });
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
        link.setAttribute('download', 'estadistica.xlsx');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } catch (e) {
        console.error(e);
        alert('Error al descargar');
    } finally {
        isDownloading.value = false;
    }
};
</script>
