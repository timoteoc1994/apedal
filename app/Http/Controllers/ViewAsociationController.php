<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Asociacion;
use App\Models\AuthUser;
use App\Models\City;
use App\Models\Reciclador;
use App\Models\SolicitudRecoleccion;
use App\Models\Zona;

class ViewAsociationController extends Controller
{
    public function index(Request $request)
{
    $search = $request->search;
    $sort = $request->get('sort', 'name'); // columna por defecto
    $direction = $request->get('direction', 'asc'); // dirección por defecto

    $asociations = AuthUser::where('role', 'asociacion')
        ->when($search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'LIKE', "%{$search}%")
                  ->orWhereHas('asociacion', function ($q2) use ($search) {
                      $q2->where('name', 'LIKE', "%{$search}%")
                         ->orWhere('email', 'LIKE', "%{$search}%");
                  });
            });
        })
        ->with('asociacion');

    // Ordenamiento
    if (in_array($sort, ['email', 'created_at'])) {
        $asociations = $asociations->orderBy($sort, $direction);
    } elseif ($sort === 'name') {
        // Ordenar por nombre de la asociación (relación)
        $asociations = $asociations->join('asociaciones', 'auth_users.profile_id', '=', 'asociaciones.id')
            ->orderBy('asociaciones.name', $direction)
            ->select('auth_users.*');
    }

    $asociations = $asociations->paginate(10);

    foreach ($asociations as $asociation) {
        $asociation->numero_recicladores = Reciclador::where('asociacion_id', $asociation->profile_id)->count();
    }

    return Inertia::render('asociation/index', [
        'Asociations' => $asociations,
        'filters' => $request->only(['search', 'sort', 'direction'])
    ]);
}
    public function show(Request $request)
    {

        //obtenemos todas las ciudades disponibles
        $ciudades = City::all(['id', 'name']);
        $asociation = AuthUser::where('role', 'asociacion')
            ->with('asociacion')
            ->find($request->id);
        return Inertia::render('asociation/show', [
            'asociation' => $asociation,
            'ciudades' => $ciudades,
        ]);
    }
    public function showrecicladores(Request $request)
    {
      
        //obtenemos todas las ciudades disponibles
        $ciudades = City::all(['id', 'name']);
        $reciclador = AuthUser::where('role', 'reciclador')
            ->with('reciclador')
            ->find($request->id);
        $nombreAsociacion = AuthUser::where('role', 'asociacion')
            ->where('profile_id', $reciclador->reciclador->asociacion_id)
            ->with('asociacion')
            ->first();
        return Inertia::render('asociation/show_recicladores', [
            'reciclador' => $reciclador,
            'ciudades' => $ciudades,
            'nombreAsociacion' => $nombreAsociacion,
        ]);
    }
    public function perfil(Request $request)
    {

        //obtenemos el perfil de la asociacion
        $asociacion = AuthUser::where('role', 'asociacion')
            ->where('profile_id', $request->id)
            ->with('asociacion')
            ->first();
        //obtenemos el numero de recicladores asociados a la asociacion
        $asociacion->numero_recicladores = Reciclador::where('asociacion_id', $asociacion->profile_id)->count();

        return Inertia::render('asociation/perfil', [
            'asociation' => $asociacion,
        ]);
    }
     public function recicladoresperfil($id)
    {
        // obtenemos el perfil del reciclador
        $reciclador = Reciclador::where('id', $id)
            ->with('authUser')
            ->first();
        $asociacion= AuthUser::where('role', 'asociacion')
            ->where('profile_id', $reciclador->asociacion_id)
            ->with('asociacion')
            ->first();
        // Buscar solicitudes de reciclador paginadas (10 por página)
        $solicitudes = SolicitudRecoleccion::where('reciclador_id', $reciclador->authUser->id)
            ->with(['materiales', 'authUser.ciudadano'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        //calcular estadisticas de las solicitudes
        $estadisticas=array();
        $estadisticas['total_solicitudes'] = SolicitudRecoleccion::where('reciclador_id', $reciclador->authUser->id)->count();
        $estadisticas['completadas'] = SolicitudRecoleccion::where('reciclador_id', $reciclador->authUser->id)->where('estado', 'completado')->count();
        $estadisticas['pendientes'] = SolicitudRecoleccion::where('reciclador_id', $reciclador->authUser->id)->where('estado', 'pendiente')->count();
        $estadisticas['asignadas'] = SolicitudRecoleccion::where('reciclador_id', $reciclador->authUser->id)->where('estado', 'asignado')->count();
        $estadisticas['en_camino'] = SolicitudRecoleccion::where('reciclador_id', $reciclador->authUser->id)->where('estado', 'en_camino')->count();
        $estadisticas['buscando_reciclador'] = SolicitudRecoleccion::where('reciclador_id', $reciclador->authUser->id)->where('estado', 'buscando_reciclador')->count();
        $estadisticas['cancelado'] = SolicitudRecoleccion::where('reciclador_id', $reciclador->authUser->id)->where('estado', 'cancelado')->count();

        return Inertia::render('asociation/perfil_reciclador', [
            'reciclador' => $reciclador,
            'asociacion' => $asociacion,
            'solicitudes' => $solicitudes,
            'estadisticas' => $estadisticas,
        ]);
    }

    

    public function recicladores(Request $request)
{
    $query = Reciclador::where('asociacion_id', $request->id)
        ->with('authUser');

    // Filtro de búsqueda
    if ($request->filled('search')) {
        $search = $request->input('search');
        $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%$search%")
              ->orWhere('telefono', 'like', "%$search%")
              ->orWhere('ciudad', 'like', "%$search%");
        });
    }

    // Ordenamiento
    $sort = $request->input('sort', 'name');
    $direction = $request->input('direction', 'asc');
    $query->orderBy($sort, $direction);

    $recicladores = $query->paginate(10)->withQueryString();

    $nombreAsociacion = AuthUser::where('role', 'asociacion')
        ->where('profile_id', $request->id)
        ->with('asociacion')
        ->first();

    return Inertia::render('asociation/recicladores', [
        'recicladores' => $recicladores,
        'nombreAsociacion' => $nombreAsociacion,
        'filters' => [
            'search' => $request->input('search', ''),
            'sort' => $sort,
            'direction' => $direction,
        ],
    ]);
}

    /**
     * Show the form for editing the specified resource.
     */


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
 
        //validar los datos 
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:auth_users,email,' . $request->id, 
            'number_phone' => 'required',
            'city' => 'required',
            'color' => 'required',
            'estado' => 'required',
            'descripcion' => 'nullable|string',
            'password' => [
                'nullable',
                'string',
                'min:8',
                        'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/'
                    ], // Asegurarse de que la contraseña sea opcional y tenga al menos 8 caracteres
        ],   [
                    'email.unique' => 'El correo electrónico ya está en uso.',
                    'password.regex' => 'La contraseña debe contener al menos una letra mayúscula, una letra minúscula, un número y un carácter especial.',
                ]);

        //actualizar usuario con los datos cal $request->id solo el campo email a AuthUser
        $usuario = AuthUser::findOrFail($request->id);
        $usuario->update([
            'email' => $request->email,
        ]);

        //actualizar asociaciones con los demas datos menos el email con el $request->id_asociacion
        $asociacion = Asociacion::findOrFail($request->id_asociacion);

        // Convertir el valor de estado a 1 o 0
        $estadoValue = ($request->estado == true) ? 1 : 0;
        //verificar si el estado es true verificar que si la asocacion tiene zonas

        if ($estadoValue == 1) {

            $zonas=Zona::where('asociacion_id', $asociacion->id)
                ->count();
                
            if ($zonas == 0) {
                
                return redirect()->back()->with('error', 'La asociación debe tener al menos una zona activa para ser verificada.');
            }

        }

        $asociacion->update([
            'name' => $request->name,
            'number_phone' => $request->number_phone,
            'city' => $request->city,
            'color' => $request->color,
            'verified' => $estadoValue,
            'descripcion' => $request->descripcion,
        ]);

        return redirect()->back()->with('success', 'Actualización completada con éxito');
    }

    public function updaterecicladores(Request $request)
    {
        //validar los datos 
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:auth_users,email,' . $request->id, 
            'telefono' => 'required',
            'ciudad' => 'required',
            'estado' => 'required',
            'password' => [
                'nullable',
                'string',
                'min:8',
                        'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/'
                    ], // Asegurarse de que la contraseña sea opcional y tenga al menos 8 caracteres
        ],   [
                    'email.unique' => 'El correo electrónico ya está en uso.',
                    'password.regex' => 'La contraseña debe contener al menos una letra mayúscula, una letra minúscula, un número y un carácter especial.',
                ]);

        //actualizar usuario con los datos cal $request->id solo el campo email a AuthUser y tambien si hay actualizar el password
        if ($request->filled('password')) {
            $validatedData['password'] = bcrypt($request->password);
        }
        
        $usuario = AuthUser::findOrFail($request->id);
        $usuario->update([
            'email' => $request->email,
            'password' => $validatedData['password'] ?? $usuario->password,
        ]);

        //actualizar recicladores con los demas datos menos el email con el $request->id_asociacion
        $reciclador = Reciclador::findOrFail($request->id_asociacion);

        $reciclador->update([
            'name' => $request->name,
            'number_phone' => $request->number_phone,
            'city' => $request->city,
            'color' => $request->color,
            'estado' => $request->estado, // Convertir el valor de estado a 1 o 0
        ]);

        return redirect()->back()->with('success', 'Actualización completada con éxito');
    }



    /**
     * Remove the specified resource from storage.
     */

        public function delete($dato)
        {
            // Verificar si tiene zonas asociadas
            $zonas = Zona::where('asociacion_id', $dato)->count();
            if ($zonas > 0) {
                return redirect()->back()->with('error', 'No se puede eliminar la asociación porque tiene zonas asociadas.');
            }

            $asociacion = Asociacion::findOrFail($dato); // Busca el registro o lanza un error 404
            $asociacion->delete(); // Elimina el registro

            // Eliminar también el AuthUser asociado
            $authUser = AuthUser::where('profile_id', $asociacion->id)
                ->where('role', 'asociacion')
                ->first();
            if ($authUser) {
                $authUser->delete(); // Elimina el usuario asociado
            }

            return redirect()->back()->with('success', 'Asociación eliminada correctamente.');
        }
          public function deletereciclador($dato)
        {
      
            $reciclador = Reciclador::findOrFail($dato); // Busca el registro o lanza un error 404
            $reciclador->delete(); // Elimina el registro

            // Eliminar también el AuthUser asociado
            $authUser = AuthUser::where('profile_id', $reciclador->id)
                ->where('role', 'reciclador')
                ->first();
            if ($authUser) {
                $authUser->delete(); // Elimina el usuario asociado
            }

            return redirect()->back()->with('success', 'Reciclador eliminado correctamente.');
        }
}
