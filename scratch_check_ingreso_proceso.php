<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$ingreso = DB::table('produccion_ingresos_proceso')->first();
print_r($ingreso);
