<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$peps = DB::table('kardex')
    ->where('documento', 'RECEPCION_PEP')
    ->get();

foreach ($peps as $pep) {
    // Buscar consumos
    $consumos = DB::table('kardex')
        ->where('documento', 'PRODUCCION')
        ->where('numero_documento', 'LIKE', $pep->numero_documento . '-%')
        ->where('tipo_movimiento', 'SALIDA')
        ->sum('total_salida');
    
    // Si la OP tiene costos adicionales (mano de obra, etc)
    preg_match('/OP-(\d+)-PROC-(\d+)/', $pep->numero_documento, $matches);
    $costosAdicionales = 0;
    if (isset($matches[1])) {
        $costosAdicionales = DB::table('produccion_costos')->where('idop', $matches[1])->sum('costo_total');
    }

    $costoTotal = $consumos + $costosAdicionales;
    
    if ($costoTotal > 0 && $pep->cantidad_entrada > 0) {
        $costoUnitario = round($costoTotal / $pep->cantidad_entrada, 6);
        
        DB::table('kardex')
            ->where('id_kardex', $pep->id_kardex)
            ->update([
                'costo_entrada' => $costoUnitario,
                'total_entrada' => round($costoTotal, 2)
            ]);
            
        echo "Actualizado PEP {$pep->numero_documento} con costo total {$costoTotal} y unitario {$costoUnitario}\n";
    }
}
