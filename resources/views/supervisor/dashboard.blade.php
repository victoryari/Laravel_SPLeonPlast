@extends('layouts.app')
@section('title', 'Panel de Supervisor')

@section('content')
<div class="container mx-auto pb-8 md:pb-10">
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Panel de Control - Supervisor</h1>
        <p class="text-sm text-gray-600 mt-1">Bienvenido al sistema, <span class="font-bold text-primary">{{ Auth::user()->nombre_usuario }}</span>.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 border-b-4 border-b-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Órdenes Activas</p>
                    <p class="text-3xl font-black text-gray-800 mt-2">{{ $ordenesActivas }}</p>
                </div>
                <div class="w-14 h-14 rounded-full bg-primary-50 flex items-center justify-center text-primary text-2xl shadow-inner">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 border-b-4 border-b-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Pendientes de Validar</p>
                    <p class="text-3xl font-black text-gray-800 mt-2">{{ $pendientesValidar }}</p>
                </div>
                <div class="w-14 h-14 rounded-full bg-yellow-50 flex items-center justify-center text-yellow-500 text-2xl shadow-inner">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 border-b-4 border-b-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Producción del Día</p>
                    <p class="text-3xl font-black text-gray-800 mt-2">{{ number_format($produccionDia, 0) }} <span class="text-sm font-medium text-gray-500">Kg</span></p>
                </div>
                <div class="w-14 h-14 rounded-full bg-green-50 flex items-center justify-center text-green-500 text-2xl shadow-inner">
                    <i class="fas fa-box-open"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-bold text-gray-700 uppercase text-sm tracking-wide mb-4 flex items-center">
                <i class="fas fa-clipboard-list text-primary mr-2"></i> Órdenes Recientes
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 uppercase text-xs">
                            <th class="px-4 py-2 font-semibold">OP</th>
                            <th class="px-4 py-2 font-semibold">Producto</th>
                            <th class="px-4 py-2 font-semibold">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse(DB::table('orden_produccion_global')->where('activo', 1)->orderBy('fecha', 'desc')->limit(5)->get() as $orden)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 font-medium">{{ $orden->codigo_op }}</td>
                            <td class="px-4 py-2">{{ $orden->descripcion_producto_proceso }}</td>
                            <td class="px-4 py-2">
                                <x-badge color="{{ $orden->estado == 'COMPLETADO' ? 'green' : ($orden->estado == 'EN_PROCESO' ? 'blue' : 'yellow') }}">
                                    {{ $orden->estado }}
                                </x-badge>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-4 py-4 text-center text-gray-400">Sin órdenes activas</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-bold text-gray-700 uppercase text-sm tracking-wide mb-4 flex items-center">
                <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i> Recepciones Pendientes
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 uppercase text-xs">
                            <th class="px-4 py-2 font-semibold">Producto</th>
                            <th class="px-4 py-2 font-semibold">Cantidad</th>
                            <th class="px-4 py-2 font-semibold">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse(DB::table('produccion_ingresos_proceso')->where('estado', 'PENDIENTE')->orderBy('fecha_ingreso', 'desc')->limit(5)->get() as $ing)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2">{{ $ing->descripcion_producto_proceso }}</td>
                            <td class="px-4 py-2 font-medium">{{ number_format($ing->cantidad, 2) }}</td>
                            <td class="px-4 py-2">{{ \Carbon\Carbon::parse($ing->fecha_ingreso)->format('d/m/Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-4 py-4 text-center text-gray-400">Sin recepciones pendientes</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
