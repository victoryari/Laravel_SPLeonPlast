<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$columns = DB::select("SHOW COLUMNS FROM orden_proceso");
print_r($columns);

$columns2 = DB::select("SHOW COLUMNS FROM produccion_ingresos_proceso");
print_r($columns2);

$proc = DB::table('orden_proceso')->where('id', 1)->first();
print_r($proc);

