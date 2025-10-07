<template>
    <div class="mapa-zonas relative" :class="{ 'h-full w-full': fullscreenMode }">
        <div ref="mapContainer" class="h-full w-full"></div>

        <!-- Botón de pantalla completa (visible siempre) -->
        <button v-if="!fullscreenMode" @click="toggleFullscreen"
            class="absolute top-4 right-4 z-[1000] bg-white hover:bg-gray-100 text-gray-700 p-3 rounded-lg shadow-lg transition-all duration-200 border border-gray-200"
            title="Ver en pantalla completa">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
            </svg>
        </button>

        <!-- Botón para salir de pantalla completa -->
        <button v-if="fullscreenMode" @click="toggleFullscreen"
            class="absolute top-4 left-4 z-[1001] bg-white hover:bg-gray-100 text-gray-700 px-4 py-2 rounded-lg shadow-lg transition-all duration-200 border border-gray-200 flex items-center gap-2"
            title="Salir de pantalla completa">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            Pantalla Completa
        </button>

        <!-- Panel de control flotante -->
        <div class="controls-overlay" v-if="fullscreenMode && !dibujando && !mostrarFormulario">
            <button @click="activarMododibujo"
                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded w-full mb-2 flex items-center justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Crear Nueva Zona
            </button>
            
            <!-- Selector de tipo de mapa -->
            <div class="mb-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Mapa</label>
                <select @change="cambiarTipoMapa" v-model="tipoMapaSeleccionado"
                    class="w-full px-3 py-2 bg-white border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-black">
                    <option value="osm">Mapa Estándar</option>
                    <option value="satellite">Vista Satélite</option>
                    <option value="terrain">Mapa Topográfico</option>
                    <option value="minimal">Mapa Minimalista</option>
                </select>
            </div>
            
            <button @click="toggleLeyenda"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded w-full flex items-center justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                    </path>
                </svg>
                {{ mostrarLeyenda ? 'Ocultar Leyenda' : 'Mostrar Leyenda' }}
            </button>
        </div>

        <!-- Panel de dibujo flotante -->
        <div class="controls-overlay" v-if="fullscreenMode && dibujando">
            <h3 class="font-bold mb-2 text-blue-700">Modo Dibujo Activo</h3>
            <p class="text-sm mb-3 text-black">Dibuja un polígono en el mapa para crear una nueva zona.</p>
            <div class="flex gap-2">
                <button @click="guardarZona" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded flex-1"
                    :disabled="!hayPoligono">
                    Guardar
                </button>
                <button @click="cancelarDibujo" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded flex-1">
                    Cancelar
                </button>
            </div>
        </div>

        <!-- Leyenda flotante -->
        <div class="legend-overlay" v-if="fullscreenMode && mostrarLeyenda && zonas.length > 0">
            <h3 class="text-lg font-semibold mb-2">Zonas por Asociación</h3>
            <div class="grid gap-2">
                <div v-for="asociacion in asociacionesUnicas" :key="asociacion.id" class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded-full" :style="{ backgroundColor: asociacion.color }"></div>
                    <span class="text-black">{{ asociacion.name }}</span>
                </div>
            </div>
        </div>

        <!-- Formulario modal para guardar/editar la zona -->
        <div v-if="mostrarFormulario" class="form-overlay">
            <h3 class="text-lg font-semibold mb-3">{{ modoEdicionZona ? 'Editar' : 'Guardar' }} Zona</h3>
            <div class="mb-4">
                <label for="nombreZona" class="block text-sm font-medium text-gray-700 mb-1">Nombre de la Zona</label>
                <input type="text" id="nombreZona" v-model="nuevaZona.nombre"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Ingrese un nombre para la zona">
            </div>

            <div class="mb-4">
                <label for="asociacionId" class="block text-sm font-medium text-gray-700 mb-1">Asociación</label>
                <select id="asociacionId" v-model="nuevaZona.asociacion_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <option disabled value="">Seleccione una asociación</option>
                    <option v-for="asociacion in asociaciones" :key="asociacion.id" :value="asociacion.id">
                        {{ asociacion.name }}
                    </option>
                </select>
            </div>

            <div class="flex justify-end gap-2">
                <button @click="cerrarFormulario" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                    Cancelar
                </button>
                <button @click="enviarFormulario" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded"
                    :disabled="!nuevaZona.nombre || !nuevaZona.asociacion_id">
                    {{ modoEdicionZona ? 'Actualizar' : 'Guardar' }}
                </button>
            </div>
        </div>


        <!-- Modal de confirmación de eliminación -->
        <div v-if="mostrarConfirmacionEliminar" class="overlay-backdrop"></div>
        <div v-if="mostrarConfirmacionEliminar" class="form-overlay">
            <h3 class="text-lg font-semibold mb-3 text-red-600">Eliminar Zona</h3>
            <p class="mb-4">¿Está seguro que desea eliminar la zona "{{ zonaSeleccionada?.nombre }}"?</p>
            <p class="text-sm text-gray-500 mb-4">Esta acción no se puede deshacer.</p>

            <div class="flex justify-end gap-2">
                <button @click="mostrarConfirmacionEliminar = false"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                    Cancelar
                </button>
                <button @click="confirmarEliminarZona" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">
                    Eliminar
                </button>
            </div>
        </div>

        <!-- UI no flotante (para modo no fullscreen) -->
        <div v-if="!fullscreenMode">
            <div v-if="dibujando" class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <p class="text-blue-700 mb-2">
                    <strong>Modo dibujo activo:</strong> Dibuja un polígono en el mapa para crear una nueva zona.
                </p>
                <div class="flex gap-2">
                    <button @click="guardarZona" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded"
                        :disabled="!hayPoligono">
                        Guardar Zona
                    </button>
                    <button @click="cancelarDibujo" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">
                        Cancelar
                    </button>
                </div>
            </div>

            <!-- Botones de acción para el mapa -->
            <div v-if="!dibujando && !mostrarFormulario" class="mt-4 flex gap-2 flex-wrap">
                <button @click="activarMododibujo" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Crear Nueva Zona
                </button>
                
                <!-- Selector de tipo de mapa para modo normal -->
                <select @change="cambiarTipoMapa" v-model="tipoMapaSeleccionado"
                    class="px-3 py-2 bg-white border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="osm">Mapa Estándar</option>
                    <option value="satellite">Vista Satélite</option>
                    <option value="terrain">Mapa Topográfico</option>
                    <option value="minimal">Mapa Minimalista</option>
                </select>
            </div>

            <!-- Leyenda de asociaciones -->
            <div v-if="zonas.length > 0" class="mt-6">
                <h3 class="text-lg font-semibold mb-2">Zonas por Asociación</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                    <div v-for="asociacion in asociacionesUnicas" :key="asociacion.id" class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded-full" :style="{ backgroundColor: asociacion.color }"></div>
                        <span>{{ asociacion.name }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    zonas: {
        type: Array,
        required: true
    },
    asociaciones: {
        type: Array,
        required: true
    },
    fullscreenMode: {
        type: Boolean,
        default: false
    }
});

