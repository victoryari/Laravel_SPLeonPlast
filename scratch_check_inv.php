<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$inv = DB::table('inventario')->where('codigo_producto', 'LIKE', 'CA%')->get();
foreach($inv as $i) {
    echo $i->codigo_producto . ' - ' . $i->lote . ' - ' . $i->stock_actual . "\n";
}
