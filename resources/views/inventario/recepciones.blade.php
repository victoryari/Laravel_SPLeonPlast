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

    <!-- PANEL DE TARJETAS (DASHBOARD) -->
    <div id="cards-panel" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        
        <!-- Tarjeta Compras -->
        <div onclick="showSection('section-compras')" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 cursor-pointer hover:shadow-lg hover:border-primary transition-all group flex flex-col items-center text-center">
            <div class="w-16 h-16 rounded-full bg-primary-50 text-primary flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <i class="fas fa-shopping-cart text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-2">Mercadería en Tránsito</h3>
            <p class="text-sm text-slate-500 mb-4">Recepciones de facturas de compra</p>
            <div class="mt-auto">
                <span class="inline-flex items-center gap-2 bg-primary-100 text-primary px-4 py-1.5 rounded-full text-sm font-bold">
                    {{ $comprasPendientes->count() }} pendientes
                </span>
            </div>
        </div>

        <!-- Tarjeta Guias -->
        <div onclick="showSection('section-guias')" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 cursor-pointer hover:shadow-lg hover:border-blue-500 transition-all group flex flex-col items-center text-center">
            <div class="w-16 h-16 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <i class="fas fa-truck text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-2">Guías de Remisión</h3>
            <p class="text-sm text-slate-500 mb-4">Transferencias desde almacén transitorio</p>
            <div class="mt-auto">
                <span class="inline-flex items-center gap-2 bg-blue-100 text-blue-800 px-4 py-1.5 rounded-full text-sm font-bold">
                    {{ $guiasPendientes->count() }} por ubicar
                </span>
            </div>
        </div>

        <!-- Tarjeta Produccion -->
        <div onclick="showSection('section-produccion')" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 cursor-pointer hover:shadow-lg hover:border-emerald-500 transition-all group flex flex-col items-center text-center">
            <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <i class="fas fa-industry text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-2">Ingresos de Producción</h3>
            <p class="text-sm text-slate-500 mb-4">Aprobación de productos terminados/proceso</p>
            <div class="mt-auto">
                <span class="inline-flex items-center gap-2 bg-emerald-100 text-emerald-800 px-4 py-1.5 rounded-full text-sm font-bold">
                    {{ $produccionPendientes->count() }} pendientes
                </span>
            </div>
        </div>
    </div>

    <!-- SECCIÓN DE COMPRAS -->
    <div id="section-compras" class="hidden">
        <button onclick="showCards()" class="mb-4 inline-flex items-center gap-2 text-slate-500 hover:text-slate-800 font-medium transition-colors">
            <i class="fas fa-arrow-left"></i> Volver a opciones
        </button>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b bg-slate-50 flex flex-col sm:flex-row justify-between gap-4 items-center">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Mercadería en Tránsito (Compras)</h2>
                    <p class="text-sm text-slate-500">Confirme el almacén destino y cantidades de proveedores.</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('inventario.recepciones.historial') }}"
                       class="inline-flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 px-3 py-1.5 rounded-lg text-sm font-semibold transition">
                        <i class="fas fa-history"></i> Historial
                    </a>
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
                                            <div class="lg:col-span-3">
                                                <p class="font-semibold text-slate-800 text-sm">
                                                    {{ $detalle->producto->descripcion ?? $detalle->descripcion_producto }}
                                                </p>
                                                <p class="text-xs text-slate-400">
                                                    Código: {{ $detalle->codigo_producto }}
                                                </p>
                                            </div>

                                            <div class="lg:col-span-2">
                                                <label class="block text-xs font-semibold text-slate-500 mb-1">Almacén</label>
                                                <select name="items[{{ $detalle->id_detalle_compra }}][codigo_almacen]" class="w-full rounded-xl border border-slate-300 px-2 py-2.5 text-sm focus:ring-2 focus:ring-primary" required>
                                                    <option value="">Seleccione...</option>
                                                    @foreach($almacenes as $almacen)
                                                        <option value="{{ $almacen->codigo_almacen }}" {{ $detalle->codigo_almacen == $almacen->codigo_almacen ? 'selected' : '' }}>
                                                            {{ $almacen->descripcion }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="lg:col-span-1">
                                                <label class="block text-xs font-semibold text-slate-500 mb-1">Fact.</label>
                                                <div class="bg-slate-100 rounded-xl px-2 py-2 text-center font-bold text-slate-700 text-sm">
                                                    {{ number_format($detalle->cantidad, 2) }}
                                                </div>
                                            </div>

                                            <div class="lg:col-span-2">
                                                <label class="block text-xs font-semibold text-primary mb-1">Recibido</label>
                                                <input type="number" name="items[{{ $detalle->id_detalle_compra }}][cantidad]" value="{{ $detalle->cantidad }}" step="0.01" class="w-full rounded-xl border-2 border-primary/20 bg-primary-50 text-center font-bold text-primary py-2 focus:ring-4 focus:ring-primary/20" required>
                                                <input type="hidden" name="items[{{ $detalle->id_detalle_compra }}][codigo_producto]" value="{{ $detalle->codigo_producto }}">
                                                <input type="hidden" name="items[{{ $detalle->id_detalle_compra }}][precio]" value="{{ $detalle->precio_unitario }}">
                                                <input type="hidden" name="items[{{ $detalle->id_detalle_compra }}][codigo_unidad_medida]" value="{{ $detalle->codigo_unidad_medida }}">
                                            </div>

                                            <div class="lg:col-span-2">
                                                <label class="block text-xs font-semibold text-slate-500 mb-1">Lote</label>
                                                <input type="text" name="items[{{ $detalle->id_detalle_compra }}][lote]" value="{{ $detalle->lote }}" placeholder="Obligatorio" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary" required>
                                            </div>

                                            <div class="lg:col-span-2">
                                                <label class="block text-xs font-semibold text-slate-500 mb-1">Vencimiento</label>
                                                <input type="date" name="items[{{ $detalle->id_detalle_compra }}][fecha_vencimiento]" value="{{ $detalle->fecha_vencimiento }}" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-6 flex flex-col sm:flex-row justify-end gap-3">
                                    <button type="button" onclick="toggleAccordion('compra-{{ $compra->id_compra }}')" class="px-6 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-600 hover:bg-slate-50 font-semibold">Cancelar</button>
                                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-linear-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold shadow-lg">
                                        <i class="fas fa-check mr-2"></i> Ingresar al Kardex
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
    </div>

    <!-- SECCIÓN DE GUÍAS -->
    <div id="section-guias" class="hidden">
        <button onclick="showCards()" class="mb-4 inline-flex items-center gap-2 text-slate-500 hover:text-slate-800 font-medium transition-colors">
            <i class="fas fa-arrow-left"></i> Volver a opciones
        </button>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b bg-blue-50 flex flex-col sm:flex-row justify-between gap-4 items-center">
                <div>
                    <h2 class="text-lg font-bold text-blue-800">Guías de Remisión en Tránsito (Por Ubicar)</h2>
                    <p class="text-sm text-blue-600">Transfiera los productos desde el almacén transitorio (ALM04) a su almacén definitivo.</p>
                </div>
            </div>

            <div class="p-6 space-y-4 bg-gray-50">
                @forelse($guiasPendientes as $guia)
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden">
                        <div class="px-6 py-5 cursor-pointer border-l-4 border-blue-400 flex flex-col lg:flex-row justify-between gap-4"
                             onclick="toggleAccordion('guia-{{ $guia->id_guia }}')">

                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                                    <i class="fas fa-file-invoice text-xl"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-bold text-slate-800 text-sm uppercase">Guía #{{ $guia->numero_guia }}</h3>
                                        <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-semibold">En Tránsito</span>
                                    </div>
                                    <p class="text-sm text-slate-500 mt-1">Proveedor: {{ $guia->datosProveedor->razon_social ?? $guia->proveedor }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-6">
                                <div class="text-right">
                                    <p class="text-xs uppercase text-slate-400">Emisión</p>
                                    <p class="font-bold text-slate-700">{{ \Carbon\Carbon::parse($guia->fecha_emision)->format('d/m/Y') }}</p>
                                </div>
                                <div class="w-10 h-10 rounded-xl border border-slate-200 flex items-center justify-center">
                                    <i class="fas fa-chevron-down transition-transform duration-300" id="icon-guia-{{ $guia->id_guia }}"></i>
                                </div>
                            </div>
                        </div>

                        <div id="guia-{{ $guia->id_guia }}" class="hidden border-t bg-slate-50">
                            <form action="{{ route('inventario.procesar_ubicacion_guia', $guia->id_guia) }}" method="POST" class="p-6">
                                @csrf
                                <div class="space-y-4">
                                    @foreach($guia->detalles as $detalle)
                                        <div class="bg-white border border-slate-200 rounded-xl p-4 grid grid-cols-1 lg:grid-cols-12 gap-4 items-center">
                                            <div class="lg:col-span-4">
                                                <p class="font-semibold text-slate-800 text-sm">{{ $detalle->producto->descripcion ?? $detalle->descripcion_producto }}</p>
                                                <p class="text-xs text-slate-400">Código: {{ $detalle->codigo_producto }} | Lote: {{ $detalle->lote ?? 'N/A' }}</p>
                                            </div>
                                            <div class="lg:col-span-3">
                                                <label class="block text-xs font-semibold text-slate-500 mb-1">Ubicación Actual</label>
                                                <div class="bg-slate-100 rounded-xl px-3 py-2 text-center text-sm text-slate-600">ALM04 - COMPRAS NAC/IMP</div>
                                            </div>
                                            <div class="lg:col-span-3">
                                                <label class="block text-xs font-semibold text-blue-600 mb-1">Transferir al Almacén Destino</label>
                                                <div class="relative">
                                                    <select name="items[{{ $detalle->id_detalle_guia }}][codigo_almacen]" class="w-full pl-3 pr-8 py-2 rounded-xl border-2 border-blue-200 bg-blue-50 text-blue-800 text-sm focus:ring-2 focus:ring-blue-500" required>
                                                        <option value="ALM04">No transferir</option>
                                                        @foreach($almacenes as $almacen)
                                                            @if($almacen->codigo_almacen !== 'ALM04')
                                                                <option value="{{ $almacen->codigo_almacen }}">{{ $almacen->descripcion }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="lg:col-span-2">
                                                <label class="block text-xs font-semibold text-slate-500 mb-1">Cantidad</label>
                                                <div class="bg-slate-100 rounded-xl px-3 py-2 text-center font-bold text-slate-700">
                                                    {{ number_format($detalle->cantidad, 2) }} {{ $detalle->codigo_unidad_medida }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-6 flex flex-col sm:flex-row justify-end gap-3">
                                    <button type="button" onclick="toggleAccordion('guia-{{ $guia->id_guia }}')" class="px-6 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-600 hover:bg-slate-50 font-semibold">Cancelar</button>
                                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-linear-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold shadow-lg">
                                        <i class="fas fa-exchange-alt mr-2"></i> Confirmar Transferencias
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-16">
                        <i class="fas fa-check-circle text-5xl text-slate-300 mb-4"></i>
                        <h3 class="text-lg font-bold text-slate-700">Todo Ubicado</h3>
                        <p class="text-slate-500">No hay guías de remisión pendientes de ubicación.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- SECCIÓN DE PRODUCCIÓN -->
    <div id="section-produccion" class="hidden">
        <button onclick="showCards()" class="mb-4 inline-flex items-center gap-2 text-slate-500 hover:text-slate-800 font-medium transition-colors">
            <i class="fas fa-arrow-left"></i> Volver a opciones
        </button>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b bg-emerald-50 flex flex-col sm:flex-row justify-between gap-4 items-center">
                <div>
                    <h2 class="text-lg font-bold text-emerald-800">Ingresos de Producción (PEP)</h2>
                    <p class="text-sm text-emerald-600">Apruebe los productos en proceso de producción.</p>
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
                                        <h3 class="font-bold text-slate-800 text-sm uppercase">{{ $pep->descripcion_producto_proceso }}</h3>
                                        <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-semibold">Pendiente</span>
                                    </div>
                                    <p class="text-sm text-slate-500 mt-1">Código: {{ $pep->codigo_producto_proceso }} | Lote: {{ $pep->lote_produccion }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-6">
                                <div class="text-right">
                                    <p class="text-xs uppercase text-slate-400">Fecha / OP</p>
                                    <p class="font-bold text-slate-700">{{ \Carbon\Carbon::parse($pep->fecha_ingreso)->format('d/m/Y') }} | OP #{{ $pep->idop }}</p>
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
                                        </div>
                                    </div>
                                    <div class="lg:col-span-3">
                                        <label class="block text-xs font-semibold text-slate-500 mb-1">Reportado</label>
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
                                    <button type="button" onclick="toggleAccordion('pep-{{ $pep->id_ingreso }}')" class="px-6 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-600 hover:bg-slate-50 font-semibold">Cancelar</button>
                                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-linear-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold shadow-lg">
                                        <i class="fas fa-check mr-2"></i> Aprobar e Ingresar
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
</div>

<script>
// Manejo de Secciones (Dashboard)
const cardsPanel = document.getElementById('cards-panel');
const sections = ['section-compras', 'section-guias', 'section-produccion'];

function showSection(sectionId) {
    cardsPanel.classList.add('hidden');
    sections.forEach(id => {
        document.getElementById(id).classList.add('hidden');
    });
    document.getElementById(sectionId).classList.remove('hidden');
}

function showCards() {
    sections.forEach(id => {
        document.getElementById(id).classList.add('hidden');
    });
    cardsPanel.classList.remove('hidden');
}

// Check URL hash or session logic to optionally auto-open a section if redirected after processing
document.addEventListener('DOMContentLoaded', function() {
    // Optional: if you want to keep the accordion logic for list items
});

// Manejo de Acordeones
function toggleAccordion(id) {
    const content = document.getElementById(id);
    const icon = document.getElementById('icon-' + id);

    document.querySelectorAll('[id^="compra-"], [id^="pep-"], [id^="guia-"]').forEach(el => {
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