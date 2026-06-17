<?php

namespace App\Http\Controllers;

use App\Models\{RequerimientoMaterial, DetalleRequerimientoMaterial, DespachoRequerimientoLote, Almacen};
use App\Services\KardexService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Auth};

class DespachoRequerimientoController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'pendientes');
        
        $fecha_desde = $request->input('fecha_desde', now()->startOfMonth()->toDateString());
        $fecha_hasta = $request->input('fecha_hasta', now()->endOfMonth()->toDateString());

        $query = RequerimientoMaterial::with(['detalles', 'creador'])
            ->whereDate('fecha_creacion', '>=', $fecha_desde)
            ->whereDate('fecha_creacion', '<=', $fecha_hasta);
            
        if ($tab === 'atendidos') {
            $query->whereIn('estado', ['ATENDIDO_TOTAL']);
        } else {
            $query->whereIn('estado', ['APROBADO', 'ATENDIDO_PARCIAL']);
        }

        if ($request->filled('search')) {
            $query->where('codigo', 'LIKE', "%{$request->search}%");
        }

        $requerimientos = $query->orderBy('fecha_creacion', 'desc')->paginate(15);
        $requerimientos->appends($request->all());

        return view('inventario.despachos.index', compact('requerimientos', 'tab', 'fecha_desde', 'fecha_hasta'));
    }

    public function atender($id)
    {
        $requerimiento = RequerimientoMaterial::with([
            'detalles.producto.unidad',
            'detalles.almacenOrigen',
            'detalles.almacenDestino',
        ])->findOrFail($id);

        if (!in_array($requerimiento->estado, ['APROBADO', 'ATENDIDO_PARCIAL'])) {
            return back()->with('error', 'Solo se pueden atender requerimientos en estado APROBADO o ATENDIDO_PARCIAL.');
        }

        $lineas = [];

        foreach ($requerimiento->detalles as $detalle) {
            $saldo = $detalle->cantidad_solicitada - $detalle->cantidad_atendida;

            if ($saldo <= 0) {
                continue;
            }

            $lotes = DB::table('inventario')
                ->join('almacen', 'inventario.codigo_almacen', '=', 'almacen.codigo_almacen')
                ->where('inventario.codigo_producto', $detalle->codigo_producto)
                ->where('inventario.stock_actual', '>', 0)
                ->orderBy('inventario.fecha_vencimiento', 'asc')
                ->orderBy('inventario.lote', 'asc')
                ->get(['inventario.codigo_almacen', 'almacen.descripcion as almacen_nombre', 'inventario.lote', 'inventario.fecha_vencimiento', 'inventario.stock_actual']);

            $lineas[] = [
                'detalle' => $detalle,
                'saldo' => $saldo,
                'lotes' => $lotes,
            ];
        }

        $almacenes = Almacen::where('activo', 1)->get();

        return view('inventario.despachos.atender', compact('requerimiento', 'lineas', 'almacenes'));
    }

    public function storeAtender(Request $request, $id)
    {
        $requerimiento = RequerimientoMaterial::findOrFail($id);

        if (!in_array($requerimiento->estado, ['APROBADO', 'ATENDIDO_PARCIAL'])) {
            return back()->with('error', 'Solo se pueden atender requerimientos en estado APROBADO o ATENDIDO_PARCIAL.');
        }

        // Convertir strings vacíos de cantidad a null para que nullable funcione correctamente
        $input = $request->all();
        if (isset($input['lotes']) && is_array($input['lotes'])) {
            foreach ($input['lotes'] as $key => $lote) {
                $input['lotes'][$key]['cantidad'] = (
                    !isset($lote['cantidad']) || $lote['cantidad'] === '' || $lote['cantidad'] === null
                ) ? null : $lote['cantidad'];
            }
            $request->merge($input);
        }

        $request->validate([
            'lotes' => 'required|array|min:1',
            'lotes.*.id_detalle' => 'required|exists:detalle_requerimientos_materiales,id_detalle',
            'lotes.*.codigo_almacen_origen' => 'required|exists:almacen,codigo_almacen',
            'lotes.*.codigo_almacen_destino' => 'required|exists:almacen,codigo_almacen',
            'lotes.*.lote' => 'required|string|max:50',
            'lotes.*.cantidad' => 'nullable|numeric|min:0',
        ]);

        $kardexService = app(KardexService::class);

        DB::beginTransaction();
        try {
            $todasCompletas = true;

            foreach ($request->lotes as $item) {
                if ($item['codigo_almacen_origen'] === $item['codigo_almacen_destino']) {
                    throw new \Exception("El almacén de origen y destino deben ser diferentes.");
                }
                $detalle = DetalleRequerimientoMaterial::where('id_detalle', $item['id_detalle'])->lockForUpdate()->firstOrFail();
                $saldo = $detalle->cantidad_solicitada - $detalle->cantidad_atendida;
                $cantidad = min($item['cantidad'] ?? 0, $saldo);

                if ($cantidad <= 0) {
                    continue;
                }

                $inventarioOrigen = DB::table('inventario')
                    ->where('codigo_producto', $detalle->codigo_producto)
                    ->where('codigo_almacen', $item['codigo_almacen_origen'])
                    ->where('lote', $item['lote'])
                    ->lockForUpdate()
                    ->first();

                if (!$inventarioOrigen || $inventarioOrigen->stock_actual < $cantidad) {
                    throw new \Exception("Stock insuficiente del lote {$item['lote']} en el almacén de origen para el producto {$detalle->codigo_producto}.");
                }

                $costosSalida = $kardexService->calcularCostos(
                    $detalle->codigo_producto,
                    $item['codigo_almacen_origen'],
                    0, 0, $cantidad,
                    $inventarioOrigen->stock_actual
                );

                DB::table('kardex')->insert([
                    'codigo_almacen' => $item['codigo_almacen_origen'],
                    'codigo_producto' => $detalle->codigo_producto,
                    'fecha_movimiento' => now(),
                    'tipo_movimiento' => 'SALIDA',
                    'documento' => 'REQUERIMIENTO',
                    'numero_documento' => $requerimiento->codigo,
                    'cantidad_entrada' => 0,
                    'costo_entrada' => 0,
                    'total_entrada' => 0,
                    'cantidad_salida' => $cantidad,
                    'costo_salida' => $costosSalida['costo_salida'],
                    'total_salida' => $costosSalida['total_salida'],
                    'cantidad_saldo' => $costosSalida['cantidad_saldo'],
                    'costo_promedio' => $costosSalida['costo_promedio'],
                    'total_saldo' => $costosSalida['total_saldo'],
                    'lote' => $item['lote'],
                    'usuario_registro' => Auth::id(),
                ]);

                DB::table('movimientos_inventario')->insert([
                    'codigo_almacen' => $item['codigo_almacen_origen'],
                    'codigo_producto' => $detalle->codigo_producto,
                    'lote' => $item['lote'],
                    'tipo_movimiento' => 'SALIDA',
                    'cantidad' => $cantidad,
                    'costo_unitario' => $costosSalida['costo_salida'],
                    'total' => $costosSalida['total_salida'],
                    'documento_referencia' => 'REQUERIMIENTO',
                    'numero_referencia' => $requerimiento->codigo,
                    'fecha_movimiento' => now(),
                    'usuario_movimiento' => Auth::id(),
                    'estado' => 1,
                ]);

                DB::table('inventario')
                    ->where('id_inventario', $inventarioOrigen->id_inventario)
                    ->update([
                        'stock_actual' => $inventarioOrigen->stock_actual - $cantidad,
                        'fecha_ultimo_movimiento' => now(),
                        'usuario_ultimo_movimiento' => Auth::id(),
                    ]);

                $invDestino = DB::table('inventario')
                    ->where('codigo_producto', $detalle->codigo_producto)
                    ->where('codigo_almacen', $item['codigo_almacen_destino'])
                    ->where('lote', $item['lote'])
                    ->lockForUpdate()
                    ->first();

                $costosIngreso = $kardexService->calcularCostos(
                    $detalle->codigo_producto,
                    $item['codigo_almacen_destino'],
                    $cantidad,
                    $costosSalida['costo_salida'],
                    0,
                    $invDestino?->stock_actual ?? 0
                );

                DB::table('kardex')->insert([
                    'codigo_almacen' => $item['codigo_almacen_destino'],
                    'codigo_producto' => $detalle->codigo_producto,
                    'fecha_movimiento' => now(),
                    'tipo_movimiento' => 'INGRESO',
                    'documento' => 'REQUERIMIENTO',
                    'numero_documento' => $requerimiento->codigo,
                    'cantidad_entrada' => $cantidad,
                    'costo_entrada' => $costosSalida['costo_salida'],
                    'total_entrada' => $costosIngreso['total_entrada'],
                    'cantidad_salida' => 0,
                    'costo_salida' => 0,
                    'total_salida' => 0,
                    'cantidad_saldo' => $costosIngreso['cantidad_saldo'],
                    'costo_promedio' => $costosIngreso['costo_promedio'],
                    'total_saldo' => $costosIngreso['total_saldo'],
                    'lote' => $item['lote'],
                    'usuario_registro' => Auth::id(),
                ]);

                DB::table('movimientos_inventario')->insert([
                    'codigo_almacen' => $item['codigo_almacen_destino'],
                    'codigo_producto' => $detalle->codigo_producto,
                    'lote' => $item['lote'],
                    'tipo_movimiento' => 'INGRESO',
                    'cantidad' => $cantidad,
                    'costo_unitario' => $costosSalida['costo_salida'],
                    'total' => $costosIngreso['total_entrada'],
                    'documento_referencia' => 'REQUERIMIENTO',
                    'numero_referencia' => $requerimiento->codigo,
                    'fecha_movimiento' => now(),
                    'usuario_movimiento' => Auth::id(),
                    'estado' => 1,
                ]);

                if ($invDestino) {
                    DB::table('inventario')
                        ->where('id_inventario', $invDestino->id_inventario)
                        ->update([
                            'stock_actual' => $invDestino->stock_actual + $cantidad,
                            'estado' => 1,
                            'costo_promedio' => $costosIngreso['costo_promedio'],
                            'fecha_ultimo_movimiento' => now(),
                            'usuario_ultimo_movimiento' => Auth::id(),
                        ]);
                } else {
                    DB::table('inventario')->insert([
                        'codigo_almacen' => $item['codigo_almacen_destino'],
                        'codigo_producto' => $detalle->codigo_producto,
                        'lote' => $item['lote'],
                        'stock_actual' => $cantidad,
                        'costo_promedio' => $costosIngreso['costo_promedio'],
                        'ultimo_costo' => $costosSalida['costo_salida'],
                        'fecha_ultimo_movimiento' => now(),
                        'usuario_ultimo_movimiento' => Auth::id(),
                    ]);
                }

                // Update the detail line if it still has null warehouses
                if (is_null($detalle->codigo_almacen_origen) || is_null($detalle->codigo_almacen_destino)) {
                    $detalle->update([
                        'codigo_almacen_origen' => $item['codigo_almacen_origen'],
                        'codigo_almacen_destino' => $item['codigo_almacen_destino']
                    ]);
                }

                DespachoRequerimientoLote::create([
                    'id_detalle' => $detalle->id_detalle,
                    'id_requerimiento' => $requerimiento->id_requerimiento,
                    'lote' => $item['lote'],
                    'cantidad' => $cantidad,
                ]);

                $detalle->increment('cantidad_atendida', $cantidad);
                $detalle->refresh();

                if ($detalle->cantidad_atendida < $detalle->cantidad_solicitada) {
                    $todasCompletas = false;
                }
            }

            $nuevoEstado = $todasCompletas ? 'ATENDIDO_TOTAL' : 'ATENDIDO_PARCIAL';
            $requerimiento->update(['estado' => $nuevoEstado]);

            DB::commit();
            return redirect()->route('inventario.despachos.index')
                ->with('success', "Despacho registrado. Estado: {$nuevoEstado}.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al procesar el despacho: ' . $e->getMessage());
        }
    }
}
