@extends('layouts.app')
@section('title', 'Guías de Salida a Terceros')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-7xl">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight">Guías de Salida a Terceros</h1>
            <p class="text-sm text-slate-500 mt-1">Gestión de salidas físicas de inventario para maquila/servicios externos.</p>
        </div>
        <a href="{{ route('terceros.salidas.create') }}" class="px-5 py-2.5 bg-primary hover:bg-primary-dark text-white rounded-xl text-sm font-semibold shadow-sm transition">
            <i class="fas fa-plus mr-2"></i>Nueva Guía de Salida
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[11px] font-semibold">
                        <th class="p-4 border-b border-slate-200">Guía N°</th>
                        <th class="p-4 border-b border-slate-200">Fecha Emisión</th>
                        <th class="p-4 border-b border-slate-200">Proveedor</th>
                        <th class="p-4 border-b border-slate-200">Almacén Origen</th>
                        <th class="p-4 border-b border-slate-200 text-center">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($guias as $guia)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 font-bold text-slate-800">
                            {{ $guia->numero_guia }}
                        </td>
                        <td class="p-4 whitespace-nowrap text-slate-500 font-medium">
                            {{ \Carbon\Carbon::parse($guia->fecha_emision)->format('d/m/Y') }}
                        </td>
                        <td class="p-4 font-semibold text-primary">
                            {{ Str::limit($guia->proveedor_destino, 30) }}
                        </td>
                        <td class="p-4 text-slate-600">
                            {{ $guia->codigo_almacen_origen }}
                        </td>
                        <td class="p-4 text-center">
                            @php
                                $badgeColor = [
                                    'EMITIDA' => 'blue',
                                    'ANULADA' => 'red'
                                ][$guia->estado_guia] ?? 'slate';
                            @endphp
                            <x-badge color="{{ $badgeColor }}">{{ $guia->estado_guia }}</x-badge>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-12 text-center text-slate-400">
                            <i class="fas fa-truck-loading text-4xl mb-3 opacity-30"></i>
                            <p class="font-semibold">No hay guías de salida a terceros registradas.</p>
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
