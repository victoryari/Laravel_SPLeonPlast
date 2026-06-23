<?php

namespace App\Http\Controllers;

use App\Models\OrdenProduccion;
use App\Models\OrdenProceso;
use App\Models\ProcesoProduccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\ComponenteOrdenProduccion;

class OrdenProcesoController extends Controller
{
    public function index($idop)
    {
        $orden = OrdenProduccion::with('productoProceso')->where('idop', $idop)->where('activo', 1)->firstOrFail();
        
        $procesos = OrdenProceso::where('idop', $idop)
            ->where('estado', 1)
            ->orderBy('secuencia', 'asc')
            ->get();
            
        // Calculate components count per process
        foreach ($procesos as $proceso) {
            $componentes = DB::table('componentes_orden_produccion_global')
                ->where('id_proceso', $proceso->id)
                ->where(function($query) {
                    $query->where('estado', 1)->orWhereNull('estado');
                })
                ->get(['descripcion_producto', 'descripcion_color', 'descripcion_formula_produccion']);
                
            $proceso->total_componentes = $componentes->count();
            
            // Obtener el nombre de la fórmula única usada en este proceso
            $formulas = $componentes->pluck('descripcion_formula_produccion')
                ->filter(function($val) { return !empty($val) && $val != 'N/A'; })
                ->unique();
                
            if ($formulas->count() > 0) {
                $proceso->nombres_componentes = $formulas->implode(' / ');
            } else {
                $proceso->nombres_componentes = null;
            }
                
            // Fetch the process description (if not already stored) or use Eloquent relationships later
            $desc = DB::table('proceso_produccion')->where('codigo', $proceso->codigo_proceso)->value('descripcion');
            $proceso->proceso_desc = $desc ?? $proceso->descripcion_proceso;
        }
        
        return view('produccion.procesos.index', compact('orden', 'procesos'));
    }

    public function create($idop)
    {
        $orden = OrdenProduccion::with('productoProceso.rutas')->where('idop', $idop)->where('activo', 1)->firstOrFail();
        
        if ($orden->productoProceso && $orden->productoProceso->rutas->count() > 0) {
            $cat_procesos = $orden->productoProceso->rutas;
        } else {
            $cat_procesos = ProcesoProduccion::where('estado', 1)->get();
        }
        
        return view('produccion.procesos.create', compact('orden', 'cat_procesos'));
    }

