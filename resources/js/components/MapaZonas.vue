<template>
    <div class="mapa-zonas relative" :class="{ 'h-full w-full': fullscreenMode }">
        <div ref="mapContainer" class="h-full w-full"></div>

        <!-- Panel de control flotante -->
        <div class="controls-overlay" v-if="fullscreenMode && !dibujando && !mostrarFormulario && !modoEdicion">
            <button @click="activarMododibujo"
                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded w-full mb-2 flex items-center justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Crear Nueva Zona
            </button>
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
            <p class="text-sm mb-3">Dibuja un polígono en el mapa para crear una nueva zona.</p>
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

        <!-- Panel de edición flotante -->
        <div class="controls-overlay" v-if="fullscreenMode && modoEdicion">
            <h3 class="font-bold mb-2 text-yellow-700">Modo Edición Activo</h3>
            <p class="text-sm mb-3">Selecciona una zona para editarla o eliminarla.</p>
            <div class="flex gap-2">
                <button @click="salirModoEdicion"
                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded flex-1">
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
                    <span>{{ asociacion.name }}</span>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-gray-200">
                <button @click="activarModoEdicion"
                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded w-full flex items-center justify-center mb-2">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                        </path>
                    </svg>
                    Editar Zonas
                </button>
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
            <div v-if="!dibujando && !mostrarFormulario" class="mt-4 flex gap-2">
                <button @click="activarMododibujo" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Crear Nueva Zona
                </button>

                <button @click="activarModoEdicion"
                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded">
                    Editar Zonas
                </button>
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
    </div>°
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
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
const modoEdicion = ref(false);
const modoEdicionZona = ref(false);
const mostrarConfirmacionEliminar = ref(false);
const zonaEditTool = ref(null); // ✅ almacena la instancia de edición activa

const L = ref(null); // Guardar referencia a Leaflet
const nuevaZona = ref({
    nombre: '',
    asociacion_id: '',
    coordenadas: []
});
const zonaSeleccionada = ref(null);
const zonaLayers = ref({}); // Mapeo de ID de zona a capa Leaflet

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

        // Añadir capa de OpenStreetMap (gratuita)
        L.value.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map.value);

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
                remove: false // Deshabilitamos esto para manejar la eliminación nosotros
            }
        });

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
    } catch (error) {
        console.error('Error al inicializar el mapa:', error);
    }
});

onUnmounted(() => {
    if (map.value) {
        map.value.remove();
    }
});

