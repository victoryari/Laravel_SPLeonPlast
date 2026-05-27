@extends('layouts.app')

@section('title', 'Panel de Control')

@section('content')
<div class="container mx-auto">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-800">Panel General de Producción</h1>
        <p class="text-gray-600">Bienvenido al sistema de gestión de <strong>Leon Plast</strong>. Aquí tienes un resumen de la actividad hoy.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-primary">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase">Producción Hoy</p>
                    <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['produccion_hoy']) }} Unid.</p>
                </div>
                <div class="bg-primary-50 p-3 rounded-lg">
                    <i class="fas fa-industry text-primary text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase">Órdenes Activas</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['ordenes_activas'] }}</p>
                </div>
                <div class="bg-green-100 p-3 rounded-lg">
                    <i class="fas fa-tasks text-green-500 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase">Alertas Almacén</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['alertas_almacen'] }}</p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-lg">
                    <i class="fas fa-exclamation-triangle text-yellow-500 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase">Eficiencia Total</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['eficiencia'] }}</p>
                </div>
                <div class="bg-purple-100 p-3 rounded-lg">
                    <i class="fas fa-chart-pie text-purple-500 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <h3 class="font-bold text-gray-700 uppercase text-sm tracking-wide">Últimos Procesos de Mezclado</h3>
                <a href="#" class="text-xs text-blue-600 hover:underline">Ver todo</a>
            </div>
            <div class="p-0 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 uppercase text-xs">
                            <th class="px-6 py-3 font-semibold">Operario</th>
                            <th class="px-6 py-3 font-semibold">Máquina</th>
                            <th class="px-6 py-3 font-semibold">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">Carlos Mendoza</td>
                            <td class="px-6 py-4 font-medium text-gray-700">Inyectora #04</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded-full">Operando</span>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">Luis Montero</td>
                            <td class="px-6 py-4 font-medium text-gray-700">Mezcladora #01</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded-full">Mantenimiento</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="font-bold text-gray-700 uppercase text-sm tracking-wide mb-4">Acceso Rápido</h3>
            <div class="grid grid-cols-2 gap-4">
                <button class="flex flex-col items-center justify-center p-4 bg-blue-50 border border-primary-50 rounded-lg text-blue-700 hover:bg-primary-50 transition">
                    <i class="fas fa-plus-circle text-2xl mb-2"></i>
                    <span class="text-xs font-semibold">Nueva Orden</span>
                </button>
                <button class="flex flex-col items-center justify-center p-4 bg-slate-50 border border-slate-100 rounded-lg text-slate-700 hover:bg-slate-100 transition">
                    <i class="fas fa-truck-loading text-2xl mb-2"></i>
                    <span class="text-xs font-semibold">Ingreso Stock</span>
                </button>
                <button class="flex flex-col items-center justify-center p-4 bg-slate-50 border border-slate-100 rounded-lg text-slate-700 hover:bg-slate-100 transition">
                    <i class="fas fa-file-pdf text-2xl mb-2"></i>
                    <span class="text-xs font-semibold">Generar Reporte</span>
                </button>
                <button class="flex flex-col items-center justify-center p-4 bg-slate-50 border border-slate-100 rounded-lg text-slate-700 hover:bg-slate-100 transition">
                    <i class="fas fa-user-plus text-2xl mb-2"></i>
                    <span class="text-xs font-semibold">Nuevo Usuario</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection