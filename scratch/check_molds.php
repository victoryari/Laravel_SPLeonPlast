<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$first = DB::table('producto_molde')->first();
print_r($first);

$order_product = DB::table('orden_produccion_global')->select('codigo_producto_proceso')->first();
print_r($order_product);
