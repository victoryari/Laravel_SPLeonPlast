<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$saldos = DB::table("kardex")
    ->select("codigo_almacen", "codigo_producto", "lote", DB::raw("SUM(cantidad_entrada - cantidad_salida) as stock_real"))
    ->whereNotNull("lote")
    ->where("lote", "!=", "")
    ->groupBy("codigo_almacen", "codigo_producto", "lote")
    ->get();

foreach($saldos as $s) {
    DB::table("inventario")
      ->where("codigo_almacen", $s->codigo_almacen)
      ->where("codigo_producto", $s->codigo_producto)
      ->where("lote", $s->lote)
      ->update(["stock_actual" => $s->stock_real]);
}
echo "OK\n";

