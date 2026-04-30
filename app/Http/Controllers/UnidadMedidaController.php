<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UnidadMedida;
use Carbon\Carbon;

class UnidadMedidaController extends Controller
{
    /**
     * Muestra el listado de unidades activas.
     */
    public function index()
    {
        // Solo mostramos los que tienen estado = 1
        $unidades = UnidadMedida::where('estado', 1)
            ->orderBy('descripcion', 'asc')
            ->get();

        return view('tablas_maestras.unidad_medida.index', compact('unidades'));
    }

    /**
     * Formulario de creación.
     */
    public function create()
    {
        return view('tablas_maestras.unidad_medida.create');
    }

    /**
     * Almacena el nuevo registro.
     */
    public function store(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:10|unique:unidad_medida,codigo',
            'descripcion' => 'required|string|max:100',
        ], [
            'codigo.required' => 'El código es obligatorio.',
            'codigo.unique' => 'Este código ya existe en el sistema.',
            'descripcion.required' => 'La descripción es obligatoria.',
        ]);

        UnidadMedida::create([
            'codigo' => strtoupper($request->codigo),
            'descripcion' => $request->descripcion,
            'estado' => 1,
            'fecha_creacion' => Carbon::now(),
        ]);

        return redirect()->route('unidades_medida.index')
            ->with('success', 'Unidad de medida creada correctamente.');
    }

    /**
     * Formulario de edición.
     */
    public function edit($codigo)
    {
        $unidad = UnidadMedida::where('codigo', $codigo)->firstOrFail();
        
        if ($unidad->estado == 0) {
            return redirect()->route('unidades_medida.index')
                ->with('error', 'No se puede editar un registro anulado.');
        }

        return view('tablas_maestras.unidad_medida.edit', compact('unidad'));
    }

    /**
     * Actualiza el registro.
     */
    public function update(Request $request, $codigo)
    {
        $unidad = UnidadMedida::where('codigo', $codigo)->firstOrFail();

        $request->validate([
            'descripcion' => 'required|string|max:100',
        ]);

        $unidad->update([
            'descripcion' => $request->descripcion,
        ]);

        return redirect()->route('unidades_medida.index')
            ->with('success', 'Unidad de medida actualizada.');
    }

    /**
     * Anulación lógica (Soft Delete).
     */
    public function destroy($codigo)
    {
        $unidad = UnidadMedida::where('codigo', $codigo)->firstOrFail();
        
        // No eliminamos, cambiamos el estado a 0
        $unidad->estado = 0;
        $unidad->save();

        return redirect()->route('unidades_medida.index')
            ->with('success', 'Registro anulado exitosamente.');
    }
}