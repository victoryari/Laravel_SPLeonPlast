<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$idop = 1;
$id = 1;

$costo_total = 423.01; // Costo Total de Produccin from screenshot

$pep_movimientos = DB::table('movimientos_inventario')
    ->whereIn('documento_referencia', ['PRODUCCION_PEP', 'PRODUCCION_PEP_GLOBAL'])
    ->where(function($q) use ($idop, $id) {
        $q->where('numero_referencia', 'OP-'.$idop.'-PROC-'.$id)
          ->orWhere('numero_referencia', 'OP-'.$idop.'-PROC-'.$id.'-G');
    })
    ->where('tipo_movimiento', 'INGRESO')
    ->where('estado', 1)
    ->get();

$cantidad_producida = $pep_movimientos->sum('cantidad');

if ($cantidad_producida > 0) {
    $costo_unitario_real = round($costo_total / $cantidad_producida, 9);
    $productos_almacenes_afectados = [];

    foreach ($pep_movimientos as $mov) {
        $nuevo_total = round($mov->cantidad * $costo_unitario_real, 2);

        DB::table('movimientos_inventario')
            ->where('id_movimiento', $mov->id_movimiento)
            ->update([
                'costo_unitario' => $costo_unitario_real,
                'total' => $nuevo_total
            ]);

        DB::table('kardex')
            ->where('codigo_referencia_movimiento', $mov->id_movimiento)
            ->whereIn('documento', ['RECEPCION_PEP', 'RECEPCION_PEP_GLOBAL'])
            ->update([
                'costo_entrada' => $costo_unitario_real,
                'total_entrada' => $nuevo_total
            ]);

        $clave_recalculo = $mov->codigo_producto . '|' . $mov->codigo_almacen;
        $productos_almacenes_afectados[$clave_recalculo] = [
            'producto' => $mov->codigo_producto,
            'almacen' => $mov->codigo_almacen
        ];
    }

    $kardexService = app(\App\Services\KardexService::class);
    foreach ($productos_almacenes_afectados as $afectado) {
        $kardexService->recalcular($afectado['producto'], $afectado['almacen']);
    }
    echo "Costos actualizados correctamente. Costo total: $costo_total, Cantidad: $cantidad_producida \n";
} else {
    echo "No hay PEP para actualizar \n";
}
