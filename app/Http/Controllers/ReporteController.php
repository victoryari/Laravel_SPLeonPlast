<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function index()
    {
        return view('reportes.index');
    }

    public function produccion(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->startOfMonth()->toDateString());
        $fechaFin = $request->input('fecha_fin', now()->toDateString());

        $ordenes = DB::table('orden_produccion_global')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->where('activo', 1)
            ->orderBy('fecha', 'desc')
            ->get();

        $totalKg = DB::table('componentes_orden_produccion_global')
            ->whereBetween('fecha_creacion', [$fechaInicio, $fechaFin])
            ->where('estado', 1)
            ->sum('cantidad');

        return view('reportes.produccion', compact('ordenes', 'totalKg', 'fechaInicio', 'fechaFin'));
    }

    public function inventario()
    {
        $productos = DB::table('inventario')
            ->join('producto', 'inventario.codigo_producto', '=', 'producto.codigo')
            ->join('almacen', 'inventario.codigo_almacen', '=', 'almacen.codigo_almacen')
            ->select('inventario.*', 'producto.descripcion as producto_desc', 'almacen.descripcion as almacen_desc')
            ->where('inventario.stock_actual', '>', 0)
            ->orderBy('producto.descripcion')
            ->get();

        return view('reportes.inventario', compact('productos'));
    }
}