const mapContainer = ref(null);
const map = ref(null);
const drawControl = ref(null);
const editControl = ref(null);
const drawnItems = ref(null);
const dibujando = ref(false);
const hayPoligono = ref(false);
const mostrarFormulario = ref(false);
const mostrarLeyenda = ref(true);
const modoEdicionZona = ref(false);
const mostrarConfirmacionEliminar = ref(false);
const zonaEditTool = ref(null); // ✅ almacena la instancia de edición activa
const zonaEnEdicion = ref(null); // ✅ almacena la capa que se está editando
const popupActivo = ref(null); // ✅ almacena el popup activo
const vistaMapa = ref({ center: null, zoom: null }); // ✅ almacena la vista del mapa
const modoFullscreen = ref(false); // ✅ estado de pantalla completa

const L = ref(null); // Guardar referencia a Leaflet
const nuevaZona = ref({
    nombre: '',
    asociacion_id: '',
    coordenadas: []
});
const zonaSeleccionada = ref(null);
const zonaLayers = ref({}); // Mapeo de ID de zona a capa Leaflet
const tipoMapaSeleccionado = ref('osm');
const capaMapaActual = ref(null);

// Definir las diferentes capas de mapas
const tiposMapas = {
    osm: {
        url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    },
    satellite: {
        url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
        attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
    },
    terrain: {
        url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}',
        attribution: 'Tiles &copy; Esri &mdash; Esri, DeLorme, NAVTEQ, TomTom, Intermap, iPC, USGS, FAO, NPS, NRCAN, GeoBase, Kadaster NL, Ordnance Survey, Esri Japan, METI, Esri China (Hong Kong), and the GIS User Community'
    },
    minimal: {
        url: 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png',
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 20
    }
};

// Calcular asociaciones únicas basado en las zonas
const asociacionesUnicas = computed(() => {
    const asociacionesMap = {};

    props.zonas.forEach(zona => {
        if (zona.asociacion && !asociacionesMap[zona.asociacion.id]) {
            asociacionesMap[zona.asociacion.id] = {
                id: zona.asociacion.id,
                name: zona.asociacion.name,
                color: zona.asociacion.color
            };
        }
    });

    return Object.values(asociacionesMap);
});

