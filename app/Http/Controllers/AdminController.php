<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        $hoy = now()->toDateString();

        $produccionHoy = DB::table('componentes_orden_produccion_global')
            ->whereDate('fecha_creacion', $hoy)
            ->where('estado', 1)
            ->sum('cantidad');

        $ordenesActivas = DB::table('orden_produccion_global')
            ->where('activo', 1)
            ->whereIn('estado', ['PENDIENTE', 'EN_PROCESO'])
            ->count();

        $alertasAlmacen = DB::table('inventario')
            ->whereColumn('stock_actual', '<', 'stock_minimo')
            ->where('stock_minimo', '>', 0)
            ->count();

        $totalProcesos = DB::table('orden_proceso')
            ->where('estado', 1)
            ->count();

        $completados = DB::table('orden_proceso')
            ->where('estado', 1)
            ->where('estado_avance', 'COMPLETADO')
            ->count();

        $eficiencia = $totalProcesos > 0 ? round(($completados / $totalProcesos) * 100) . '%' : '0%';

        $datos = [
            'usuario' => Auth::user(),
            'empresa' => 'Leon Plast',
            'stats' => [
                'produccion_hoy' => number_format($produccionHoy, 0),
                'ordenes_activas' => $ordenesActivas,
                'alertas_almacen' => $alertasAlmacen,
                'eficiencia' => $eficiencia
            ]
        ];

        return view('admin.dashboard', $datos);
    }
}
