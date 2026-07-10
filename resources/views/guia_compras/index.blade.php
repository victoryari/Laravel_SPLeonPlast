@extends('layouts.app')
@section('title', 'Guías de Remisión de Compra')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-7xl">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight">Guías de Remisión</h1>
            <p class="text-sm text-slate-500 mt-1">Recepción física de materia prima al almacén.</p>
        </div>
        <a href="{{ route('guia_compras.create') }}" class="px-5 py-2.5 bg-primary hover:bg-primary-dark text-white rounded-xl text-sm font-semibold shadow-sm transition">
            <i class="fas fa-plus mr-2"></i>Nueva Guía
        </a>
    </div>

    {{-- Filtros --}}
    <div class="bg-white p-3 md:p-4 rounded-xl shadow-sm border border-slate-200 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form method="GET" action="{{ route('guia_compras.index') }}" class="w-full flex flex-col md:flex-row gap-4">
            
            <div class="flex items-center gap-2">
                <div class="flex flex-col">
                    <label class="text-[10px] uppercase text-slate-500 font-bold mb-1">Fecha Desde</label>
                    <input type="date" name="fecha_desde" value="{{ $fecha_desde }}" class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary outline-none" max="{{ date('Y-m-d') }}">
                </div>
                <div class="flex flex-col">
                    <label class="text-[10px] uppercase text-slate-500 font-bold mb-1">Fecha Hasta</label>
                    <input type="date" name="fecha_hasta" value="{{ $fecha_hasta }}" class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary outline-none" max="{{ date('Y-m-d') }}">
                </div>
            </div>

            <div class="flex-1 flex flex-col">
                <label class="text-[10px] uppercase text-slate-500 font-bold mb-1">Búsqueda</label>
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-slate-400"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por Guía N° o Proveedor..." 
                        class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary text-sm outline-none">
                </div>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="bg-primary hover:bg-primary-dark text-white px-5 py-2 rounded-lg text-sm font-bold transition-colors">
                    <i class="fas fa-filter mr-1"></i> Filtrar
                </button>
                <a href="{{ route('guia_compras.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-lg text-sm font-bold transition-colors border border-slate-300">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[11px] font-semibold">
                        <th class="p-4 border-b border-slate-200">Emisión</th>
                        <th class="p-4 border-b border-slate-200">Guía N°</th>
                        <th class="p-4 border-b border-slate-200">Proveedor</th>
                        <th class="p-4 border-b border-slate-200 text-center">Cant. Total</th>
                        <th class="p-4 border-b border-slate-200 text-center">Estado</th>
                        <th class="p-4 border-b border-slate-200 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($guias as $guia)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 whitespace-nowrap text-slate-500 font-medium">
                            {{ \Carbon\Carbon::parse($guia->fecha_emision)->format('d/m/Y') }}
                        </td>
                        <td class="p-4 font-bold text-slate-800">
                            {{ $guia->numero_guia }}
                        </td>
                        <td class="p-4 font-semibold text-primary">
                            {{ Str::limit($guia->proveedor, 30) }}
                        </td>
                        <td class="p-4 text-center font-bold text-slate-700">
                            {{ number_format($guia->detalles_sum_cantidad, 2) }}
                        </td>
                        <td class="p-4 text-center">
                            @php
                                $badgeColor = [
                                    'RECIBIDA' => 'green',
                                    'FACTURADA' => 'blue',
                                    'ANULADA' => 'red'
                                ][$guia->estado] ?? 'slate';
                            @endphp
                            <x-badge color="{{ $badgeColor }}">{{ $guia->estado }}</x-badge>
                        </td>
                        <td class="p-4 text-right whitespace-nowrap">
                            <a href="{{ route('guia_compras.show', $guia->id_guia) }}" class="inline-flex items-center px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition">
                                <i class="fas fa-eye mr-1.5 text-slate-500"></i>Ver Detalle
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-12 text-center text-slate-400">
                            <i class="fas fa-box-open text-4xl mb-3 opacity-30"></i>
                            <p class="font-semibold">No hay guías de remisión registradas.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($guias->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50">
            {{ $guias->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
