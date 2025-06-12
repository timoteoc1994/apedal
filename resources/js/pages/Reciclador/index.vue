<template>
    <div class="container">
        <h1 class="title">Listado de Recicladores</h1>

        <!-- Botón para Crear Reciclador -->
        <div class="create-button-container">
            <button class="btn create-btn" @click="createReciclador">Crear Reciclador</button>
        </div>

        <!-- Mensaje cuando no hay recicladores -->
        <div v-if="recicladoresLocal.length === 0" class="no-citizens">
            <p>No hay recicladores disponibles. Por favor, agrega un reciclador.</p>
        </div>

        <!-- Tabla de recicladores -->
        <table v-else>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Teléfono</th>
                    <th>Ciudad</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="reciclador in recicladoresLocal" :key="reciclador.id">
                    <td>{{ reciclador.name }}</td>
                    <td>{{ reciclador.telefono }}</td>
                    <td>{{ reciclador.ciudad }}</td>
                    <td class="actions">
                        <button class="btn edit-btn" @click="editReciclador(reciclador.id)">Editar</button>
                        <button class="btn delete-btn" @click="deleteReciclador(reciclador.id)">Eliminar</button>
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
        recicladores: Array,
    },

    data() {
        return {
            recicladoresLocal: [...this.recicladores] // Copia local de la prop
        };
    },

    watch: {
        // Mantener sincronizado si la prop cambia desde el padre
        recicladores(newVal) {
            this.recicladoresLocal = [...newVal];
        }
    },

    methods: {
        createReciclador() {
            this.$inertia.visit('/crear-reciclador');  // Cambia la ruta a la de reciclador
        },

        editReciclador(id) {
            // Lógica para editar un reciclador
            this.$inertia.visit(`/recicladores/${id}/editar`); // Cambia la ruta a la de reciclador
        },

        async deleteReciclador(id) {
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
                    const response = await axios.delete(`/recicladores/${id}`);

                    if (response.data.success) {
                        Swal.fire({
                            title: '¡Eliminado!',
                            text: 'El reciclador ha sido eliminado.',
                            icon: 'success',
                            confirmButtonText: 'Cerrar'
                        });

                        // Filtrar el reciclador eliminado desde la copia local
                        this.recicladoresLocal = this.recicladoresLocal.filter(reciclador => reciclador.id !== id);
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: 'Hubo un problema al eliminar el reciclador.',
                            icon: 'error',
                            confirmButtonText: 'Cerrar'
                        });
                    }
                } catch (error) {
                    console.error('Error al eliminar reciclador:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Hubo un problema al eliminar el reciclador.',
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

/* Contenedor del botón de crear reciclador */
.create-button-container {
    text-align: center;
    margin-bottom: 20px;
}

/* Estilo del botón de crear reciclador */
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

/* Mensaje cuando no hay recicladores */
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
