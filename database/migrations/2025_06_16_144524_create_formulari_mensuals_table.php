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
        Schema::create('formulari_mensuals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('auth_users')->onDelete('cascade');
            $table->foreignId('asociacion_id')->constrained('asociaciones')->onDelete('cascade');
            $table->string('mes');
            $table->string('anio');
            $table->text('lugar');
            $table->integer('num_recicladores');
            $table->decimal('total_kilos', 10, 2)->default(0);
            $table->decimal('total_monto', 12, 2)->default(0);

            // Campos para cada material (kilos y precio)
            $table->decimal('carton_kilos', 10, 2)->nullable();
            $table->decimal('carton_precio', 10, 2)->nullable();
            $table->decimal('duplex_cubeta_kilos', 10, 2)->nullable();
            $table->decimal('duplex_cubeta_precio', 10, 2)->nullable();
            $table->decimal('papel_comercio_kilos', 10, 2)->nullable();
            $table->decimal('papel_comercio_precio', 10, 2)->nullable();
            $table->decimal('papel_bond_kilos', 10, 2)->nullable();
            $table->decimal('papel_bond_precio', 10, 2)->nullable();
            $table->decimal('papel_mixto_kilos', 10, 2)->nullable();
            $table->decimal('papel_mixto_precio', 10, 2)->nullable();
            $table->decimal('papel_multicolor_kilos', 10, 2)->nullable();
            $table->decimal('papel_multicolor_precio', 10, 2)->nullable();
            $table->decimal('tetrapak_kilos', 10, 2)->nullable();
            $table->decimal('tetrapak_precio', 10, 2)->nullable();
            $table->decimal('plastico_soplado_kilos', 10, 2)->nullable();
            $table->decimal('plastico_soplado_precio', 10, 2)->nullable();
            $table->decimal('plastico_duro_kilos', 10, 2)->nullable();
            $table->decimal('plastico_duro_precio', 10, 2)->nullable();
            $table->decimal('plastico_fino_kilos', 10, 2)->nullable();
            $table->decimal('plastico_fino_precio', 10, 2)->nullable();
            $table->decimal('pet_kilos', 10, 2)->nullable();
            $table->decimal('pet_precio', 10, 2)->nullable();
            $table->decimal('vidrio_kilos', 10, 2)->nullable();
            $table->decimal('vidrio_precio', 10, 2)->nullable();
            $table->decimal('chatarra_kilos', 10, 2)->nullable();
            $table->decimal('chatarra_precio', 10, 2)->nullable();
            $table->decimal('bronce_kilos', 10, 2)->nullable();
            $table->decimal('bronce_precio', 10, 2)->nullable();
            $table->decimal('cobre_kilos', 10, 2)->nullable();
            $table->decimal('cobre_precio', 10, 2)->nullable();
            $table->decimal('aluminio_kilos', 10, 2)->nullable();
            $table->decimal('aluminio_precio', 10, 2)->nullable();
            $table->decimal('pvc_kilos', 10, 2)->nullable();
            $table->decimal('pvc_precio', 10, 2)->nullable();
            $table->decimal('baterias_kilos', 10, 2)->nullable();
            $table->decimal('baterias_precio', 10, 2)->nullable();
            $table->decimal('lona_kilos', 10, 2)->nullable();
            $table->decimal('lona_precio', 10, 2)->nullable();
            $table->decimal('caucho_kilos', 10, 2)->nullable();
            $table->decimal('caucho_precio', 10, 2)->nullable();
            $table->decimal('fomix_kilos', 10, 2)->nullable();
            $table->decimal('fomix_precio', 10, 2)->nullable();
            $table->decimal('polipropileno_kilos', 10, 2)->nullable();
            $table->decimal('polipropileno_precio', 10, 2)->nullable();
            $table->json('archivos_adjuntos')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formulari_mensuals');
    }
};