// 🔥 Watcher que recarga zonas cuando cambien las props (SIMPLE y SIN ERRORES)
watch(() => props.zonas, async (newZonas, oldZonas) => {
    if (!map.value || !drawnItems.value) return;
    
    // Solo recargar si realmente cambiaron
    if (JSON.stringify(newZonas) !== JSON.stringify(oldZonas)) {
        // Limpiar capas
        drawnItems.value.clearLayers();
        zonaLayers.value = {};
        
        // Recargar zonas SIN hacer fitBounds (mantiene la vista)
        await cargarZonasDirecto(newZonas, false);
    }
}, { deep: true });

onMounted(async () => {
    try {
        // Importar Leaflet de manera dinámica
        const LeafletModule = await import('leaflet');
        L.value = LeafletModule.default || LeafletModule;

        await import('leaflet-draw');

        // Fix para los iconos de Leaflet
        delete L.value.Icon.Default.prototype._getIconUrl;
        L.value.Icon.Default.mergeOptions({
            iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
            iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
        });

        // Inicializar el mapa
        map.value = L.value.map(mapContainer.value, {
            center: [-0.19, -78.5], // Coordenadas iniciales (ejemplo: Quito, Ecuador)
            zoom: 13,
            zoomControl: !props.fullscreenMode
        });

        // Añadir controles de zoom en una posición diferente para fullscreen
        if (props.fullscreenMode) {
            L.value.control.zoom({
                position: 'bottomleft'
            }).addTo(map.value);
        }

        // Añadir capa de mapa inicial
        capaMapaActual.value = L.value.tileLayer(tiposMapas.osm.url, tiposMapas.osm).addTo(map.value);

        // Inicializar capa para los elementos dibujados
        drawnItems.value = new L.value.FeatureGroup();
        map.value.addLayer(drawnItems.value);

        // Configurar los controles de dibujo
        drawControl.value = new L.value.Control.Draw({
            draw: {
                marker: false,
                circle: false,
                circlemarker: false,
                rectangle: false,
                polyline: false,
                polygon: {
                    allowIntersection: false,
                    drawError: {
                        color: '#e1e100',
                        message: '<strong>Error:</strong> ¡Los bordes no pueden cruzarse!'
                    },
                    shapeOptions: {
                        color: '#3388ff'
                    }
                },
            },
            edit: {
                featureGroup: drawnItems.value,
                remove: false,
                edit: {
                    selectedPathOptions: {
                        maintainColor: true,
                        dashArray: '10, 10',
                        weight: 3,
                        // ✅ Estilos para puntos circulares
                        fillOpacity: 0.8
                    }
                }
            }
        });

        // ✅ Personalizar los puntos de edición para que sean circulares
        L.value.Edit = L.value.Edit || {};
        L.value.Edit.PolyVerticesEdit = L.value.Edit.PolyVerticesEdit || L.value.Handler.extend({});
        
        const originalUpdateMarkers = L.value.Edit.PolyVerticesEdit.prototype._createMarker;
        L.value.Edit.PolyVerticesEdit.prototype._createMarker = function (latlng, icon) {
            const marker = originalUpdateMarkers.call(this, latlng, icon);
            if (marker && marker._icon) {
                // Hacer los puntos circulares y más bonitos
                marker._icon.style.width = '12px';
                marker._icon.style.height = '12px';
                marker._icon.style.marginLeft = '-6px';
                marker._icon.style.marginTop = '-6px';
                marker._icon.style.borderRadius = '50%';
                marker._icon.style.backgroundColor = '#fff';
                marker._icon.style.border = '3px solid #3388ff';
                marker._icon.style.boxShadow = '0 2px 6px rgba(0,0,0,0.3)';
            }
            return marker;
        };

        // No añadir el control por defecto, se activará solo cuando se quiera crear una zona

        // Eventos de dibujo
        map.value.on('draw:created', function (event) {
            const layer = event.layer;
            drawnItems.value.addLayer(layer);
            hayPoligono.value = true;

            // Obtener las coordenadas del polígono
            const latlngs = layer.getLatLngs()[0];
            nuevaZona.value.coordenadas = latlngs.map(latlng => ({
                lat: latlng.lat,
                lng: latlng.lng
            }));
        });

        map.value.on('draw:edited', function (event) {
            const layers = event.layers;
            layers.eachLayer(function (layer) {
                if (zonaLayers.value[layer._leaflet_id]) {
                    const zonaId = zonaLayers.value[layer._leaflet_id];
                    const latlngs = layer.getLatLngs()[0];

                    nuevaZona.value.coordenadas = latlngs.map(latlng => ({
                        lat: latlng.lat,
                        lng: latlng.lng
                    }));

                    abrirFormularioEdicion(zonaId);
                }
            });
        });

        cargarZonas();

        // ✅ Cerrar popup al hacer zoom o mover el mapa (previene bugs visuales)
        map.value.on('zoomstart', () => {
            if (popupActivo.value && !modoEdicionZona.value) {
                popupActivo.value.closePopup();
                popupActivo.value = null;
            }
        });

        map.value.on('movestart', () => {
            if (popupActivo.value && !modoEdicionZona.value) {
                popupActivo.value.closePopup();
                popupActivo.value = null;
            }
        });

    } catch (error) {
        console.error('Error al inicializar el mapa:', error);
    }

    // ✅ Detectar cuando el usuario sale de pantalla completa con ESC
    document.addEventListener('fullscreenchange', () => {
        if (!document.fullscreenElement) {
            modoFullscreen.value = false;
            setTimeout(() => {
                if (map.value) {
                    map.value.invalidateSize();
                }
            }, 100);
        }
    });

    document.addEventListener('webkitfullscreenchange', () => {
        if (!document.webkitFullscreenElement) {
            modoFullscreen.value = false;
            setTimeout(() => {
                if (map.value) {
                    map.value.invalidateSize();
                }
            }, 100);
        }
    });
});

