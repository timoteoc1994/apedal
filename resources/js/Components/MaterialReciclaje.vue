<template>
  <div class="flex items-center gap-2">
    <img
      v-if="materialInfo"
      :src="getUrlImagen(materialInfo.imagen)"
      :alt="materialInfo.nombre"
      class="h-8 w-8 rounded bg-gray-100 object-contain border"
    />
    <span class="font-semibold">{{ materialInfo ? materialInfo.nombre : tipo }}</span>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
  tipo: string
}>();

// Lista de tipos de reciclaje (puedes moverla a un archivo aparte si quieres)
const recyclingTypes = [
  { id: 'papel', nombre: 'Papel y Cartón', imagen: 'tiporeciclaje/PapelyCarton.png' },
  { id: 'tetrapak', nombre: 'Tetra Pak', imagen: 'tiporeciclaje/Tetrapack.png' },
  { id: 'botellasPET', nombre: 'Botellas PET', imagen: 'tiporeciclaje/BotellasPet.png' },
  { id: 'plasticosSuaves', nombre: 'Plásticos Suaves', imagen: 'tiporeciclaje/Bolsaplastico.png' },
  { id: 'plasticosSoplado', nombre: 'Plásticos Soplado', imagen: 'tiporeciclaje/Plasticosoplado.png' },
  { id: 'plasticosRigidos', nombre: 'Plásticos Rígidos', imagen: 'tiporeciclaje/Plasticorigido.png' },
  { id: 'vidrio', nombre: 'Envases de Vidrio', imagen: 'tiporeciclaje/Vidrio.png' },
  { id: 'pilas', nombre: 'Pilas y Baterías', imagen: 'tiporeciclaje/Pilas.png' },
  { id: 'latas', nombre: 'Aluminio', imagen: 'tiporeciclaje/Latas.png' },
  { id: 'metales', nombre: 'Metales', imagen: 'tiporeciclaje/Metal.png' },
  { id: 'electrodomesticos', nombre: 'Electrodomésticos', imagen: 'tiporeciclaje/electrodome.png' },
  { id: 'electronicos', nombre: 'Electrónicos', imagen: 'tiporeciclaje/electronico.png' },
  { id: 'otros', nombre: 'Otros', imagen: 'tiporeciclaje/otros.png' },
];

const materialInfo = computed(() =>
  recyclingTypes.find(t => t.id.toLowerCase() === props.tipo.toLowerCase())
);

function getUrlImagen(path: string) {
  if (!path) return '';
  if (path.startsWith('http')) return path;
  // Asegura que la ruta incluya /storage/ si no lo tiene
  let ruta = path.startsWith('/storage/') ? path : '/storage/' + path.replace(/^\//, '');
  const base = window.location.origin;
  return base + '/' + ruta.replace(/^\//, '');
}
</script>