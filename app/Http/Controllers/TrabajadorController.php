<?php

namespace App\Http\Controllers;

use App\Models\Trabajador;
use Illuminate\Http\Request;

class TrabajadorController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Trabajador::activos();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('codigo', 'LIKE', "%$search%")
                  ->orWhere('nombre', 'LIKE', "%$search%")
                  ->orWhere('empresa', 'LIKE', "%$search%");
            });
        }

        $trabajadores = $query->orderBy('nombre', 'asc')->paginate(10);

        // Retornamos directamente la vista index, el JavaScript se encargará de extraer la tabla.
        return view('tablas_maestras.trabajadores.index', compact('trabajadores', 'search'));
    }

    public function create()
    {
        return view('tablas_maestras.trabajadores.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:20|unique:trabajador,codigo',
            'nombre' => 'required|string|max:150',
            'empresa' => 'nullable|string|max:100',
            'sueldo' => 'nullable|numeric|min:0',
        ]);

        Trabajador::create([
            'codigo' => strtoupper($request->codigo),
            'nombre' => $request->nombre,
            'empresa' => $request->empresa,
            'sueldo' => $request->sueldo,
            'estado' => 1
        ]);

        return redirect()->route('trabajadores.index')->with('success', 'Trabajador registrado correctamente.');
    }

    public function edit($codigo)
    {
        $trabajador = Trabajador::findOrFail($codigo);
        return view('tablas_maestras.trabajadores.edit', compact('trabajador'));
    }

    public function update(Request $request, $codigo)
    {
        $trabajador = Trabajador::findOrFail($codigo);

        $request->validate([
            'nombre' => 'required|string|max:150',
            'empresa' => 'nullable|string|max:100',
            'sueldo' => 'nullable|numeric|min:0',
        ]);

        $trabajador->update([
            'nombre' => $request->nombre,
            'empresa' => $request->empresa,
            'sueldo' => $request->sueldo
        ]);

        return redirect()->route('trabajadores.index')->with('success', 'Datos del trabajador actualizados.');
    }

    public function destroy($codigo)
    {
        $trabajador = Trabajador::findOrFail($codigo);
        $trabajador->update(['estado' => 0]);

        return redirect()->route('trabajadores.index')->with('success', 'Trabajador dado de baja correctamente.');
    }
}