onUnmounted(() => {
    if (map.value) {
        map.value.remove();
    }
});

// Función para cargar las zonas desde el backend
const cargarZonas = async (autoFit = true) => {
    return cargarZonasDirecto(props.zonas, autoFit);
};

// Función interna que acepta zonas como parámetro
const cargarZonasDirecto = async (zonasData, autoFit = true) => {
    try {
        if (!L.value) return;

        // ✅ Usar las zonas pasadas como parámetro
        const zonas = zonasData || [];

        if (drawnItems.value) {
            drawnItems.value.clearLayers();
        }

        zonaLayers.value = {};

        // Si no hay zonas, no dibujar nada
        if (zonas.length === 0) {
            console.log('No hay zonas para esta ciudad');
            return;
        }

        // Añadir polígonos de las zonas
        zonas.forEach(zona => {
            const polygon = L.value.polygon(zona.coordenadas.map(coord => [coord.lat, coord.lng]), {
                color: zona.asociacion?.color || '#3388ff',
                fillOpacity: 0.5,
                weight: 2
            });

            // ✅ Popup con opciones de edición/eliminación al hacer hover
            const popupContent = `
                <div class="zona-hover-popup" style="padding: 8px; min-width: 150px;">
                    <h4 style="margin: 0 0 8px 0; font-weight: bold; color: #333;">${zona.nombre}</h4>
                    <p style="margin: 0 0 8px 0; font-size: 12px; color: #666;">
                        <strong>Asociación:</strong> ${zona.asociacion?.name || 'Sin asociación'}
                    </p>
                    <div style="display: flex; gap: 8px; margin-top: 8px;">
                        <button id="editar-zona-${zona.id}" 
                                style="flex: 1; background: #3b82f6; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 13px; display: flex; align-items: center; justify-content: center; gap: 4px;">
                            <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                            </svg>
                            Editar
                        </button>
                        <button id="eliminar-zona-${zona.id}" 
                                style="flex: 1; background: #ef4444; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 13px; display: flex; align-items: center; justify-content: center; gap: 4px;">
                            <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Eliminar
                        </button>
                    </div>
                </div>
            `;

            const popupInstance = L.value.popup({
                closeButton: true,
                autoPan: false,
                className: 'custom-zona-popup',
                closeOnClick: false,
                autoClose: false
            }).setContent(popupContent);

            polygon.bindPopup(popupInstance);

            // ✅ Sistema simple y robusto para abrir/cerrar popup
            let hideTimeout = null;

            const showPopup = () => {
                if (dibujando.value || modoEdicionZona.value) return;
                
                // Cancelar cierre pendiente
                if (hideTimeout) {
                    clearTimeout(hideTimeout);
                    hideTimeout = null;
                }
                
                // Cerrar otros popups
                if (popupActivo.value && popupActivo.value !== polygon) {
                    popupActivo.value.closePopup();
                }
                
                polygon.openPopup();
                popupActivo.value = polygon;
                
                // Configurar botones después de renderizar
                requestAnimationFrame(() => {
                    const editBtn = document.getElementById(`editar-zona-${zona.id}`);
                    const deleteBtn = document.getElementById(`eliminar-zona-${zona.id}`);
                    
                    if (editBtn) {
                        editBtn.onclick = (e) => {
                            e.stopPropagation();
                            polygon.closePopup();
                            popupActivo.value = null;
                            editarZona(zona.id, polygon);
                        };
                    }
                    
                    if (deleteBtn) {
                        deleteBtn.onclick = (e) => {
                            e.stopPropagation();
                            polygon.closePopup();
                            popupActivo.value = null;
                            eliminarZona(zona.id);
                        };
                    }
                });
            };

            const hidePopup = () => {
                if (hideTimeout) clearTimeout(hideTimeout);
                hideTimeout = setTimeout(() => {
                    if (popupActivo.value === polygon && !modoEdicionZona.value) {
                        polygon.closePopup();
                        popupActivo.value = null;
                    }
                }, 500);
            };

            // Eventos del polígono
            polygon.on('mouseover', showPopup);
            polygon.on('mouseout', hidePopup);

            // Eventos del popup para mantenerlo abierto
            polygon.on('popupopen', () => {
                const popupElement = polygon.getPopup().getElement();
                if (popupElement) {
                    popupElement.addEventListener('mouseenter', () => {
                        if (hideTimeout) {
                            clearTimeout(hideTimeout);
                            hideTimeout = null;
                        }
                    });
                    popupElement.addEventListener('mouseleave', hidePopup);
                }
            });

            drawnItems.value.addLayer(polygon);
            zonaLayers.value[polygon._leaflet_id] = zona.id;
        });

        // ✅ Ajustar la vista solo si autoFit es true (primera carga)
        if (autoFit && zonas.length > 0 && drawnItems.value.getBounds().isValid()) {
            map.value.fitBounds(drawnItems.value.getBounds());
        }

    } catch (error) {
        console.error('Error al cargar zonas:', error);
    }
};

