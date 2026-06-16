@extends('layouts.app')

@section('title', 'Gestión de Órdenes de Producción')

@section('content')
<div class="container mx-auto pb-8 md:pb-10">
    <div class="flex justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Órdenes de Producción</h1>
            <p class="text-xs sm:text-sm text-gray-600">Administración de las órdenes activas en el sistema</p>
        </div>
        <a href="{{ route('produccion.ordenes.create') }}" class="shrink-0 flex items-center justify-center bg-primary hover:bg-primary-dark text-white font-semibold py-2 px-3 sm:px-4 rounded-lg shadow transition">
            <i class="fas fa-plus"></i>
            <span class="hidden sm:inline ml-2">Nueva Orden</span>
        </a>
    </div>

    {{-- Filtros --}}
    <div class="bg-white p-3 md:p-4 rounded-xl shadow-sm border border-slate-200 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form method="GET" action="{{ route('produccion.ordenes.index') }}" class="w-full flex flex-col md:flex-row gap-4">
            
            <div class="flex items-center gap-2">
                <div class="flex flex-col">
                    <label class="text-[10px] uppercase text-gray-500 font-bold mb-1">Fecha Desde</label>
                    <input type="date" name="fecha_desde" value="{{ $fecha_desde }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary outline-none">
                </div>
                <div class="flex flex-col">
                    <label class="text-[10px] uppercase text-gray-500 font-bold mb-1">Fecha Hasta</label>
                    <input type="date" name="fecha_hasta" value="{{ $fecha_hasta }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary outline-none">
                </div>
            </div>

            <div class="flex-1 flex flex-col">
                <label class="text-[10px] uppercase text-gray-500 font-bold mb-1">Búsqueda</label>
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por OP o producto..." 
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary text-sm outline-none">
                </div>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="bg-primary hover:bg-primary-dark text-white px-5 py-2 rounded-lg text-sm font-bold transition-colors">
                    <i class="fas fa-filter mr-1"></i> Filtrar
                </button>
                <a href="{{ route('produccion.ordenes.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-bold transition-colors border border-gray-300">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    {{-- Tabla de Órdenes --}}
    <div id="table-container" class="transition-opacity duration-300">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-slate-800 text-slate-300 uppercase tracking-wider font-semibold">
                            <th class="p-4 border-r border-slate-700 text-center">OP</th>
                            <th class="p-4 border-r border-slate-700 text-center">Producto</th>
                            <th class="p-4 border-r border-slate-700 text-center">Fecha</th>
                            <th class="p-4 border-r border-slate-700 text-center">Estado</th>
                            <th class="p-4 border-r border-slate-700 text-center">Acciones</th>
                        </tr>
                    </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($ordenes as $o)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-4 border-r border-slate-200 text-center font-medium text-gray-900">
                                {{ $o->codigo_op }}
                            </td>
                            <td class="p-4 border-r border-slate-200">
                                {{ $o->descripcion_producto_proceso ?? 'N/A' }}
                            </td>
                            <td class="p-4 border-r border-slate-200 text-center text-gray-600">
                                {{ \Carbon\Carbon::parse($o->fecha)->format('d/m/Y') }}
                            </td>
                            <td class="p-4 border-r border-slate-200 text-center">
                                @if($o->estado == 'COMPLETADO')
                                    <x-badge color="green">{{ $o->estado }}</x-badge>
                                @elseif($o->estado == 'EN_PROCESO')
                                    <x-badge color="blue">{{ str_replace('_', ' ', $o->estado) }}</x-badge>
                                @elseif($o->estado == 'CANCELADO')
                                    <x-badge color="red">{{ $o->estado }}</x-badge>
                                @else
                                    <x-badge color="yellow">{{ $o->estado ?? 'PENDIENTE' }}</x-badge>
                                @endif
                            </td>
                            <td class="p-4 border-r border-slate-200 text-center">
                                <a href="{{ route('ordenes.procesos.index', $o->idop) }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-lg text-white bg-primary hover:bg-primary-dark shadow-sm transition-colors">
                                    <i class="fas fa-cogs mr-1"></i> Procesos
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-500">
                                <i class="fas fa-folder-open text-4xl text-gray-300 mb-3 block"></i>
                                No se encontraron órdenes de producción en el sistema.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($ordenes->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 bg-gray-50">
                {{ $ordenes->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
