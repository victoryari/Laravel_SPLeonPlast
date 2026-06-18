<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\GuiaRemisionTercerosSalidaDetalle;

class TercerosLiquidacionController extends Controller
{
    public function index()
    {
        $detalles = DB::table('guia_remision_terceros_salida_detalle as d')
            ->join('guia_remision_terceros_salida as c', 'd.id_guia_salida', '=', 'c.id_guia_salida')
            ->select(
                'd.id_detalle_salida',
                'c.numero_guia',
                'c.fecha_emision',
                'c.proveedor_destino',
                'd.codigo_producto',
                'd.cantidad_enviada',
                'd.cantidad_devuelta',
                'd.cantidad_merma',
                'd.estado_detalle'
            )
            ->orderBy('c.fecha_emision', 'desc')
            ->paginate(50);
            
        return view('terceros.liquidacion.index', compact('detalles'));
    }

    public function cerrarConMerma(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $detalle = GuiaRemisionTercerosSalidaDetalle::findOrFail($id);
            
            if ($detalle->estado_detalle == 'CERRADO_CON_MERMA' || $detalle->estado_detalle == 'COMPLETADO') {
                throw new \Exception('El detalle ya se encuentra cerrado o completado.');
            }
            
            $saldoPendiente = $detalle->cantidad_enviada - $detalle->cantidad_devuelta - $detalle->cantidad_merma;
            
            if ($saldoPendiente <= 0) {
                throw new \Exception('No hay saldo pendiente para mandar a merma.');
            }
            
            // Enviar saldo a merma
            $detalle->update([
                'cantidad_merma' => $detalle->cantidad_merma + $saldoPendiente,
                'estado_detalle' => 'CERRADO_CON_MERMA'
            ]);
            
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Se ha enviado el saldo residual a merma y se cerró la guía exitosamente.']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
