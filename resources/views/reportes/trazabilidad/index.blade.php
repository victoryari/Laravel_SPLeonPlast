@extends('layouts.app')

@section('title', 'Trazabilidad de Lotes')

@section('content')
<div class="container mx-auto max-w-6xl pb-8 md:pb-10">
    <div class="flex justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800">Trazabilidad de Lotes</h1>
            <p class="text-xs sm:text-sm text-slate-600">Rastreo de insumos, procesos y productos terminados.</p>
        </div>
        @if(isset($resultados))
        <button onclick="window.print()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg shadow inline-flex items-center">
            <i class="fas fa-file-pdf mr-2"></i> Exportar PDF
        </button>
        @endif
    </div>

    <!-- Buscador Central -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-8 text-center print:hidden">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Ingresa el número de lote a rastrear</h2>
        <form action="{{ route('reportes.trazabilidad') }}" method="GET" class="max-w-2xl mx-auto flex gap-2">
            <div class="relative flex-grow">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-slate-400"></i>
                </div>
                <input type="text" name="lote" value="{{ $lote ?? '' }}" placeholder="Ej. LOTE-12345, MERMA-P2..." class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 font-semibold text-slate-700 uppercase" required>
            </div>
            <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-semibold py-3 px-6 rounded-xl shadow transition whitespace-nowrap">
                Rastrear <i class="fas fa-arrow-right ml-2"></i>
            </button>
        </form>
    </div>

    @if(isset($lote) && !$resultados['origen'] && !$resultados['consumos'])
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-r-xl text-center">
            <i class="fas fa-exclamation-triangle text-yellow-500 text-4xl mb-3"></i>
            <h3 class="text-lg font-bold text-yellow-800">Lote no encontrado</h3>
            <p class="text-yellow-700">No existen movimientos registrados para el lote <strong>{{ $lote }}</strong>.</p>
        </div>
    @endif

    @if(isset($resultados) && ($resultados['origen'] || $resultados['consumos']))
    
    <!-- CONTENEDOR PRINCIPAL DEL ÁRBOL -->
    <div class="bg-slate-50 p-6 rounded-2xl border border-slate-200 shadow-inner">
        <div class="text-center mb-8">
            <h3 class="text-2xl font-black text-slate-800 uppercase tracking-wider bg-white inline-block px-6 py-2 rounded-full border shadow-sm border-indigo-200 text-indigo-900">
                LOTE: {{ $resultados['lote_buscado'] }}
            </h3>
        </div>

        <div class="space-y-12">
            
            <!-- 1. ORÍGENES (Hacia Atrás) -->
            @if(count($resultados['origen']) > 0)
            <div>
                <h4 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <i class="fas fa-history"></i> Origen del Lote
                </h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($resultados['origen'] as $orig)
                        @if($orig['tipo'] != 'PRODUCCION')
                            <div class="bg-white p-5 rounded-xl border-l-4 border-blue-500 shadow-sm">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                    <div>
                                        <h5 class="font-bold text-slate-800">Ingreso ({{ $orig['tipo'] }})</h5>
                                        <p class="text-xs text-slate-500">Doc: {{ $orig['documento'] ?? 'N/A' }} | {{ isset($orig['fecha']) ? \Carbon\Carbon::parse($orig['fecha'])->format('d/m/Y') : '' }}</p>
                                    </div>
                                </div>
                                <div class="mt-3 text-sm">
                                    @if(isset($orig['proveedor']) && $orig['proveedor'] !== 'Desconocido')
                                        <p><span class="font-semibold text-slate-600">Proveedor:</span> {{ $orig['proveedor'] }}</p>
                                    @endif
                                    @if(isset($orig['factura_asociada']) && $orig['factura_asociada'])
                                        <p><span class="font-semibold text-slate-600">Factura:</span> <span class="text-indigo-600 font-medium">{{ $orig['factura_asociada'] }}</span></p>
                                    @endif
                                    <p><span class="font-semibold text-slate-600">Producto:</span> {{ $orig['producto'] ?? 'N/A' }}</p>
                                    <p><span class="font-semibold text-slate-600">Cantidad Recibida:</span> <span class="font-bold">{{ number_format($orig['cantidad'] ?? 0, 2) }}</span></p>
                                </div>
                            </div>
                        @else
                            <div class="bg-white p-5 rounded-xl border-l-4 border-emerald-500 shadow-sm">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                        <i class="fas fa-industry"></i>
                                    </div>
                                    <div>
                                        <h5 class="font-bold text-slate-800">Fabricación (Producción)</h5>
                                        <p class="text-xs text-slate-500">OP #{{ $orig['op'] ?? 'N/A' }} | {{ isset($orig['fecha']) ? \Carbon\Carbon::parse($orig['fecha'])->format('d/m/Y') : '' }}</p>
                                    </div>
                                </div>
                                <div class="mt-3 text-sm">
                                    <p><span class="font-semibold text-slate-600">Producto:</span> {{ $orig['producto'] ?? 'N/A' }}</p>
                                    <p><span class="font-semibold text-slate-600">Cantidad Producida:</span> <span class="font-bold text-emerald-600">{{ number_format($orig['cantidad'] ?? 0, 2) }}</span></p>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                @if(isset($resultados['origen_insumos']) && count($resultados['origen_insumos']) > 0)
                    <div class="mt-4 ml-8 border-l-2 border-dashed border-slate-300 pl-8 relative">
                        <div class="absolute -left-3 top-4 bg-slate-50 w-6 h-6 flex items-center justify-center">
                            <i class="fas fa-link text-slate-400 text-xs"></i>
                        </div>
                        <h5 class="text-xs font-bold text-slate-500 uppercase mb-3">Insumos fundidos en esta OP:</h5>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($resultados['origen_insumos'] as $insumo)
                                <div class="bg-white p-3 rounded-lg border border-slate-200 text-sm shadow-sm flex justify-between items-center hover:border-indigo-300 transition-colors">
                                    <div>
                                        <p class="font-semibold text-slate-700">{{ $insumo['producto'] }}</p>
                                        <p class="text-xs text-slate-500">Lote Origen: <a href="{{ route('reportes.trazabilidad', ['lote' => $insumo['lote']]) }}" class="text-indigo-600 font-bold hover:underline">{{ $insumo['lote'] }}</a></p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs text-slate-400 block">Consumido</span>
                                        <span class="font-bold">{{ number_format($insumo['cantidad_consumida'], 2) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            @endif

            <!-- FLECHA DIVISORIA SI HAY ORIGEN Y CONSUMOS -->
            @if(count($resultados['origen']) > 0 && count($resultados['consumos']) > 0)
                <div class="flex justify-center text-slate-300 text-3xl">
                    <i class="fas fa-arrow-down"></i>
                </div>
            @endif

            <!-- 2. CONSUMOS Y DESTINOS (Hacia Adelante) -->
            @if(count($resultados['consumos']) > 0)
            <div>
                <h4 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <i class="fas fa-random"></i> Trazabilidad Hacia Adelante (Uso)
                </h4>

                <div class="space-y-6">
                    @foreach($resultados['consumos'] as $consumo)
                        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                            <div class="bg-slate-100 px-5 py-3 border-b flex justify-between items-center">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-cogs text-slate-500"></i>
                                    <span class="font-bold text-slate-700">Consumido en OP #{{ $consumo['op'] }}</span>
                                </div>
                                <div class="text-right text-sm">
                                    <span class="text-slate-500">{{ \Carbon\Carbon::parse($consumo['fecha'])->format('d/m/Y') }}</span>
                                    <span class="ml-3 bg-white px-2 py-1 rounded border font-bold text-slate-700">-{{ number_format($consumo['cantidad'], 2) }}</span>
                                </div>
                            </div>
                            
                            <div class="p-5 bg-emerald-50/30">
                                <h5 class="text-xs font-bold text-emerald-600 uppercase mb-3"><i class="fas fa-box-open mr-1"></i> Productos generados en esta OP:</h5>
                                @php
                                    $destinosDeEstaOP = collect($resultados['destinos'])->where('op', $consumo['op']);
                                    if(isset($consumo['id_proceso']) && $consumo['id_proceso']) {
                                        $destinosDeEstaOP = $destinosDeEstaOP->where('id_proceso', $consumo['id_proceso']);
                                    }
                                @endphp

                                @if($destinosDeEstaOP->count() > 0)
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                        @foreach($destinosDeEstaOP as $dest)
                                            <div class="bg-white p-3 rounded-lg border border-emerald-200 shadow-sm hover:border-emerald-400 transition-colors">
                                                <p class="font-semibold text-slate-700 text-sm truncate" title="{{ $dest['producto'] }}">{{ $dest['producto'] }}</p>
                                                <div class="flex justify-between items-end mt-2">
                                                    <div>
                                                        <p class="text-xs text-slate-500">Nuevo Lote:</p>
                                                        <a href="{{ route('reportes.trazabilidad', ['lote' => $dest['lote_generado']]) }}" class="text-emerald-700 font-bold hover:underline text-xs">{{ $dest['lote_generado'] }}</a>
                                                    </div>
                                                    <div class="text-right">
                                                        <span class="font-bold text-emerald-600">+{{ number_format($dest['cantidad'], 2) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-slate-500 italic">Esta OP no registró ingresos de productos terminados aún, o toda la cantidad se reportó como merma sin lote rastreable.</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- 3. AJUSTES MANUALES (Bajas/Mermas) -->
            @if(isset($resultados['ajustes_salida']) && count($resultados['ajustes_salida']) > 0)
            <div class="mt-8 border-t border-slate-200 pt-8">
                <h4 class="text-sm font-bold text-red-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <i class="fas fa-exclamation-triangle"></i> Ajustes / Bajas Manuales
                </h4>
                <div class="space-y-3">
                    @foreach($resultados['ajustes_salida'] as $ajuste)
                        <div class="bg-red-50 border border-red-200 rounded-xl p-4 flex justify-between items-center shadow-sm">
                            <div>
                                <h5 class="font-bold text-red-800 text-sm">{{ $ajuste['documento'] }} #{{ $ajuste['numero'] }}</h5>
                                <p class="text-xs text-red-600 mt-1"><i class="fas fa-info-circle mr-1"></i> {{ $ajuste['observaciones'] ?: 'Sin observaciones' }}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-xs text-red-500 block">{{ \Carbon\Carbon::parse($ajuste['fecha'])->format('d/m/Y') }}</span>
                                <span class="font-bold text-red-700">-{{ number_format($ajuste['cantidad'], 2) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>
    @endif
</div>

<!-- Print Styles -->
<style>
@media print {
    body { background-color: white !important; }
    .bg-slate-50 { background-color: transparent !important; border: none !important; }
    .shadow-sm, .shadow-inner { box-shadow: none !important; border: 1px solid #e2e8f0 !important; }
    #sidebar, header, nav, .print\:hidden { display: none !important; }
    .container { max-width: 100% !important; padding: 0 !important; margin: 0 !important; }
    a { text-decoration: none !important; color: inherit !important; }
}
</style>
@endsection
