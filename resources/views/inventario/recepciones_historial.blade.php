@extends('layouts.app')
@section('title', 'Historial de Recepciones')

@section('content')
<div class="container mx-auto pb-8 md:pb-10">
    <div class="flex justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Historial de Recepciones</h1>
            <p class="text-xs sm:text-sm text-gray-600">Compras recibidas en almacén</p>
        </div>
        <a href="{{ route('inventario.recepciones') }}"
           class="px-4 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-sm font-semibold shadow transition">
            <i class="fas fa-arrow-left mr-1"></i> Recepciones Pendientes
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="bg-slate-800 text-slate-300 uppercase tracking-wider font-semibold">
                        <th class="p-3">Documento</th>
                        <th class="p-3">Proveedor</th>
                        <th class="p-3">Fecha Compra</th>
                        <th class="p-3">Total</th>
                        <th class="p-3">Moneda</th>
                        <th class="p-3 text-center">Items</th>
                        <th class="p-3 text-center">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recepciones as $compra)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-3 font-semibold text-slate-800">
                            {{ $compra->tipo_documento }} {{ $compra->serie_documento }}-{{ $compra->numero_documento }}
                        </td>
                        <td class="p-3 text-slate-600">
                            {{ $compra->datosProveedor->razon_social ?? $compra->proveedor }}
                        </td>
                        <td class="p-3 text-slate-500 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($compra->fecha_compra)->format('d/m/Y') }}
                        </td>
                        <td class="p-3 font-bold text-slate-700">
                            S/ {{ number_format($compra->total, 2) }}
                        </td>
                        <td class="p-3">
                            <x-badge color="{{ $compra->moneda === 'USD' ? 'blue' : 'green' }}">
                                {{ $compra->moneda }}
                            </x-badge>
                        </td>
                        <td class="p-3 text-center font-semibold text-slate-600">
                            {{ $compra->detalles->count() }}
                        </td>
                        <td class="p-3 text-center">
                            <button onclick="toggleDetalle('detalle-{{ $compra->id_compra }}')"
                                    class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition">
                                <i class="fas fa-eye mr-1"></i> Ver detalle
                            </button>
                        </td>
                    </tr>
                    <tr id="detalle-{{ $compra->id_compra }}" class="hidden bg-slate-50">
                        <td colspan="7" class="p-4">
                            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                                <table class="w-full text-xs">
                                    <thead>
                                        <tr class="bg-slate-100 text-slate-500 uppercase tracking-wider font-semibold">
                                            <th class="p-2 text-left">Producto</th>
                                            <th class="p-2 text-left">Almacén</th>
                                            <th class="p-2 text-right">Cantidad</th>
                                            <th class="p-2 text-right">P. Unit.</th>
                                            <th class="p-2 text-right">Total</th>
                                            <th class="p-2 text-left">Lote</th>
                                            <th class="p-2 text-left">Vencimiento</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($compra->detalles as $det)
                                        <tr class="hover:bg-white transition-colors">
                                            <td class="p-2 font-semibold text-slate-700">
                                                {{ $det->producto->descripcion ?? $det->descripcion_producto }}
                                                <p class="text-[10px] text-slate-400">{{ $det->codigo_producto }}</p>
                                            </td>
                                            <td class="p-2 text-slate-500">{{ $det->codigo_almacen }}</td>
                                            <td class="p-2 text-right font-semibold">{{ number_format($det->cantidad, 2) }}</td>
                                            <td class="p-2 text-right">S/ {{ number_format($det->precio_unitario, 4) }}</td>
                                            <td class="p-2 text-right font-bold">S/ {{ number_format($det->total, 2) }}</td>
                                            <td class="p-2 text-slate-500">{{ $det->lote ?? '-' }}</td>
                                            <td class="p-2 text-slate-500">{{ $det->fecha_vencimiento ? \Carbon\Carbon::parse($det->fecha_vencimiento)->format('d/m/Y') : '-' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-12 text-center text-slate-400">
                            <i class="fas fa-history text-4xl mb-3 opacity-20"></i>
                            <p class="font-semibold">No hay recepciones registradas.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($recepciones->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">
                {{ $recepciones->links() }}
            </div>
        @endif
    </div>
</div>

<script>
function toggleDetalle(id) {
    const row = document.getElementById(id);
    row.classList.toggle('hidden');
}
</script>
@endsection