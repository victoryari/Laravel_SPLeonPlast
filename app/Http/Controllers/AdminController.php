<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Muestra el panel de control exclusivo del Administrador.
     */
    public function index()
    {
        // Estos son los datos estadísticos que requiere tu vista admin.dashboard.blade.php
        // Más adelante conectaremos esto con consultas reales a tus tablas de Leon Plast
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

        return view('admin.dashboard', $datos);
    }
}