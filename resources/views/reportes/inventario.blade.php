@extends('layouts.app')
@section('title', 'Reporte de Inventario')

@section('content')
<div class="container mx-auto pb-8 md:pb-10">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
        <div>
            <nav class="flex text-sm text-slate-500 mb-2">
                <a href="{{ route('reportes.index') }}" class="hover:text-primary transition-colors">Reportes</a>
                <span class="mx-2">/</span>
                <span class="text-slate-700">Inventario</span>
            </nav>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800">Reporte de Inventario</h1>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead>
                    <tr class="bg-slate-900 text-slate-200 text-xs uppercase tracking-wider font-bold">
                        <th class="p-4 border-r border-slate-800">Producto</th>
                        <th class="p-4 border-r border-slate-800">Almacén</th>
                        <th class="p-4 border-r border-slate-800 text-right">Stock</th>
                        <th class="p-4 border-r border-slate-800">Lote</th>
                        <th class="p-4 border-r border-slate-800 last:border-r-0 text-right">Costo Promedio</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($productos as $p)
                    <tr class="hover:bg-slate-50">
                        <td class="p-4">
                            <span class="font-semibold">{{ $p->producto_desc }}</span>
                            <span class="text-slate-400 ml-1">({{ $p->codigo_producto }})</span>
                        </td>
                        <td class="p-4">{{ $p->almacen_desc }}</td>
                        <td class="p-4 text-right font-bold {{ $p->stock_actual <= 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ number_format($p->stock_actual, 2) }}
                        </td>
                        <td class="p-4 text-slate-500">{{ $p->lote ?? '—' }}</td>
                        <td class="p-4 text-right">{{ number_format($p->costo_promedio, 4) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="p-8 text-center text-slate-400">No hay productos con stock disponible.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
