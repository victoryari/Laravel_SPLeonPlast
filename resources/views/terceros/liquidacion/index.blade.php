@extends('layouts.app')
@section('title', 'Liquidación de Terceros')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-7xl">
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight">Liquidación y Control de Terceros</h1>
        <p class="text-sm text-slate-500 mt-1">Control de saldos pendientes y liquidación de mermas de servicios externos.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[11px] font-semibold">
                        <th class="p-4 border-b border-slate-200">Proveedor</th>
                        <th class="p-4 border-b border-slate-200">Guía Envío</th>
                        <th class="p-4 border-b border-slate-200">Fecha</th>
                        <th class="p-4 border-b border-slate-200">Producto</th>
                        <th class="p-4 border-b border-slate-200 text-right">Enviado (KG)</th>
                        <th class="p-4 border-b border-slate-200 text-right">Devuelto (KG)</th>
                        <th class="p-4 border-b border-slate-200 text-right">Saldo / Merma</th>
                        <th class="p-4 border-b border-slate-200 text-center">Estado</th>
                        <th class="p-4 border-b border-slate-200 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($detalles as $det)
                        @php
                            $saldo = $det->cantidad_enviada - $det->cantidad_devuelta - $det->cantidad_merma;
                        @endphp
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 font-semibold text-primary">
                            {{ Str::limit($det->proveedor_destino, 20) }}
                        </td>
                        <td class="p-4 font-bold text-slate-800">
                            {{ $det->numero_guia }}
                        </td>
                        <td class="p-4 whitespace-nowrap text-slate-500 font-medium">
                            {{ date('d/m/Y', strtotime($det->fecha_emision)) }}
                        </td>
                        <td class="p-4 text-slate-600">
                            {{ $det->codigo_producto }}
                        </td>
                        <td class="p-4 text-right text-slate-600">
                            {{ number_format($det->cantidad_enviada, 2) }}
                        </td>
                        <td class="p-4 text-right text-slate-600">
                            {{ number_format($det->cantidad_devuelta, 2) }}
                        </td>
                        <td class="p-4 text-right font-bold">
                            @if($det->estado_detalle == 'CERRADO_CON_MERMA')
                                <span class="text-red-500" title="Mermado">
                                    {{ number_format($det->cantidad_merma, 2) }}
                                </span>
                            @else
                                <span class="text-slate-800">
                                    {{ number_format($saldo, 2) }}
                                </span>
                            @endif
                        </td>
                        <td class="p-4 text-center">
                            @php
                                $badgeColor = [
                                    'PENDIENTE' => 'yellow',
                                    'PARCIAL' => 'blue',
                                    'COMPLETADO' => 'green',
                                    'CERRADO_CON_MERMA' => 'slate'
                                ][$det->estado_detalle] ?? 'slate';
                            @endphp
                            <x-badge color="{{ $badgeColor }}">
                                {{ $det->estado_detalle == 'CERRADO_CON_MERMA' ? 'MERMA' : $det->estado_detalle }}
                            </x-badge>
                        </td>
                        <td class="p-4 text-center">
                            @if(in_array($det->estado_detalle, ['PENDIENTE', 'PARCIAL']) && $saldo > 0)
                                <button type="button" onclick="liquidarMerma({{ $det->id_detalle_salida }})" class="inline-flex items-center px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-xs font-semibold transition">
                                    <i class="fas fa-balance-scale-right mr-1.5"></i>A Merma
                                </button>
                            @else
                                <span class="text-slate-300">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="p-12 text-center text-slate-400">
                            <i class="fas fa-balance-scale text-4xl mb-3 opacity-30"></i>
                            <p class="font-semibold">No hay registros de liquidación pendientes.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($detalles->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50">
            {{ $detalles->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function liquidarMerma(id) {
    if (confirm('¿Está seguro de cerrar este saldo pendiente como MERMA? Esta acción no generará más retornos de esta guía.')) {
        fetch(`{{ url('terceros/liquidacion') }}/${id}/cerrar`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message || 'Error al procesar.');
            }
        })
        .catch(error => {
            alert('Error de red.');
        });
    }
}
</script>
@endpush
@endsection
