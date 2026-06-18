<?php

namespace App\Http\Controllers;

use App\Models\OrdenProduccion;
use App\Models\ProductoProceso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdenProduccionController extends Controller
{
    public function index(Request $request)
    {
        $fecha_desde = $request->input('fecha_desde', now()->startOfMonth()->toDateString());
        $fecha_hasta = $request->input('fecha_hasta', now()->endOfMonth()->toDateString());

        $query = OrdenProduccion::with('productoProceso')
            ->where('activo', 1)
            ->whereDate('fecha', '>=', $fecha_desde)
            ->whereDate('fecha', '<=', $fecha_hasta);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('codigo_op', 'like', "%$search%")
                  ->orWhere('descripcion_producto_proceso', 'like', "%$search%");
            });
        }

        $ordenes = $query->orderBy('fecha', 'desc')
            ->orderBy('idop', 'desc')
            ->paginate(15)
            ->appends($request->all());

        return view('produccion.ordenes.index', compact('ordenes', 'fecha_desde', 'fecha_hasta'));
    }

    public function create()
    {
        $year = date('Y');
        $prefix = "OP-{$year}-";

        $latestOp = \DB::table('orden_produccion_global')
            ->where('codigo_op', 'LIKE', "{$prefix}%")
            ->orderByRaw('LENGTH(codigo_op) DESC')
            ->orderBy('codigo_op', 'desc')
            ->first();

        $nextNumber = 1;
        if ($latestOp) {
            $numberPart = str_replace($prefix, '', $latestOp->codigo_op);
            if (is_numeric($numberPart)) {
                $nextNumber = intval($numberPart) + 1;
            }
        }

        $codigo_op_sugerido = $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        // Obtenemos productos de proceso para el select
        $productos_proceso = \App\Models\ProductoProceso::orderBy('descripcion', 'asc')->get();
        return view('produccion.ordenes.create', compact('productos_proceso', 'codigo_op_sugerido'));
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
            DB::beginTransaction();
            $orden = OrdenProduccion::where('idop', $id)->lockForUpdate()->firstOrFail();

            $procesosActivos = \App\Models\OrdenProceso::where('idop', $id)->where('estado', 1)->count();
            if ($procesosActivos > 0) {
                throw new \Exception("No se puede anular la orden porque tiene {$procesosActivos} proceso(s) activo(s). Anule los procesos primero.");
            }

            $orden->update(['activo' => 0]);

            DB::commit();
            return redirect()->route('produccion.ordenes.index')->with('success', 'Orden de producción anulada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al anular: ' . $e->getMessage());
        }
    }

    public function finalizar($id)
    {
        try {
            \App\Models\OrdenProduccion::where('idop', $id)->update(['estado' => 'COMPLETADO']);
            return back()->with('success', 'Orden de Producción cerrada exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al cerrar la Orden: ' . $e->getMessage());
        }
    }

    public function getProcesos($idop)
    {
        $procesos = \App\Models\OrdenProceso::where('idop', $idop)
            ->where('estado', 1)
            ->whereIn('estado_avance', ['PENDIENTE', 'EN_PROCESO'])
            ->orderBy('secuencia', 'asc')
            ->get(['id', 'codigo_proceso', 'descripcion_proceso', 'secuencia']);
            
        return response()->json($procesos);
    }
}
