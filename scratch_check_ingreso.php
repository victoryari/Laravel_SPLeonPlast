<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$ingreso = DB::table('ingresos_produccion')->first();
print_r($ingreso);
