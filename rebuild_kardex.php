<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$productos_almacenes = DB::table('kardex')
    ->select('codigo_producto', 'codigo_almacen')
    ->distinct()
    ->get();

foreach ($productos_almacenes as $pa) {
    $registros = DB::table('kardex')
        ->where('codigo_producto', $pa->codigo_producto)
        ->where('codigo_almacen', $pa->codigo_almacen)
        ->orderBy('fecha_movimiento', 'asc')
        ->orderBy('id_kardex', 'asc')
        ->get();

    $saldo = 0;
    foreach ($registros as $reg) {
        $esAnulado = str_contains($reg->observaciones ?? '', '[ANULADO]') || str_contains($reg->observaciones ?? '', '[EXTORNADO]');
        $esExtorno = ($reg->tipo_movimiento === 'EXTORNO');
        
        if (!$esAnulado && !$esExtorno) {
            $saldo += (float)$reg->cantidad_entrada - (float)$reg->cantidad_salida;
        }
        
        DB::table('kardex')
            ->where('id_kardex', $reg->id_kardex)
            ->update(['cantidad_saldo' => max(0, $saldo)]);
    }
}

echo "Saldos reconstruidos.\n";
