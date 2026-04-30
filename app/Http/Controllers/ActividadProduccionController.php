<?php

namespace App\Http\Controllers;

use App\Models\ActividadProduccion;
use Illuminate\Http\Request;

class ActividadProduccionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = ActividadProduccion::activos();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('codigo', 'LIKE', "%$search%")
                  ->orWhere('descripcion', 'LIKE', "%$search%");
            });
        }

        $actividades = $query->orderBy('descripcion', 'asc')->paginate(10);

        return view('tablas_maestras.actividades.index', compact('actividades', 'search'));
    }

    public function create()
    {
        return view('tablas_maestras.actividades.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:20|unique:actividad_produccion,codigo',
            'descripcion' => 'required|string|max:150',
        ], [
            'codigo.unique' => 'Este código de actividad ya existe.',
            'codigo.required' => 'El código es obligatorio.',
            'descripcion.required' => 'La descripción es obligatoria.'
        ]);

        ActividadProduccion::create([
            'codigo' => strtoupper($request->codigo),
            'descripcion' => $request->descripcion,
            'estado' => 1
        ]);

        return redirect()->route('actividades.index')->with('success', 'Actividad registrada correctamente.');
    }

    public function edit($codigo)
    {
        $actividad = ActividadProduccion::findOrFail($codigo);
        return view('tablas_maestras.actividades.edit', compact('actividad'));
    }

    public function update(Request $request, $codigo)
    {
        $actividad = ActividadProduccion::findOrFail($codigo);

        $request->validate([
            'descripcion' => 'required|string|max:150',
        ], [
            'descripcion.required' => 'La descripción es obligatoria.'
        ]);

        $actividad->update([
            'descripcion' => $request->descripcion
        ]);

        return redirect()->route('actividades.index')->with('success', 'Actividad actualizada correctamente.');
    }

    public function destroy($codigo)
    {
        $actividad = ActividadProduccion::findOrFail($codigo);
        $actividad->update(['estado' => 0]);

        return redirect()->route('actividades.index')->with('success', 'Actividad anulada correctamente.');
    }
}