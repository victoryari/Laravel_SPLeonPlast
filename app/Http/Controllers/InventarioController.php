<?php

namespace App\Http\Controllers;

use App\Models\{Inventario, MovimientoInventario, Kardex, Compra, Almacen, Producto, ProduccionIngresoProceso, UnidadMedida, GuiaRemisionCompra};
use App\Services\KardexService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Auth};
use App\Exports\KardexExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class InventarioController extends Controller
{
    // 1. EXISTENCIAS (Stock Actual)
    public function index(Request $request) {
        $query = DB::table('inventario')
            ->join('producto', 'inventario.codigo_producto', '=', 'producto.codigo')
            ->join('almacen', 'inventario.codigo_almacen', '=', 'almacen.codigo_almacen')
            ->select(
                'inventario.id_inventario',
                'inventario.codigo_producto', 
                'inventario.codigo_almacen',
                'producto.descripcion as producto',
                'almacen.descripcion as almacen',
                'inventario.stock_actual',
                'inventario.stock_minimo',
                'inventario.stock_maximo',
                'inventario.fecha_ultimo_movimiento'
            )
            ->where('inventario.stock_actual', '!=', 0);

        if ($request->search) {
            $query->where('producto.descripcion', 'LIKE', "%{$request->search}%")
                  ->orWhere('inventario.codigo_producto', 'LIKE', "%{$request->search}%");
        }

        $stocks = $query->orderBy('producto.descripcion')->paginate(10);
        return view('inventario.index', compact('stocks'));
    }

    // 2. RECEPCIONES (Facturas Pendientes)
    public function recepciones() {
        $comprasPendientes = Compra::with(['datosProveedor', 'detalles.producto'])
            ->where('estado', 'PENDIENTE')
            ->orderBy('fecha_compra', 'asc')
            ->get();
            
        $produccionPendientes = ProduccionIngresoProceso::where('estado', 'PENDIENTE')
            ->orderBy('fecha_ingreso', 'asc')
            ->get();

        $guiasPendientes = GuiaRemisionCompra::with(['datosProveedor', 'detalles.producto'])
            ->where('estado', 'RECIBIDA')
            ->orderBy('fecha_emision', 'asc')
            ->get();

        $almacenes = Almacen::where('activo', 1)->get();
        return view('inventario.recepciones', compact('comprasPendientes', 'produccionPendientes', 'guiasPendientes', 'almacenes'));
    }

    // 2.0.1 HISTORIAL DE RECEPCIONES
    public function recepcionesHistorial() {
        $recepciones = Compra::with(['datosProveedor', 'detalles'])
            ->where('estado', 'RECIBIDA')
            ->orderBy('fecha_compra', 'desc')
            ->paginate(20);

        return view('inventario.recepciones_historial', compact('recepciones'));
    }

    // 2.1 PROCESAR RECEPCIÓN
    public function procesarRecepcion(Request $request, $id) {
        $compra = Compra::with('detalles')->findOrFail($id);

        if ($compra->estado !== 'PENDIENTE') {
            return back()->with('error', 'La compra ya ha sido procesada o no está pendiente.');
        }

        $request->validate([
            'items.*.lote' => 'required|string|max:50'
        ], [
            'items.*.lote.required' => 'Es obligatorio ingresar el número de Lote para todos los productos recibidos.'
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->items as $id_detalle => $data) {
                $cantidad_recibida = floatval($data['cantidad']);
                $codigo_producto = $data['codigo_producto'];
                $codigo_almacen = $data['codigo_almacen'];
                $precio_original = floatval($data['precio'] ?? 0);
                $lote = $data['lote'] ?? null;
                $fecha_vencimiento = $data['fecha_vencimiento'] ?? null;

                // Convertir a soles si la compra fue en dólares
                $precio_unitario = ($compra->moneda === 'USD' && $compra->tipo_cambio > 0)
                    ? $precio_original * $compra->tipo_cambio
                    : $precio_original;

                // Bloqueo de fila para evitar condiciones de carrera
                $registroInventario = DB::table('inventario')
                    ->where('codigo_producto', $codigo_producto)
                    ->where('codigo_almacen', $codigo_almacen)
                    ->lockForUpdate()
                    ->first();

                $saldo_anterior = $registroInventario ? $registroInventario->stock_actual : 0;
                $nuevo_saldo = $saldo_anterior + $cantidad_recibida;

                // Calcular costos con promedio ponderado
                $kardexService = app(KardexService::class);
                $costos = $kardexService->calcularCostos(
                    $codigo_producto, $codigo_almacen,
                    $cantidad_recibida, $precio_unitario,
                    0, $nuevo_saldo
                );

                // Actualizar inventario general
                DB::table('inventario')->updateOrInsert(
                    ['codigo_producto' => $codigo_producto, 'codigo_almacen' => $codigo_almacen],
                    [
                        'stock_actual' => $nuevo_saldo,
                        'costo_promedio' => $costos['costo_promedio'],
                        'ultimo_costo' => $precio_unitario,
                        'fecha_ultimo_movimiento' => now(),
                        'usuario_ultimo_movimiento' => Auth::id()
                    ]
                );

                // Insertar en Kardex si hubo ingreso real
                if ($cantidad_recibida > 0) {
                    $kardexId = DB::table('kardex')->insertGetId([
                        'codigo_almacen'   => $codigo_almacen,
                        'codigo_producto'  => $codigo_producto,
                        'fecha_movimiento' => now(),
                        'tipo_movimiento'  => 'INGRESO',
                        'documento'        => $compra->tipo_documento,
                        'numero_documento' => $compra->serie_documento . '-' . $compra->numero_documento,
                        'cantidad_entrada' => $cantidad_recibida,
                        'costo_entrada'    => $costos['costo_entrada'],
                        'total_entrada'    => $costos['total_entrada'],
                        'cantidad_salida'  => 0,
                        'costo_salida'     => $costos['costo_salida'],
                        'total_salida'     => $costos['total_salida'],
                        'cantidad_saldo'   => $costos['cantidad_saldo'],
                        'costo_promedio'   => $costos['costo_promedio'],
                        'total_saldo'      => $costos['total_saldo'],
                        'lote'             => $lote,
                        'usuario_registro' => Auth::id()
                    ]);

                    // Insertar en movimientos_inventario
                    DB::table('movimientos_inventario')->insert([
                        'codigo_almacen'       => $codigo_almacen,
                        'codigo_producto'      => $codigo_producto,
                        'codigo_unidad_medida' => $data['codigo_unidad_medida'] ?? null,
                        'lote'                 => $lote,
                        'fecha_vencimiento'    => $fecha_vencimiento,
                        'tipo_movimiento'      => 'INGRESO',
                        'cantidad'             => $cantidad_recibida,
                        'costo_unitario'       => $costos['costo_entrada'],
                        'total'                => $costos['total_entrada'],
                        'documento_referencia' => $compra->tipo_documento,
                        'numero_referencia'    => $compra->serie_documento . '-' . $compra->numero_documento,
                        'observaciones'        => 'Recepción de compra',
                        'usuario_movimiento'   => Auth::id(),
                        'tiene_kardex'         => 1,
                        'fecha_movimiento'     => now(),
                    ]);
                }
            }

            // Actualizar lote/fecha_vencimiento en detalle_compra
            foreach ($request->items as $id_detalle => $data) {
                $updates = [];
                if (!empty($data['lote'])) $updates['lote'] = $data['lote'];
                if (!empty($data['fecha_vencimiento'])) $updates['fecha_vencimiento'] = $data['fecha_vencimiento'];
                if (!empty($updates)) {
                    DB::table('detalle_compra')->where('id_detalle_compra', $id_detalle)->update($updates);
                }
            }

            $compra->update(['estado' => 'RECIBIDA']);
            DB::commit();
            return back()->with('success', 'Recepción procesada y stock actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al procesar recepción: ' . $e->getMessage());
        }
    }

    // 2.2 PROCESAR RECEPCIÓN PRODUCCIÓN (PEP)
    public function procesarRecepcionProduccion(Request $request, $id) {
        try {
            DB::beginTransaction();

            $ingreso = ProduccionIngresoProceso::where('id_ingreso', $id)->where('estado', 'PENDIENTE')->lockForUpdate()->first();

            if (!$ingreso) {
                throw new \Exception("El registro de producción no existe, ya fue aprobado o fue anulado.");
            }

            $almacen_destino = !empty($request->codigo_almacen) ? trim($request->codigo_almacen) : $ingreso->codigo_almacen;
            $codigo_prod = $ingreso->codigo_producto_proceso;
            $cantidad_reportada = floatval($ingreso->cantidad);
            $cantidad_real = $request->has('cantidad_real') ? floatval($request->cantidad_real) : $cantidad_reportada;

            if ($cantidad_real <= 0) {
                throw new \Exception("La cantidad real ingresada debe ser mayor a cero.");
            }

            if ($cantidad_real > $cantidad_reportada) {
                throw new \Exception(
                    "La cantidad real ($cantidad_real) no puede exceder la cantidad reportada ($cantidad_reportada). "
                    . "Si hay sobreproducción, registre un nuevo proceso."
                );
            }

            $lote = $ingreso->lote_produccion;
            $idop = $ingreso->idop;
            $id_proceso = $ingreso->id_proceso;
            $usuario_movimiento = Auth::id() ?? 5;

            $doc_ref = "PRODUCCION_PEP";
            $num_ref = "OP-{$idop}-PROC-{$id_proceso}";
            
            $observacion_kardex = "Aprobación de Ingreso de Producto en Proceso (PEP)";
            if ($cantidad_real != $cantidad_reportada) {
                $diferencia = $cantidad_real - $cantidad_reportada;
                $signo = $diferencia > 0 ? '+' : '';
                $observacion_kardex .= ". [DIFERENCIA BÁSCULA] Reportado: {$cantidad_reportada} | Real: {$cantidad_real} | Dif: {$signo}{$diferencia}";
            }

            // Obtener costo promedio actual del producto
            $ultimoKardex = DB::table('kardex')
                ->where('codigo_producto', $codigo_prod)
                ->where('codigo_almacen', $almacen_destino)
                ->orderBy('fecha_movimiento', 'desc')
                ->orderBy('id_kardex', 'desc')
                ->first();
            $costoPromedioActual = $ultimoKardex?->costo_promedio ?? 0;

            // Calcular costos con promedio ponderado
            $kardexService = app(KardexService::class);
            $costos = $kardexService->calcularCostos(
                $codigo_prod, $almacen_destino,
                $cantidad_real, $costoPromedioActual,
                0, 0
            );

            // Movimiento inventario
            $movId = DB::table('movimientos_inventario')->insertGetId([
                'codigo_almacen' => $almacen_destino,
                'codigo_producto' => $codigo_prod,
                'lote' => $lote,
                'tipo_movimiento' => 'INGRESO',
                'cantidad' => $cantidad_real,
                'costo_unitario' => $costos['costo_promedio'],
                'total' => $costos['total_entrada'],
                'documento_referencia' => $doc_ref,
                'numero_referencia' => $num_ref,
                'idop' => $idop,
                'observaciones' => $observacion_kardex,
                'usuario_movimiento' => $usuario_movimiento,
                'fecha_movimiento' => now(),
                'estado' => 1,
                'tiene_kardex' => true
            ]);

            // Actualizar Inventario General
            $registroInventario = DB::table('inventario')
                ->where('codigo_producto', $codigo_prod)
                ->where('codigo_almacen', $almacen_destino)
                ->lockForUpdate()
                ->first();

            if ($registroInventario) {
                DB::table('inventario')->where('id_inventario', $registroInventario->id_inventario)->update([
                    'stock_actual' => $registroInventario->stock_actual + $cantidad_real,
                    'costo_promedio' => $costos['costo_promedio'],
                    'ultimo_costo' => $costoPromedioActual,
                    'fecha_ultimo_movimiento' => now(),
                    'usuario_ultimo_movimiento' => $usuario_movimiento
                ]);
                $nuevoStockPEP = $registroInventario->stock_actual + $cantidad_real;
            } else {
                DB::table('inventario')->insert([
                    'codigo_producto' => $codigo_prod,
                    'codigo_almacen' => $almacen_destino,
                    'stock_actual' => $cantidad_real,
                    'stock_minimo' => 0,
                    'stock_maximo' => 0,
                    'costo_promedio' => $costos['costo_promedio'],
                    'ultimo_costo' => $costoPromedioActual,
                    'estado' => 1,
                    'fecha_ultimo_movimiento' => now(),
                    'usuario_ultimo_movimiento' => $usuario_movimiento
                ]);
                $nuevoStockPEP = $cantidad_real;
            }

            // Kardex INGRESO por recepción de PEP
            DB::table('kardex')->insert([
                'codigo_almacen'       => $almacen_destino,
                'codigo_producto'      => $codigo_prod,
                'fecha_movimiento'     => $ingreso->fecha_ingreso,
                'tipo_movimiento'      => 'INGRESO',
                'documento'            => 'RECEPCION_PEP',
                'numero_documento'     => $num_ref,
                'cantidad_entrada'     => $costos['cantidad_saldo'] - ($ultimoKardex?->cantidad_saldo ?? 0),
                'costo_entrada'        => $costos['costo_entrada'],
                'total_entrada'        => $costos['total_entrada'],
                'cantidad_salida'      => 0,
                'costo_salida'         => 0,
                'total_salida'         => 0,
                'cantidad_saldo'       => $costos['cantidad_saldo'],
                'costo_promedio'       => $costos['costo_promedio'],
                'total_saldo'          => $costos['total_saldo'],
                'codigo_referencia_movimiento' => $movId,
                'observaciones'        => $observacion_kardex,
                'usuario_registro'     => $usuario_movimiento
            ]);

            // Actualizar produccion_ingresos_proceso
            $ingreso->update([
                'estado' => 'APROBADO',
                'cantidad' => $cantidad_real,
                'codigo_almacen' => $almacen_destino
            ]);

            DB::commit();
            return back()->with('success', "El Producto en Proceso ({$codigo_prod}) ha sido ingresado al almacén exitosamente con cantidad {$cantidad_real}.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al procesar recepción de producción: ' . $e->getMessage());
        }
    }

    // 2.3 PROCESAR UBICACIÓN DE GUÍA (TRANSFERENCIA DESDE ALM04)
    public function procesarUbicacionGuia(Request $request, $id) {
        $guia = GuiaRemisionCompra::with('detalles')->findOrFail($id);

        if ($guia->estado !== 'RECIBIDA') {
            return back()->with('error', 'La guía ya ha sido ubicada o no está en estado RECIBIDA.');
        }

        if (!$request->has('items') || !is_array($request->items)) {
            return back()->with('error', 'No se enviaron items para procesar.');
        }

        try {
            DB::beginTransaction();
            $kardexService = app(KardexService::class);
            $almacen_origen = 'ALM04';

            foreach ($request->items as $id_detalle => $data) {
                $almacen_destino = $data['codigo_almacen'];
                if ($almacen_destino === $almacen_origen) {
                    continue; // No transferir si el destino es igual al origen
                }

                $detalle = $guia->detalles->where('id_detalle_guia_compra', $id_detalle)->first();
                if (!$detalle) continue;

                $codigo_producto = $detalle->codigo_producto;
                $cantidad_transferir = floatval($detalle->cantidad);

                if ($cantidad_transferir <= 0) continue;

                // ===== 1. SALIDA DE ALM04 =====
                // Bloqueo de inventario origen
                $inventarioOrigen = DB::table('inventario')
                    ->where('codigo_producto', $codigo_producto)
                    ->where('codigo_almacen', $almacen_origen)
                    ->lockForUpdate()
                    ->first();

                if (!$inventarioOrigen || $inventarioOrigen->stock_actual < $cantidad_transferir) {
                    throw new \Exception("Stock insuficiente en ALMACEN COMPRAS NAC/IMP para el producto $codigo_producto.");
                }

                $costoPromedioActualOrigen = $inventarioOrigen->costo_promedio;
                
                $costosSalida = $kardexService->calcularCostos(
                    $codigo_producto, $almacen_origen,
                    0, 0, // No hay ingreso
                    $cantidad_transferir, $inventarioOrigen->stock_actual - $cantidad_transferir
                );

                // Actualizar inventario origen
                DB::table('inventario')
                    ->where('id_inventario', $inventarioOrigen->id_inventario)
                    ->update([
                        'stock_actual' => $inventarioOrigen->stock_actual - $cantidad_transferir,
                        'fecha_ultimo_movimiento' => now(),
                        'usuario_ultimo_movimiento' => Auth::id()
                    ]);

                // Registrar SALIDA en Kardex origen
                DB::table('kardex')->insert([
                    'codigo_almacen'   => $almacen_origen,
                    'codigo_producto'  => $codigo_producto,
                    'codigo_unidad_medida' => $detalle->codigo_unidad_medida ?? 'NIU',
                    'fecha_movimiento' => now(),
                    'tipo_movimiento'  => 'SALIDA',
                    'documento'        => 'TRANSFERENCIA',
                    'numero_documento' => $guia->numero_guia,
                    'cantidad_entrada' => 0,
                    'costo_entrada'    => 0,
                    'total_entrada'    => 0,
                    'cantidad_salida'  => $cantidad_transferir,
                    'costo_salida'     => $costoPromedioActualOrigen,
                    'total_salida'     => $cantidad_transferir * $costoPromedioActualOrigen,
                    'cantidad_saldo'   => $costosSalida['cantidad_saldo'],
                    'costo_promedio'   => $costosSalida['costo_promedio'],
                    'total_saldo'      => $costosSalida['total_saldo'],
                    'lote'             => $detalle->lote,
                    'observaciones'    => "Ubicación de Guía hacia {$almacen_destino}",
                    'usuario_registro' => Auth::id()
                ]);

                // ===== 2. INGRESO ALMACEN DESTINO =====
                $inventarioDestino = DB::table('inventario')
                    ->where('codigo_producto', $codigo_producto)
                    ->where('codigo_almacen', $almacen_destino)
                    ->lockForUpdate()
                    ->first();

                $saldo_anterior_destino = $inventarioDestino ? $inventarioDestino->stock_actual : 0;
                $nuevo_saldo_destino = $saldo_anterior_destino + $cantidad_transferir;

                $costosIngreso = $kardexService->calcularCostos(
                    $codigo_producto, $almacen_destino,
                    $cantidad_transferir, $costoPromedioActualOrigen, // Ingresa con el costo origen
                    0, $nuevo_saldo_destino
                );

                // Actualizar inventario destino
                DB::table('inventario')->updateOrInsert(
                    ['codigo_producto' => $codigo_producto, 'codigo_almacen' => $almacen_destino],
                    [
                        'codigo_unidad_medida' => $detalle->codigo_unidad_medida ?? 'NIU',
                        'stock_actual' => $nuevo_saldo_destino,
                        'costo_promedio' => $costosIngreso['costo_promedio'],
                        'ultimo_costo' => $costoPromedioActualOrigen,
                        'fecha_ultimo_movimiento' => now(),
                        'usuario_ultimo_movimiento' => Auth::id()
                    ]
                );

                // Registrar INGRESO en Kardex destino
                DB::table('kardex')->insert([
                    'codigo_almacen'   => $almacen_destino,
                    'codigo_producto'  => $codigo_producto,
                    'codigo_unidad_medida' => $detalle->codigo_unidad_medida ?? 'NIU',
                    'fecha_movimiento' => now(),
                    'tipo_movimiento'  => 'INGRESO',
                    'documento'        => 'TRANSFERENCIA',
                    'numero_documento' => $guia->numero_guia,
                    'cantidad_entrada' => $cantidad_transferir,
                    'costo_entrada'    => $costoPromedioActualOrigen,
                    'total_entrada'    => $cantidad_transferir * $costoPromedioActualOrigen,
                    'cantidad_salida'  => 0,
                    'costo_salida'     => 0,
                    'total_salida'     => 0,
                    'cantidad_saldo'   => $costosIngreso['cantidad_saldo'],
                    'costo_promedio'   => $costosIngreso['costo_promedio'],
                    'total_saldo'      => $costosIngreso['total_saldo'],
                    'lote'             => $detalle->lote,
                    'observaciones'    => "Recepción por Ubicación de Guía desde {$almacen_origen}",
                    'usuario_registro' => Auth::id()
                ]);
            }

            // Cambiar estado a UBICADA
            $guia->update(['estado' => 'UBICADA']);
            DB::commit();

            return back()->with('success', 'Guía ubicada y stock transferido correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al ubicar guía: ' . $e->getMessage());
        }
    }

    // 3. KARDEX VALORIZADO
    public function kardex(Request $request) {
        $query = DB::table('kardex')
            ->join('producto', 'kardex.codigo_producto', '=', 'producto.codigo')
            ->join('almacen', 'kardex.codigo_almacen', '=', 'almacen.codigo_almacen')
            ->select('kardex.*', 'producto.descripcion as producto', 'almacen.descripcion as almacen');

        if ($request->filled('documento')) {
            $query->where('kardex.documento', $request->documento);
        }

        if ($request->filled('codigo_producto')) {
            $query->where(function ($q) use ($request) {
                $q->where('kardex.codigo_producto', 'LIKE', "%{$request->codigo_producto}%")
                  ->orWhere('producto.descripcion', 'LIKE', "%{$request->codigo_producto}%");
            });
        }

        if ($request->filled('codigo_almacen')) {
            $query->where('kardex.codigo_almacen', $request->codigo_almacen);
        }

        if ($request->filled('fecha_desde')) {
            $query->where('kardex.fecha_movimiento', '>=', $request->fecha_desde . ' 00:00:00');
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('kardex.fecha_movimiento', '<=', $request->fecha_hasta . ' 23:59:59');
        }

        // Obtener query sin paginación para totales
        $queryClone = clone $query;
        $resumen = (object) [
            'total_entradas'     => $queryClone->sum('kardex.cantidad_entrada'),
            'total_entradas_val' => $queryClone->sum('kardex.total_entrada'),
        ];
        $queryClone2 = clone $query;
        $resumen->total_salidas     = $queryClone2->sum('kardex.cantidad_salida');
        $resumen->total_salidas_val = $queryClone2->sum('kardex.total_salida');

        // Ultimo saldo valorizado de cada producto en el filtro
        $ultimosSaldos = DB::table('kardex')
            ->whereIn('id_kardex', function ($sub) {
                $sub->select(DB::raw('MAX(k2.id_kardex)'))
                    ->from('kardex as k2')
                    ->whereColumn('k2.codigo_producto', 'kardex.codigo_producto')
                    ->whereColumn('k2.codigo_almacen', 'kardex.codigo_almacen');
            })
            ->select(DB::raw('SUM(cantidad_saldo) as total_cantidad'), DB::raw('SUM(total_saldo) as total_valorizado'))
            ->first();

        $resumen->saldo_final_cantidad = $ultimosSaldos?->total_cantidad ?? 0;
        $resumen->saldo_final_valor    = $ultimosSaldos?->total_valorizado ?? 0;

        $movimientos = $query->orderBy('kardex.fecha_movimiento', 'asc')
            ->orderBy('kardex.id_kardex', 'asc')
            ->paginate(15);

        $tiposDocumento = DB::table('kardex')->select('documento')->distinct()->orderBy('documento')->pluck('documento');
        $almacenes = \App\Models\Almacen::where('activo', 1)->get();

        return view('inventario.kardex', compact('movimientos', 'tiposDocumento', 'almacenes', 'resumen'));
    }

    public function exportarKardexExcel(Request $request)
    {
        return Excel::download(new KardexExport($request->all()), 'kardex_valorizado_'.date('Ymd_Hi').'.xlsx');
    }

    public function exportarKardexPdf(Request $request)
    {
        $export = new KardexExport($request->all());
        $movimientos = $export->collection();
        
        // Obtener resumen como en kardex()
        $resumen = (object)[
            'total_entradas' => $movimientos->sum('cantidad_entrada'),
            'total_entradas_val' => $movimientos->sum('total_entrada'),
            'total_salidas' => $movimientos->sum('cantidad_salida'),
            'total_salidas_val' => $movimientos->sum('total_salida'),
            'saldo_final_cantidad' => $movimientos->last()?->cantidad_saldo ?? 0,
            'saldo_final_val' => $movimientos->last()?->total_saldo ?? 0,
        ];

        $filtros = $request->all();

        $pdf = Pdf::loadView('inventario.pdf.kardex_pdf', compact('movimientos', 'resumen', 'filtros'))
                  ->setPaper('a4', 'landscape');

        return $pdf->download('kardex_valorizado_'.date('Ymd_Hi').'.pdf');
    }

    // 2.2 ALERTAS DE STOCK (productos por debajo del mínimo)
    public function alertasStock() {
        $alertas = DB::table('inventario')
            ->join('producto', 'inventario.codigo_producto', '=', 'producto.codigo')
            ->join('almacen', 'inventario.codigo_almacen', '=', 'almacen.codigo_almacen')
            ->whereColumn('inventario.stock_actual', '<', 'inventario.stock_minimo')
            ->where('inventario.stock_minimo', '>', 0)
            ->select(
                'inventario.*',
                'producto.descripcion as producto',
                'producto.codigo_unidad_medida',
                'almacen.descripcion as almacen'
            )
            ->orderBy('inventario.stock_actual', 'asc')
            ->paginate(20);

        return view('inventario.alertas_stock', compact('alertas'));
    }

    // 2.3 ACTUALIZAR STOCK MÍNIMO/MÁXIMO (AJAX)
    public function actualizarStockMinimo(Request $request) {
        $request->validate([
            'id_inventario' => 'required|integer|exists:inventario,id_inventario',
            'stock_minimo'  => 'nullable|numeric|min:0',
            'stock_maximo'  => 'nullable|numeric|min:0',
        ]);

        $updates = [];
        if ($request->has('stock_minimo')) $updates['stock_minimo'] = $request->stock_minimo;
        if ($request->has('stock_maximo')) $updates['stock_maximo'] = $request->stock_maximo;

        DB::table('inventario')->where('id_inventario', $request->id_inventario)->update($updates);

        return response()->json(['success' => true]);
    }

    // 3.1 EXPORTAR KARDEX (CSV)
    public function exportarKardex(Request $request) {
        $query = DB::table('kardex')
            ->join('producto', 'kardex.codigo_producto', '=', 'producto.codigo')
            ->join('almacen', 'kardex.codigo_almacen', '=', 'almacen.codigo_almacen')
            ->select('kardex.*', 'producto.descripcion as producto', 'almacen.descripcion as almacen');

        if ($request->filled('documento')) {
            $query->where('kardex.documento', $request->documento);
        }

        if ($request->filled('codigo_producto')) {
            $query->where(function ($q) use ($request) {
                $q->where('kardex.codigo_producto', 'LIKE', "%{$request->codigo_producto}%")
                  ->orWhere('producto.descripcion', 'LIKE', "%{$request->codigo_producto}%");
            });
        }

        if ($request->filled('codigo_almacen')) {
            $query->where('kardex.codigo_almacen', $request->codigo_almacen);
        }

        if ($request->filled('fecha_desde')) {
            $query->where('kardex.fecha_movimiento', '>=', $request->fecha_desde . ' 00:00:00');
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('kardex.fecha_movimiento', '<=', $request->fecha_hasta . ' 23:59:59');
        }

        $movimientos = $query->orderBy('kardex.fecha_movimiento', 'asc')
            ->orderBy('kardex.id_kardex', 'asc')
            ->get();

        $filename = 'kardex_valorizado_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($movimientos) {
            $output = fopen('php://output', 'w');

            // BOM UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            // Cabeceras
            fputcsv($output, [
                'Fecha', 'Producto', 'Almacen', 'Tipo', 'Documento',
                'Entrada Cant', 'Entrada Costo', 'Entrada Total',
                'Salida Cant', 'Salida Costo', 'Salida Total',
                'Saldo Cant', 'Costo Prom.', 'Saldo Total'
            ]);

            foreach ($movimientos as $mov) {
                fputcsv($output, [
                    \Carbon\Carbon::parse($mov->fecha_movimiento)->format('d/m/Y H:i'),
                    $mov->producto . ' (' . $mov->codigo_producto . ')',
                    $mov->almacen,
                    $mov->tipo_movimiento,
                    $mov->documento . ' ' . $mov->numero_documento,
                    $mov->cantidad_entrada ?: 0,
                    $mov->costo_entrada ?: 0,
                    $mov->total_entrada ?: 0,
                    $mov->cantidad_salida ?: 0,
                    $mov->costo_salida ?: 0,
                    $mov->total_salida ?: 0,
                    $mov->cantidad_saldo ?: 0,
                    $mov->costo_promedio ?: 0,
                    $mov->total_saldo ?: 0,
                ]);
            }

            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }

    // 4. AJUSTE MANUAL
    public function ajuste() {
        $productos = Producto::where('estado', 1)->get();
        $almacenes = Almacen::where('activo', 1)->get();
        $unidadesMedida = UnidadMedida::where('estado', 1)->get();
        return view('inventario.ajuste', compact('productos', 'almacenes', 'unidadesMedida'));
    }

    public function storeAjuste(Request $request) {
        $request->validate([
            'codigo_producto'      => 'required',
            'codigo_almacen'       => 'required',
            'cantidad'             => 'required|numeric|min:0.01',
            'tipo'                 => 'required|in:INGRESO,SALIDA',
            'codigo_unidad_medida' => 'required'
        ]);

        try {
            DB::beginTransaction();

            $registroInventario = DB::table('inventario')
                ->where('codigo_producto', $request->codigo_producto)
                ->where('codigo_almacen', $request->codigo_almacen)
                ->lockForUpdate()
                ->first();

            $saldo_anterior = $registroInventario ? $registroInventario->stock_actual : 0;
            
            if ($request->tipo === 'SALIDA' && $saldo_anterior < $request->cantidad) {
                return back()->with('error', 'Stock insuficiente para realizar la salida.');
            }

            $nuevo_saldo = $request->tipo === 'INGRESO' ? $saldo_anterior + $request->cantidad : $saldo_anterior - $request->cantidad;

            // Calcular costos con promedio ponderado
            $kardexService = app(KardexService::class);
            $cantidadEntrada = $request->tipo === 'INGRESO' ? $request->cantidad : 0;
            $cantidadSalida = $request->tipo === 'SALIDA' ? $request->cantidad : 0;

            $ultimoKardex = DB::table('kardex')
                ->where('codigo_producto', $request->codigo_producto)
                ->where('codigo_almacen', $request->codigo_almacen)
                ->orderBy('fecha_movimiento', 'desc')
                ->orderBy('id_kardex', 'desc')
                ->first();
            $costoPromedioActual = $ultimoKardex?->costo_promedio ?? 0;

            $costos = $kardexService->calcularCostos(
                $request->codigo_producto, $request->codigo_almacen,
                $cantidadEntrada, $costoPromedioActual,
                $cantidadSalida, $nuevo_saldo
            );

            DB::table('inventario')->updateOrInsert(
                ['codigo_producto' => $request->codigo_producto, 'codigo_almacen' => $request->codigo_almacen],
                [
                    'stock_actual' => $nuevo_saldo,
                    'costo_promedio' => $costos['costo_promedio'],
                    'codigo_unidad_medida' => $request->codigo_unidad_medida,
                    'fecha_ultimo_movimiento' => now(),
                    'usuario_ultimo_movimiento' => Auth::id()
                ]
            );

            DB::table('kardex')->insert([
                'codigo_almacen'       => $request->codigo_almacen,
                'codigo_producto'      => $request->codigo_producto,
                'codigo_unidad_medida' => $request->codigo_unidad_medida,
                'fecha_movimiento'     => now(),
                'tipo_movimiento'      => 'AJUSTE',
                'documento'            => 'TICKET',
                'numero_documento'     => 'AJ-' . date('YmdHis'),
                'cantidad_entrada'     => $cantidadEntrada,
                'costo_entrada'        => $costos['costo_entrada'],
                'total_entrada'        => $costos['total_entrada'],
                'cantidad_salida'      => $cantidadSalida,
                'costo_salida'         => $costos['costo_salida'],
                'total_salida'         => $costos['total_salida'],
                'cantidad_saldo'       => $costos['cantidad_saldo'],
                'costo_promedio'       => $costos['costo_promedio'],
                'total_saldo'          => $costos['total_saldo'],
                'usuario_registro'     => Auth::id()
            ]);

            DB::commit();
            return redirect()->route('inventario.index')->with('success', 'Ajuste procesado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // 4.2 BANDEJA DE AJUSTES (Lista con filtros)
    public function ajustesIndex(Request $request) {
        $query = DB::table('kardex')
            ->join('producto', 'kardex.codigo_producto', '=', 'producto.codigo')
            ->join('almacen', 'kardex.codigo_almacen', '=', 'almacen.codigo_almacen')
            ->leftJoin('unidad_medida', 'kardex.codigo_unidad_medida', '=', 'unidad_medida.codigo')
            ->where('kardex.tipo_movimiento', 'AJUSTE')
            ->select(
                'kardex.*',
                'producto.descripcion as producto',
                'almacen.descripcion as almacen',
                'unidad_medida.descripcion as unidad_medida'
            );

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('producto.descripcion', 'LIKE', "%{$search}%")
                  ->orWhere('kardex.codigo_producto', 'LIKE', "%{$search}%")
                  ->orWhere('kardex.numero_documento', 'LIKE', "%{$search}%")
                  ->orWhere('kardex.observaciones', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('codigo_almacen')) {
            $query->where('kardex.codigo_almacen', $request->codigo_almacen);
        }

        if ($request->filled('fecha_desde')) {
            $query->where('kardex.fecha_movimiento', '>=', $request->fecha_desde . ' 00:00:00');
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('kardex.fecha_movimiento', '<=', $request->fecha_hasta . ' 23:59:59');
        }

        $ajustes = $query->orderBy('kardex.fecha_movimiento', 'desc')->paginate(15);
        $almacenes = Almacen::where('activo', 1)->get();

        return view('inventario.ajuste_lista', compact('ajustes', 'almacenes'));
    }

    // 4.3 VER DETALLE DE AJUSTE
    public function showAjuste($id) {
        $ajuste = DB::table('kardex')
            ->join('producto', 'kardex.codigo_producto', '=', 'producto.codigo')
            ->join('almacen', 'kardex.codigo_almacen', '=', 'almacen.codigo_almacen')
            ->leftJoin('unidad_medida', 'kardex.codigo_unidad_medida', '=', 'unidad_medida.codigo')
            ->leftJoin('users', 'kardex.usuario_registro', '=', 'users.id')
            ->where('kardex.id_kardex', $id)
            ->where('kardex.tipo_movimiento', 'AJUSTE')
            ->select(
                'kardex.*',
                'producto.descripcion as producto',
                'almacen.descripcion as almacen',
                'unidad_medida.descripcion as unidad_medida',
                'unidad_medida.codigo as codigo_unidad_medida',
                'users.name as usuario_nombre'
            )
            ->firstOrFail();

        $movimientosPosteriores = DB::table('kardex')
            ->join('producto', 'kardex.codigo_producto', '=', 'producto.codigo')
            ->where('kardex.codigo_producto', $ajuste->codigo_producto)
            ->where('kardex.codigo_almacen', $ajuste->codigo_almacen)
            ->where(function($q) use ($ajuste) {
                $q->where('kardex.fecha_movimiento', '>', $ajuste->fecha_movimiento)
                  ->orWhere(function($q2) use ($ajuste) {
                      $q2->where('kardex.fecha_movimiento', '=', $ajuste->fecha_movimiento)
                         ->where('kardex.id_kardex', '>', $ajuste->id_kardex);
                  });
            })
            ->where('kardex.tipo_movimiento', '!=', 'AJUSTE')
            ->select('kardex.*', 'producto.descripcion as producto')
            ->orderBy('kardex.fecha_movimiento', 'asc')
            ->orderBy('kardex.id_kardex', 'asc')
            ->get();

        return view('inventario.ajuste_show', compact('ajuste', 'movimientosPosteriores'));
    }

    // 4.4 EDITAR AJUSTE (solo campos editables: cantidad, unidad_medida, observaciones)
    public function editAjuste($id) {
        $ajuste = DB::table('kardex')
            ->join('producto', 'kardex.codigo_producto', '=', 'producto.codigo')
            ->join('almacen', 'kardex.codigo_almacen', '=', 'almacen.codigo_almacen')
            ->where('kardex.id_kardex', $id)
            ->where('kardex.tipo_movimiento', 'AJUSTE')
            ->select('kardex.*', 'producto.descripcion as producto', 'almacen.descripcion as almacen')
            ->firstOrFail();

        $productos = Producto::where('estado', 1)->get();
        $almacenes = Almacen::where('activo', 1)->get();
        $unidadesMedida = UnidadMedida::where('estado', 1)->get();

        return view('inventario.ajuste_edit', compact('ajuste', 'productos', 'almacenes', 'unidadesMedida'));
    }

    public function updateAjuste(Request $request, $id) {
        $request->validate([
            'codigo_unidad_medida' => 'required',
            'cantidad'             => 'required|numeric|min:0.01',
            'tipo'                 => 'required|in:INGRESO,SALIDA',
            'observaciones'        => 'nullable|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            $ajusteOriginal = DB::table('kardex')->where('id_kardex', $id)->where('tipo_movimiento', 'AJUSTE')->lockForUpdate()->first();
            if (!$ajusteOriginal) {
                throw new \Exception("Ajuste no encontrado.");
            }

            $nuevaEntrada = $request->tipo === 'INGRESO' ? $request->cantidad : 0;
            $nuevaSalida  = $request->tipo === 'SALIDA' ? $request->cantidad : 0;

            $movimientosPosteriores = DB::table('kardex')
                ->where('codigo_producto', $ajusteOriginal->codigo_producto)
                ->where('codigo_almacen', $ajusteOriginal->codigo_almacen)
                ->where(function($q) use ($ajusteOriginal) {
                    $q->where('fecha_movimiento', '>', $ajusteOriginal->fecha_movimiento)
                      ->orWhere(function($q2) use ($ajusteOriginal) {
                          $q2->where('fecha_movimiento', '=', $ajusteOriginal->fecha_movimiento)
                             ->where('id_kardex', '>', $ajusteOriginal->id_kardex);
                      });
                })
                ->orderBy('fecha_movimiento', 'asc')
                ->orderBy('id_kardex', 'asc')
                ->get();

            if ($movimientosPosteriores->isNotEmpty()) {
                $originalEntrada = $ajusteOriginal->cantidad_entrada;
                $originalSalida  = $ajusteOriginal->cantidad_salida;

                $diferencia = $request->tipo === 'INGRESO'
                    ? $nuevaEntrada - $originalEntrada
                    : ($nuevaSalida - $originalSalida) * -1;

                $saldosAjustados = [];
                $saldoActual = ($ajusteOriginal->cantidad_saldo ?? 0) + $diferencia;

                $saldosAjustados[] = [
                    'id'     => $id,
                    'saldo'  => $saldoActual,
                ];

                foreach ($movimientosPosteriores as $mov) {
                    $saldoActual = $saldoActual + ($mov->cantidad_entrada - $mov->cantidad_salida);
                    $saldosAjustados[] = [
                        'id'    => $mov->id_kardex,
                        'saldo' => $saldoActual,
                    ];
                }

                foreach ($saldosAjustados as $item) {
                    DB::table('kardex')->where('id_kardex', $item['id'])->update(['cantidad_saldo' => $item['saldo']]);
                }

                DB::table('inventario')
                    ->where('codigo_producto', $ajusteOriginal->codigo_producto)
                    ->where('codigo_almacen', $ajusteOriginal->codigo_almacen)
                    ->update([
                        'stock_actual' => $saldoActual,
                        'codigo_unidad_medida' => $request->codigo_unidad_medida,
                        'fecha_ultimo_movimiento' => now(),
                        'usuario_ultimo_movimiento' => Auth::id()
                    ]);
            } else {
                $saldoAnterior = DB::table('kardex')
                    ->where('codigo_producto', $ajusteOriginal->codigo_producto)
                    ->where('codigo_almacen', $ajusteOriginal->codigo_almacen)
                    ->where('id_kardex', '<', $id)
                    ->orderBy('id_kardex', 'desc')
                    ->value('cantidad_saldo') ?? 0;

                $nuevo_saldo = $saldoAnterior + ($nuevaEntrada - $nuevaSalida);

                DB::table('inventario')
                    ->where('codigo_producto', $ajusteOriginal->codigo_producto)
                    ->where('codigo_almacen', $ajusteOriginal->codigo_almacen)
                    ->update([
                        'stock_actual' => $nuevo_saldo,
                        'codigo_unidad_medida' => $request->codigo_unidad_medida,
                        'fecha_ultimo_movimiento' => now(),
                        'usuario_ultimo_movimiento' => Auth::id()
                    ]);
            }

            DB::table('kardex')->where('id_kardex', $id)->update([
                'codigo_unidad_medida' => $request->codigo_unidad_medida,
                'cantidad_entrada'     => $request->tipo === 'INGRESO' ? $request->cantidad : 0,
                'cantidad_salida'      => $request->tipo === 'SALIDA' ? $request->cantidad : 0,
                'observaciones'        => $request->observaciones,
            ]);

            // Recalcular costos para este producto/almacen
            app(KardexService::class)->recalcular(
                $ajusteOriginal->codigo_producto,
                $ajusteOriginal->codigo_almacen
            );

            DB::commit();
            return redirect()->route('inventario.ajuste.lista')->with('success', 'Ajuste actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar: ' . $e->getMessage())->withInput();
        }
    }

    // 4.5 ELIMINAR AJUSTE (con validación de movimientos posteriores)
    public function destroyAjuste(Request $request, $id) {
        try {
            DB::beginTransaction();

            $ajuste = DB::table('kardex')->where('id_kardex', $id)->where('tipo_movimiento', 'AJUSTE')->lockForUpdate()->first();
            if (!$ajuste) {
                throw new \Exception("Ajuste no encontrado.");
            }

            if (str_contains($ajuste->observaciones ?? '', '[EXTORNADO]')) {
                return back()->with('error', 'Este ajuste ya fue extornado. No se puede eliminar.');
            }

            $movimientosPosteriores = DB::table('kardex')
                ->where('codigo_producto', $ajuste->codigo_producto)
                ->where('codigo_almacen', $ajuste->codigo_almacen)
                ->where(function($q) use ($ajuste) {
                    $q->where('fecha_movimiento', '>', $ajuste->fecha_movimiento)
                      ->orWhere(function($q2) use ($ajuste) {
                          $q2->where('fecha_movimiento', '=', $ajuste->fecha_movimiento)
                             ->where('id_kardex', '>', $ajuste->id_kardex);
                      });
                })
                ->orderBy('fecha_movimiento', 'asc')
                ->orderBy('id_kardex', 'asc')
                ->get();

            if ($movimientosPosteriores->isNotEmpty()) {
                $docs = $movimientosPosteriores->map(function ($m) {
                    $tipo = match($m->tipo_movimiento) {
                        'INGRESO'  => 'Ingreso',
                        'SALIDA'   => 'Salida',
                        'TRASPASO' => 'Traspaso',
                        'EXTORNO'  => 'Extorno',
                        default    => $m->tipo_movimiento,
                    };
                    return "{$tipo} #{$m->numero_documento} ({$m->fecha_movimiento})";
                })->implode(', ');

                return back()->with('error', "No se puede eliminar el ajuste porque tiene {$movimientosPosteriores->count()} movimiento(s) posterior(es) que dependen de este saldo: {$docs}. Puede extornar el ajuste desde la sección de Extornos para revertir su efecto.");
            }

            $saldoAnterior = DB::table('kardex')
                ->where('codigo_producto', $ajuste->codigo_producto)
                ->where('codigo_almacen', $ajuste->codigo_almacen)
                ->where('id_kardex', '<', $id)
                ->orderBy('id_kardex', 'desc')
                ->value('cantidad_saldo') ?? 0;

            DB::table('inventario')
                ->where('codigo_producto', $ajuste->codigo_producto)
                ->where('codigo_almacen', $ajuste->codigo_almacen)
                ->update(['stock_actual' => $saldoAnterior, 'fecha_ultimo_movimiento' => now(), 'usuario_ultimo_movimiento' => Auth::id()]);

            DB::table('kardex')->where('id_kardex', $id)->delete();

            // Recalcular costos para este producto/almacen
            app(KardexService::class)->recalcular(
                $ajuste->codigo_producto,
                $ajuste->codigo_almacen
            );

            DB::commit();
            return redirect()->route('inventario.ajuste.lista')->with('success', 'Ajuste eliminado correctamente y stock revertido.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }

    // 5. EXTORNAR AJUSTE
    public function extornarAjuste(Request $request, $id) {
        if ($request->confirmacion !== 'ANULAR') return back()->with('error', 'Palabra incorrecta.');
        try {
            DB::beginTransaction();
            $mov = MovimientoInventario::findOrFail($id);
            
            $stock = Inventario::where('codigo_producto', $mov->codigo_producto)
                               ->where('codigo_almacen', $mov->codigo_almacen)
                               ->first(); 
            
            $nuevo_stock = $stock->stock_actual - $mov->cantidad;
            if ($nuevo_stock < 0) throw new \Exception("El stock no puede quedar en negativo.");
            
            $stock->update(['stock_actual' => $nuevo_stock]);
            $mov->update(['estado' => 0, 'observaciones' => $mov->observaciones . ' [EXTORNADO]']);
            
            DB::commit();
            return back()->with('success', 'Extorno realizado.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error en extorno: ' . $e->getMessage());
        }
    }

    // =========================================================
    // MÓDULO DE EXTORNOS (AUDITORÍA)
    // =========================================================

    public function extornos(Request $request) {
        $query = DB::table('kardex')
            ->join('producto', 'kardex.codigo_producto', '=', 'producto.codigo')
            ->join('almacen', 'kardex.codigo_almacen', '=', 'almacen.codigo_almacen')
            ->select('kardex.*', 'producto.descripcion as producto', 'almacen.descripcion as almacen')
            // Criterio 1: No mostrar movimientos que ya son extornos
            ->where('kardex.tipo_movimiento', '!=', 'EXTORNO')
            // Criterio 2: No mostrar movimientos que ya fueron extornados
            ->where(function($q) {
                $q->whereNull('kardex.observaciones')
                  ->orWhere('kardex.observaciones', 'NOT LIKE', '%[EXTORNADO]%');
            })
            ->orderBy('kardex.fecha_movimiento', 'desc');

        if ($request->search) {
            $query->where('kardex.numero_documento', 'LIKE', "%{$request->search}%")
                  ->orWhere('producto.descripcion', 'LIKE', "%{$request->search}%");
        }

        $movimientos = $query->paginate(10);
        return view('inventario.extornos', compact('movimientos'));
    }

    public function procesarExtorno(Request $request, $id) {
        if ($request->confirmacion !== 'EXTORNAR') {
            return back()->with('error', 'Palabra de seguridad incorrecta. El movimiento no fue extornado.');
        }

        try {
            DB::beginTransaction();

            // 1. Obtener el movimiento original
            $movimientoOriginal = DB::table('kardex')->where('id_kardex', $id)->first();
            if (!$movimientoOriginal) {
                $movimientoOriginal = DB::table('kardex')->where('id', $id)->first();
            }
            
            if (!$movimientoOriginal) throw new \Exception("Movimiento no encontrado en la base de datos.");

            // 2. Bloquear y consultar el stock actual
            $registroInventario = DB::table('inventario')
                ->where('codigo_producto', $movimientoOriginal->codigo_producto)
                ->where('codigo_almacen', $movimientoOriginal->codigo_almacen)
                ->lockForUpdate()
                ->first();

            $stock_actual = $registroInventario ? $registroInventario->stock_actual : 0;
            $nuevo_saldo = 0;

            // 3. APLICAR CRITERIOS DE REVERSIÓN (Stock)
            if ($movimientoOriginal->cantidad_entrada > 0) {
                if ($stock_actual < $movimientoOriginal->cantidad_entrada) {
                    throw new \Exception("Imposible extornar. El stock actual (" . number_format($stock_actual, 2) . ") es menor a la cantidad ingresada (" . number_format($movimientoOriginal->cantidad_entrada, 2) . ").");
                }
                $nuevo_saldo = $stock_actual - $movimientoOriginal->cantidad_entrada;
            } else {
                $nuevo_saldo = $stock_actual + $movimientoOriginal->cantidad_salida;
            }

            // Obtener costo promedio actual para el extorno
            $ultimoKardex = DB::table('kardex')
                ->where('codigo_producto', $movimientoOriginal->codigo_producto)
                ->where('codigo_almacen', $movimientoOriginal->codigo_almacen)
                ->orderBy('fecha_movimiento', 'desc')
                ->orderBy('id_kardex', 'desc')
                ->first();
            $costoPromedioActual = $ultimoKardex?->costo_promedio ?? 0;

            $cantEntradaExtorno = $movimientoOriginal->cantidad_salida > 0 ? $movimientoOriginal->cantidad_salida : 0;
            $cantSalidaExtorno  = $movimientoOriginal->cantidad_entrada > 0 ? $movimientoOriginal->cantidad_entrada : 0;

            // Calcular costos para el extorno usando KardexService
            $kardexService = app(KardexService::class);
            $costos = $kardexService->calcularCostos(
                $movimientoOriginal->codigo_producto,
                $movimientoOriginal->codigo_almacen,
                $cantEntradaExtorno, $costoPromedioActual,
                $cantSalidaExtorno, $nuevo_saldo
            );

            // 4. Actualizar el stock físico
            DB::table('inventario')->updateOrInsert(
                ['codigo_producto' => $movimientoOriginal->codigo_producto, 'codigo_almacen' => $movimientoOriginal->codigo_almacen],
                [
                    'stock_actual' => $nuevo_saldo,
                    'costo_promedio' => $costos['costo_promedio'],
                    'fecha_ultimo_movimiento' => now(),
                    'usuario_ultimo_movimiento' => Auth::id()
                ]
            );

            // 5. Registrar el movimiento de EXTORNO en el Kardex
            DB::table('kardex')->insert([
                'codigo_almacen'   => $movimientoOriginal->codigo_almacen,
                'codigo_producto'  => $movimientoOriginal->codigo_producto,
                'fecha_movimiento' => now(),
                'tipo_movimiento'  => 'EXTORNO',
                'documento'        => 'EXT',
                'numero_documento' => 'REV-' . $movimientoOriginal->numero_documento,
                'cantidad_entrada' => $cantEntradaExtorno,
                'costo_entrada'    => $costos['costo_entrada'],
                'total_entrada'    => $costos['total_entrada'],
                'cantidad_salida'  => $cantSalidaExtorno,
                'costo_salida'     => $costos['costo_salida'],
                'total_salida'     => $costos['total_salida'],
                'cantidad_saldo'   => $costos['cantidad_saldo'],
                'costo_promedio'   => $costos['costo_promedio'],
                'total_saldo'      => $costos['total_saldo'],
                'observaciones'    => "Extorno de DOC: " . $movimientoOriginal->numero_documento . " | Motivo: " . $request->motivo,
                'usuario_registro' => Auth::id()
            ]);

            // 6. Marcar el original como EXTORNADO
            $pk = isset($movimientoOriginal->id_kardex) ? 'id_kardex' : 'id';
            DB::table('kardex')
                ->where($pk, $id)
                ->update(['observaciones' => DB::raw("CONCAT(COALESCE(observaciones, ''), ' [EXTORNADO]')")]);

            // =========================================================
            // 7. VINCULACIÓN: DEVOLVER COMPRA A ESTADO PENDIENTE
            // =========================================================
            // Buscamos la compra que coincida con el número de documento del Kardex
            // numero_documento en Kardex es 'SERIE-CORRELATIVO' (Ej: F001-123)
            $docKardex = $movimientoOriginal->numero_documento;

            $compraAfectada = Compra::whereRaw("CONCAT(serie_documento, '-', numero_documento) = ?", [$docKardex])
                ->first();

            if ($compraAfectada && $compraAfectada->estado === 'RECIBIDA') {
                $compraAfectada->update(['estado' => 'PENDIENTE']);
            }
            // =========================================================

            DB::commit();
            return redirect()->route('inventario.extornos')->with('success', 'Movimiento extornado. La compra asociada ha vuelto a estar PENDIENTE para su nueva recepción.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}