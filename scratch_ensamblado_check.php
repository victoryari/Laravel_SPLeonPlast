<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$idop = 1; // Testing with OP 1
echo "OP: $idop\n";

$ingresos = DB::table('produccion_ingresos_proceso')
    ->where('idop', $idop)
    ->get();
echo "Ingresos de produccion (cascaras) para la OP:\n";
foreach ($ingresos as $ing) {
    echo " - Lote: {$ing->lote_produccion}, Prod: {$ing->codigo_producto_proceso}\n";
}

$formulas = DB::table('formula_produccion')
    ->where('codigo', 'LIKE', 'EN%')
    ->get();
echo "\nAlgunas formulas de Ensamblado:\n";
foreach ($formulas->take(5) as $f) {
    echo " - Formula: {$f->codigo} - {$f->descripcion}\n";
    $mats = DB::table('composicion_formula')->where('codigo_formula', $f->codigo)->get();
    foreach ($mats as $m) {
        echo "   * Mat: {$m->codigo_producto} (Molde: {$m->codigo_molde})\n";
    }
}
