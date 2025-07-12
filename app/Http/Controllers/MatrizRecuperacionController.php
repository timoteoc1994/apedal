<?php

namespace App\Http\Controllers;

use App\Models\Asociacion;
use App\Models\FormulariMensual;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class MatrizRecuperacionController extends Controller
{
    function index(Request $request)
    {


        $asociacion = Asociacion::all();
        //obtener solo nombre de asociacion
        $asociacion = $asociacion->map(function ($item) {
            return [
                'id' => $item->id,
                'nombre' => $item->name,
            ];
        });
        if ($request->filled(['anio', 'mes', 'asociacion'])) {
            $validate = $request->validate([
                'anio' => 'required|integer|min:2020|max:' . date('Y'),
                'mes' => 'required',
                'asociacion' => 'required',
            ]);

            $query = FormulariMensual::with('asociacion');

            if ($request->anio !== 'Todos') {
                $query->where('anio', $request->anio);
            }
            if ($request->mes !== 'Todos') {
                $query->where('mes', $request->mes);
            }
            if ($request->asociacion !== 'Todos') {
                $query->where('asociacion_id', $request->asociacion);
            }
            $matriz_recuperacion = $query->paginate(15);
        } else {
            $matriz_recuperacion = FormulariMensual::with('asociacion')->paginate(15);
        }


        return inertia('MatrizRecuperacion/Index', [
            'matriz_recuperacion' => $matriz_recuperacion,
            'asociacion' => $asociacion
        ]);
    }
    public function descargar_excel(Request $request)
    {

        if ($request->filled(['anio', 'mes', 'asociacion'])) {
     
            $query = FormulariMensual::with('asociacion');

            if ($request->anio !== 'Todos') {
                $query->where('anio', $request->anio);
            }
            if ($request->mes !== 'Todos') {
                $query->where('mes', $request->mes);
            }
            if ($request->asociacion !== 'Todos') {
                $query->where('asociacion_id', $request->asociacion);
            }
            $usuarios = $query->get();
        } else {
            $usuarios = FormulariMensual::with('asociacion')->get();
        }
   

        // Crear nuevo archivo
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Encabezados
        $sheet->setCellValue('A1', 'NRO');
        $sheet->setCellValue('B1', 'Asociación');
        $sheet->setCellValue('C1', 'Mes');
        $sheet->setCellValue('D1', 'Año');
        $sheet->setCellValue('E1', 'Fecha de Reporte');
        $sheet->setCellValue('F1', 'Lugar de Recuperación (botadero, relleno, calle)');
        $sheet->setCellValue('G1', 'Número Recicladores que Reportan');
        $sheet->setCellValue('H1', 'Cartón (kilos)');
        $sheet->setCellValue('I1', 'Cartón Precio');

        $sheet->setCellValue('J1', 'Duplex Cubeta Kilos');
        $sheet->setCellValue('K1', 'Duplex Cubeta Precio');
        $sheet->setCellValue('L1', 'Papel Comercio Kilos');
        $sheet->setCellValue('M1', 'Papel Comercio Precio');
        $sheet->setCellValue('N1', 'Papel Bond Kilos');
        $sheet->setCellValue('O1', 'Papel Bond Precio');
        $sheet->setCellValue('P1', 'Papel Mixto Kilos');
        $sheet->setCellValue('Q1', 'Papel Mixto Precio');
        $sheet->setCellValue('R1', 'Papel Multicolor Kilos');
        $sheet->setCellValue('S1', 'Papel Multicolor Precio');
        $sheet->setCellValue('T1', 'Tetrapak Kilos');
        $sheet->setCellValue('U1', 'Tetrapak Precio');
        $sheet->setCellValue('V1', 'Plástico Soplado Kilos');
        $sheet->setCellValue('W1', 'Plástico Soplado Precio');
        $sheet->setCellValue('X1', 'Plástico Duro Kilos');
        $sheet->setCellValue('Y1', 'Plástico Duro Precio');
        $sheet->setCellValue('Z1', 'Plástico Fino Kilos');
        $sheet->setCellValue('AA1', 'Plástico Fino Precio');
        $sheet->setCellValue('AB1', 'PET Kilos');
        $sheet->setCellValue('AC1', 'PET Precio');
        $sheet->setCellValue('AD1', 'Vidrio Kilos');
        $sheet->setCellValue('AE1', 'Vidrio Precio');
        $sheet->setCellValue('AF1', 'Chatarra Kilos');
        $sheet->setCellValue('AG1', 'Chatarra Precio');
        $sheet->setCellValue('AH1', 'Bronce Kilos');
        $sheet->setCellValue('AI1', 'Bronce Precio');
        $sheet->setCellValue('AJ1', 'Cobre Kilos');
        $sheet->setCellValue('AK1', 'Cobre Precio');
        $sheet->setCellValue('AL1', 'Aluminio Kilos');
        $sheet->setCellValue('AM1', 'Aluminio Precio');
        $sheet->setCellValue('AN1', 'PVC Kilos');
        $sheet->setCellValue('AO1', 'PVC Precio');
        $sheet->setCellValue('AP1', 'Baterías Kilos');
        $sheet->setCellValue('AQ1', 'Baterías Precio');
        $sheet->setCellValue('AR1', 'Lona Kilos');
        $sheet->setCellValue('AS1', 'Lona Precio');
        $sheet->setCellValue('AT1', 'Caucho Kilos');
        $sheet->setCellValue('AU1', 'Caucho Precio');
        $sheet->setCellValue('AV1', 'Fomix Kilos');
        $sheet->setCellValue('AW1', 'Fomix Precio');
        $sheet->setCellValue('AX1', 'Polipropileno Kilos');
        $sheet->setCellValue('AY1', 'Polipropileno Precio');
        $sheet->setCellValue('AZ1', 'Total Kilos');
        $sheet->setCellValue('BA1', 'Archivos Adjuntos');



        $sheet->getStyle('A1:BA1')->getFont()->setBold(true);

        // Agregar datos
        $row = 2;
        $contador = 1;
        foreach ($usuarios as $usuario) {
            // Obtener la lectura anterior usando una subconsulta o query adecuada

            $sheet->setCellValue('A' . $row, $contador);
            $sheet->setCellValue('B' . $row, $usuario->asociacion->name);
            $sheet->setCellValue('C' . $row, $usuario->mes);
            $sheet->setCellValue('D' . $row, $usuario->anio);
            $sheet->setCellValue('E' . $row, $usuario->created_at);
            $sheet->setCellValue('F' . $row, $usuario->lugar);
            $sheet->setCellValue('G' . $row, $usuario->num_recicladores);
            $sheet->setCellValue('H' . $row, $usuario->carton_kilos);
            $sheet->setCellValue('I' . $row, $usuario->carton_precio);
            $sheet->setCellValue('J' . $row, $usuario->duplex_cubeta_kilos);
            $sheet->setCellValue('K' . $row, $usuario->duplex_cubeta_precio);
            $sheet->setCellValue('L' . $row, $usuario->papel_comercio_kilos);
            $sheet->setCellValue('M' . $row, $usuario->papel_comercio_precio);
            $sheet->setCellValue('N' . $row, $usuario->papel_bond_kilos);
            $sheet->setCellValue('O' . $row, $usuario->papel_bond_precio);
            $sheet->setCellValue('P' . $row, $usuario->papel_mixto_kilos);
            $sheet->setCellValue('Q' . $row, $usuario->papel_mixto_precio);
            $sheet->setCellValue('R' . $row, $usuario->papel_multicolor_kilos);
            $sheet->setCellValue('S' . $row, $usuario->papel_multicolor_precio);
            $sheet->setCellValue('T' . $row, $usuario->tetrapak_kilos);
            $sheet->setCellValue('U' . $row, $usuario->tetrapak_precio);
            $sheet->setCellValue('V' . $row, $usuario->plastico_soplado_kilos);
            $sheet->setCellValue('W' . $row, $usuario->plastico_soplado_precio);
            $sheet->setCellValue('X' . $row, $usuario->plastico_duro_kilos);
            $sheet->setCellValue('Y' . $row, $usuario->plastico_duro_precio);
            $sheet->setCellValue('Z' . $row, $usuario->plastico_fino_kilos);
            $sheet->setCellValue('AA' . $row, $usuario->plastico_fino_precio);
            $sheet->setCellValue('AB' . $row, $usuario->pet_kilos);
            $sheet->setCellValue('AC' . $row, $usuario->pet_precio);
            $sheet->setCellValue('AD' . $row, $usuario->vidrio_kilos);
            $sheet->setCellValue('AE' . $row, $usuario->vidrio_precio);
            $sheet->setCellValue('AF' . $row, $usuario->chatarra_kilos);
            $sheet->setCellValue('AG' . $row, $usuario->chatarra_precio);
            $sheet->setCellValue('AH' . $row, $usuario->bronce_kilos);
            $sheet->setCellValue('AI' . $row, $usuario->bronce_precio);
            $sheet->setCellValue('AJ' . $row, $usuario->cobre_kilos);
            $sheet->setCellValue('AK' . $row, $usuario->cobre_precio);
            $sheet->setCellValue('AL' . $row, $usuario->aluminio_kilos);
            $sheet->setCellValue('AM' . $row, $usuario->aluminio_precio);
            $sheet->setCellValue('AN' . $row, $usuario->pvc_kilos);
            $sheet->setCellValue('AO' . $row, $usuario->pvc_precio);
            $sheet->setCellValue('AP' . $row, $usuario->baterias_kilos);
            $sheet->setCellValue('AQ' . $row, $usuario->baterias_precio);
            $sheet->setCellValue('AR' . $row, $usuario->lona_kilos);
            $sheet->setCellValue('AS' . $row, $usuario->lona_precio);
            $sheet->setCellValue('AT' . $row, $usuario->caucho_kilos);
            $sheet->setCellValue('AU' . $row, $usuario->caucho_precio);
            $sheet->setCellValue('AV' . $row, $usuario->fomix_kilos);
            $sheet->setCellValue('AW' . $row, $usuario->fomix_precio);
            $sheet->setCellValue('AX' . $row, $usuario->polipropileno_kilos);
            $sheet->setCellValue('AY' . $row, $usuario->polipropileno_precio);
            $sheet->setCellValue('AZ' . $row, $usuario->total_kilos);

            //RECORER ARCHIVOS ADJUNTOS
            $archivos = [];
            if ($usuario->archivos_adjuntos) {
                $archivos_array = json_decode($usuario->archivos_adjuntos, true);
                if (is_array($archivos_array)) {
                    foreach ($archivos_array as $archivo) {
                        $archivos[] = url('storage/' . ltrim($archivo, '/'));
                    }
                }
            }
            $sheet->setCellValue('BA' . $row, implode("\n", $archivos));



            $row++;
            $contador++;
        }
        foreach (range('A', 'H') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        // Preparar archivo para descargar
        $filename = 'registro_' . date('Y-m-d') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->stream(
            function () use ($writer) {
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }
}
