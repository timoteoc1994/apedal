<template>
    <div class="container">
        <h1 class="title">Listado de Ciudadanos</h1>

        <!-- Botón para Crear Ciudadano -->
        <div class="create-button-container">
            <button class="btn create-btn" @click="createCiudadano">Crear Ciudadano</button>
        </div>

        <!-- Mensaje cuando no hay ciudadanos -->
        <div v-if="ciudadanosLocal.length === 0" class="no-citizens">
            <p>No hay ciudadanos disponibles. Por favor, agrega un ciudadano.</p>
        </div>

        <!-- Tabla de ciudadanos -->
        <table v-else>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Teléfono</th>
                    <th>Dirección</th>
                    <th>Ciudad</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="ciudadano in ciudadanosLocal" :key="ciudadano.id">
                    <td>{{ ciudadano.name }}</td>
                    <td>{{ ciudadano.telefono }}</td>
                    <td>{{ ciudadano.direccion }}</td>
                    <td>{{ ciudadano.ciudad }}</td>
                    <td class="actions">
                        <button class="btn edit-btn" @click="editCiudadano(ciudadano.id)">Editar</button>
                        <button class="btn delete-btn" @click="deleteCiudadano(ciudadano.id)">Eliminar</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
import Swal from 'sweetalert2'
import axios from 'axios'

export default {
    props: {
        ciudadanos: Array,
    },

    data() {
        return {
            ciudadanosLocal: [...this.ciudadanos] // Copia local de la prop
        };
    },

    watch: {
        // Mantener sincronizado si la prop cambia desde el padre
        ciudadanos(newVal) {
            this.ciudadanosLocal = [...newVal];
        }
    },

    methods: {
        createCiudadano() {
            this.$inertia.visit('/crear-ciudadano');
        },

        editCiudadano(id) {
            console.log(`Editar ciudadano con ID: ${id}`);
        },

        async deleteCiudadano(id) {
            const confirmation = await Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Eliminar',
                cancelButtonText: 'Cancelar'
            });

            if (confirmation.isConfirmed) {
                try {
                    const response = await axios.delete(`/ciudadanos/${id}`);

                    if (response.data.success) {
                        Swal.fire({
                            title: '¡Eliminado!',
                            text: 'El ciudadano ha sido eliminado.',
                            icon: 'success',
                            confirmButtonText: 'Cerrar'
                        });

                        // Filtrar el ciudadano eliminado desde la copia local
                        this.ciudadanosLocal = this.ciudadanosLocal.filter(ciudadano => ciudadano.id !== id);
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: 'Hubo un problema al eliminar el ciudadano.',
                            icon: 'error',
                            confirmButtonText: 'Cerrar'
                        });
                    }
                } catch (error) {
                    console.error('Error al eliminar ciudadano:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Hubo un problema al eliminar el ciudadano.',
                        icon: 'error',
                        confirmButtonText: 'Cerrar'
                    });
                }
            }
        }
    }
}
</script>

<style scoped>
/* Contenedor principal */
.container {
    width: 90%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    font-family: 'Arial', sans-serif;
}

/* Título de la página */
.title {
    text-align: center;
    font-size: 2rem;
    margin-bottom: 20px;
    color: #2c3e50;
}

/* Contenedor del botón de crear ciudadano */
.create-button-container {
    text-align: center;
    margin-bottom: 20px;
}

/* Estilo del botón de crear ciudadano */
.create-btn {
    padding: 10px 20px;
    font-size: 1rem;
    background-color: #2ecc71;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.create-btn:hover {
    background-color: #27ae60;
}

/* Mensaje cuando no hay ciudadanos */
.no-citizens {
    text-align: center;
    font-size: 1.2rem;
    color: #e74c3c;
    padding: 20px;
    background-color: #f9e4e4;
    border-radius: 5px;
}

/* Estilo de la tabla */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    background-color: #fff;
}

th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

th {
    background-color: #f2f2f2;
    color: #333;
    font-weight: bold;
}

tbody tr:hover {
    background-color: #f9f9f9;
}

/* Estilo de las acciones de los botones */
.actions {
    display: flex;
    gap: 10px;
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: background-color 0.3s ease;
}

.edit-btn {
    background-color: #3498db;
    color: #fff;
}

.edit-btn:hover {
    background-color: #2980b9;
}

.delete-btn {
    background-color: #e74c3c;
    color: #fff;
}

.delete-btn:hover {
    background-color: #c0392b;
}
</style>
