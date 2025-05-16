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
        // Tabla de ciudades
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
        });

        // Tabla de ciudadanos
        Schema::create('ciudadanos', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('telefono')->nullable();
            $table->string('direccion');
            $table->string('ciudad');
            $table->text('referencias_ubicacion')->nullable();
            $table->string('logo_url')->nullable();
            $table->timestamps();
        });

        // Tabla de asociaciones
        Schema::create('asociaciones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('number_phone');
            $table->string('direccion')->nullable();
            $table->string('city');
            $table->text('descripcion')->nullable();
            $table->string('logo_url')->nullable();
            $table->boolean('verified')->default(false);
            $table->string('color')->default('#0000FF');
            $table->timestamps();
        });

        // Tabla de recicladores
        Schema::create('recicladores', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('telefono');
            $table->string('ciudad');
            $table->unsignedBigInteger('asociacion_id');
            $table->enum('status', ['disponible', 'en_ruta', 'inactivo'])->default('disponible');
            $table->enum('estado', ['Activo', 'Inactivo'])->default('Activo');
            $table->string('logo_url')->nullable();
            $table->timestamps();

            $table->foreign('asociacion_id')->references('id')->on('asociaciones');
        });

        // Tabla de autenticación
        Schema::create('auth_users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('fcm_token')->nullable();
            $table->enum('role', ['ciudadano', 'reciclador', 'asociacion']);
            $table->unsignedBigInteger('profile_id');
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_users');
        Schema::dropIfExists('recicladores');
        Schema::dropIfExists('asociaciones');
        Schema::dropIfExists('ciudadanos');
        Schema::dropIfExists('cities');
    }
};
