<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ops = Illuminate\Support\Facades\DB::table('orden_proceso_resultantes as opr')
    ->join('orden_proceso as op', 'opr.id_proceso', '=', 'op.id')
    ->join('producto as p', 'opr.codigo_producto', '=', 'p.codigo')
    ->where('op.codigo_proceso', 10)
    ->select('p.codigo_tipo_producto')
    ->distinct()
    ->get();
foreach($ops as $op) echo $op->codigo_tipo_producto . "\n";
