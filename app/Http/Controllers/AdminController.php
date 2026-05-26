<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        $stats = DB::selectOne("
            SELECT
                COALESCE((SELECT SUM(cantidad) FROM componentes_orden_produccion_global WHERE DATE(fecha_creacion) = CURDATE() AND estado = 1), 0) AS produccion_hoy,
                (SELECT COUNT(*) FROM orden_produccion_global WHERE activo = 1 AND estado IN ('PENDIENTE', 'EN_PROCESO')) AS ordenes_activas,
                (SELECT COUNT(*) FROM inventario WHERE stock_actual < stock_minimo AND stock_minimo > 0) AS alertas_almacen,
                (SELECT COUNT(*) FROM orden_proceso WHERE estado = 1) AS total_procesos,
                (SELECT COUNT(*) FROM orden_proceso WHERE estado = 1 AND estado_avance = 'COMPLETADO') AS completados
        ");

        $eficiencia = $stats->total_procesos > 0
            ? round(($stats->completados / $stats->total_procesos) * 100) . '%'
            : '0%';

        return view('admin.dashboard', [
            'usuario'  => Auth::user(),
            'empresa'  => 'Leon Plast',
            'stats'    => [
                'produccion_hoy'  => number_format($stats->produccion_hoy, 0),
                'ordenes_activas' => $stats->ordenes_activas,
                'alertas_almacen' => $stats->alertas_almacen,
                'eficiencia'      => $eficiencia,
            ],
        ]);
    }
}
