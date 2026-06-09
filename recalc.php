<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$ks = app(App\Services\KardexService::class);
foreach(Illuminate\Support\Facades\DB::table('kardex')->select('codigo_producto', 'codigo_almacen')->distinct()->get() as $g) {
    echo "Recalculating {$g->codigo_producto} en {$g->codigo_almacen}\n";
    $ks->recalcular($g->codigo_producto, $g->codigo_almacen);
}
echo "Done\n";
