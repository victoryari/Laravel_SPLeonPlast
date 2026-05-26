@extends('layouts.app')
@section('title', 'Detalle de Compra #' . $compra->serie_documento . '-' . $compra->numero_documento)

@section('content')
<div class="container mx-auto px-4 py-6 max-w-7xl">
    
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight">
                    {{ $compra->tipo_documento }}: {{ $compra->serie_documento }}-{{ $compra->numero_documento }}
                </h1>
                <span class="px-3 py-1 rounded-full text-xs font-bold 
                    @switch($compra->estado)
                        @case('PENDIENTE') bg-amber-100 text-amber-700 @break
                        @case('CANCELADA') bg-red-100 text-red-700 @break
                        @default bg-green-100 text-green-700
                    @endswitch">
                    {{ $compra->estado }}
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-1">Registrado el {{ \Carbon\Carbon::parse($compra->fecha_creacion)->format('d/m/Y H:i') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('compras.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-all shadow-sm">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-semibold hover:bg-slate-700 transition-all shadow-sm">
                <i class="fas fa-print"></i> Imprimir
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-50/50 border-b border-slate-100 px-6 py-4">
                    <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i class="fas fa-file-invoice text-blue-500"></i> Información del Documento
                    </h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase">Proveedor</p>
                            <p class="text-sm font-semibold text-slate-800">{{ $compra->proveedor }}</p>
                            <p class="text-xs text-slate-500">RUC: {{ $compra->ruc_proveedor }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase">Fecha Emisión</p>
                            <p class="text-sm font-semibold text-slate-800">{{ \Carbon\Carbon::parse($compra->fecha_compra)->format('d/m/Y') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase">Moneda</p>
                            <p class="text-sm font-semibold text-slate-800">{{ $compra->moneda ?? 'PEN' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase">Condición</p>
                            <p class="text-sm font-semibold text-slate-800">{{ $compra->condicion_pago ?? 'CONTADO' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-50/50 border-b border-slate-100 px-6 py-4">
                    <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i class="fas fa-boxes text-blue-500"></i> Detalle de Items
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse table-fixed">
                        <colgroup>
                            <col class="w-[40%]">
                            <col class="w-[15%]">
                            <col class="w-[13%]">
                            <col class="w-[16%]">
                            <col class="w-[16%]">
                        </colgroup>
                        <thead>
                            <tr class="bg-slate-50 text-[11px] uppercase text-slate-500 tracking-wider">
                                <th class="p-2 font-semibold">Producto</th>
                                <th class="p-2 font-semibold text-center">Almacén</th>
                                <th class="p-2 font-semibold text-center">Cant.</th>
                                <th class="p-2 font-semibold text-right">P. Unit.</th>
                                <th class="p-2 font-semibold text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($compra->detalles as $detalle)
                            <tr class="text-xs hover:bg-slate-50/50 transition-colors">
                                <td class="p-2">
                                    <div class="truncate font-medium text-slate-800" title="{{ $detalle->descripcion_producto }}">{{ $detalle->descripcion_producto }}</div>
                                    <div class="text-[10px] text-slate-500">{{ $detalle->codigo_producto }}</div>
                                </td>
                                <td class="p-2 text-center">
                                    <span class="px-1.5 py-0.5 bg-slate-100 rounded text-[10px] font-medium text-slate-600">
                                        {{ $detalle->codigo_almacen }}
                                    </span>
                                </td>
                                <td class="p-2 text-center font-semibold text-slate-700">
                                    {{ number_format($detalle->cantidad, 2) }}
                                </td>
                                <td class="p-2 text-right text-slate-600">
                                    {{ number_format($detalle->precio_unitario, 2) }}
                                </td>
                                <td class="p-2 text-right font-semibold text-slate-900">
                                    S/ {{ number_format($detalle->total, 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="lg:col-span-1 space-y-6">
            <div class="bg-slate-800 rounded-2xl shadow-lg border border-slate-700 overflow-hidden">
                <div class="p-6">
                    <h2 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                        <i class="fas fa-calculator text-blue-400"></i> Resumen de Pago
                    </h2>
                    <div class="space-y-4">
                        <div class="flex justify-between text-slate-300 text-sm">
                            <span>Subtotal:</span> 
                            <span class="font-medium text-white">S/ {{ number_format($compra->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-slate-300 text-sm">
                            <span>IGV (18%):</span> 
                            <span class="font-medium text-white">S/ {{ number_format($compra->igv, 2) }}</span>
                        </div>
                        <div class="pt-4 mt-4 border-t border-slate-600 flex justify-between items-center">
                            <span class="text-slate-200 font-bold">TOTAL:</span> 
                            <span class="text-2xl font-black text-blue-400">S/ {{ number_format($compra->total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-xs font-bold text-slate-400 uppercase mb-4">Auditoría</h3>
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold uppercase">
                        {{ substr($compra->creador->nombre_usuario ?? 'U', 0, 1) }}
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-800">{{ $compra->creador->nombre_usuario ?? 'N/A' }}</p>
                        <p class="text-xs text-slate-500">Registró esta operación</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        header, footer, nav, aside, button, .no-print {
            display: none !important;
        }
        .container {
            max-width: 100% !important;
            width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .shadow-sm, .shadow-lg {
            shadow: none !important;
            border: 1px solid #e2e8f0 !important;
        }
        .bg-slate-800 {
            background-color: white !important;
            color: black !important;
        }
        .text-blue-400, .text-white, .text-slate-300 {
            color: black !important;
        }
    }
</style>
@endsection