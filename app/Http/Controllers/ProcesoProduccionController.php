<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProcesoProduccion;
use Carbon\Carbon;

class ProcesoProduccionController extends Controller
{
    /**
     * Listado con búsqueda predictiva y paginación.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        // Base de la consulta: solo registros activos
        $query = ProcesoProduccion::where('estado', 1);

        // Búsqueda predictiva (Código o Descripción)
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('codigo', 'LIKE', "%{$search}%")
                  ->orWhere('descripcion', 'LIKE', "%{$search}%");
            });
        }

        // Paginación de 10 en 10
        $procesos = $query->orderBy('descripcion', 'asc')->paginate(10);
        
        // Mantener el término de búsqueda en los enlaces de paginación
        $procesos->appends(['search' => $search]);

        return view('tablas_maestras.proceso_produccion.index', compact('procesos', 'search'));
    }

    /**
     * Formulario de creación.
     */
    public function create()
    {
        return view('tablas_maestras.proceso_produccion.create');
    }

    /**
     * Almacenar registro.
     */
    public function store(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:15|unique:proceso_produccion,codigo',
            'descripcion' => 'required|string|max:150',
        ], [
            'codigo.required' => 'El código es obligatorio.',
            'codigo.unique' => 'Este código de proceso ya está registrado.',
            'descripcion.required' => 'La descripción es obligatoria.',
        ]);

        ProcesoProduccion::create([
            'codigo' => strtoupper($request->codigo),
            'descripcion' => $request->descripcion,
            'estado' => 1,
            'fecha_creacion' => Carbon::now(),
        ]);

        return redirect()->route('procesos_produccion.index')
            ->with('success', 'Proceso registrado exitosamente.');
    }

    /**
     * Formulario de edición.
     */
    public function edit($codigo)
    {
        $proceso = ProcesoProduccion::where('codigo', $codigo)->firstOrFail();
        
        if ($proceso->estado == 0) {
            return redirect()->route('procesos_produccion.index')->with('error', 'No se puede editar un registro anulado.');
        }

        return view('tablas_maestras.proceso_produccion.edit', compact('proceso'));
    }

    /**
     * Actualizar registro.
     */
    public function update(Request $request, $codigo)
    {
        $proceso = ProcesoProduccion::where('codigo', $codigo)->firstOrFail();

        $request->validate([
            'descripcion' => 'required|string|max:150',
        ], [
            'descripcion.required' => 'La descripción es obligatoria.',
        ]);

        $proceso->update([
            'descripcion' => $request->descripcion,
        ]);

        return redirect()->route('procesos_produccion.index')
            ->with('success', 'Proceso actualizado correctamente.');
    }

    /**
     * Anulación lógica (Soft Delete).
     */
    public function destroy($codigo)
    {
        $proceso = ProcesoProduccion::where('codigo', $codigo)->firstOrFail();
        
        $proceso->estado = 0;
        $proceso->save();

        return redirect()->route('procesos_produccion.index')
            ->with('success', 'Registro anulado correctamente.');
    }
}