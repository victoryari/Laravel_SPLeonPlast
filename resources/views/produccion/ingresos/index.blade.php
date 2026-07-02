@extends('layouts.app')
@section('title', 'Ingresos de Producción')

@section('content')
<div class="container mx-auto pb-8 md:pb-10">
    <div class="flex justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-emerald-800">Ingresos de Producción (PEP)</h1>
            <p class="text-xs sm:text-sm text-emerald-600">Apruebe los productos en proceso de producción.</p>
        </div>
    </div>

    @if (session('success'))
    <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-r-xl">
        <p class="text-sm text-green-700">{{ session('success') }}</p>
    </div>
    @endif

    @if (session('error'))
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl">
        <p class="text-sm text-red-700">{{ session('error') }}</p>
    </div>
    @endif

    @if ($errors->any())
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl">
        <ul class="list-disc list-inside text-sm text-red-700">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 space-y-4 bg-gray-50">
            @forelse($produccionPendientesAgrupada as $idop => $productos)
                @php
                    $totalLotesOP = 0;
                    foreach($productos as $lotes) { $totalLotesOP += $lotes->count(); }
                @endphp
                <!-- Acordeón Nivel OP -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden">
                    <div class="px-6 py-5 cursor-pointer border-l-4 border-slate-800 flex flex-col lg:flex-row justify-between gap-4 bg-slate-50"
                         onclick="toggleAccordion('op-{{ $idop }}')">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-slate-800 text-white flex items-center justify-center font-bold">
                                OP
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 text-lg uppercase">Orden de Producción #{{ $idop }}</h3>
                                <p class="text-sm text-slate-500 mt-1">
                                    <span class="font-semibold text-slate-700">{{ $productos->count() }}</span> productos distintos &bull; <span class="font-semibold text-slate-700">{{ $totalLotesOP }}</span> lotes pendientes
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-6">
                            <div class="w-10 h-10 rounded-xl border border-slate-300 flex items-center justify-center bg-white">
                                <i class="fas fa-chevron-down transition-transform duration-300" id="icon-op-{{ $idop }}"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Contenido OP -->
                    <div id="op-{{ $idop }}" class="hidden border-t bg-white p-4 lg:p-6 space-y-4">
                        @foreach($productos as $codigoProducto => $lotes)
                            @php
                                $primerLote = $lotes->first();
                            @endphp
                            <!-- Acordeón Nivel Producto/Color -->
                            <div class="bg-emerald-50/50 rounded-xl border border-emerald-100 overflow-hidden">
                                <div class="px-5 py-4 cursor-pointer flex flex-col lg:flex-row justify-between gap-4"
                                     onclick="toggleAccordion('prod-{{ $idop }}-{{ Str::slug($codigoProducto) }}')">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                            <i class="fas fa-tint"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-slate-800 text-sm uppercase">{{ $primerLote->descripcion_producto_proceso }}</h4>
                                            <p class="text-xs text-slate-500 mt-0.5">Código: {{ $codigoProducto }} | {{ $lotes->count() }} lotes pendientes</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <!-- Botón de Aprobación Global -->
                                        <form action="{{ route('inventario.procesar_recepcion_produccion_global', ['idop' => $idop, 'codigo_producto' => $codigoProducto]) }}" method="POST" class="flex items-center gap-2" onsubmit="event.stopPropagation();">
                                            @csrf
                                            <select name="codigo_almacen" class="px-2 py-1 border border-emerald-200 rounded-lg text-xs text-emerald-800 bg-white outline-none focus:border-emerald-500 max-w-[150px] truncate" onclick="event.stopPropagation();" required>
                                                <option value="{{ $primerLote->codigo_almacen }}">{{ $primerLote->almacen->descripcion ?? 'ALM-PEP' }}</option>
                                                @foreach($almacenes as $a)
                                                    @if($a->codigo_almacen != $primerLote->codigo_almacen)
                                                        <option value="{{ $a->codigo_almacen }}">{{ $a->descripcion }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            <input type="date" name="fecha_recepcion" value="{{ \Carbon\Carbon::parse($primerLote->fecha_ingreso)->format('Y-m-d') }}" class="px-2 py-1 border border-emerald-200 rounded-lg text-xs text-emerald-800 bg-white outline-none focus:border-emerald-500 max-w-[120px]" title="Fecha de Recepción (Kardex)" required onclick="event.stopPropagation();">
                                            <button type="submit" onclick="return confirm('¿Está seguro de aprobar TODOS los {{ $lotes->count() }} lotes de este color al almacén seleccionado?');" 
                                                class="px-4 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm flex items-center gap-2">
                                                <i class="fas fa-check-double"></i> Aprobar Todo
                                            </button>
                                        </form>
                                        <div class="w-8 h-8 rounded-lg bg-white border border-emerald-200 flex items-center justify-center">
                                            <i class="fas fa-chevron-down text-xs text-emerald-600 transition-transform duration-300" id="icon-prod-{{ $idop }}-{{ Str::slug($codigoProducto) }}"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contenido Producto/Color (Lotes individuales) -->
                                <div id="prod-{{ $idop }}-{{ Str::slug($codigoProducto) }}" class="hidden border-t border-emerald-100 bg-white p-4">
                                    <div class="space-y-4">
                                        @foreach($lotes as $pep)
                                            <div class="bg-white rounded-xl border border-slate-200 shadow-xs hover:border-emerald-300 transition-colors overflow-hidden">
                                                <div class="px-4 py-3 bg-slate-50 border-b flex justify-between items-center cursor-pointer"
                                                     onclick="toggleAccordion('pep-{{ $pep->id_ingreso }}')">
                                                    <div class="flex items-center gap-3">
                                                        <i class="fas fa-box text-slate-400 text-sm"></i>
                                                        <div>
                                                            <p class="text-xs font-bold text-slate-700">Lote: {{ $pep->lote_produccion }}</p>
                                                            <p class="text-[10px] text-slate-500">Fecha: {{ \Carbon\Carbon::parse($pep->fecha_ingreso)->format('d/m/Y H:i') }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center gap-3">
                                                        <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded">
                                                            {{ number_format($pep->cantidad, 2) }} {{ $pep->codigo_unidad_medida }}
                                                        </span>
                                                        <i class="fas fa-chevron-down text-xs text-slate-400 transition-transform duration-300" id="icon-pep-{{ $pep->id_ingreso }}"></i>
                                                    </div>
                                                </div>
                                                
                                                <div id="pep-{{ $pep->id_ingreso }}" class="hidden p-4">
                                                    <form action="{{ route('inventario.procesar_recepcion_produccion', $pep->id_ingreso) }}" method="POST">
                                                        @csrf
                                                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                                                            <div class="md:col-span-4">
                                                                <label class="block text-xs font-semibold text-slate-500 mb-1">Almacén destino</label>
                                                                <div class="relative">
                                                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                                        <i class="fas fa-warehouse text-slate-400"></i>
                                                                    </div>
                                                                    <select name="codigo_almacen" class="w-full pl-10 pr-8 py-2 rounded-lg border border-slate-300 bg-white text-sm focus:ring-2 focus:ring-emerald-500" required>
                                                                        <option value="">Seleccione...</option>
                                                                        @foreach($almacenes as $almacen)
                                                                            <option value="{{ $almacen->codigo_almacen }}" {{ $pep->codigo_almacen == $almacen->codigo_almacen ? 'selected' : '' }}>
                                                                                {{ $almacen->descripcion }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="md:col-span-4">
                                                                <label class="block text-xs font-semibold text-emerald-600 mb-1">Cant. Ingresar</label>
                                                                <div class="flex items-center">
                                                                    <input type="number" name="cantidad_real" value="{{ $pep->cantidad }}" step="0.01" class="w-full rounded-l-lg border-2 border-emerald-200 bg-emerald-50 text-center font-bold text-emerald-700 py-1.5 focus:ring-4 focus:ring-emerald-100" required>
                                                                    <span class="bg-emerald-100 border-2 border-l-0 border-emerald-200 text-emerald-700 rounded-r-lg px-3 py-1.5 font-bold text-sm">{{ $pep->codigo_unidad_medida }}</span>
                                                                </div>
                                                            </div>
                                                            <div class="md:col-span-3">
                                                                <label class="block text-xs font-semibold text-slate-500 mb-1">Fecha de Recepción</label>
                                                                <div class="relative">
                                                                    <input type="date" name="fecha_recepcion" value="{{ \Carbon\Carbon::parse($pep->fecha_ingreso)->format('Y-m-d') }}" class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-sm focus:ring-2 focus:ring-emerald-500" required>
                                                                </div>
                                                            </div>
                                                            <div class="md:col-span-2 flex justify-end gap-2 items-end">
                                                                <button type="submit" class="w-full py-2 rounded-lg bg-linear-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold shadow text-sm">
                                                                    <i class="fas fa-check mr-1"></i> Aprobar
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
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
// Manejo de Acordeones
function toggleAccordion(id) {
    const content = document.getElementById(id);
    const icon = document.getElementById('icon-' + id);

    // Si es un OP principal, cerramos los del mismo nivel.
    if (id.startsWith('op-')) {
        document.querySelectorAll('[id^="op-"]').forEach(el => {
            if (el.id !== id && !el.classList.contains('hidden')) {
                el.classList.add('hidden');
                const otherIcon = document.getElementById('icon-' + el.id);
                if (otherIcon) otherIcon.classList.remove('rotate-180');
            }
        });
    }

    content.classList.toggle('hidden');
    if (icon) icon.classList.toggle('rotate-180');
}
</script>
@endsection