// Función para cargar las zonas desde el backend
const cargarZonas = async () => {
    try {
        if (!L.value) return;

        const response = await axios.get(route('api.zonas'));

        if (drawnItems.value) {
            drawnItems.value.clearLayers();
        }

        zonaLayers.value = {};

        // Añadir polígonos de las zonas
        response.data.forEach(zona => {
            const polygon = L.value.polygon(zona.coordenadas.map(coord => [coord.lat, coord.lng]), {
                color: zona.color || '#3388ff',
                fillOpacity: 0.5,
                weight: 2
            });

            polygon.bindTooltip(`<strong>${zona.nombre}</strong><br>Asociación: ${zona.asociacion}`, {
                permanent: false,
                direction: 'center',
                className: 'leaflet-tooltip-zona'
            });

            polygon.on('click', () => {
                if (modoEdicion.value) {
                    seleccionarZona(zona.id, polygon);
                }
            });

            drawnItems.value.addLayer(polygon);
            zonaLayers.value[polygon._leaflet_id] = zona.id;
        });


        // Ajustar la vista si hay zonas
        if (response.data.length > 0) {
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

    if (modoEdicion.value) {
        salirModoEdicion();
    }

    if (map.value && drawControl.value) {
        map.value.addControl(drawControl.value);
        drawControl.value._toolbars.draw._modes.polygon.handler.enable();
    }
};

// Activar modo edición
const activarModoEdicion = () => {
    if (!L.value) return;
    modoEdicion.value = true;

    const mapElement = mapContainer.value;
    if (mapElement) {
        mapElement.style.cursor = 'pointer';
    }
};

// Salir del modo edición
const salirModoEdicion = () => {
    modoEdicion.value = false;

    const mapElement = mapContainer.value;
    if (mapElement) {
        mapElement.style.cursor = '';
    }

    drawnItems.value.eachLayer(layer => {
        layer.setStyle({
            opacity: 1,
            fillOpacity: 0.5,
            weight: 2,
            dashArray: null
        });
    });

    map.value.closePopup();
};

// Seleccionar una zona para editar o eliminar
const seleccionarZona = async (zonaId, layer) => {
    try {
        // Obtener los detalles de la zona
        const response = await axios.get(route('zonas.obtener', zonaId));
        zonaSeleccionada.value = response.data;

        // Mostrar menú de opciones para editar o eliminar
        L.value.popup({
            closeButton: true,
            className: 'zona-actions-popup'
        })
            .setLatLng(layer.getBounds().getCenter())
            .setContent(`
        <div class="p-2">
          <h3 class="font-bold">${zonaSeleccionada.value.nombre}</h3>
          <div class="flex mt-2 gap-2">
            <button id="editarZonaBtn" class="bg-blue-500 text-white px-3 py-1 rounded text-sm">Editar</button>
            <button id="eliminarZonaBtn" class="bg-red-500 text-white px-3 py-1 rounded text-sm">Eliminar</button>
          </div>
        </div>
      `)
            .openOn(map.value);

        // Añadir listeners a los botones del popup
        setTimeout(() => {
            const editarBtn = document.getElementById('editarZonaBtn');
            const eliminarBtn = document.getElementById('eliminarZonaBtn');

            if (editarBtn) {
                editarBtn.addEventListener('click', () => {
                    map.value.closePopup();
                    editarZona(zonaId, layer);
                });
            }

            if (eliminarBtn) {
                eliminarBtn.addEventListener('click', () => {
                    map.value.closePopup();
                    eliminarZona(zonaId);
                });
            }
        }, 100);

    } catch (error) {
        console.error('Error al seleccionar zona:', error);
    }
};

// Editar una zona existente
const editarZona = (zonaId, layer) => {
    if (zonaEditTool.value) {
        zonaEditTool.value.disable();
        zonaEditTool.value = null;
    }

    zonaEditTool.value = new L.value.EditToolbar.Edit(map.value, {
        featureGroup: drawnItems.value,
        selectedPathOptions: {
            maintainColor: true,
            dashArray: '10, 10',
            weight: 3
        }
    });

    drawnItems.value.eachLayer(l => {
        if (l !== layer) {
            l.setStyle({ opacity: 0.3, fillOpacity: 0.1 });
        } else {
            l.setStyle({ weight: 3, dashArray: '5, 10' });
        }
    });

    zonaEditTool.value.enable();

    L.value.popup({
        closeButton: true,
        className: 'zona-edit-popup'
    })
        .setLatLng(layer.getBounds().getCenter())
        .setContent(`
        <div class="p-2">
            <h3 class="font-bold">Editando: ${zonaSeleccionada.value.nombre}</h3>
            <p class="text-sm my-2">Arrastra los puntos para modificar la forma.</p>
            <div class="flex mt-2 gap-2">
                <button id="guardarEdicionBtn" class="bg-green-500 text-white px-3 py-1 rounded text-sm">Guardar Cambios</button>
                <button id="cancelarEdicionBtn" class="bg-gray-500 text-white px-3 py-1 rounded text-sm">Cancelar</button>
            </div>
        </div>
    `)
        .openOn(map.value);

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
                zonaEditTool.value.save();
                zonaEditTool.value.disable();
                zonaEditTool.value = null;

                modoEdicionZona.value = true;
                mostrarFormulario.value = true;
            });
        }

        if (cancelarBtn) {
            cancelarBtn.addEventListener('click', () => {
                map.value.closePopup();
                if (zonaEditTool.value) {
                    zonaEditTool.value.revertLayers();
                    zonaEditTool.value.disable();
                    zonaEditTool.value = null;
                }

                // Restaurar estilos
                drawnItems.value.eachLayer(l => {
                    l.setStyle({
                        opacity: 1,
                        fillOpacity: 0.5,
                        weight: 2,
                        dashArray: null
                    });
                });
            });
        }
    }, 100);
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
            onSuccess: () => {
                mostrarConfirmacionEliminar.value = false;
                zonaSeleccionada.value = null;
                nuevaZona.value = {
                    nombre: '',
                    asociacion_id: '',
                    coordenadas: []
                };
                cargarZonas();
                salirModoEdicion();
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
        drawnItems.value.eachLayer(layer => {
            layer.setStyle({
                opacity: 1,
                fillOpacity: 0.5,
                weight: 2,
                dashArray: null
            });
        });
        modoEdicionZona.value = false;
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
        nuevaZona.value = {
            nombre: '',
            asociacion_id: '',
            coordenadas: []
        };
    }
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

    // Recargar las capas de zonas existentes
    cargarZonas();
}


// Enviar el formulario para crear o actualizar la zona
const enviarFormulario = () => {
    if (modoEdicionZona.value && nuevaZona.value.id) {
        router.put(route('zonas.update', nuevaZona.value.id), {
            nombre: nuevaZona.value.nombre,
            asociacion_id: nuevaZona.value.asociacion_id,
            coordenadas: nuevaZona.value.coordenadas
        }, {
            onSuccess: () => {
                mostrarFormulario.value = false;
                modoEdicionZona.value = false;
                zonaSeleccionada.value = null;
                nuevaZona.value = {
                    nombre: '',
                    asociacion_id: '',
                    coordenadas: []
                };

                if (zonaEditTool.value) {
                    zonaEditTool.value.disable();
                    zonaEditTool.value = null;
                }

                drawnItems.value.eachLayer(layer => {
                    if (layer.setStyle) {
                        layer.setStyle({
                            weight: 2,
                            color: '#3388ff',
                            dashArray: null,
                            opacity: 1,
                            fillOpacity: 0.5
                        });
                    }
                });

                if (map.value) {
                    map.value.eachLayer(layer => {
                        const esEditable = layer._path && layer._path.classList?.contains('leaflet-edit-resize');
                        const esMarker = layer.options && layer.options.draggable;
                        if (esEditable || esMarker) {
                            map.value.removeLayer(layer);
                        }
                    });
                }

                drawnItems.value.clearLayers();
                cargarZonas();
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
            onSuccess: () => {
                mostrarFormulario.value = false;
                modoEdicionZona.value = false;
                dibujando.value = false;
                zonaSeleccionada.value = null;
                nuevaZona.value = {
                    nombre: '',
                    asociacion_id: '',
                    coordenadas: []
                };

                drawnItems.value.clearLayers();
                cargarZonas();
            },
            onError: (error) => {
                console.error('Error al crear la zona:', error);
            }
        });
    }
};
</script>
