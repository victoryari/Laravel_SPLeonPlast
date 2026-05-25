<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Trabajador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $rolFiltro = $request->input('rol');

        $query = Usuario::with('trabajador')->activos();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nombre_usuario', 'LIKE', "%$search%")
                  ->orWhere('email', 'LIKE', "%$search%")
                  ->orWhere('rol', 'LIKE', "%$search%");
            });
        }

        if ($rolFiltro) {
            $query->where('rol', $rolFiltro);
        }

        $usuarios = $query->orderBy('nombre_usuario', 'asc')->paginate(10);
        $usuarios->appends(['search' => $search, 'rol' => $rolFiltro]);

        return view('usuarios.index', compact('usuarios', 'search', 'rolFiltro'));
    }

    public function create()
    {
        $trabajadores = Trabajador::activos()->orderBy('nombre', 'asc')->get();
        $roles = \App\Models\Rol::orderBy('nombre')->pluck('nombre');
        
        return view('usuarios.create', compact('trabajadores', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo_trabajador' => 'required|exists:trabajador,codigo',
            'nombre_usuario' => 'required|string|max:50|unique:usuarios,nombre_usuario',
            'password' => 'required|string|min:6',
            'email' => 'nullable|email|max:100',
            'rol' => 'required|string|max:50',
        ], [
            'nombre_usuario.unique' => 'Este nombre de usuario ya está en uso.',
        ]);

        Usuario::create([
            'codigo_trabajador' => $request->codigo_trabajador,
            'nombre_usuario' => strtolower($request->nombre_usuario),
            'contrasena_hash' => Hash::make($request->password), // Encriptamos la contraseña
            'email' => $request->email,
            'rol' => $request->rol,
            'activo' => 1
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit($id)
    {
        $usuario = Usuario::findOrFail($id);
        $trabajadores = Trabajador::activos()->orderBy('nombre', 'asc')->get();
        $roles = \App\Models\Rol::orderBy('nombre')->pluck('nombre');

        return view('usuarios.edit', compact('usuario', 'trabajadores', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);

        $request->validate([
            'codigo_trabajador' => 'nullable|exists:trabajador,codigo',
            'nombre_usuario' => 'required|string|max:50|unique:usuarios,nombre_usuario,' . $id . ',id_usuario',
            'email' => 'nullable|email|max:100',
            'rol' => 'required|string|max:50',
            'password' => 'nullable|string|min:6', // Opcional al editar
        ]);

        $data = [
            'codigo_trabajador' => $request->codigo_trabajador,
            'nombre_usuario' => strtolower($request->nombre_usuario),
            'email' => $request->email,
            'rol' => $request->rol,
        ];

        // Solo actualizamos la contraseña si el usuario escribió una nueva
        if ($request->filled('password')) {
            $data['contrasena_hash'] = Hash::make($request->password);
        }

        $usuario->update($data);

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy($id)
    {
        $usuario = Usuario::findOrFail($id);

        // Prevención para no desactivarse a uno mismo
        if (Auth::id() == $usuario->id_usuario) {
            return redirect()->route('usuarios.index')->with('error', 'No puedes desactivar tu propia cuenta mientras estás en sesión.');
        }

        $usuario->update(['activo' => 0]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario desactivado del sistema.');
    }
}