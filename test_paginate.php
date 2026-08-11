<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$query = DB::table('inventario')
    ->join('producto', 'inventario.codigo_producto', '=', 'producto.codigo')
    ->join('almacen', 'inventario.codigo_almacen', '=', 'almacen.codigo_almacen')
    ->select(
        'inventario.codigo_producto', 
        'inventario.codigo_almacen',
        'producto.descripcion as producto',
        'almacen.descripcion as almacen',
        DB::raw('SUM(inventario.stock_actual) as stock_actual'),
        DB::raw('MAX(inventario.stock_minimo) as stock_minimo'),
        DB::raw('MAX(inventario.stock_maximo) as stock_maximo'),
        DB::raw('MAX(inventario.fecha_ultimo_movimiento) as fecha_ultimo_movimiento')
    )
    ->groupBy(
        'inventario.codigo_producto',
        'inventario.codigo_almacen',
        'producto.descripcion',
        'almacen.descripcion'
    )
    ->havingRaw('SUM(inventario.stock_actual) > 0')
    ->orderBy('producto.descripcion');

$stocks = $query->paginate(10);
print_r($stocks->items());
