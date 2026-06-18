<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class KardexService
{
    public function calcularCostos(
        string $codigoProducto,
        string $codigoAlmacen,
        float $cantidadEntrada,
        float $costoUnitarioEntrada,
        float $cantidadSalida,
        float $saldoActual
    ): array {
        $ultimo = DB::table('kardex')
            ->where('codigo_producto', $codigoProducto)
            ->where('codigo_almacen', $codigoAlmacen)
            ->orderBy('fecha_movimiento', 'desc')
            ->orderBy('id_kardex', 'desc')
            ->first();

        $saldoAnterior = $ultimo?->cantidad_saldo ?? 0;
        $totalSaldoAnterior = $ultimo?->total_saldo ?? 0;
        $costoPromedioAnterior = $ultimo?->costo_promedio ?? 0;

        if ($cantidadEntrada > 0) {
            $totalEntrada = $cantidadEntrada * $costoUnitarioEntrada;
            $nuevaCantidadSaldo = $saldoAnterior + $cantidadEntrada;
            $nuevoTotalSaldo = $totalSaldoAnterior + $totalEntrada;
            $nuevoCostoPromedio = $nuevaCantidadSaldo > 0
                ? round($nuevoTotalSaldo / $nuevaCantidadSaldo, 9)
                : $costoUnitarioEntrada;

            return [
                'costo_entrada'  => $costoUnitarioEntrada,
                'total_entrada'  => $totalEntrada,
                'costo_salida'   => 0,
                'total_salida'   => 0,
                'cantidad_saldo' => $nuevaCantidadSaldo,
                'costo_promedio' => $nuevoCostoPromedio,
                'total_saldo'    => round($nuevaCantidadSaldo * $nuevoCostoPromedio, 9),
            ];
        }

        $costoSalida = max(0, $costoPromedioAnterior);
        $totalSalida = $cantidadSalida * $costoSalida;
        $nuevaCantidadSaldo = max(0, $saldoAnterior - $cantidadSalida);
        $nuevoTotalSaldo = $nuevaCantidadSaldo > 0
            ? round($nuevaCantidadSaldo * $costoSalida, 9)
            : 0;

        return [
            'costo_entrada'  => 0,
            'total_entrada'  => 0,
            'costo_salida'   => $costoSalida,
            'total_salida'   => $totalSalida,
            'cantidad_saldo' => $nuevaCantidadSaldo,
            'costo_promedio' => $nuevaCantidadSaldo > 0 ? $costoSalida : 0,
            'total_saldo'    => $nuevoTotalSaldo,
        ];
    }

    public function recalcular(string $codigoProducto, string $codigoAlmacen): void
    {
        $movimientos = DB::table('kardex')
            ->where('codigo_producto', $codigoProducto)
            ->where('codigo_almacen', $codigoAlmacen)
            ->orderBy('fecha_movimiento', 'asc')
            ->orderBy('id_kardex', 'asc')
            ->get();

        $cantidadAcumulada = 0;
        $totalValorAcumulado = 0;
        $costoPromedio = 0;

        foreach ($movimientos as $mov) {
            if ($mov->tipo_movimiento === 'SALIDA' || ($mov->tipo_movimiento === 'EXTORNO' && $mov->cantidad_salida > 0)) {
                $costoSalidaEfectivo = ($costoPromedio > 0) ? $costoPromedio : ($mov->costo_salida ?? 0);
                $totalSalida = $mov->cantidad_salida * $costoSalidaEfectivo;
                $cantidadAcumulada -= $mov->cantidad_salida;
                $totalValorAcumulado = max(0, $totalValorAcumulado - $totalSalida);
                DB::table('kardex')
                    ->where('id_kardex', $mov->id_kardex)
                    ->update([
                        'costo_salida' => $costoSalidaEfectivo,
                        'total_salida' => round($totalSalida, 2),
                        'costo_promedio' => $costoPromedio,
                        'cantidad_saldo' => max(0, $cantidadAcumulada),
                        'total_saldo' => max(0, round($cantidadAcumulada * $costoPromedio, 9)),
                    ]);

                if ($mov->codigo_referencia_movimiento) {
                    DB::table('movimientos_inventario')
                        ->where('id_movimiento', $mov->codigo_referencia_movimiento)
                        ->update([
                            'costo_unitario' => $costoSalidaEfectivo,
                            'total' => round($totalSalida, 2)
                        ]);
                }

                // Propagar costo a la entrada de la transferencia si aplica
                if ($mov->documento === 'TRANSFERENCIA' || $mov->documento === 'ANULACION_TRANSFERENCIA') {
                    $ingresoTransf = DB::table('kardex')
                        ->where('numero_documento', $mov->numero_documento)
                        ->where('codigo_producto', $codigoProducto)
                        ->where('tipo_movimiento', 'INGRESO')
                        ->first();
                        
                    if ($ingresoTransf && abs((float)$ingresoTransf->costo_entrada - (float)$costoSalidaEfectivo) > 0.000001) {
                        DB::table('kardex')
                            ->where('id_kardex', $ingresoTransf->id_kardex)
                            ->update(['costo_entrada' => $costoSalidaEfectivo]);
                            
                        // Llamada recursiva para propagar el recálculo en el almacén destino
                        $this->recalcular($codigoProducto, $ingresoTransf->codigo_almacen);
                    }
                }
                
                continue;
            }

            if ($mov->tipo_movimiento === 'TRASPASO') {
                $cantidadAcumulada += ($mov->cantidad_entrada - $mov->cantidad_salida);
                $totalValorAcumulado = max(0, $cantidadAcumulada * $costoPromedio);
                DB::table('kardex')
                    ->where('id_kardex', $mov->id_kardex)
                    ->update([
                        'costo_promedio' => $costoPromedio,
                        'cantidad_saldo' => max(0, $cantidadAcumulada),
                        'total_saldo' => round($totalValorAcumulado, 9),
                    ]);
                continue;
            }

            $costoEntradaEfectivo = ($mov->costo_entrada > 0) ? $mov->costo_entrada : $costoPromedio;
            $totalEntrada = $mov->cantidad_entrada * $costoEntradaEfectivo;
            $cantidadAcumulada += $mov->cantidad_entrada;
            $totalValorAcumulado += $totalEntrada;
            $costoPromedio = $cantidadAcumulada > 0
                ? round($totalValorAcumulado / $cantidadAcumulada, 9)
                : 0;

            DB::table('kardex')
                ->where('id_kardex', $mov->id_kardex)
                ->update([
                    'total_entrada'  => round($totalEntrada, 2),
                    'costo_promedio' => $costoPromedio,
                    'cantidad_saldo' => max(0, $cantidadAcumulada),
                    'total_saldo'    => round(max(0, $cantidadAcumulada) * $costoPromedio, 9),
                ]);

            if ($mov->codigo_referencia_movimiento) {
                DB::table('movimientos_inventario')
                    ->where('id_movimiento', $mov->codigo_referencia_movimiento)
                    ->update([
                        'costo_unitario' => $costoEntradaEfectivo,
                        'total' => round($totalEntrada, 2)
                    ]);
            }
        }

        $ultimo = DB::table('kardex')
            ->where('codigo_producto', $codigoProducto)
            ->where('codigo_almacen', $codigoAlmacen)
            ->orderBy('fecha_movimiento', 'desc')
            ->orderBy('id_kardex', 'desc')
            ->first(['cantidad_saldo', 'costo_promedio']);

        if ($ultimo) {
            DB::table('inventario')
                ->where('codigo_producto', $codigoProducto)
                ->where('codigo_almacen', $codigoAlmacen)
                ->update([
                    'stock_actual'   => $ultimo->cantidad_saldo,
                    'costo_promedio' => $ultimo->costo_promedio,
                ]);
        }
    }
}