<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asociacion;
use App\Models\AuthUser;
use App\Models\Ciudadano;
use App\Models\Reciclador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EliminarCuenta extends Controller
{
    /**
     * Eliminar cuenta del usuario autenticado
     */
    public function eliminarCuenta(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            DB::beginTransaction();
            $auth_user=AuthUser::where('id', $user->id)->first();
            if($auth_user->role=='asociacion'){
                $asociacion = Asociacion::where('id', $auth_user->profile_id)->first();
                if ($asociacion) {
                    $asociacion->delete();
                    $auth_user->delete();
                }
            }elseif($auth_user->role=='reciclador'){
                $reciclador = Reciclador::where('id', $auth_user->profile_id)->first();
                if ($reciclador) {
                    $reciclador->delete();
                    $auth_user->delete();
                }
            }elseif($auth_user->role=='ciudadano'){
                $ciudadano = Ciudadano::where('id', $auth_user->profile_id)->first();
                if ($ciudadano) {
                    $ciudadano->delete();
                    $auth_user->delete();
                }
            }


            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cuenta eliminada exitosamente',
                'data' => null
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'errors' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

   
}