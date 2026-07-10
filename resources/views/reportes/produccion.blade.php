@extends('layouts.app')
@section('title', 'Reporte de Producción')

@section('content')
<div class="container mx-auto pb-8 md:pb-10">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
        <div>
            <nav class="flex text-sm text-gray-500 mb-2">
                <a href="{{ route('reportes.index') }}" class="hover:text-primary transition-colors">Reportes</a>
                <span class="mx-2">/</span>
                <span class="text-gray-700">Producción</span>
            </nav>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Reporte de Producción</h1>
        </div>
    </div>

    <form method="GET" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Fecha Inicio</label>
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm" max="{{ date('Y-m-d') }}">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Fecha Fin</label>
                <input type="date" name="fecha_fin" value="{{ $fechaFin }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm" max="{{ date('Y-m-d') }}">
            </div>
            <button type="submit" class="bg-primary hover:bg-primary-dark text-white font-semibold py-2 px-4 rounded-lg shadow transition text-sm">
                <i class="fas fa-search mr-1"></i> Filtrar
            </button>
        </div>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 border-b-4 border-b-blue-500">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Procesado</p>
            <p class="text-3xl font-black text-gray-800 mt-2">{{ number_format($totalKg, 0) }} <span class="text-sm font-medium text-gray-500">Kg</span></p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 border-b-4 border-b-indigo-500">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Órdenes en el Período</p>
            <p class="text-3xl font-black text-gray-800 mt-2">{{ $ordenes->count() }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead>
                    <tr class="bg-slate-800 text-slate-300 uppercase tracking-wider font-semibold">
                        <th class="p-4">OP</th>
                        <th class="p-4">Producto</th>
                        <th class="p-4">Fecha</th>
                        <th class="p-4 text-center">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($ordenes as $o)
                    <tr class="hover:bg-gray-50">
                        <td class="p-4 font-medium">{{ $o->codigo_op }}</td>
                        <td class="p-4">{{ $o->descripcion_producto_proceso }}</td>
                        <td class="p-4">{{ \Carbon\Carbon::parse($o->fecha)->format('d/m/Y') }}</td>
                        <td class="p-4 text-center">
                            <x-badge color="{{ $o->estado == 'COMPLETADO' ? 'green' : ($o->estado == 'EN_PROCESO' ? 'blue' : 'yellow') }}">
                                {{ $o->estado }}
                            </x-badge>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="p-8 text-center text-gray-400">No se encontraron órdenes en el período seleccionado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
