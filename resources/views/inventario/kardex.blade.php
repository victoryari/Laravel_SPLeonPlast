@extends('layouts.app')
@section('title', 'Historial de Movimientos - Kardex')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-7xl">
    
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight">Kardex de Inventario</h1>
        <p class="text-sm text-slate-500 mt-1">Historial cronológico de entradas, salidas y ajustes de stock.</p>
    </div>



    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
        <form action="{{ route('inventario.kardex') }}" method="GET" class="relative w-full md:w-96">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por producto o documento..." 
                class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 bg-slate-50 shadow-inner">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
        </form>
        <div class="flex gap-2">
            <button onclick="window.print()" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-sm font-bold hover:bg-slate-200 transition">
                <i class="fas fa-print mr-2"></i> Imprimir Reporte
            </button>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="bg-slate-800 text-slate-300 uppercase tracking-wider font-semibold">
                        <th class="p-4 border-r border-slate-700">Fecha y Hora</th>
                        <th class="p-4 border-r border-slate-700">Producto / Insumo</th>
                        <th class="p-4 border-r border-slate-700 text-center">Tipo</th>
                        <th class="p-4 border-r border-slate-700">Documento Ref.</th>
                        <th class="p-4 border-r border-slate-700 text-right bg-slate-700/50">Entrada</th>
                        <th class="p-4 border-r border-slate-700 text-right bg-slate-700/50">Salida</th>
                        <th class="p-4 text-right bg-blue-900/30 text-blue-100">Saldo Final</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($movimientos as $mov)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-slate-500 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($mov->fecha_movimiento)->format('d/m/Y H:i') }}
                        </td>
                        <td class="p-4 font-bold text-slate-800">
                            {{ $mov->producto }}
                            <p class="text-[10px] text-slate-400 font-medium">ALM: {{ $mov->codigo_almacen }}</p>
                        </td>
                        <td class="p-4 text-center">
                            @php
                                $color = [
                                    'INGRESO' => 'bg-green-100 text-green-700 border-green-200',
                                    'SALIDA'  => 'bg-red-100 text-red-700 border-red-200',
                                    'AJUSTE'  => 'bg-amber-100 text-amber-700 border-amber-200',
                                ][$mov->tipo_movimiento] ?? 'bg-slate-100 text-slate-600';
                            @endphp
                            <span class="px-2 py-1 rounded text-[10px] font-black border {{ $color }}">
                                {{ $mov->tipo_movimiento }}
                            </span>
                        </td>
                        <td class="p-4">
                            <span class="text-slate-600 font-medium">{{ $mov->documento }}</span>
                            <p class="text-xs text-slate-400">{{ $mov->numero_documento }}</p>
                        </td>
                        <td class="p-4 text-right font-bold text-green-600 bg-green-50/30">
                            {{ $mov->cantidad_entrada > 0 ? number_format($mov->cantidad_entrada, 2) : '-' }}
                        </td>
                        <td class="p-4 text-right font-bold text-red-600 bg-red-50/30">
                            {{ $mov->cantidad_salida > 0 ? number_format($mov->cantidad_salida, 2) : '-' }}
                        </td>
                        <td class="p-4 text-right font-black text-blue-700 bg-blue-50/50">
                            {{ number_format($mov->cantidad_saldo, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-12 text-center text-slate-400">
                            <i class="fas fa-history text-4xl mb-3 opacity-20"></i>
                            <p class="font-semibold">No hay movimientos registrados en el historial.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($movimientos->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">
                {{ $movimientos->links() }}
            </div>
        @endif
    </div>
</div>

<style>
    @media print {
        .container { max-width: 100% !important; width: 100% !important; }
        nav, form, button, .no-print { display: none !important; }
        .shadow-sm { box-shadow: none !important; border: 1px solid #e2e8f0 !important; }
        table { font-size: 10px !important; }
    }
</style>
@endsection