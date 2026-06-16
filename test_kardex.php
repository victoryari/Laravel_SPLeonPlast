<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ks = app(\App\Services\KardexService::class);
$costos = $ks->calcularCostos('S2504', 'ALM03', 0, 0, 18000, 0);
echo json_encode($costos);
