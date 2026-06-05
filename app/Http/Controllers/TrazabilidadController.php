<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrazabilidadController extends Controller
{
    public function index(Request $request)
    {
        $lote = $request->input('lote');
        $resultados = null;

        if ($lote) {
            $resultados = $this->rastrearLote($lote);
        }

        return view('reportes.trazabilidad.index', compact('lote', 'resultados'));
    }

    private function rastrearLote($lote)
    {
        $resultados = [
            'lote_buscado' => $lote,
            'origen' => [],      // De donde vino (Compras, OP anterior)
            'consumos' => [],    // Donde se consumió (OPs)
            'destinos' => [],    // Qué productos salieron de esas OPs
            'ajustes_salida' => [] // Salidas por ajuste manual
        ];

        // 1. ORIGEN: Verificar si es materia prima (Compra) o producto fabricado (PEP/Terminado)
        // Buscar ingreso inicial
        $ingresoCompra = DB::table('movimientos_inventario')
            ->where('lote', $lote)
            ->where('tipo_movimiento', 'INGRESO')
            ->where('documento_referencia', '!=', 'PRODUCCION')
            ->where('documento_referencia', '!=', 'TRANSFERENCIA')
            ->first();

        if ($ingresoCompra) {
            // Es Materia prima
            $compra = DB::table('compras')
                ->where(DB::raw("CONCAT(serie_documento, '-', numero_documento)"), $ingresoCompra->numero_referencia)
                ->first();
            
            $proveedor = 'Desconocido';
            $factura_asociada = null;

            if ($compra) {
                $proveedor = $compra->proveedor;
            } else {
                $guia = DB::table('guia_remision_compras')->where('numero_guia', $ingresoCompra->numero_referencia)->first();
                if ($guia) {
                    $proveedor = $guia->proveedor;
                    // Buscar factura vinculada a la guia
                    $compraAsociada = DB::table('compras')->where('id_guia_remision_compra', $guia->id_guia)->first();
                    if ($compraAsociada) {
                        $factura_asociada = $compraAsociada->tipo_documento . ' ' . $compraAsociada->serie_documento . '-' . $compraAsociada->numero_documento;
                    }
                }
            }

            $resultados['origen'][] = [
                'tipo' => $ingresoCompra->documento_referencia,
                'fecha' => $ingresoCompra->fecha_movimiento,
                'proveedor' => $proveedor,
                'documento' => $ingresoCompra->numero_referencia,
                'factura_asociada' => $factura_asociada,
                'producto' => $ingresoCompra->codigo_producto,
                'cantidad' => $ingresoCompra->cantidad
            ];
        } else {
            // Verificar si es Producto en Proceso o Terminado fabricado en planta
            $ingresoProduccion = DB::table('produccion_ingresos_proceso')
                ->where('lote_produccion', $lote)
                ->first();

            if ($ingresoProduccion) {
                // Trazabilidad hacia Atrás (Qué insumos conformaron este lote)
                $resultados['origen'][] = [
                    'tipo' => 'PRODUCCION',
                    'fecha' => $ingresoProduccion->fecha_ingreso,
                    'op' => $ingresoProduccion->idop,
                    'producto' => $ingresoProduccion->descripcion_producto_proceso,
                    'cantidad' => $ingresoProduccion->cantidad
                ];

                $insumos_usados = DB::table('movimientos_inventario')
                    ->where('idop', $ingresoProduccion->idop)
                    ->where('tipo_movimiento', 'SALIDA')
                    ->where('documento_referencia', 'PRODUCCION')
                    ->get();
                
                foreach($insumos_usados as $ins) {
                    $origenInsumo = DB::table('movimientos_inventario')
                        ->where('lote', $ins->lote)
                        ->where('tipo_movimiento', 'INGRESO')
                        ->first();
                    
                    $resultados['origen_insumos'][] = [
                        'producto' => $ins->codigo_producto,
                        'lote' => $ins->lote,
                        'cantidad_consumida' => $ins->cantidad,
                        'origen' => $origenInsumo ? $origenInsumo->documento_referencia : 'N/A'
                    ];
                }
            }
        }

        // 2. HACIA ADELANTE: Verificar en qué Órdenes de Producción se consumió este lote
        $consumos = DB::table('movimientos_inventario')
            ->where('lote', $lote)
            ->where('tipo_movimiento', 'SALIDA')
            ->where('documento_referencia', 'PRODUCCION')
            ->get();

        foreach ($consumos as $consumo) {
            $resultados['consumos'][] = [
                'op' => $consumo->idop,
                'fecha' => $consumo->fecha_movimiento,
                'cantidad' => $consumo->cantidad
            ];

            // 3. PRODUCTOS FINALES: Qué productos se generaron de esa OP
            $productosGenerados = DB::table('produccion_ingresos_proceso')
                ->where('idop', $consumo->idop)
                ->get();

            foreach ($productosGenerados as $prod) {
                $resultados['destinos'][] = [
                    'op' => $prod->idop,
                    'producto' => $prod->descripcion_producto_proceso,
                    'lote_generado' => $prod->lote_produccion,
                    'cantidad' => $prod->cantidad,
                    'fecha' => $prod->fecha_ingreso
                ];
            }
        }

        // 4. AJUSTES MANUALES (Salidas)
        $ajustesSalida = DB::table('movimientos_inventario')
            ->where('lote', $lote)
            ->where('tipo_movimiento', 'SALIDA')
            ->where('documento_referencia', '!=', 'PRODUCCION')
            ->where('documento_referencia', '!=', 'TRANSFERENCIA')
            ->where('documento_referencia', '!=', 'REQUERIMIENTO')
            ->get();

        foreach ($ajustesSalida as $ajuste) {
            $resultados['ajustes_salida'][] = [
                'documento' => $ajuste->documento_referencia,
                'numero' => $ajuste->numero_referencia,
                'fecha' => $ajuste->fecha_movimiento,
                'cantidad' => $ajuste->cantidad,
                'observaciones' => $ajuste->observaciones
            ];
        }

        return $resultados;
    }
}
