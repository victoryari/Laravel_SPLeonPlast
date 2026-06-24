<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Merma, Producto, Almacen, ParametroSistema};
use Illuminate\Support\Facades\{DB, Auth};
use App\Services\KardexService;

class MermaController extends Controller
{
    public function index(Request $request)
    {
        $query = Merma::with(['producto', 'almacen', 'usuarioRegistro']);
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('codigo_producto', 'like', "%{$request->search}%")
                  ->orWhere('descripcion_producto', 'like', "%{$request->search}%");
            });
        }

        if ($request->fecha) {
            $query->whereDate('created_at', $request->fecha);
        }

        $mermas = $query->orderBy('created_at', 'desc')->paginate(10);
        return view('mermas.index', compact('mermas', 'request'));
    }

    public function create()
    {
        $almacenes = Almacen::where('activo', 1)->get();
        
        $ordenes = DB::table('orden_produccion_global')
            ->where('activo', 1)
            ->select('idop', 'codigo_op', 'descripcion_producto_proceso', 'estado')
            ->orderBy('idop', 'desc')
            ->get();
            
        return view('mermas.create', compact('almacenes', 'ordenes'));
    }

    public function getProcesosPorOP(Request $request)
    {
        $idop = $request->idop;
        
        $procesos = DB::table('orden_proceso')
            ->where('idop', $idop)
            ->select('id', 'descripcion_proceso', 'estado_avance', 'observaciones')
            ->orderBy('secuencia')
            ->get();
            
        foreach ($procesos as $p) {
            $componentes = DB::table('componentes_orden_produccion_global')
                ->where('id_proceso', $p->id)
                ->where(function($query) {
                    $query->where('estado', 1)->orWhereNull('estado');
                })
                ->get(['descripcion_formula_produccion']);
                
            $formulas = $componentes->pluck('descripcion_formula_produccion')
                ->filter(function($val) { return !empty($val) && $val != 'N/A'; })
                ->unique();
                
            $extra = '';
            if ($formulas->count() > 0) {
                $extra = $formulas->implode(' / ');
            } elseif (!empty($p->observaciones)) {
                $extra = $p->observaciones;
            }
            
            $p->descripcion_completa = $p->descripcion_proceso . ' (' . $p->estado_avance . ')';
            if ($extra) {
                $p->descripcion_completa .= ' - ' . $extra;
            }
        }
            
        return response()->json($procesos);
    }

    public function getComponentesEnsamblado(Request $request)
    {
        $idop = $request->idop;
        $id_proceso = $request->id_proceso;
        $codigo_almacen = $request->codigo_almacen;

        $componentes = DB::table('componentes_orden_produccion_global as c')
            ->join('producto as p', 'c.codigo_producto', '=', 'p.codigo')
            ->join('inventario as i', function($join) use ($codigo_almacen) {
                $join->on('c.codigo_producto', '=', 'i.codigo_producto')
                     ->where('i.codigo_almacen', '=', $codigo_almacen)
                     ->where('i.stock_actual', '>', 0);
            })
            ->where('c.idop', $idop)
            ->where('c.id_proceso', $id_proceso)
            ->where('c.estado', 1)
            ->select(
                'c.codigo_producto',
                'p.descripcion',
                'i.stock_actual',
                'c.codigo_unidad_medida',
                DB::raw('SUM(c.cantidad) as cantidad_total')
            )
            ->groupBy('c.codigo_producto', 'p.descripcion', 'i.stock_actual', 'c.codigo_unidad_medida')
            ->get();

        return response()->json($componentes);
    }

    public function getProductosPorOP(Request $request)
    {
        $idop = $request->idop;
        $id_proceso = $request->id_proceso;
        
        $productos = DB::table('inventario')
            ->join('producto', 'inventario.codigo_producto', '=', 'producto.codigo')
            ->join('produccion_ingresos_proceso', function($join) use ($idop, $id_proceso) {
                $join->on('produccion_ingresos_proceso.codigo_producto_proceso', '=', 'inventario.codigo_producto')
                     ->on('produccion_ingresos_proceso.codigo_almacen', '=', 'inventario.codigo_almacen')
                     ->where('produccion_ingresos_proceso.idop', '=', $idop);
                
                if ($id_proceso) {
                    $join->where('produccion_ingresos_proceso.id_proceso', '=', $id_proceso);
                }
            })
            // Se elimina la restricción de stock > 0 porque la merma descuenta de la materia prima, 
            // no del producto generado (el cual pudo haber sido transferido a otro almacén).
            ->select(
                'producto.codigo', 
                'producto.descripcion', 
                'inventario.codigo_almacen', 
                'inventario.stock_actual', 
                'inventario.costo_promedio'
            )
            ->distinct()
            ->get();

        return response()->json($productos);
    }

    public function store(Request $request, KardexService $kardexService)
    {
        $request->validate([
            'id_orden_produccion' => 'required|integer',
            'codigo_producto' => 'required|exists:producto,codigo',
            'codigo_almacen' => 'required|exists:almacen,codigo_almacen',
            'cantidad_pura' => 'nullable|numeric|min:0',
            'cantidad_recuperada' => 'nullable|numeric|min:0',
            'motivo' => 'nullable|string|max:255',
            'es_ensamblado' => 'nullable|in:0,1',
            'componentes' => 'nullable|array'
        ]);

        $es_ensamblado = $request->es_ensamblado === '1';

        if (!$es_ensamblado) {
            $pura = (float) $request->cantidad_pura;
            $recuperada = (float) $request->cantidad_recuperada;
            $cantidadTotalMerma = $pura + $recuperada;

            if ($cantidadTotalMerma <= 0) {
                return back()->with('error', 'Debe ingresar al menos una cantidad mayor a 0.');
            }
        } else {
            $hasMerma = false;
            if ($request->componentes) {
                foreach ($request->componentes as $cod => $cant) {
                    $p = (float)($cant['pura'] ?? 0);
                    $r = (float)($cant['recuperada'] ?? 0);
                    if ($p > 0 || $r > 0) $hasMerma = true;
                }
            }
            if (!$hasMerma) {
                return back()->with('error', 'Debe ingresar al menos una cantidad mayor a 0 en algún componente.');
            }
        }

        if (str_starts_with($request->codigo_producto, 'REC-')) {
            return back()->with('error', 'No se puede registrar merma de un producto ya recuperado (REC-).');
        }

        try {
            DB::beginTransaction();
            if ($es_ensamblado) {
                $totalPuraGlobal = 0;
                $totalRecuperadaGlobal = 0;
                $totalValorSalidaGlobal = 0;
                $detallesKardex = [];

                foreach ($request->componentes as $cod => $cant) {
                    $pura = (float)($cant['pura'] ?? 0);
                    $recuperada = (float)($cant['recuperada'] ?? 0);
                    $cantidadTotal = $pura + $recuperada;

                    if ($cantidadTotal <= 0) continue;

                    $compProducto = Producto::findOrFail($cod);
                    $inv = DB::table('inventario')
                        ->where('codigo_producto', $cod)
                        ->where('codigo_almacen', $request->codigo_almacen)
                        ->lockForUpdate()
                        ->first();

                    if (!$inv || $inv->stock_actual < $cantidadTotal) {
                        throw new \Exception("Stock insuficiente del componente {$cod} ({$compProducto->descripcion}) en el almacén {$request->codigo_almacen}. Se requieren {$cantidadTotal}.");
                    }

                    $costoSalida = $inv->costo_promedio;
                    $totalSalida = round($cantidadTotal * $costoSalida, 2);

                    $totalPuraGlobal += $pura;
                    $totalRecuperadaGlobal += $recuperada;
                    $totalValorSalidaGlobal += $totalSalida;

                    $detallesKardex[] = [
                        'codigo' => $cod,
                        'cantidad' => $cantidadTotal,
                        'costo' => $costoSalida,
                        'total' => $totalSalida,
                        'inv' => $inv
                    ];
                }

                $cantidadTotalGlobal = $totalPuraGlobal + $totalRecuperadaGlobal;
                
                if ($cantidadTotalGlobal <= 0) {
                    throw new \Exception("Debe ingresar al menos una cantidad mayor a 0.");
                }

                $costoUnitarioGlobal = round($totalValorSalidaGlobal / $cantidadTotalGlobal, 6);

                $prodPrincipal = Producto::findOrFail($request->codigo_producto);
                $merma = Merma::create([
                    'id_orden_produccion' => $request->id_orden_produccion,
                    'codigo_producto' => $request->codigo_producto,
                    'descripcion_producto' => $prodPrincipal->descripcion,
                    'cantidad' => $cantidadTotalGlobal,
                    'costo_unitario' => $costoUnitarioGlobal,
                    'costo_total' => $totalValorSalidaGlobal,
                    'motivo' => $request->motivo,
                    'tipo_merma' => ($totalPuraGlobal > 0 && $totalRecuperadaGlobal > 0) ? 'MIXTO' : (($totalPuraGlobal > 0) ? 'PURA' : 'RECUPERABLE'),
                    'codigo_almacen' => $request->codigo_almacen,
                    'estado' => 'REGISTRADA',
                    'usuario_registro' => Auth::id()
                ]);

                $numeroDoc = 'MERMA-' . str_pad($merma->id_merma, 6, '0', STR_PAD_LEFT);

                foreach ($detallesKardex as $det) {
                    $nuevoStock = $det['inv']->stock_actual - $det['cantidad'];

                    DB::table('kardex')->insert([
                        'codigo_producto' => $det['codigo'],
                        'codigo_almacen' => $request->codigo_almacen,
                        'fecha_movimiento' => now(),
                        'tipo_movimiento' => 'SALIDA',
                        'documento' => 'MERMA',
                        'numero_documento' => $numeroDoc,
                        'cantidad_entrada' => 0,
                        'costo_entrada' => 0,
                        'total_entrada' => 0,
                        'cantidad_salida' => $det['cantidad'],
                        'costo_salida' => $det['costo'],
                        'total_salida' => $det['total'],
                        'cantidad_saldo' => $nuevoStock,
                        'costo_promedio' => $det['inv']->costo_promedio,
                        'total_saldo' => $nuevoStock * $det['inv']->costo_promedio,
                        'observaciones' => "SALIDA PROPORCIONAL POR LIMPIEZA OP-{$request->id_orden_produccion} MERMA $numeroDoc",
                        'usuario_registro' => Auth::id()
                    ]);

                    DB::table('inventario')
                        ->where('id_inventario', $det['inv']->id_inventario)
                        ->update([
                            'stock_actual' => $nuevoStock,
                            'fecha_ultimo_movimiento' => now(),
                            'usuario_ultimo_movimiento' => Auth::id()
                        ]);
                }

                if ($totalRecuperadaGlobal > 0) {
                    $param = ParametroSistema::where('codigo_parametro', 'PORCENTAJE_COSTO_RECICLADO')->lockForUpdate()->first();
                    $porcentaje = $param ? (float) $param->valor : 0.8;
                    
                    $costoRecicladoGlobal = $costoUnitarioGlobal * $porcentaje;
                    $totalEntradaRecGlobal = round($totalRecuperadaGlobal * $costoRecicladoGlobal, 2);

                    $codRecuperado = 'REC-' . $request->codigo_producto;

                    $prodRecuperado = Producto::firstOrCreate(
                        ['codigo' => $codRecuperado],
                        [
                            'descripcion' => 'RECUPERADO - ' . $prodPrincipal->descripcion,
                            'codigo_tipo_producto' => $prodPrincipal->codigo_tipo_producto,
                            'codigo_unidad_medida' => $prodPrincipal->codigo_unidad_medida,
                            'estado' => 1
                        ]
                    );

                    $invRec = DB::table('inventario')
                        ->where('codigo_producto', $codRecuperado)
                        ->where('codigo_almacen', $request->codigo_almacen)
                        ->lockForUpdate()
                        ->first();

                    if ($invRec) {
                        $nuevoStockRec = $invRec->stock_actual + $totalRecuperadaGlobal;
                        $nuevoTotalValor = ($invRec->stock_actual * $invRec->costo_promedio) + $totalEntradaRecGlobal;
                        $nuevoCostoPromedio = $nuevoStockRec > 0 ? round($nuevoTotalValor / $nuevoStockRec, 6) : 0;

                        DB::table('inventario')->where('id_inventario', $invRec->id_inventario)->update([
                            'stock_actual' => $nuevoStockRec,
                            'costo_promedio' => $nuevoCostoPromedio,
                            'fecha_ultimo_movimiento' => now(),
                            'usuario_ultimo_movimiento' => Auth::id()
                        ]);
                    } else {
                        $nuevoStockRec = $totalRecuperadaGlobal;
                        $nuevoCostoPromedio = round($totalEntradaRecGlobal / $totalRecuperadaGlobal, 6);

                        DB::table('inventario')->insert([
                            'codigo_producto' => $codRecuperado,
                            'codigo_almacen' => $request->codigo_almacen,
                            'stock_actual' => $nuevoStockRec,
                            'costo_promedio' => $nuevoCostoPromedio,
                            'fecha_ultimo_movimiento' => now(),
                            'usuario_ultimo_movimiento' => Auth::id()
                        ]);
                    }

                    DB::table('kardex')->insert([
                        'codigo_producto' => $codRecuperado,
                        'codigo_almacen' => $request->codigo_almacen,
                        'fecha_movimiento' => now(),
                        'tipo_movimiento' => 'INGRESO',
                        'documento' => 'MERMA_RECUPERADA',
                        'numero_documento' => $numeroDoc,
                        'cantidad_entrada' => $totalRecuperadaGlobal,
                        'costo_entrada' => $costoRecicladoGlobal,
                        'total_entrada' => $totalEntradaRecGlobal,
                        'cantidad_salida' => 0,
                        'costo_salida' => 0,
                        'total_salida' => 0,
                        'cantidad_saldo' => $nuevoStockRec,
                        'costo_promedio' => $nuevoCostoPromedio,
                        'total_saldo' => $nuevoStockRec * $nuevoCostoPromedio,
                        'observaciones' => "RECUPERACIÓN POR LIMPIEZA OP-{$request->id_orden_produccion} MERMA $numeroDoc",
                        'usuario_registro' => Auth::id()
                    ]);
                }

                DB::commit();
                return redirect()->route('mermas.index')->with('success', 'Merma de Ensamblado registrada exitosamente.');
            }

            // ================== FLUJO INYECTADO (ESTÁNDAR) ==================
            
            $productoOrigen = Producto::findOrFail($request->codigo_producto);

            // Obtener el proceso de la OP que generó este producto
            $procesoIngreso = DB::table('produccion_ingresos_proceso')
                ->where('idop', $request->id_orden_produccion)
                ->where('codigo_producto_proceso', $request->codigo_producto)
                ->first();

            if (!$procesoIngreso) {
                return back()->with('error', 'No se encontró el proceso de producción para este producto en la OP seleccionada.');
            }

            $cantidadPlanificadaPEP = (float) $procesoIngreso->cantidad;
            if ($cantidadPlanificadaPEP <= 0) {
                return back()->with('error', 'La cantidad planificada del proceso es inválida.');
            }

            // Factor de consumo proporcional
            $factor = $cantidadTotalMerma / $cantidadPlanificadaPEP;

            // Obtener las materias primas del proceso (agrupadas para evitar duplicados en el Kardex)
            $componentes = DB::table('componentes_orden_produccion_global')
                ->where('idop', $request->id_orden_produccion)
                ->where('id_proceso', $procesoIngreso->id_proceso)
                ->whereIn('codigo_tipo_producto', ['MTP', 'MAT', 'INS'])
                ->where('estado', 1)
                ->selectRaw('codigo_producto, MAX(descripcion_producto) as descripcion_producto, SUM(cantidad) as cantidad')
                ->groupBy('codigo_producto')
                ->get();

            if ($componentes->isEmpty()) {
                return back()->with('error', 'No hay materias primas configuradas en este proceso para calcular el consumo de la merma.');
            }

            $costoTotalMerma = 0;

            // 1. Crear Merma Principal
            $merma = Merma::create([
                'id_orden_produccion' => $request->id_orden_produccion,
                'codigo_producto' => $request->codigo_producto,
                'descripcion_producto' => $productoOrigen->descripcion,
                'cantidad' => $cantidadTotalMerma,
                'costo_unitario' => 0, // Se actualizará al final
                'costo_total' => 0,
                'motivo' => $request->motivo,
                'tipo_merma' => ($pura > 0 && $recuperada > 0) ? 'MIXTO' : (($pura > 0) ? 'PURA' : 'RECUPERABLE'),
                'codigo_almacen' => $request->codigo_almacen,
                'estado' => 'REGISTRADA',
                'usuario_registro' => Auth::id()
            ]);

            $numeroDoc = 'MERMA-' . str_pad($merma->id_merma, 6, '0', STR_PAD_LEFT);

            // 2. Consumir cada componente (Materia Prima)
            foreach ($componentes as $comp) {
                $cantidadConsumir = round($comp->cantidad * $factor, 6);
                
                if ($cantidadConsumir <= 0) continue;

                $inv = DB::table('inventario')
                    ->where('codigo_producto', $comp->codigo_producto)
                    ->where('codigo_almacen', $request->codigo_almacen)
                    ->lockForUpdate()
                    ->first();

                if (!$inv || $inv->stock_actual < $cantidadConsumir) {
                    throw new \Exception("Stock insuficiente de materia prima {$comp->codigo_producto} ({$comp->descripcion_producto}) en el almacén {$request->codigo_almacen} para cubrir la merma. Se requieren {$cantidadConsumir}.");
                }

                $costoSalida = $inv->costo_promedio;
                $totalSalida = round($cantidadConsumir * $costoSalida, 2);
                $costoTotalMerma += $totalSalida;
                
                $nuevoStock = $inv->stock_actual - $cantidadConsumir;

                // SALIDA de materia prima
                DB::table('kardex')->insert([
                    'codigo_producto' => $comp->codigo_producto,
                    'codigo_almacen' => $request->codigo_almacen,
                    'fecha_movimiento' => now(),
                    'tipo_movimiento' => 'SALIDA',
                    'documento' => 'MERMA',
                    'numero_documento' => $numeroDoc,
                    'cantidad_entrada' => 0,
                    'costo_entrada' => 0,
                    'total_entrada' => 0,
                    'cantidad_salida' => $cantidadConsumir,
                    'costo_salida' => $costoSalida,
                    'total_salida' => $totalSalida,
                    'cantidad_saldo' => $nuevoStock,
                    'costo_promedio' => $inv->costo_promedio,
                    'total_saldo' => $nuevoStock * $inv->costo_promedio,
                    'observaciones' => "CONSUMO VIRGEN POR MERMA OP-{$request->id_orden_produccion}",
                    'usuario_registro' => Auth::id()
                ]);

                DB::table('inventario')
                    ->where('id_inventario', $inv->id_inventario)
                    ->update([
                        'stock_actual' => $nuevoStock,
                        'fecha_ultimo_movimiento' => now(),
                        'usuario_ultimo_movimiento' => Auth::id()
                    ]);
            }

            $costoUnitarioMerma = $cantidadTotalMerma > 0 ? round($costoTotalMerma / $cantidadTotalMerma, 6) : 0;

            // Actualizar costo de la merma
            $merma->update([
                'costo_unitario' => $costoUnitarioMerma,
                'costo_total' => $costoTotalMerma
            ]);

            // 3. ENTRADA de producto recuperado (si aplica)
            if ($recuperada > 0) {
                $param = ParametroSistema::where('codigo_parametro', 'PORCENTAJE_COSTO_RECICLADO')->lockForUpdate()->first();
                $porcentaje = $param ? (float) $param->valor : 0.8;
                
                $costoReciclado = $costoUnitarioMerma * $porcentaje;
                $totalEntradaRec = round($recuperada * $costoReciclado, 2);

                $esMolido = $request->has('es_molido');
                
                if ($esMolido) {
                    $partes = explode('-', $request->codigo_producto, 2);
                    $codigoSufijo = count($partes) > 1 ? $partes[1] : $request->codigo_producto;
                    $codRecuperado = 'MO07-' . $codigoSufijo;
                    
                    $descLimpia = str_replace(['CASCARA', 'COLADA'], '', $productoOrigen->descripcion);
                    $descLimpia = str_replace(['INYECTADO '], '', $descLimpia);
                    $descRecuperado = 'MOLIDO RECICLADO ' . trim($descLimpia);
                } else {
                    $codRecuperado = 'REC-' . $request->codigo_producto;
                    $descRecuperado = 'RECUPERADO - ' . $productoOrigen->descripcion;
                }

                $prodRecuperado = Producto::firstOrCreate(
                    ['codigo' => $codRecuperado],
                    [
                        'descripcion' => $descRecuperado,
                        'codigo_tipo_producto' => $productoOrigen->codigo_tipo_producto,
                        'codigo_unidad_medida' => $productoOrigen->codigo_unidad_medida,
                        'estado' => 1
                    ]
                );
                
                DB::table('inventario')->updateOrInsert(
                    ['codigo_producto' => $codRecuperado, 'codigo_almacen' => $request->codigo_almacen],
                    [
                        'stock_minimo' => 0,
                        'stock_maximo' => 0
                    ]
                );
                
                $invRec = DB::table('inventario')
                    ->where('codigo_producto', $codRecuperado)
                    ->where('codigo_almacen', $request->codigo_almacen)
                    ->lockForUpdate()
                    ->first();
                
                $saldoAnteriorRec = $invRec->stock_actual ?? 0;
                $costoPromAnteriorRec = $invRec->costo_promedio ?? 0;
                $nuevoSaldoRec = $saldoAnteriorRec + $recuperada;
                $nuevoTotalSaldoRec = ($saldoAnteriorRec * $costoPromAnteriorRec) + $totalEntradaRec;
                $nuevoCostoPromRec = $nuevoSaldoRec > 0 ? round($nuevoTotalSaldoRec / $nuevoSaldoRec, 6) : 0;

                DB::table('kardex')->insert([
                    'codigo_producto' => $codRecuperado,
                    'codigo_almacen' => $request->codigo_almacen,
                    'fecha_movimiento' => now(),
                    'tipo_movimiento' => 'INGRESO',
                    'documento' => 'MERMA',
                    'numero_documento' => $numeroDoc,
                    'cantidad_entrada' => $recuperada,
                    'costo_entrada' => $costoReciclado,
                    'total_entrada' => $totalEntradaRec,
                    'cantidad_salida' => 0,
                    'costo_salida' => 0,
                    'total_salida' => 0,
                    'cantidad_saldo' => $nuevoSaldoRec,
                    'costo_promedio' => $nuevoCostoPromRec,
                    'total_saldo' => round($nuevoSaldoRec * $nuevoCostoPromRec, 6),
                    'observaciones' => "INGRESO DE MATERIAL RECUPERADO DE MERMA OP-{$request->id_orden_produccion}",
                    'usuario_registro' => Auth::id()
                ]);

                DB::table('inventario')
                    ->where('codigo_producto', $codRecuperado)
                    ->where('codigo_almacen', $request->codigo_almacen)
                    ->update([
                        'stock_actual' => $nuevoSaldoRec,
                        'costo_promedio' => $nuevoCostoPromRec,
                        'fecha_ultimo_movimiento' => now(),
                        'usuario_ultimo_movimiento' => Auth::id()
                    ]);
            }

            DB::commit();
            return redirect()->route('mermas.index')->with('success', 'Merma registrada exitosamente consumiendo materiales vírgenes de la OP.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al registrar merma: ' . $e->getMessage());
        }
    }

    public function anular($id)
    {
        try {
            DB::beginTransaction();
            
            $merma = Merma::findOrFail($id);
            
            if ($merma->estado === 'ANULADA') {
                return back()->with('error', 'La merma ya se encuentra anulada.');
            }
            
            $numeroDoc = 'MERMA-' . str_pad($merma->id_merma, 6, '0', STR_PAD_LEFT);
            
            $movimientos = DB::table('kardex')
                ->where('numero_documento', $numeroDoc)
                ->orderBy('id_kardex', 'desc')
                ->get();
                
            foreach ($movimientos as $mov) {
                $inv = DB::table('inventario')
                    ->where('codigo_producto', $mov->codigo_producto)
                    ->where('codigo_almacen', $mov->codigo_almacen)
                    ->lockForUpdate()
                    ->first();
                    
                if (!$inv) continue;
                
                if ($mov->tipo_movimiento === 'SALIDA') {
                    // Revertir salida -> sumar stock
                    $nuevoStock = $inv->stock_actual + $mov->cantidad_salida;
                } else {
                    // Revertir ingreso -> restar stock
                    $nuevoStock = $inv->stock_actual - $mov->cantidad_entrada;
                    if ($nuevoStock < 0) {
                        throw new \Exception("No se puede anular porque el stock del producto {$mov->codigo_producto} quedaría en negativo.");
                    }
                }
                
                // Registrar el movimiento de extorno
                DB::table('kardex')->insert([
                    'codigo_producto' => $mov->codigo_producto,
                    'codigo_almacen' => $mov->codigo_almacen,
                    'fecha_movimiento' => now(),
                    'tipo_movimiento' => 'EXTORNO',
                    'documento' => 'ANULACION MERMA',
                    'numero_documento' => 'ANUL-' . $numeroDoc,
                    'cantidad_entrada' => $mov->tipo_movimiento === 'SALIDA' ? $mov->cantidad_salida : 0,
                    'costo_entrada' => $mov->tipo_movimiento === 'SALIDA' ? $mov->costo_salida : 0,
                    'total_entrada' => $mov->tipo_movimiento === 'SALIDA' ? $mov->total_salida : 0,
                    'cantidad_salida' => $mov->tipo_movimiento === 'INGRESO' ? $mov->cantidad_entrada : 0,
                    'costo_salida' => $mov->tipo_movimiento === 'INGRESO' ? $mov->costo_entrada : 0,
                    'total_salida' => $mov->tipo_movimiento === 'INGRESO' ? $mov->total_entrada : 0,
                    'cantidad_saldo' => $nuevoStock,
                    'costo_promedio' => $inv->costo_promedio,
                    'total_saldo' => $nuevoStock * $inv->costo_promedio,
                    'observaciones' => "EXTORNO POR ANULACION DE MERMA OP-{$merma->id_orden_produccion}",
                    'usuario_registro' => Auth::id()
                ]);
                
                DB::table('inventario')
                    ->where('id_inventario', $inv->id_inventario)
                    ->update([
                        'stock_actual' => $nuevoStock,
                        'fecha_ultimo_movimiento' => now(),
                        'usuario_ultimo_movimiento' => Auth::id()
                    ]);
            }
            
            $merma->update(['estado' => 'ANULADA']);
            
            DB::commit();
            return redirect()->route('mermas.index')->with('success', 'La merma ha sido anulada y los movimientos de inventario han sido extornados.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al anular la merma: ' . $e->getMessage());
        }
    }
}