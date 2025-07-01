<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormulariMensual extends Model
{
    protected $table = 'formulari_mensuals';

    protected $fillable = [
        'user_id',
        'asociacion_id',
        'mes',
        'anio',
        'lugar',
        'num_recicladores',
        'total_kilos',
        'total_monto',
        // Materiales (kilos y precios)
        'carton_kilos',
        'carton_precio',
        'duplex_cubeta_kilos',
        'duplex_cubeta_precio',
        'papel_comercio_kilos',
        'papel_comercio_precio',
        'papel_bond_kilos',
        'papel_bond_precio',
        'papel_mixto_kilos',
        'papel_mixto_precio',
        'papel_multicolor_kilos',
        'papel_multicolor_precio',
        'tetrapak_kilos',
        'tetrapak_precio',
        'plastico_soplado_kilos',
        'plastico_soplado_precio',
        'plastico_duro_kilos',
        'plastico_duro_precio',
        'plastico_fino_kilos',
        'plastico_fino_precio',
        'pet_kilos',
        'pet_precio',
        'vidrio_kilos',
        'vidrio_precio',
        'chatarra_kilos',
        'chatarra_precio',
        'bronce_kilos',
        'bronce_precio',
        'cobre_kilos',
        'cobre_precio',
        'aluminio_kilos',
        'aluminio_precio',
        'pvc_kilos',
        'pvc_precio',
        'baterias_kilos',
        'baterias_precio',
        'lona_kilos',
        'lona_precio',
        'caucho_kilos',
        'caucho_precio',
        'fomix_kilos',
        'fomix_precio',
        'polipropileno_kilos',
        'polipropileno_precio',
        'archivos_adjuntos',
    ];
}
