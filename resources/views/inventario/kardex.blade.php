@extends('layouts.app')
@section('title', 'Kardex Valorizado')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-7xl">

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight">Kardex Valorizado</h1>
            <p class="text-sm text-slate-500 mt-1">Historial cronológico valorizado de entradas, salidas y saldos.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('inventario.kardex.exportar.excel', request()->query()) }}" id="btn-export-excel"
               class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold shadow transition no-print">
                <i class="fas fa-file-excel mr-1"></i> Exportar Excel
            </a>
            <a href="{{ route('inventario.kardex.exportar.pdf', request()->query()) }}" id="btn-export-pdf"
               class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold shadow transition no-print">
                <i class="fas fa-file-pdf mr-1"></i> Exportar PDF
            </a>
        </div>
    </div>

    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 mb-6">
        <form method="GET" id="search-form" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Tipo Documento</label>
                <select name="documento" id="inputDocumento"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
                    <option value="">Todos</option>
                    @foreach($tiposDocumento as $td)
                        <option value="{{ $td }}" {{ request('documento') == $td ? 'selected' : '' }}>{{ $td }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Producto</label>
                <input type="text" name="codigo_producto" id="inputProducto" value="{{ request('codigo_producto') }}"
                    placeholder="Código o descripción..."
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Almacén</label>
                <select name="codigo_almacen" id="inputAlmacen"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
                    <option value="">Todos</option>
                    @foreach($almacenes as $alm)
                        <option value="{{ $alm->codigo_almacen }}" {{ request('codigo_almacen') == $alm->codigo_almacen ? 'selected' : '' }}>{{ $alm->descripcion }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Fecha Desde</label>
                <input type="date" name="fecha_desde" id="inputFechaDesde" value="{{ request('fecha_desde') }}"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Fecha Hasta</label>
                <input type="date" name="fecha_hasta" id="inputFechaHasta" value="{{ request('fecha_hasta') }}"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit"
                    class="px-4 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-sm font-semibold shadow transition">
                    <i class="fas fa-search mr-1"></i> Filtrar
                </button>
                <a href="{{ route('inventario.kardex') }}"
                    class="px-4 py-2 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-sm font-semibold transition">
                    Limpiar
                </a>
                <button type="button" onclick="window.print()" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-sm font-bold hover:bg-slate-200 transition no-print">
                    <i class="fas fa-print mr-1"></i> Imprimir
                </button>
            </div>
        </form>
    </div>

    <div id="table-container" class="transition-opacity duration-300">

    @if(isset($resumen))
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4 no-print">
        <div class="bg-green-50 border border-green-200 rounded-xl p-3 text-center">
            <p class="text-xs text-green-600 font-semibold uppercase">Total Entradas</p>
            <p class="text-lg font-black text-green-700">{{ number_format($resumen->total_entradas, 2) }}</p>
            <p class="text-xs text-green-500">S/ {{ number_format($resumen->total_entradas_val, 2) }}</p>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-xl p-3 text-center">
            <p class="text-xs text-red-600 font-semibold uppercase">Total Salidas</p>
            <p class="text-lg font-black text-red-700">{{ number_format($resumen->total_salidas, 2) }}</p>
            <p class="text-xs text-red-500">S/ {{ number_format($resumen->total_salidas_val, 2) }}</p>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 text-center">
            <p class="text-xs text-blue-600 font-semibold uppercase">Saldo Final (Cant.)</p>
            <p class="text-lg font-black text-blue-700">{{ number_format($resumen->saldo_final_cantidad, 2) }}</p>
        </div>
        <div class="bg-primary-50 border border-primary-200 rounded-xl p-3 text-center">
            <p class="text-xs text-primary-600 font-semibold uppercase">Saldo Final (Valorizado)</p>
            <p class="text-lg font-black text-primary-700">S/ {{ number_format($resumen->saldo_final_valor, 2) }}</p>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="bg-slate-800 text-slate-300 uppercase tracking-wider font-semibold text-center">
                        <th class="p-3 border border-slate-700 align-middle" rowspan="2">Fecha</th>
                        <th class="p-3 border border-slate-700 align-middle" rowspan="2">Producto / Almacén</th>
                        <th class="p-3 border border-slate-700 align-middle" rowspan="2">Tipo Operac.</th>
                        <th class="p-3 border border-slate-700 align-middle" rowspan="2">Documento</th>
                        <th class="p-2 border border-slate-700" colspan="3">Entradas</th>
                        <th class="p-2 border border-slate-700" colspan="3">Salidas</th>
                        <th class="p-2 border border-slate-700" colspan="3">Saldo Final</th>
                    </tr>
                    <tr class="bg-slate-700 text-slate-400 text-[10px] uppercase tracking-wider text-center">
                        <th class="p-2 border border-slate-600">Cantidad</th>
                        <th class="p-2 border border-slate-600">C. Unitario</th>
                        <th class="p-2 border border-slate-600">Costo Total</th>
                        <th class="p-2 border border-slate-600">Cantidad</th>
                        <th class="p-2 border border-slate-600">C. Unitario</th>
                        <th class="p-2 border border-slate-600">Costo Total</th>
                        <th class="p-2 border border-slate-600">Cantidad</th>
                        <th class="p-2 border border-slate-600">C. Unitario</th>
                        <th class="p-2 border border-slate-600">Costo Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($movimientos as $mov)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-3 border-r border-slate-100 text-slate-500 whitespace-nowrap text-[11px] text-center">
                            {{ \Carbon\Carbon::parse($mov->fecha_movimiento)->format('d/m/Y H:i') }}
                        </td>
                        <td class="p-3 border-r border-slate-100 font-semibold text-slate-800">
                            <span class="text-xs text-indigo-600 block mb-0.5">{{ $mov->codigo_producto }}</span>
                            {{ $mov->producto }}
                            <p class="text-[10px] text-slate-400 mt-0.5">{{ $mov->almacen }}</p>
                        </td>
                        <td class="p-3 border-r border-slate-100 text-center">
                            @php
                                $badgeColor = [
                                    'INGRESO' => 'green',
                                    'SALIDA'  => 'red',
                                    'AJUSTE'  => 'amber',
                                    'EXTORNO' => 'purple',
                                ][$mov->tipo_movimiento] ?? 'slate';
                            @endphp
                            <x-badge color="{{ $badgeColor }}">{{ $mov->tipo_movimiento }}</x-badge>
                        </td>
                        <td class="p-3 border-r border-slate-100">
                            <div class="flex items-center gap-2">
                                <div>
                                    <span class="text-slate-600 font-medium text-[11px]">{{ $mov->documento }}</span>
                                    <p class="text-[10px] text-slate-400">{{ $mov->numero_documento }}</p>
                                </div>
                                @if($mov->tipo_movimiento === 'INGRESO' && in_array($mov->documento, ['RECEPCION_PEP', 'RECEPCION_PEP_GLOBAL', 'PRODUCCION', 'MERMA']))
                                <button type="button" onclick="verDesgloseCosto({{ $mov->id_kardex }})" class="text-blue-500 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 p-1 rounded transition-colors" title="Ver desglose de costo">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </button>
                                @endif
                            </div>
                        </td>
                        <!-- ENTRADAS -->
                        <td class="p-3 border-r border-slate-100 text-right font-semibold text-green-700 bg-green-50/30">
                            {{ $mov->cantidad_entrada > 0 ? number_format($mov->cantidad_entrada, 2) : '-' }}
                        </td>
                        <td class="p-3 border-r border-slate-100 text-right text-slate-500 bg-green-50/30">
                            @if($mov->cantidad_entrada > 0 && $mov->total_entrada > 0)
                                {{ number_format($mov->total_entrada / $mov->cantidad_entrada, 6) }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="p-3 border-r border-slate-200 text-right font-medium text-green-700 bg-green-50/30">
                            {{ $mov->total_entrada > 0 ? number_format($mov->total_entrada, 2) : '-' }}
                        </td>
                        
                        <!-- SALIDAS -->
                        <td class="p-3 border-r border-slate-100 text-right font-semibold text-red-700 bg-red-50/30">
                            {{ $mov->cantidad_salida > 0 ? number_format($mov->cantidad_salida, 2) : '-' }}
                        </td>
                        <td class="p-3 border-r border-slate-100 text-right text-slate-500 bg-red-50/30">
                            @if($mov->cantidad_salida > 0 && $mov->total_salida > 0)
                                {{ number_format($mov->total_salida / $mov->cantidad_salida, 6) }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="p-3 border-r border-slate-200 text-right font-medium text-red-700 bg-red-50/30">
                            {{ $mov->total_salida > 0 ? number_format($mov->total_salida, 2) : '-' }}
                        </td>
                        
                        <!-- SALDO FINAL -->
                        <td class="p-3 border-r border-slate-100 text-right font-bold text-slate-800">
                            {{ number_format($mov->cantidad_saldo, 2) }}
                        </td>
                        <td class="p-3 border-r border-slate-100 text-right text-slate-500">
                            {{ $mov->costo_promedio > 0 ? number_format($mov->costo_promedio, 6) : '0.000000' }}
                        </td>
                        <td class="p-3 text-right font-bold text-primary">
                            {{ $mov->total_saldo > 0 ? number_format($mov->total_saldo, 2) : '0.00' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="13" class="p-12 text-center text-slate-400">
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
    </div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('search-form');
    const inputProducto = document.getElementById('inputProducto');
    const tableContainer = document.getElementById('table-container');
    let timeout = null;

    function fetchResults(url = null) {
        if (!url) {
            url = new URL(window.location.href);
            const formData = new FormData(form);
            for (const [key, val] of formData) {
                if (val) {
                    url.searchParams.set(key, val);
                } else {
                    url.searchParams.delete(key);
                }
            }
            url.searchParams.delete('page');
        }
        window.history.pushState({}, '', url);
        
        const currentParams = url.searchParams.toString();
        document.getElementById('btn-export-excel').href = "{{ route('inventario.kardex.exportar.excel') }}?" + currentParams;
        document.getElementById('btn-export-pdf').href = "{{ route('inventario.kardex.exportar.pdf') }}?" + currentParams;
        
        tableContainer.classList.add('opacity-50', 'pointer-events-none');

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newContainer = doc.getElementById('table-container');
                if (newContainer) {
                    tableContainer.innerHTML = newContainer.innerHTML;
                }
            })
            .catch(error => console.error('Error al filtrar:', error))
            .finally(() => {
                tableContainer.classList.remove('opacity-50', 'pointer-events-none');
            });
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        fetchResults();
    });

    inputProducto.addEventListener('input', function() {
        clearTimeout(timeout);
        timeout = setTimeout(() => fetchResults(), 400);
    });

    form.querySelectorAll('select, input[type="date"]').forEach(function(el) {
        el.addEventListener('change', function() {
            fetchResults();
        });
    });

    tableContainer.addEventListener('click', function(e) {
        const aTag = e.target.closest('nav[role="navigation"] a');
        if (aTag) {
            e.preventDefault();
            fetchResults(new URL(aTag.href));
        }
    });
});

window.verDesgloseCosto = function(id) {
    const modal = document.getElementById('modalDesgloseCosto');
    const content = document.getElementById('modalDesgloseContent');
    modal.classList.remove('hidden');
    content.innerHTML = '<div class="flex justify-center p-4"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div></div>';

    fetch(`/admin/inventario/kardex/${id}/desglose-costo`)
        .then(res => {
            if (!res.ok) throw new Error('Error en red o servidor');
            return res.json();
        })
        .then(data => {
            if (data.html) {
                content.innerHTML = data.html;
            } else if (data.error) {
                content.innerHTML = `<p class="text-red-500 text-sm p-4">${data.error}</p>`;
            }
        })
        .catch(err => {
            content.innerHTML = `<p class="text-red-500 text-sm p-4">Error al cargar el desglose. Es posible que el registro ya no exista o el servidor esté fallando.</p>`;
            console.error(err);
        });
};

window.cerrarModalDesglose = function() {
    document.getElementById('modalDesgloseCosto').classList.add('hidden');
};
</script>

<!-- Modal Desglose de Costo -->
<div id="modalDesgloseCosto" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm overflow-y-auto">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="relative w-full max-w-2xl rounded-2xl bg-white shadow-2xl overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <h3 class="text-lg font-bold text-slate-800">Desglose de Costo de Producción</h3>
                <button onclick="cerrarModalDesglose()" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="modalDesgloseContent" class="p-6">
                <!-- Contenido cargado por AJAX -->
            </div>
            <div class="bg-slate-50 px-6 py-4 flex justify-end">
                <button onclick="cerrarModalDesglose()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-lg text-sm font-medium transition-colors">
                    Cerrar
                </button>
            </div>
        </div>
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