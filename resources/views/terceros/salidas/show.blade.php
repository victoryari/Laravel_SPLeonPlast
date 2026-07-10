@extends('layouts.app')
@section('title', 'Detalle de Guía de Salida')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-5xl">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight">Detalle de Guía de Salida</h1>
            <p class="text-sm text-slate-500 mt-1">Guía N° {{ $guia->numero_guia }}</p>
        </div>
        <a href="{{ route('terceros.salidas.index') }}" class="px-5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm font-semibold transition">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
        <div class="p-6 border-b border-slate-100 bg-slate-50">
            <h2 class="font-bold text-slate-800 text-lg mb-4">1. Datos Generales</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Guía N°</label>
                    <div class="font-medium text-slate-800">{{ $guia->numero_guia }}</div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Fecha Emisión</label>
                    <div class="font-medium text-slate-800">{{ \Carbon\Carbon::parse($guia->fecha_emision)->format('d/m/Y') }}</div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Almacén Origen</label>
                    <div class="font-medium text-slate-800">{{ $guia->codigo_almacen_origen }}</div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Estado</label>
                    <div>
                        @php
                            $badgeColor = [
                                'EMITIDA' => 'blue',
                                'ANULADA' => 'red'
                            ][$guia->estado_guia] ?? 'slate';
                        @endphp
                        <x-badge color="{{ $badgeColor }}">{{ $guia->estado_guia }}</x-badge>
                    </div>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Proveedor Destino</label>
                    <div class="font-medium text-slate-800">{{ $guia->proveedor_destino }}</div>
                </div>
                @if($guia->ruc_proveedor)
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">RUC Proveedor</label>
                    <div class="font-medium text-slate-800">{{ $guia->ruc_proveedor }}</div>
                </div>
                @endif
            </div>
        </div>

        <div class="p-6">
            <h2 class="font-bold text-slate-800 text-lg mb-4">2. Detalle de Productos</h2>
            <div class="overflow-x-auto border border-slate-200 rounded-xl">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider font-bold">
                            <th class="p-4 border-b border-slate-200">Ítem</th>
                            <th class="p-4 border-b border-slate-200">Código de Producto</th>
                            <th class="p-4 border-b border-slate-200">Descripción</th>
                            <th class="p-4 border-b border-slate-200 text-right">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($detalles as $index => $detalle)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-4 text-slate-500 font-medium">{{ $index + 1 }}</td>
                            <td class="p-4 font-semibold text-slate-800">{{ $detalle->codigo_producto }}</td>
                            <td class="p-4 text-slate-600">{{ $detalle->descripcion_producto }}</td>
                            <td class="p-4 font-bold text-slate-800 text-right">{{ number_format($detalle->cantidad_enviada, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($guia->observaciones)
        <div class="p-6 border-t border-slate-100 bg-slate-50">
            <h2 class="font-bold text-slate-800 text-lg mb-2">3. Observaciones</h2>
            <p class="text-sm text-slate-600">{{ $guia->observaciones }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
