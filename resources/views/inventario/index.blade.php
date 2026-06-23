@extends('layouts.app')
@section('title', 'Control de Inventario')

@section('content')
<div class="min-h-screen bg-slate-50 py-8 px-4">
    <div class="max-w-7xl mx-auto">

        <x-page-header title="Control de Almacenes" subtitle="Consulta de saldos físicos y movimientos del inventario" />

        <!-- Buscador y Filtros -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-5 mb-6">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="relative flex-1">
                    <input 
                        type="text"
                        id="searchInput"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Buscar por código o descripción..."
                        class="w-full pl-11 pr-4 py-3 rounded-2xl border border-slate-300 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition"
                    >
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                </div>
                <div class="relative sm:w-72">
                    <select id="almacenFilter" name="almacen"
                        class="w-full pl-10 pr-4 py-3 rounded-2xl border border-slate-300 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition appearance-none text-sm">
                        <option value="todos">Todos los almacenes</option>
                        @foreach($almacenes as $almacen)
                            <option value="{{ $almacen->codigo_almacen }}"
                                {{ request('almacen', 'todos') == $almacen->codigo_almacen ? 'selected' : '' }}>
                                {{ $almacen->descripcion }}
                            </option>
                        @endforeach
                    </select>
                    <i class="fas fa-warehouse absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                </div>
                <div class="flex">
                    <a href="{{ route('inventario.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 md:px-6 py-3 rounded-2xl border border-gray-300 transition font-medium text-sm flex items-center justify-center w-full h-full">
                        <i class="fas fa-times mr-2"></i> <span class="hidden sm:inline">Limpiar</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Tabla -->
        <div id="table-container" class="transition-opacity duration-300">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-linear-to-r from-slate-800 to-slate-700 text-slate-200 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-4 text-left">Código</th>
                            <th class="px-6 py-4 text-left">Producto / Insumo</th>
                            <th class="px-6 py-4 text-center">Almacén</th>
                            <th class="px-6 py-4 text-right">Stock</th>
                            <th class="px-6 py-4 text-right">Stock Mín</th>
                            <th class="px-6 py-4 text-right">Stock Máx</th>
                            <th class="px-6 py-4 text-center">Último Movimiento</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($stocks as $stock)
                            @php
                                $bajoMinimo = $stock->stock_minimo > 0 && $stock->stock_actual < $stock->stock_minimo;
                            @endphp
                            <tr class="hover:bg-slate-50 transition {{ $bajoMinimo ? 'bg-red-50/50' : '' }}">
                                <td class="px-6 py-4 text-slate-500 font-medium">
                                    {{ $stock->codigo_producto }}
                                </td>

                                <td class="px-6 py-4">
                                    <span class="font-semibold text-slate-800">
                                        {{ $stock->producto }}
                                    </span>
                                    @if($bajoMinimo)
                                        <span class="ml-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-red-100 text-red-600 text-[10px] font-bold">
                                            <i class="fas fa-exclamation-triangle"></i> BAJO MÍNIMO
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex px-3 py-1 rounded-xl bg-primary-50 text-primary text-xs font-semibold border border-primary-50">
                                        {{ $stock->almacen }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <span class="inline-flex px-3 py-1 rounded-xl text-sm font-bold
                                        {{ $bajoMinimo
                                            ? 'bg-red-50 text-red-600 border border-red-100'
                                            : ($stock->stock_actual <= 0 
                                                ? 'bg-red-50 text-red-600 border border-red-100' 
                                                : 'bg-green-50 text-green-600 border border-green-100') }}">
                                        {{ number_format($stock->stock_actual, 2) }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <input type="number"
                                           value="{{ number_format($stock->stock_minimo, 2, '.', '') }}"
                                           step="0.01"
                                           data-id="{{ $stock->id_inventario ?? $stock->codigo_producto }}"
                                           data-field="stock_minimo"
                                           class="w-20 text-right rounded-lg border border-slate-200 px-2 py-1 text-sm focus:ring-2 focus:ring-primary/20 stock-min-input">
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <input type="number"
                                           value="{{ $stock->stock_maximo ? number_format($stock->stock_maximo, 2, '.', '') : '' }}"
                                           step="0.01"
                                           data-id="{{ $stock->id_inventario ?? $stock->codigo_producto }}"
                                           data-field="stock_maximo"
                                           class="w-20 text-right rounded-lg border border-slate-200 px-2 py-1 text-sm focus:ring-2 focus:ring-primary/20 stock-min-input">
                                </td>

                                <td class="px-6 py-4 text-center text-slate-500 text-xs">
                                    {{ $stock->fecha_ultimo_movimiento 
                                        ? \Carbon\Carbon::parse($stock->fecha_ultimo_movimiento)->format('d/m/Y H:i') 
                                        : 'N/A' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <i class="fas fa-box-open text-5xl text-slate-300 mb-4"></i>
                                    <p class="text-lg font-semibold text-slate-600">
                                        No hay registros de inventario
                                    </p>
                                    <p class="text-sm text-slate-400 mt-1">
                                        Aún no existen movimientos para mostrar.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($stocks->hasPages())
                <div class="border-t border-slate-100 bg-slate-50 px-6 py-4">
                    {{ $stocks->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const tableContainer = document.getElementById('table-container');
    let timeout = null;

    function fetchResults(url = null) {
        if (!url) {
            url = new URL(window.location.href);
            if (searchInput.value) {
                url.searchParams.set('search', searchInput.value);
            } else {
                url.searchParams.delete('search');
            }
            const almFilter = document.getElementById('almacenFilter');
            if (almFilter && almFilter.value !== 'todos') {
                url.searchParams.set('almacen', almFilter.value);
            } else {
                url.searchParams.delete('almacen');
            }
            url.searchParams.delete('page');
        }
        window.history.pushState({}, '', url);
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

    searchInput.addEventListener('input', function () {
        clearTimeout(timeout);
        timeout = setTimeout(() => fetchResults(), 400);
    });

    document.getElementById('almacenFilter').addEventListener('change', function () {
        fetchResults();
    });

    tableContainer.addEventListener('click', function(e) {
        const aTag = e.target.closest('nav[role="navigation"] a');
        if (aTag) {
            e.preventDefault();
            fetchResults(new URL(aTag.href));
        }
    });

    // Inline update de stock_minimo / stock_maximo
    tableContainer.addEventListener('change', function(e) {
        const input = e.target.closest('.stock-min-input');
        if (!input) return;

        const id = input.dataset.id;
        const field = input.dataset.field;
        const value = input.value;

        const formData = new FormData();
        formData.append('id_inventario', id);
        formData.append(field, value);

        fetch('{{ route("inventario.actualizar_stock_minimo") }}', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                input.classList.add('border-green-400');
                setTimeout(() => input.classList.remove('border-green-400'), 1500);
            }
        })
        .catch(error => console.error('Error al actualizar stock:', error));
    });
});
</script>
@endsection