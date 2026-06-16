<?php
use Illuminate\Support\Facades\DB;
use App\Services\KardexService;

try {
    DB::beginTransaction();

    $idop = 2;
    $id_proceso = 5;
    $codigo_producto_pep = 'CA07-G001.A';
    $codigo_almacen = 'PS1C'; // De acuerdo a los logs anteriores
    $nuevo_costo_unitario = 6.411500;
    $nuevo_total = 641.15;

    // 1. Insertar el costo de máquina omitido en produccion_costos
    // Verificamos si ya existe para no duplicar
    $existe = DB::table('produccion_costos')
                ->where('idop', $idop)
                ->where('tipo_costo', 'EQUIPOS')
                ->first();
                
    if (!$existe) {
        DB::table('produccion_costos')->insert([
            'idop' => $idop,
            'tipo_costo' => 'EQUIPOS',
            'descripcion' => 'Horas Máquina Calculadas',
            'cantidad' => 9,
            'costo_unitario' => 15.50,
            'costo_total' => 139.50,
            'moneda' => 'PEN',
            'fecha_costo' => now()->toDateString(),
            'usuario_registro' => null
        ]);
        echo "Costo de máquina insertado.\n";
    }

    // 2. Actualizar movimientos_inventario del PEP
    DB::table('movimientos_inventario')
        ->where('documento_referencia', 'PRODUCCION_PEP')
        ->where('numero_referencia', "OP-{$idop}-PROC-{$id_proceso}")
        ->where('tipo_movimiento', 'INGRESO')
        ->update([
            'costo_unitario' => $nuevo_costo_unitario,
            'total' => $nuevo_total
        ]);
    echo "Movimientos de inventario actualizados.\n";

    // 3. Actualizar Kardex RECEPCION_PEP
    DB::table('kardex')
        ->where('documento', 'RECEPCION_PEP')
        ->where('numero_documento', "OP-{$idop}-PROC-{$id_proceso}")
        ->update([
            'costo_entrada' => $nuevo_costo_unitario,
            'total_entrada' => $nuevo_total
        ]);
    echo "Kardex RECEPCION_PEP actualizado.\n";

    // 4. Recalcular el Kardex para propagar los saldos y promedios hacia adelante
    $kardexService = app(KardexService::class);
    $kardexService->recalcular($codigo_producto_pep, $codigo_almacen);
    echo "Kardex recalculado para {$codigo_producto_pep} en {$codigo_almacen}.\n";

    DB::commit();
    echo "TODO OK.\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
