@extends('layouts.app')

@section('title', 'Gestión de Órdenes de Producción')

@section('content')
<div class="container mx-auto pb-8 md:pb-10">
    <div class="flex justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Órdenes de Producción</h1>
            <p class="text-xs sm:text-sm text-gray-600">Administración de las órdenes activas en el sistema</p>
        </div>
        <a href="{{ route('produccion.ordenes.create') }}" class="shrink-0 flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-3 sm:px-4 rounded-lg shadow transition">
            <i class="fas fa-plus"></i>
            <span class="hidden sm:inline ml-2">Nueva Orden</span>
        </a>
    </div>

    {{-- Notificaciones --}}
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 md:p-4 mb-6 rounded shadow-sm text-sm md:text-base">
            <p>{{ session('success') }}</p>
        </div>
    @endif

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
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ $o->estado }}
                                    </span>
                                @elseif($o->estado == 'EN_PROCESO')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ str_replace('_', ' ', $o->estado) }}
                                    </span>
                                @elseif($o->estado == 'CANCELADO')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        {{ $o->estado }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        {{ $o->estado ?? 'PENDIENTE' }}
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 border-r border-slate-200 text-center">
                                <a href="{{ route('ordenes.procesos.index', $o->idop) }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition-colors">
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
