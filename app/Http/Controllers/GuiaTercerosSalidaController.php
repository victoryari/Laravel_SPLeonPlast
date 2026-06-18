<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\GuiaRemisionTercerosSalida;
use App\Models\GuiaRemisionTercerosSalidaDetalle;
use App\Services\KardexService;
use Carbon\Carbon;

class GuiaTercerosSalidaController extends Controller
{
    public function index()
    {
        $guias = GuiaRemisionTercerosSalida::orderBy('fecha_emision', 'desc')->paginate(20);
        return view('terceros.salidas.index', compact('guias'));
    }

    public function create()
    {
        $almacenes = DB::table('almacen')->where('activo', 1)->get();
        $proveedores = DB::table('proveedores')->where('activo', 1)->get();
        // Solo listar productos que sean origen de transformación (peps mapeados)
        $pepsMapeados = DB::table('terceros_mapeo_productos')
            ->where('estado', 1)
            ->pluck('codigo_producto_origen')
            ->toArray();
            
        $productos = DB::table('producto')
            ->whereIn('codigo', $pepsMapeados)
            ->where('estado', 1)
            ->get();
            
        return view('terceros.salidas.create', compact('almacenes', 'proveedores', 'productos'));
    }

    public function store(Request $request, KardexService $kardexService)
    {
        $request->validate([
            'numero_guia' => 'required|string|max:50|unique:guia_remision_terceros_salida,numero_guia',
            'fecha_emision' => 'required|date',
            'proveedor_destino' => 'required|string',
            'codigo_almacen_origen' => 'required|string',
            'productos' => 'required|array|min:1',
            'productos.*.codigo' => 'required|string',
            'productos.*.cantidad' => 'required|numeric|min:0.01',
        ]);

        try {
            DB::beginTransaction();
            
            $prov = DB::table('proveedores')->where('razon_social', $request->proveedor_destino)->first();
            $ruc = $prov ? $prov->ruc : null;

            $guia = GuiaRemisionTercerosSalida::create([
                'numero_guia' => $request->numero_guia,
                'fecha_emision' => $request->fecha_emision,
                'codigo_almacen_origen' => $request->codigo_almacen_origen,
                'proveedor_destino' => $request->proveedor_destino,
                'ruc_proveedor' => $ruc,
                'motivo_traslado' => 'SERVICIOS DE TERCEROS',
                'observaciones' => $request->observaciones,
                'estado_guia' => 'EMITIDA',
                'usuario_registro' => Auth::id() ?? 1,
            ]);

            foreach ($request->productos as $item) {
                // 1. Verificar Stock
                $stock = DB::table('inventario')
                    ->where('codigo_almacen', $request->codigo_almacen_origen)
                    ->where('codigo_producto', $item['codigo'])
                    ->sum('stock_actual');
                    
                if ($stock < $item['cantidad']) {
                    throw new \Exception("Stock insuficiente para el producto {$item['codigo']} en el almacén seleccionado. Disponible: {$stock}");
                }

                // 2. Registrar Detalle de Guía
                $detalle = GuiaRemisionTercerosSalidaDetalle::create([
                    'id_guia_salida' => $guia->id_guia_salida,
                    'codigo_producto' => $item['codigo'],
                    'cantidad_enviada' => $item['cantidad'],
                    'cantidad_devuelta' => 0,
                    'cantidad_merma' => 0,
                    'estado_detalle' => 'PENDIENTE'
                ]);

                // 3. Generar SALIDA en Movimientos de Inventario (FIFO)
                $lotes = DB::table('inventario')
                    ->where('codigo_almacen', $request->codigo_almacen_origen)
                    ->where('codigo_producto', $item['codigo'])
                    ->where('stock_actual', '>', 0)
                    ->orderBy('id_inventario', 'asc')
                    ->get();

                $cantidadRestante = $item['cantidad'];
                foreach ($lotes as $lote) {
                    if ($cantidadRestante <= 0) break;

                    $cantidadDescontar = min($lote->stock_actual, $cantidadRestante);
                    
                    // Actualizar Inventario
                    DB::table('inventario')
                        ->where('id_inventario', $lote->id_inventario)
                        ->update([
                            'stock_actual' => $lote->stock_actual - $cantidadDescontar,
                            'fecha_ultimo_movimiento' => now()
                        ]);

                    $totalSalidaLote = $cantidadDescontar * $lote->costo_promedio;
                    
                    // Movimiento Inventario
                    $idMov = DB::table('movimientos_inventario')->insertGetId([
                        'codigo_almacen' => $request->codigo_almacen_origen,
                        'codigo_producto' => $item['codigo'],
                        'codigo_unidad_medida' => $lote->codigo_unidad_medida,
                        'lote' => $lote->lote,
                        'tipo_movimiento' => 'SALIDA',
                        'cantidad' => $cantidadDescontar,
                        'costo_unitario' => $lote->costo_promedio,
                        'total' => $totalSalidaLote,
                        'documento_referencia' => 'GUIA_SALIDA_TERCEROS',
                        'numero_referencia' => $guia->numero_guia,
                        'observaciones' => 'Salida para Tercero: ' . $guia->proveedor_destino,
                        'usuario_movimiento' => Auth::id() ?? 1,
                        'estado' => 1,
                        'fecha_movimiento' => now(),
                        'tiene_kardex' => 1
                    ]);

                    // Kardex
                    DB::table('kardex')->insert([
                        'codigo_almacen' => $request->codigo_almacen_origen,
                        'codigo_producto' => $item['codigo'],
                        'codigo_unidad_medida' => $lote->codigo_unidad_medida,
                        'codigo_referencia_movimiento' => $idMov,
                        'fecha_movimiento' => now(),
                        'tipo_movimiento' => 'SALIDA',
                        'documento' => 'GUIA_SALIDA_TERCEROS',
                        'numero_documento' => $guia->numero_guia,
                        'cantidad_entrada' => 0,
                        'costo_entrada' => 0,
                        'total_entrada' => 0,
                        'cantidad_salida' => $cantidadDescontar,
                        'costo_salida' => $lote->costo_promedio,
                        'total_salida' => $totalSalidaLote,
                        'observaciones' => 'Envío a Tercero: ' . $guia->proveedor_destino,
                        'lote' => $lote->lote,
                        'usuario_registro' => Auth::id() ?? 1
                    ]);

                    $cantidadRestante -= $cantidadDescontar;
                }
                
                // Recalcular el Kardex de este almacén para cuadrar saldos
                $kardexService->recalcular($item['codigo'], $request->codigo_almacen_origen);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Guía de Salida a Tercero registrada correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
