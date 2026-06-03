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

        $costoSalida = $costoPromedioAnterior;
        $totalSalida = $cantidadSalida * $costoSalida;
        $nuevaCantidadSaldo = $saldoAnterior - $cantidadSalida;
        $nuevoTotalSaldo = $totalSaldoAnterior - $totalSalida;

        return [
            'costo_entrada'  => 0,
            'total_entrada'  => 0,
            'costo_salida'   => $costoSalida,
            'total_salida'   => $totalSalida,
            'cantidad_saldo' => max(0, $nuevaCantidadSaldo),
            'costo_promedio' => $costoPromedioAnterior,
            'total_saldo'    => max(0, round($nuevaCantidadSaldo * $costoPromedioAnterior, 9)),
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
            if ($mov->tipo_movimiento === 'SALIDA') {
                $totalSalida = $mov->cantidad_salida * $costoPromedio;
                DB::table('kardex')
                    ->where('id_kardex', $mov->id_kardex)
                    ->update([
                        'costo_salida' => $costoPromedio,
                        'total_salida' => round($totalSalida, 2),
                        'costo_promedio' => $costoPromedio,
                        'cantidad_saldo' => $cantidadAcumulada - $mov->cantidad_salida,
                        'total_saldo' => round(($cantidadAcumulada - $mov->cantidad_salida) * $costoPromedio, 9),
                    ]);
                $cantidadAcumulada -= $mov->cantidad_salida;
                $totalValorAcumulado = $cantidadAcumulada * $costoPromedio;
                continue;
            }

            if ($mov->tipo_movimiento === 'TRASPASO') {
                $cantidadAcumulada += ($mov->cantidad_entrada - $mov->cantidad_salida);
                $totalValorAcumulado = $cantidadAcumulada * $costoPromedio;
                DB::table('kardex')
                    ->where('id_kardex', $mov->id_kardex)
                    ->update([
                        'costo_promedio' => $costoPromedio,
                        'cantidad_saldo' => $cantidadAcumulada,
                        'total_saldo' => round($totalValorAcumulado, 9),
                    ]);
                continue;
            }

            $totalEntrada = $mov->cantidad_entrada * $mov->costo_entrada;
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
                    'cantidad_saldo' => $cantidadAcumulada,
                    'total_saldo'    => round($cantidadAcumulada * $costoPromedio, 9),
                ]);
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