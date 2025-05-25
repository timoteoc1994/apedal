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
        Schema::create('solicitudes_recoleccion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('auth_users')->onDelete('cascade');
            $table->foreignId('asociacion_id')->nullable()->constrained('auth_users');
            $table->foreignId('zona_id')->nullable()->constrained('zonas');
            $table->foreignId('reciclador_id')->nullable()->constrained('auth_users');
            $table->json('ids_disponibles')->nullable();
            $table->date('fecha');
            $table->string('hora_inicio');
            $table->string('hora_fin');
            $table->string('direccion');
            $table->string('referencia')->nullable();
            $table->double('latitud', 10, 7);
            $table->double('longitud', 10, 7);
            $table->decimal('peso_total', 8, 2);
            $table->string('imagen')->nullable();
            $table->enum('estado', ['pendiente', 'asignado', 'en_camino', 'completado', 'cancelado', 'buscando_reciclador', 'agendada'])->default('pendiente');
            $table->dateTime('fecha_completado')->nullable();
            $table->text('comentarios')->nullable();
            $table->string('ciudad');
            $table->boolean('es_inmediata')->default(false);
            //calificaciones
            $table->integer('calificacion_reciclador')->nullable();
            $table->integer('calificacion_ciudadano')->nullable();
            $table->decimal('peso_total_revisado', 8, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('materiales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->constrained('solicitudes_recoleccion')->onDelete('cascade');
            $table->string('tipo');
            $table->decimal('peso', 8, 2);
            $table->decimal('peso_revisado', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materiales');
        Schema::dropIfExists('solicitudes_recoleccion');
    }
};