// Alternar la visualización de la leyenda
const toggleLeyenda = () => {
    mostrarLeyenda.value = !mostrarLeyenda.value;
};

// Activar el modo dibujo
const activarMododibujo = () => {
    if (!L.value) return;
    dibujando.value = true;

    if (map.value && drawControl.value) {
        map.value.addControl(drawControl.value);
        drawControl.value._toolbars.draw._modes.polygon.handler.enable();
    }
};

// Editar una zona existente
const editarZona = async (zonaId, layer) => {
    try {
        // ✅ Guardar la vista actual del mapa
        vistaMapa.value = {
            center: map.value.getCenter(),
            zoom: map.value.getZoom()
        };

        // Obtener los datos de la zona
        const response = await axios.get(route('zonas.obtener', zonaId));
        zonaSeleccionada.value = response.data;

        // Cerrar cualquier popup abierto
        if (popupActivo.value) {
            popupActivo.value.closePopup();
            popupActivo.value = null;
        }

        // ✅ Desactivar edición previa si existe
        if (zonaEditTool.value) {
            zonaEditTool.value.disable();
            zonaEditTool.value = null;
        }

        // ✅ Ocultar todas las demás zonas visualmente
        drawnItems.value.eachLayer(l => {
            if (l !== layer) {
                l.setStyle({ opacity: 0.15, fillOpacity: 0.05 });
            } else {
                l.setStyle({ weight: 3, dashArray: '5, 10', opacity: 1, fillOpacity: 0.5 });
            }
        });

        // ✅ Crear un FeatureGroup solo con la zona que se va a editar
        const editGroup = new L.value.FeatureGroup();
        editGroup.addLayer(layer);

        // ✅ Activar edición solo en esta zona
        zonaEditTool.value = new L.value.EditToolbar.Edit(map.value, {
            featureGroup: editGroup,
            selectedPathOptions: {
                maintainColor: true,
                dashArray: '10, 10',
                weight: 3,
                fillOpacity: 0.5
            }
        });

        zonaEditTool.value.enable();
        zonaEnEdicion.value = layer;
        modoEdicionZona.value = true;

        // ✅ Función mejorada para actualizar los puntos de edición cuando cambia el zoom/pan
        const actualizarPuntosEdicion = () => {
            if (!layer || !layer.editing || !layer.editing._enabled) return;
            
            // Método 1: Actualizar manualmente todos los marcadores de vértices
            if (layer.editing._verticesHandlers && layer.editing._verticesHandlers.length > 0) {
                layer.editing._verticesHandlers.forEach(handler => {
                    if (handler._markers && handler._markers.length > 0) {
                        handler._markers.forEach(marker => {
                            if (marker && marker._icon) {
                                // Forzar actualización de la posición del icono
                                marker.update();
                            }
                        });
                    }
                });
            }
            
            // Método 2: Si el método anterior no funciona, usar redibujado completo
            const latlngs = layer.getLatLngs();
            layer.editing.disable();
            setTimeout(() => {
                if (layer.editing && modoEdicionZona.value) {
                    layer.editing.enable();
                }
            }, 10);
        };

        // ✅ Escuchar múltiples eventos del mapa para mantener puntos sincronizados
        map.value.on('zoom', actualizarPuntosEdicion);
        map.value.on('zoomend', actualizarPuntosEdicion);
        map.value.on('move', actualizarPuntosEdicion);
        map.value.on('moveend', actualizarPuntosEdicion);

        // ✅ Crear popup persistente con opciones de edición
        const editPopup = L.value.popup({
            closeButton: false, // ✅ Sin botón de cerrar para evitar cierre accidental
            closeOnClick: false, // ✅ No se cierra al hacer clic en el mapa
            autoClose: false, // ✅ No se cierra automáticamente
            className: 'zona-edit-popup-persistent'
        })
            .setLatLng(layer.getBounds().getCenter())
            .setContent(`
                <div style="padding: 12px; min-width: 200px;">
                    <h3 style="margin: 0 0 8px 0; font-weight: bold; color: #f59e0b;">✏️ Editando: ${zonaSeleccionada.value.nombre}</h3>
                    <p style="margin: 0 0 12px 0; font-size: 12px; color: #666;">
                        Arrastra los puntos circulares para modificar la forma de la zona.
                    </p>
                    <div style="display: flex; gap: 8px;">
                        <button id="guardarEdicionBtn" 
                                style="flex: 1; background: #10b981; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 500; display: flex; align-items: center; justify-content: center; gap: 4px;">
                            <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Guardar
                        </button>
                        <button id="cancelarEdicionBtn" 
                                style="flex: 1; background: #6b7280; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 500; display: flex; align-items: center; justify-content: center; gap: 4px;">
                            <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Cancelar
                        </button>
                    </div>
                </div>
            `)
            .openOn(map.value);

        // ✅ Añadir event listeners a los botones
        setTimeout(() => {
            const guardarBtn = document.getElementById('guardarEdicionBtn');
            const cancelarBtn = document.getElementById('cancelarEdicionBtn');

            if (guardarBtn) {
                guardarBtn.addEventListener('click', () => {
                    const latlngs = layer.getLatLngs()[0];
                    nuevaZona.value = {
                        id: zonaId,
                        nombre: zonaSeleccionada.value.nombre,
                        asociacion_id: zonaSeleccionada.value.asociacion_id,
                        coordenadas: latlngs.map(latlng => ({
                            lat: latlng.lat,
                            lng: latlng.lng
                        }))
                    };

                    map.value.closePopup();
                    if (zonaEditTool.value) {
                        zonaEditTool.value.save();
                        zonaEditTool.value.disable();
                        zonaEditTool.value = null;
                    }

                    mostrarFormulario.value = true;
                });

                guardarBtn.addEventListener('mouseover', () => {
                    guardarBtn.style.background = '#059669';
                });
                guardarBtn.addEventListener('mouseout', () => {
                    guardarBtn.style.background = '#10b981';
                });
            }

            if (cancelarBtn) {
                cancelarBtn.addEventListener('click', () => {
                    cancelarEdicion();
                });

                cancelarBtn.addEventListener('mouseover', () => {
                    cancelarBtn.style.background = '#4b5563';
                });
                cancelarBtn.addEventListener('mouseout', () => {
                    cancelarBtn.style.background = '#6b7280';
                });
            }
        }, 50);

    } catch (error) {
        console.error('Error al editar zona:', error);
    }
};

