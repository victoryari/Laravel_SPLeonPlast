<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');

    $tablas = [
        'compras',
        'detalle_compras',
        'guia_remision_compras',
        'detalle_guia_compras',
        'inventario',
        'kardex',
        'movimientos_inventario',
        'orden_produccion_global',
        'componentes_orden_produccion_global',
        'produccion_ingresos_proceso',
        'orden_proceso',
        'requerimiento_materiales',
        'detalle_requerimiento_materiales',
        'despacho_requerimientos',
    ];

    foreach ($tablas as $tabla) {
        if (Schema::hasTable($tabla)) {
            DB::table($tabla)->truncate();
            echo "Tabla $tabla truncada exitosamente.\n";
        }
    }

    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    echo "¡Limpieza completada con éxito!\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
}
