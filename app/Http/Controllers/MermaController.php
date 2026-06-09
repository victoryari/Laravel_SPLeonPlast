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
            'codigo_producto' => 'required|exists:producto,codigo',
            'codigo_almacen' => 'required|exists:almacenes,codigo_almacen',
            'cantidad' => 'required|numeric|min:0.01',
            'tipo_merma' => 'required|in:PURA,RECUPERABLE,MOLIDO',
            'motivo' => 'nullable|string|max:255'
        ]);

        if (str_starts_with($request->codigo_producto, 'REC-')) {
            return back()->with('error', 'No se puede registrar merma de un producto ya recuperado (REC-).');
        }

        try {
            DB::beginTransaction();

            $productoOrigen = Producto::findOrFail($request->codigo_producto);
            
            // Obtener stock y costo actual CON LOCK
            $inv = DB::table('inventario')
                ->where('codigo_producto', $request->codigo_producto)
                ->where('codigo_almacen', $request->codigo_almacen)
                ->lockForUpdate()
                ->first();

            if (!$inv || $inv->stock_actual < $request->cantidad) {
                return back()->with('error', 'Stock insuficiente para registrar la merma en el almacén seleccionado.');
            }

            $costoUnitario = $inv->costo_promedio;
            $costoTotal = $costoUnitario * $request->cantidad;

            // Crear Merma
            $merma = Merma::create([
                'codigo_producto' => $request->codigo_producto,
                'descripcion_producto' => $productoOrigen->descripcion,
                'cantidad' => $request->cantidad,
                'costo_unitario' => $costoUnitario,
                'costo_total' => $costoTotal,
                'motivo' => $request->motivo,
                'tipo_merma' => $request->tipo_merma,
                'codigo_almacen' => $request->codigo_almacen,
                'estado' => 'REGISTRADA',
                'usuario_registro' => Auth::id()
            ]);

            // 1. SALIDA del producto origen
            $numeroDoc = 'MERMA-' . str_pad($merma->id_merma, 6, '0', STR_PAD_LEFT);
            $nuevoStock = $inv->stock_actual - $request->cantidad;
            DB::table('kardex')->insert([
                'codigo_producto' => $request->codigo_producto,
                'codigo_almacen' => $request->codigo_almacen,
                'fecha_movimiento' => now(),
                'tipo_movimiento' => 'SALIDA',
                'documento' => 'MERMA',
                'numero_documento' => $numeroDoc,
                'cantidad_entrada' => 0,
                'costo_entrada' => 0,
                'total_entrada' => 0,
                'cantidad_salida' => $request->cantidad,
                'costo_salida' => $costoUnitario,
                'total_salida' => $costoTotal,
                'cantidad_saldo' => $nuevoStock,
                'costo_promedio' => $inv->costo_promedio,
                'total_saldo' => $nuevoStock * $inv->costo_promedio,
                'observaciones' => 'AJUSTE POR MERMA',
                'usuario_registro' => Auth::id()
            ]);

            // Actualizar inventario origen
            DB::table('inventario')
                ->where('id_inventario', $inv->id_inventario)
                ->update([
                    'stock_actual' => $nuevoStock,
                    'fecha_ultimo_movimiento' => now(),
                    'usuario_ultimo_movimiento' => Auth::id()
                ]);

            // 2. ENTRADA del producto recuperado si aplica
            if (in_array($request->tipo_merma, ['RECUPERABLE', 'MOLIDO'])) {
                $param = ParametroSistema::where('codigo_parametro', 'PORCENTAJE_COSTO_RECICLADO')->lockForUpdate()->first();
                $porcentaje = $param ? (float) $param->valor : 0.8;
                $costoReciclado = $costoUnitario * $porcentaje;

                $codRecuperado = 'REC-' . $request->codigo_producto;

                // Crear producto recuperado si no existe (con lock for update previene duplicados)
                $prodRecuperado = Producto::firstOrCreate(
                    ['codigo' => $codRecuperado],
                    [
                        'descripcion' => 'RECUPERADO - ' . $productoOrigen->descripcion,
                        'codigo_tipo_producto' => $productoOrigen->codigo_tipo_producto,
                        'codigo_unidad_medida' => $productoOrigen->codigo_unidad_medida,
                        'estado' => 1
                    ]
                );
                
                // Asegurar que exista inventario para el producto recuperado
                DB::table('inventario')->updateOrInsert(
                    ['codigo_producto' => $codRecuperado, 'codigo_almacen' => $request->codigo_almacen],
                    [
                        'stock_actual' => 0,
                        'stock_minimo' => 0,
                        'stock_maximo' => 0,
                        'costo_promedio' => 0
                    ]
                );

                // Obtener último Kardex para calcular saldos correctos
                $ultimoKardexRec = DB::table('kardex')
                    ->where('codigo_producto', $codRecuperado)
                    ->where('codigo_almacen', $request->codigo_almacen)
                    ->orderBy('fecha_movimiento', 'desc')
                    ->orderBy('id_kardex', 'desc')
                    ->lockForUpdate()
                    ->first();

                $saldoAnteriorRec = $ultimoKardexRec->cantidad_saldo ?? 0;
                $costoPromAnteriorRec = $ultimoKardexRec->costo_promedio ?? 0;
                $nuevoSaldoRec = $saldoAnteriorRec + $request->cantidad;
                $totalEntradaRec = round($request->cantidad * $costoReciclado, 2);
                $nuevoTotalSaldoRec = $saldoAnteriorRec * $costoPromAnteriorRec + $totalEntradaRec;
                $nuevoCostoPromRec = $nuevoSaldoRec > 0 ? round($nuevoTotalSaldoRec / $nuevoSaldoRec, 9) : 0;

                DB::table('kardex')->insert([
                    'codigo_producto' => $codRecuperado,
                    'codigo_almacen' => $request->codigo_almacen,
                    'fecha_movimiento' => now(),
                    'tipo_movimiento' => 'INGRESO',
                    'documento' => 'MERMA',
                    'numero_documento' => $numeroDoc,
                    'cantidad_entrada' => $request->cantidad,
                    'costo_entrada' => $costoReciclado,
                    'total_entrada' => $totalEntradaRec,
                    'cantidad_salida' => 0,
                    'costo_salida' => 0,
                    'total_salida' => 0,
                    'cantidad_saldo' => $nuevoSaldoRec,
                    'costo_promedio' => $nuevoCostoPromRec,
                    'total_saldo' => round($nuevoSaldoRec * $nuevoCostoPromRec, 9),
                    'observaciones' => 'INGRESO POR MOLIENDA',
                    'usuario_registro' => Auth::id()
                ]);

                // Actualizar inventario recuperado
                DB::table('inventario')
                    ->where('codigo_producto', $codRecuperado)
                    ->where('codigo_almacen', $request->codigo_almacen)
                    ->update([
                        'stock_actual' => DB::raw("stock_actual + {$request->cantidad}"),
                        'costo_promedio' => $nuevoCostoPromRec,
                        'fecha_ultimo_movimiento' => now(),
                        'usuario_ultimo_movimiento' => Auth::id()
                    ]);
            }

            // Recalcular saldos y costos del producto origen
            $kardexService->recalcular($request->codigo_producto, $request->codigo_almacen);

            DB::commit();
            return redirect()->route('mermas.index')->with('success', 'Merma registrada correctamente y Kardex actualizado.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}
