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

        try {
            DB::beginTransaction();

            $productoOrigen = Producto::findOrFail($request->codigo_producto);
            
            // Obtener stock y costo actual
            $inv = DB::table('inventario')
                ->where('codigo_producto', $request->codigo_producto)
                ->where('codigo_almacen', $request->codigo_almacen)
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
            DB::table('kardex')->insert([
                'codigo_producto' => $request->codigo_producto,
                'codigo_almacen' => $request->codigo_almacen,
                'tipo_movimiento' => 'SALIDA',
                'numero_documento' => $numeroDoc,
                'fecha_movimiento' => now(),
                'cantidad_salida' => $request->cantidad,
                'costo_salida' => $costoUnitario,
                'total_salida' => $costoTotal,
                'motivo' => 'AJUSTE POR MERMA',
                'usuario_registro' => Auth::id(),
                'cantidad_entrada' => 0,
                'costo_entrada' => 0,
                'total_entrada' => 0,
                'cantidad_saldo' => 0,
                'costo_promedio' => 0,
                'total_saldo' => 0
            ]);
            $kardexService->recalcular($request->codigo_producto, $request->codigo_almacen);

            // 2. ENTRADA del producto recuperado si aplica
            if (in_array($request->tipo_merma, ['RECUPERABLE', 'MOLIDO'])) {
                $param = ParametroSistema::where('codigo_parametro', 'PORCENTAJE_COSTO_RECICLADO')->first();
                $porcentaje = $param ? (float) $param->valor : 0.8;
                $costoReciclado = $costoUnitario * $porcentaje;

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
                
                // Asegurar que exista inventario para el producto recuperado
                DB::table('inventario')->insertOrIgnore([
                    'codigo_producto' => $codRecuperado,
                    'codigo_almacen' => $request->codigo_almacen,
                    'stock_actual' => 0,
                    'stock_minimo' => 0,
                    'stock_maximo' => 0,
                    'costo_promedio' => 0
                ]);

                DB::table('kardex')->insert([
                    'codigo_producto' => $codRecuperado,
                    'codigo_almacen' => $request->codigo_almacen,
                    'tipo_movimiento' => 'INGRESO',
                    'numero_documento' => $numeroDoc,
                    'fecha_movimiento' => now(),
                    'cantidad_entrada' => $request->cantidad,
                    'costo_entrada' => $costoReciclado,
                    'total_entrada' => $request->cantidad * $costoReciclado,
                    'motivo' => 'INGRESO POR MOLIENDA',
                    'usuario_registro' => Auth::id(),
                    'cantidad_salida' => 0,
                    'costo_salida' => 0,
                    'total_salida' => 0,
                    'cantidad_saldo' => 0,
                    'costo_promedio' => 0,
                    'total_saldo' => 0
                ]);
                $kardexService->recalcular($codRecuperado, $request->codigo_almacen);
            }

            DB::commit();
            return redirect()->route('mermas.index')->with('success', 'Merma registrada correctamente y Kardex actualizado.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}