// ✅ Función para cancelar la edición y restaurar el estado
const cancelarEdicion = () => {
    map.value.closePopup();
    
    // ✅ Remover todos los listeners de zoom/movimiento
    map.value.off('zoom');
    map.value.off('zoomend');
    map.value.off('move');
    map.value.off('moveend');
    
    if (zonaEditTool.value) {
        zonaEditTool.value.revertLayers();
        zonaEditTool.value.disable();
        zonaEditTool.value = null;
    }

    // Restaurar estilos de todas las zonas
    drawnItems.value.eachLayer(l => {
        l.setStyle({
            opacity: 1,
            fillOpacity: 0.5,
            weight: 2,
            dashArray: null
        });
    });

    zonaEnEdicion.value = null;
    modoEdicionZona.value = false;
};


// Abrir formulario para edición
const abrirFormularioEdicion = (zonaId) => {
    modoEdicionZona.value = true;
    mostrarFormulario.value = true;
    nuevaZona.value.id = zonaId;
};

// Eliminar una zona
const eliminarZona = (zonaId) => {
    zonaSeleccionada.value = props.zonas.find(z => z.id === zonaId);
    mostrarConfirmacionEliminar.value = true;
};

// Confirmar eliminación de zona
const confirmarEliminarZona = () => {
    if (zonaSeleccionada.value && zonaSeleccionada.value.id) {
        router.delete(route('zonas.destroy', zonaSeleccionada.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                mostrarConfirmacionEliminar.value = false;
                zonaSeleccionada.value = null;
                nuevaZona.value = {
                    nombre: '',
                    asociacion_id: '',
                    coordenadas: []
                };
                
                // 🔥 Recargar solo las zonas (el watcher se encarga del resto)
                router.reload({ only: ['zonas'], preserveScroll: true });
            }
        });
    }
};


