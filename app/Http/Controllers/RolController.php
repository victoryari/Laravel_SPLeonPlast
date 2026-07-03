<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Modulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RolController extends Controller
{
    public function index()
    {
        $roles = Rol::withCount('usuarios')->get();
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $modulosGrupados = Modulo::orderBy('grupo')->orderBy('nombre')->get()->groupBy('grupo');
        return view('roles.create', compact('modulosGrupados'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:50|unique:roles,nombre',
            'descripcion' => 'nullable|string|max:255',
            'modulos' => 'array',
            'modulos.*' => 'exists:modulos,id'
        ]);

        $rol = Rol::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion
        ]);

        if ($request->has('modulos')) {
            $rol->modulos()->sync($request->modulos);
        }

        Cache::forget('permisos_rol_' . $rol->nombre);

        return redirect()->route('roles.index')->with('success', 'Rol creado exitosamente.');
    }

    public function edit(Rol $role)
    {
        $modulosGrupados = Modulo::orderBy('grupo')->orderBy('nombre')->get()->groupBy('grupo');
        $rolModulos = $role->modulos->pluck('id')->toArray();
        return view('roles.edit', compact('role', 'modulosGrupados', 'rolModulos'));
    }

    public function update(Request $request, Rol $role)
    {
        $request->validate([
            'nombre' => 'required|string|max:50|unique:roles,nombre,' . $role->id,
            'descripcion' => 'nullable|string|max:255',
            'modulos' => 'array',
            'modulos.*' => 'exists:modulos,id'
        ]);

        $role->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion
        ]);

        $role->modulos()->sync($request->input('modulos', []));

        Cache::forget('permisos_rol_' . $role->nombre);
        
        return redirect()->route('roles.index')->with('success', 'Rol actualizado exitosamente.');
    }

    public function destroy(Rol $role)
    {
        if ($role->usuarios()->count() > 0) {
            return redirect()->route('roles.index')->with('error', 'No se puede eliminar el rol porque tiene usuarios asignados.');
        }

        if ($role->nombre === 'Administrador') {
            return redirect()->route('roles.index')->with('error', 'El rol de Administrador principal no puede ser eliminado.');
        }

        $nombre = $role->nombre;
        $role->delete();
        
        Cache::forget('permisos_rol_' . $nombre);

        return redirect()->route('roles.index')->with('success', 'Rol eliminado exitosamente.');
    }
}
