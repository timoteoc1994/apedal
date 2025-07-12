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
        //tablas productos
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('tipo_producto');
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('direccion_reclamo')->nullable();
            $table->string('url_imagen');
            $table->string('categoria');
            $table->integer('puntos')->default(0);
            $table->string('estado')->default('publicado');
            $table->timestamps();
        });

        //tabla canje
        Schema::create('canje', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auth_user_id')->constrained('auth_users')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->integer('puntos_canjeados');
            $table->string('codigo');
            $table->string('estado')->default('pendiente');
            $table->string('fecha_canjeado')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('canje');
        Schema::dropIfExists('productos');
    }
};
