<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Material;

class ImpactoAmbientalController extends Controller
{
    /**
     * Obtener estadísticas de impacto ambiental por mes
     */
    public function obtenerEstadisticasPorMes(Request $request)
    {
        try {
            $anio = $request->query('anio');
            $mes = $request->query('mes');

            // Validar parámetros
            if (!$anio || !$mes) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anio y mes son requeridos',
                    'errors' => ['Faltan parámetros anio y/o mes']
                ], 400);
            }

            $usuario_id = Auth::user()->id;


            // Aquí irían las consultas reales a la base de datos
            // Por ahora uso datos de ejemplo
            $estadisticas = $this->generarEstadisticasEjemplo($anio, $mes, $usuario_id);

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
    private function generarEstadisticasEjemplo($anio, $mes, $usuario_id)
    {
        //sumar todos los pesos totales donde 
        //papel
        $suma_papel = Material::where('user_id', $usuario_id)->where('tipo', 'papel')->whereYear('created_at', $anio)->whereMonth('created_at', $mes)->sum('peso_revisado');
        //tetrapak
        //$suma_tetrapak = Material::where('user_id', $usuario_id)->where('tipo', 'tetrapak')->whereYear('created_at', $anio)->whereMonth('created_at', $mes)->sum('peso_revisado');
        //botellasPET
        $suma_botellasPET = Material::where('user_id', $usuario_id)->where('tipo', 'botellasPET')->whereYear('created_at', $anio)->whereMonth('created_at', $mes)->sum('peso_revisado');
        //plasticosSuaves
        $suma_plasticosSuaves = Material::where('user_id', $usuario_id)->where('tipo', 'plasticosSuaves')->whereYear('created_at', $anio)->whereMonth('created_at', $mes)->sum('peso_revisado');
        //plasticosSoplado
        $suma_plasticosSoplado = Material::where('user_id', $usuario_id)->where('tipo', 'plasticosSoplado')->whereYear('created_at', $anio)->whereMonth('created_at', $mes)->sum('peso_revisado');
        //plasticosRigidos
        $suma_plasticosRigidos = Material::where('user_id', $usuario_id)->where('tipo', 'plasticosRigidos')->whereYear('created_at', $anio)->whereMonth('created_at', $mes)->sum('peso_revisado');
        //vidrio
        //$suma_vidrio = Material::where('user_id', $usuario_id)->where('tipo', 'vidrio')->whereYear('created_at', $anio)->whereMonth('created_at', $mes)->sum('peso_revisado');
        //pilas
        //$suma_pilas = Material::where('user_id', $usuario_id)->where('tipo', 'pilas')->whereYear('created_at', $anio)->whereMonth('created_at', $mes)->sum('peso_revisado');
        //latas
        $suma_latas = Material::where('user_id', $usuario_id)->where('tipo', 'latas')->whereYear('created_at', $anio)->whereMonth('created_at', $mes)->sum('peso_revisado');
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


        $estadisticas = [
            'anio' => (int)$anio,
            'mes' => (int)$mes,
            'usuario_id' => $usuario_id,

            'papel' => [
                'peso_total' => round($suma_papel, 2),
                'impactos' => [
                    "Se ha evitado la tala de " . round($evitado_tala_arboles, 2) . " árboles adultos",
                    "Se produce el oxígeno necesario para " . round($oxigeno_producido, 0) . " personas",
                    "Se captarán " . round($co2_captado, 1) . " Kg de CO2 al año",
                    "Se ha evitado el consumo de  " . round($evitado_consumo_agua, 0) . " litros de agua",
                    "Se ha evitado el consumo de  " . round($evitado_consumo_kwh, 0) . " KWh de energía",
                    "Se ha evitado la emisión de " . round($evitado_c02_anio, 0) . " Kg de CO2 al año",
                    "Se ha evitado emitir a la atmósfera " . round($evitado_emitir_admosfera, 0) . " Kg de CO2",

                ]
            ],

            'plastico' => [
                'peso_total' => round($total_plasticos_calculo, 2),
                'impactos' => [
                    "Se ha evitado el consumo de " . round($evitado_consumo_agua_plasticos, 0) . " litros de agua",
                    "Se ha evitado emitir a la atmósfera " . round($evitado_emitir_admosfera_plasticos, 0) . " Kg de CO2",
                    "Se ha evitado el consumo de " . round($evitado_consumo_kwh_plasticos, 0) . " KWh de energía",
                    "Se ha recuperado el equivalente a  " . round($recuperado_botellas_pet, 0) . " botellas PET de 500 ml",

                ]
            ],
            'latas' => [
                'peso_total' => round($total_calculo_aluminio, 2),
                'impactos' => [
                    "Se ha evitado explotar " . round($evitado_explotar_bauxita, 0) . " Kg de Bauxita",
                    "Se ha recuperado el equivalente a  " . round($recuperado_latas_bebida, 0) . " latas de bebida de 355 ml",
                    "Se ha evitado emitir a la atmósfera " . round($evitado_emitir_admosfera_aluminio, 0) . " Kg de CO2",
                    "Se ha evitado el consumo de " . round($evitado_consumo_kwh_aluminio, 0) . " KWh de energía",

                ]
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