    public function store(Request $request, $idop)
    {
        $request->validate([
            'codigo_proceso' => 'required|string',
            'observaciones' => 'nullable|string',
        ]);

        try {
            $procesoRef = ProcesoProduccion::findOrFail($request->codigo_proceso);
            
            $orden = OrdenProduccion::with('productoProceso.rutas')->where('idop', $idop)->where('activo', 1)->firstOrFail();

            if ($orden->productoProceso && $orden->productoProceso->rutas->count() > 0) {
                if (!$orden->productoProceso->rutas->contains('codigo', $request->codigo_proceso)) {
                    throw new \Exception('El proceso seleccionado no pertenece a la ruta de producción de este producto.');
                }
            }

            $max_seq = OrdenProceso::where('idop', $idop)->where('estado', 1)->max('secuencia');
            $secuencia_nueva = $max_seq ? $max_seq + 10 : 10;
            
            OrdenProceso::create([
                'idop' => $idop,
                'secuencia' => $secuencia_nueva,
                'codigo_proceso' => $request->codigo_proceso,
                'descripcion_proceso' => $procesoRef->descripcion,
                'observaciones' => $request->observaciones,
                'estado' => 1,
                'estado_avance' => 'PENDIENTE',
                'fecha_inicio' => $orden->fecha // As derived from legacy code
            ]);
            
            return redirect()->route('ordenes.procesos.index', $idop)->with('success', 'Proceso registrado correctamente.');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            
            $proceso = OrdenProceso::where('id', $id)->lockForUpdate()->firstOrFail();
            if ($proceso->estado_avance === 'COMPLETADO') {
                throw new \Exception("No se puede anular un proceso que ya está COMPLETADO.");
            }
            $orden_id = $proceso->idop;
            
            // 1. VALIDACIÓN DE BLOQUEO: Verificar si el PEP generado ya fue consumido
            $ingresos_generados = DB::table('produccion_ingresos_proceso')
                ->where('id_proceso', $id)
                ->whereIn('estado', ['PENDIENTE', 'APROBADO'])
                ->get();
                
            foreach ($ingresos_generados as $ing) {
                if ($ing->estado === 'APROBADO') {
                    $stock_actual = DB::table('inventario')
                        ->where('codigo_producto', $ing->codigo_producto_proceso)
                        ->where('lote', $ing->lote_produccion)
                        ->where('estado', 1)
                        ->value('stock_actual');
                        
                    if ($stock_actual !== null && round($stock_actual, 2) < round($ing->cantidad, 2)) {
                        throw new \Exception("El lote {$ing->lote_produccion} ya registra salidas. Anule consumos posteriores primero.");
                    }
                }
            }
            
            $numero_ref_prefijo = "OP-{$orden_id}-PROC-{$id}";
            $usuario_id = Auth::user()->id_usuario ?? 1; // Ajustar según el sistema de auth
            
            // 2. EXTORNO DE PRODUCTOS EN PROCESO (PEP)
            $todos_ingresos = DB::table('produccion_ingresos_proceso')
                ->where('id_proceso', $id)
                ->get();
                
            foreach ($todos_ingresos as $ing) {
                if ($ing->estado === 'APROBADO') {
                    DB::table('inventario')
                        ->where('codigo_producto', $ing->codigo_producto_proceso)
                        ->where('lote', $ing->lote_produccion)
                        ->update([
                            'stock_actual' => DB::raw("stock_actual - {$ing->cantidad}"),
                            'fecha_ultimo_movimiento' => now(),
                            'usuario_ultimo_movimiento' => $usuario_id
                        ]);
                        
                    $codigo_almacen = DB::table('inventario')
                        ->where('codigo_producto', $ing->codigo_producto_proceso)
                        ->where('lote', $ing->lote_produccion)
                        ->value('codigo_almacen');

                    $stockPEP = DB::table('inventario')
                        ->where('codigo_producto', $ing->codigo_producto_proceso)
                        ->where('lote', $ing->lote_produccion)
                        ->value('stock_actual') ?? 0;
                        
                    $movIdExt = DB::table('movimientos_inventario')->insertGetId([
                        'codigo_almacen' => $codigo_almacen,
                        'codigo_producto' => $ing->codigo_producto_proceso,
                        'lote' => $ing->lote_produccion,
                        'tipo_movimiento' => 'SALIDA',
                        'cantidad' => $ing->cantidad,
                        'costo_unitario' => 0,
                        'total' => 0,
                        'documento_referencia' => 'EXTORNO_PROD',
                        'numero_referencia' => $numero_ref_prefijo,
                        'idop' => $orden_id,
                        'observaciones' => 'Anulación de proceso',
                        'usuario_movimiento' => $usuario_id,
                        'fecha_movimiento' => now(),
                        'estado' => 1,
                        'tiene_kardex' => true
                    ]);

                    DB::table('kardex')->insert([
                        'codigo_almacen'       => $codigo_almacen,
                        'codigo_producto'      => $ing->codigo_producto_proceso,
                        'fecha_movimiento'     => now(),
                        'tipo_movimiento'      => 'EXTORNO',
                        'documento'            => 'EXTORNO_PROD',
                        'numero_documento'     => $numero_ref_prefijo,
                        'cantidad_entrada'     => 0,
                        'cantidad_salida'      => $ing->cantidad,
                        'cantidad_saldo'       => $stockPEP,
                        'codigo_referencia_movimiento' => $movIdExt,
                        'observaciones'        => "Anulación de PEP por anulación de proceso OP-{$orden_id}",
                        'usuario_registro'     => $usuario_id
                    ]);
                }
            }
            
            // 3. EXTORNO DE MATERIAS PRIMAS (MTP)
            $consumos = DB::table('movimientos_inventario')
                ->where('numero_referencia', 'LIKE', $numero_ref_prefijo . '%')
                ->where('tipo_movimiento', 'SALIDA')
                ->where('documento_referencia', 'PRODUCCION')
                ->where('estado', 1)
                ->get();
                
            $resumenDevoluciones = [];
            foreach ($consumos as $s) {
                // Devolver stock al inventario
                DB::table('inventario')
                    ->where('codigo_producto', $s->codigo_producto)
                    ->where('lote', $s->lote)
                    ->where('codigo_almacen', $s->codigo_almacen)
                    ->update([
                        'stock_actual' => DB::raw("stock_actual + {$s->cantidad}"),
                        'fecha_ultimo_movimiento' => now()
                    ]);
                    
                // Activar el lote si estaba inactivo
                DB::table('inventario')
                    ->where('codigo_producto', $s->codigo_producto)
                    ->where('lote', $s->lote)
                    ->where('codigo_almacen', $s->codigo_almacen)
                    ->where('stock_actual', '>', 0)
                    ->update([
                        'estado' => 1,
                        'fecha_ultimo_movimiento' => now(),
                        'usuario_ultimo_movimiento' => $usuario_id
                    ]);

                $stockDevuelto = DB::table('inventario')
                    ->where('codigo_producto', $s->codigo_producto)
                    ->where('lote', $s->lote)
                    ->where('codigo_almacen', $s->codigo_almacen)
                    ->value('stock_actual') ?? $s->cantidad;
                    
                // Registrar el ingreso por devolución
                $movDevId = DB::table('movimientos_inventario')->insertGetId([
                    'codigo_almacen' => $s->codigo_almacen,
                    'codigo_producto' => $s->codigo_producto,
                    'lote' => $s->lote,
                    'tipo_movimiento' => 'INGRESO',
                    'cantidad' => $s->cantidad,
                    'costo_unitario' => $s->costo_unitario,
                    'total' => ($s->cantidad * $s->costo_unitario),
                    'documento_referencia' => 'EXTORNO_CONS',
                    'numero_referencia' => $numero_ref_prefijo,
                    'idop' => $orden_id,
                    'observaciones' => 'Devolución por anulación de proceso',
                    'usuario_movimiento' => $usuario_id,
                    'fecha_movimiento' => now(),
                    'estado' => 1,
                    'tiene_kardex' => true
                ]);

                $key = $s->codigo_producto . '|' . $s->codigo_almacen;
                if (!isset($resumenDevoluciones[$key])) {
                    $resumenDevoluciones[$key] = [
                        'producto' => $s->codigo_producto,
                        'almacen' => $s->codigo_almacen,
                        'cantidad' => 0,
                        'primer_mov_id' => $movDevId,
                        'stock_final' => $stockDevuelto
                    ];
                }
                $resumenDevoluciones[$key]['cantidad'] += $s->cantidad;
            }

            foreach ($resumenDevoluciones as $dev) {
                $stockFinal = DB::table('inventario')
                    ->where('codigo_producto', $dev['producto'])
                    ->where('codigo_almacen', $dev['almacen'])
                    ->sum('stock_actual') ?? $dev['stock_final'];

                DB::table('kardex')->insert([
                    'codigo_almacen'       => $dev['almacen'],
                    'codigo_producto'      => $dev['producto'],
                    'fecha_movimiento'     => now(),
                    'tipo_movimiento'      => 'EXTORNO',
                    'documento'            => 'EXTORNO_CONS',
                    'numero_documento'     => $numero_ref_prefijo,
                    'cantidad_entrada'     => $dev['cantidad'],
                    'cantidad_salida'      => 0,
                    'cantidad_saldo'       => $stockFinal,
                    'codigo_referencia_movimiento' => $dev['primer_mov_id'],
                    'observaciones'        => "Devolución de MTP por anulación de proceso OP-{$orden_id}",
                    'usuario_registro'     => $usuario_id
                ]);
            }
            
            // 4. BAJA LÓGICA
            DB::table('componentes_orden_produccion_global')->where('id_proceso', $id)->update(['estado' => 0]);
            DB::table('produccion_ingresos_proceso')->where('id_proceso', $id)->update(['estado' => 'ANULADO']);
            $proceso->update(['estado' => 0]);
            
            DB::commit();
            return redirect()->route('ordenes.procesos.index', $orden_id)
                ->with('success', "Proceso anulado correctamente. Se revirtieron " . count($consumos) . " consumos de materia prima y el stock ha sido restaurado.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al anular: ' . $e->getMessage());
        }
    }

    public function ejecutar($idop, $id)
    {
        $orden = OrdenProduccion::where('idop', $idop)->firstOrFail();
        $proceso = OrdenProceso::where('id', $id)->firstOrFail();
        $proceso_desc = DB::table('proceso_produccion')->where('codigo', $proceso->codigo_proceso)->value('descripcion');
        $proceso->proceso_desc = $proceso_desc ?? $proceso->descripcion_proceso;

        $estado_proceso_actual = strtoupper(trim($proceso->estado_avance ?? 'PENDIENTE'));
        if (empty($estado_proceso_actual)) $estado_proceso_actual = 'PENDIENTE';

        $es_actividad = ($proceso->codigo_proceso == '1');
        $es_mezclado = ($proceso->codigo_proceso == '16');
        $es_inyectado = ($proceso->codigo_proceso == '15');
        $es_ensamblado = ($proceso->codigo_proceso == '10');
        $es_molido = ($proceso->codigo_proceso == '18');

        $proceso_nombre = strtoupper(trim($proceso->proceso_desc));
        $formulas_disponibles = DB::table('formula_produccion')
            ->select('codigo', 'descripcion')
            ->where('estado', 1)
            ->where(function($query) use ($proceso_nombre) {
                // Filtro principal por descripción
                $query->where('descripcion', 'LIKE', "%{$proceso_nombre}%");
                
                // Mapeo de prefijos comunes
                if (str_contains($proceso_nombre, 'MEZCLADO')) {
                    $query->orWhere('codigo', 'LIKE', 'MZ%');
                } elseif (str_contains($proceso_nombre, 'INYECTADO')) {
                    $query->orWhere('codigo', 'LIKE', 'INY%')
                          ->orWhere('codigo', 'LIKE', 'CA%'); // Cascara
                } elseif (str_contains($proceso_nombre, 'HORNEADO')) {
                    $query->orWhere('codigo', 'LIKE', 'C1%');
                } elseif (str_contains($proceso_nombre, 'TROQUELADO')) {
                    $query->orWhere('codigo', 'LIKE', 'C2%');
                } elseif (str_contains($proceso_nombre, 'ENSAMBLADO')) {
                    $query->orWhere('codigo', 'LIKE', 'EN%');
                } elseif (str_contains($proceso_nombre, 'MOLIDO')) {
                    $query->orWhere('codigo', 'LIKE', 'MO%');
                }
            })
            ->orderBy('codigo')
            ->get();

        $registrados = ComponenteOrdenProduccion::where('id_proceso', $id)->where('estado', 1)->orderBy('id_op_componentes', 'desc')->get();
        $tiene_componentes = ($registrados->count() > 0);

        $tipos_producto = DB::table('tipo_producto')->select('codigo', 'descripcion')->where('estado', 1)->get();
        
        $colores = DB::table('color')->select('codigo', 'descripcion')->where('activo', 1)->get();
        $unidades = DB::table('unidad_medida')->select('codigo', 'descripcion')->where('estado', 1)->get();
        $trabajadores = DB::table('trabajador')->select('codigo', 'nombre')->where('estado', 1)->get();

        $moldes = [];
        if ($es_inyectado) {
            $productCode = $orden->codigo_producto_proceso;
            
            $query = DB::table('molde as m')
                ->select('m.codigo', 'm.descripcion', DB::raw('(SELECT codigo_formula FROM composicion_formula cf WHERE cf.codigo_molde = m.codigo LIMIT 1) as codigo_formula'))
                ->where('m.activo', 1);

            if ($productCode) {
                $query->join('producto_molde as pm', 'm.codigo', '=', 'pm.codigo_molde')
                      ->where('pm.codigo_producto_proceso', $productCode);
            }

            $moldes = $query->get();
            
            // Si no hay moldes vinculados al producto, traer todos los activos como fallback
            if ($moldes->isEmpty()) {
                $query_fallback = DB::table('molde')
                    ->select('codigo', 'descripcion', DB::raw('(SELECT codigo_formula FROM composicion_formula cf WHERE cf.codigo_molde = molde.codigo LIMIT 1) as codigo_formula'))
                    ->where('activo', 1);
                    
                $descProducto = strtoupper($orden->descripcion_producto_proceso);
                if (str_contains($descProducto, 'GANCHO')) {
                    $query_fallback->where('descripcion', 'LIKE', '%GANCHO%');
                } elseif (str_contains($descProducto, 'JABONERA')) {
                    $query_fallback->where('descripcion', 'LIKE', '%JABONERA%');
                } elseif (str_contains($descProducto, 'COLADOR')) {
                    $query_fallback->where('descripcion', 'LIKE', '%COLADOR%');
                } elseif (str_contains($descProducto, 'MATAMOSCA')) {
                    $query_fallback->where('descripcion', 'LIKE', '%MATAMOSCA%');
                }

                $moldes = $query_fallback->get();
            }
        }

        $query_centros = DB::table('centro_trabajo_produccion as ct')
            ->join('proceso_centro_trabajo as pct', 'ct.codigo', '=', 'pct.codigo_centro_trabajo')
            ->select('ct.codigo', 'ct.descripcion')
            ->where('pct.codigo_proceso', $proceso->codigo_proceso);

        if ($es_inyectado) {
            $descProducto = strtoupper($orden->descripcion_producto_proceso);
            if (str_contains($descProducto, 'GANCHO')) {
                $query_centros->where('ct.codigo', '!=', 'INY-001');
            } elseif (str_contains($descProducto, 'COLADOR')) {
                $query_centros->where('ct.codigo', 'INY-001');
            }
        }

        $centros_trabajo = $query_centros->get();

        $almacenes = DB::table('almacen')->where('activo', 1)->get();
        $proceso_produccion_almacen = DB::table('proceso_produccion')->where('codigo', $proceso->codigo_proceso)->value('codigo_almacen');

        return view('produccion.procesos.ejecucion', compact(
            'orden', 'proceso', 'estado_proceso_actual', 'es_actividad', 'es_mezclado', 'es_inyectado', 'es_ensamblado', 'es_molido',
            'formulas_disponibles', 'registrados', 'tiene_componentes', 'tipos_producto',
            'colores', 'unidades', 'trabajadores', 'moldes', 'centros_trabajo', 'almacenes', 'proceso_produccion_almacen'
        ));
    }

    public function getFormulaComponents(Request $request)
    {
        $codigo_formula = $request->codigo_formula;
        $codigo_molde = $request->codigo_molde;
        $codigo_almacen = $request->codigo_almacen;
        
        if (!$codigo_formula) {
            return response()->json(['success' => false, 'message' => 'Código de fórmula no proporcionado.']);
        }
        
        $query = DB::table('composicion_formula as cf')
            ->join('producto as p', 'cf.codigo_producto', '=', 'p.codigo')
            ->leftJoin('unidad_medida as u', 'cf.codigo_unidad_medida', '=', 'u.codigo')
            ->leftJoin('tipo_producto as tp', 'p.codigo_tipo_producto', '=', 'tp.codigo')
            ->select(
                'cf.codigo_producto',
                'p.descripcion as descripcion_producto',
                'p.codigo_tipo_producto',
                'tp.descripcion as descripcion_tipo_producto',
                'cf.cantidad_nominal',
                'cf.codigo_unidad_medida',
                'u.descripcion as descripcion_unidad_medida',
                'cf.codigo_molde'
            )
            ->where('cf.codigo_formula', $codigo_formula);

        if ($codigo_molde) {
            $query->where(function($q) use ($codigo_molde) {
                $q->where('cf.codigo_molde', $codigo_molde)
                  ->orWhereNull('cf.codigo_molde');
            });
        }

        $componentes = $query->get();


        // Lógica de ensamblado (dinámica) si no se pasa molde desde el UI
        if (!$codigo_molde) {
            $componentesFiltrados = collect();
            $productosConMolde = [];

            foreach ($componentes as $comp) {
                if (!empty($comp->codigo_molde)) {
                    if (!array_key_exists($comp->codigo_producto, $productosConMolde)) {
                        // Buscar el molde del lote más antiguo en stock
                        $queryStock = DB::table('inventario as i')
                            ->join('almacen as a', 'i.codigo_almacen', '=', 'a.codigo_almacen')
                            ->where('i.codigo_producto', $comp->codigo_producto)
                            ->where('a.activo', 1)
                            ->where(function($q) { $q->where('i.estado', 1)->orWhereNull('i.estado'); })
                            ->where('i.stock_actual', '>', 0);
                            
                        if ($codigo_almacen) {
                            $queryStock->where('i.codigo_almacen', $codigo_almacen);
                        }

                        $loteActivo = $queryStock->orderBy('i.fecha_vencimiento', 'asc')
                            ->orderBy('i.id_inventario', 'asc')
                            ->first();

                        $moldeStock = null;
                        if ($loteActivo && $loteActivo->lote) {
                            $moldeStock = DB::table('produccion_ingresos_proceso')
                                ->where('lote_produccion', $loteActivo->lote)
                                ->value('codigo_molde');
                        }
                        $productosConMolde[$comp->codigo_producto] = $moldeStock;
                    }

                    // Solo incluir si coincide con el molde en stock
                    if ($comp->codigo_molde === $productosConMolde[$comp->codigo_producto]) {
                        $componentesFiltrados->push($comp);
                    }
                } else {
                    // Si no tiene molde (ej. Clip) se incluye directamente
                    $componentesFiltrados->push($comp);
                }
            }
            $componentes = $componentesFiltrados;
        }

        if ($componentes->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'La fórmula no tiene componentes o no coinciden los parámetros de stock/molde.']);
        }

