<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Material;

class ImpactoAmbientalController extends Controller
{
    /**
     * Obtener estadísticas de impacto ambiental por mes
     */
    public function obtenerEstadisticasPorMes(Request $request)
    {

        try {
            // Extraer posible payload anidado en 'request'
            $payload = $request->input('request', null);

            // Si viene como JSON string, decodificar
            if (is_string($payload)) {
                $decoded = json_decode($payload, true);
                if (is_array($decoded)) $payload = $decoded;
            }

            if (is_object($payload)) $payload = (array) $payload;

            if (is_array($payload) && (array_key_exists('anios', $payload) || array_key_exists('meses', $payload))) {
                $years = $payload['anios'] ?? [];
                $months = $payload['meses'] ?? [];
            } else {
                // Fallback: buscar en params o body top-level
                $years = $request->input('anios', $request->input('anio', $request->query('anio')));
                $months = $request->input('meses', $request->input('mes', $request->query('mes')));

                // También admitir propiedades directas en $request
                if ($years === null) $years = $request->anios ?? $request->anio ?? [];
                if ($months === null) $months = $request->meses ?? $request->mes ?? [];
            }

            // Normalizar a arrays (acepta CSV en string también)
            if (!is_array($years)) {
                if (is_string($years) && strpos($years, ',') !== false) $years = array_map('trim', explode(',', $years));
                else $years = $years === null ? [] : [$years];
            }
            if (!is_array($months)) {
                if (is_string($months) && strpos($months, ',') !== false) $months = array_map('trim', explode(',', $months));
                else $months = $months === null ? [] : [$months];
            }

            // Convertir a enteros, filtrar rangos válidos, eliminar duplicados y ordenar
            $years = array_values(array_unique(array_filter(array_map('intval', $years), function ($y) { return $y > 0; })));
            sort($years);
            $months = array_values(array_unique(array_filter(array_map('intval', $months), function ($m) { return $m >= 1 && $m <= 12; })));
            sort($months);

            // Validar parámetros
            if (empty($years) || empty($months)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Años y meses son requeridos',
                    'errors' => ['Faltan parámetros anios y/o meses o valores inválidos']
                ], 400);
            }

            $usuario_id = Auth::user()->id;


            // Aquí irían las consultas reales a la base de datos
            // Por ahora uso datos de ejemplo
            $estadisticas = $this->generarEstadisticasEjemplo($years, $months, $usuario_id);

                // Crear una versión con cero inicial para meses ("01","02",...)
                $meses_padded = array_map(function ($m) {
                    return str_pad((string)$m, 2, '0', STR_PAD_LEFT);
                }, $months);

                // Logear los valores normalizados para facilitar debug
                Log::info('Años y meses normalizados', ['anios' => $years, 'meses' => $months, 'meses_padded' => $meses_padded]);

