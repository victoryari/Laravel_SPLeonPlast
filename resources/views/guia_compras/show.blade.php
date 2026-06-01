@extends('layouts.app')
@section('title', 'Detalle de Guía de Remisión')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-5xl">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight">Guía de Remisión N° {{ $guia->numero_guia }}</h1>
            <p class="text-sm text-slate-500 mt-1">Registrada el {{ \Carbon\Carbon::parse($guia->fecha_registro)->format('d/m/Y H:i') }} por {{ $guia->creador->nombre_completo ?? 'Sistema' }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('guia_compras.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-semibold transition">
                <i class="fas fa-arrow-left mr-2"></i>Volver
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="md:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">Información del Proveedor</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-slate-400 font-semibold mb-1">Razón Social</p>
                    <p class="font-bold text-slate-800">{{ $guia->proveedor }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-semibold mb-1">RUC</p>
                    <p class="font-semibold text-slate-700">{{ $guia->ruc_proveedor ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-semibold mb-1">Fecha Emisión (Guía)</p>
                    <p class="font-semibold text-slate-700">{{ \Carbon\Carbon::parse($guia->fecha_emision)->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-semibold mb-1">Estado</p>
                    @php
                        $badgeColor = [
                            'RECIBIDA' => 'green',
                            'FACTURADA' => 'blue',
                            'ANULADA' => 'red'
                        ][$guia->estado] ?? 'slate';
                    @endphp
                    <x-badge color="{{ $badgeColor }}">{{ $guia->estado }}</x-badge>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">Facturas Vinculadas</h2>
            @if($guia->compras->count() > 0)
                <ul class="space-y-3">
                    @foreach($guia->compras as $compra)
                    <li class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-bold text-slate-800">{{ $compra->serie_documento }}-{{ $compra->numero_documento }}</p>
                            <p class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($compra->fecha_compra)->format('d/m/Y') }}</p>
                        </div>
                        <a href="{{ route('compras.show', $compra->id_compra) }}" class="text-primary hover:underline text-xs font-semibold">Ver Factura</a>
                    </li>
                    @endforeach
                </ul>
            @else
                <div class="text-center py-4">
                    <p class="text-sm text-slate-500 italic">No hay facturas vinculadas aún.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Detalle de Ingreso (ALMACÉN COMPRAS NAC/IMP)</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[11px] font-semibold">
                        <th class="p-3 border-b border-slate-200 text-center">Item</th>
                        <th class="p-3 border-b border-slate-200">Producto</th>
                        <th class="p-3 border-b border-slate-200 text-center">U.M.</th>
                        <th class="p-3 border-b border-slate-200 text-center">Lote</th>
                        <th class="p-3 border-b border-slate-200 text-center">Vencimiento</th>
                        <th class="p-3 border-b border-slate-200 text-right">Cantidad Entrante</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($guia->detalles as $index => $item)
                    <tr class="hover:bg-slate-50">
                        <td class="p-3 text-center text-sm font-semibold text-slate-400">{{ $index + 1 }}</td>
                        <td class="p-3">
                            <p class="text-sm font-bold text-slate-800">{{ $item->codigo_producto }}</p>
                            <p class="text-xs text-slate-500">{{ $item->descripcion_producto }}</p>
                        </td>
                        <td class="p-3 text-center text-sm font-semibold text-slate-600">{{ $item->codigo_unidad_medida }}</td>
                        <td class="p-3 text-center text-sm text-slate-600">{{ $item->lote ?: '-' }}</td>
                        <td class="p-3 text-center text-sm text-slate-600">
                            {{ $item->fecha_vencimiento ? \Carbon\Carbon::parse($item->fecha_vencimiento)->format('d/m/Y') : '-' }}
                        </td>
                        <td class="p-3 text-right text-sm font-bold text-slate-800">{{ number_format($item->cantidad, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
    @if($guia->observaciones)
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-2">Observaciones</h2>
        <p class="text-sm text-slate-700">{{ $guia->observaciones }}</p>
    </div>
    @endif
</div>
@endsection
