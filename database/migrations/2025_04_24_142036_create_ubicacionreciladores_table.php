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
        Schema::create('ubicacionreciladores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('auth_user_id');
            $table->double('latitude', 10, 8);
            $table->double('longitude', 11, 8);
            $table->timestamp('timestamp')->nullable();
            $table->timestamps();

            // Relación con el usuario
            $table->foreign('auth_user_id')->references('id')->on('auth_users')->onDelete('cascade');

            // Índice para el usuario (único)
            $table->unique('auth_user_id');

            // Índice para búsquedas geoespaciales
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ubicacionreciladores');
    }
};
