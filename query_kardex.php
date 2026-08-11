<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rows = DB::table('kardex')->where('codigo_producto', 'C100_Z002')->orderBy('fecha_movimiento', 'asc')->orderBy('id_kardex', 'asc')->get(['id_kardex', 'codigo_producto', 'tipo_movimiento', 'fecha_movimiento', 'cantidad_entrada', 'cantidad_salida', 'cantidad_saldo', 'costo_promedio', 'codigo_almacen']);
foreach($rows as $r) {
    echo $r->id_kardex . ' | ' . $r->codigo_producto . ' | ' . $r->codigo_almacen . ' | ' . $r->tipo_movimiento . ' | ' . $r->fecha_movimiento . ' | in: ' . $r->cantidad_entrada . ' | out: ' . $r->cantidad_salida . ' | saldo: ' . $r->cantidad_saldo . ' | prom: ' . $r->costo_promedio . "\n";
}
