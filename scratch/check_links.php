<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$links = DB::table('producto_molde')->get();
foreach($links as $l) {
    echo "Prod: {$l->codigo_producto_proceso} | Molde: {$l->codigo_molde}\n";
}
