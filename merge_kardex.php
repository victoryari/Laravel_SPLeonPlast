<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rows = DB::table('kardex')->where('documento', 'GUIA_SALIDA_TERCEROS')->where('numero_documento', 'T010-00000001')->orderBy('id_kardex', 'asc')->get();
if($rows->count() > 1) {
    $first = $rows->first();
    DB::table('kardex')->where('id_kardex', $first->id_kardex)->update([
        'cantidad_salida' => $rows->sum('cantidad_salida'),
        'total_salida' => $rows->sum('total_salida'),
        'lote' => 'VARIOS LOTES'
    ]);
    DB::table('kardex')->where('documento', 'GUIA_SALIDA_TERCEROS')->where('numero_documento', 'T010-00000001')->where('id_kardex', '>', $first->id_kardex)->delete();
    app(\App\Services\KardexService::class)->recalcular('C100_Z002', 'P5H');
    echo "Merged and recalculated\n";
} else {
    echo "No need to merge\n";
}
