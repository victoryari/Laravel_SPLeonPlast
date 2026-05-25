<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InventorySyncKardex extends Command
{
    protected $signature = 'inventory:sync-kardex
        {--dry-run : Solo mostrar lo que se sincronizará sin insertar}
        {--tipo= : Filtrar por documento_referencia (ej: PRODUCCION, AJUSTE, DEVOLUCION_EDIT)}';

    protected $description = 'Sincroniza movimientos_inventario históricos con la tabla kardex';

    public function handle()
    {
        $query = DB::table('movimientos_inventario')
            ->where('tiene_kardex', false)
            ->orderBy('fecha_movimiento', 'asc');

        if ($this->option('tipo')) {
            $query->where('documento_referencia', $this->option('tipo'));
        }

        $movimientos = $query->get();

        if ($movimientos->isEmpty()) {
            $this->info('No hay movimientos pendientes de sincronizar.');
            return 0;
        }

        $this->info("Se encontraron {$movimientos->count()} movimiento(s) pendiente(s).");

        $kardexCount = 0;
        $productosProcesados = [];

        foreach ($movimientos as $mov) {
            $tipoKardex = match ($mov->documento_referencia) {
                'PRODUCCION'    => $mov->tipo_movimiento === 'SALIDA' ? 'SALIDA' : 'INGRESO',
                'PRODUCCION_PEP' => 'INGRESO',
                'EXTORNO_PROD'  => 'SALIDA',
                'EXTORNO_CONS'  => 'INGRESO',
                default         => $mov->tipo_movimiento,
            };

            $documento = match ($mov->documento_referencia) {
                'PRODUCCION'    => 'PRODUCCION',
                'PRODUCCION_PEP' => 'RECEPCION_PEP',
                'EXTORNO_PROD'  => 'EXTORNO_PROD',
                'EXTORNO_CONS'  => 'EXTORNO_CONS',
                default         => $mov->documento_referencia,
            };

            $key = $mov->codigo_producto . '|' . $mov->codigo_almacen;

            if ($tipoKardex === 'SALIDA') {
                $cantidadEntrada = 0;
                $cantidadSalida = $mov->cantidad;
            } else {
                $cantidadEntrada = $mov->cantidad;
                $cantidadSalida = 0;
            }

            $saldoAnterior = DB::table('kardex')
                ->where('codigo_producto', $mov->codigo_producto)
                ->where('codigo_almacen', $mov->codigo_almacen)
                ->where('fecha_movimiento', '<=', $mov->fecha_movimiento)
                ->orderBy('fecha_movimiento', 'desc')
                ->orderBy('id_kardex', 'desc')
                ->value('cantidad_saldo') ?? 0;

            if (!isset($productosProcesados[$key])) {
                $productosProcesados[$key] = [
                    'producto' => $mov->codigo_producto,
                    'almacen'  => $mov->codigo_almacen,
                    'saldo'    => $saldoAnterior,
                ];
            }

            $productosProcesados[$key]['saldo'] += ($cantidadEntrada - $cantidadSalida);
            $nuevoSaldo = $productosProcesados[$key]['saldo'];

            if ($this->option('dry-run')) {
                $this->line("  [DRY-RUN] {$mov->documento_referencia} | {$mov->codigo_producto}@{$mov->codigo_almacen} | {$tipoKardex} {$mov->cantidad} | saldo {$nuevoSaldo} | mov_id={$mov->id_movimiento}");
                $kardexCount++;
                continue;
            }

            DB::table('kardex')->insert([
                'codigo_almacen'              => $mov->codigo_almacen,
                'codigo_producto'             => $mov->codigo_producto,
                'fecha_movimiento'            => $mov->fecha_movimiento,
                'tipo_movimiento'             => $tipoKardex,
                'documento'                   => $documento,
                'numero_documento'            => $mov->numero_referencia ?? $documento . '-' . $mov->id_movimiento,
                'cantidad_entrada'            => $cantidadEntrada,
                'cantidad_salida'             => $cantidadSalida,
                'cantidad_saldo'              => $nuevoSaldo,
                'codigo_referencia_movimiento' => $mov->id_movimiento,
                'observaciones'               => $mov->observaciones ?? 'Sincronizado del histórico (' . $mov->documento_referencia . ')',
                'usuario_registro'            => $mov->usuario_movimiento,
            ]);

            DB::table('movimientos_inventario')
                ->where('id_movimiento', $mov->id_movimiento)
                ->update(['tiene_kardex' => true]);

            $kardexCount++;
        }

        if ($this->option('dry-run')) {
            $this->info("Dry-run completado. {$kardexCount} registros listos para sincronizar.");
        } else {
            $this->info("Sincronización completada. {$kardexCount} registro(s) insertado(s) en kardex.");
        }

        return 0;
    }
}
