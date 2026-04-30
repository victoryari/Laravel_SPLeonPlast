<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Almacen;
use Carbon\Carbon;

class AlmacenController extends Controller
{
    /**
     * Listado de almacenes activos con búsqueda y filtro por tipo.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $tipoFiltro = $request->input('tipo_almacen');

        $query = Almacen::where('activo', 1);

        // Filtro por búsqueda (Código o Descripción)
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('codigo_almacen', 'LIKE', "%{$search}%")
                  ->orWhere('descripcion', 'LIKE', "%{$search}%")
                  ->orWhere('responsable', 'LIKE', "%{$search}%");
            });
        }

        // Filtro por tipo de almacén
        if (!empty($tipoFiltro)) {
            $query->where('tipo_almacen', $tipoFiltro);
        }

        $almacenes = $query->orderBy('descripcion', 'asc')->paginate(10);

        // Conservar filtros en paginación
        $almacenes->appends(['search' => $search, 'tipo_almacen' => $tipoFiltro]);

        $tipos = Almacen::TIPOS_ALMACEN;

        return view('almacen.index', compact('almacenes', 'tipos', 'search', 'tipoFiltro'));
    }

    /**
     * Formulario de creación.
     */
    public function create()
    {
        $tipos = Almacen::TIPOS_ALMACEN;
        return view('almacen.create', compact('tipos'));
    }

    /**
     * Guardar nuevo almacén.
     */
    public function store(Request $request)
    {
        $request->validate([
            'codigo_almacen'  => 'required|string|max:10|unique:almacen,codigo_almacen',
            'descripcion'     => 'required|string|max:100',
            'tipo_almacen'    => 'required|in:MATERIA_PRIMA,PRODUCTO_TERMINADO,PRODUCTO_PROCESO,INSUMOS,SUMINISTROS',
            'direccion'       => 'nullable|string|max:200',
            'responsable'     => 'nullable|string|max:100',
        ], [
            'codigo_almacen.required' => 'El código es obligatorio.',
            'codigo_almacen.unique'   => 'Este código de almacén ya está registrado.',
            'codigo_almacen.max'      => 'El código no puede exceder 10 caracteres.',
            'descripcion.required'    => 'La descripción es obligatoria.',
            'tipo_almacen.required'   => 'Debe seleccionar un tipo de almacén.',
            'tipo_almacen.in'         => 'El tipo de almacén seleccionado no es válido.',
        ]);

        Almacen::create([
            'codigo_almacen' => strtoupper($request->codigo_almacen),
            'descripcion'    => $request->descripcion,
            'tipo_almacen'   => $request->tipo_almacen,
            'direccion'      => $request->direccion,
            'responsable'    => $request->responsable,
            'activo'         => 1,
        ]);

        return redirect()->route('almacenes.index')
            ->with('success', 'Almacén registrado exitosamente.');
    }

    /**
     * Formulario de edición.
     */
    public function edit(string $codigo)
    {
        $almacen = Almacen::where('codigo_almacen', $codigo)->firstOrFail();

        if ($almacen->activo == 0) {
            return redirect()->route('almacenes.index')
                ->with('error', 'No se puede editar un almacén anulado.');
        }

        $tipos = Almacen::TIPOS_ALMACEN;

        return view('almacen.edit', compact('almacen', 'tipos'));
    }

    /**
     * Actualizar datos del almacén.
     */
    public function update(Request $request, string $codigo)
    {
        $almacen = Almacen::where('codigo_almacen', $codigo)->firstOrFail();

        $request->validate([
            'descripcion'  => 'required|string|max:100',
            'tipo_almacen' => 'required|in:MATERIA_PRIMA,PRODUCTO_TERMINADO,PRODUCTO_PROCESO,INSUMOS,SUMINISTROS',
            'direccion'    => 'nullable|string|max:200',
            'responsable'  => 'nullable|string|max:100',
        ], [
            'descripcion.required'  => 'La descripción es obligatoria.',
            'tipo_almacen.required' => 'Debe seleccionar un tipo de almacén.',
        ]);

        $almacen->update([
            'descripcion'  => $request->descripcion,
            'tipo_almacen' => $request->tipo_almacen,
            'direccion'    => $request->direccion,
            'responsable'  => $request->responsable,
        ]);

        return redirect()->route('almacenes.index')
            ->with('success', 'Almacén actualizado correctamente.');
    }

    /**
     * Anulación lógica del almacén.
     */
    public function destroy(string $codigo)
    {
        $almacen = Almacen::where('codigo_almacen', $codigo)->firstOrFail();

        $almacen->activo = 0;
        $almacen->save();

        return redirect()->route('almacenes.index')
            ->with('success', 'Almacén anulado correctamente.');
    }
}
