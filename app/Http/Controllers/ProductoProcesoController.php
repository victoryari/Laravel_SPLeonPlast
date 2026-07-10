<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductoProceso;

class ProductoProcesoController extends Controller
{
    public function index()
    {
        $productos_proceso = ProductoProceso::where('estado', 1)
            ->orderBy('codigo', 'asc')
            ->get();

        return view('tablas_maestras.productos_proceso.index', compact('productos_proceso'));
    }

    public function create()
    {
        return view('tablas_maestras.productos_proceso.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:20|unique:producto_proceso,codigo',
            'descripcion' => 'required|string|max:100',
        ], [
            'codigo.required' => 'El código es obligatorio.',
            'codigo.unique' => 'Este código ya existe en el sistema.',
            'descripcion.required' => 'La descripción es obligatoria.',
        ]);

        ProductoProceso::create([
            'codigo' => strtoupper($request->codigo),
            'descripcion' => $request->descripcion,
            'estado' => 1,
        ]);

        return redirect()->route('productos_proceso.index')
            ->with('success', 'Producto de proceso creado correctamente.');
    }

    public function edit($codigo)
    {
        $producto = ProductoProceso::where('codigo', $codigo)->firstOrFail();
        
        if ($producto->estado == 0) {
            return redirect()->route('productos_proceso.index')
                ->with('error', 'No se puede editar un registro anulado.');
        }

        return view('tablas_maestras.productos_proceso.edit', compact('producto'));
    }

    public function update(Request $request, $codigo)
    {
        $producto = ProductoProceso::where('codigo', $codigo)->firstOrFail();

        $request->validate([
            'descripcion' => 'required|string|max:100',
        ], [
            'descripcion.required' => 'La descripción es obligatoria.',
        ]);

        $producto->update([
            'descripcion' => $request->descripcion,
        ]);

        return redirect()->route('productos_proceso.index')
            ->with('success', 'Producto de proceso actualizado.');
    }

    public function destroy($codigo)
    {
        $producto = ProductoProceso::where('codigo', $codigo)->firstOrFail();
        
        $producto->estado = 0;
        $producto->save();

        return redirect()->route('productos_proceso.index')
            ->with('success', 'Registro anulado exitosamente.');
    }
}
