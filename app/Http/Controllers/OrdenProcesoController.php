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
            $proceso->total_componentes = DB::table('componentes_orden_produccion_global')
                ->where('id_proceso', $proceso->id)
                ->where(function($query) {
                    $query->where('estado', 1)->orWhereNull('estado');
                })
                ->count();
                
            // Fetch the process description (if not already stored) or use Eloquent relationships later
            $desc = DB::table('proceso_produccion')->where('codigo', $proceso->codigo_proceso)->value('descripcion');
            $proceso->proceso_desc = $desc ?? $proceso->descripcion_proceso;
        }
        
        return view('produccion.procesos.index', compact('orden', 'procesos'));
    }

    public function create($idop)
    {
        $orden = OrdenProduccion::where('idop', $idop)->where('activo', 1)->firstOrFail();
        $cat_procesos = ProcesoProduccion::where('estado', 1)->get();
        
        return view('produccion.procesos.create', compact('orden', 'cat_procesos'));
    }

    public function store(Request $request, $idop)
    {
        $request->validate([
            'codigo_proceso' => 'required|string',
        ]);

        try {
            $procesoRef = ProcesoProduccion::findOrFail($request->codigo_proceso);
            
            $orden = OrdenProduccion::where('idop', $idop)->where('activo', 1)->firstOrFail();

            $max_seq = OrdenProceso::where('idop', $idop)->where('estado', 1)->max('secuencia');
            $secuencia_nueva = $max_seq ? $max_seq + 10 : 10;
            
            OrdenProceso::create([
                'idop' => $idop,
                'secuencia' => $secuencia_nueva,
                'codigo_proceso' => $request->codigo_proceso,
                'descripcion_proceso' => $procesoRef->descripcion,
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
            
            $proceso = OrdenProceso::findOrFail($id);
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
                        
                    DB::table('movimientos_inventario')->insert([
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
                        'fecha_movimiento' => now()
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
                    ->update(['estado' => 1]);
                    
                // Registrar el ingreso por devolución
                DB::table('movimientos_inventario')->insert([
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
                    'estado' => 1
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
                }
            })
            ->orderBy('codigo')
            ->get();

        $registrados = ComponenteOrdenProduccion::where('id_proceso', $id)->where('estado', 1)->orderBy('id_op_componentes', 'desc')->get();
        $tiene_componentes = ($registrados->count() > 0);

        $tipos_producto = DB::table('tipo_producto')->select('codigo', 'descripcion')->where('estado', 1)->get();
        
        $productos_raw = DB::table('producto')
            ->select('codigo', 'descripcion', 'codigo_tipo_producto')
            ->where(function ($query) {
                $query->where('estado', 1)->orWhereNull('estado');
            })
            ->whereIn('codigo_tipo_producto', ['MTP', 'SUM', 'AUX', 'EMB', 'ENV', 'PEP'])
            ->get();
            
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
                $moldes = DB::table('molde')
                    ->select('codigo', 'descripcion', DB::raw('(SELECT codigo_formula FROM composicion_formula cf WHERE cf.codigo_molde = molde.codigo LIMIT 1) as codigo_formula'))
                    ->where('activo', 1)
                    ->get();
            }
        }

        $centros_trabajo = DB::table('centro_trabajo_produccion as ct')
            ->join('proceso_centro_trabajo as pct', 'ct.codigo', '=', 'pct.codigo_centro_trabajo')
            ->select('ct.codigo', 'ct.descripcion')
            ->where('pct.codigo_proceso', $proceso->codigo_proceso)
            ->get();

        return view('produccion.procesos.ejecucion', compact(
            'orden', 'proceso', 'estado_proceso_actual', 'es_actividad', 'es_mezclado', 'es_inyectado',
            'formulas_disponibles', 'registrados', 'tiene_componentes', 'tipos_producto', 'productos_raw',
            'colores', 'unidades', 'trabajadores', 'moldes', 'centros_trabajo'
        ));
    }

    public function getFormulaComponents(Request $request)
    {
        $codigo_formula = $request->codigo_formula;
        $codigo_molde = $request->codigo_molde;
        
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
                'u.descripcion as descripcion_unidad_medida'
            )
            ->where('cf.codigo_formula', $codigo_formula);

        if ($codigo_molde) {
            $query->where('cf.codigo_molde', $codigo_molde);
        }

        $componentes = $query->get();

        if ($componentes->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'La fórmula no tiene componentes o no coinciden los parámetros.']);
        }

        return response()->json(['success' => true, 'componentes' => $componentes]);
    }

    public function storeComponentes(Request $request, $idop, $id)
    {
        $componentes = json_decode($request->componentes_json ?? '[]', true);
        $merma_kg = floatval($request->merma_kg ?? 0);
        
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

            $proceso = OrdenProceso::findOrFail($id);
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

            if ($merma_kg > $total_insumos_ingresados) {
                throw new \Exception("La merma ($merma_kg KG) no puede ser mayor a la cantidad de materiales ingresados ($total_insumos_ingresados KG).");
            }

            foreach ($cantidades_agrupadas as $codigo_prod => $cant_req) {
                $stock_disp = DB::table('inventario as i')
                    ->join('almacen as a', 'i.codigo_almacen', '=', 'a.codigo_almacen')
                    ->where('i.codigo_producto', $codigo_prod)
                    ->where('a.activo', 1)
                    ->where(function($q) { $q->where('i.estado', 1)->orWhereNull('i.estado'); })
                    ->sum('i.stock_actual') ?? 0;

                if ($stock_disp < $cant_req) {
                    $faltantes[] = "[$codigo_prod] Req: " . number_format($cant_req, 2) . " | Disp: " . number_format($stock_disp, 2);
                }
            }

            if (!empty($faltantes)) {
                throw new \Exception("STOCK INSUFICIENTE. Faltan materiales en almacén: " . implode(" ; ", $faltantes));
            }

            $grupos_pep = []; 

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
                }

                $cantidad_restante = $cantidad;
                $lotes = DB::table('inventario as i')
                    ->join('almacen as a', 'i.codigo_almacen', '=', 'a.codigo_almacen')
                    ->select('i.id_inventario', 'i.stock_actual', 'i.lote', 'i.costo_promedio', 'i.codigo_almacen')
                    ->where('i.codigo_producto', $codigo_producto)
                    ->where('a.activo', 1)
                    ->where('i.stock_actual', '>', 0)
                    ->where(function($q) { $q->where('i.estado', 1)->orWhereNull('i.estado'); })
                    ->orderBy('i.fecha_vencimiento', 'asc')
                    ->orderBy('i.id_inventario', 'asc')
                    ->get();
                
                foreach ($lotes as $lote) {
                    if ($cantidad_restante <= 0) break;
                    $consumo = min($lote->stock_actual, $cantidad_restante);
                    
                    DB::table('movimientos_inventario')->insert([
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
                        'observaciones' => 'Consumo proceso',
                        'usuario_movimiento' => $usuario_id,
                        'fecha_movimiento' => now(),
                        'estado' => 1
                    ]);
                    
                    $trace_movimientos++;
                    $nuevo_stock = $lote->stock_actual - $consumo;
                    
                    DB::table('inventario')->where('id_inventario', $lote->id_inventario)->update([
                        'stock_actual' => $nuevo_stock,
                        'estado' => ($nuevo_stock > 0 ? 1 : 0)
                    ]);
                    
                    $cantidad_restante -= $consumo;
                }

                ComponenteOrdenProduccion::create([
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
                    'estado' => 1
                ]);
            }

            if ($merma_kg > 0) {
                $merma_restante = $merma_kg;
                foreach ($grupos_pep as &$g) {
                    if ($merma_restante <= 0) break;
                    if ($g['cant'] >= $merma_restante) {
                        $g['cant'] -= $merma_restante;
                        $merma_restante = 0;
                    } else {
                        $merma_restante -= $g['cant'];
                        $g['cant'] = 0;
                    }
                }
            }

            $ingresos_creados = 0;
            foreach ($grupos_pep as $g) {
                if ($g['cant'] > 0) {
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
                        'estado' => 'PENDIENTE'
                    ]);
                    $ingresos_creados++;
                }
            }

            $merma_registrada = false;
            if ($merma_kg > 0) {
                $codigo_merma = 'MERMA-001';
                
                DB::table('producto')->insertOrIgnore([
                    'codigo' => $codigo_merma, 'descripcion' => 'MERMA / RECUPERABLE DE PRODUCCION', 'codigo_tipo_producto' => 'SUM', 'estado' => 1
                ]);
                DB::table('almacen')->insertOrIgnore([
                    'codigo_almacen' => 'ALM-REC', 'descripcion' => 'ALMACEN DE RECICLAJE Y MERMA', 'activo' => 1
                ]);

                $lote_merma = 'MERMA-P' . $id . '-' . date('Ymd');
                
                $inv_m = DB::table('inventario')->where('codigo_producto', $codigo_merma)->where('codigo_almacen', 'ALM-REC')->where('lote', $lote_merma)->first();
                if ($inv_m) {
                    DB::table('inventario')->where('id_inventario', $inv_m->id_inventario)->increment('stock_actual', $merma_kg);
                } else {
                    DB::table('inventario')->insert([
                        'codigo_almacen' => 'ALM-REC', 'codigo_producto' => $codigo_merma, 'lote' => $lote_merma, 'stock_actual' => $merma_kg, 'costo_promedio' => 0, 'estado' => 1
                    ]);
                }

                DB::table('movimientos_inventario')->insert([
                    'codigo_almacen' => 'ALM-REC', 'codigo_producto' => $codigo_merma, 'lote' => $lote_merma, 'tipo_movimiento' => 'INGRESO', 'cantidad' => $merma_kg, 'costo_unitario' => 0, 'total' => 0, 'documento_referencia' => 'PRODUCCION', 'numero_referencia' => $numero_referencia, 'idop' => $idop, 'observaciones' => 'Ingreso por merma de proceso', 'usuario_movimiento' => $usuario_id, 'fecha_movimiento' => now(), 'estado' => 1
                ]);
                
                $merma_registrada = true;
            }

            OrdenProceso::where('id', $id)->where(function($q) {
                $q->where('estado_avance', 'PENDIENTE')->orWhereNull('estado_avance');
            })->update(['estado_avance' => 'EN_PROCESO']);

            DB::commit();
            return back()->with('success', "Componentes guardados. Movimientos: $trace_movimientos, PEPs: $ingresos_creados, Merma: " . ($merma_registrada ? "$merma_kg KG" : "0 KG"));
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al procesar: ' . $e->getMessage());
        }
    }

    public function finalizar(Request $request, $idop, $id)
    {
        try {
            $count = ComponenteOrdenProduccion::where('id_proceso', $id)->where('estado', 1)->count();
            if ($count == 0) {
                return back()->with('error', 'Debe registrar al menos un material o actividad para finalizar.');
            }
            OrdenProceso::where('id', $id)->update(['estado_avance' => 'COMPLETADO', 'fecha_fin' => now()]);
            return back()->with('success', 'Proceso finalizado y cerrado correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function destroyComponente($idop, $id, $id_componente)
    {
        try {
            ComponenteOrdenProduccion::where('id_op_componentes', $id_componente)->update(['estado' => 0]);
            return back()->with('success', 'Registro desactivado correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al desactivar: ' . $e->getMessage());
        }
    }

    private function determinarCodigoPEP($codigo_formula) {
        if (empty($codigo_formula)) return null;
        $res = DB::table('formula_produccion')->where('codigo', $codigo_formula)->value('codigo_producto_resultante');
        if ($res) return $res;
        $res = DB::table('producto')->where('codigo', $codigo_formula)->where('codigo_tipo_producto', 'PEP')->where('estado', 1)->value('codigo');
        return $res ?: null;
    }

    private function generarCodigoPEP($codigo, $color, $proceso_id) {
        $base = $codigo . '-' . ($color ?: 'SC') . '-P' . $proceso_id . '-' . date('YmdHis');
        return substr(preg_replace('/[^A-Za-z0-9\-]/', '', $base), 0, 45);
    }
}
