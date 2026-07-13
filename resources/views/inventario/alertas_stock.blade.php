@extends('layouts.app')
@section('title', 'Alertas de Stock')

@section('content')
<div class="container mx-auto pb-8 md:pb-10">
    <div class="flex justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800">Alertas de Stock</h1>
            <p class="text-xs sm:text-sm text-slate-600">Productos con stock por debajo del mínimo configurado</p>
        </div>
        <a href="{{ route('inventario.index') }}"
           class="px-4 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-sm font-semibold shadow transition">
            <i class="fas fa-warehouse mr-1"></i> Ver Existencias
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="bg-red-600 text-white uppercase tracking-wider font-semibold">
                        <th class="p-3">Producto</th>
                        <th class="p-3">Almacén</th>
                        <th class="p-3 text-right">Stock Actual</th>
                        <th class="p-3 text-right">Stock Mínimo</th>
                        <th class="p-3 text-right">Stock Máximo</th>
                        <th class="p-3 text-right">Déficit</th>
                        <th class="p-3 text-center">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($alertas as $item)
                    @php
                        $deficit = $item->stock_minimo - $item->stock_actual;
                        $porcentaje = $item->stock_minimo > 0
                            ? round(($item->stock_actual / $item->stock_minimo) * 100, 1)
                            : 0;
                    @endphp
                    <tr class="hover:bg-red-50 transition-colors">
                        <td class="p-3">
                            <p class="font-semibold text-slate-800">{{ $item->producto }}</p>
                            <p class="text-xs text-slate-400">{{ $item->codigo_producto }}</p>
                        </td>
                        <td class="p-3">
                            <span class="inline-flex px-2 py-1 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold">
                                {{ $item->almacen }}
                            </span>
                        </td>
                        <td class="p-3 text-right font-bold text-red-600 text-base">
                            {{ number_format($item->stock_actual, 2) }}
                        </td>
                        <td class="p-3 text-right text-slate-500">
                            {{ number_format($item->stock_minimo, 2) }}
                        </td>
                        <td class="p-3 text-right text-slate-500">
                            {{ $item->stock_maximo ? number_format($item->stock_maximo, 2) : '-' }}
                        </td>
                        <td class="p-3 text-right font-semibold text-red-500">
                            -{{ number_format($deficit, 2) }}
                        </td>
                        <td class="p-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <div class="w-24 bg-slate-200 rounded-full h-2.5 overflow-hidden">
                                    <div class="bg-red-500 h-full rounded-full" style="width: {{ min($porcentaje, 100) }}%"></div>
                                </div>
                                <span class="text-xs font-semibold text-red-600">{{ $porcentaje }}%</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-12 text-center text-slate-400">
                            <i class="fas fa-check-circle text-5xl text-green-300 mb-4"></i>
                            <p class="text-lg font-semibold text-slate-600">Inventario en niveles óptimos</p>
                            <p class="text-sm text-slate-400">No hay productos por debajo del stock mínimo.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($alertas->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">
                {{ $alertas->links() }}
            </div>
        @endif
    </div>
</div>
@endsection