@extends('layouts.app')
@section('title', 'Transferencias entre Almacenes')

@section('content')
<div class="container mx-auto pb-8 md:pb-10">
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Transferencias de Almacén</h1>
            <p class="text-xs sm:text-sm text-gray-500">Historial de movimientos de stock entre almacenes.</p>
        </div>
        <a href="{{ route('inventario.transferencias.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-bold shadow-md shadow-blue-200 transition-all">
            <i class="fas fa-exchange-alt"></i> Nueva Transferencia
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-3">
            <i class="fas fa-check-circle text-xl"></i>
            <span class="font-medium text-sm">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-3">
            <i class="fas fa-exclamation-circle text-xl"></i>
            <span class="font-medium text-sm">{{ session('error') }}</span>
        </div>
    @endif

    <div class="bg-white p-3 md:p-4 rounded-xl shadow-sm border border-slate-200 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form method="GET" action="{{ route('inventario.transferencias.index') }}" class="w-full flex flex-col md:flex-row gap-4">
            
            <div class="flex items-center gap-2">
                <div class="flex flex-col">
                    <label class="text-[10px] uppercase text-gray-500 font-bold mb-1">Fecha Desde</label>
                    <input type="date" name="fecha_desde" value="{{ $fecha_desde }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" max="{{ date('Y-m-d') }}">
                </div>
                <div class="flex flex-col">
                    <label class="text-[10px] uppercase text-gray-500 font-bold mb-1">Fecha Hasta</label>
                    <input type="date" name="fecha_hasta" value="{{ $fecha_hasta }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" max="{{ date('Y-m-d') }}">
                </div>
            </div>

            <div class="flex-1 flex flex-col">
                <label class="text-[10px] uppercase text-gray-500 font-bold mb-1">Búsqueda</label>
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por número o nota..." 
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm outline-none">
                </div>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-lg text-sm font-bold transition-colors">
                    <i class="fas fa-filter mr-1"></i> Filtrar
                </button>
                <a href="{{ route('inventario.transferencias.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-bold transition-colors border border-gray-300">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="bg-slate-800 text-slate-300 uppercase tracking-wider font-semibold border-b border-slate-700">
                        <th class="p-4">Documento</th>
                        <th class="p-4">Fecha</th>
                        <th class="p-4">Origen</th>
                        <th class="p-4">Destino</th>
                        <th class="p-4">Estado</th>
                        <th class="p-4 text-center">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($transferencias as $t)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4 font-bold text-gray-800">{{ $t->numero_transferencia }}</td>
                        <td class="p-4 text-gray-600">{{ \Carbon\Carbon::parse($t->fecha_transferencia)->format('d/m/Y H:i') }}</td>
                        <td class="p-4">
                            <span class="inline-block px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs font-bold">{{ $t->almacenOrigen->descripcion ?? $t->codigo_almacen_origen }}</span>
                        </td>
                        <td class="p-4">
                            <span class="inline-block px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs font-bold">{{ $t->almacenDestino->descripcion ?? $t->codigo_almacen_destino }}</span>
                        </td>
                        <td class="p-4">
                            @if($t->estado == 'COMPLETADO')
                                <span class="text-green-600 bg-green-50 px-2.5 py-1 rounded-md text-[10px] font-black border border-green-200">COMPLETADO</span>
                            @else
                                <span class="text-red-600 bg-red-50 px-2.5 py-1 rounded-md text-[10px] font-black border border-red-200">ANULADO</span>
                            @endif
                        </td>
                        <td class="p-4 text-center">
                            <a href="{{ route('inventario.transferencias.show', $t->id_transferencia) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-bold text-gray-700 hover:bg-gray-50 transition shadow-sm">
                                <i class="fas fa-eye text-blue-600"></i> Ver
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-500 italic">No se han registrado transferencias de almacén.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transferencias->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                {{ $transferencias->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
