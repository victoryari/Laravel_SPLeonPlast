<?php

namespace App\Http\Controllers;

use App\Models\{Inventario, MovimientoInventario, Compra, Almacen, Producto, ProduccionIngresoProceso};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Auth};

class InventarioController extends Controller
{
    // 1. EXISTENCIAS (Stock Actual)
    public function index(Request $request) {
        $query = DB::table('inventario')
            ->join('producto', 'inventario.codigo_producto', '=', 'producto.codigo')
            ->join('almacen', 'inventario.codigo_almacen', '=', 'almacen.codigo_almacen')
            ->select(
                'inventario.codigo_producto', 
                'inventario.codigo_almacen',
                'producto.descripcion as producto',
                'almacen.descripcion as almacen',
                'inventario.stock_actual',
                'inventario.fecha_ultimo_movimiento'
            );

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

        $almacenes = Almacen::where('activo', 1)->get();
        return view('inventario.recepciones', compact('comprasPendientes', 'produccionPendientes', 'almacenes'));
    }

    // 2.1 PROCESAR RECEPCIÓN
    public function procesarRecepcion(Request $request, $id) {
        $compra = Compra::with('detalles')->findOrFail($id);

        try {
            DB::beginTransaction();

            foreach ($request->items as $id_detalle => $data) {
                $cantidad_recibida = floatval($data['cantidad']);
                $codigo_producto = $data['codigo_producto'];
                $codigo_almacen = $data['codigo_almacen'];
                $precio_unitario = floatval($data['precio'] ?? 0);

                // Bloqueo de fila para evitar condiciones de carrera
                $registroInventario = DB::table('inventario')
                    ->where('codigo_producto', $codigo_producto)
                    ->where('codigo_almacen', $codigo_almacen)
                    ->lockForUpdate()
                    ->first();

                $saldo_anterior = $registroInventario ? $registroInventario->stock_actual : 0;
                $nuevo_saldo = $saldo_anterior + $cantidad_recibida;

                // Actualizar inventario general
                DB::table('inventario')->updateOrInsert(
                    ['codigo_producto' => $codigo_producto, 'codigo_almacen' => $codigo_almacen],
                    [
                        'stock_actual' => $nuevo_saldo,
                        'fecha_ultimo_movimiento' => now(),
                        'usuario_ultimo_movimiento' => Auth::id()
                    ]
                );

                // Insertar en Kardex si hubo ingreso real
                if ($cantidad_recibida > 0) {
                    DB::table('kardex')->insert([
                        'codigo_almacen'   => $codigo_almacen,
                        'codigo_producto'  => $codigo_producto,
                        'fecha_movimiento' => now(),
                        'tipo_movimiento'  => 'INGRESO',
                        'documento'        => $compra->tipo_documento,
                        'numero_documento' => $compra->serie_documento . '-' . $compra->numero_documento,
                        'cantidad_entrada' => $cantidad_recibida,
                        'costo_entrada'    => $precio_unitario,
                        'total_entrada'    => $cantidad_recibida * $precio_unitario,
                        'cantidad_salida'  => 0,
                        'costo_salida'     => 0,
                        'total_salida'     => 0,
                        'cantidad_saldo'   => $nuevo_saldo,
                        'costo_promedio'   => $precio_unitario,
                        'total_saldo'      => $nuevo_saldo * $precio_unitario,
                        'usuario_registro' => Auth::id()
                    ]);
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

            // Movimiento inventario
            DB::table('movimientos_inventario')->insert([
                'codigo_almacen' => $almacen_destino,
                'codigo_producto' => $codigo_prod,
                'lote' => $lote,
                'tipo_movimiento' => 'INGRESO',
                'cantidad' => $cantidad_real,
                'costo_unitario' => 0,
                'total' => 0,
                'documento_referencia' => $doc_ref,
                'numero_referencia' => $num_ref,
                'idop' => $idop,
                'observaciones' => $observacion_kardex,
                'usuario_movimiento' => $usuario_movimiento,
                'fecha_movimiento' => now(),
                'estado' => 1
            ]);

            // Actualizar Inventario General
            $registroInventario = DB::table('inventario')
                ->where('codigo_producto', $codigo_prod)
                ->where('codigo_almacen', $almacen_destino)
                ->where('lote', $lote)
                ->lockForUpdate()
                ->first();

            if ($registroInventario) {
                DB::table('inventario')->where('id_inventario', $registroInventario->id_inventario)->update([
                    'stock_actual' => $registroInventario->stock_actual + $cantidad_real,
                    'fecha_ultimo_movimiento' => now(),
                    'usuario_ultimo_movimiento' => $usuario_movimiento
                ]);
            } else {
                DB::table('inventario')->insert([
                    'codigo_producto' => $codigo_prod,
                    'codigo_almacen' => $almacen_destino,
                    'lote' => $lote,
                    'stock_actual' => $cantidad_real,
                    'stock_minimo' => 0,
                    'stock_maximo' => 0,
                    'costo_promedio' => 0,
                    'ultimo_costo' => 0,
                    'estado' => 1,
                    'fecha_ultimo_movimiento' => now(),
                    'usuario_ultimo_movimiento' => $usuario_movimiento
                ]);
            }

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

    // 3. KARDEX DETALLADO
    public function kardex(Request $request) {
        $movimientos = DB::table('kardex')
            ->join('producto', 'kardex.codigo_producto', '=', 'producto.codigo')
            ->select('kardex.*', 'producto.descripcion as producto')
            ->orderBy('fecha_movimiento', 'desc')
            ->paginate(10);
        return view('inventario.kardex', compact('movimientos'));
    }

    // 4. AJUSTE MANUAL
    public function ajuste() {
        $productos = Producto::where('estado', 1)->get();
        $almacenes = Almacen::where('activo', 1)->get();
        return view('inventario.ajuste', compact('productos', 'almacenes'));
    }

    public function storeAjuste(Request $request) {
        $request->validate([
            'codigo_producto' => 'required',
            'codigo_almacen'  => 'required',
            'cantidad'        => 'required|numeric|min:0.01',
            'tipo'            => 'required|in:INGRESO,SALIDA'
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

            DB::table('inventario')->updateOrInsert(
                ['codigo_producto' => $request->codigo_producto, 'codigo_almacen' => $request->codigo_almacen],
                ['stock_actual' => $nuevo_saldo, 'fecha_ultimo_movimiento' => now(), 'usuario_ultimo_movimiento' => Auth::id()]
            );

            DB::table('kardex')->insert([
                'codigo_almacen'   => $request->codigo_almacen,
                'codigo_producto'  => $request->codigo_producto,
                'fecha_movimiento' => now(),
                'tipo_movimiento'  => 'AJUSTE',
                'documento'        => 'TICKET',
                'numero_documento' => 'AJ-' . date('YmdHis'),
                'cantidad_entrada' => $request->tipo === 'INGRESO' ? $request->cantidad : 0,
                'cantidad_salida'  => $request->tipo === 'SALIDA' ? $request->cantidad : 0,
                'cantidad_saldo'   => $nuevo_saldo,
                'usuario_registro' => Auth::id()
            ]);

            DB::commit();
            return redirect()->route('inventario.index')->with('success', 'Ajuste procesado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
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

            // 4. Actualizar el stock físico
            DB::table('inventario')->updateOrInsert(
                ['codigo_producto' => $movimientoOriginal->codigo_producto, 'codigo_almacen' => $movimientoOriginal->codigo_almacen],
                ['stock_actual' => $nuevo_saldo, 'fecha_ultimo_movimiento' => now(), 'usuario_ultimo_movimiento' => Auth::id()]
            );

            // 5. Registrar el movimiento de EXTORNO en el Kardex
            DB::table('kardex')->insert([
                'codigo_almacen'   => $movimientoOriginal->codigo_almacen,
                'codigo_producto'  => $movimientoOriginal->codigo_producto,
                'fecha_movimiento' => now(),
                'tipo_movimiento'  => 'EXTORNO',
                'documento'        => 'EXT',
                'numero_documento' => 'REV-' . $movimientoOriginal->numero_documento,
                'cantidad_entrada' => $movimientoOriginal->cantidad_salida > 0 ? $movimientoOriginal->cantidad_salida : 0,
                'cantidad_salida'  => $movimientoOriginal->cantidad_entrada > 0 ? $movimientoOriginal->cantidad_entrada : 0,
                'cantidad_saldo'   => $nuevo_saldo,
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