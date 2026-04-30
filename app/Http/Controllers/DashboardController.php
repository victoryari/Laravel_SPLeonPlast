<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Mostrar la página principal del sistema.
     */
    public function index()
    {
        // Aquí podrías obtener datos reales de tus tablas:
        // $totalProduccion = Produccion::count();
        // $alertasStock = Producto::where('stock', '<', 10)->count();

        $datos = [
            'usuario' => Auth::user(),
            'empresa' => 'Leon Plast',
            'stats' => [
                'produccion_hoy' => 1250,
                'ordenes_activas' => 12,
                'alertas_almacen' => 4,
                'eficiencia' => '94%'
            ]
        ];

        return view('admin/dashboard', $datos);
    }
}