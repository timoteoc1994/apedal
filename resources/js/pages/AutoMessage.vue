<template>
    <div class="auto-message-container">
      <h1>Auto Mensaje</h1>
      <div class="status-panel">
        <p>Estado: {{ status }}</p>
        <p>Hora: {{ time }}</p>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    props: {
      status: String,
      time: String,
    },
  }
  </script>
  
  <style scoped>
  .auto-message-container {
    padding: 20px;
    max-width: 600px;
    margin: 0 auto;
    font-family: sans-serif;
  }
  .status-panel {
    background-color: #f5f5f5;
    padding: 15px;
    border-radius: 8px;
    margin-top: 20px;
  }
  </style>