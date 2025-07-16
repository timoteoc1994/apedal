<template>
  <div class="tracking-container">
    <div id="map" ref="mapContainer" class="map"></div>
    
    <div class="info-panel">
      <h3>Usuario #{{ recyclerId }}</h3>
      
      <div class="status-container">
        Estado: 
        <span :class="['status-badge', `status-${status}`]">
          {{ status }}
        </span>
      </div>
      
      <div class="info-item">
        Última actualización: {{ lastUpdate }}
      </div>
      
      <div class="info-item">
        Coordenadas: {{ coordinates }}
      </div>
      
      <div v-if="error" class="error">
        {{ error }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import axios from 'axios'; // Agregar esta línea

const props = defineProps({
  recyclerId: {
    type: [Number, String],
    default: 3
  }
});

// Referencias reactivas
const mapContainer = ref(null);
const status = ref('Cargando...');
const lastUpdate = ref('...');
const coordinates = ref('...');
const error = ref(null);

// Variables no reactivas
let map = null;
let marker = null;
let polyline = null;
let trail = [];
let updateInterval = null;

const initMap = () => {
  map = L.map(mapContainer.value).setView([-1.2491, -78.6167], 15);
  
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
  }).addTo(map);
  
  const recyclerIcon = L.icon({
    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.3.4/images/marker-shadow.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
  });
  
  marker = L.marker([0, 0], { icon: recyclerIcon }).addTo(map);
  
  polyline = L.polyline([], {
    color: '#3388ff',
    weight: 3,
    opacity: 0.7
  }).addTo(map);
};

const updateLocation = async () => {
  try {
    const response = await axios.get(`/api/tracking/${props.recyclerId}`);
    
    if (response.data.success) {
      const { location, status: userStatus } = response.data.data;
      
      status.value = userStatus;
      lastUpdate.value = location.last_update;
      error.value = null;
      
      const lat = parseFloat(location.latitude);
      const lng = parseFloat(location.longitude);
      coordinates.value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
      
      const newLatLng = [lat, lng];
      marker.setLatLng(newLatLng);
      
      if (trail.length === 0 || 
          trail[trail.length - 1][0] !== lat || 
          trail[trail.length - 1][1] !== lng) {
        
        trail.push(newLatLng);
        polyline.setLatLngs(trail);
        map.setView(newLatLng);
      }
    } else {
      error.value = response.data.message;
    }
  } catch (err) {
    console.error('Error al obtener ubicación:', err);
    error.value = 'Error al conectar con el servidor';
  }
};

onMounted(() => {
  initMap();
  updateLocation();
  updateInterval = setInterval(updateLocation, 2000);
});

onUnmounted(() => {
  if (updateInterval) {
    clearInterval(updateInterval);
  }
});
</script>

<style scoped>
.tracking-container {
  position: relative;
  height: 100vh;
  width: 100%;
}

.map {
  height: 100%;
  width: 100%;
}

.info-panel {
  position: absolute;
  top: 10px;
  right: 10px;
  background: white;
  padding: 15px;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
  z-index: 1000;
  min-width: 250px;
}

.status-badge {
  display: inline-block;
  padding: 5px 10px;
  border-radius: 15px;
  font-size: 12px;
  font-weight: bold;
  margin-left: 10px;
}

.status-disponible {
  background: #4CAF50;
  color: white;
}

.status-en_ruta {
  background: #2196F3;
  color: white;
}

.status-inactivo {
  background: #9E9E9E;
  color: white;
}

.info-item {
  margin: 10px 0;
}

.error {
  color: #f44336;
  margin-top: 10px;
  padding: 10px;
  background: #ffebee;
  border-radius: 4px;
}
</style>