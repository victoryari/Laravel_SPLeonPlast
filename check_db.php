<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$formula = DB::table('formula_componente')->where('codigo_formula', 'MZ07-B009.A')->get();
echo json_encode($formula, JSON_PRETTY_PRINT);
