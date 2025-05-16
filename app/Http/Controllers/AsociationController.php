<?php

namespace App\Http\Controllers;

use App\Models\asociation;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

class AsociationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'number_phone' => 'required|string',
                'city' => 'required|string|max:255',
            ]);

            $asociacion = Asociation::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => bcrypt($validatedData['password']),
                'number_phone' => $validatedData['number_phone'],
                'city' => $validatedData['city'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Asociación creada correctamente',
                'data' => $asociacion
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (QueryException $e) {
            if ($e->getCode() == 23505) { // Código de error de clave única en PostgreSQL
                return response()->json([
                    'success' => false,
                    'message' => 'El correo electrónico ya está registrado.'
                ], 400);
            }
            return response()->json([
                'success' => false,
                'message' => 'Error en la base de datos',
                'error' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error inesperado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function city()
    {
        //responder a la api con todas la city encontradas y solo enviar 2 datos id y name
        $cities = City::all(['id', 'name']);
        return response()->json([
            'success' => true,
            'message' => 'Ciudades encontradas',
            'data' => $cities
        ], 200);
    }

    public function login(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            // Verificar las credenciales del usuario
            $asociacion = Asociation::where('email', $validatedData['email'])->first();

            if (!$asociacion || !Hash::check($validatedData['password'], $asociacion->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credenciales incorrectas'
                ], 401);
            }

            // Generar token de acceso
            $token = $asociacion->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Inicio de sesión exitoso',
                'data' => [
                    'asociacion' => $asociacion,
                    'token' => $token
                ]
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error inesperado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(asociation $asociation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(asociation $asociation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, asociation $asociation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
}
