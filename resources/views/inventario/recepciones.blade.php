@extends('layouts.app')
@section('title', 'Recepciones de Almacén')

@section('content')
<div class="container mx-auto pb-8 md:pb-10">
    <div class="flex justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Registro de Recepciones</h1>
            <p class="text-xs sm:text-sm text-gray-600">Control de inventario y movimientos de almacén</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 md:p-4 mb-6 rounded shadow-sm text-sm md:text-base">
            <p><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 md:p-4 mb-6 rounded shadow-sm text-sm md:text-base">
            <p><i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}</p>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b bg-slate-50 flex flex-col sm:flex-row justify-between gap-4 items-center">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Mercadería en Tránsito (Compras)</h2>
                <p class="text-sm text-slate-500">Confirme el almacén destino y cantidades de proveedores.</p>
            </div>
            <div class="inline-flex items-center gap-2 bg-primary-100 text-primary px-3 py-1 rounded-lg text-sm font-semibold">
                <i class="fas fa-truck text-primary"></i>
                {{ $comprasPendientes->count() }} pendientes
            </div>
        </div>

        <div class="p-6 space-y-4 bg-gray-50">

            @forelse($comprasPendientes as $compra)
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden">

                    <div class="px-6 py-5 cursor-pointer border-l-4 border-amber-400 flex flex-col lg:flex-row justify-between gap-4"
                         onclick="toggleAccordion('compra-{{ $compra->id_compra }}')">

                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                                <i class="fas fa-box-open text-xl"></i>
                            </div>

                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-slate-800 text-sm uppercase">
                                        {{ $compra->tipo_documento }} {{ $compra->serie_documento }}-{{ $compra->numero_documento }}
                                    </h3>

                                    <x-badge color="amber">Pendiente</x-badge>
                                </div>

                                <p class="text-sm text-slate-500 mt-1">
                                    {{ $compra->datosProveedor->razon_social ?? $compra->proveedor }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-6">
                            <div class="text-right">
                                <p class="text-xs uppercase text-slate-400">Fecha</p>
                                <p class="font-bold text-slate-700">
                                    {{ \Carbon\Carbon::parse($compra->fecha_compra)->format('d/m/Y') }}
                                </p>
                            </div>

                            <div class="w-10 h-10 rounded-xl border border-slate-200 flex items-center justify-center">
                                <i class="fas fa-chevron-down transition-transform duration-300" id="icon-compra-{{ $compra->id_compra }}"></i>
                            </div>
                        </div>
                    </div>

                    <div id="compra-{{ $compra->id_compra }}" class="hidden border-t bg-slate-50">
                        <form action="{{ route('inventario.procesar_recepcion', $compra->id_compra) }}" method="POST" class="p-6">
                            @csrf

                            <div class="space-y-4">
                                @foreach($compra->detalles as $detalle)
                                    <div class="bg-white border border-slate-200 rounded-xl p-4 grid grid-cols-1 lg:grid-cols-12 gap-4 items-center">

                                        <div class="lg:col-span-4">
                                            <p class="font-semibold text-slate-800">
                                                {{ $detalle->producto->descripcion ?? $detalle->descripcion_producto }}
                                            </p>
                                            <p class="text-xs text-slate-400">
                                                Código: {{ $detalle->codigo_producto }}
                                            </p>
                                        </div>

                                        <div class="lg:col-span-3">
                                            <label class="block text-xs font-semibold text-slate-500 mb-1">Almacén destino</label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <i class="fas fa-warehouse text-slate-400"></i>
                                                </div>

                                                <select name="items[{{ $detalle->id_detalle_compra }}][codigo_almacen]"
                                                        class="w-full pl-10 pr-8 py-2.5 rounded-xl border border-slate-300 bg-white text-sm focus:ring-2 focus:ring-primary focus:border-primary"
                                                        required>
                                                    <option value="">Seleccione...</option>
                                                    @foreach($almacenes as $almacen)
                                                        <option value="{{ $almacen->codigo_almacen }}"
                                                            {{ $detalle->codigo_almacen == $almacen->codigo_almacen ? 'selected' : '' }}>
                                                            {{ $almacen->descripcion }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                    <i class="fas fa-chevron-down text-slate-400 text-xs"></i>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="lg:col-span-2">
                                            <label class="block text-xs font-semibold text-slate-500 mb-1">Facturado</label>
                                            <div class="bg-slate-100 rounded-xl px-4 py-2 text-center font-bold text-slate-700">
                                                {{ number_format($detalle->cantidad, 2) }}
                                            </div>
                                        </div>

                                        <div class="lg:col-span-3">
                                            <label class="block text-xs font-semibold text-primary mb-1">Recibido físico</label>
                                            <input type="number"
                                                   name="items[{{ $detalle->id_detalle_compra }}][cantidad]"
                                                   value="{{ $detalle->cantidad }}"
                                                   step="0.01"
                                                   class="w-full rounded-xl border-2 border-primary/20 bg-primary-50 text-center text-lg font-bold text-primary py-2 focus:ring-4 focus:ring-primary/20"
                                                   required>

                                            <input type="hidden" name="items[{{ $detalle->id_detalle_compra }}][codigo_producto]" value="{{ $detalle->codigo_producto }}">
                                            <input type="hidden" name="items[{{ $detalle->id_detalle_compra }}][precio]" value="{{ $detalle->precio_unitario }}">
                                        </div>

                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-6 flex flex-col sm:flex-row justify-end gap-3">
                                <button type="button"
                                        onclick="toggleAccordion('compra-{{ $compra->id_compra }}')"
                                        class="px-6 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-600 hover:bg-slate-50 font-semibold">
                                    Cancelar
                                </button>

                                <button type="submit"
                                        class="px-6 py-2.5 rounded-xl bg-linear-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold shadow-lg">
                                    <i class="fas fa-check mr-2"></i>
                                    Ingresar al Kardex
                                </button>
                            </div>

                        </form>
                    </div>

                </div>
            @empty
                <div class="text-center py-16">
                    <i class="fas fa-clipboard-check text-5xl text-slate-300 mb-4"></i>
                    <h3 class="text-lg font-bold text-slate-700">Inventario al día</h3>
                    <p class="text-slate-500">No existen órdenes pendientes de recepción.</p>
                </div>
            @endforelse

        </div>
    </div>

    <!-- SECCIÓN DE PRODUCCIÓN (PEP) -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b bg-emerald-50 flex flex-col sm:flex-row justify-between gap-4 items-center">
            <div>
                <h2 class="text-lg font-bold text-emerald-800">Ingresos de Producción (PEP)</h2>
                <p class="text-sm text-emerald-600">Apruebe los productos en proceso de producción.</p>
            </div>
            <div class="inline-flex items-center gap-2 bg-emerald-100 text-emerald-800 px-3 py-1 rounded-lg text-sm font-semibold">
                <i class="fas fa-industry text-emerald-600"></i>
                {{ $produccionPendientes->count() }} pendientes
            </div>
        </div>

        <div class="p-6 space-y-4 bg-gray-50">
            @forelse($produccionPendientes as $pep)
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden">

                    <div class="px-6 py-5 cursor-pointer border-l-4 border-emerald-400 flex flex-col lg:flex-row justify-between gap-4"
                         onclick="toggleAccordion('pep-{{ $pep->id_ingreso }}')">

                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                <i class="fas fa-cogs text-xl"></i>
                            </div>

                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-slate-800 text-sm uppercase">
                                        {{ $pep->descripcion_producto_proceso }}
                                    </h3>
                                    <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-semibold">
                                        Pendiente
                                    </span>
                                </div>
                                <p class="text-sm text-slate-500 mt-1">
                                    Código: {{ $pep->codigo_producto_proceso }} | Lote: {{ $pep->lote_produccion }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-6">
                            <div class="text-right">
                                <p class="text-xs uppercase text-slate-400">Fecha / OP</p>
                                <p class="font-bold text-slate-700">
                                    {{ \Carbon\Carbon::parse($pep->fecha_ingreso)->format('d/m/Y') }} | OP #{{ $pep->idop }}
                                </p>
                            </div>

                            <div class="w-10 h-10 rounded-xl border border-slate-200 flex items-center justify-center">
                                <i class="fas fa-chevron-down transition-transform duration-300" id="icon-pep-{{ $pep->id_ingreso }}"></i>
                            </div>
                        </div>
                    </div>

                    <div id="pep-{{ $pep->id_ingreso }}" class="hidden border-t bg-slate-50">
                        <form action="{{ route('inventario.procesar_recepcion_produccion', $pep->id_ingreso) }}" method="POST" class="p-6">
                            @csrf

                            <div class="bg-white border border-slate-200 rounded-xl p-4 grid grid-cols-1 lg:grid-cols-12 gap-4 items-center">
                                <div class="lg:col-span-5">
                                    <label class="block text-xs font-semibold text-slate-500 mb-1">Almacén destino</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-warehouse text-slate-400"></i>
                                        </div>
                                        <select name="codigo_almacen" class="w-full pl-10 pr-8 py-2.5 rounded-xl border border-slate-300 bg-white text-sm focus:ring-2 focus:ring-emerald-500" required>
                                            <option value="">Seleccione...</option>
                                            @foreach($almacenes as $almacen)
                                                <option value="{{ $almacen->codigo_almacen }}" {{ $pep->codigo_almacen == $almacen->codigo_almacen ? 'selected' : '' }}>
                                                    {{ $almacen->descripcion }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <i class="fas fa-chevron-down text-slate-400 text-xs"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="lg:col-span-3">
                                    <label class="block text-xs font-semibold text-slate-500 mb-1">Reportado (Báscula Prod)</label>
                                    <div class="bg-slate-100 rounded-xl px-4 py-2 text-center font-bold text-slate-700">
                                        {{ number_format($pep->cantidad, 2) }} {{ $pep->codigo_unidad_medida }}
                                    </div>
                                </div>

                                <div class="lg:col-span-4">
                                    <label class="block text-xs font-semibold text-emerald-600 mb-1">Cantidad Real a Ingresar</label>
                                    <div class="flex items-center">
                                        <input type="number" name="cantidad_real" value="{{ $pep->cantidad }}" step="0.01" class="w-full rounded-l-xl border-2 border-emerald-200 bg-emerald-50 text-center text-lg font-bold text-emerald-700 py-2 focus:ring-4 focus:ring-emerald-100" required>
                                        <span class="bg-emerald-100 border-2 border-l-0 border-emerald-200 text-emerald-700 rounded-r-xl px-4 py-2 font-bold">{{ $pep->codigo_unidad_medida }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex flex-col sm:flex-row justify-end gap-3">
                                <button type="button" onclick="toggleAccordion('pep-{{ $pep->id_ingreso }}')" class="px-6 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-600 hover:bg-slate-50 font-semibold">
                                    Cancelar
                                </button>

                                <button type="submit" class="px-6 py-2.5 rounded-xl bg-linear-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold shadow-lg">
                                    <i class="fas fa-check mr-2"></i>
                                    Aprobar e Ingresar al Kardex
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            @empty
                <div class="text-center py-16">
                    <i class="fas fa-industry text-5xl text-slate-300 mb-4"></i>
                    <h3 class="text-lg font-bold text-slate-700">Producción al día</h3>
                    <p class="text-slate-500">No hay ingresos de producción pendientes de aprobación.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
function toggleAccordion(id) {
    const content = document.getElementById(id);
    const icon = document.getElementById('icon-' + id);

    document.querySelectorAll('[id^="compra-"], [id^="pep-"]').forEach(el => {
        if (el.id !== id && !el.classList.contains('hidden')) {
            el.classList.add('hidden');
            const otherIcon = document.getElementById('icon-' + el.id);
            if (otherIcon) otherIcon.classList.remove('rotate-180');
        }
    });

    content.classList.toggle('hidden');
    if (icon) icon.classList.toggle('rotate-180');
}
</script>
@endsection