// Guardar la zona dibujada
const guardarZona = () => {
    if (hayPoligono.value) {
        if (drawControl.value && drawControl.value._toolbars.draw._modes.polygon.handler.enabled()) {
            drawControl.value._toolbars.draw._modes.polygon.handler.disable();
        }

        if (map.value && drawControl.value) {
            map.value.removeControl(drawControl.value);
        }

        mostrarFormulario.value = true;
        modoEdicionZona.value = false;
    }
};


// Cerrar el formulario
const cerrarFormulario = () => {
    mostrarFormulario.value = false;

    if (modoEdicionZona.value) {
        // ✅ Remover todos los listeners de zoom/movimiento
        map.value.off('zoom');
        map.value.off('zoomend');
        map.value.off('move');
        map.value.off('moveend');
        
        // Restaurar estilos de todas las zonas
        drawnItems.value.eachLayer(layer => {
            layer.setStyle({
                opacity: 1,
                fillOpacity: 0.5,
                weight: 2,
                dashArray: null
            });
        });
        
        // Limpiar markers de edición residuales
        if (map.value) {
            map.value.eachLayer(layer => {
                const esEditable = layer._path && layer._path.classList?.contains('leaflet-edit-resize');
                const esMarker = layer.options && layer.options.draggable;
                if (esEditable || esMarker) {
                    map.value.removeLayer(layer);
                }
            });
        }
        
        modoEdicionZona.value = false;
        zonaEnEdicion.value = null;
    } else {
        if (map.value && drawControl.value) {
            map.value.removeControl(drawControl.value);
        }
        drawnItems.value.eachLayer(layer => {
            if (!zonaLayers.value[layer._leaflet_id]) {
                drawnItems.value.removeLayer(layer);
            }
        });
        dibujando.value = false;
        hayPoligono.value = false;
    }
    
    nuevaZona.value = {
        nombre: '',
        asociacion_id: '',
        coordenadas: []
    };
};


// Cancelar el dibujo
const cancelarDibujo = () => {
    dibujando.value = false;
    hayPoligono.value = false;

    // Desactivar el modo polígono si está habilitado
    if (drawControl.value && drawControl.value._toolbars.draw._modes.polygon.handler.enabled()) {
        drawControl.value._toolbars.draw._modes.polygon.handler.disable();
    }

    // Remover del mapa cualquier polígono que no pertenece a zonas ya guardadas
    if (!modoEdicionZona.value) {
        drawnItems.value.eachLayer(layer => {
            if (!zonaLayers.value[layer._leaflet_id]) {
                drawnItems.value.removeLayer(layer);
            }
        });
    }

    // Quitar el control de dibujo
    if (map.value && drawControl.value) {
        map.value.removeControl(drawControl.value);
    }

    // Resetear el formulario para nuevas zonas (si no estamos editando)
    if (!modoEdicionZona.value) {
        nuevaZona.value = {
            nombre: '',
            asociacion_id: '',
            coordenadas: []
        };
    }

    // ✅ Recargar las capas de zonas existentes SIN hacer fitBounds
    cargarZonas(false);
}


// Función para cambiar a pantalla completa
const toggleFullscreen = () => {
    const mapaContainer = mapContainer.value?.parentElement;
    if (!mapaContainer) return;

    if (!modoFullscreen.value) {
        // Entrar en pantalla completa
        if (mapaContainer.requestFullscreen) {
            mapaContainer.requestFullscreen();
        } else if (mapaContainer.webkitRequestFullscreen) {
            mapaContainer.webkitRequestFullscreen();
        } else if (mapaContainer.msRequestFullscreen) {
            mapaContainer.msRequestFullscreen();
        }
        modoFullscreen.value = true;
    } else {
        // Salir de pantalla completa
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }
        modoFullscreen.value = false;
    }

    // Esperar a que se complete el cambio y ajustar el mapa
    setTimeout(() => {
        if (map.value) {
            map.value.invalidateSize();
        }
    }, 100);
};

// Función para cambiar el tipo de mapa
const cambiarTipoMapa = () => {
    if (capaMapaActual.value && map.value) {
        // Remover la capa actual
        map.value.removeLayer(capaMapaActual.value);
        
        // Agregar la nueva capa
        const tipoMapa = tiposMapas[tipoMapaSeleccionado.value];
        capaMapaActual.value = L.value.tileLayer(tipoMapa.url, {
            attribution: tipoMapa.attribution,
            subdomains: tipoMapa.subdomains || 'abc',
            maxZoom: tipoMapa.maxZoom || 18
        }).addTo(map.value);
    }
};

