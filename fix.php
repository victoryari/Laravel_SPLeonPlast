<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Fix historical records by finding the original PRODUCCION reference
$extornos_movs = DB::table('movimientos_inventario')
    ->where('documento_referencia', 'EXTORNO_CONS')
    ->get();

foreach($extornos_movs as $ext) {
    // To find the original movement, we might not have a direct link from EXTORNO_CONS to the exact PRODUCCION.
    // Wait, let's look at the logic. The EXTORNO_CONS has 'cantidad' and 'codigo_producto'.
    // If we want to be safe, we can just find any PRODUCCION movement for the SAME component ID?
    // Wait, in extornos_movs, what is the 'numero_referencia'? It is 'OP-1-PROC-1-COMP-13'.
    // Can we extract the component_id?
    if (preg_match('/COMP-(\d+)$/', $ext->numero_referencia, $matches)) {
        $componente_id = $matches[1];
        
        // Find the PRODUCCION movement for this component_id
        $original_movs = DB::table('movimientos_inventario')
            ->where('componente_origen_id', $componente_id)
            ->where('documento_referencia', 'PRODUCCION')
            ->get();
            
        foreach($original_movs as $omov) {
            DB::table('kardex')
                ->where('numero_documento', $omov->numero_referencia)
                ->where('tipo_movimiento', 'SALIDA')
                ->where('documento', 'PRODUCCION')
                ->where('observaciones', 'NOT LIKE', '%[ANULADO]%')
                // match the specific product and amount just in case they were grouped
                ->where('codigo_producto', $omov->codigo_producto)
                ->where('cantidad_salida', $omov->cantidad)
                ->update(['observaciones' => DB::raw("CONCAT(COALESCE(observaciones, ''), ' [ANULADO]')")]);
        }
    }
}

// For whole process annulments (EXTORNO_PROD):
// They use the same prefix.
$extornos_prod = DB::table('kardex')->where('documento', 'EXTORNO_PROD')->pluck('numero_documento');
foreach($extornos_prod as $ext) {
    DB::table('kardex')
        ->where('numero_documento', 'LIKE', $ext . '%')
        ->where('documento', 'PRODUCCION')
        ->where('observaciones', 'NOT LIKE', '%[ANULADO]%')
        ->update(['observaciones' => DB::raw("CONCAT(COALESCE(observaciones, ''), ' [ANULADO]')")]);
}

echo "Fixed.\n";
