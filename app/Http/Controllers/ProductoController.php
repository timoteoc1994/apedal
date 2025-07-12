<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Canje;
use App\Models\User;
use App\Models\City;
use App\Models\AuthUser;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ProductoController extends Controller
{
    function index(Request $request)
    {
        $ciudades = City::all();

        // Query base para tiendas
        $query = User::whereHas('roles', function ($q) {
            $q->where('name', 'Tienda');
        });

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // Ordenamiento
        $sort = $request->get('sort', 'name');
        $direction = $request->get('direction', 'asc');

        $allowedSorts = ['name', 'email', 'ciudad', 'created_at'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $direction);
        }

        // Paginación
        $user_tiendas = $query->paginate(10)->withQueryString();

        return inertia('Productos/Index', [
            'ciudades' => $ciudades,
            'user_tiendas' => $user_tiendas
        ]);
    }

    function show(Request $request, $id)
    {

        $tienda = User::findOrFail($id);
        $productos = Producto::where('user_id', $id)->paginate(12);

        return inertia('Productos/Productos', [
            'tienda' => $tienda,
            'productos' => $productos
        ]);
    }

    function store(Request $request)
    {
       
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'ciudad' => 'required',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/'
            ],
            'logo_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB = 5120KB
        ], [
            'email.unique' => 'El correo electrónico ya está en uso.',
            'password.regex' => 'La contraseña debe contener al menos una letra mayúscula, una letra minúscula, un número y un carácter especial.',
            'logo_url.image' => 'El logo debe ser una imagen válida.',
            'logo_url.mimes' => 'El logo debe ser una imagen (jpeg, png, jpg, gif).',
            'logo_url.max' => 'El logo no puede ser mayor a 5MB.',
        ]);

        //procesar la imagen primero
        $imagePath = null;
        if ($request->hasFile('logo_url')) {
            $file = $request->file('logo_url');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            // Guardar la imagen y obtener la ruta
            $imagePath = $file->storeAs('imagenes_marcas', $filename, 'public');
        }

        //crear usuario
        $usuario = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'ciudad' => $request->ciudad,
            'password' => bcrypt($request->password),
            'logo_url' => $imagePath, // Usar la ruta procesada
        ]);
        $usuario->assignRole('Tienda');
        return redirect()->route('producto.index')->with('success', 'Tienda creada exitosamente.');
    }

    function update(Request $request, $id)
    {
        
        $usuario = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'ciudad' => 'required',
            'password' => [
                'nullable', // Opcional para edición
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/'
            ],
            'logo_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB = 5120KB
        ], [
            'email.unique' => 'El correo electrónico ya está en uso.',
            'password.regex' => 'La contraseña debe contener al menos una letra mayúscula, una letra minúscula, un número y un carácter especial.',
            'logo_url.image' => 'El logo debe ser una imagen válida.',
            'logo_url.mimes' => 'El logo debe ser una imagen (jpeg, png, jpg, gif).',
            'logo_url.max' => 'El logo no puede ser mayor a 5MB.',
        ]);

        // Procesar logo si se proporciona
        if ($request->hasFile('logo_url')) {
            // Eliminar logo anterior si existe
            if ($usuario->logo_url) {
                $oldLogoPath = storage_path('app/public/' . $usuario->logo_url);
                if (file_exists($oldLogoPath)) {
                    unlink($oldLogoPath);
                }
            }
            
            $file = $request->file('logo_url');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $imagePath = $file->storeAs('imagenes_marcas', $filename, 'public');
            $usuario->logo_url = $imagePath;
        }

        // Actualizar datos
        $usuario->name = $request->name;
        $usuario->email = $request->email;
        $usuario->ciudad = $request->ciudad;

        // Solo actualizar contraseña si se proporciona
        if ($request->filled('password')) {
            $usuario->password = bcrypt($request->password);
        }

        $usuario->save();

        return redirect()->route('producto.index')->with('success', 'Tienda actualizada exitosamente.');
    }
    public function actualizartienda(Request $request, $producto_id)
    {
       
        $producto = Producto::findOrFail($producto_id);

        $rules = [
            'tipo_producto' => 'required|string|max:255',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string|max:1000',
            'direccion_reclamo' => 'required|string|max:255',
            'categoria' => 'required|string|max:255',
            'puntos' => 'required|integer|min:0',
            'estado' => 'required|in:publicado,borrador,pausado',
            'url_imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Opcional en edición
        ];

        $request->validate($rules);

        // Actualizar campos básicos
        $producto->tipo_producto = $request->tipo_producto;
        $producto->nombre = $request->nombre;
        $producto->descripcion = $request->descripcion;
        $producto->direccion_reclamo = $request->direccion_reclamo;
        $producto->categoria = $request->categoria;
        $producto->puntos = $request->puntos;
        $producto->estado = $request->estado;

        // Manejar imagen solo si se subió una nueva
        if ($request->hasFile('url_imagen')) {
            // Eliminar imagen anterior si existe
            if ($producto->url_imagen) {
                $imagePath = public_path('storage/' . $producto->url_imagen);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            // Subir nueva imagen
            $imagen = $request->file('url_imagen');
            $nombreImagen = time() . '_' . uniqid() . '.' . $imagen->getClientOriginalExtension();
            $imagen->move(public_path('storage/imagenes_productos'), $nombreImagen);
            $producto->url_imagen = 'imagenes_productos/' . $nombreImagen;
        }
        // Si no hay archivo, mantener la imagen existente (no hacer nada)

        $producto->save();

        return redirect()->back()->with('success', 'Producto actualizado exitosamente.');
    }
    function destroytienda($id)
    {

        $producto = Producto::findOrFail($id);
        //eliminar tambien la imagen si existe
        if ($producto->url_imagen) {
            $imagePath = public_path('storage/' . $producto->url_imagen);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $producto->delete();

        //regresar para atras
        return redirect()->back()->with('success', 'Producto eliminado exitosamente.');
    }
    function destroy($id)
    {
        $usuario = User::findOrFail($id);

        //eliminar la imagen
        if ($usuario->logo_url) {
            $oldLogoPath = storage_path('app/public/' . $usuario->logo_url);
            if (file_exists($oldLogoPath)) {
                unlink($oldLogoPath);
            }
        }
        $usuario->delete();

        return redirect()->route('producto.index')->with('success', 'Tienda eliminada exitosamente.');
    }
    function guardar_tiendas(Request $request, $id)
    {
        $request->validate([
            'tipo_producto' => 'required|string|max:255',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string|max:1000',
            'categoria' => 'required|string|max:255',
            'puntos' => 'required|integer|min:0',
            'estado' => 'required|in:publicado,pendiente,eliminado',
            'direccion_reclamo' => 'required|string|max:255',
            'url_imagen' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Procesar la imagen PRIMERO
        $imagePath = null;
        if ($request->hasFile('url_imagen')) {
            $file = $request->file('url_imagen');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            // Guardar la imagen y obtener la ruta
            $imagePath = $file->storeAs('imagenes_productos', $filename, 'public');
        }

        // Crear el producto con la ruta correcta de la imagen
        $producto = Producto::create([
            'tipo_producto' => $request->tipo_producto,
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'categoria' => $request->categoria,
            'puntos' => $request->puntos,
            'estado' => $request->estado,
            'direccion_reclamo' => $request->direccion_reclamo,
            'url_imagen' => $imagePath, // Usar la ruta procesada
            'user_id' => $id,
        ]);

        if (!$producto) {
            return redirect()->back()->with('error', 'Error al crear el producto.');
        }

        return redirect()->route('producto.index.show', ['id' => $id])->with('success', 'Producto creado exitosamente.');
    }
}