// Enviar el formulario para crear o actualizar la zona
const enviarFormulario = () => {
    if (modoEdicionZona.value && nuevaZona.value.id) {
        // Actualizar zona existente
        router.put(route('zonas.update', nuevaZona.value.id), {
            nombre: nuevaZona.value.nombre,
            asociacion_id: nuevaZona.value.asociacion_id,
            coordenadas: nuevaZona.value.coordenadas
        }, {
            preserveScroll: true,
            onSuccess: () => {
                // Limpiar UI
                mostrarFormulario.value = false;
                modoEdicionZona.value = false;
                zonaSeleccionada.value = null;
                zonaEnEdicion.value = null;

                // Limpiar herramientas de edición
                if (zonaEditTool.value) {
                    zonaEditTool.value.disable();
                    zonaEditTool.value = null;
                }

                // Remover listeners temporales
                map.value.off('zoom');
                map.value.off('move');

                // Limpiar estado
                nuevaZona.value = {
                    nombre: '',
                    asociacion_id: '',
                    coordenadas: []
                };

                // 🔥 Recargar solo las zonas (el watcher se encarga del resto)
                router.reload({ only: ['zonas'], preserveScroll: true });
            },
            onError: (error) => {
                console.error('Error al actualizar la zona:', error);
            }
        });
    } else {
        // Crear nueva zona
        router.post(route('zonas.store'), {
            nombre: nuevaZona.value.nombre,
            asociacion_id: nuevaZona.value.asociacion_id,
            coordenadas: nuevaZona.value.coordenadas
        }, {
            preserveScroll: true,
            onSuccess: () => {
                mostrarFormulario.value = false;
                dibujando.value = false;
                nuevaZona.value = {
                    nombre: '',
                    asociacion_id: '',
                    coordenadas: []
                };

                // 🔥 Recargar solo las zonas (el watcher se encarga del resto)
                router.reload({ only: ['zonas'], preserveScroll: true });
            },
            onError: (error) => {
                console.error('Error al crear la zona:', error);
            }
        });
    }
};
</script>

<style scoped>
/* Estilos generales para el componente de mapa */
.mapa-zonas {
    position: relative;
}

/* Pantalla completa */
.mapa-zonas:fullscreen {
    width: 100vw !important;
    height: 100vh !important;
}

.mapa-zonas:-webkit-full-screen {
    width: 100vw !important;
    height: 100vh !important;
}

.mapa-zonas:-moz-full-screen {
    width: 100vw !important;
    height: 100vh !important;
}

.mapa-zonas:-ms-fullscreen {
    width: 100vw !important;
    height: 100vh !important;
}

/* Panel de controles flotante */
.controls-overlay {
    position: absolute;
    top: 20px;
    right: 20px;
    background: white;
    padding: 16px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    z-index: 1000;
    max-width: 280px;
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.95);
}

/* Panel de leyenda flotante */
.legend-overlay {
    position: absolute;
    bottom: 20px;
    left: 20px;
    background: white;
    padding: 16px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    z-index: 1000;
    max-width: 300px;
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.95);
}

/* Formulario flotante */
.form-overlay {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 24px;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
    z-index: 1001;
    min-width: 400px;
    max-width: 500px;
}

/* Fondo oscuro para el modal */
.overlay-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    backdrop-filter: blur(2px);
}
</style>

<style>
/* Estilos globales para Leaflet (sin scoped) */

/* Popup personalizado para hover sobre zonas */
.leaflet-popup.custom-zona-popup .leaflet-popup-content-wrapper {
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    padding: 0;
}

.leaflet-popup.custom-zona-popup .leaflet-popup-content {
    margin: 0;
    min-width: 180px;
}

/* Popup persistente para edición */
.leaflet-popup.zona-edit-popup-persistent .leaflet-popup-content-wrapper {
    border-radius: 8px;
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.25);
    padding: 0;
    border-left: 4px solid #f59e0b;
}

.leaflet-popup.zona-edit-popup-persistent .leaflet-popup-content {
    margin: 0;
    min-width: 220px;
}

/* Tooltip de zona */
.leaflet-tooltip-zona {
    background: rgba(0, 0, 0, 0.8);
    color: white;
    border: none;
    border-radius: 6px;
    padding: 8px 12px;
    font-size: 13px;
    font-weight: 500;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
}

/* Puntos de edición circulares personalizados */
.leaflet-editing-icon {
    width: 12px !important;
    height: 12px !important;
    margin-left: -6px !important;
    margin-top: -6px !important;
    border-radius: 50% !important;
    background-color: #fff !important;
    border: 3px solid #3388ff !important;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3) !important;
    cursor: move !important;
}

.leaflet-editing-icon:hover {
    border-color: #2563eb !important;
    transform: scale(1.2);
    transition: all 0.2s ease;
}

/* Líneas de edición más suaves */
.leaflet-edit-move {
    cursor: move;
}

/* Estilo para el polígono en edición */
.leaflet-interactive.leaflet-edit-move {
    stroke-dasharray: 10, 10;
    stroke-width: 3;
}
</style>
