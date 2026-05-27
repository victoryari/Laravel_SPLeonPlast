@extends('layouts.app')
@section('title', 'Bandeja de Ajustes de Inventario')

@section('content')
<div class="min-h-screen bg-slate-50 py-10 px-4">
    <div class="max-w-7xl mx-auto">

        <div class="rounded-3xl bg-white shadow-xl border border-slate-200 overflow-hidden">
            <div class="h-2 bg-linear-to-r from-blue-600 to-indigo-600"></div>

            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-slate-800">Bandeja de Ajustes</h2>
                    <a href="{{ route('inventario.ajuste') }}"
                        class="px-4 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white text-sm font-semibold shadow transition">
                        <i class="fas fa-plus mr-1"></i> Nuevo Ajuste
                    </a>
                </div>

                <form method="GET" id="search-form" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 p-4 bg-slate-50 rounded-2xl border border-slate-200">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Buscar</label>
                        <input type="text" name="search" id="inputSearch" value="{{ request('search') }}"
                            placeholder="Producto, documento, motivo..."
                            class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Almacén</label>
                        <select name="codigo_almacen" id="inputAlmacen"
                            class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
                            <option value="">Todos</option>
                            @foreach($almacenes as $a)
                                <option value="{{ $a->codigo_almacen }}" {{ request('codigo_almacen') == $a->codigo_almacen ? 'selected' : '' }}>
                                    {{ $a->descripcion }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Fecha Desde</label>
                        <input type="date" name="fecha_desde" id="inputFechaDesde" value="{{ request('fecha_desde') }}"
                            class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Fecha Hasta</label>
                        <input type="date" name="fecha_hasta" id="inputFechaHasta" value="{{ request('fecha_hasta') }}"
                            class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
                    </div>
                    <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-4">
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-sm font-semibold shadow transition">
                            <i class="fas fa-search mr-1"></i> Filtrar
                        </button>
                        <a href="{{ route('inventario.ajuste.lista') }}"
                            class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-sm font-semibold transition">
                            Limpiar
                        </a>
                    </div>
                </form>

                <div id="table-container" class="transition-opacity duration-300">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-500 text-xs uppercase tracking-wider">
                                <th class="text-left py-3 px-4 font-semibold">#</th>
                                <th class="text-left py-3 px-4 font-semibold">Fecha</th>
                                <th class="text-left py-3 px-4 font-semibold">Documento</th>
                                <th class="text-left py-3 px-4 font-semibold">Producto</th>
                                <th class="text-left py-3 px-4 font-semibold">Almacén</th>
                                <th class="text-center py-3 px-4 font-semibold">Tipo</th>
                                <th class="text-right py-3 px-4 font-semibold">Cantidad</th>
                                <th class="text-right py-3 px-4 font-semibold">U.M.</th>
                                <th class="text-center py-3 px-4 font-semibold">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ajustes as $a)
                            <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                                <td class="py-3 px-4 text-slate-500">{{ $a->id_kardex }}</td>
                                <td class="py-3 px-4 text-slate-700">{{ \Carbon\Carbon::parse($a->fecha_movimiento)->format('d/m/Y H:i') }}</td>
                                <td class="py-3 px-4 font-mono text-xs text-slate-600">{{ $a->numero_documento }}</td>
                                <td class="py-3 px-4 text-slate-700 max-w-[200px] truncate" title="{{ $a->producto }}">
                                    {{ $a->codigo_producto }} - {{ $a->producto }}
                                </td>
                                <td class="py-3 px-4 text-slate-600">{{ $a->almacen }}</td>
                                <td class="py-3 px-4 text-center">
                                    @if($a->cantidad_entrada > 0)
                                        <x-badge color="green">INGRESO</x-badge>
                                    @else
                                        <x-badge color="red">SALIDA</x-badge>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-semibold {{ $a->cantidad_entrada > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($a->cantidad_entrada > 0 ? $a->cantidad_entrada : $a->cantidad_salida, 2) }}
                                </td>
                                <td class="py-3 px-4 text-right text-slate-600">{{ $a->unidad_medida ?? '—' }}</td>
                                <td class="py-3 px-4 text-center">
                                    <div class="flex justify-center gap-1">
                                        <a href="{{ route('inventario.ajuste.show', $a->id_kardex) }}"
                                            class="p-2 rounded-lg text-primary hover:bg-primary-50 transition" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('inventario.ajuste.edit', $a->id_kardex) }}"
                                            class="p-2 rounded-lg text-amber-600 hover:bg-amber-50 transition" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('inventario.ajuste.destroy', $a->id_kardex) }}" method="POST"
                                            onsubmit="return confirm('¿Está seguro de eliminar este ajuste? Esta acción no se puede deshacer.');"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="p-2 rounded-lg text-red-600 hover:bg-red-50 transition" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-slate-400">
                                    <i class="fas fa-inbox text-3xl mb-3 block"></i>
                                    No se encontraron ajustes registrados.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $ajustes->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('search-form');
    const inputSearch = document.getElementById('inputSearch');
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

    inputSearch.addEventListener('input', function() {
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
</script>
@endsection
