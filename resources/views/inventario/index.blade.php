@extends('layouts.app')
@section('title', 'Control de Inventario')

@section('content')
<div class="min-h-screen bg-slate-50 py-8 px-4">
    <div class="max-w-7xl mx-auto">

        <x-page-header title="Control de Almacenes" subtitle="Consulta de saldos físicos y movimientos del inventario" />

        <!-- Buscador y Filtros -->
        <x-filter-bar action="{{ route('inventario.index') }}">
            <div class="relative flex-1 min-w-[200px]">
                <input 
                    type="text"
                    id="searchInput"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Buscar por código o descripción..."
                    class="input-field pl-11"
                >
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
            </div>
            <div class="relative sm:w-72">
                <select id="almacenFilter" name="almacen" class="input-field pl-10 appearance-none">
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
        </x-filter-bar>

        <!-- Tabla -->
        <div id="table-container" class="transition-opacity duration-300">
            @php
                $headers = [
                    ['label' => 'Código', 'class' => 'text-left'],
                    ['label' => 'Producto / Insumo', 'class' => 'text-left'],
                    ['label' => 'Almacén', 'class' => 'text-center'],
                    ['label' => 'Stock', 'class' => 'text-right'],
                    ['label' => 'Stock Mín', 'class' => 'text-right'],
                    ['label' => 'Stock Máx', 'class' => 'text-right'],
                    ['label' => 'Último Movimiento', 'class' => 'text-center']
                ];
            @endphp
            <x-data-table :headers="$headers" :hasActions="false">
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

                        <td class="px-6 py-4 text-right text-slate-600">
                            {{ number_format($stock->stock_minimo, 2, '.', '') }}
                        </td>

                        <td class="px-6 py-4 text-right text-slate-600">
                            {{ $stock->stock_maximo ? number_format($stock->stock_maximo, 2, '.', '') : '-' }}
                        </td>

                        <td class="px-6 py-4 text-center text-slate-500 text-xs">
                            {{ $stock->fecha_ultimo_movimiento 
                                ? \Carbon\Carbon::parse($stock->fecha_ultimo_movimiento)->format('d/m/Y H:i') 
                                : 'N/A' }}
                        </td>
                    </tr>
                @empty
                    <x-slot name="empty">
                        <i class="fas fa-box-open text-5xl text-slate-300 mb-4"></i>
                        <p class="text-lg font-semibold text-slate-600">
                            No hay registros de inventario
                        </p>
                        <p class="text-sm text-slate-400 mt-1">
                            Aún no existen movimientos para mostrar.
                        </p>
                    </x-slot>
                @endforelse
            </x-data-table>

            <!-- Paginación -->
            @if($stocks->hasPages())
                <div class="border-t border-slate-100 bg-slate-50 px-6 py-4">
                    {{ $stocks->links() }}
                </div>
            @endif
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
});
</script>
@endsection
