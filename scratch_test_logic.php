<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$idop = 1;

// 1. Find the inventory records for lotes produced in this OP that have stock in the assembly warehouse (assume ALM-ENS, we need to know the code).
// Let's get the code of the assembly warehouse
$almacen_ensamblado = DB::table('proceso_produccion')->where('codigo', '10')->value('codigo_almacen');
echo "Almacen ensamblado: $almacen_ensamblado\n";

$lotes_op = DB::table('produccion_ingresos_proceso')
    ->where('idop', $idop)
    ->pluck('lote_produccion')
    ->toArray();

$cascaras_disponibles = DB::table('inventario as i')
    ->join('produccion_ingresos_proceso as p', 'i.lote', '=', 'p.lote_produccion')
    ->where('i.codigo_almacen', $almacen_ensamblado)
    ->where('i.stock_actual', '>', 0)
    ->whereIn('i.lote', $lotes_op)
    ->select('i.codigo_producto', 'p.codigo_molde', 'p.descripcion_molde')
    ->groupBy('i.codigo_producto', 'p.codigo_molde', 'p.descripcion_molde')
    ->get();

echo "Cascaras en almacen ensamblado:\n";
foreach ($cascaras_disponibles as $c) {
    echo "  - Prod: {$c->codigo_producto}, Molde: {$c->codigo_molde} - {$c->descripcion_molde}\n";
}

// 2. Filter formulas based on these cascaras.
// The formulas have materials. We only want formulas whose 'Cáscara' material matches one of the $cascaras_disponibles (both codigo_producto and codigo_molde).
$formulas = DB::table('formula_produccion as f')
    ->where('f.estado', 1)
    ->where('f.codigo', 'LIKE', 'EN%')
    ->get();

$filtered_formulas = [];
foreach ($formulas as $f) {
    $materials = DB::table('composicion_formula')
        ->where('codigo_formula', $f->codigo)
        ->get();
    
    $hasValidCascara = false;
    foreach ($materials as $m) {
        // Is this material a cascara? Let's assume cascara starts with CA
        if (str_starts_with($m->codigo_producto, 'CA')) {
            foreach ($cascaras_disponibles as $c) {
                if ($c->codigo_producto === $m->codigo_producto && $c->codigo_molde === $m->codigo_molde) {
                    $hasValidCascara = true;
                    break;
                }
            }
        }
    }
    
    if ($hasValidCascara) {
        $filtered_formulas[] = $f;
    }
}

echo "\nFormulas filtradas:\n";
foreach ($filtered_formulas as $f) {
    echo "  - {$f->codigo} - {$f->descripcion}\n";
}

