@extends('layouts.app')
@section('title', 'Atender Requerimiento ' . $requerimiento->codigo)

@section('content')
<div class="container mx-auto px-4 py-6 max-w-7xl">

    <x-page-header title="Atender Requerimiento {{ $requerimiento->codigo }}" subtitle="Seleccione los lotes a transferir al almacén destino">
        <x-slot:actions>
            <a href="{{ route('inventario.despachos.index') }}" class="btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Bandeja
            </a>
        </x-slot:actions>
    </x-page-header>

    @if ($errors->any())
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl">
        <h3 class="text-red-800 font-bold text-sm mb-2">Se encontraron los siguientes errores:</h3>
        <ul class="list-disc list-inside text-xs text-red-700">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if (session('error'))
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl">
        <h3 class="text-red-800 font-bold text-sm mb-2">Error</h3>
        <p class="text-xs text-red-700">{{ session('error') }}</p>
    </div>
    @endif

    <form action="{{ route('inventario.despachos.store_atender', $requerimiento->id_requerimiento) }}" method="POST" id="formAtender">
        @csrf

        @forelse($lineas as $index => $linea)
        @php $det = $linea['detalle']; @endphp
        <x-card class="mb-4">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                <h2 class="text-base font-bold text-slate-800">
                    <i class="fas fa-cube text-primary"></i>
                    {{ $det->producto->descripcion ?? $det->codigo_producto }}
                </h2>
                <div class="text-sm text-slate-500">
                    <span class="font-semibold text-slate-700">Solicitado:</span> {{ number_format($det->cantidad_solicitada, 2) }} {{ $det->producto->unidad->abreviatura ?? '' }}
                    &nbsp;|&nbsp;
                    <span class="font-semibold text-green-600">Atendido:</span> {{ number_format($det->cantidad_atendida, 2) }} {{ $det->producto->unidad->abreviatura ?? '' }}
                    &nbsp;|&nbsp;
                    <span class="font-semibold {{ $linea['saldo'] > 0 ? 'text-amber-600' : 'text-green-600' }}">Pendiente:</span> {{ number_format($linea['saldo'], 2) }} {{ $det->producto->unidad->abreviatura ?? '' }}
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm mb-4">
                    <div>
                        <span class="text-xs text-slate-500 font-semibold uppercase">Estado</span>
                        <p class="font-medium text-slate-700">Pendiente de Atención</p>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500 font-semibold uppercase">Almacén Destino</span>
                        <select class="w-full border border-slate-300 rounded-lg text-sm px-2 py-1 select-destino" data-target=".hidden-destino-{{ $index }}" required>
                            <option value="">-- Seleccione Almacén Destino --</option>
                            @foreach($almacenes as $alm)
                                <option value="{{ $alm->codigo_almacen }}" {{ $det->codigo_almacen_destino == $alm->codigo_almacen ? 'selected' : '' }}>{{ $alm->descripcion }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @if(count($linea['lotes']) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-slate-100 text-[11px] uppercase text-slate-500 tracking-wider">
                                <th class="p-2 font-semibold">Almacén Origen</th>
                                <th class="p-2 font-semibold">Lote</th>
                                <th class="p-2 font-semibold">Fecha Venc.</th>
                                <th class="p-2 font-semibold text-right">Stock Disponible ({{ $det->producto->unidad->abreviatura ?? 'U.M.' }})</th>
                                <th class="p-2 font-semibold text-center">Cantidad a Retirar ({{ $det->producto->unidad->abreviatura ?? 'U.M.' }})</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($linea['lotes'] as $lote)
                            <tr>
                                <td class="p-2 text-slate-700">{{ $lote->almacen_nombre ?? $lote->codigo_almacen }}</td>
                                <td class="p-2 font-mono text-slate-800 font-semibold">{{ $lote->lote }}</td>
                                <td class="p-2 text-slate-600">{{ $lote->fecha_vencimiento ? \Carbon\Carbon::parse($lote->fecha_vencimiento)->format('d/m/Y') : 'N/A' }}</td>
                                <td class="p-2 text-right font-medium text-slate-700">{{ number_format($lote->stock_actual, 2) }}</td>
                                <td class="p-2 text-center">
                                    <input type="number" step="0.01" min="0" max="{{ min($lote->stock_actual, $linea['saldo']) }}" class="w-28 border border-slate-300 rounded-lg text-center text-sm px-2 py-1 input-lote-cant" data-saldo="{{ $linea['saldo'] }}" data-id-detalle="{{ $det->id_detalle }}" data-lote="{{ $lote->lote }}" data-max="{{ $lote->stock_actual }}" data-index="{{ $index }}" placeholder="0.00" name="lotes[{{ $index . '_' . $loop->index }}][cantidad]">
                                    <input type="hidden" name="lotes[{{ $index . '_' . $loop->index }}][id_detalle]" value="{{ $det->id_detalle }}">
                                    <input type="hidden" name="lotes[{{ $index . '_' . $loop->index }}][lote]" value="{{ $lote->lote }}">
                                    <input type="hidden" name="lotes[{{ $index . '_' . $loop->index }}][codigo_almacen_origen]" value="{{ $lote->codigo_almacen }}">
                                    <input type="hidden" name="lotes[{{ $index . '_' . $loop->index }}][codigo_almacen_destino]" class="hidden-destino-{{ $index }}" value="{{ $det->codigo_almacen_destino }}">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-2 text-right text-xs text-slate-500">
                    <span>Total asignado esta línea: </span>
                    <span class="font-bold text-indigo-600 total-linea" id="total-linea-{{ $index }}">0.00</span>
                    <span> / {{ number_format($linea['saldo'], 2) }} {{ $det->producto->unidad->abreviatura ?? '' }}</span>
                </div>
                @else
                <p class="text-sm text-amber-600 bg-amber-50 p-3 rounded-lg">
                    <i class="fas fa-exclamation-triangle"></i> No hay stock disponible en el almacén origen para este producto.
                </p>
                @endif
            </div>
        </x-card>
        @empty
        <x-card>
            <div class="p-10 text-center text-slate-400">
                <i class="fas fa-check-circle text-4xl mb-3 text-green-400"></i>
                <p class="font-semibold">Todas las líneas han sido atendidas completamente.</p>
            </div>
        </x-card>
        @endforelse

        @if(count($lineas) > 0)
        <div class="text-center mt-6">
            <button type="submit" class="btn-primary py-3 px-8 rounded-xl font-bold text-base" onclick="return confirm('¿Confirmar el despacho de los lotes seleccionados?')">
                <i class="fas fa-check-double"></i> Confirmar Despacho
            </button>
        </div>
        @endif
    </form>
</div>

<script>
    document.querySelectorAll('.input-lote-cant').forEach(function(input) {
        input.addEventListener('input', function() {
            const max = parseFloat(this.getAttribute('data-max')) || 0;
            const saldo = parseFloat(this.getAttribute('data-saldo')) || 0;
            let val = parseFloat(this.value) || 0;
            if (val > max) val = max;

            const idDetalle = this.getAttribute('data-id-detalle');
            let totalLinea = 0;
            document.querySelectorAll(`.input-lote-cant[data-id-detalle="${idDetalle}"]`).forEach(function(inp) {
                totalLinea += parseFloat(inp.value) || 0;
            });

            if (totalLinea > saldo) {
                const exceso = totalLinea - saldo;
                val = Math.max(0, (parseFloat(this.value) || 0) - exceso);
                this.value = val.toFixed(2);
                totalLinea = 0;
                document.querySelectorAll(`.input-lote-cant[data-id-detalle="${idDetalle}"]`).forEach(function(inp) {
                    totalLinea += parseFloat(inp.value) || 0;
                });
            }

            if (val > max) {
                this.value = max.toFixed(2);
                totalLinea = 0;
                document.querySelectorAll(`.input-lote-cant[data-id-detalle="${idDetalle}"]`).forEach(function(inp) {
                    totalLinea += parseFloat(inp.value) || 0;
                });
            }
            if (val < 0) this.value = '0';

            const rowIndex = this.getAttribute('data-index');
            const totalEl = document.getElementById('total-linea-' + rowIndex);
            if (totalEl) totalEl.innerText = totalLinea.toFixed(2);
        });
    });

    document.querySelectorAll('.select-destino').forEach(function(select) {
        select.addEventListener('change', function() {
            const targetClass = this.getAttribute('data-target');
            const val = this.value;
            document.querySelectorAll(targetClass).forEach(function(hiddenInput) {
                hiddenInput.value = val;
            });
        });
        
        // Inicializar el valor on load si ya está pre-seleccionado
        if (select.value) {
            const targetClass = select.getAttribute('data-target');
            const val = select.value;
            document.querySelectorAll(targetClass).forEach(function(hiddenInput) {
                hiddenInput.value = val;
            });
        }
    });

</script>
@endsection
