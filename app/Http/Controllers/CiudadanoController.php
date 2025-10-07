<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use App\Models\Ciudadano;
use App\Models\Asociacion;
use App\Models\City;
use App\Models\User;
use Illuminate\Support\Facades\Redirect;  // ← importa el facade
use App\Models\AuthUser;
use App\Models\SolicitudRecoleccion;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class CiudadanoController extends Controller
{
    /**
     * Muestra el listado de ciudadanos
     */
    // En el controlador CiudadanoController.php


    public function index(Request $request)
    {
        $sort = $request->input('sort', 'puntos'); // por defecto puntos
        $direction = $request->input('direction', 'desc'); // por defecto descendente

        $allowedSorts = [
            'name' => 'ciudadanos.name',
            'puntos' => 'auth_users.puntos',
            'telefono' => 'ciudadanos.telefono',
            'ciudad' => 'ciudadanos.ciudad',
        ];
        $sortColumn = $allowedSorts[$sort] ?? 'auth_users.puntos';

        $query = Ciudadano::query()
            ->join('auth_users', function ($join) {
                $join->on('auth_users.profile_id', '=', 'ciudadanos.id')
                    ->where('auth_users.role', 'ciudadano');
            })
            ->select('ciudadanos.*', 'auth_users.puntos')
            ->orderBy($sortColumn, $direction);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('ciudadanos.name', 'LIKE', '%' . $search . '%');
        }

        $ciudadanos = $query->paginate(10)->withQueryString();

        return Inertia::render('Ciudadano/index', [
            'ciudadanos' => $ciudadanos,
            'filters' => [
                'search' => $request->input('search', ''),
                'sort' => $sort,
                'direction' => $direction,
            ],
        ]);
    }

    public function perfil_ciudadano(Request $request)
    {
        $ciudadano = Ciudadano::with('authUser')->find($request->id);

        // Obtener filtros
        $estado = $request->input('estado');
        $fecha_desde = $request->input('fecha_desde');
        $fecha_hasta = $request->input('fecha_hasta');

        // Query base para solicitudes
        $solicitudesQuery = SolicitudRecoleccion::where('user_id', $ciudadano->authUser->id);

        // Aplicar filtros si existen
        if ($estado && $estado !== 'todos') {
            $solicitudesQuery->where('estado', $estado);
        }

        if ($fecha_desde) {
            $solicitudesQuery->whereDate('created_at', '>=', $fecha_desde);
        }

        if ($fecha_hasta) {
            $solicitudesQuery->whereDate('created_at', '<=', $fecha_hasta);
        }

        // Calcular estadisticas de las solicitudes (sin filtros para tener totales generales)
        $estadisticas = array();
        $estadisticas['total_solicitudes'] = SolicitudRecoleccion::where('user_id', $ciudadano->authUser->id)->count();
        $estadisticas['completadas'] = SolicitudRecoleccion::where('user_id', $ciudadano->authUser->id)->where('estado', 'completado')->count();
        $estadisticas['pendientes'] = SolicitudRecoleccion::where('user_id', $ciudadano->authUser->id)->where('estado', 'pendiente')->count();
        $estadisticas['asignadas'] = SolicitudRecoleccion::where('user_id', $ciudadano->authUser->id)->where('estado', 'asignado')->count();
        $estadisticas['en_camino'] = SolicitudRecoleccion::where('user_id', $ciudadano->authUser->id)->where('estado', 'en_camino')->count();
        $estadisticas['buscando_reciclador'] = SolicitudRecoleccion::where('user_id', $ciudadano->authUser->id)->where('estado', 'buscando_reciclador')->count();
        $estadisticas['cancelado'] = SolicitudRecoleccion::where('user_id', $ciudadano->authUser->id)->where('estado', 'cancelado')->count();

        $total_recogido=SolicitudRecoleccion::where('user_id', $ciudadano->authUser->id)->where('estado', 'completado')->sum('peso_total_revisado');

        // Calcular total de kg reciclados (solo solicitudes completadas)
        $kgReciclados = SolicitudRecoleccion::where('user_id', $ciudadano->authUser->id)
            ->where('estado', 'completado')
            ->whereNotNull('peso_total_revisado')
            ->sum('peso_total_revisado');

        // Si no hay peso_total_revisado, usar peso_total
        if ($kgReciclados == 0) {
            $kgReciclados = SolicitudRecoleccion::where('user_id', $ciudadano->authUser->id)
                ->where('estado', 'completado')
                ->whereNotNull('peso_total')
                ->sum('peso_total');
        }

        $estadisticas['kg_reciclados'] = round($kgReciclados, 2);

        // Buscar solicitudes filtradas y paginadas (10 por página)
        $solicitudes = $solicitudesQuery
            ->with(['materiales', 'authUser.reciclador'])
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString(); // Mantener parámetros de filtros en paginación
            
        return Inertia::render('Ciudadano/perfil_ciudadano', [
            'ciudadano' => $ciudadano,
            'estadisticas' => $estadisticas,
            'solicitudes' => $solicitudes,
            'total_recogido' => $total_recogido,
            'filtros' => [
                'estado' => $estado,
                'fecha_desde' => $fecha_desde,
                'fecha_hasta' => $fecha_hasta,
            ],
        ]);
    }


    /**
     * Muestra el formulario de creación
     */
    public function create()
    {
        $cities = City::orderBy('name')->get();

        return Inertia::render('Ciudadano/Create', [
            'cities' => $cities->isEmpty() ? null : $cities  // Pasar las ciudades al frontend
        ]);
    }

    /**
     * Almacena un nuevo ciudadano
     */
    public function createCiudadano(Request $request)
    {
        try {
            // Validación de los datos recibidos
            $data = $request->validate([
                'name' => 'required|string',
                'telefono' => 'nullable|string',
                'direccion' => 'required|string',
                'ciudad' => 'required|string|exists:cities,name', // Asegúrate de que la ciudad exista
                'logo_url' => 'nullable|string',
                'email' => 'required|email|unique:users,email', // Validación del email único
                'password' => 'required|string|min:8', // Validación de la contraseña
                'role' => 'required|in:ciudadano', // El role siempre debe ser 'ciudadano'
            ]);

            // Crear el ciudadano
            $ciudadano = Ciudadano::create([
                'name' => $data['name'],
                'telefono' => $data['telefono'] ?? null,
                'direccion' => $data['direccion'],
                'ciudad' => $data['ciudad'],
                'logo_url' => $data['logo_url'] ?? null,
            ]);

            // Crear el usuario asociado al ciudadano
            $authUser = AuthUser::create([
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'ciudadano', // Role siempre será 'ciudadano'
                'profile_id' => $ciudadano->id,  // Vinculamos el profile_id con el ID del ciudadano
            ]);

            // Redirigir con mensaje de éxito
            return Redirect::route('ciudadano.index')
                ->with('message', 'Ciudadano y usuario creados exitosamente');
        } catch (ValidationException $e) {
            // En caso de error de validación
            return back()->withErrors($e->errors());
        } catch (\Exception $e) {
            // En caso de error general
            \Log::error('Error al crear ciudadano y usuario: ' . $e->getMessage());
            return back()->with('errorMessage', 'Error al crear ciudadano y usuario');
        }
    }



    // Método para mostrar el formulario de edición con los datos del ciudadano
    public function edit($id)
    {
        $ciudadano = Ciudadano::with('authUser')->findOrFail($id); // Buscar el ciudadano por ID
        $cities = City::orderBy('name')->get(); // Obtener todas las ciudades

        // Renderizar la vista de edición con Inertia
        return Inertia::render('Ciudadano/Edit', [
            'ciudadano' => $ciudadano, // Pasar el ciudadano a la vista
            'cities' => $cities->isEmpty() ? null : $cities // Pasar las ciudades al frontend
        ]);
    }

    // Método para actualizar el ciudadano
    public function update(Request $request, $id)
    {

        try {
            $data = $request->validate(
                [
                    'name' => 'required|string',
                    'telefono' => 'required|string',
                    'nickname' => 'required|string',
                    'email' => 'required|email|unique:auth_users,email,' . $request->id_usuario, // Validación del email único, excepto el actual
                    'email_verified_at' => 'nullable|date',
                    'password' => [
                        'nullable',
                        'string',
                        'min:8',
                        'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/'
                    ], // Solo se requiere si se cambia
                    'ciudad' => 'required|string|exists:cities,name',
                ],
                [
                    'email.unique' => 'El correo electrónico ya está en uso.',
                    'password.regex' => 'La contraseña debe contener al menos una letra mayúscula, una letra minúscula, un número y un carácter especial.',
                ]
            );

            $ciudadano = Ciudadano::findOrFail($id);
            $ciudadano->update([
                'name' => $data['name'],
                'telefono' => $data['telefono'],
                'nickname' => $data['nickname'],
                'ciudad' => $data['ciudad'],
            ]);
            $authUser = AuthUser::findOrFail($request->id_usuario);
            $authUser->email = $data['email'];
            if (!empty($data['password'])) {
                $authUser->password = Hash::make($data['password']);
            }
            $authUser->email_verified_at = $data['email_verified_at'] ?? null;
            $authUser->save();

            return redirect()->route('ciudadano.index', $id)->with('successMessage', 'Ciudadano actualizado exitosamente');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Exception $e) {

            return back()->with('errorMessage', 'Error al actualizar ciudadano');
        }
    }

    public function deleteCiudadano($id)
    {
        try {
            $ciudadano = Ciudadano::findOrFail($id);
            $authUser = AuthUser::where('profile_id', $ciudadano->id)->where('role', 'ciudadano')->first();
            if ($authUser) {
                $authUser->delete();
            }
            $ciudadano->delete();

            return response()->json([
                'success' => true,
                'message' => 'Ciudadano eliminado correctamente.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al eliminar ciudadano: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar ciudadano',
                'error' => $e->getMessage()
            ], 500);
        }
    }





    /**
     * Obtener todas las solicitudes del ciudadano autenticado
     */
    public function getSolicitudes(Request $request)
    {
        try {
            $user = $request->user();
            $ciudadano = Ciudadano::find($user->profile_id);

            if (!$ciudadano) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ciudadano no encontrado'
                ], 404);
            }

            $solicitudes = Solicitud::with(['asociacion:id,name,number_phone', 'reciclador:id,name,telefono'])
                ->where('ciudadano_id', $ciudadano->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Solicitudes obtenidas correctamente',
                'data' => $solicitudes
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener solicitudes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener solicitudes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener una solicitud específica
     */
    public function getSolicitud(Request $request, $id)
    {
        try {
            $user = $request->user();
            $ciudadano = Ciudadano::find($user->profile_id);

            if (!$ciudadano) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ciudadano no encontrado'
                ], 404);
            }

            $solicitud = Solicitud::with(['asociacion:id,name,number_phone', 'reciclador:id,name,telefono'])
                ->where('ciudadano_id', $ciudadano->id)
                ->where('id', $id)
                ->first();

            if (!$solicitud) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solicitud no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Solicitud obtenida correctamente',
                'data' => $solicitud
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener solicitud: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener solicitud',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear una nueva solicitud de recolección
     */
    public function createSolicitud(Request $request)
    {
        try {
            $user = $request->user();
            $ciudadano = Ciudadano::find($user->profile_id);

            if (!$ciudadano) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ciudadano no encontrado'
                ], 404);
            }

            $data = $request->validate([
                'direccion' => 'required|string',
                'ciudad' => 'required|string',
                'referencias' => 'nullable|string',
                'materiales' => 'required|string',
                'comentarios' => 'nullable|string',
                'fecha_solicitud' => 'required|date',
                'asociacion_id' => 'required|exists:asociaciones,id',
            ]);

            $asociacion = Asociacion::find($data['asociacion_id']);
            if (!$asociacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'La asociación seleccionada no existe'
                ], 404);
            }

            $solicitud = new Solicitud([
                'ciudadano_id' => $ciudadano->id,
                'asociacion_id' => $data['asociacion_id'],
                'direccion' => $data['direccion'],
                'ciudad' => $data['ciudad'],
                'referencias' => $data['referencias'] ?? null,
                'materiales' => $data['materiales'],
                'comentarios' => $data['comentarios'] ?? null,
                'fecha_solicitud' => $data['fecha_solicitud'],
                'status' => 'pendiente',
            ]);

            $solicitud->save();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud creada correctamente',
                'data' => $solicitud
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al crear solicitud: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear solicitud',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancelar una solicitud
     */
    public function cancelSolicitud(Request $request, $id)
    {
        try {
            $user = $request->user();
            $ciudadano = Ciudadano::find($user->profile_id);

            if (!$ciudadano) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ciudadano no encontrado'
                ], 404);
            }

            $solicitud = Solicitud::where('ciudadano_id', $ciudadano->id)
                ->where('id', $id)
                ->first();

            if (!$solicitud) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solicitud no encontrada'
                ], 404);
            }

            if ($solicitud->status === 'completada') {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede cancelar una solicitud completada'
                ], 400);
            }

            $solicitud->status = 'cancelada';
            $solicitud->save();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud cancelada correctamente',
                'data' => $solicitud
            ]);
        } catch (\Exception $e) {
            Log::error('Error al cancelar solicitud: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar solicitud',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener todas las asociaciones disponibles
     */
    public function getAsociaciones()
    {
        try {
            $asociaciones = Asociacion::where('verified', true)
                ->select('id', 'name', 'number_phone', 'city')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Asociaciones obtenidas correctamente',
                'data' => $asociaciones
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener asociaciones: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener asociaciones',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar reporte PDF del ciudadano
     */
    public function generarReportePDF(Request $request, $id)
    {
        //dd($request->all());
        try {
            $ciudadano = Ciudadano::with('authUser')->findOrFail($id);

            // Obtener filtros del request
            $estado = $request->input('estado');
            $fecha_desde = $request->input('fecha_desde');
            $fecha_hasta = $request->input('fecha_hasta');


            // Query base para solicitudes
            $solicitudesQuery = SolicitudRecoleccion::where('user_id', $ciudadano->authUser->id);

            if ($fecha_desde) {
                $solicitudesQuery->whereDate('fecha', '>=', $fecha_desde);
            }

            if ($fecha_hasta) {
                $solicitudesQuery->whereDate('fecha', '<=', $fecha_hasta);
            }

            // Obtener solicitudes con materiales
            $solicitudes = $solicitudesQuery
                ->with(['materiales'])
                ->get();

            // Calcular estadísticas
            $estadisticas = [
                'total_solicitudes' => $solicitudes->count(),
                'completadas' => $solicitudes->where('estado', 'completado')->count(),
                'pendientes' => $solicitudes->where('estado', 'pendiente')->count(),
                'asignadas' => $solicitudes->where('estado', 'asignado')->count(),
                'en_camino' => $solicitudes->where('estado', 'en_camino')->count(),
                'buscando_reciclador' => $solicitudes->where('estado', 'buscando_reciclador')->count(),
                'canceladas' => $solicitudes->where('estado', 'cancelado')->count(),
            ];

            // Calcular kg reciclados total (solo completadas)
            $solicitudesCompletadas = $solicitudes->where('estado', 'completado');
            $kgReciclados = $solicitudesCompletadas->sum(function ($solicitud) {
                return $solicitud->peso_total_revisado;
            });

            // Calcular materiales por tipo (solo solicitudes completadas)
            $materialesPorTipo = [
                'papel' => 0,
                'tetrapak' => 0,
                'botellasPET' => 0,
                'plasticosSuaves' => 0,
                'plasticosSoplado' => 0,
                'plasticosRigidos' => 0,
                'vidrio' => 0,
                'pilas' => 0,
                'latas' => 0,
                'metales' => 0,
                'electrodomesticos' => 0,
                'electronicos' => 0,
            ];

            // Debug: Ver qué tipos de materiales existen exactamente
            $tiposUnicos = [];
            foreach ($solicitudesCompletadas as $solicitud) {
                foreach ($solicitud->materiales as $material) {
                    $tiposUnicos[] = $material->tipo;
                }
            }
            
            Log::info('Tipos de materiales encontrados:', [
                'ciudadano_id' => $ciudadano->id,
                'tipos_unicos' => array_unique($tiposUnicos),
                'solicitudes_completadas' => $solicitudesCompletadas->count()
            ]);

            // Sumar directamente por tipo exacto
            foreach ($solicitudesCompletadas as $solicitud) {
                foreach ($solicitud->materiales as $material) {
                    $peso = $material->peso_revisado ?? $material->peso ?? 0;
                    $tipo = $material->tipo; // Sin convertir a minúsculas primero
                    
                    Log::info('Procesando material:', [
                        'solicitud_id' => $solicitud->id,
                        'tipo_original' => $tipo,
                        'peso_revisado' => $material->peso_revisado,
                        'peso' => $material->peso,
                        'peso_final' => $peso
                    ]);
                    
                    // Mapear directamente por los tipos que tienes en tu array
                    if (isset($materialesPorTipo[$tipo])) {
                        $materialesPorTipo[$tipo] += $peso;
                    } else {
                        Log::warning('Tipo de material no encontrado en array:', [
                            'tipo' => $tipo,
                            'tipos_disponibles' => array_keys($materialesPorTipo)
                        ]);
                    }
                }
            }

            // Preparar datos para el PDF
            $datosPDF = [
                'nombre' => $ciudadano->name,
                'email' => $ciudadano->authUser->email,
                'telefono' => $ciudadano->telefono,
                'ciudad' => $ciudadano->ciudad,
                'puntos_actuales' => $ciudadano->authUser->puntos ?? 0,
                'solicitudes_completadas' => $estadisticas['completadas'],
                'solicitudes_pendientes' => $estadisticas['pendientes'],
                'solicitudes_canceladas' => $estadisticas['canceladas'],
                'total_solicitudes' => $estadisticas['total_solicitudes'],
                'suma_peso_kg' => round($kgReciclados, 2),
                'papel' => round($materialesPorTipo['papel'], 2),
                'tetrapak' => round($materialesPorTipo['tetrapak'], 2),
                'botellasPET' => round($materialesPorTipo['botellasPET'], 2),
                'plasticosSuaves' => round($materialesPorTipo['plasticosSuaves'], 2),
                'plasticosSoplado' => round($materialesPorTipo['plasticosSoplado'], 2),
                'plasticosRigidos' => round($materialesPorTipo['plasticosRigidos'], 2),
                'vidrio' => round($materialesPorTipo['vidrio'], 2),
                'pilas' => round($materialesPorTipo['pilas'], 2),
                'latas' => round($materialesPorTipo['latas'], 2),
                'metales' => round($materialesPorTipo['metales'], 2),
                'electrodomesticos' => round($materialesPorTipo['electrodomesticos'], 2),
                'eletronicos' => round($materialesPorTipo['electronicos'], 2),
                'filtros' => [
                    'estado' => $estado,
                    'fecha_desde' => $fecha_desde,
                    'fecha_hasta' => $fecha_hasta,
                ]
            ];

            // Generar PDF
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.reporte_ciudadano', $datosPDF);
            $pdf->setPaper('A4', 'portrait');

            $nombreArchivo = 'reporte_ciudadano_' . $ciudadano->name . '_' . date('Y-m-d') . '.pdf';
            
            return $pdf->download($nombreArchivo);

        } catch (\Exception $e) {
            Log::error('Error al generar reporte PDF del ciudadano: ' . $e->getMessage());
            return back()->with('errorMessage', 'Error al generar el reporte PDF');
        }
    }
}
