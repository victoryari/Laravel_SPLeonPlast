<?php

namespace App\Console\Commands;

use App\Services\KardexService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class KardexRecalcular extends Command
{
    protected $signature = 'kardex:recalcular {--producto=} {--almacen=}';
    protected $description = 'Recalcula costos promedios y saldos valorizados del kardex';

    public function handle(KardexService $kardexService)
    {
        $query = DB::table('kardex')
            ->select('codigo_producto', 'codigo_almacen')
            ->groupBy('codigo_producto', 'codigo_almacen');

        if ($producto = $this->option('producto')) {
            $query->where('codigo_producto', $producto);
        }

        if ($almacen = $this->option('almacen')) {
            $query->where('codigo_almacen', $almacen);
        }

        $items = $query->get();
        $count = 0;

        $this->info("Recalculando {$items->count()} combinaciones producto/almacen...");

        foreach ($items as $item) {
            $kardexService->recalcular($item->codigo_producto, $item->codigo_almacen);
            $count++;
            $this->line("  [{$count}] {$item->codigo_producto} / {$item->codigo_almacen}");
        }

        $this->info("Completado. {$count} combinaciones recalculadas.");
    }
}
