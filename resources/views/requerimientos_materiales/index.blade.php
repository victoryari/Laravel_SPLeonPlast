@extends('layouts.app')
@section('title', 'Requerimientos de Materiales')

@section('content')
<div class="container mx-auto pb-8 md:pb-10">
    <x-page-header title="Requerimientos de Materiales" subtitle="Solicitudes de transferencia de materiales entre almacenes">
        <x-slot:actions>
            <a href="{{ route('requerimientos_materiales.create') }}" class="btn-primary">
                <i class="fas fa-plus"></i>
                <span class="hidden sm:inline ml-2">Nuevo Requerimiento</span>
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="bg-white p-3 md:p-4 rounded-xl shadow-md mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form method="GET" action="{{ route('requerimientos_materiales.index') }}" class="w-full flex flex-col md:flex-row gap-4">
            
            <div class="flex items-center gap-2">
                <div class="flex flex-col">
                    <label class="text-[10px] uppercase text-gray-500 font-bold mb-1">Fecha Desde</label>
                    <input type="date" name="fecha_desde" value="{{ $fecha_desde }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 outline-none" max="{{ date('Y-m-d') }}">
                </div>
                <div class="flex flex-col">
                    <label class="text-[10px] uppercase text-gray-500 font-bold mb-1">Fecha Hasta</label>
                    <input type="date" name="fecha_hasta" value="{{ $fecha_hasta }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 outline-none" max="{{ date('Y-m-d') }}">
                </div>
            </div>



            <div class="flex-1 flex flex-col">
                <label class="text-[10px] uppercase text-gray-500 font-bold mb-1">Búsqueda</label>
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" name="codigo" value="{{ request('codigo') }}" placeholder="Buscar por código..." 
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 text-sm outline-none">
                </div>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-lg text-sm font-bold transition-colors">
                    <i class="fas fa-filter mr-1"></i> Filtrar
                </button>
                <a href="{{ route('requerimientos_materiales.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-bold transition-colors border border-gray-300">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 border-b border-gray-200 text-[11px] md:text-xs">
                        <th class="px-4 md:px-6 py-3 md:py-4 font-bold uppercase tracking-wider">Código / Fecha</th>
                        <th class="px-4 md:px-6 py-3 md:py-4 font-bold uppercase tracking-wider">Productos</th>
                        <th class="px-4 md:px-6 py-3 md:py-4 font-bold uppercase tracking-wider">Creado Por</th>
                        <th class="px-4 md:px-6 py-3 md:py-4 font-bold uppercase tracking-wider text-center">Estado</th>
                        <th class="px-4 md:px-6 py-3 md:py-4 font-bold uppercase tracking-wider text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs md:text-sm">
                    @forelse ($requerimientos as $req)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-4 md:px-6 py-3 md:py-4">
                                <div class="font-bold text-gray-900 uppercase">{{ $req->codigo }}</div>
                                <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($req->fecha_requerimiento)->format('d/m/Y') }}</div>
                            </td>
                            <td class="px-4 md:px-6 py-3 md:py-4">
                                <div class="text-gray-800 font-medium">
                                    {{ $req->detalles->pluck('codigo_producto')->unique()->take(3)->implode(', ') }}
                                    @if($req->detalles->pluck('codigo_producto')->unique()->count() > 3)
                                        <span class="text-xs text-gray-400">(+{{ $req->detalles->pluck('codigo_producto')->unique()->count() - 3 }})</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500">{{ $req->detalles->count() }} línea(s)</div>
                            </td>
                            <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600">{{ $req->creador->nombre_usuario ?? 'Sistema' }}</td>
                            <td class="px-4 md:px-6 py-3 md:py-4 text-center">
                                @php
                                    $colors = ['BORRADOR' => 'slate', 'PENDIENTE' => 'yellow', 'APROBADO' => 'green', 'RECHAZADO' => 'red', 'ATENDIDO_PARCIAL' => 'blue', 'ATENDIDO_TOTAL' => 'emerald', 'ANULADO' => 'red'];
                                @endphp
                                <x-badge color="{{ $colors[$req->estado] ?? 'slate' }}">{{ $req->estado }}</x-badge>
                            </td>
                            <td class="px-4 md:px-6 py-3 md:py-4 text-center">
                                <div class="flex items-center justify-center gap-3">
                                    <a href="{{ route('requerimientos_materiales.show', $req->id_requerimiento) }}" class="text-slate-400 hover:text-primary transition-colors" title="Ver Detalle">
                                        <i class="fas fa-eye text-lg"></i>
                                    </a>
                                    @if($req->estado === 'BORRADOR')
                                        <a href="{{ route('requerimientos_materiales.edit', $req->id_requerimiento) }}" class="text-slate-400 hover:text-amber-600 transition-colors" title="Editar">
                                            <i class="fas fa-edit text-lg"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500 italic">No se encontraron requerimientos.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 md:px-6 py-3 border-t bg-gray-50/50">
            {{ $requerimientos->links() }}
        </div>
    </div>
</div>
@endsection