        return response()->json(['success' => true, 'componentes' => $componentes->values()]);
    }

    public function verificarStock(Request $request)
    {
        $productos = $request->input('productos', []);
        $codigo_almacen = $request->codigo_almacen;

        if (!$codigo_almacen || empty($productos)) {
            return response()->json(['success' => false, 'message' => 'Parámetros incompletos.']);
        }

        $stocks = DB::table('inventario')
            ->where('codigo_almacen', $codigo_almacen)
            ->whereIn('codigo_producto', $productos)
            ->selectRaw('codigo_producto, SUM(stock_actual) as stock_total')
            ->groupBy('codigo_producto')
            ->pluck('stock_total', 'codigo_producto');

        $result = [];
        foreach ($productos as $prod) {
            $result[$prod] = floatval($stocks[$prod] ?? 0);
        }

        return response()->json(['success' => true, 'data' => $result]);
    }

    public function storeComponentes(Request $request, $idop, $id)
    {
        $componentes = json_decode($request->componentes_json ?? '[]', true);
        
        if (empty($componentes)) {
            return back()->with('error', 'No hay datos para guardar.');
        }

        try {
            DB::beginTransaction();

            $usuario_id = Auth::user()->id_usuario ?? 5;
            $batch_id = mt_rand(1000, 9999);
            $numero_referencia = "OP-{$idop}-PROC-{$id}-" . $batch_id;
            
            $trace_movimientos = 0;
            $trace_componentes = count($componentes);
            $consumosResumen = []; // [key => ['producto','almacen','cantidad','primer_mov_id']]

            $codigo_molde_op = null;
            foreach ($componentes as $comp) {
                if (!empty($comp['codigo_molde'])) {
                    $codigo_molde_op = $comp['codigo_molde'];
                    break;
                }
            }

            $descripcion_molde_op = null;
            if ($codigo_molde_op) {
                $descripcion_molde_op = DB::table('molde')->where('codigo', $codigo_molde_op)->value('descripcion');
            }

            $proceso = OrdenProceso::findOrFail($id);
            if ($proceso->estado_avance === 'COMPLETADO') {
                throw new \Exception("No se pueden registrar componentes en un proceso COMPLETADO.");
            }
            $es_actividad = ($proceso->codigo_proceso == '1');

            if ($es_actividad) {
                foreach ($componentes as $comp) {
                    $codigo_act = $comp['codigo_producto'];
                    $desc_act = DB::table('actividad_produccion')->where('codigo', $codigo_act)->value('descripcion');
                    
                    $codigo_trabajador = !empty($comp['codigo_trabajador']) ? $comp['codigo_trabajador'] : null;

                    DB::table('producto')->insertOrIgnore([
                        'codigo' => $codigo_act,
                        'descripcion' => $desc_act,
                        'codigo_tipo_producto' => 'ACT',
                        'es_producto_proceso' => 0,
                        'estado' => 1
                    ]);

                    ComponenteOrdenProduccion::create([
                        'idop' => $idop,
                        'id_proceso' => $id,
                        'codigo_tipo_producto' => 'ACT',
                        'descripcion_tipo_producto' => 'ACTIVIDADES PRODUCCION',
                        'codigo_producto' => $codigo_act,
                        'descripcion_producto' => $desc_act,
                        'codigo_trabajador' => $codigo_trabajador,
                        'descripcion_trabajador' => DB::table('trabajador')->where('codigo', $codigo_trabajador)->value('nombre'),
                        'codigo_unidad_medida' => 'UNI',
                        'descripcion_unidad_medida' => 'UNIDADES',
                        'cantidad' => 1,
                        'fecha_inicio' => $comp['fecha_inicio'],
                        'fecha_fin' => $comp['fecha_fin'],
                        'hora_inicio' => $comp['hora_inicio'],
                        'hora_fin' => $comp['hora_fin'],
                        'fecha_inicio_maquina' => $comp['fecha_inicio_maquina'] ?? $comp['fecha_inicio'],
                        'hora_inicio_maquina' => $comp['hora_inicio_maquina'] ?? $comp['hora_inicio'],
                        'fecha_fin_maquina' => $comp['fecha_fin_maquina'] ?? $comp['fecha_fin'],
                        'hora_fin_maquina' => $comp['hora_fin_maquina'] ?? $comp['hora_fin'],
                        'estado' => 1
                    ]);
                }
                DB::commit();
                return back()->with('success', 'Actividades registradas correctamente.');
            }

            // Flujo Producción
            $faltantes = [];
            $cantidades_agrupadas = [];
            $total_insumos_ingresados = 0;

            foreach ($componentes as $comp) {
                $cod = $comp['codigo_producto'];
                $cant = floatval($comp['cantidad']);
                if (!isset($cantidades_agrupadas[$cod])) { $cantidades_agrupadas[$cod] = 0; }
                $cantidades_agrupadas[$cod] += $cant;
                $total_insumos_ingresados += $cant;
            }

            $codigo_almacen_consumo = $request->input('codigo_almacen_consumo');
            if (empty($codigo_almacen_consumo) && !$es_actividad) {
                throw new \Exception("Debe seleccionar un Almacén de Consumo.");
            }

            foreach ($cantidades_agrupadas as $codigo_prod => $cant_req) {
                $query_disp = DB::table('inventario as i')
                    ->join('almacen as a', 'i.codigo_almacen', '=', 'a.codigo_almacen')
                    ->where('i.codigo_producto', $codigo_prod)
                    ->where('a.activo', 1)
                    ->where(function($q) { $q->where('i.estado', 1)->orWhereNull('i.estado'); });

                if ($codigo_almacen_consumo) {
                    $query_disp->where('i.codigo_almacen', $codigo_almacen_consumo);
                }

                $stock_disp = $query_disp->lockForUpdate()->sum('i.stock_actual') ?? 0;

                if ($stock_disp < $cant_req) {
                    $alm_msg = $codigo_almacen_consumo ? " en el almacén seleccionado ($codigo_almacen_consumo)" : " en almacén";
                    $faltantes[] = "[$codigo_prod] Req: " . number_format($cant_req, 2) . " | Disp: " . number_format($stock_disp, 2) . $alm_msg;
                }
            }

            if (!empty($faltantes)) {
                throw new \Exception("STOCK INSUFICIENTE. Faltan materiales en almacén: " . implode(" ; ", $faltantes));
            }

            $grupos_pep = []; 
            $productos_resultantes_arr = json_decode($request->productos_resultantes_json ?? '[]', true);
            $has_manual_products = count($productos_resultantes_arr) > 0;

            foreach ($componentes as $comp) {
                $codigo_producto = $comp['codigo_producto'];
                $cantidad = floatval($comp['cantidad']);
                
                $codigo_formula = !empty($comp['codigo_formula']) ? $comp['codigo_formula'] : null;
                $codigo_color = !empty($comp['codigo_color']) ? $comp['codigo_color'] : null;
                $codigo_centro_trabajo = !empty($comp['codigo_centro_trabajo']) ? $comp['codigo_centro_trabajo'] : null;
                $codigo_molde = !empty($comp['codigo_molde']) ? $comp['codigo_molde'] : null;
                $codigo_trabajador = !empty($comp['codigo_trabajador']) ? $comp['codigo_trabajador'] : null;

                if (!empty($codigo_formula)) {
                    $codigo_pep = $this->determinarCodigoPEP($codigo_formula);
                    if (empty($codigo_pep)) {
                        throw new \Exception("La fórmula [$codigo_formula] no tiene un Producto Resultante asociado.");
                    }

                    $key = $codigo_pep . '_' . ($codigo_color ?: 'SC');
                    if (!isset($grupos_pep[$key])) {
                        $grupos_pep[$key] = [
                            'codigo_pep' => $codigo_pep,
                            'descripcion_pep' => DB::table('producto')->where('codigo', $codigo_pep)->value('descripcion'),
                            'formula' => $codigo_formula,
                            'color' => $codigo_color,
                            'cant' => 0,
                            'unidad' => $comp['codigo_unidad_medida'] ?? 'KG'
                        ];
                    }
                    $grupos_pep[$key]['cant'] += $cantidad;
                } elseif (!$has_manual_products && !empty($comp['codigo_tipo_producto']) && $comp['codigo_tipo_producto'] === 'PEP') {
                    $codigo_pep = $this->determinarPEPdesdeProducto($codigo_producto);
                    if (!empty($codigo_pep)) {
                        $key = $codigo_pep . '_' . ($codigo_color ?: 'SC');
                        if (!isset($grupos_pep[$key])) {
                            $grupos_pep[$key] = [
                                'codigo_pep' => $codigo_pep,
                                'descripcion_pep' => DB::table('producto')->where('codigo', $codigo_pep)->value('descripcion'),
                                'formula' => null,
                                'color' => $codigo_color,
                                'cant' => 0,
                                'unidad' => $comp['codigo_unidad_medida'] ?? 'KG'
                            ];
                        }
                        $grupos_pep[$key]['cant'] += $cantidad;
                    }
                } elseif (!$has_manual_products && !empty($comp['codigo_tipo_producto']) && $comp['codigo_tipo_producto'] !== 'ACT') {
                    $op = DB::table('orden_produccion_global')->where('idop', $idop)->first();
                    if ($op && !empty($op->descripcion_producto_proceso)) {
                        $producto_pep = DB::table('producto')
                            ->where('descripcion', $op->descripcion_producto_proceso)
                            ->where('codigo_tipo_producto', 'PEP')
                            ->where('estado', 1)
                            ->first();
                        $codigo_pep = $producto_pep ? $producto_pep->codigo : null;
                        
                        if ($codigo_pep) {
                            $key = $codigo_pep . '_' . ($codigo_color ?: 'SC');
                            if (!isset($grupos_pep[$key])) {
                                $grupos_pep[$key] = [
                                    'codigo_pep' => $codigo_pep,
                                    'descripcion_pep' => DB::table('producto')->where('codigo', $codigo_pep)->value('descripcion'),
                                    'formula' => null,
                                    'color' => $codigo_color,
                                    'cant' => 0,
                                    'unidad' => $comp['codigo_unidad_medida'] ?? 'KG'
                                ];
                            }
                            $grupos_pep[$key]['cant'] += $cantidad;
                        }
                    }
                }

                $componente = ComponenteOrdenProduccion::create([
                    'idop' => $idop,
                    'id_proceso' => $id,
                    'codigo_tipo_producto' => $comp['codigo_tipo_producto'] ?? 'MTP',
                    'descripcion_tipo_producto' => DB::table('tipo_producto')->where('codigo', $comp['codigo_tipo_producto'] ?? 'MTP')->value('descripcion'),
                    'codigo_producto' => $codigo_producto,
                    'descripcion_producto' => DB::table('producto')->where('codigo', $codigo_producto)->value('descripcion'),
                    'codigo_centro_trabajo' => $codigo_centro_trabajo,
                    'descripcion_centro_trabajo' => DB::table('centro_trabajo_produccion')->where('codigo', $codigo_centro_trabajo)->value('descripcion'),
                    'codigo_molde' => $codigo_molde,
                    'descripcion_molde' => DB::table('molde')->where('codigo', $codigo_molde)->value('descripcion'),
                    'codigo_trabajador' => $codigo_trabajador,
                    'descripcion_trabajador' => DB::table('trabajador')->where('codigo', $codigo_trabajador)->value('nombre'),
                    'codigo_unidad_medida' => $comp['codigo_unidad_medida'] ?? 'KG',
                    'descripcion_unidad_medida' => DB::table('unidad_medida')->where('codigo', $comp['codigo_unidad_medida'] ?? 'KG')->value('descripcion'),
                    'cantidad' => $cantidad,
                    'codigo_color' => $codigo_color,
                    'descripcion_color' => DB::table('color')->where('codigo', $codigo_color)->value('descripcion'),
                    'codigo_formula_produccion' => $codigo_formula,
                    'descripcion_formula_produccion' => DB::table('formula_produccion')->where('codigo', $codigo_formula)->value('descripcion'),
                    'fecha_inicio' => $comp['fecha_inicio'],
                    'fecha_fin' => $comp['fecha_fin'],
                    'hora_inicio' => $comp['hora_inicio'],
                    'hora_fin' => $comp['hora_fin'],
                    'fecha_inicio_maquina' => $comp['fecha_inicio_maquina'] ?? $comp['fecha_inicio'],
                    'hora_inicio_maquina' => $comp['hora_inicio_maquina'] ?? $comp['hora_inicio'],
                    'fecha_fin_maquina' => $comp['fecha_fin_maquina'] ?? $comp['fecha_fin'],
                    'hora_fin_maquina' => $comp['hora_fin_maquina'] ?? $comp['hora_fin'],
                    'estado' => 1
                ]);
                $idComponente = $componente->id_op_componentes;

                $cantidad_restante = $cantidad;
                $query_lotes = DB::table('inventario as i')
                    ->join('almacen as a', 'i.codigo_almacen', '=', 'a.codigo_almacen')
                    ->where('i.codigo_producto', $codigo_producto)
                    ->where('a.activo', 1)
                    ->where(function($q) { $q->where('i.estado', 1)->orWhereNull('i.estado'); });

                if ($codigo_almacen_consumo) {
                    $query_lotes->where('i.codigo_almacen', $codigo_almacen_consumo);
                }

                $lotes = $query_lotes->select('i.id_inventario', 'i.stock_actual', 'i.lote', 'i.costo_promedio', 'i.codigo_almacen')
                    ->orderBy('i.fecha_vencimiento', 'asc')
                    ->orderBy('i.id_inventario', 'asc')
                    ->lockForUpdate()
                    ->get();
                
                foreach ($lotes as $lote) {
                    if ($cantidad_restante <= 0) break;
                    $consumo = min($lote->stock_actual, $cantidad_restante);
                    
                    $movId = DB::table('movimientos_inventario')->insertGetId([
                        'codigo_almacen' => $lote->codigo_almacen,
                        'codigo_producto' => $codigo_producto,
                        'lote' => $lote->lote,
                        'tipo_movimiento' => 'SALIDA',
                        'cantidad' => $consumo,
                        'costo_unitario' => $lote->costo_promedio,
                        'total' => $consumo * $lote->costo_promedio,
                        'documento_referencia' => 'PRODUCCION',
                        'numero_referencia' => $numero_referencia,
                        'idop' => $idop,
                        'componente_origen_id' => $idComponente,
                        'observaciones' => 'Consumo proceso',
                        'usuario_movimiento' => $usuario_id,
                        'fecha_movimiento' => now(),
                        'estado' => 1
                    ]);
                    
                    $costoConsumo = $consumo * ($lote->costo_promedio ?? 0);
                    $key = $codigo_producto . '|' . $lote->codigo_almacen . '|' . $lote->lote;
                    if (!isset($consumosResumen[$key])) {
                        $consumosResumen[$key] = [
                            'producto' => $codigo_producto,
                            'almacen' => $lote->codigo_almacen,
                            'lote' => $lote->lote,
                            'cantidad' => 0,
                            'total_costo' => 0,
                            'primer_mov_id' => $movId
                        ];
                    }
                    $consumosResumen[$key]['cantidad'] += $consumo;
                    $consumosResumen[$key]['total_costo'] += $costoConsumo;
                    
                    $trace_movimientos++;
                    $nuevo_stock = $lote->stock_actual - $consumo;
                    
                    DB::table('inventario')->where('id_inventario', $lote->id_inventario)->update([
                        'stock_actual' => $nuevo_stock,
                        'estado' => ($nuevo_stock > 0 ? 1 : 0),
                        'fecha_ultimo_movimiento' => now(),
                        'usuario_ultimo_movimiento' => $usuario_id
                    ]);
                    
                    $cantidad_restante -= $consumo;
                }

                if ($cantidad_restante > 0) {
                    throw new \Exception(
                        "Stock insuficiente para el producto {$codigo_producto}. "
                        . "Faltan " . number_format($cantidad_restante, 4) . " unidades."
                    );
                }
            }

            // Kardex SALIDA por cada producto consumido
            foreach ($consumosResumen as $resumen) {
                $stockActual = DB::table('inventario')
                    ->where('codigo_producto', $resumen['producto'])
                    ->where('codigo_almacen', $resumen['almacen'])
                    ->sum('stock_actual') ?? 0;

                $costoSalidaProm = $resumen['cantidad'] > 0
                    ? round($resumen['total_costo'] / $resumen['cantidad'], 9)
                    : 0;
                $totalSaldo = $stockActual * $costoSalidaProm;

                DB::table('kardex')->insert([
                    'codigo_almacen'       => $resumen['almacen'],
                    'codigo_producto'      => $resumen['producto'],
                    'lote'                 => $resumen['lote'],
                    'fecha_movimiento'     => now(),
                    'tipo_movimiento'      => 'SALIDA',
                    'documento'            => 'PRODUCCION',
                    'numero_documento'     => $numero_referencia,
                    'cantidad_entrada'     => 0,
                    'costo_entrada'        => 0,
                    'total_entrada'        => 0,
                    'cantidad_salida'      => $resumen['cantidad'],
                    'costo_salida'         => $costoSalidaProm,
                    'total_salida'         => round($resumen['total_costo'], 2),
                    'cantidad_saldo'       => max(0, $stockActual),
                    'costo_promedio'       => $costoSalidaProm,
                    'total_saldo'          => round($totalSaldo, 9),
                    'codigo_referencia_movimiento' => $resumen['primer_mov_id'],
                    'observaciones'        => 'Consumo de producción',
                    'usuario_registro'     => $usuario_id
                ]);
            }

            DB::table('movimientos_inventario')
                ->where('numero_referencia', $numero_referencia)
                ->where('documento_referencia', 'PRODUCCION')
                ->update(['tiene_kardex' => true]);

            $productos_resultantes = json_decode($request->productos_resultantes_json ?? '[]', true);
            $productos_manuales = [];
            foreach ($productos_resultantes as $pr) {
                if (floatval($pr['cantidad']) > 0) {
                    $productos_manuales[$pr['codigo_producto']] = true;
                }
            }

            $ingresos_creados = 0;
            foreach ($grupos_pep as $g) {
                if ($g['cant'] > 0 && !isset($productos_manuales[$g['codigo_pep']])) {
                    $lote_pep = $this->generarCodigoPEP($g['codigo_pep'], $g['color'], $id);
                    DB::table('produccion_ingresos_proceso')->insert([
                        'idop' => $idop,
                        'id_proceso' => $id,
                        'codigo_producto_proceso' => $g['codigo_pep'],
                        'descripcion_producto_proceso' => $g['descripcion_pep'],
                        'cantidad' => $g['cant'],
                        'codigo_unidad_medida' => $g['unidad'],
                        'codigo_almacen' => 'ALM-PEP',
                        'lote_produccion' => $lote_pep,
                        'fecha_ingreso' => now(),
                        'usuario_registro' => $usuario_id,
                        'estado' => 'PENDIENTE',
                        'codigo_molde' => $codigo_molde_op,
                        'descripcion_molde' => $descripcion_molde_op
                    ]);
                    $ingresos_creados++;
                }
            }

            // Procesar productos resultantes manuales (no formula-based)
            $productos_resultantes = json_decode($request->productos_resultantes_json ?? '[]', true);
            foreach ($productos_resultantes as $pr) {
                $codigo_pr = $pr['codigo_producto'];
                $cantidad_pr = floatval($pr['cantidad']);
                $unidad_pr = $pr['codigo_unidad_medida'] ?? 'KG';

                if ($cantidad_pr > 0) {
                    $desc_pr = DB::table('producto')->where('codigo', $codigo_pr)->value('descripcion');
                    $lote_pr = $this->generarCodigoPEP($codigo_pr, null, $id);

                    DB::table('produccion_ingresos_proceso')->insert([
                        'idop' => $idop,
                        'id_proceso' => $id,
                        'codigo_producto_proceso' => $codigo_pr,
                        'descripcion_producto_proceso' => $desc_pr,
                        'cantidad' => $cantidad_pr,
                        'codigo_unidad_medida' => $unidad_pr,
                        'codigo_almacen' => 'ALM-PEP',
                        'lote_produccion' => $lote_pr,
                        'fecha_ingreso' => now(),
                        'usuario_registro' => $usuario_id,
                        'codigo_molde' => $codigo_molde_op,
                        'descripcion_molde' => $descripcion_molde_op
                    ]);
                    $ingresos_creados++;
                }
            }
            OrdenProceso::where('id', $id)->where(function($q) {
                $q->where('estado_avance', 'PENDIENTE')->orWhereNull('estado_avance');
            })->update(['estado_avance' => 'EN_PROCESO']);

            DB::commit();
            return back()->with('success', "Componentes guardados. Movimientos: $trace_movimientos, PEPs: $ingresos_creados.");
            
        } catch (\Exception $e) {
            \Log::error('Error en storeComponentes: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            DB::rollBack();
            return back()->with('error', 'Error al procesar: ' . $e->getMessage());
        }
    }

    public function updateComponente(Request $request, $idop, $id, $id_componente)
    {
        $request->validate([
            'cantidad'        => 'required|numeric|min:0.01',
            'codigo_trabajador' => 'nullable|string|max:20',
            'fecha_inicio'    => 'nullable|date',
            'fecha_fin'       => 'nullable|date',
            'hora_inicio'     => 'nullable',
            'hora_fin'        => 'nullable',
            'fecha_inicio_maquina' => 'nullable|date',
            'fecha_fin_maquina'    => 'nullable|date',
            'hora_inicio_maquina'  => 'nullable',
            'hora_fin_maquina'     => 'nullable',
        ]);

        try {
            DB::beginTransaction();

            $componente = ComponenteOrdenProduccion::where('id_op_componentes', $id_componente)
                ->where('id_proceso', $id)
                ->where('estado', 1)
                ->lockForUpdate()
                ->firstOrFail();

            $proceso = OrdenProceso::findOrFail($id);
            if ($proceso->estado_avance === 'COMPLETADO') {
                throw new \Exception("No se puede modificar un componente de un proceso COMPLETADO.");
            }

            if ($componente->codigo_tipo_producto === 'ACT') {
                $componente->update([
                    'codigo_trabajador' => $request->codigo_trabajador,
                    'descripcion_trabajador' => $request->codigo_trabajador
                        ? DB::table('trabajador')->where('codigo', $request->codigo_trabajador)->value('nombre')
                        : $componente->descripcion_trabajador,
                    'fecha_inicio' => $request->fecha_inicio ?? $componente->fecha_inicio,
                    'fecha_fin'    => $request->fecha_fin ?? $componente->fecha_fin,
                    'hora_inicio'  => $request->hora_inicio ?? $componente->hora_inicio,
                    'hora_fin'     => $request->hora_fin ?? $componente->hora_fin,
                    'fecha_inicio_maquina' => $request->fecha_inicio_maquina ?? $componente->fecha_inicio_maquina,
                    'fecha_fin_maquina'    => $request->fecha_fin_maquina ?? $componente->fecha_fin_maquina,
                    'hora_inicio_maquina'  => $request->hora_inicio_maquina ?? $componente->hora_inicio_maquina,
                    'hora_fin_maquina'     => $request->hora_fin_maquina ?? $componente->hora_fin_maquina,
                ]);
                DB::commit();
                return back()->with('success', 'Actividad actualizada correctamente.');
            }

            $nuevaCantidad = floatval($request->cantidad);
            $originalCantidad = floatval($componente->cantidad);
            $diferencia = $nuevaCantidad - $originalCantidad;

            $movimientosOrigen = DB::table('movimientos_inventario')
                ->where('componente_origen_id', $id_componente)
                ->where('documento_referencia', 'PRODUCCION')
                ->where('estado', 1)
                ->orderBy('id_movimiento', 'asc')
                ->get();

            if ($movimientosOrigen->isEmpty()) {
                $componente->update([
                    'codigo_trabajador' => $request->codigo_trabajador,
                    'descripcion_trabajador' => $request->codigo_trabajador
                        ? DB::table('trabajador')->where('codigo', $request->codigo_trabajador)->value('nombre')
                        : $componente->descripcion_trabajador,
                    'fecha_inicio' => $request->fecha_inicio ?? $componente->fecha_inicio,
                    'fecha_fin'    => $request->fecha_fin ?? $componente->fecha_fin,
                    'hora_inicio'  => $request->hora_inicio ?? $componente->hora_inicio,
                    'hora_fin'     => $request->hora_fin ?? $componente->hora_fin,
                    'fecha_inicio_maquina' => $request->fecha_inicio_maquina ?? $componente->fecha_inicio_maquina,
                    'fecha_fin_maquina'    => $request->fecha_fin_maquina ?? $componente->fecha_fin_maquina,
                    'hora_inicio_maquina'  => $request->hora_inicio_maquina ?? $componente->hora_inicio_maquina,
                    'hora_fin_maquina'     => $request->hora_fin_maquina ?? $componente->hora_fin_maquina,
                    'cantidad'     => $nuevaCantidad,
                ]);
                DB::commit();
                return back()->with('success', 'Componente actualizado (sin movimientos de inventario asociados).');
            }

            $codigo_producto = $componente->codigo_producto;
            $numero_referencia = $movimientosOrigen->first()->numero_referencia;
            $idop = $componente->idop;
            $usuario_id = Auth::user()->id_usuario ?? 5;

            $pepGenerado = DB::table('produccion_ingresos_proceso')
                ->where('id_proceso', $id)
                ->where('estado', 'APROBADO')
                ->exists();

            if ($pepGenerado && $diferencia != 0) {
                throw new \Exception(
                    "No se puede modificar la cantidad porque el PEP asociado ya fue recibido en almacén. "
                    . "Solo puede editar los datos de trabajador y fechas."
                );
            }

            if ($diferencia > 0) {
                $codigo_almacen_origen = $movimientosOrigen->first()->codigo_almacen ?? null;

                $query_disp = DB::table('inventario as i')
                    ->join('almacen as a', 'i.codigo_almacen', '=', 'a.codigo_almacen')
                    ->where('i.codigo_producto', $codigo_producto)
                    ->where('a.activo', 1)
                    ->where(function($q) { $q->where('i.estado', 1)->orWhereNull('i.estado'); });

                if ($codigo_almacen_origen) {
                    $query_disp->where('i.codigo_almacen', $codigo_almacen_origen);
                }

                $stockDisp = $query_disp->lockForUpdate()->sum('i.stock_actual') ?? 0;

                if ($stockDisp < $diferencia) {
                    $alm_msg = $codigo_almacen_origen ? " en el almacén original ($codigo_almacen_origen)" : " en almacén";
                    throw new \Exception(
                        "Stock insuficiente para aumentar la cantidad. "
                        . "Disponible: " . number_format($stockDisp, 2)
                        . ", Requerido adicional: " . number_format($diferencia, 2)
                        . $alm_msg
                    );
                }

                $cantidad_restante = $diferencia;
                $query_lotes = DB::table('inventario as i')
                    ->join('almacen as a', 'i.codigo_almacen', '=', 'a.codigo_almacen')
                    ->where('i.codigo_producto', $codigo_producto)
                    ->where('a.activo', 1)
                    ->where('i.stock_actual', '>', 0)
                    ->where(function($q) { $q->where('i.estado', 1)->orWhereNull('i.estado'); });

                if ($codigo_almacen_origen) {
                    $query_lotes->where('i.codigo_almacen', $codigo_almacen_origen);
                }

                $lotes = $query_lotes->select('i.id_inventario', 'i.stock_actual', 'i.lote', 'i.costo_promedio', 'i.codigo_almacen')
                    ->orderBy('i.fecha_vencimiento', 'asc')
                    ->orderBy('i.id_inventario', 'asc')
                    ->lockForUpdate()
                    ->get();

                $consumosResumen = [];
                foreach ($lotes as $lote) {
                    if ($cantidad_restante <= 0) break;
                    $consumo = min($lote->stock_actual, $cantidad_restante);

                    $movId = DB::table('movimientos_inventario')->insertGetId([
                        'codigo_almacen'          => $lote->codigo_almacen,
                        'codigo_producto'         => $codigo_producto,
                        'lote'                    => $lote->lote,
                        'tipo_movimiento'         => 'SALIDA',
                        'cantidad'                => $consumo,
                        'costo_unitario'          => $lote->costo_promedio,
                        'total'                   => $consumo * $lote->costo_promedio,
                        'documento_referencia'    => 'PRODUCCION',
                        'numero_referencia'       => $numero_referencia . '-AJ-' . $id_componente,
                        'idop'                    => $idop,
                        'componente_origen_id'    => $id_componente,
                        'observaciones'           => 'Ajuste por edición de componente #' . $id_componente,
                        'usuario_movimiento'      => $usuario_id,
                        'fecha_movimiento'        => now(),
                        'estado'                  => 1,
                        'tiene_kardex'            => true,
                    ]);

                    $key = $codigo_producto . '|' . $lote->codigo_almacen . '|' . $lote->lote;
                    if (!isset($consumosResumen[$key])) {
                        $consumosResumen[$key] = [
                            'producto' => $codigo_producto,
                            'almacen'  => $lote->codigo_almacen,
                            'lote'     => $lote->lote,
                            'cantidad' => 0,
                            'primer_mov_id' => $movId,
                        ];
                    }
                    $consumosResumen[$key]['cantidad'] += $consumo;

                    $nuevo_stock_lote = $lote->stock_actual - $consumo;
                    DB::table('inventario')
                        ->where('id_inventario', $lote->id_inventario)
                        ->update([
                            'stock_actual' => $nuevo_stock_lote,
                            'estado'       => ($nuevo_stock_lote > 0 ? 1 : 0),
                            'fecha_ultimo_movimiento' => now(),
                            'usuario_ultimo_movimiento' => $usuario_id
                        ]);

                    $cantidad_restante -= $consumo;
                }

                if ($cantidad_restante > 0) {
                    throw new \Exception(
                        "Stock insuficiente para el producto {$codigo_producto}. "
                        . "Faltan " . number_format($cantidad_restante, 4) . " unidades."
                    );
                }

                foreach ($consumosResumen as $resumen) {
                    $stockActual = DB::table('inventario')
                        ->where('codigo_producto', $resumen['producto'])
                        ->where('codigo_almacen', $resumen['almacen'])
                        ->sum('stock_actual') ?? 0;

                    DB::table('kardex')->insert([
                        'codigo_almacen'              => $resumen['almacen'],
                        'codigo_producto'             => $resumen['producto'],
                        'lote'                        => $resumen['lote'],
                        'fecha_movimiento'            => now(),
                        'tipo_movimiento'             => 'SALIDA',
                        'documento'                   => 'PRODUCCION',
                        'numero_documento'            => $numero_referencia . '-AJ-' . $id_componente,
                        'cantidad_entrada'            => 0,
                        'cantidad_salida'             => $resumen['cantidad'],
                        'cantidad_saldo'              => $stockActual,
                        'codigo_referencia_movimiento' => $resumen['primer_mov_id'],
                        'observaciones'               => 'Ajuste por edición de componente #' . $id_componente,
                        'usuario_registro'            => $usuario_id,
                    ]);
                }
            }

            if ($diferencia < 0) {
                $devolver = abs($diferencia);

                foreach ($movimientosOrigen as $mov) {
                    if ($devolver <= 0) break;

                    $devolverLote = min($mov->cantidad, $devolver);

                    DB::table('inventario')
                        ->where('codigo_producto', $mov->codigo_producto)
                        ->where('lote', $mov->lote)
                        ->where('codigo_almacen', $mov->codigo_almacen)
                        ->update([
                            'stock_actual' => DB::raw("stock_actual + {$devolverLote}"),
                            'estado'       => 1,
                        ]);

                    $movDevId = DB::table('movimientos_inventario')->insertGetId([
                        'codigo_almacen'          => $mov->codigo_almacen,
                        'codigo_producto'         => $mov->codigo_producto,
                        'lote'                    => $mov->lote,
                        'tipo_movimiento'         => 'INGRESO',
                        'cantidad'                => $devolverLote,
                        'costo_unitario'          => $mov->costo_unitario,
                        'total'                   => $devolverLote * $mov->costo_unitario,
                        'documento_referencia'    => 'DEVOLUCION_EDIT',
                        'numero_referencia'       => $numero_referencia . '-AJ-' . $id_componente,
                        'idop'                    => $idop,
                        'componente_origen_id'    => $id_componente,
                        'observaciones'           => 'Devolución por edición de componente #' . $id_componente,
                        'usuario_movimiento'      => $usuario_id,
                        'fecha_movimiento'        => now(),
                        'estado'                  => 1,
                        'tiene_kardex'            => true,
                    ]);

                    $stockActual = DB::table('inventario')
                        ->where('codigo_producto', $mov->codigo_producto)
                        ->where('codigo_almacen', $mov->codigo_almacen)
                        ->sum('stock_actual') ?? 0;

                    DB::table('kardex')->insert([
                        'codigo_almacen'              => $mov->codigo_almacen,
                        'codigo_producto'             => $mov->codigo_producto,
                        'fecha_movimiento'            => now(),
                        'tipo_movimiento'             => 'INGRESO',
                        'documento'                   => 'DEVOLUCION_EDIT',
                        'numero_documento'            => $numero_referencia . '-AJ-' . $id_componente,
                        'cantidad_entrada'            => $devolverLote,
                        'cantidad_salida'             => 0,
                        'cantidad_saldo'              => $stockActual,
                        'codigo_referencia_movimiento' => $movDevId,
                        'observaciones'               => 'Devolución por edición de componente #' . $id_componente,
                        'usuario_registro'            => $usuario_id,
                    ]);

                    $devolver -= $devolverLote;
                }
            }

            if (!empty($componente->codigo_formula_produccion)) {
                $codigo_pep = $this->determinarCodigoPEP($componente->codigo_formula_produccion);
            } elseif ($componente->codigo_tipo_producto === 'PEP') {
                $codigo_pep = $this->determinarPEPdesdeProducto($componente->codigo_producto);
            } elseif ($componente->codigo_tipo_producto !== 'ACT') {
                $op = DB::table('orden_produccion_global')->where('idop', $idop)->first();
                if ($op && !empty($op->descripcion_producto_proceso)) {
                    $producto_pep = DB::table('producto')
                        ->where('descripcion', $op->descripcion_producto_proceso)
                        ->where('codigo_tipo_producto', 'PEP')
                        ->where('estado', 1)
                        ->first();
                    $codigo_pep = $producto_pep ? $producto_pep->codigo : null;
                } else {
                    $codigo_pep = null;
                }
            } else {
                $codigo_pep = null;
            }

            if ($codigo_pep && $diferencia != 0) {
                $pepRow = DB::table('produccion_ingresos_proceso')
                    ->where('id_proceso', $id)
                    ->where('codigo_producto_proceso', $codigo_pep)
                    ->first();
                if ($pepRow) {
                    if ($pepRow->estado === 'APROBADO' && $diferencia < 0) {
                        throw new \Exception("No se puede reducir la cantidad porque el PEP asociado ya fue recibido en almacén.");
                    }
                    $nuevaCantidadPEP = max(0, $pepRow->cantidad + $diferencia);
                    if ($nuevaCantidadPEP <= 0) {
                        DB::table('produccion_ingresos_proceso')->where('id', $pepRow->id)->delete();
                    } else {
                        DB::table('produccion_ingresos_proceso')->where('id', $pepRow->id)->update(['cantidad' => $nuevaCantidadPEP]);
                    }
                }
            }

            $componente->update([
                'codigo_trabajador' => $request->codigo_trabajador,
                'descripcion_trabajador' => $request->codigo_trabajador
                    ? DB::table('trabajador')->where('codigo', $request->codigo_trabajador)->value('nombre')
                    : $componente->descripcion_trabajador,
                'fecha_inicio' => $request->fecha_inicio ?? $componente->fecha_inicio,
                'fecha_fin'    => $request->fecha_fin ?? $componente->fecha_fin,
                'hora_inicio'  => $request->hora_inicio ?? $componente->hora_inicio,
                'hora_fin'     => $request->hora_fin ?? $componente->hora_fin,
                'fecha_inicio_maquina' => $request->fecha_inicio_maquina ?? $componente->fecha_inicio_maquina,
                'fecha_fin_maquina'    => $request->fecha_fin_maquina ?? $componente->fecha_fin_maquina,
                'hora_inicio_maquina'  => $request->hora_inicio_maquina ?? $componente->hora_inicio_maquina,
                'hora_fin_maquina'     => $request->hora_fin_maquina ?? $componente->hora_fin_maquina,
                'cantidad'     => $nuevaCantidad,
            ]);

            DB::commit();
            return back()->with('success', 'Componente #' . $id_componente . ' actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar componente: ' . $e->getMessage());
        }
    }

    public function finalizar(Request $request, $idop, $id)
    {
        try {
            DB::beginTransaction();

            $proceso = OrdenProceso::where('id', $id)->lockForUpdate()->firstOrFail();
            if ($proceso->estado_avance === 'COMPLETADO') {
                throw new \Exception("El proceso ya está COMPLETADO.");
            }

            $count = ComponenteOrdenProduccion::where('id_proceso', $id)->where('estado', 1)->count();
            if ($count == 0) {
                DB::rollBack();
                return back()->with('error', 'Debe registrar al menos un material o actividad para finalizar.');
            }

            // 1. Calcular Costo Total de Materiales Consumidos
            $costo_materiales = DB::table('movimientos_inventario')
                ->join('componentes_orden_produccion_global as c', function($join) use ($id) {
                    $join->on('movimientos_inventario.componente_origen_id', '=', 'c.id_op_componentes')
                         ->where('c.id_proceso', '=', $id)
                         ->where('c.estado', '=', 1);
                })
                ->where('movimientos_inventario.documento_referencia', 'PRODUCCION')
                ->where('movimientos_inventario.tipo_movimiento', 'SALIDA')
                ->where('movimientos_inventario.estado', 1)
                ->sum('movimientos_inventario.total');

            // 2. Calcular Costo Mano de Obra y Máquina
            $costo_hora_hombre = DB::table('parametros_sistema')->where('codigo_parametro', 'COSTO_HORA_HOMBRE')->value('valor') ?? 0;
            $costo_hora_maquina = DB::table('parametros_sistema')->where('codigo_parametro', 'COSTO_HORA_MAQUINA')->value('valor') ?? 0;

            $componentes = DB::table('componentes_orden_produccion_global')
                ->where('id_proceso', $id)
                ->where('estado', 1)
                ->get();

            $costo_mano_obra = 0;
            $costo_maquina = 0;
            
            $horas_hombre_total = 0;
            $horas_maquina_total = 0;

            $min_inicio_maq = null;
            $max_fin_maq = null;

            foreach ($componentes as $comp) {
                if ($comp->fecha_inicio && $comp->hora_inicio && $comp->fecha_fin && $comp->hora_fin) {
                    $inicio = \Carbon\Carbon::parse($comp->fecha_inicio . ' ' . $comp->hora_inicio);
                    $fin = \Carbon\Carbon::parse($comp->fecha_fin . ' ' . $comp->hora_fin);
                    $horas = $inicio->diffInMinutes($fin) / 60;
                    if ($horas > 0) {
                        $horas_hombre_total += $horas;
                        $costo_mano_obra += ($horas * $costo_hora_hombre);
                    }
                }
                
                if ($comp->fecha_inicio_maquina && $comp->hora_inicio_maquina && $comp->fecha_fin_maquina && $comp->hora_fin_maquina) {
                    $inicio = \Carbon\Carbon::parse($comp->fecha_inicio_maquina . ' ' . $comp->hora_inicio_maquina);
                    $fin = \Carbon\Carbon::parse($comp->fecha_fin_maquina . ' ' . $comp->hora_fin_maquina);
                    
                    if (!$min_inicio_maq || $inicio < $min_inicio_maq) $min_inicio_maq = $inicio;
                    if (!$max_fin_maq || $fin > $max_fin_maq) $max_fin_maq = $fin;
                }
            }
            
            if ($min_inicio_maq && $max_fin_maq) {
                $horas_maquina_total = $min_inicio_maq->diffInMinutes($max_fin_maq) / 60;
                if ($horas_maquina_total > 0) {
                    $costo_maquina = $horas_maquina_total * $costo_hora_maquina;
                }
            }

            $costo_total = $costo_materiales + $costo_mano_obra + $costo_maquina;

            if ($costo_mano_obra > 0) {
                DB::table('produccion_costos')->insert([
                    'idop' => $idop,
                    'tipo_costo' => 'MANO_OBRA',
                    'descripcion' => 'Horas Hombre Calculadas',
                    'cantidad' => $horas_hombre_total,
                    'costo_unitario' => $costo_hora_hombre,
                    'costo_total' => $costo_mano_obra,
                    'moneda' => 'PEN',
                    'fecha_costo' => now()->toDateString(),
                    'usuario_registro' => auth()->id() ?? null
                ]);
            }
            
            if ($costo_maquina > 0) {
                DB::table('produccion_costos')->insert([
                    'idop' => $idop,
                    'tipo_costo' => 'EQUIPOS',
                    'descripcion' => 'Horas Máquina Calculadas',
                    'cantidad' => $horas_maquina_total,
                    'costo_unitario' => $costo_hora_maquina,
                    'costo_total' => $costo_maquina,
                    'moneda' => 'PEN',
                    'fecha_costo' => now()->toDateString(),
                    'usuario_registro' => auth()->id() ?? null
                ]);
            }


            // 3. Distribuir Costos y Actualizar Inventario
            $pep_movimientos = DB::table('movimientos_inventario')
                ->where('documento_referencia', 'PRODUCCION_PEP')
                ->where('numero_referencia', "OP-{$idop}-PROC-{$id}")
                ->where('tipo_movimiento', 'INGRESO')
                ->where('estado', 1)
                ->get();

            $cantidad_producida = $pep_movimientos->sum('cantidad');

            if ($cantidad_producida > 0) {
                $costo_unitario_real = round($costo_total / $cantidad_producida, 9);
                
                $productos_almacenes_afectados = [];

                foreach ($pep_movimientos as $mov) {
                    $nuevo_total = round($mov->cantidad * $costo_unitario_real, 2);

                    DB::table('movimientos_inventario')
                        ->where('id_movimiento', $mov->id_movimiento)
                        ->update([
                            'costo_unitario' => $costo_unitario_real,
                            'total' => $nuevo_total
                        ]);

                    DB::table('kardex')
                        ->where('codigo_referencia_movimiento', $mov->id_movimiento)
                        ->where('documento', 'RECEPCION_PEP')
                        ->update([
                            'costo_entrada' => $costo_unitario_real,
                            'total_entrada' => $nuevo_total
                        ]);
                    
                    $clave_recalculo = $mov->codigo_producto . '|' . $mov->codigo_almacen;
                    $productos_almacenes_afectados[$clave_recalculo] = [
                        'producto' => $mov->codigo_producto,
                        'almacen'  => $mov->codigo_almacen
                    ];
                }

                $kardexService = app(\App\Services\KardexService::class);
                foreach ($productos_almacenes_afectados as $afectado) {
                    $kardexService->recalcular($afectado['producto'], $afectado['almacen']);
                }
            }

            OrdenProceso::where('id', $id)->update(['estado_avance' => 'COMPLETADO', 'fecha_fin' => now()]);

            DB::commit();
            return back()->with('success', 'Proceso finalizado y cerrado correctamente. Costos de producción asignados y Kardex actualizado.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function destroyComponente($idop, $id, $id_componente)
    {
        try {
            DB::beginTransaction();

            $componente = ComponenteOrdenProduccion::where('id_op_componentes', $id_componente)
                ->where('id_proceso', $id)
                ->where('estado', 1)
                ->lockForUpdate()
                ->firstOrFail();

            $proceso = OrdenProceso::findOrFail($id);
            if ($proceso->estado_avance === 'COMPLETADO') {
                throw new \Exception("No se puede eliminar un componente de un proceso COMPLETADO.");
            }

            $usuario_id = Auth::user()->id_usuario ?? 1;

            if ($componente->codigo_tipo_producto !== 'ACT') {
                $movimientos = DB::table('movimientos_inventario')
                    ->where('componente_origen_id', $id_componente)
                    ->where('tipo_movimiento', 'SALIDA')
                    ->where('documento_referencia', 'PRODUCCION')
                    ->where('estado', 1)
                    ->get();

                $numero_referencia_ext = "OP-{$idop}-PROC-{$id}-COMP-{$id_componente}";

                foreach ($movimientos as $mov) {
                    DB::table('inventario')
                        ->where('codigo_producto', $mov->codigo_producto)
                        ->where('lote', $mov->lote)
                        ->where('codigo_almacen', $mov->codigo_almacen)
                        ->update([
                            'stock_actual' => DB::raw("stock_actual + {$mov->cantidad}"),
                            'fecha_ultimo_movimiento' => now()
                        ]);

                    DB::table('inventario')
                        ->where('codigo_producto', $mov->codigo_producto)
                        ->where('lote', $mov->lote)
                        ->where('codigo_almacen', $mov->codigo_almacen)
                        ->where('stock_actual', '>', 0)
                        ->where('estado', 0)
                        ->update([
                            'estado' => 1,
                            'fecha_ultimo_movimiento' => now(),
                            'usuario_ultimo_movimiento' => $usuario_id
                        ]);

                    $stockActual = DB::table('inventario')
                        ->where('codigo_producto', $mov->codigo_producto)
                        ->where('lote', $mov->lote)
                        ->where('codigo_almacen', $mov->codigo_almacen)
                        ->value('stock_actual') ?? $mov->cantidad;

                    $movExtId = DB::table('movimientos_inventario')->insertGetId([
                        'codigo_almacen' => $mov->codigo_almacen,
                        'codigo_producto' => $mov->codigo_producto,
                        'lote' => $mov->lote,
                        'tipo_movimiento' => 'INGRESO',
                        'cantidad' => $mov->cantidad,
                        'costo_unitario' => $mov->costo_unitario,
                        'total' => $mov->cantidad * $mov->costo_unitario,
                        'documento_referencia' => 'EXTORNO_CONS',
                        'numero_referencia' => $numero_referencia_ext,
                        'idop' => $idop,
                        'observaciones' => "Devolución por anulación de componente #{$id_componente}",
                        'usuario_movimiento' => $usuario_id,
                        'fecha_movimiento' => now(),
                        'estado' => 1,
                        'tiene_kardex' => true
                    ]);

                    DB::table('kardex')->insert([
                        'codigo_almacen'       => $mov->codigo_almacen,
                        'codigo_producto'      => $mov->codigo_producto,
                        'fecha_movimiento'     => now(),
                        'tipo_movimiento'      => 'EXTORNO',
                        'documento'            => 'EXTORNO_CONS',
                        'numero_documento'     => $numero_referencia_ext,
                        'cantidad_entrada'     => $mov->cantidad,
                        'cantidad_salida'      => 0,
                        'cantidad_saldo'       => $stockActual,
                        'codigo_referencia_movimiento' => $movExtId,
                        'observaciones'        => "Devolución por anulación de componente OP-{$idop}",
                        'usuario_registro'     => $usuario_id
                    ]);

                    DB::table('movimientos_inventario')
                        ->where('id_movimiento', $mov->id_movimiento)
                        ->update(['tiene_kardex' => true]);
                }
            }

            if (!empty($componente->codigo_formula_produccion)) {
                $codigo_pep = $this->determinarCodigoPEP($componente->codigo_formula_produccion);
            } elseif ($componente->codigo_tipo_producto === 'PEP') {
                $codigo_pep = $this->determinarPEPdesdeProducto($componente->codigo_producto);
            } elseif ($componente->codigo_tipo_producto !== 'ACT') {
                $op = DB::table('orden_produccion_global')->where('idop', $idop)->first();
                if ($op && !empty($op->descripcion_producto_proceso)) {
                    $producto_pep = DB::table('producto')
                        ->where('descripcion', $op->descripcion_producto_proceso)
                        ->where('codigo_tipo_producto', 'PEP')
                        ->where('estado', 1)
                        ->first();
                    $codigo_pep = $producto_pep ? $producto_pep->codigo : null;
                } else {
                    $codigo_pep = null;
                }
            } else {
                $codigo_pep = null;
            }

            if ($codigo_pep) {
                $pepRow = DB::table('produccion_ingresos_proceso')
                    ->where('id_proceso', $id)
                    ->where('codigo_producto_proceso', $codigo_pep)
                    ->first();
                if ($pepRow) {
                    if ($pepRow->estado === 'APROBADO') {
                        throw new \Exception("No se puede eliminar el componente porque el PEP asociado ya fue recibido en almacén.");
                    }
                    $nuevaCantidadPEP = max(0, $pepRow->cantidad - $componente->cantidad);
                    if ($nuevaCantidadPEP <= 0) {
                        DB::table('produccion_ingresos_proceso')->where('id_ingreso', $pepRow->id_ingreso)->delete();
                    } else {
                        DB::table('produccion_ingresos_proceso')->where('id_ingreso', $pepRow->id_ingreso)->update(['cantidad' => $nuevaCantidadPEP]);
                    }
                }
            }

            $componente->update(['estado' => 0]);

            DB::commit();
            return back()->with('success', 'Registro desactivado correctamente. Stock restaurado, extorno registrado en kardex y PEP ajustado.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al desactivar: ' . $e->getMessage());
        }
    }

    private function determinarCodigoPEP($codigo_formula) {
        if (empty($codigo_formula)) return null;
        // Primero verificar si la fórmula tiene asignado explícitamente un producto resultante
        $res = DB::table('formula_produccion')->where('codigo', $codigo_formula)->value('codigo_producto_resultante');
        if ($res) return $res;
        // Si no, asumir que el código de la fórmula es el código del producto resultante (puede ser PEP, REC, PDT)
        $res = DB::table('producto')
            ->where('codigo', $codigo_formula)
            ->whereIn('codigo_tipo_producto', ['PEP', 'REC', 'PDT'])
            ->where('estado', 1)
            ->value('codigo');
        return $res ?: null;
    }

    private function determinarPEPdesdeProducto($codigo_producto)
    {
        // Si el producto existe como PEP, retornarlo directamente
        $producto = DB::table('producto')
            ->where('codigo', $codigo_producto)
            ->where('codigo_tipo_producto', 'PEP')
            ->where('estado', 1)
            ->first();
        if ($producto) return $producto->codigo;

        // Fallback original para MZ07-
        if (str_starts_with($codigo_producto, 'MZ07-')) {
            $codigo_inyectado = str_replace('MZ07-', 'CA07-', $codigo_producto);
            $existe = DB::table('producto')
                ->where('codigo', $codigo_inyectado)
                ->where('estado', 1)
                ->exists();
            if ($existe) return $codigo_inyectado;
        }
        return null;
    }

    private function generarCodigoPEP($codigo, $color, $proceso_id) {
        $base = $codigo . '-' . ($color ?: 'SC') . '-P' . $proceso_id . '-' . date('YmdHis');
        return substr(preg_replace('/[^A-Za-z0-9\-]/', '', $base), 0, 45);
    }
}