            return response()->json([
                'success' => true,
                'message' => 'Estadísticas obtenidas correctamente',
                'data' => $estadisticas
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * Generar datos de ejemplo para las estadísticas
     */
    private function generarEstadisticasEjemplo(array $anios, array $meses, $usuario_id)
    {
        //sumar todos los pesos totales donde 
        //papel
        $suma_papel = Material::where('user_id', $usuario_id)
            ->where('tipo', 'papel')
            ->whereIn(DB::raw('YEAR(created_at)'), $anios)
            ->whereIn(DB::raw('MONTH(created_at)'), $meses)
            ->sum('peso_revisado');
        //tetrapak
        //$suma_tetrapak = Material::where('user_id', $usuario_id)->where('tipo', 'tetrapak')->whereYear('created_at', $anio)->whereMonth('created_at', $mes)->sum('peso_revisado');
        //botellasPET
        $suma_botellasPET = Material::where('user_id', $usuario_id)
            ->where('tipo', 'botellasPET')
            ->whereIn(DB::raw('YEAR(created_at)'), $anios)
            ->whereIn(DB::raw('MONTH(created_at)'), $meses)
            ->sum('peso_revisado');
        //plasticosSuaves
        $suma_plasticosSuaves = Material::where('user_id', $usuario_id)
            ->where('tipo', 'plasticosSuaves')
            ->whereIn(DB::raw('YEAR(created_at)'), $anios)
            ->whereIn(DB::raw('MONTH(created_at)'), $meses)
            ->sum('peso_revisado');
        //plasticosSoplado
        $suma_plasticosSoplado = Material::where('user_id', $usuario_id)
            ->where('tipo', 'plasticosSoplado')
            ->whereIn(DB::raw('YEAR(created_at)'), $anios)
            ->whereIn(DB::raw('MONTH(created_at)'), $meses)
            ->sum('peso_revisado');
        //plasticosRigidos
        $suma_plasticosRigidos = Material::where('user_id', $usuario_id)
            ->where('tipo', 'plasticosRigidos')
            ->whereIn(DB::raw('YEAR(created_at)'), $anios)
            ->whereIn(DB::raw('MONTH(created_at)'), $meses)
            ->sum('peso_revisado');
        //vidrio
        //$suma_vidrio = Material::where('user_id', $usuario_id)->where('tipo', 'vidrio')->whereYear('created_at', $anio)->whereMonth('created_at', $mes)->sum('peso_revisado');
        //pilas
        //$suma_pilas = Material::where('user_id', $usuario_id)->where('tipo', 'pilas')->whereYear('created_at', $anio)->whereMonth('created_at', $mes)->sum('peso_revisado');
        //latas
        $suma_latas = Material::where('user_id', $usuario_id)
            ->where('tipo', 'latas')
            ->whereIn(DB::raw('YEAR(created_at)'), $anios)
            ->whereIn(DB::raw('MONTH(created_at)'), $meses)
            ->sum('peso_revisado');
        //metales
        //$suma_metales = Material::where('user_id', $usuario_id)->where('tipo', 'metales')->whereYear('created_at', $anio)->whereMonth('created_at', $mes)->sum('peso_revisado');
        //electrodomesticos
        //$suma_electrodomesticos = Material::where('user_id', $usuario_id)->where('tipo', 'electrodomesticos')->whereYear('created_at', $anio)->whereMonth('created_at', $mes)->sum('peso_revisado');
        //electronicos
        //$suma_electronicos = Material::where('user_id', $usuario_id)->where('tipo', 'electronicos')->whereYear('created_at', $anio)   ->whereMonth('created_at', $mes)->sum('peso_revisado');
        //otros
        //$suma_otros = Material::where('user_id', $usuario_id)->where('tipo', 'otros')->whereYear('created_at', $anio)->whereMonth('created_at', $mes)->sum('peso_revisado');
        // Datos simulados - aquí deberías hacer consultas reales a tu BD

        //estadisticas reales
        //Total papel y cartón para reciclaje
        $evitado_tala_arboles = $suma_papel / 58.85;
        $oxigeno_producido = $evitado_tala_arboles * 4;
        $co2_captado = $evitado_tala_arboles * 21;
        $evitado_consumo_agua = $suma_papel / 1000 * 70000;
        $evitado_consumo_kwh = $suma_papel / 1000 * 2750;
        $evitado_c02_anio = $suma_papel * 2.5;
        $evitado_emitir_admosfera = $evitado_consumo_kwh * 0.65;

        //total plasticos para reciclaje
        $total_plasticos_calculo = $suma_botellasPET + $suma_plasticosSuaves + $suma_plasticosSoplado + $suma_plasticosRigidos;
        $evitado_consumo_agua_plasticos = $total_plasticos_calculo * 233.67;
        $evitado_emitir_admosfera_plasticos = $total_plasticos_calculo * 1.45;
        $evitado_consumo_kwh_plasticos = $total_plasticos_calculo * 2.5;
        $recuperado_botellas_pet = $total_plasticos_calculo * (1000 / 18);

        //total latas de aluminio
        $total_calculo_aluminio = $suma_latas;
        $evitado_explotar_bauxita = $total_calculo_aluminio * 4;
        $recuperado_latas_bebida = $total_calculo_aluminio * (1000 / 15);
        $evitado_emitir_admosfera_aluminio = $total_calculo_aluminio * 16;
        $evitado_consumo_kwh_aluminio = $total_calculo_aluminio * 14.9;

        //evitado el desecho al relleno sanitario
        $suma_desechos_carros_recolector = ($suma_papel + $total_plasticos_calculo + $total_calculo_aluminio) / 100 / 15;

        //resumen eco-equivalencia
        $total_evitado_consumo_agua = $evitado_consumo_agua + $evitado_consumo_agua_plasticos;
        $total_evitado_emitir_admosfera = $evitado_emitir_admosfera + $evitado_emitir_admosfera_plasticos + $evitado_emitir_admosfera_aluminio + $evitado_c02_anio;
        $total_evitado_consumo_kwh = $evitado_consumo_kwh + $evitado_consumo_kwh_plasticos + $evitado_consumo_kwh_aluminio;


        // Construir impactos solo si el valor es mayor a cero
        $impactos_papel = [];
        if ($evitado_tala_arboles > 0) $impactos_papel[] = "Se ha evitado la tala de " . round($evitado_tala_arboles, 2) . " árboles adultos";
        if ($oxigeno_producido > 0) $impactos_papel[] = "Se produce el oxígeno necesario para " . round($oxigeno_producido, 0) . " personas";
        if ($co2_captado > 0) $impactos_papel[] = "Se captarán " . round($co2_captado, 1) . " Kg de CO2 al año";
        if ($evitado_consumo_agua > 0) $impactos_papel[] = "Se ha evitado el consumo de  " . round($evitado_consumo_agua, 0) . " litros de agua";
        if ($evitado_consumo_kwh > 0) $impactos_papel[] = "Se ha evitado el consumo de  " . round($evitado_consumo_kwh, 0) . " KWh de energía";
        if ($evitado_c02_anio > 0) $impactos_papel[] = "Se ha evitado la emisión de " . round($evitado_c02_anio, 0) . " Kg de CO2 al año";
        if ($evitado_emitir_admosfera > 0) $impactos_papel[] = "Se ha evitado emitir a la atmósfera " . round($evitado_emitir_admosfera, 0) . " Kg de CO2";

        $impactos_plastico = [];
        if ($evitado_consumo_agua_plasticos > 0) $impactos_plastico[] = "Se ha evitado el consumo de " . round($evitado_consumo_agua_plasticos, 0) . " litros de agua";
        if ($evitado_emitir_admosfera_plasticos > 0) $impactos_plastico[] = "Se ha evitado emitir a la atmósfera " . round($evitado_emitir_admosfera_plasticos, 0) . " Kg de CO2";
        if ($evitado_consumo_kwh_plasticos > 0) $impactos_plastico[] = "Se ha evitado el consumo de " . round($evitado_consumo_kwh_plasticos, 0) . " KWh de energía";
        if ($recuperado_botellas_pet > 0) $impactos_plastico[] = "Se ha recuperado el equivalente a  " . round($recuperado_botellas_pet, 0) . " botellas PET de 500 ml";

        $impactos_latas = [];
        if ($evitado_explotar_bauxita > 0) $impactos_latas[] = "Se ha evitado explotar " . round($evitado_explotar_bauxita, 0) . " Kg de Bauxita";
        if ($recuperado_latas_bebida > 0) $impactos_latas[] = "Se ha recuperado el equivalente a  " . round($recuperado_latas_bebida, 0) . " latas de bebida de 355 ml";
        if ($evitado_emitir_admosfera_aluminio > 0) $impactos_latas[] = "Se ha evitado emitir a la atmósfera " . round($evitado_emitir_admosfera_aluminio, 0) . " Kg de CO2";
        if ($evitado_consumo_kwh_aluminio > 0) $impactos_latas[] = "Se ha evitado el consumo de " . round($evitado_consumo_kwh_aluminio, 0) . " KWh de energía";

        // Para compatibilidad, devolvemos los arrays y también un valor representativo (primer año/mes)
        $estadisticas = [
            'anios' => $anios,
            'meses' => $meses,
                // También incluimos la versión con cero inicial para el frontend si la necesita
                'meses_padded' => array_map(function ($m) { return str_pad((string)$m, 2, '0', STR_PAD_LEFT); }, $meses),
            'anio' => (int)($anios[0] ?? 0),
            'mes' => (int)($meses[0] ?? 0),
            'usuario_id' => $usuario_id,

            'papel' => [
                'peso_total' => round($suma_papel, 2),
                'impactos' => $impactos_papel
            ],

            'plastico' => [
                'peso_total' => round($total_plasticos_calculo, 2),
                'impactos' => $impactos_plastico
            ],
            'latas' => [
                'peso_total' => round($total_calculo_aluminio, 2),
                'impactos' => $impactos_latas
            ],

            'resumen_total' => [
                'total_consumo_agua' => round($total_evitado_consumo_agua, 0),
                'total_emitir_admosfera' => round($total_evitado_emitir_admosfera, 0),
                'total_consumo_energia' => round($total_evitado_consumo_kwh, 0),
                'relleno_carro_recolector' => round($suma_desechos_carros_recolector, 5),
            ]
        ];

        return $estadisticas;
    }
}

// Ruta que debes agregar en tu routes/api.php:
/*
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/ciudadano/impacto-ambiental', [ImpactoAmbientalController::class, 'obtenerEstadisticasPorMes']);
});
*/