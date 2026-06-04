<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductoProceso;
use App\Models\ProcesoProduccion;
use Illuminate\Support\Facades\DB;

class RutasProduccionController extends Controller
{
    public function index()
    {
        $productos = ProductoProceso::with('rutas')->where('estado', 1)->get();
        $procesos = ProcesoProduccion::where('estado', 1)->get();
        
        return view('admin.rutas_produccion.index', compact('productos', 'procesos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo_producto_proceso' => 'required|integer',
            'procesos' => 'array'
        ]);

        try {
            DB::beginTransaction();
            
            $producto = ProductoProceso::findOrFail($request->codigo_producto_proceso);
            
            // Sync removes all existing and adds the new ones
            $syncData = [];
            if ($request->has('procesos') && is_array($request->procesos)) {
                $secuencia = 10;
                foreach ($request->procesos as $proc_id) {
                    $syncData[$proc_id] = ['secuencia' => $secuencia];
                    $secuencia += 10;
                }
            }
            
            $producto->rutas()->sync($syncData);
            
            DB::commit();
            return back()->with('success', 'Ruta de producción actualizada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar ruta: ' . $e->getMessage());
        }
    }
}
