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
        // Agregar campos de sincronización a tabla recicladores
        Schema::table('recicladores', function (Blueprint $table) {
            $table->timestamp('ultima_sincronizacion')->nullable()->after('logo_url');
            $table->json('solicitudes_notificadas')->nullable()->after('ultima_sincronizacion');
        });

        // Agregar campos a tabla solicitudes_recoleccion para control de notificaciones
        Schema::table('solicitudes_recoleccion', function (Blueprint $table) {
            $table->json('recicladores_notificados')->nullable()->after('ids_disponibles');
            $table->timestamp('fecha_limite_asignacion')->nullable()->after('recicladores_notificados');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recicladores', function (Blueprint $table) {
            $table->dropColumn(['ultima_sincronizacion', 'solicitudes_notificadas']);
        });

        Schema::table('solicitudes_recoleccion', function (Blueprint $table) {
            $table->dropColumn(['recicladores_notificados', 'fecha_limite_asignacion']);
        });
    }
};
