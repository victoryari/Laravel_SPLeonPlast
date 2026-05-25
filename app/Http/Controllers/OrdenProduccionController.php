<?php

namespace App\Http\Controllers;

use App\Models\OrdenProduccion;
use App\Models\ProductoProceso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdenProduccionController extends Controller
{
    public function index()
    {
        // Traer órdenes activas ordenadas por fecha descendente
        $ordenes = OrdenProduccion::with('productoProceso')
            ->where('activo', 1)
            ->orderBy('fecha', 'desc')
            ->orderBy('idop', 'desc')
            ->paginate(15);

        return view('produccion.ordenes.index', compact('ordenes'));
    }

    public function create()
    {
        // Obtenemos productos de proceso para el select
        $productos_proceso = ProductoProceso::orderBy('descripcion', 'asc')->get();
        return view('produccion.ordenes.create', compact('productos_proceso'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo_op' => 'required|string|max:50',
            'codigo_producto_proceso' => 'required|string',
            'fecha' => 'required|date',
            'hora_inicio' => 'nullable|date_format:H:i',
            'texto_obs' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Buscar descripción del producto seleccionado
            $producto = ProductoProceso::findOrFail($request->codigo_producto_proceso);

            $orden = OrdenProduccion::create([
                'codigo_op' => $request->codigo_op,
                'codigo_producto_proceso' => $request->codigo_producto_proceso,
                'descripcion_producto_proceso' => $producto->descripcion,
                'fecha' => $request->fecha,
                'hora_inicio' => $request->hora_inicio,
                'texto_obs' => $request->texto_obs,
                'estado' => 'PENDIENTE',
                'activo' => 1
            ]);

            DB::commit();

            return redirect()->route('ordenes.procesos.index', $orden->idop)
                ->with('success', "Orden de producción creada exitosamente (ID: {$orden->idop})");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al crear la orden: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $orden = OrdenProduccion::findOrFail($id);
            // Baja lógica
            $orden->update(['activo' => 0]);
            
            return redirect()->route('produccion.ordenes.index')->with('success', 'Orden de producción anulada correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al anular: ' . $e->getMessage());
        }
    }
}
