<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "--- MEZCLADO ---\n";
print_r(DB::table('formula_produccion')->where('descripcion', 'LIKE', '%MEZCLADO%')->get());

echo "\n--- INYECTADO ---\n";
print_r(DB::table('formula_produccion')->where('descripcion', 'LIKE', '%INYECTADO%')->get());

echo "\n--- TODOS ---\n";
print_r(DB::table('formula_produccion')->limit(20)->get());
