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
        Schema::create('puntos', function (Blueprint $table) {
            $table->id();
            //fecha hasta cuando esta activo
            $table->date('fecha_hasta')->nullable();
            //puntos por registro promocional
            $table->integer('puntos_registro_promocional')->default(0);
            //puntos por registro de reciclador
            $table->integer('puntos_reciclado_normal')->default(0);
            //puntos por referido
            $table->integer('puntos_reciclado_referido')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('puntos');
    }
};
