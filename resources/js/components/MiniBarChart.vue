<template>
  <div class="w-full max-w-xs p-2">
    <canvas ref="canvas" height="120"></canvas>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps<{
  labels: string[];
  values: number[];
  colors?: string[];
}>();

const canvas = ref<HTMLCanvasElement | null>(null);
let chart: Chart | null = null;

const defaultColors = [
  '#10b981', // completadas
  '#f59e42', // pendientes
  '#6366f1', // asignadas
  '#3b82f6', // en_camino
  '#a855f7', // buscando_reciclador
  '#ef4444', // cancelado
];

function renderChart() {
  if (!canvas.value) return;
  if (chart) chart.destroy();
  chart = new Chart(canvas.value, {
    type: 'bar',
    data: {
      labels: props.labels,
      datasets: [{
        data: props.values,
        backgroundColor: props.colors || defaultColors,
        borderRadius: 6,
        borderSkipped: false,
        barPercentage: 0.7,
        categoryPercentage: 0.7,
      }],
    },
    options: {
      plugins: {
        legend: { display: false },
        tooltip: { enabled: true },
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { font: { size: 11 } },
        },
        y: {
          beginAtZero: true,
          grid: { color: '#e5e7eb' },
          ticks: { stepSize: 1, font: { size: 11 } },
        },
      },
      responsive: true,
      maintainAspectRatio: false,
    },
  });
}

onMounted(renderChart);
watch(() => [props.labels, props.values], renderChart);
</script>

<style scoped>
canvas {
  width: 100% !important;
  height: 120px !important;
}
</style>
