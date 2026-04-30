<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TipoProducto;
use Carbon\Carbon;

class TipoProductoController extends Controller
{
    /**
     * Muestra el listado de tipos de producto activos.
     */
    public function index()
    {
        $tipos = TipoProducto::where('estado', 1)
            ->orderBy('descripcion', 'asc')
            ->get();

        return view('tablas_maestras.tipo_producto.index', compact('tipos'));
    }

    /**
     * Formulario de creación.
     */
    public function create()
    {
        return view('tablas_maestras.tipo_producto.create');
    }

    /**
     * Almacena el nuevo registro.
     */
    public function store(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:10|unique:tipo_producto,codigo',
            'descripcion' => 'required|string|max:100',
        ], [
            'codigo.required' => 'El código es obligatorio.',
            'codigo.unique' => 'Este código ya existe en el sistema.',
            'descripcion.required' => 'La descripción es obligatoria.',
        ]);

        TipoProducto::create([
            'codigo' => strtoupper($request->codigo),
            'descripcion' => $request->descripcion,
            'estado' => 1,
            'fecha_creacion' => Carbon::now(),
        ]);

        return redirect()->route('tipos_producto.index')
            ->with('success', 'Tipo de producto creado correctamente.');
    }

    /**
     * Formulario de edición.
     */
    public function edit($codigo)
    {
        $tipo = TipoProducto::where('codigo', $codigo)->firstOrFail();
        
        if ($tipo->estado == 0) {
            return redirect()->route('tipos_producto.index')
                ->with('error', 'No se puede editar un registro anulado.');
        }

        return view('tablas_maestras.tipo_producto.edit', compact('tipo'));
    }

    /**
     * Actualiza el registro.
     */
    public function update(Request $request, $codigo)
    {
        $tipo = TipoProducto::where('codigo', $codigo)->firstOrFail();

        $request->validate([
            'descripcion' => 'required|string|max:100',
        ], [
            'descripcion.required' => 'La descripción es obligatoria.',
        ]);

        $tipo->update([
            'descripcion' => $request->descripcion,
        ]);

        return redirect()->route('tipos_producto.index')
            ->with('success', 'Tipo de producto actualizado.');
    }

    /**
     * Anulación lógica (Soft Delete).
     */
    public function destroy($codigo)
    {
        $tipo = TipoProducto::where('codigo', $codigo)->firstOrFail();
        
        $tipo->estado = 0;
        $tipo->save();

        return redirect()->route('tipos_producto.index')
            ->with('success', 'Registro anulado exitosamente.');
    }
}