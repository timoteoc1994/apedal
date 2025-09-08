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
        Schema::table('formulari_mensuals', function (Blueprint $table) {
            $table->decimal('espuma_flex_kilos', 10, 2)->nullable();
            $table->decimal('espuma_flex_precio', 10, 2)->nullable();
            $table->decimal('polipropileno_expandido_kilos', 10, 2)->nullable();
            $table->decimal('polipropileno_expandido_precio', 10, 2)->nullable();
            $table->decimal('otro_material_1_kilos', 10, 2)->nullable();
            $table->decimal('otro_material_1_precio', 10, 2)->nullable();
            $table->decimal('otro_material_2_kilos', 10, 2)->nullable();
            $table->decimal('otro_material_2_precio', 10, 2)->nullable();
            $table->decimal('otro_material_3_kilos', 10, 2)->nullable();
            $table->decimal('otro_material_3_precio', 10, 2)->nullable();
        });
           
        
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('formulari_mensuals', function (Blueprint $table) {
            $table->dropColumn([
                'espuma_flex_kilos',
                'espuma_flex_precio',
                'polipropileno_expandido_kilos',
                'polipropileno_expandido_precio',
                'otro_material_1_kilos',
                'otro_material_1_precio',
                'otro_material_2_kilos',
                'otro_material_2_precio',
                'otro_material_3_kilos',
                'otro_material_3_precio'
            ]);
        });
    }
};
