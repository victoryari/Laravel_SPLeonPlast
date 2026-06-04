<?php
use App\Models\GuiaRemisionCompra;
use App\Models\Compra;
use App\Services\KardexService;
use Illuminate\Support\Facades\DB;

$guias = GuiaRemisionCompra::whereNotNull('id_compra')->get();
$kardexService = app(KardexService::class);

foreach($guias as $guia) {
    $compra = Compra::with('detalles')->find($guia->id_compra);
    if($compra) {
        foreach($compra->detalles as $det) {
            DB::table('kardex')
                ->where('numero_documento', $guia->numero_guia)
                ->where('tipo_movimiento', 'INGRESO')
                ->where('codigo_producto', $det->codigo_producto)
                ->update(['costo_entrada' => $det->precio_unitario]);
                
            $almacenes = DB::table('kardex')
                ->where('numero_documento', $guia->numero_guia)
                ->where('codigo_producto', $det->codigo_producto)
                ->pluck('codigo_almacen')
                ->unique();
                
            foreach($almacenes as $alm) {
                $kardexService->recalcular($det->codigo_producto, $alm);
            }
        }
    }
}
echo "Costos actualizados.\n";
