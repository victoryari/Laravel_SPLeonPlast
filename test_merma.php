<?php
use Illuminate\Support\Facades\DB;
use App\Models\Producto;

try {
    $id_orden_produccion = 2;
    $codigo_producto = 'CA07-G001.A';
    $codigo_almacen = 'P1C';
    $cantidadTotalMerma = 12.0;

    $productoOrigen = Producto::findOrFail($codigo_producto);

    $procesoIngreso = DB::table('produccion_ingresos_proceso')
        ->where('idop', $id_orden_produccion)
        ->where('codigo_producto_proceso', $codigo_producto)
        ->first();

    if (!$procesoIngreso) {
        throw new \Exception('No se encontró el proceso de producción.');
    }

    $cantidadPlanificadaPEP = (float) $procesoIngreso->cantidad;
    if ($cantidadPlanificadaPEP <= 0) {
        throw new \Exception('La cantidad planificada del proceso es inválida.');
    }

    $factor = $cantidadTotalMerma / $cantidadPlanificadaPEP;

    $componentes = DB::table('componentes_orden_produccion_global')
        ->where('idop', $id_orden_produccion)
        ->where('id_proceso', $procesoIngreso->id_proceso)
        ->whereIn('codigo_tipo_producto', ['MTP', 'MAT', 'INS'])
        ->where('estado', 1)
        ->get();

    if ($componentes->isEmpty()) {
        throw new \Exception('No hay materias primas configuradas en este proceso para calcular el consumo de la merma.');
    }

    echo "Componentes encontrados: " . $componentes->count() . "\n";

    foreach ($componentes as $comp) {
        $cantidadConsumir = round($comp->cantidad * $factor, 6);
        echo "Validando componente {$comp->codigo_producto} cantidad a consumir: {$cantidadConsumir}\n";

        $inv = DB::table('inventario')
            ->where('codigo_producto', $comp->codigo_producto)
            ->where('codigo_almacen', $codigo_almacen)
            ->first();

        if (!$inv) {
            throw new \Exception("Stock insuficiente: no existe inventario para {$comp->codigo_producto} en almacen {$codigo_almacen}.");
        }
        
        if ($inv->stock_actual < $cantidadConsumir) {
            throw new \Exception("Stock insuficiente de materia prima {$comp->codigo_producto} ({$comp->descripcion_producto}) en el almacén {$codigo_almacen} para cubrir la merma. Se requieren {$cantidadConsumir}, stock actual: {$inv->stock_actual}");
        }
    }

    echo "Validacion exitosa.\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
