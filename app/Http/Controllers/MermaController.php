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
            $query->where('codigo_producto', 'like', "%{$request->search}%")
                  ->orWhere('descripcion_producto', 'like', "%{$request->search}%");
        }
        $mermas = $query->orderBy('created_at', 'desc')->paginate(15);
        return view('mermas.index', compact('mermas'));
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

    public function getProductosPorOP(Request $request)
    {
        $idop = $request->idop;
        
        $productos = DB::table('inventario')
            ->join('producto', 'inventario.codigo_producto', '=', 'producto.codigo')
            ->join('produccion_ingresos_proceso', function($join) use ($idop) {
                $join->on('produccion_ingresos_proceso.codigo_producto_proceso', '=', 'inventario.codigo_producto')
                     ->where('produccion_ingresos_proceso.idop', '=', $idop);
            })
            ->where('inventario.stock_actual', '>', 0)
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
            'motivo' => 'nullable|string|max:255'
        ]);

        $pura = (float) $request->cantidad_pura;
        $recuperada = (float) $request->cantidad_recuperada;
        $cantidadTotalMerma = $pura + $recuperada;

        if ($cantidadTotalMerma <= 0) {
            return back()->with('error', 'Debe ingresar al menos una cantidad mayor a 0.');
        }

        if (str_starts_with($request->codigo_producto, 'REC-')) {
            return back()->with('error', 'No se puede registrar merma de un producto ya recuperado (REC-).');
        }

        try {
            DB::beginTransaction();

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

            // Obtener las materias primas del proceso
            $componentes = DB::table('componentes_orden_produccion_global')
                ->where('idop', $request->id_orden_produccion)
                ->where('id_proceso', $procesoIngreso->id_proceso)
                ->whereIn('codigo_tipo_producto', ['MTP', 'MAT', 'INS'])
                ->where('estado', 1)
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

                $codRecuperado = 'REC-' . $request->codigo_producto;

                $prodRecuperado = Producto::firstOrCreate(
                    ['codigo' => $codRecuperado],
                    [
                        'descripcion' => 'RECUPERADO - ' . $productoOrigen->descripcion,
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
}
