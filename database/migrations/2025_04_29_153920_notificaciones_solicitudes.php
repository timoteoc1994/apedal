<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notificaciones_solicitudes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('solicitud_id');
            $table->unsignedBigInteger('reciclador_id');
            $table->enum('estado', ['pendiente', 'aceptada', 'rechazada', 'expirada'])->default('pendiente');
            $table->timestamps();

            // Índices y relaciones
            $table->foreign('solicitud_id')->references('id')->on('solicitudes_recoleccion')->onDelete('cascade');
            $table->foreign('reciclador_id')->references('id')->on('recicladores')->onDelete('cascade');

            // No permitir duplicados
            $table->unique(['solicitud_id', 'reciclador_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificaciones_solicitudes');
    }
};
