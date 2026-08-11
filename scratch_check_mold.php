<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$idop = 1;

$ingresos = DB::table('produccion_ingresos_proceso')
    ->where('idop', $idop)
    ->get();

foreach ($ingresos as $ing) {
    echo "Lote: {$ing->lote_produccion}, Proceso ID: {$ing->id_proceso}\n";
    $proceso = DB::table('orden_proceso')->where('id', $ing->id_proceso)->first();
    echo "  - Proceso Molde: " . ($proceso->codigo_molde ?? 'N/A') . "\n";
}
